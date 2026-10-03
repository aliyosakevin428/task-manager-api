<?php

namespace Database\Seeders;

use App\Actions\Workspaces\EnsureWorkspaceRoles;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DemoWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Demo',
                'password' => Hash::make('password'),
            ]
        );

        $workspace = Workspace::factory()->create([
            'name' => 'Demo Workspace',
            'owner_id' => $user->id,
        ]);

        DB::table('workspace_members')->updateOrInsert(
            [
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->call(PermissionsSeeder::class);

        app(EnsureWorkspaceRoles::class)->handle($workspace);

        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $ownerRole = Role::where('team_id', $workspace->id)
            ->where('name', 'owner')
            ->where('guard_name', 'sanctum'
            )->firstOrFail();

        $user->syncRoles($ownerRole);
    }
}
