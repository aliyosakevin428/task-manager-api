<?php

use App\Models\User;
use App\Models\Workspace;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;


beforeEach(function () {
    $this->seed(PermissionsSeeder::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function makeWorkspaceWithMember(User $user, string $role): Workspace {
    $workpsace = Workspace::factory()->create([
        'owner_id' => $user->id,
    ]);

    DB::table('workspace_members')->insert([
        'workspace_id' => $workpsace->id,
        'user_id' => $user->id,
        'role' => $role,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($workpsace->id);
    $user->assignRole($role);

    return $workpsace;
}

it('owner can create invite token', function () {
    $owner = User::factory()->create();
    $workspace = makeWorkspaceWithMember($owner, 'owner');

    Sanctum::actingAs($owner);

    $res = $this->postJson("/api/workspaces/{$workspace->id}/invites", [
        'role' => 'member',
        'expires_in_hours' => 24,
    ]);

    $res->assertStatus(201)
        ->assertJsonStatus([
            'data' => [
                'token',
                'role',
                'expires_at',
            ]
        ]);

    $token = $res->json('data.token');
    expect($token)->toStartWith('INV_');

    $this->assertDatabaseCount('workspace_invites', 1);
    $invite = DB::table('workspace_invites')->first();

    expect($invite->workspace_id)->toBe($workspace->id);
    expect($invite->created_by)->toBe($owner->id);
    expect($invite->used_at)->toBeNull();

});

it('member cannot create invite token', function () {
    $member = User::factory()->create();

    $workspace = makeWorkspaceWithMember($member, 'member');

    Sanctum::actingAs($member);

    $this->postJson("/api/workspaces/{$workspace->id}/invites", [
        'role' => 'member',
    ])->assertStatus(403);

});

it('user can join workspace with token only once', function () {
    $owner = User::factory()->create();
    $workspace = makeWorkspaceWithMember($owner, 'owner');

    Sanctum::actingAs($owner);

    $inviteRes = $this->postJson("/api/workspaces/{$workspace->id}/invites", [
        'role' => 'member',
        'expires_in_hours' => 24,
    ])->assertStatus(201);

    $token = $inviteRes->json('data.token');

    $u1 = User::factory()->create();
    Sanctum::actingAs($u1);

    $this->postJson('/api/workspaces/join', [
        'token' => $token,
    ])->assertOk();

    $this->assertDataBaseHas('workspace_members', [
        'workspace_id' => $workspace->id,
        'user_id' => $u1->id,
        'role' => 'member',
    ]);

    $inviteRow = DB::table('workspace_invites')->first();
    expect($inviteRow->used_at)->not()->toBeNull();
    expect($inviteRow->used_by)->toBe($u1->id);

    $u2 = User::factory()->create();
    Sanctum::actingAs($u2);

    $this->postJson('/api/workspaces/join', [
        'token' => $token,
    ])->assertStatus(422);

});

it('members endpoint includes joined user', function () {
    $owner = User::factory()->create();
    $workspace = makeWorkspaceWithMember($owner, 'owner');

    Sanctum::actingAs($owner);
    $inviteRes = $this->postJson("/api/workspaces/{$workspace->id}/invites", [
        'role' => 'member',
    ])->assertStatus(201);

    $token = $inviteRes->json('data.token');

    $member = User::factory()->create();
    Sanctum::actingAs($member);
    $this->postJson('/api/workspaces/join', [
        'token' => $token,
    ])->assertOk();

    Sanctum::actingAs($owner);

    $res = $this->getJson("/api/workspaces/{$workspace->id}/members")
        ->assertOk();

    $ids = collect($res->json('data'))->pluck('id');

    expect($ids)->toContain($owner->id);
    expect($ids)->toContain($member->id);
});
