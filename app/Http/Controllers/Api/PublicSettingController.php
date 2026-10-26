<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class PublicSettingController extends Controller
{
    /** Only these groups are ever exposed to visitors. */
    private const PUBLIC_GROUPS = ['general', 'contact'];

    public function index()
    {
        $settings = Setting::query()
            ->whereIn('group', self::PUBLIC_GROUPS)
            ->pluck('value', 'key');

        return response()->json([
            'settings' => $settings,
        ]);
    }
}
