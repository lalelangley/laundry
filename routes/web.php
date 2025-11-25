<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;

/* 1 Halaman Login */


Route::post('/login', [AuthController::class, 'login']);
