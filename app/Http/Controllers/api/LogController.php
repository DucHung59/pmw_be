<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class LogController extends Controller
{
    //
    public function writeLog(Request $request)
    {
        // Validate the request
        $request->validate([
            'message' => 'required|string|max:255',
            'level' => 'required|string|in:info,warning,error,debug',
        ]);

        // Write the log message
        ActivityLog::{$request->level}($request->message);

        return response()->json([
            'message' => 'Log written successfully',
            'level' => $request->level,
            'log_message' => $request->message,
        ]);
    }

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
}
