<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        return response()->json([
            'properties' => [
                'total' => Property::count(),
                'published' => Property::where('status', 'published')->count(),
                'pending' => Property::where('status', 'pending')->count(),
                'rejected' => Property::where('status', 'rejected')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
            ],
            'inquiries' => [
                'total' => Inquiry::count(),
                'this_month' => Inquiry::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'properties_by_city' => Property::query()
                ->select('city_id', DB::raw('count(*) as total'))
                ->groupBy('city_id')
                ->with('city:id,name_en')
                ->get(),
        ]);
    }
}
