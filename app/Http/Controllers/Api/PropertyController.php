<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function ownerIndex(Request $request)
    {
        return response()->json([
            'properties' => Property::query()
                ->where('owner_id', $request->user()->id)
                ->with(['type', 'city', 'images', 'amenities'])
                ->latest()
                ->get(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Property::query()
            ->where('status', 'published')
            ->with([
                'type',
                'city',
                'images' => fn ($q) => $q->where('is_primary', true),
            ]);

        $query->when($request->filled('city_id'), function ($q) use ($request) {
            $q->where('city_id', $request->input('city_id'));
        });

        $query->when($request->filled('type_id'), function ($q) use ($request) {
            $q->where('type_id', $request->input('type_id'));
        });

        $query->when($request->filled('type_ids'), function ($q) use ($request) {
            $ids = array_filter(explode(',', (string) $request->input('type_ids')), 'ctype_digit');
            if ($ids !== []) {
                $q->whereIn('type_id', $ids);
            }
        });

        $query->when($request->filled('city_ids'), function ($q) use ($request) {
            $ids = array_filter(explode(',', (string) $request->input('city_ids')), 'ctype_digit');
            if ($ids !== []) {
                $q->whereIn('city_id', $ids);
            }
        });

        $query->when($request->filled('q'), function ($q) use ($request) {
            $search = trim((string) $request->input('q'));
            $q->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%")
                    ->orWhereHas('city', fn ($cityQuery) => $cityQuery->where('name_en', 'like', "%{$search}%"))
                    ->orWhereHas('type', fn ($typeQuery) => $typeQuery->where('name_en', 'like', "%{$search}%"));
            });
        });

        $query->when($request->filled('operation'), function ($q) use ($request) {
            $q->where('operation', $request->input('operation'));
        });

        $query->when($request->filled('min_price'), function ($q) use ($request) {
            $q->where('price', '>=', $request->input('min_price'));
        });

        $query->when($request->filled('max_price'), function ($q) use ($request) {
            $q->where('price', '<=', $request->input('max_price'));
        });

        $query->when($request->filled('bedrooms'), function ($q) use ($request) {
            $q->where('bedrooms', $request->input('bedrooms'));
        });

        $allowedSortColumns = ['price', 'created_at', 'area'];
        $sortBy = in_array($request->input('sort_by'), $allowedSortColumns)
            ? $request->input('sort_by')
            : 'created_at';
        $sortDirection = $request->input('sort_direction') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        $properties = $query->paginate(12);

        return response()->json($properties);
    }

    public function show(string $slug)
    {
        $property = Property::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['type', 'city', 'owner:id,name', 'images', 'amenities'])
            ->firstOrFail();

        $property->increment('views_count');

        return response()->json([
            'property' => $property,
        ]);
    }

    public function uploadImages(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
        ]);

        $hasPrimaryAlready = $property->images()->where('is_primary', true)->exists();
        $currentImageCount = $property->images()->count();

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('cloudinary');

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('properties/' . $property->id, 'cloudinary');
            $url = $disk->url($path);

            $property->images()->create([
                'image_path' => $url,
                'is_primary' => ! $hasPrimaryAlready && $index === 0,
                'order' => $currentImageCount + $index,
            ]);
        }

        return response()->json([
            'images' => $property->images()->orderBy('order')->get(),
        ], 201);
    }

    public function store(Request $request)
    {
        if ($request->user()->broker_verification_status !== 'verified') {
            return response()->json([
                'message' => 'Your broker license must be verified before you can publish properties.',
                'broker_verification_status' => $request->user()->broker_verification_status,
            ], 403);
        }

        $validated = $request->validate([
            'type_id' => ['required', 'exists:property_types,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'original_language' => ['required', 'in:ar,en'],
            'title_ar' => ['required_if:original_language,ar', 'nullable', 'string', 'max:255'],
            'title_en' => ['required_if:original_language,en', 'nullable', 'string', 'max:255'],
            'description_ar' => ['required_if:original_language,ar', 'nullable', 'string'],
            'description_en' => ['required_if:original_language,en', 'nullable', 'string'],
            'operation' => ['required', 'in:rent,sale'],
            'price' => ['required', 'numeric', 'min:0'],
            'area' => ['required', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'district' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'amenities' => ['sometimes', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
        ]);

        $titleForSlug = $validated['title_' . $validated['original_language']];
        $slug = Str::slug($titleForSlug) . '-' . Str::random(6);

        $property = Property::create([
            ...Arr::except($validated, ['amenities']),
            'slug' => $slug,
            'owner_id' => $request->user()->id,
        ]);

        if (array_key_exists('amenities', $validated)) {
            $property->amenities()->sync($validated['amenities']);
        }

        return response()->json([
            'property' => $property->load('type', 'city', 'owner', 'amenities'),
        ], 201);
    }

    public function update(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'type_id' => ['sometimes', 'exists:property_types,id'],
            'city_id' => ['sometimes', 'exists:cities,id'],
            'title_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'operation' => ['sometimes', 'in:rent,sale'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'area' => ['sometimes', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'district' => ['nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'amenities' => ['sometimes', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
        ]);

        $property->update(Arr::except($validated, ['amenities']));

        if (array_key_exists('amenities', $validated)) {
            $property->amenities()->sync($validated['amenities']);
        }

        // Any edit sends the listing back to moderation. A rejected listing
        // returns to "pending" too, so the owner can fix it and resubmit.
        if (in_array($property->status, ['published', 'rejected'], true)) {
            $property->status = 'pending';
            if (array_key_exists('rejection_reason', $property->getAttributes())) {
                $property->rejection_reason = null;
            }
            $property->save();
        }

        return response()->json([
            'property' => $property->fresh()->load('type', 'city', 'amenities'),
        ]);
    }

    public function destroy(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully.',
        ]);
    }
}