<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelOrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('orders', TravelOrderController::class)->except(['update', 'destroy']);
    Route::post('/orders/{order}/status', [TravelOrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel', [TravelOrderController::class, 'cancel']);
});