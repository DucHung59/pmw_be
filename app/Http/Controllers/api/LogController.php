<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class LogController extends Controller
{
    public function getLogs(Request $request)
    {
        // Validate the request
        $request->validate([
            'level' => 'nullable|string|in:info,warning,error,debug',
        ]);

        // Retrieve logs based on the level
        $logs = ActivityLog::when($request->level, function ($query) use ($request) {
            return $query->where('level', $request->level);
        })->get();

        return response()->json([
            'message' => 'Logs retrieved successfully',
            'logs' => $logs,
        ]);
    }

    function getLogsByProject(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        // Retrieve logs for the specified project
        $logs = ActivityLog::with(
                'workspace:id,workspace_name',
                'project:id,project_name,project_key',
                'task:id,subject',
                'user:id,username,email',
                )
            ->where('project_id', $request->project_id)
            ->orderBy('created_at', 'desc')
            ->select('*')
            ->paginate($request->perPage ?? 15);

        return response()->json([
            'message' => 'Logs retrieved successfully for project',
            'logs' => $logs,
        ]);
    }

    function getLogsByWorkspace(Request $request)
    {
        // Validate the request
        $request->validate([
            'workspace_id' => 'required|integer|exists:tblWorkspaces,id',
        ]);

        // Retrieve logs for the specified workspace
        $logs = ActivityLog::with(
                'workspace:id,workspace_name',
                'project:id,project_name,project_key',
                'task:id,subject',
                'user:id,username,email',
                )
            ->where('workspace_id', $request->workspace_id)
            ->orderBy('created_at', 'desc')
            ->select('*')
            ->paginate($request->perPage ?? 15);

        return response()->json([
            'message' => 'Logs retrieved successfully for workspace',
            'logs' => $logs,
        ]);
    }

    public function getLogsByUser(Request $request)
    {
        // Validate the request
        $request->validate([
            'user_id' => 'required|integer|exists:tblUsers,id',
            'workspace_id' => 'nullable|integer|exists:tblWorkspaces,id',
        ]);

        // Retrieve logs for the specified user
        $logs = ActivityLog::with(
                'workspace:id,workspace_name',
                'project:id,project_name,project_key',
                'task:id,subject',
                'user:id,username,email',
                )
            ->where('user_id', $request->user_id)
            ->where('workspace_id', $request->workspace_id)
            ->orderBy('created_at', 'desc')
            ->select('*')
            ->paginate($request->perPage ?? 15);

        return response()->json([
            'message' => 'Logs retrieved successfully for user',
            'logs' => $logs,
        ]);
    }
}
