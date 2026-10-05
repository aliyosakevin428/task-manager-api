<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Workspace $workspace, Project $project)
    {
        $tasks = Task::query()
            ->where('workspace_id', $workspace->id)
            ->where('project_id', $project->id)
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->query('status'))
            )
            ->when($request->filled('assigned_to'), fn ($q) =>
                $q->where('assigned_to', $request->query('assigned_to'))
            )
            ->when($request->filled('q'), fn ($q) =>
                $q->where('title', 'like', '%' . $request->query('q') . '%')
            );

        $sort = $request->query('sort', '-id');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        $allowedSorts = ['id', 'due_date', 'created_by', 'priority', 'status'];
        if (!in_array($column, $allowedSorts, true)) {
            $column = 'id';
            $direction = 'desc';
        }

        $tasks = $tasks
            ->orderBy($column, $direction)
            ->paginate($request->query('per_page', 15))
            ->withQueryString();

        return TaskResource::collection($tasks);
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
    public function store(StoreTaskRequest $request, Workspace $workspace, Project $project)
    {
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'title' => $request->string('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'todo'),
            'priority' => $request->input('priority', 2),
            'due_date' => $request->input('due_date'),
            'created_by' => $request->user()->id,
            'assigned_to' => $request->input('assigned_to'),
        ]);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Workspace $workspace, Project $project, Task $task)
    {
        return new TaskResource($task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workspace $workspace, Project $project, Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Workspace $workspace, Project $project, Task $task)
    {
        $task->fill($request->validated());
        $task->save();

        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace, Project $project, Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }
}
