<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectMember;
use App\Models\ProjectIssue;

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

        // Create a new project
        $project = Project::create([
            'workspace_id' => $request->workspace_id,
            'project_name' => $request->project_name,
            'project_key' => $request->project_key,
        ]);

        //Create the default creator member for the project
        $projectMember = ProjectMember::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'project_role' => 'PM', // Default role for the creator
        ]);

        $issueTypes = ['Task', 'Bug', 'Request', 'Other'];

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
                'issue_type' => $issueType,
                'issue_color' => $issueColor,
            ]);
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

    public function getProjectDetail(Request $request)
    {
        $request->validate([
            'project_key' => 'required|exists:tblProjects,project_key',
        ]);

        $project = Project::where('project_key', $request->project_key)
            ->with(['members.user:id,username,email'])
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        }

        $projectIssues = ProjectIssue::where('project_id', $project->id)->get();

        return response()->json([
            'message' => 'Project details retrieved successfully',
            'project' => $project,
            'issues' => $projectIssues,
        ]);
    }

    public function addProjectMember(Request $request) {

    }
}
