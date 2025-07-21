<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\ProjectStatus;
use App\Models\User;
use Carbon\Carbon;

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

        $due_date = $request->due_date ? Carbon::parse($request->due_date)->format('Y-m-d H:i:s') : null;
        $taskCountInProject = Task::where('project_id', $request->project_id)->count();
        $nextTaskNumber = $taskCountInProject + 1;

        // Create a new task
        $task = Task::create([
            'project_id' => $request->project_id,
            'subject' => $request->subject,
            'status' => $request->status,
            'issue_type' => $request->category,
            'assignee' => $request->assignee,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $due_date,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        $task_key = $request->project_key . '-' . $nextTaskNumber;

        if($task) {
            $task->update(['task_key' => $task_key]);

            $taskComment = TaskComment::create([
                'task_id' => $task->id,
                'comment' => 'Đã tạo mới công việc',
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

    function getTasksByProject(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        $search = $request->search ?? '';

        // Retrieve the task by ID
        $task = Task::with([
                'assigneeUser:id,username',
                'issueType:id,project_id,issue_type,issue_color',
                'statusInfo:id,project_id,status_type,status_color',
                'creator:id,username',
            ])
            ->where('project_id', $request->project_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('issue_type', $request->category))
            ->when($request->assignee, fn($q) => $q->where('assignee', $request->assignee))
            ->when($search, function ($query, $search) {
                return $query->where('subject', 'like', '%' . $search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->select('*')
            ->paginate(25);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Task retrieved successfully',
            'task' => $task,
            'success' => true
        ]);
    }

    function getTasksByAssignee(Request $request)
    {
        $task = Task::with([
                'assigneeUser:id,username',
                'issueType:id,project_id,issue_type,issue_color',
                'statusInfo:id,project_id,status_type,status_color',
                'creator:id,username',
            ])
            ->where('assignee', Auth::id())
            ->whereHas('statusInfo', function ($query) {
                $query->where('status_type', '!=', 'closed');
            })
            ->orderBy('created_at', 'desc')
            ->select('*')
            ->paginate(10);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Task retrieved successfully',
            'task' => $task,
            'success' => true
        ]);
    }

    function getTasksByCreator(Request $request)
    {
        // Retrieve the task by ID
        $task = Task::with([
                'assigneeUser:id,username',
                'issueType:id,project_id,issue_type,issue_color',
                'statusInfo:id,project_id,status_type,status_color',
                'creator:id,username',
            ])
            ->where('created_by', Auth::id())
            ->whereHas('statusInfo', function ($query) {
                $query->where('status_type', '!=', 'closed');
            })
            ->orderBy('created_at', 'desc')
            ->select('*')
            ->paginate(10);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Task retrieved successfully',
            'task' => $task,
            'success' => true
        ]);
    }

    function getTaskDetail(Request $request)
    {
        $request->validate([
            'task_key' => 'required|exists:tblTasks,task_key',
        ]);

        $task = Task::with([
                'assigneeUser:id,username',
                'issueType:id,project_id,issue_type,issue_color',
                'statusInfo:id,project_id,status_type,status_color',
                'creator:id,username',
            ])
            ->where('task_key', $request->task_key)
            ->first();

        if($task) {
            $taskComments = TaskComment::with('creator:id,username')
                ->where('task_id', $task->id)
                ->orderBy('created_at', 'desc')
                ->select('*')
                ->paginate(10);
        }

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Task retrieved successfully',
            'task' => $task,
            'comments' => $taskComments ?? [],
            'success' => true
        ]);
    }

    function addComment(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:tblTasks,id',
            'comment' => 'required|string|max:1000',
            'status' => 'nullable|integer',
            'assignee' => 'nullable|integer',
            'due_date' => 'nullable|date_format:d-m-Y H:i:s',
        ]);

        
        $changes = [];
        $task = Task::findOrFail($validated['id']);

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        if (isset($validated['status']) && $validated['status'] != $task->status) {
            $oldStatus = ProjectStatus::find($task->status)?->status_type ?? '';
            $newStatus = ProjectStatus::find($validated['status'])?->status_type ?? '';
            $task->status = $validated['status'];
            $changes[] = "Trạng thái: \"$oldStatus\" → \"$newStatus\"";
        }

        if (isset($validated['assignee']) && $validated['assignee'] != $task->assignee) {
            $oldUser = User::find($task->assignee)?->username ?? '';
            $newUser = User::find($validated['assignee'])?->username ?? '';
            $task->assignee = $validated['assignee'];
            $changes[] = "Người xử lý: \"$oldUser\" → \"$newUser\"";
        }

        if (isset($validated['due_date']) && $validated['due_date'] != $task->due_date) {
            $oldDue = $task->due_date ?? 'Chưa có';
            $newDue = $validated['due_date'];
            $task->due_date = $validated['due_date'];
            $changes[] = "Hạn hoàn thành: $oldDue → $newDue";
        }

        if (!empty($changes)) {
            $task->updated_by = Auth::id();
            $task->save();
        }

        $finalComment = $validated['comment'];
        if (!empty($changes)) {
            $finalComment .= "<br>---<br>" . implode("<br>", $changes);
        }

        $taskComment = TaskComment::create([
            'task_id' => $task->id,
            'comment' => $finalComment,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $taskComment,
            'success' => true
        ]);
    }
}
