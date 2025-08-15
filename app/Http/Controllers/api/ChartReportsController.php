<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectIssue;
use Illuminate\Http\Request;
use App\Models\ProjectStatus;
use App\Models\Task;

class ChartReportsController extends Controller
{
    //
    function getProjectReports(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer|exists:tblProjects,id',
        ]);

        $projectId = $request->project_id;

        // Lấy toàn bộ task của project
        $tasks = Task::where('project_id', $projectId)
            ->where('is_del', '!=', 9)
            ->get();

        // Lấy tất cả trạng thái định nghĩa
        $statuses = ProjectStatus::with('status:id,status_type,status_color')
            ->orderBy('status_id', 'asc')
            ->where('project_id', $projectId)
            ->get();

        // Đếm theo status_id
        $counts = $statuses->map(function ($status) use ($tasks) {
            $count = $tasks->where('status', $status->status->id)->count();

            return [
                'label' => $status->status->status_type,
                'count' => $count,
                'color' => $status->status->status_color,
            ];
        });

        $total = $counts->sum('count');

        return response()->json([
            'total' => $total,
            'data' => $counts
        ]);
    }

    function getChartDataUser(Request $request)
    {
        $user_id = $request->validate([
            'user_id' => 'required|integer|exists:tblUsers,id',
        ])['user_id'];

        // Lấy tất cả các task của user (không bị xoá)
        $tasks = Task::where('assignee', $user_id)
            ->where('is_del', '!=', 9)
            ->get();

        $allTasks = Task::where('is_del', '!=', 9)->get();
        // Group theo project_id
        $grouped = $tasks->groupBy('project_id');

        $data = [];

        foreach ($grouped as $project_id => $projectTasks) {
            $project = Project::find($project_id);
            if (!$project) continue;

            $statuses = ProjectStatus::with('status:id,status_type,status_color')
                ->orderBy('status_id', 'asc')
                ->where('project_id', $project_id)->get();
            $projectAllTasks = $allTasks->where('project_id', $project_id);

            $userTasks = $projectTasks->where('assignee', $user_id);
            $userTaskCount = $userTasks->count();
            $userTaskPercentage = $projectAllTasks->count() > 0 ? round($userTaskCount / $projectAllTasks->count() * 100, 2) : 0;

            $total = $projectAllTasks->count();

            $statusCounts = $statuses->map(function ($status) use ($projectTasks, $total) {
                $count = $projectTasks->where('status', $status->status->id)->count();

                return [
                    'label' => $status->status->status_type,
                    'value' => $total > 0 ? round($count / $total * 100, 2) : 0,
                    'color' => $status->status->status_color,
                ];
            });

            $data[] = [
                'project_id' => $project_id,
                'project_name' => $project->project_name,
                'statuses' => $statusCounts,
                'total_tasks' => $total,
                'user_task_count' => $userTaskCount,
                'user_task_percentage' => $userTaskPercentage,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
