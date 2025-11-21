<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Web\AuthWebController;

// Login Admin (POST)
Route::post('login/admin', [AuthController::class, 'loginAdmin']);

// Login Kasir (POST)
Route::post('login/kasir', [AuthController::class, 'loginKasir']);

Route::get('/login', [AuthWebController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');