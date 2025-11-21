<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;

/* 1 Halaman Login */
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');

Route::get('/admin/dashboard', [AuthWebController::class, 'dashboard'])
    ->name('admin.dashboard');

Route::get('/kasir/dashboard', [AuthWebController::class, 'kasirDashboard'])->name('kasir.dashboard');

Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');