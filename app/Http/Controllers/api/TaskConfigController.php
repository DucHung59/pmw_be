<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use \App\Models\ProjectIssue;
use App\Models\ProjectStatus;
use App\Models\Task;
use App\Models\TaskCategories;
use App\Models\TaskStatuses;

class TaskConfigController extends Controller
{
    //get
    function getProjectIssues(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        // Retrieve all issues for the specified project
        $issues = ProjectCategory::with('category:id,category_type,category_color')
            ->where('project_id', $request->project_id)
            ->orderBy('category_id')
            ->select()
            ->paginate(10);

        return response()->json([
            'message' => 'Issues retrieved successfully',
            'issues' => $issues,
            'success' => true,
        ]);
    }

    function getProjectStatuses(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        // Retrieve all statuses for the specified project
        $statuses = ProjectStatus::with('status:id,status_type,status_color')
            ->where('project_id', $request->project_id)
            ->orderBy('status_id', 'asc')
            ->select()
            ->paginate(10);

        return response()->json([
            'message' => 'Statuses retrieved successfully',
            'statuses' => $statuses,
            'success' => true,
        ]);
    }

    function createProjectIssue(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
            'category_id' => 'required|integer|exists:tblTaskCategories,id',
        ]);

        // Create a new project issue
        $issue = ProjectCategory::create([
            'project_id' => $request->project_id,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Issue created successfully',
            'issue' => $issue,
            'success' => true,
        ]);
    }

    function createProjectStatus(Request $request)
    {
        // Validate the request
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
            'status_id' => 'required|integer|exists:tblTaskCategories,id',
        ]);

        // Create a new project status
        $status = ProjectStatus::create([
            'project_id' => $request->project_id,
            'status_id' => $request->status_id,
        ]);

        return response()->json([
            'message' => 'Status created successfully',
            'status' => $status,
            'success' => true,
        ]);
    }

    function getTaskCategory(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        $usedCategoryIds = ProjectCategory::where('project_id', $request->project_id)
            ->pluck('category_id')
            ->toArray();

        $categories = TaskCategories::whereNotIn('id', $usedCategoryIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Task categories retrieved successfully',
            'categories' => $categories,
            'success' => true,
        ]);
    }

    function getStatusCategory(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        $usedStatusIds = ProjectStatus::where('project_id', $request->project_id)
            ->pluck('status_id')
            ->toArray();

        $statuses = TaskStatuses::whereNotIn('id', $usedStatusIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Task categories retrieved successfully',
            'statuses' => $statuses,
            'success' => true,
        ]);
    }

    function deleteProjectCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:tblTaskCategories,id',
        ]);

        // Find the project category
        $projectCategory = ProjectCategory::where('id', $request->category_id)
            ->first();

        $taskCategory = TaskCategories::where('id', $projectCategory->category_id)
            ->select('id', 'category_type', 'category_color')
            ->first();

        if (in_array($taskCategory->category_type, ['Task', 'Bug', 'Other'])) {
            return response()->json([
                'message' => 'Không thể thao tác với danh mục mặc định: Task, Bug hoặc Other.',
                'success' => false,
            ]);
        }

        if (!$projectCategory) {
            return response()->json([
                'message' => 'Dự án không có danh mục này',
                'success' => false,
            ]);
        }

        $task = Task::where('project_id', $request->project_id)
            ->where('category_type', $request->category_id)
            ->select();

        if ($task->count() > 0) {
            return response()->json([
                'message' => 'Không thể xoá danh mục vì có task liên quan',
                'success' => false,
            ]);
        }

        // Delete the project category
        $projectCategory->delete();

        return response()->json([
            'message' => 'Danh mục ' . $taskCategory->category_type . ' trong dự án đã được xoá thành công',
            'success' => true,
        ]);
    }

    function deleteProjectStatus(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
            'status_id' => 'required|integer|exists:tblTaskCategories,id',
        ]);

        // Find the project status
        $projectStatus = ProjectStatus::where('id', $request->status_id)
            ->first();

        $taskStatus = TaskStatuses::where('id', $projectStatus->status_id)
            ->select('id', 'status_type', 'status_color')
            ->first();

        if (in_array($taskStatus->status_type, ['Open', 'Closed', 'In Progress'])) {
            return response()->json([
                'message' => 'Không thể thao tác với trạng thái mặc định: Open, In Progress hoặc Closed.',
                'success' => false,
            ]);
        }

        if (!$projectStatus) {
            return response()->json([
                'message' => 'Dự án không có trạng thái này',
                'success' => false,
            ]);
        }

        $task = Task::where('project_id', $request->project_id)
            ->where('status', $request->status_id)
            ->select();

        if ($task->count() > 0) {
            return response()->json([
                'message' => 'Không thể xoá trạng thái vì có task liên quan',
                'success' => false,
            ]);
        }

        // Delete the project status
        $projectStatus->delete();

        return response()->json([
            'message' => 'Trạng thái ' . $taskStatus->status_type . ' trong dự án đã được xoá thành công',
            'success' => true,
        ]);
    }
}
