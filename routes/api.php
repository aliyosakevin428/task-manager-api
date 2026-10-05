<?php

use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\WorkspaceInviteController;
use App\Http\Controllers\Api\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('/workspaces', [WorkspaceController::class, 'index']);
        Route::post('/workspaces', [WorkspaceController::class, 'store']);
        Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show']);

        Route::post('/workspace/join', [WorkspaceInviteController::class, 'join']);
        Route::prefix('workspaces/{workspace}')
            ->middleware(['workspace'])
            ->scopeBindings()
            ->group(function () {
                Route::get('/members', [WorkspaceMemberController::class, 'index']);
                Route::post('/invites', [WorkspaceInviteController::class, 'store'])
                    ->middleware('permission:workspace.invite,sanctum');

                Route::get('/projects', [ProjectController::class, 'index'])
                    ->middleware('permission:projects.view,sanctum');

                Route::post('/projects', [ProjectController::class, 'store'])
                    ->middleware('permission:projects.create,sanctum');

                Route::get('/projects/{project}', [ProjectController::class, 'show'])
                    ->middleware('permission:projects.view,sanctum');

                Route::put('/projects/{project}', [ProjectController::class, 'update'])
                    ->middleware('permission:projects.update,sanctum');

                Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
                    ->middleware('permission:projects.delete,sanctum');

                Route::get('tasks', [TaskController::class, 'index'])
                    ->middleware('permission:tasks.view,sanctum');

                Route::post('tasks', [TaskController::class, 'store'])
                    ->middleware('permission:tasks.create,sanctum');

                Route::get('tasks/{task}', [TaskController::class, 'show'])
                    ->middleware('permission:tasks.view,sanctum');

                Route::put('tasks/{task}', [TaskController::class, 'update'])
                    ->middleware('permission:tasks.update,sanctum');

                Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
                    ->middleware('permission:tasks.delete,sanctum');
            });

    });
});

Route::middleware(['auth:sanctum','admin.team', 'role:superadmin,sanctum'])
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('users', UserController::class);
    });

