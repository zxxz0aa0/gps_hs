<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// GPS Tracking Routes
Route::post('/upload', [\App\Http\Controllers\Api\GpsController::class, 'upload']);
Route::get('/tracks', [\App\Http\Controllers\Api\GpsController::class, 'index']);
Route::get('/vehicles', [\App\Http\Controllers\Api\GpsController::class, 'vehicles']);

