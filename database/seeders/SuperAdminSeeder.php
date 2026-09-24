<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $role = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'sanctum',
            'team_id' => 0,
        ]);

        $role->syncPermissions(Permission::query()->where('guard_name', 'sanctum')->get());

        $admin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Superadmin',
                'password' => Hash::make('password'),
            ]
        );

        $admin->syncRoles([$role]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
