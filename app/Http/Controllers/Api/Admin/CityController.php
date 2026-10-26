<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        return response()->json([
            'cities' => City::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:100'],
            'name_en' => ['required', 'string', 'max:100'],
        ]);

        $city = City::create($validated);

        return response()->json(['city' => $city], 201);
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name_ar' => ['sometimes', 'string', 'max:100'],
            'name_en' => ['sometimes', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $city->update($validated);

        return response()->json(['city' => $city->fresh()]);
    }

    public function destroy(City $city)
    {
        if ($city->properties()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a city that has properties linked to it.',
            ], 422);
        }

        $city->delete();

        return response()->json(['message' => 'City deleted successfully.']);
    }
}