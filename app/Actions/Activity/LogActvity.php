<?php

namespace App\Actions\Activity;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class LogActivity
{
    public function handle(
        int $workspaceId,
        ?int $userId,
        string $action,
        ?Model $subject = null,
        array $metadata = []
    ): ActivityLog {
        return ActivityLog::create([
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata
        ]);
    }
}
