<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\LayananController;
use App\Http\Controllers\Web\SatuanParfumController;
use App\Http\Controllers\Web\TransaksiController;

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

    // DASHBOARD ADMIN
    Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])
        ->name('admin.dashboard');

    // =========================
    // PELANGGAN
    // =========================
    Route::get('/pelanggan', [AuthWebController::class, 'pelangganIndex'])
        ->name('pelanggan.index');

    Route::get('/pelanggan/create', [AuthWebController::class, 'create'])
        ->name('pelanggan.create');

    Route::post('/pelanggan/store', [AuthWebController::class, 'store'])
        ->name('pelanggan.store');

// =========================
// TRANSAKSI
// =========================

// Pilih pelanggan
Route::get('/transaksi/pelanggan', [TransaksiController::class, 'pilihPelanggan'])
    ->name('transaksi.pelanggan');

Route::get('/transaksi/set-pelanggan/{id}', [TransaksiController::class, 'setPelanggan'])
    ->name('transaksi.setPelanggan');


// Halaman create
Route::get('/transaksi/create', [TransaksiController::class, 'create'])
    ->name('transaksi.create');


// Tambah layanan ke keranjang
Route::get('/admin/transaksi/add-layanan/{id}', [TransaksiController::class, 'addLayanan'])
    ->name('transaksi.addLayanan');


// Checkout
Route::post('/transaksi/checkout', [TransaksiController::class, 'checkout'])
    ->name('transaksi.checkout');
Route::post('/admin/transaksi/checkout', [TransaksiController::class, 'checkout'])
     ->name('transaksi.checkout');


// Remove layanan
Route::post('/admin/transaksi/remove/{id}', [TransaksiController::class, 'remove'])
    ->name('transaksi.remove');


// Update keterangan (AJAX)
Route::post('/transaksi/update-keterangan', [TransaksiController::class, 'updateKeterangan'])
    ->name('transaksi.updateKeterangan');


// Reset transaksi
Route::get('/transaksi/reset', function () {
    session()->forget('detail_transaksi');
    session()->forget('pelanggan');
    session()->forget('keterangan_transaksi'); // ⬅ TAMBAHKAN INI
    return redirect()->route('admin.dashboard');
})->name('transaksi.reset');



// Bayar transaksi
Route::post('/transaksi/bayar', [TransaksiController::class, 'bayar'])
    ->name('transaksi.bayar');


// Print struk
Route::get('/transaksi/print/{id}', [TransaksiController::class, 'print'])
    ->name('transaksi.print');



    // =========================
    // LAYANAN
    // =========================

// TAMBAHKAN PARAMETER OPSIONAL "from"
    Route::get('/admin/layanan', [LayananController::class, 'index'])->name('layanan.index');

    Route::get('/transaksi/add-layanan/{id}', [TransaksiController::class, 'addLayanan']);

    Route::get('/layanan', [LayananController::class, 'index'])
        ->name('layanan.index');

    Route::get('/layanan/create', [LayananController::class, 'create'])
        ->name('layanan.create');

    Route::post('/layanan/store', [LayananController::class, 'store'])
        ->name('layanan.store');

    Route::get('/layanan/{id}/edit', [LayananController::class, 'edit'])
        ->name('layanan.edit');

    Route::put('/layanan/{id}', [LayananController::class, 'update'])
        ->name('layanan.update');

    Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])
        ->name('layanan.destroy');

    Route::get('/layanan/{id}/duplicate', [LayananController::class, 'duplicate'])
        ->name('layanan.duplicate');

    // Jenis layanan (session)
    Route::get('/layanan/{from}/jenis/add', [LayananController::class, 'sessionCreateJenis'])
        ->name('session.create');

    Route::post('/layanan/{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenis'])
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
