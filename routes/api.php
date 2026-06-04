<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
});

// Cars routes (public)
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{id}', [CarController::class, 'show']);

// Brands (public)
Route::get('/brands', [BrandController::class, 'index']);

// Stats (public)
Route::get('/stats', [StatsController::class, 'index']);

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    // Bids
    Route::post('/cars/{id}/bid', [BidController::class, 'store']);

    // User
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'update']);
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);
    Route::get('/user/my-bids', [UserController::class, 'myBids']);
});
// Support messages (заглушка)
Route::get('/support-messages', function () {
    return response()->json([]);
});

Route::post('/support-messages', function () {
    return response()->json([
        'id' => rand(1, 1000),
        'message' => request('message'),
        'created_at' => now(),
    ], 201);
});