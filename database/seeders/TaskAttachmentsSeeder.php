<?php

namespace Database\Seeders;

use App\Actions\Workspaces\EnsureWorkspaceRoles;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class TaskAttachmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('email', 'user@example.com')->firstOrFail(); // user yang sudah ada

        $member = User::firstOrCreate(
            ['email' => 'member.attach@example.com'],
            ['name' => 'Member Attach', 'password' => bcrypt('password123')]
        );

        $workspace = Workspace::firstOrCreate(
            ['name' => 'Demo Workspace Attachments 2'],
            ['owner_id' => $owner->id]
        );

        DB::table('workspace_members')->updateOrInsert(
            ['workspace_id' => $workspace->id, 'user_id' => $owner->id],
            ['role' => 'owner', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('workspace_members')->updateOrInsert(
            ['workspace_id' => $workspace->id, 'user_id' => $member->id],
            ['role' => 'member', 'created_at' => now(), 'updated_at' => now()]
        );

        $this->call(PermissionsSeeder::class);

        app(EnsureWorkspaceRoles::class)->handle($workspace);

        app(PermissionRegistrar::class)->setPermissionsTeamId($workspace->id);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner->syncRoles(['owner']);
        $member->syncRoles(['member']);

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Project Attachments Demo 2',
        ]);

        $tasks = Task::factory()->count(3)->create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assigned_to' => $member->id,
        ]);

        foreach ($tasks as $task) {
            $originalName = "task-{$task->id}-attachment.txt";
            $path = "attachments/workspaces/{$workspace->id}/tasks/{$task->id}/".Str::uuid()."-{$originalName}";

            Storage::disk('local')->put($path, "Demo attachment for task #{$task->id}\n");
            $size = Storage::disk('local')->size($path);

            TaskAttachment::create([
                'task_id' => $task->id,
                'user_id' => $owner->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $originalName,
                'mime_type' => 'text/plain',
                'size' => $size,
            ]);
        }

    }
}
