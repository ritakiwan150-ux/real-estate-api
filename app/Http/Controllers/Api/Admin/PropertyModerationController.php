<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class PropertyModerationController extends Controller
{
    public function pending()
    {
        $properties = Property::query()
            ->where('status', 'pending')
            ->with(['type', 'city', 'owner'])
            ->latest()
            ->paginate(15);

        return response()->json($properties);
    }

    public function approve(Request $request, Property $property)
    {
        $property->status = 'published';
        $property->rejection_reason = null;
        $property->save();

        ActivityLogger::log($request->user(), 'approved_property', $property, "Approved property #{$property->id}");

        return response()->json([
            'message' => 'Property approved and published.',
            'property' => $property->fresh(),
        ]);
    }

    public function reject(Request $request, Property $property)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $property->status = 'rejected';
        $property->rejection_reason = $validated['rejection_reason'];
        $property->save();

        ActivityLogger::log($request->user(), 'rejected_property', $property, $validated['rejection_reason']);

        return response()->json([
            'message' => 'Property rejected.',
            'property' => $property->fresh(),
        ]);
    }

    public function toggleFeatured(Property $property)
    {
        if ($property->status !== 'published') {
            return response()->json([
                'message' => 'Only published properties can be featured.',
            ], 422);
        }

        $property->is_featured = ! $property->is_featured;
        $property->save();

        return response()->json([
            'message' => $property->is_featured
                ? 'Property marked as featured.'
                : 'Property removed from featured.',
            'property' => $property->fresh(),
        ]);
    }
}
