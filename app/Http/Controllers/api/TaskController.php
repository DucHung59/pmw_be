<?php

namespace App\Http\Controllers\api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\ProjectStatus;
use App\Models\User;
use App\Models\ProjectIssue;
use App\Models\ProjectMember;
use App\Models\TaskCategories;
use App\Models\TaskStatuses;
use App\Models\WorkspaceMember;
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
            'category_type' => $request->category,
            'assignee' => $request->assignee,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $due_date,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        $task_key = $request->project_key . '-' . $nextTaskNumber;

        if ($task) {
            $task->update(['task_key' => $task_key]);

            $taskComment = TaskComment::create([
                'task_id' => $task->id,
                'comment' => 'Đã tạo mới công việc',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);
            $message = "Đã tạo mới công việc {$task->subject}";
            if ($request->assignee) {
                $message .= "<br>---<br>Người xử lý: " . User::find($request->assignee)->username;
            }
            ActivityLogger::log(
                'add',
                Auth::id(),
                $message,
                ($task->project)->workspace_id,
                $task->project_id,
                $task->id
            );
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
            'categoryInfo:id,category_type,category_color',
            'statusInfo:id,status_type,status_color',
            'creator:id,username',
        ])
            ->where('project_id', $request->project_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('category_type', $request->category))
            ->when($request->assignee, fn($q) => $q->where('assignee', $request->assignee))
            ->when($search, function ($query, $search) {
                return $query->where('subject', 'like', '%' . $search . '%');
            })
            ->when($request->isDelEnable === 'false', fn($q) => $q->where('is_del', '!=', 9))
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

    function getTasksByAssignee(Request $request)
    {
        $task = Task::with([
            'assigneeUser:id,username',
            'categoryInfo:id,category_type,category_color',
            'statusInfo:id,status_type,status_color',
            'creator:id,username',
        ])
            ->where('assignee', Auth::id())
            ->where('is_del', '!=', 9)
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
            'categoryInfo:id,category_type,category_color',
            'statusInfo:id,status_type,status_color',
            'creator:id,username',
        ])
            ->where('created_by', Auth::id())
            ->whereHas('statusInfo', function ($query) {
                $query->where('status_type', '!=', 'closed');
            })
            ->where('is_del', '!=', 9)
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
            'categoryInfo:id,category_type,category_color',
            'statusInfo:id,status_type,status_color',
            'creator:id,username',
        ])
            ->where('task_key', $request->task_key)
            ->first();

        if ($task) {
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
            'comment' => 'nullable|string',
            'status' => 'nullable|integer',
            'assignee' => 'nullable|integer',
        ]);


        $changes = [];
        $task = Task::findOrFail($validated['id']);

        $due_date = $request->due_date ? Carbon::parse($request->due_date)->format('Y-m-d H:i:s') : null;

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        $openStatusId = TaskStatuses::where('status_type', 'Open')
            ->value('id');

        $closedStatusId = TaskStatuses::where('status_type', 'Closed')
            ->value('id');

        $userRole = WorkspaceMember::where('user_id', Auth::id())
            ->value('role');

        $userProjectRole = ProjectMember::where('user_id', Auth::id())
            ->where('project_id', $task->project_id)
            ->value('project_role');

        $isAdminOrManager = in_array($userRole, ['admin']) || in_array($userProjectRole, ['PManager']);

        if (isset($validated['status'])) {
            $newStatus = $request->status;
            $currentStatus = $task->status;

            if ($newStatus == $closedStatusId && !$isAdminOrManager) {
                return response()->json([
                    'message' => 'Chỉ Quản lý hoặc Quản trị hệ thống mới được chuyển trạng thái sang Closed.',
                    'success' => false
                ]);
            }

            // Không cho phép chuyển sang "Open" hoặc "Closed" nếu trạng thái hiện tại không phải "Open" hoặc "Closed", trừ khi là Manager/Admin
            if (in_array($newStatus, [$openStatusId, $closedStatusId]) &&
                !in_array($currentStatus, [$openStatusId, $closedStatusId]) &&
                !$isAdminOrManager) {
                return response()->json([
                    'message' => 'Chỉ Manager hoặc Admin mới được chuyển sang trạng thái Open hoặc Closed từ trạng thái hiện tại.',
                    'success' => false
                ]);
            }

            if ($newStatus != $task->status) {
                $oldStatus = TaskStatuses::find($task->status)?->status_type ?? '';
                $newStatusText = TaskStatuses::find($validated['status'])?->status_type ?? '';
                $task->status = $newStatus;
                $changes[] = "Trạng thái: \"$oldStatus\" → \"$newStatusText\"";
            }
        }

        if (isset($validated['assignee']) && $validated['assignee'] != $task->assignee) {
            $oldUser = User::find($task->assignee)?->username ?? '';
            $newUser = User::find($validated['assignee'])?->username ?? '';
            $task->assignee = $validated['assignee'];
            $changes[] = "Người xử lý: \"$oldUser\" → \"$newUser\"";
        }

        if (isset($due_date) && $due_date != Carbon::parse($task->due_date)->format('Y-m-d H:i:s')) {
            $oldDue = $task->due_date ?? 'Chưa có';
            $newDue = $request->due_date;
            $task->due_date = $due_date;
            $changes[] = "Hạn hoàn thành: $oldDue → $newDue";
        }

        if (!empty($changes)) {
            $task->updated_by = Auth::id();
            $task->save();
            // Log the activity
            ActivityLogger::log(
                'update',
                Auth::id(),
                "Đã cập nhật công việc {$task->subject}: <br>" . implode('<br> ', $changes),
                ($task->project)->workspace_id,
                $task->project_id,
                $task->id
            );
        }

        $finalComment = $validated['comment'];
        if (!empty($changes)) {
            $finalComment = "<br><br>----<br>Cập nhật công việc: <br>" . implode('<br> ', $changes);
        } else {
            return response()->json([
                'message' => 'Không có thay đổi nào để cập nhật',
                'success' => false
            ]);
        }

        $taskComment = TaskComment::create([
            'task_id' => $task->id,
            'comment' => $finalComment,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if ($taskComment) {
            // Log the activity
            ActivityLogger::log(
                'add',
                Auth::id(),
                "Đã thêm bình luận vào công việc {$task->subject}: $finalComment",
                ($task->project)->workspace_id,
                $task->project_id,
                $task->id
            );
        } else {
            return response()->json([
                'message' => 'Failed to add comment',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $taskComment,
            'success' => true
        ]);
    }

    function update(Request $request)
    {
        // Validate the request
        $request->validate([
            'task_id' => 'required|exists:tblTasks,id',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|max:50',
            'category' => 'required|integer|exists:tblTaskCategories,id',
        ]);

        // Find the task and update it
        $task = Task::with([
            'assigneeUser:id,username',
            'categoryInfo:id,category_type,category_color',
            'statusInfo:id,status_type,status_color',
            'creator:id,username',
        ])
            ->where('id', $request->task_id)
            ->where('is_del', '!=', 9)
            ->first();
        if (!$task) {
            return response()->json([
                'message' => 'Không tim thấy công việc',
                'success' => false
            ]);
        }

        if (strtolower(($task->statusInfo)->status_type) == 'closed') {
            return response()->json([
                'message' => 'Không thể cập nhật công việc đã đóng',
                'success' => false
            ]);
        }

        $message = "Đã cập nhật công việc {$task->subject}";
        $changes = [];
        if ($task->category_type != $request->category) {
            $changes[] = "Thay đổi loại công việc: " . ($task->categoryInfo)->category_type . " → " . TaskCategories::find($request->category)->category_type;
        }
        if ($task->subject != $request->subject) {
            $changes[] = "Thay đổi tiêu đề: {$task->subject} → {$request->subject}";
        }
        if ($task->priority != $request->priority) {
            $changes[] = "Chuyển đổi mức ưu tiên: {$task->priority} → {$request->priority}";
        }
        if ($task->description != $request->description) {
            $changes[] = "Cập nhật mô tả: {$request->description}";
        }

        if (!empty($changes)) {
            $message .= "<br>---<br>" . implode('<br>', $changes);
        } else {
            return response()->json([
                'message' => 'Không có thay đổi nào để cập nhật',
                'success' => false
            ]);
        }

        $task->update([
            'subject' => $request->subject,
            'category_type' => $request->category,
            'description' => $request->description,
            'priority' => $request->priority,
            'updated_by' => Auth::id()
        ]);

        // Log the activity
        ActivityLogger::log(
            'update',
            Auth::id(),
            $message,
            ($task->project)->workspace_id,
            $task->project_id,
            $task->id
        );

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
            'success' => true
        ]);
    }

    function softDelete(Request $request)
    {
        // Validate the request
        $request->validate([
            'task_id' => 'required|exists:tblTasks,id',
        ]);

        // Find the task and soft delete it
        $task = Task::where('id', $request->task_id)
            ->where('is_del', '!=', 9)
            ->first();

        if (!$task) {
            return response()->json([
                'message' => 'Task not found',
                'success' => false
            ]);
        }

        $task->is_del = 9;
        $task->save();

        ActivityLogger::log(
            'delete',
            Auth::id(),
            "Đã xóa công việc {$task->subject}",
            ($task->project)->workspace_id,
            $task->project_id,
            $task->id
        );

        return response()->json([
            'message' => 'Task deleted successfully',
            'success' => true
        ]);
    }

    public function getTasksByUser(Request $request)
    {
        // Validate the request
        $request->validate([
            'viewer_id' => 'required|integer|exists:tblUsers,id',
            'workspace_id' => 'required|integer|exists:tblWorkspaces,id',
            'user_id' => 'required|integer|exists:tblUsers,id',
        ]);

        $viewerId = $request->viewer_id;
        $workspaceId = $request->workspace_id;
        $id = $request->user_id;

        $viewerRole = WorkspaceMember::where('workspace_id', $workspaceId)
            ->where('user_id', $viewerId)
            ->value('role');

        $isAdminOrManager = in_array($viewerRole, ['admin']);

        if ($viewerId == $id || $isAdminOrManager) {
            $tasks = Task::with([
                'assigneeUser:id,username',
                'project:id,project_name',
                'categoryInfo:id,category_type,category_color',
                'statusInfo:id,status_type,status_color',
                'creator:id,username',
            ])
                ->where('assignee', $id)
                ->where('is_del', '!=', 9)
                ->select()
                ->paginate(10);
        } else {
            $viewerProjectIds = ProjectMember::where('user_id', $viewerId)
                ->whereHas('project', fn($q) => $q->where('workspace_id', $workspaceId))
                ->pluck('project_id');

            $sharedProjectIds = ProjectMember::where('user_id', $id)
                ->whereIn('project_id', $viewerProjectIds)
                ->pluck('project_id');

            $tasks = Task::with([
                'assigneeUser:id,username',
                'project:id,project_name',
                'categoryInfo:id,category_type,category_color',
                'statusInfo:id,status_type,status_color',
                'creator:id,username',
            ])
                ->where('assignee', $id)
                ->where('is_del', '!=', 9)
                ->whereIn('project_id', $sharedProjectIds)
                ->select()
                ->paginate(10);
        }

        $grouped = $tasks->getCollection()->groupBy(function ($task) {
            return $task->project->project_name ?? 'Không rõ dự án';
        });

        // Đặt lại collection đã group vào paginator
        $tasks->setCollection($grouped);

        return response()->json([
            'message' => 'Tasks retrieved successfully',
            'tasks' => $tasks,
            'success' => true
        ]);
    }
}
