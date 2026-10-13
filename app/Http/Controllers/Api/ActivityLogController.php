<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Workspace;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request, Workspace $workspace)
    {
        $logs = ActivityLog::query()
            ->where('workspace_id', $workspace->id)
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return response()->json($logs);
    }
}
