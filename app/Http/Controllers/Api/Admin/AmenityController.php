<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index()
    {
        return response()->json([
            'amenities' => Amenity::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $amenity = Amenity::create($validated);

        return response()->json(['amenity' => $amenity], 201);
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name_ar' => ['sometimes', 'string', 'max:100'],
            'name_en' => ['sometimes', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $amenity->update($validated);

        return response()->json(['amenity' => $amenity->fresh()]);
    }

    public function destroy(Amenity $amenity)
    {
        if ($amenity->properties()->exists()) {
            return response()->json([
                'message' => 'Cannot delete an amenity that is linked to properties.',
            ], 422);
        }

        $amenity->delete();

        return response()->json(['message' => 'Amenity deleted successfully.']);
    }
}
