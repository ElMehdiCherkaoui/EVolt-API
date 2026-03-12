<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\StationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/reservations', [ReservationController::class, 'index']);
});
Route::get('/stations', [StationController::class, 'index']);
Route::Post('/stations/search', [StationController::class, 'search']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/stations', [StationController::class, 'store']);
    Route::put('/stations/{id}', [StationController::class, 'update']);
    Route::delete('/stations/{id}', [StationController::class, 'destroy']);
    Route::get('/stations/stats', [StationController::class, 'stats']);
});

Route::middleware('auth:sanctum')->group(function(){

Route::get('/reservations/{id}', [ReservationController::class, 'show']);
Route::post('/reservations', [ReservationController::class, 'store']);
Route::put('/reservations/{id}', [ReservationController::class, 'update']);
Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
Route::patch('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);
Route::get('/my-reservations', [ReservationController::class, 'myHistory']);
});