<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\WorkspaceController;
use App\Http\Controllers\api\ProjectController;
use App\Http\Controllers\api\TaskConfigController;
use App\Http\Controllers\api\TaskController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\api\DocumentController;
use App\Http\Controllers\api\ChartReportsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/workspace', [WorkspaceController::class, 'getWorkspaceByUser'])->middleware('auth:sanctum');
Route::get('/getAllWorkspace', [WorkspaceController::class, 'getAllWorkspaces'])->middleware('auth:sanctum');
Route::get('/getWorkspaceById', [WorkspaceController::class, 'getWorkspaceById'])->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/signin', [AuthController::class, 'signin']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/signout', [AuthController::class, 'signout'])->middleware('auth:sanctum');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
});

Route::prefix('workspace')->group(function () {
    Route::post('/create', [WorkspaceController::class, 'create'])->middleware('auth:sanctum');
    Route::get('/members', [WorkspaceController::class, 'getAllMembers'])->middleware('auth:sanctum');
    Route::post('/change-member-role', [WorkspaceController::class, 'changeMemberRole'])->middleware('auth:sanctum');
    Route::post('/invite', [WorkspaceController::class, 'sendInvite'])->middleware('auth:sanctum');
    Route::post('/invite/accept', [WorkspaceController::class, 'acceptInvite'])->middleware('auth:sanctum');
    Route::get('/invites/get', [WorkspaceController::class, 'getAllInvites'])->middleware('auth:sanctum');
    Route::get('/user-info', [AuthController::class, 'getUserInfo'])->middleware('auth:sanctum');
    Route::get('/get/chart-user', [ChartReportsController::class, 'getChartDataUser'])->middleware('auth:sanctum');
});

Route::prefix('project')->group(function () {
    Route::post('/create', [ProjectController::class, 'create'])->middleware('auth:sanctum');
    Route::get('/get', [ProjectController::class, 'getProjectByUser'])->middleware('auth:sanctum');
    Route::get('/detail', [ProjectController::class, 'getProjectDetail'])->middleware('auth:sanctum');
    Route::post('/addMember', [ProjectController::class, 'addProjectMember'])->middleware('auth:sanctum');
    Route::get('/getAllProjects', [ProjectController::class, 'getAllProjects'])->middleware('auth:sanctum');
    Route::get('/getProjectMembers', [ProjectController::class, 'getProjectMembers'])->middleware('auth:sanctum');
    Route::get('/member-role', [ProjectController::class, 'getMemberRole'])->middleware('auth:sanctum');
    Route::post('/member/delete', [ProjectController::class, 'deleleMember'])->middleware('auth:sanctum');

    Route::post('/document/upload', [DocumentController::class, 'upload'])->middleware('auth:sanctum');
    Route::get('/documents/get', [DocumentController::class, 'getDocumentsByProjectId'])->middleware('auth:sanctum');
    Route::post('/document/delete', [DocumentController::class, 'deleteDocument'])->middleware('auth:sanctum');
    
    Route::get('/getProjectIssues', [TaskConfigController::class, 'getProjectIssues'])->middleware('auth:sanctum');
    Route::get('/getProjectStatuses', [TaskConfigController::class, 'getProjectStatuses'])->middleware('auth:sanctum');
    Route::post('/createProjectIssue', [TaskConfigController::class, 'createProjectIssue'])->middleware('auth:sanctum');
    Route::post('/createProjectStatus', [TaskConfigController::class, 'createProjectStatus'])->middleware('auth:sanctum');
    Route::get('get/task-category', [TaskConfigController::class, 'getTaskCategory'])->middleware('auth:sanctum');
    Route::get('get/task-status', [TaskConfigController::class, 'getStatusCategory'])->middleware('auth:sanctum');
    route::post('delete/project-category', [TaskConfigController::class, 'deleteProjectCategory'])->middleware('auth:sanctum');
    route::post('delete/project-status', [TaskConfigController::class, 'deleteProjectStatus'])->middleware('auth:sanctum');

    Route::get('/get/reports', [ChartReportsController::class, 'getProjectReports'])->middleware('auth:sanctum');
});

Route::prefix('task')->group(function () {
    Route::post('/create', [TaskController::class, 'create'])->middleware('auth:sanctum');
    Route::get('/get', [TaskController::class, 'getTasksByProject'])->middleware('auth:sanctum');
    Route::get('/getByAssignee', [TaskController::class, 'getTasksByAssignee'])->middleware('auth:sanctum');
    Route::get('/getByCreator', [TaskController::class, 'getTasksByCreator'])->middleware('auth:sanctum');
    Route::get('/detail', [TaskController::class, 'getTaskDetail'])->middleware('auth:sanctum');
    Route::post('/addComment', [TaskController::class, 'addComment'])->middleware('auth:sanctum');
    Route::post('/update', [TaskController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/delete', [TaskController::class, 'softDelete'])->middleware('auth:sanctum');
    Route::get('/getByUser', [TaskController::class, 'getTasksByUser'])->middleware('auth:sanctum');
});

Route::prefix('log')->group(function () {
    Route::get('/get', [\App\Http\Controllers\api\LogController::class, 'getLogs'])->middleware('auth:sanctum');
    Route::get('/getByProject', [\App\Http\Controllers\api\LogController::class, 'getLogsByProject'])->middleware('auth:sanctum');
    Route::get('/getByWorkspace', [\App\Http\Controllers\api\LogController::class, 'getLogsByWorkspace'])->middleware('auth:sanctum');
    Route::get('/getByUser', [\App\Http\Controllers\api\LogController::class, 'getLogsByUser'])->middleware('auth:sanctum');
});

