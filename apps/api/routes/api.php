<?php

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
});
