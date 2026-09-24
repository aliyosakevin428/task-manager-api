<?php

namespace App\Http\Controllers\Api;

use App\Actions\Workspaces\EnsureWorkspaceRoles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workspaces\JoinWorkspaceRequest;
use App\Http\Requests\Workspaces\StoreInviteRequest;
use App\Models\Workspace;
use App\Models\WorkspaceInvite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class WorkspaceInviteController extends Controller
{
    public function store(StoreInviteRequest $request, Workspace $workspace, EnsureWorkspaceRoles $ensureRoles)
    {
        $ensureRoles->handle($workspace);

        $plain = 'INV_' . Str::random(40);
        $hash = hash('sha256', $plain);

        $role = $request->input('role', 'member');
        $hours = (int) $request->input('expires_in_hours', 24);

        $invite = WorkspaceInvite::class([
            'workspace_id' => $workspace->id,
            'token_hash' => $hash,
            'role' => $role,
            'expires_at' => now()->addHours($hours),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'data' => [
                'token' => $plain,
                'role' => $role,
                'expires_at' => $invite->expires_at->toDateString(),
            ]
        ]);
    }

    public function join(JoinWorkspaceRequest $request, EnsureWorkspaceRoles $ensureRoles)
    {
        $user = $request->user();
        $hash = hash('sha256', $request->string('token'));

        $result = DB::transaction(function () use ($hash, $user, $ensureRoles) {
            $invite = WorkspaceInvite::lockForUpdate()
                ->where('token_hash', $hash)
                ->first();

            if (!$invite) abort(404, 'Invalid token.');

            if ($invite->used_at) abort(422, 'Token already used.');
            if ($invite->expires_at->isPast()) abort(422, 'Token expired.');

            $workspace = Workspace::findOrFail($invite->workspace_id);

            $alreadyMember = DB::table('workspace_members')
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyMember) abort(409, 'Already a member.');

            DB::table('workspace_members')->insert([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $invite->role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);
            $ensureRoles->handle($workspace);

            $user->assignRole($invite->role);

            $invite->update([
                'used_at' => now(),
                'used_by' => $user->id,
            ]);

            return $workspace;
        });

        return response()->json([
            'message' => 'Joined workspace',
            'data' => [
                'workspace_id' => $result->id,
                'workspace_name' => $result->name,
            ],
        ], 200);
    }

}
