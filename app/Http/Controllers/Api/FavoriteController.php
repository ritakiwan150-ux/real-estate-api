<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        // whereHas() honours soft deletes, so deleted or unpublished
        // properties never come back as `property: null`.
        $favorites = $request->user()
            ->favorites()
            ->whereHas('property', fn($q) => $q->where('status', 'published'))
            ->with([
                'property.type',
                'property.city',
                'property.images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('order'),
            ])
            ->latest('id')
            ->get();

        return response()->json([
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request, Property $property)
    {
        $favorite = $request->user()->favorites()->firstOrCreate([
            'property_id' => $property->id,
        ]);

        return response()->json([
            'message' => 'Property added to favorites.',
            'favorite' => $favorite,
        ], 201);
    }

    public function destroy(Request $request, Property $property)
    {
        $request->user()->favorites()->where('property_id', $property->id)->delete();

        return response()->json([
            'message' => 'Property removed from favorites.',
        ]);
    }
}
