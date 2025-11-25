<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('login/admin', [AuthController::class, 'loginAdmin']);
Route::post('login/kasir', [AuthController::class, 'loginKasir']);
