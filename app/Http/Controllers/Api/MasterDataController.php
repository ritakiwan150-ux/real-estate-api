<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\City;
use App\Models\PropertyType;

class MasterDataController extends Controller
{
    public function propertyTypes()
    {
        return response()->json([
            'property_types' => PropertyType::where('is_active', true)->get(),
        ]);
    }

    public function cities()
    {
        return response()->json([
            'cities' => City::where('is_active', true)->get(),
        ]);
    }

    public function amenities()
    {
        return response()->json([
            'amenities' => Amenity::all(),
        ]);
    }
}
