<?php

namespace App\Actions\Workspaces;

use App\Models\Workspace;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;


class EnsureWorkspaceRoles
{
    public function handle(Workspace $workspace): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

        $owner = Role::firstOrCreate([
            'name' => 'owner',
            'guard_name' => 'sanctum',
            'team_id' => $workspace->id
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'sanctum',
            'team_id' => $workspace->id
        ]);

        $member = Role::firstOrCreate([
            'name' => 'member',
            'guard_name' => 'sanctum',
            'team_id' => $workspace->id
        ]);

        $all = Permission::where('guard_name', 'sanctum')->get();

        $owner->syncPermissions($all);

        $admin->syncPermissions(Permission::whereIn('name', [
            'workspace.invite',
            'workspace.members.manage',
            'projects.view','projects.create','projects.update','projects.delete',
            'tasks.view','tasks.create','tasks.update','tasks.delete',
            'comments.view','comments.create','comments.delete',
        ])->get());

        $member->syncPermissions(Permission::whereIn('name', [
            'projects.view',
            'tasks.view','tasks.create','tasks.update',
            'comments.view','comments.create',
        ])->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
