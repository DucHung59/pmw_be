<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\ProjectIssue;
use App\Models\ProjectStatus;

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
        $issues = ProjectIssue::where('project_id', $request->project_id)
            ->orderBy('created_at', 'desc')
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
        $statuses = ProjectStatus::where('project_id', $request->project_id)
            ->orderBy('created_at', 'desc')
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
            'category_type' => 'required|string|max:255',
            'category_color' => 'nullable|string|max:7',
        ]);

        // Create a new project issue
        $issue = ProjectIssue::create([
            'project_id' => $request->project_id,
            'category_type' => $request->category_type,
            'category_color' => $request->category_color ?? '#007fe0',
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
            'status_type' => 'required|string|max:255',
            'status_color' => 'nullable|string|max:7',
        ]);

        // Create a new project status
        $status = ProjectStatus::create([
            'project_id' => $request->project_id,
            'status_type' => $request->status_type,
            'status_color' => $request->status_color ?? '#007fe0',
        ]);

        return response()->json([
            'message' => 'Status created successfully',
            'status' => $status,
            'success' => true,
        ]);
    }

    function updateProjectIssue(Request $request)
    {
        // Validate the request
        $request->validate([
            'category_id' => 'required|integer|exists:tblTaskCategory,id',
            'category_type' => 'required|string|max:255',
            'category_color' => 'nullable|string|max:7',
        ]);

        // Find the issue and update it
        $issue = ProjectIssue::find($request->category_id);
        $issue->update([
            'category_type' => $request->category_type,
            'category_color' => $request->category_color ?? '#007fe0',
        ]);

        return response()->json([
            'message' => 'Issue updated successfully',
            'issue' => $issue,
            'success' => true,
        ]);
    }

    function updateProjectStatus(Request $request)
    {
        // Validate the request
        $request->validate([
            'status_id' => 'required|integer|exists:tblTaskStatuses,id',
            'status_type' => 'required|string|max:255',
            'status_color' => 'nullable|string|max:7',
        ]);

        // Find the status and update it
        $status = ProjectStatus::find($request->status_id);
        $status->update([
            'status_type' => $request->status_type,
            'status_color' => $request->status_color ?? '#007fe0',
        ]);

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $status,
            'success' => true,
        ]);
    }
}
