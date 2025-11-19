<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\BlackoutPeriodApiController;

Route::apiResource('users', UserApiController::class);
Route::apiResource('departments', DepartmentApiController::class);
Route::apiResource('leaves', LeaveApiController::class);

Route::post('leaves/{leave}/approve', [LeaveApiController::class, 'approve']);
Route::post('leaves/{leave}/reject', [LeaveApiController::class, 'reject']);

Route::apiResource('blackouts', BlackoutPeriodApiController::class);
