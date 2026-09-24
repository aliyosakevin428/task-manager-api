<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $workspace = Workspace::query()
            ->whereHas('members', fn($q) => $q->where('users.id', $user->id))
            ->orderByDesc('id')
            ->paginate(15);

        return WorkspaceResource::collection($workspace);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkspaceRequest $request)
    {
        $user = $request->user();

        $workspace = DB::transaction(function () use ($request, $user) {
            $workspace = Workspace::create([
                'name'=> $request->string('name'),
                'owner_id' => $user->id,
            ]);

            DB::table('workspace_members')->insert([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);

            Role::firstOrCreate([
                'name' => 'owner',
                'guard_name' => 'sanctum',
                'team_id' => $workspace->id
            ]);
            Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'sanctum',
                'team_id' => $workspace->id
            ]);
            Role::firstOrCreate([
                'name' => 'member',
                'guard_name' => 'sanctum',
                'team_id' => $workspace->id
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $user->assignRole('owner');

            return $workspace;

        });

        return (new WorkspaceResource($workspace))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Workspace $workspace)
    {
        $isMember = $workspace->members()
            ->where('users.id', $request->user()->id)
            ->exists();

        abort_unless($isMember, 403);

        return new WorkspaceResource($workspace);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
