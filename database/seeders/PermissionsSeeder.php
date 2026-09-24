<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'workspace.invite',
            'workspace.members.manage',

            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',

            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',

            'comments.view',
            'comments.create',
            'comments.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'sanctum',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
