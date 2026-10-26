<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'أحمد العميل',
                'password' => 'password',
            ]
        );
        $client->assignRole('client');

        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'سارة الوسيطة',
                'password' => 'password',
                'broker_license_number' => 'RERA-123456',
                'broker_verification_status' => 'verified',
            ]
        );
        $owner->assignRole('owner');

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مدير النظام',
                'password' => 'password',
            ]
        );
        $admin->assignRole('admin');
    }
}