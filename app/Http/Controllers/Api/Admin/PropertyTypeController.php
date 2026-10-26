<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    public function index()
    {
        return response()->json([
            'property_types' => PropertyType::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $propertyType = PropertyType::create($validated);

        return response()->json([
            'property_type' => $propertyType,
        ], 201);
    }

    public function update(Request $request, PropertyType $propertyType)
    {
        $validated = $request->validate([
            'name_ar' => ['sometimes', 'string', 'max:100'],
            'name_en' => ['sometimes', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $propertyType->update($validated);

        return response()->json([
            'property_type' => $propertyType->fresh(),
        ]);
    }

    public function destroy(PropertyType $propertyType)
    {
        if ($propertyType->properties()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a property type that has properties linked to it.',
            ], 422);
        }

        $propertyType->delete();

        return response()->json([
            'message' => 'Property type deleted successfully.',
        ]);
    }
}
