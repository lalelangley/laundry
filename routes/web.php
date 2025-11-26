<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\LayananController;
use App\Http\Controllers\Web\SatuanParfumController;


// =========================
// LOGIN & LOGOUT
// =========================
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::get('/logout', fn() => redirect('/login'));


// =========================
// ADMIN ROUTES
// =========================
Route::prefix('admin')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])
        ->name('admin.dashboard');

    // =========================
    // LAYANAN UTAMA
    // =========================
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::get('/layanan/create', [LayananController::class, 'create'])->name('layanan.create');
    Route::post('/layanan/store', [LayananController::class, 'store'])->name('layanan.store');

    Route::get('/layanan/{id}/edit', [LayananController::class, 'edit'])->name('layanan.edit');
    Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');

    Route::get('/layanan/{id}/duplicate', [LayananController::class, 'duplicate'])
        ->name('layanan.duplicate');

    // FIX destroy → hapus "admin/"
    Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])
        ->name('layanan.destroy');

    // FIX jenis.edit → hapus "admin/" + pakai id
Route::get('/layanan/jenis/{id_jenis}/edit', [LayananController::class, 'editJenis'])
    ->name('jenis.edit');




    // =========================
    // JENIS LAYANAN DALAM LAYANAN
    // =========================
    Route::get('/layanan/jenis/create', [LayananController::class, 'createJenis'])
        ->name('jenis_layanan.create');

    Route::post('/layanan/jenis/store', [LayananController::class, 'storeJenis'])
        ->name('jenis_layanan.store');

    // Hapus 1 jenis dari SESSION
    Route::post('/layanan/jenis/remove/{index}', function ($index) {
        $items = session()->get('jenis_baru', []);
        unset($items[$index]);
        session()->put('jenis_baru', array_values($items));
        return back();
    })->name('jenis_layanan.remove');

    // Clear semua jenis
    Route::post('/layanan/jenis/clear', function () {
        session()->forget('jenis_baru');
        return back();
    })->name('jenis_layanan.clear');


    // =========================
    // SATUAN PARFUM
    // =========================
    Route::get('/satuan', [SatuanParfumController::class, 'satuanIndex'])->name('satuan.index');
    Route::get('/satuan/create', [SatuanParfumController::class, 'satuanCreate'])->name('satuan.create');
    Route::post('/satuan/store', [SatuanParfumController::class, 'satuanStore'])->name('satuan.store');
    Route::get('/satuan/edit/{id}', [SatuanParfumController::class, 'satuanEdit'])->name('satuan.edit');
    Route::put('/satuan/update/{id}', [SatuanParfumController::class, 'satuanUpdate'])->name('satuan.update');
    Route::delete('/satuan/delete/{id}', [SatuanParfumController::class, 'satuanDestroy'])->name('satuan.destroy');


    // =========================
    // PARFUM
    // =========================
    Route::get('/parfum', [SatuanParfumController::class, 'parfumIndex'])->name('parfum.index');
    Route::get('/parfum/create', [SatuanParfumController::class, 'parfumCreate'])->name('parfum.create');
    Route::post('/parfum/store', [SatuanParfumController::class, 'parfumStore'])->name('parfum.store');
    Route::get('/parfum/{id}/edit', [SatuanParfumController::class, 'parfumEdit'])->name('parfum.edit');
    Route::put('/parfum/{id}', [SatuanParfumController::class, 'parfumUpdate'])->name('parfum.update');
    Route::delete('/parfum/{id}', [SatuanParfumController::class, 'parfumDestroy'])->name('parfum.destroy');

});
