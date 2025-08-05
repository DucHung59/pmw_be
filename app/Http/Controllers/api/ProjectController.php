<?php

namespace App\Http\Controllers\api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectMember;
use App\Models\ProjectIssue;
use App\Models\ProjectStatus;
use App\Models\Task;
use Carbon\Carbon;
use Whoops\Exception\Formatter;

class ProjectController extends Controller
{
    public function create(Request $request)
    {
        // Validate the request
        $request->validate([
            'workspace_id' => 'required|integer|exists:tblWorkspaces,id',
            'project_name' => 'required|string|max:255',
            'project_key' => 'required|string|max:50',
        ]);

        $start_date = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d H:i:s') : null;
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d H:i:s') : null;

        // Create a new project
        $project = Project::create([
            'workspace_id' => $request->workspace_id,
            'project_name' => $request->project_name,
            'project_key' => $request->project_key,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'created_by' => Auth::id(),
        ]);

        //Create the default creator member for the project
        $projectMember = ProjectMember::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'project_role' => 'PManager', // Default role for the creator
        ]);

        $issueTypes = ['Task', 'Bug', 'Request', 'Other'];
        $statusTypes = ['Open', 'In Progress', 'Resolved', 'Closed'];

        foreach ($issueTypes as $issueType) {
            if ($issueType === 'Task') {
                $issueColor = '#f87171'; // Green for Task
            } elseif ($issueType === 'Bug') {
                $issueColor = '#fbbf24'; // Red for Bug
            } elseif ($issueType === 'Request') {
                $issueColor = '#2dd4bf'; // Blue for Request
            } else {
                $issueColor = '#22d3ee'; // Grey for Other
            }
            ProjectIssue::create([
                'project_id' => $project->id,
                'category_type' => $issueType,
                'category_color' => $issueColor,
            ]);
        }

        foreach ($statusTypes as $statusType) {
            if ($statusType === 'Open') {
                $statusColor = '#f87171'; // Red for Open
            } elseif ($statusType === 'In Progress') {
                $statusColor = '#fbbf24'; // Yellow for In Progress
            } elseif ($statusType === 'Resolved') {
                $statusColor = '#22c55e'; // Green for Resolved
            } else {
                $statusColor = '#6b7280'; // Grey for Closed
            }
            ProjectStatus::create([
                'project_id' => $project->id,
                'status_type' => $statusType,
                'status_color' => $statusColor,
            ]);
        }

        if ($project) {
            $message = "Tạo mới dự án {$project->project_name}";
            ActivityLogger::log(
                'add',
                Auth::id(),
                $message,
                $project->workspace_id,
                $project->id,
                null,
            );
        }

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project,
            'project_member' => $projectMember
        ]);
    }

    public function getProjectByUser(Request $request)
    {
        $request->validate([
            'search_condition' => 'nullable|string|max:255',
        ]);

        $search = $request->search_condition ?? '';

        $projects = ProjectMember::with('project:id,project_name,project_key')
            ->where('user_id', Auth::id())
            ->get()
            ->pluck('project')
            ->filter(function ($project) use ($search) {
                return str_contains(strtolower($project->project_name), strtolower($search));
            });


        if ($projects->isEmpty()) {
            return response()->json([
                'message' => 'No projects found for the authenticated user',
                'projects' => [],
            ]);
        }

        return response()->json([
            'message' => 'Projects retrieved successfully',
            'projects' => $projects
        ]);
    }

    public function getAllProjects(Request $request)
    {
        $projects = Project::with('members.user:id,username,email')
            ->paginate(10);

        return response()->json([
            'message' => 'Projects retrieved successfully',
            'projects' => $projects
        ]);
    }

    public function getProjectDetail(Request $request)
    {
        $project = Project::where('project_key', $request->project_key)
            ->with(['members.user:id,username,email'])
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        }

        $projectIssues = ProjectIssue::where('project_id', $project->id)->get();
        $projectStatuses = ProjectStatus::where('project_id', $project->id)->get();

        return response()->json([
            'message' => 'Project details retrieved successfully',
            'project' => $project,
            'issues' => $projectIssues,
            'statuses' => $projectStatuses
        ]);
    }

    public function addProjectMember(Request $request)
    {
        $request->validate([
            'project_key' => 'required|exists:tblProjects,project_key',
            'user_id' => 'required|integer|exists:tblUsers,id',
            'role' => 'required|string|max:50',
        ]);

        $project = Project::where('project_key', $request->project_key)->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found',
                'success' => false
            ]);
        }

        // Check if the user is already a member of the project
        $existingMember = ProjectMember::where('project_id', $project->id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingMember) {
            return response()->json([
                'message' => 'User is already a member of this project',
                'success' => false
            ]);
        }

        // Add the new member to the project
        $newMember = ProjectMember::create([
            'user_id' => $request->user_id,
            'project_id' => $project->id,
            'project_role' => $request->role,
        ]);

        if ($newMember) {
            $message = "Thêm thành viên {$newMember->user->username} vào dự án {$project->project_name}";
            ActivityLogger::log(
                'add',
                Auth::id(),
                $message,
                $project->workspace_id,
                $project->id,
                null,
            );
        }

        return response()->json([
            'message' => 'Project member added successfully',
            'member' => $newMember,
            'success' => true
        ]);
    }

    public function getProjectMembers(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:tblProjectMembers,project_id',
        ]);

        $projectMember = ProjectMember::where('project_id', $request->project_id)
            ->with('user:id,username,email')
            ->select()
            ->paginate(10);

        if (!$projectMember) {
            return response()->json([
                'message' => 'Project not found',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Project members retrieved successfully',
            'members' => $projectMember,
            'success' => true
        ]);
    }

    function getMemberRole(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:tblProjectMembers,project_id',
        ]);

        $memberRole = ProjectMember::where('project_id', $request->project_id)
            ->where('user_id', Auth::id())
            ->value('project_role');

        if (!$memberRole) {
            return response()->json([
                'message' => 'Member not found in the project',
                'success' => false
            ]);
        }

        return response()->json([
            'message' => 'Member role retrieved successfully',
            'role' => $memberRole,
            'success' => true
        ]);
    }
    
    function deleleMember(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:tblProjectMembers,project_id',
            'user_id' => 'required|exists:tblProjectMembers,user_id',
        ]);

        $member = ProjectMember::where('project_id', $request->project_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member not found in the project',
                'success' => false
            ]);
        } else {
            ActivityLogger::log(
                'delete',
                Auth::id(),
                "Xóa thành viên {$member->user->username} khỏi dự án {$member->project->project_name}",
                $member->project->workspace_id,
                $member->project_id,
            );
            
            Task::where('project_id', $member->project_id)
                ->where('assignee', $member->user_id)
                ->update(['assignee' => null]);
        }

        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully',
            'success' => true
        ]);
    }
}
