<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GpsController;

// 公開路由
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 受保護路由
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // GPS Tracking Routes
    Route::post('/upload', [GpsController::class, 'upload']);
    Route::get('/tracks', [GpsController::class, 'index']);
    Route::get('/vehicles', [GpsController::class, 'vehicles']);
});
