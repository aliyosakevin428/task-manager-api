<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreTaskCommentRequest;
use App\Http\Requests\Comments\UpdateTaskCommentRequest;
use App\Http\Resources\TaskCommentResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Workspace;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        $comments = $task->comments()
            ->with('user:id,name,email')
            ->orderBy('id')
            ->get();

        return TaskCommentResource::collection($comments);
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
    public function store(StoreTaskCommentRequest $request, Workspace $workspace, Project $project, Task $task)
    {
        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->input('body'),
        ]);

        $comment->load('user:id,name,email');

        return (new TaskCommentResource($comment))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(UpdateTaskCommentRequest $request, Workspace $workspace, Project $project, Task $task, TaskComment $comment)
    {
        $user = $request->user();

        $isAuthor = $comment->user_id === $user->id;

        $isModerator = $user->hasRole('admin') || $user->hasRole('owner');

        if (!$isAuthor && !$isModerator) {
            abort(403, 'You are not authorized to update this comment.');
        }

        $comment->update([
            'body' => $request->input('body'),
        ]);

        $comment->load('user:id,name,email');

        return (new TaskCommentResource($comment))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Workspace $workspace, Project $project, Task $task, TaskComment $comment)
    {
        $user = $request->user();

        $isAuthor = $comment->user_id === $user->id;
        $isModerator = $user->hasRole('admin') || $user->hasRole('owner');

        if (!$isAuthor && !$isModerator) {
            abort(403, 'You are not authorized to delete this comment.');
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.'
        ], 200);
    }
}
