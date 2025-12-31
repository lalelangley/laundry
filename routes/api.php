<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaundryOrderController;
use App\Http\Controllers\Api\ProfilePelanggansController;
use App\Http\Controllers\Api\DriverTaskController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('profile')->group(function () {
        Route::get('/{id}', [ProfilePelanggansController::class, 'show']);
        Route::post('/update/{id}', [ProfilePelanggansController::class, 'update']);
        Route::post('/update-gambar/{id}', [ProfilePelanggansController::class, 'updateGambar']);
    });

    Route::prefix('order')->group(function () {
        Route::get('/options', [LaundryOrderController::class, 'getOptions']);
        Route::post('/create', [LaundryOrderController::class, 'createOrder']);
        Route::get('/list', [LaundryOrderController::class, 'getOrders']);
        Route::get('/detail/{id}', [LaundryOrderController::class, 'getOrderDetail']);
    });

    Route::prefix('driver')->group(function () {
        Route::get('/{id_driver}/history', [DriverTaskController::class, 'getDriverHistory']);
        Route::get('/{id_driver}/tasks', [DriverTaskController::class, 'getPendingTasks']); // task list
        Route::post('/accept-task', [DriverTaskController::class, 'acceptTask']);             // accept task
        Route::post('/on-the-way-to-pickup', [DriverTaskController::class, 'onTheWayToPickup']);
        Route::post('/complete-pickup', [DriverTaskController::class, 'completePickup']);
        Route::post('/on-the-way-to-laundry', [DriverTaskController::class, 'onTheWayToLaundry']);
        Route::post('/arrived-at-laundry', [DriverTaskController::class, 'arrivedAtLaundry']);
        Route::post('/on-the-way-to-customer', [DriverTaskController::class, 'onTheWayToCustomer']);
        Route::post('/complete-delivery', [DriverTaskController::class, 'completeDelivery']);
    });

});
