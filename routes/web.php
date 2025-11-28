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

// =========================
// ADMIN ROUTES
// =========================
Route::prefix('admin')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])->name('admin.dashboard');

    // =========================
// LAYANAN
// =========================
Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');

// Create layanan
Route::get('/layanan/create', [LayananController::class, 'create'])->name('layanan.create');
// routes/web.php
Route::get('admin/layanan/{from}/jenis/add', [LayananController::class, 'sessionCreateJenis'])
    ->name('session.create');

Route::post('/layanan/store', [LayananController::class, 'store'])->name('layanan.store');

// Edit / Update layanan
Route::get('/layanan/{id}/edit', [LayananController::class, 'edit'])->name('layanan.edit');
Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');

// Delete layanan
Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('layanan.destroy');

// Duplicate layanan
Route::get('/layanan/{id}/duplicate', [LayananController::class, 'duplicate'])->name('layanan.duplicate');

// Tambah jenis baru ke session di edit layanan
Route::post('/layanan/{id}/jenis/add', [LayananController::class, 'addJenisEdit'])
     ->whereNumber('id')
     ->name('layanan.jenis.add.edit');

Route::post('/admin/layanan/{from}/jenis/add-edit', [LayananController::class, 'addJenisEdit'])
     ->name('layanan.jenis.add.edit');

     // Form tambah jenis untuk layanan yang sedang diedit
Route::get('/admin/layanan/{from}/jenis/add', [LayananController::class, 'addJenisSessionForm'])
     ->name('layanan.jenis.add.form');

Route::post('/admin/layanan/{from}/jenis/add', [LayananController::class, 'addJenisEdit'])
    ->name('layanan.jenis.add.edit');

Route::get('/admin/layanan/{from}/jenis/create-session', [LayananController::class, 'sessionCreateJenis'])->name('session.create');


     // Halaman tambah jenis sementara untuk layanan tertentu
Route::get('/admin/layanan/{from}/jenis/create-session', [LayananController::class, 'sessionCreateJenis'])
    ->name('session.create');
    
// Route untuk store jenis layanan sementara (session)
Route::post('admin/layanan/{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenis'])
    ->name('session.store');



// =========================
// JENIS LAYANAN
// =========================

Route::prefix('admin/layanan')->group(function() {
    Route::get('/jenis/{id}/edit', [LayananController::class, 'editJenis'])->name('jenis.edit');
    Route::put('/jenis/{id}', [LayananController::class, 'updateJenis'])->name('layanan.jenis.update');
});

// Create jenis baru untuk layanan yang sudah ada (langsung DB)
Route::get('/layanan/{id_layanan}/jenis/create', [LayananController::class, 'createJenis'])
    ->name('jenis.create');
Route::post('/layanan/{id_layanan}/jenis/store', [LayananController::class, 'storeJenis'])
    ->name('jenis.store');

// Edit / Update jenis yang sudah ada
Route::get('/admin/layanan/jenis/{id_jenis}/edit', [LayananController::class, 'editJenis'])->name('jenis.edit');
Route::put('layanan/jenis/{id_jenis}/id', [LayananController::class, 'updateJenis'])->name('jenis.update');
Route::delete('/layanan/jenis/{id_jenis}/destroy', [LayananController::class, 'destroyJenis'])->name('jenis.destroy');

// =========================
// JENIS LAYANAN SESSION (UNTUK TAMBAH DI EDIT LAYANAN)
// =========================
// FORM tambah jenis session (untuk edit layanan)
Route::get('/layanan/{from}/jenis/create-session', [LayananController::class, 'addJenisSessionForm'])
    ->name('session.create'); // <-- harus 'session.create' supaya Blade valid
Route::post('/layanan/{id}/jenis/add-session', [LayananController::class, 'addJenisEdit'])
    ->name('layanan.jenis.add.edit');

Route::post('/layanan/jenis/remove/{index}', [LayananController::class, 'removeJenisSession'])
    ->name('jenis_layanan.remove');
Route::post('/layanan/jenis/clear-session', [LayananController::class, 'clearJenisSession'])

    ->name('jenis_layanan.clear');


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
