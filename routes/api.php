<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaundryOrderController;
use App\Http\Controllers\Api\ProfilePelanggansController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // PROFILE
    Route::get('/profile/{id}', [ProfilePelanggansController::class, 'show']);
    Route::post('/profile/update/{id}', [ProfilePelanggansController::class, 'update']);
    Route::post('/profile/gambar/{id}', [ProfilePelanggansController::class, 'updateGambar']);

    // ORDER
    Route::get('/options', [LaundryOrderController::class, 'getOptions']);
Route::post('/order/create', [LaundryOrderController::class, 'createOrder']);
Route::post('/order/list', [LaundryOrderController::class, 'getOrders']);
Route::get('/order/detail/{id}', [LaundryOrderController::class, 'orderDetail']);
});
