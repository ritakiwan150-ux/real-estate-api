<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view-properties',
            'create-properties',
            'edit-own-properties',
            'delete-own-properties',
            'approve-properties',
            'reject-properties',
            'manage-users',
            'manage-settings',
            'manage-master-data',
            'view-activity-logs',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $clientRole->syncPermissions([
            'view-properties',
        ]);

        $ownerRole = Role::firstOrCreate(['name' => 'owner']);
        $ownerRole->syncPermissions([
            'view-properties',
            'create-properties',
            'edit-own-properties',
            'delete-own-properties',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());
    }
}
