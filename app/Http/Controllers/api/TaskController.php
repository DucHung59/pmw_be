<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskComment;

class TaskController extends Controller
{
    //
    public function create(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
            'subject' => 'required|string|max:255',
            'project_key' => 'required|string|max:50',
        ]);


        // Create a new task
        $task = Task::create([
            'project_id' => $request->project_id,
            'subject' => $request->subject,
            'status' => $request->status,
            'issue_type' => $request->category,
            'assignee' => $request->assignee,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        $task_key = $request->project_key . '-' . $task->id;

        if($task) {
            $task->update(['task_key' => $task_key]);

            $taskComment = TaskComment::create([
                'task_id' => $task->id,
                'comment' => 'đã tạo mới công việc',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to create task',
                'suceess' => false
            ]);
        }

        return response()->json([
            'message' => 'Task created successfully', 
            'task' => $task,
            'comment' => $taskComment,
            'success' => true
        ]);
    }
}
