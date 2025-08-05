<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        string $type,
        int $userId,
        string $description,
        ?int $workspaceId = null,
        ?int $projectId = null,
        ?int $taskId = null
    ): void {
        ActivityLog::create([
            'type' => $type,
            'user_id' => $userId,
            'workspace_id' => $workspaceId,
            'project_id' => $projectId,
            'task_id' => $taskId,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
