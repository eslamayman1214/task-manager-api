<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public Auth Endpoints
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth Actions
        Route::post('logout', [AuthController::class, 'logout']);

        // Task Routes
        Route::controller(TaskController::class)->prefix('tasks')->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store')->middleware('throttle:tasks-create');
            Route::patch('{task}/status', 'updateStatus');
            Route::get('search', 'search');
            Route::delete('{task}', 'destroy');
        });
    });
});
