<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\WorkspaceController;
use Illuminate\Support\Facades\Mail;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/signin', [AuthController::class, 'signin']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/signout', [AuthController::class, 'signout'])->middleware('auth:sanctum');
});

Route::prefix('workspace')->group(function () {
    Route::post('/create', [WorkspaceController::class, 'create'])->middleware('auth:sanctum');
    Route::get('/members', [WorkspaceController::class, 'getAllMembers'])->middleware('auth:sanctum');
    Route::post('/invite', [WorkspaceController::class, 'sendInvite'])->middleware('auth:sanctum');
    Route::post('/invite/accept', [WorkspaceController::class, 'acceptInvite'])->middleware('auth:sanctum');
});


