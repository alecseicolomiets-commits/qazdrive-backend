<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Auth (публичные) ──────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('jwt.auth');
});

// ── Cars (публичные) ──────────────────────────────────────────────────────
Route::get('/cars',      [CarController::class, 'index']);
Route::get('/cars/{id}', [CarController::class, 'show']);

// ── Brands / Stats (публичные) ────────────────────────────────────────────
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/stats',  [StatsController::class, 'index']);

// ── Support (публичные заглушки) ──────────────────────────────────────────
Route::get('/support-messages', fn() => response()->json([]));
Route::post('/support-messages', fn() => response()->json([
    'id'         => rand(1, 1000),
    'message'    => request('message'),
    'created_at' => now(),
], 201));

// ── Защищённые маршруты ───────────────────────────────────────────────────
Route::middleware('jwt.auth')->group(function () {

    // Ставки
    Route::post('/cars/{id}/bid',    [BidController::class, 'store']);

    // Просмотры (для подсчёта)
    Route::post('/cars/{id}/view',   [CarController::class, 'recordView']);

    // Жалобы (авторизованные и гости — см. ниже)
    Route::post('/cars/{id}/report', [CarController::class, 'report']);

    // Создание лота (только seller/admin)
    Route::post('/cars', [CarController::class, 'store']);

    // Загрузка фотографий к лоту (только владелец или admin)
    Route::post('/cars/{id}/images', [CarController::class, 'uploadImages']);

    // Профиль пользователя
    Route::get('/user/profile',   [UserController::class, 'profile']);
    Route::post('/user/profile',  [UserController::class, 'update']);
    Route::post('/user/avatar',   [UserController::class, 'uploadAvatar']);
    Route::get('/user/my-bids',   [UserController::class, 'myBids']);
});

// Жалобы от неавторизованных тоже принимаем
Route::post('/cars/{id}/report', [CarController::class, 'report']);

// ── Админка ───────────────────────────────────────────────────────────────
Route::middleware(['jwt.auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard',                [AdminController::class, 'dashboard']);
    Route::get('/users',                    [AdminController::class, 'users']);
    Route::post('/users/{id}/toggle-block', [AdminController::class, 'toggleBlock']);
    Route::post('/users/{id}/tariff',       [AdminController::class, 'changeTariff']);
    Route::delete('/users/{id}',            [AdminController::class, 'deleteUser']);
    Route::get('/lots',                     [AdminController::class, 'lots']);
    Route::delete('/lots/{id}',             [AdminController::class, 'deleteLot']);
    Route::get('/reports',                  [AdminController::class, 'reports']);
    Route::post('/reports/{id}/resolve',    [AdminController::class, 'resolveReport']);
});