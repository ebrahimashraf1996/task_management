<?php

use App\Http\Controllers\Api\AuditLog\AuditLogController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Task\TaskController;
use App\Http\Controllers\Api\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('register', RegisterController::class);

    Route::post('login', LoginController::class);

});

Route::middleware('auth:api')->group(function () {

    Route::apiResource('users', UserController::class)->except(['show', 'create', 'edit']);

    Route::apiResource('tasks', TaskController::class)->except(['create', 'edit']);

    Route::apiResource('audit-logs', AuditLogController::class)->only(['index']);

});
