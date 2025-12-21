<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\LayananController;
use App\Http\Controllers\Web\SatuanParfumController;
use App\Http\Controllers\Web\TransaksiController;
use App\Http\Controllers\Web\LaporanController;
use App\Http\Controllers\Web\RiwayatController;
use App\Http\Controllers\Web\UserManagerController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');

Route::post('/logout', function () {
    Auth::guard('admin')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth:admin')->group(function () {

// Super Admin
Route::get('admin/dashboard', [AuthWebController::class, 'adminDashboard'])
    ->middleware('auth:admin')
    ->name('admin.dashboard');

// Admin Biasa
Route::get('admin2/dashboard', [AuthWebController::class, 'admin2Dashboard'])
    ->middleware('auth:admin')
    ->name('admin2.dashboard');

// Kasir
Route::get('kasir/dashboard', [AuthWebController::class, 'kasirDashboard'])
    ->middleware('auth:kasir')
    ->name('kasir.dashboard');

    // ===== USER MANAGER =====
    Route::get('/manager', [UserManagerController::class, 'index'])->name('manager.index');

    // ================= PELANGGAN =================
    Route::get('/pelanggan', [AuthWebController::class, 'pelangganIndex'])->name('pelanggan.index');
    Route::get('/pelanggan/create', [AuthWebController::class, 'create'])->name('pelanggan.create');
    Route::post('/pelanggan/store', [AuthWebController::class, 'store'])->name('pelanggan.store');
    Route::get('/pelanggan/{id}/edit', [AuthWebController::class, 'pelangganEdit'])->name('pelanggan.edit');
    Route::put('/pelanggan/{id}', [AuthWebController::class, 'pelangganUpdate'])->name('pelanggan.update');
    Route::delete('/pelanggan/{id}', [AuthWebController::class, 'pelangganDestroy'])->name('pelanggan.destroy');

    // ================= TRANSAKSI =================
    Route::get('/transaksi/pelanggan', [TransaksiController::class, 'pilihPelanggan'])->name('transaksi.pelanggan');
    Route::get('/transaksi/set-pelanggan/{id}', [TransaksiController::class, 'setPelanggan'])->name('transaksi.setPelanggan');
    Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::post('/transaksi/add-layanan/{id}', [TransaksiController::class, 'addLayanan'])->name('transaksi.addLayanan');
    Route::post('/transaksi/temp-store-layanan', [TransaksiController::class, 'tempStoreLayanan'])->name('transaksi.temp_store_layanan');
    Route::post('/transaksi/update-keterangan', [TransaksiController::class, 'updateKeterangan'])->name('transaksi.updateKeterangan');
    Route::post('/transaksi/checkout', [TransaksiController::class, 'checkout'])->name('transaksi.checkout');
    Route::post('/transaksi/bayar', [TransaksiController::class, 'bayar'])->name('transaksi.bayar');
    Route::get('/transaksi/print/{id}', [TransaksiController::class, 'print'])->name('transaksi.print');
    Route::post('/transaksi/remove/{id}', [TransaksiController::class, 'remove'])->name('transaksi.remove');
    Route::get('/transaksi/reset', function () {
        session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
        return redirect()->route('admin.dashboard');
    })->name('transaksi.reset');

    // ================= LAYANAN =================
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::get('/layanan/create', [LayananController::class, 'create'])->name('layanan.create');
    Route::post('/layanan/store', [LayananController::class, 'store'])->name('layanan.store');
    Route::get('/layanan/{id}/edit', [LayananController::class, 'edit'])->name('layanan.edit');
    Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');
    Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('layanan.destroy');
    Route::get('/layanan/{id}/duplicate', [LayananController::class, 'duplicate'])->name('layanan.duplicate');

    // Jenis layanan
    Route::get('/layanan/{from}/jenis/add', [LayananController::class, 'sessionCreateJenis'])->name('session.create');
    Route::post('/layanan/{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenis'])->name('session.store');
    Route::get('/layanan/{id_layanan}/jenis/create', [LayananController::class, 'createJenis'])->name('jenis.create');
    Route::post('/layanan/{id_layanan}/jenis/store', [LayananController::class, 'storeJenis'])->name('jenis.store');
    Route::get('/layanan/jenis/{id_jenis}/edit', [LayananController::class, 'editJenis'])->name('layanan.jenis.edit');
    Route::put('/layanan/jenis/{id}', [LayananController::class, 'updateJenis'])->name('layanan.jenis.update');
    Route::delete('/layanan/jenis/{id_jenis}/destroy', [LayananController::class, 'destroyJenis'])->name('jenis.destroy');

    // ================= RIWAYAT =================
    Route::prefix('riwayat')->group(function () {
        Route::get('/', [RiwayatController::class, 'index'])->name('riwayat.index');
        Route::get('/{id}/detail', [RiwayatController::class, 'detail'])->name('riwayat.detail');
        Route::get('/{id}/show', [RiwayatController::class, 'show'])->name('riwayat.show');
        Route::get('/{id}/edit', [RiwayatController::class, 'edit'])->name('riwayat.edit');
        Route::put('/{id}', [RiwayatController::class, 'update'])->name('riwayat.update');
        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrder'])->name('riwayat.proses');
        Route::get('/{id}/batal', [RiwayatController::class, 'batalOrder'])->name('riwayat.batal');
        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrder'])->name('riwayat.selesai');
        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbil'])->name('riwayat.siap_di_ambil');

        // Layanan dalam riwayat
        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPage'])->name('riwayat.add_layanan_page');
        Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayanan'])->name('riwayat.add_layanan_store');
        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarSubmit'])->name('riwayat.bayar.submit');
        Route::put('/layanan/{id}', [RiwayatController::class, 'updateLayanan'])->name('riwayat.update_layanan');
        Route::delete('/layanan/{id}', [RiwayatController::class, 'destroyLayanan'])->name('riwayat.layanan.destroy');
        Route::get('/layanan/{id}/edit', [RiwayatController::class, 'editLayanan'])->name('riwayat.edit_layanan');
        Route::put('detail/{id}/update', [RiwayatController::class, 'updateDetail'])->name('riwayat.update_detail');
        Route::delete('/admin/riwayat/detail/{id}', [RiwayatController::class, 'deleteDetail'])->name('riwayat.delete_detail');
        Route::delete('/{id}', [RiwayatController::class, 'destroy'])->name('riwayat.destroy');
    });

    // ================= SATUAN & PARFUM =================
    Route::get('/satuan', [SatuanParfumController::class, 'satuanIndex'])->name('satuan.index');
    Route::get('/satuan/create', [SatuanParfumController::class, 'satuanCreate'])->name('satuan.create');
    Route::post('/satuan/store', [SatuanParfumController::class, 'satuanStore'])->name('satuan.store');
    Route::get('/satuan/{id}/edit', [SatuanParfumController::class, 'satuanEdit'])->name('satuan.edit');
    Route::put('/satuan/{id}', [SatuanParfumController::class, 'satuanUpdate'])->name('satuan.update');
    Route::delete('/satuan/{id}', [SatuanParfumController::class, 'satuanDestroy'])->name('satuan.destroy');

    Route::get('/parfum', [SatuanParfumController::class, 'parfumIndex'])->name('parfum.index');
    Route::get('/parfum/create', [SatuanParfumController::class, 'parfumCreate'])->name('parfum.create');
    Route::post('/parfum/store', [SatuanParfumController::class, 'parfumStore'])->name('parfum.store');
    Route::get('/parfum/{id}/edit', [SatuanParfumController::class, 'parfumEdit'])->name('parfum.edit');
    Route::put('/parfum/{id}', [SatuanParfumController::class, 'parfumUpdate'])->name('parfum.update');
    Route::delete('/parfum/{id}', [SatuanParfumController::class, 'parfumDestroy'])->name('parfum.destroy');

    // ================= PENGELUARAN =================
    Route::prefix('pengeluaran')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('pengeluaran.index');
        Route::get('/create', [LaporanController::class, 'create'])->name('pengeluaran.create');
        Route::post('/', [LaporanController::class, 'store'])->name('pengeluaran.store');
        Route::get('/{id}/edit', [LaporanController::class, 'edit'])->name('pengeluaran.edit');
        Route::put('/{id}', [LaporanController::class, 'update'])->name('pengeluaran.update');
        Route::delete('/{id}', [LaporanController::class, 'destroy'])->name('pengeluaran.destroy');
    });

    // ================= LAPORAN =================
    Route::get('/laporan', [LaporanController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('laporan/transaksi/index', [LaporanController::class, 'transaksiIndex'])->name('laporan.transaksi.index');
    Route::get('laporan/kasir/index', [LaporanController::class, 'kasirIndex'])->name('laporan.kasir.index');
    Route::get('laporan/bayar/index', [LaporanController::class, 'bayarIndex'])->name('laporan.bayar.index');
    Route::get('laporan/pengeluaran/index', [LaporanController::class, 'pengeluaranIndex'])->name('laporan.pengeluaran.index');
    Route::get('laporan/satuan/index', [LaporanController::class, 'satuanIndex'])->name('laporan.satuan.index');
    Route::get('laporan/pelanggan/index', [LaporanController::class, 'pelangganIndex'])->name('laporan.pelanggan.index');

    // ================= USER MANAGER DETAIL =================
    Route::get('/manager/akses/{user_type}/{user_id}', [UserManagerController::class, 'aksesUser'])->name('manager.akses');
    Route::post('/manager/admin', [AuthWebController::class, 'storeAdmin'])->name('manager.admin.store');
    Route::post('/manager/kasir', [UserManagerController::class, 'storeKasir'])->name('manager.kasir.store');
    Route::post('/manager/permission/user', [UserManagerController::class, 'saveUserPermission'])->name('manager.permission.save.user');
    Route::get('/manager/menu-role/create', [UserManagerController::class, 'create'])->name('manager.create');
    Route::post('/manager/menu-role', [UserManagerController::class, 'store'])->name('manager.store');
    Route::put('/manager/menu-role/{id}', [UserManagerController::class, 'update'])->name('manager.update');
    Route::delete('/manager/menu-role/{id}', [UserManagerController::class, 'destroy'])->name('manager.destroy');
    Route::get('/manager/role/hak-akses', [UserManagerController::class, 'hakRole'])->name('manager.role.hak');
    Route::post('/manager/role/hak-akses', [UserManagerController::class, 'saveHakRole'])->name('manager.role.hak.save');
    Route::get('/manager/admin/create', function () {
        $roles = \App\Models\Role::all();
        return view('manager.admin.create', compact('roles'));
    })->name('manager.admin.create');
    Route::get('/manager/kasir/create', function () {
        return view('manager.kasir.create');
    })->name('manager.kasir.create');
    Route::post('/manager/status', [UserManagerController::class, 'updateStatus'])->name('manager.update.status');
});
