<?php

use App\Modules\Crew\Http\Controllers\CrewMemberController;
use App\Modules\Maintenance\Http\Controllers\MaintenanceTaskController;
use App\Modules\Tenants\Http\Controllers\CurrentTenantController;
use App\Modules\Yachts\Http\Controllers\YachtController;
use App\Support\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/tenant', CurrentTenantController::class)
    ->middleware(['auth:sanctum', 'user.active', 'tenant.context']);

Route::middleware(['auth:sanctum', 'user.active', 'tenant.context'])->group(function () {
    Route::get('/yachts', [YachtController::class, 'index']);
    Route::get('/yacht', [YachtController::class, 'current'])->middleware('yacht.context');
    Route::middleware('yacht.context')->group(function () {
        Route::get('/crew', [CrewMemberController::class, 'index']);
        Route::post('/crew', [CrewMemberController::class, 'store']);
        Route::get('/crew/{crewMember}', [CrewMemberController::class, 'show'])->whereUuid('crewMember');
        Route::patch('/crew/{crewMember}', [CrewMemberController::class, 'update'])->whereUuid('crewMember');
        Route::get('/maintenance', [MaintenanceTaskController::class, 'index']);
        Route::post('/maintenance', [MaintenanceTaskController::class, 'store']);
        Route::get('/maintenance/{maintenanceTask}', [MaintenanceTaskController::class, 'show'])->whereUuid('maintenanceTask');
        Route::patch('/maintenance/{maintenanceTask}', [MaintenanceTaskController::class, 'update'])->whereUuid('maintenanceTask');
        Route::post('/maintenance/{maintenanceTask}/complete', [MaintenanceTaskController::class, 'complete'])->whereUuid('maintenanceTask');
    });
});
