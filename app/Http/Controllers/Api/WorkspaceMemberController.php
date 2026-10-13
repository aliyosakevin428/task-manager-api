<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspaces\UpdateWorkspaceMemberRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class WorkspaceMemberController extends Controller
{
    public function index(Request $request, Workspace $workspace)
    {
        $members = $workspace->members()
            ->select('users.id', 'users.name', 'users.email')
            ->withPivot(['role'])
            ->orderBy('users.name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->pivot->role
            ]);

        return response()->json([
            'data' => [
                'members' => $members,
            ]
        ]);

    }

    public function update(UpdateWorkspaceMemberRequest $request, Workspace $workspace, User $member)
    {
        abort_if($member->id === $workspace->owner_id, 403, 'Cannot change the role of the workspace owner.');

        $newRole = $request->input('role');

        $membership = DB::table('workspace_members')
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $member->id)
            ->first();

        abort_unless((bool)$membership, 404, 'User is not a member of this workspace.');

        DB::transaction(function () use ($workspace, $member, $newRole) {
            DB::table('workspace_members')
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $member->id)
                ->update([
                    'role' => $newRole,
                    'updated_at' => now(),
                ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

            $member->syncRoles([$newRole]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        return response()->json([
            'message' => 'Member role updated successfully.',
            'data' => [
                'user_id' => $member->id,
                'workspace_id' => $workspace->id,
                'role' => (string) $newRole,
            ],
        ], 200);
    }

    public function destroy(Request $request, Workspace $workspace, User $member)
    {
        abort_if($member->id === $workspace->owner_id, 403, 'Cannot remove the workspace owner.');

        $exists = DB::table('workspace_members')
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $member->id)
            ->exists();

        abort_unless($exists, 404, 'User is not a member of this workspace.');

        DB::transaction(function () use ($workspace, $member) {
            DB::table('workspace_members')
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $member->id)
                ->delete();

            app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

            $member->syncRoles([]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        return response()->json([
            'message' => 'Member removed successfully.',
            'data' => [
                'user_id' => $member->id,
                'workspace_id' => $workspace->id,
            ],
        ], 200);
    }
}
