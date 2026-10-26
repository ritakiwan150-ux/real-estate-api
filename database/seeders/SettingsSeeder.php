<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Smart Real Estate', 'type' => 'text', 'group' => 'general'],
            ['key' => 'hero_title', 'value' => 'Find Your Dream Home', 'type' => 'text', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'info@example.com', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+971500000000', 'type' => 'text', 'group' => 'contact'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
