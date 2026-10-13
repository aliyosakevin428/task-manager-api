<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attachments\StoreAttachmentRequest;
use App\Http\Resources\TaskAttachmentResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Workspace $workspace, Project $project, Task $task)
    {
        $attachments = $task->attachments()
            ->with('user:id,name,email')
            ->orderBy('id')
            ->get();

        return TaskAttachmentResource::collection($attachments);
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
    public function store(StoreAttachmentRequest $request, Workspace $workspace, Project $project, Task $task)
    {
        $file = $request->file('file');

        $path = $file->store("attachments/workspaces/{$workspace->id}/tasks/{$task->id}", 'local');

        $attachment = $task->attachments()->create([
            'user_id' => $request->user()->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        $attachment->load('user:id,name,email');

        return (new TaskAttachmentResource($attachment))
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

    public function download(Workspace $workspace, Project $project, Task $task, TaskAttachment $attachment)
    {
       $disk = Storage::disk($attachment->disk);

       abort_unless($disk->exists($attachment->path), 404);

       $absolutePath = $disk->path($attachment->path);

       return response()->download($absolutePath, $attachment->original_name);
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
    public function destroy(Workspace $workspace, Project $project, Task $task, TaskAttachment $attachment)
    {
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => 'Attachment deleted successfully.'], 200);
    }
}
