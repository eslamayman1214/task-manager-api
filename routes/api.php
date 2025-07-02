<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('tasks', [TaskController::class, 'store'])->middleware('auth:sanctum');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->middleware('auth:sanctum');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->middleware('auth:sanctum');
});
