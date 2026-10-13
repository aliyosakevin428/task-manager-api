<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function show(Request $request, Workspace $workspace)
    {
        $userId = $request->user()->id;

        $cacheKey = "ws:{$workspace->id}:dashboard:{$userId}";

        $data = Cache::remember($cacheKey, 30, function () use ($workspace, $userId) {

            $projectsCount = Project::where('workspace_id', $workspace->id)->count();

            $tasksTotal = Task::where('workspace_id', $workspace->id)->count();

            $byStatus = Task::query()
                ->selectRaw('status, COUNT(*) as total')
                ->where('workspace_id', $workspace->id)
                ->groupBy('status')
                ->pluck('total', 'status');

            $tasksDueSoon = Task::query()
                ->where('workspace_id', $workspace->id)
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [now()->toDateString(), now()->addDays(5)->toDateString()])
                ->count();

            $myAssigned = Task::query()
                ->where('workspace_id', $workspace->id)
                ->where('assigned_to', $userId)
                ->count();

            return [
                'projects_count' => $projectsCount,
                'tasks_total' => $tasksTotal,
                'tasks_by_status' => [
                    'todo' => (int) ($byStatus['todo'] ?? 0),
                    'in_progress' => (int) ($byStatus['in_progress'] ?? 0),
                    'done' => (int) ($byStatus['done'] ?? 0),
                ],
                'tasks_due_soon' => $tasksDueSoon,
                'my_assigned' => $myAssigned,
            ];
        });

        return response()->json([
            'data' => $data,
            'message' => 'Dashboard data fetched successfully.'
        ], 200);
    }
}
