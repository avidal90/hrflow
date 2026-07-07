<?php

use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api-authenticated'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'throttle:api-authenticated'])->group(function (): void {
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('users', UserController::class);
});
