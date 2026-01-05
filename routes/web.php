<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\LayananController;
use App\Http\Controllers\Web\SatuanParfumController;
use App\Http\Controllers\Web\TransaksiController;
use App\Http\Controllers\Web\LaporanController;
use App\Http\Controllers\Web\RiwayatController;
use App\Http\Controllers\Web\UserManagerController;
use App\Http\Controllers\Web\PengaturanController;
use App\Http\Controllers\Web\ChangePasswordController;
use App\Http\Controllers\Web\PesananOnlineController;
use App\Models\Satuan;

// Route untuk assign driver pickup
Route::post('/pesanan-online/{id}/assign-driver-pickup', [PesananOnlineController::class, 'assignDriverPickup'])
    ->name('pesanan.online.assign-driver-pickup');


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES - Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');

Route::post('/logout', function (): RedirectResponse {
    Auth::guard('admin')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::post(
    'kasir/riwayat/{id}/add-layanan',
    [RiwayatController::class, 'addLayananKasir']
)->middleware('permission:layanan,add');


Route::prefix('kasir')->middleware('auth:kasir')->group(function () {

    // ================= DASHBOARD (No Permission) =================
    Route::get('/dashboard', [AuthWebController::class, 'kasirDashboard'])
        ->name('kasir.dashboard');

    Route::post('/kasir/logout', function () {
        Auth::guard('kasir')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/kasir/login');
    })->name('kasir.logout');

    // ================= TRANSAKSI (Minimal Permission) =================
    Route::get('/transaksi/pelanggan', [TransaksiController::class, 'pelangganKasir'])
        ->name('kasir.transaksi.pelanggan');

    // ========================================
    // TRANSAKSI (Minimal Permission)
    // ========================================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        Route::get('/pelanggan', [TransaksiController::class, 'pelangganKasir'])->name('pelanggan');
        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelangganKasir'])->name('setPelanggan');
        Route::get('/create', [TransaksiController::class, 'createKasir'])->name('create');
        Route::get('/checkout', [TransaksiController::class, 'checkoutKasir'])->name('checkout');
        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayananKasir'])->name('addLayanan');
        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayananKasir'])->name('temp_store_layanan');
        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeteranganKasir'])->name('updateKeterangan');
        Route::post('/bayar', [TransaksiController::class, 'bayarKasir'])->name('bayar');
        Route::get('/print/{id}', [TransaksiController::class, 'printKasir'])->name('print');
        Route::post('/remove/{id}', [TransaksiController::class, 'removeKasir'])->name('remove');
        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenisKasir'])->name('addJenis');
        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
            return redirect()->route('kasir.dashboard');
        })->name('reset');
    });

    Route::get('/transaksi/create', [TransaksiController::class, 'createKasir'])
        ->name('kasir.transaksi.create');

    Route::get('/transaksi/checkout', [TransaksiController::class, 'checkoutKasir'])
        ->name('kasir.transaksi.checkout');

    Route::post('/transaksi/add-layanan/{id}', [TransaksiController::class, 'addLayananKasir'])
        ->name('kasir.transaksi.addLayanan');

    Route::post('/transaksi/temp-store-layanan', [TransaksiController::class, 'tempStoreLayananKasir'])
        ->name('kasir.transaksi.temp_store_layanan');

    Route::post('/transaksi/update-keterangan', [TransaksiController::class, 'updateKeteranganKasir'])
        ->name('kasir.transaksi.updateKeterangan');

    Route::post('/transaksi/bayar', [TransaksiController::class, 'bayarKasir'])
        ->name('kasir.transaksi.bayar');

    Route::get('/transaksi/print/{id}', [TransaksiController::class, 'printKasir'])
        ->name('kasir.transaksi.print');

    Route::post('/transaksi/remove/{id}', [TransaksiController::class, 'removeKasir'])
        ->name('kasir.transaksi.remove');

    Route::get('/transaksi/reset', function () {
        session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
        return redirect()->route('kasir.dashboard');
    })->name('kasir.transaksi.reset');

    Route::post('/transaksi/add-jenis/{id}', [TransaksiController::class, 'addJenisKasir'])
        ->name('kasir.transaksi.addJenis');

    // ================= PELANGGAN (With Permission) =================
    Route::get('/pelanggan', [AuthWebController::class, 'pelangganIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.pelanggan.index');

    Route::get('/pelanggan/create', [AuthWebController::class, 'createKasir'])
        ->middleware('permission:add')
        ->name('kasir.pelanggan.create');

    Route::post('/pelanggan/store', [AuthWebController::class, 'storeKasir'])
        ->middleware('permission:add')
        ->name('kasir.pelanggan.store');

    Route::get('/pelanggan/{id}/edit', [AuthWebController::class, 'pelangganEditKasir'])
        ->middleware('permission:edit')
        ->name('kasir.pelanggan.edit');

    Route::put('/pelanggan/{id}', [AuthWebController::class, 'pelangganUpdateKasir'])
        ->middleware('permission:edit')
        ->name('kasir.pelanggan.update');

    Route::delete('/pelanggan/{id}', [AuthWebController::class, 'pelangganDestroyKasir'])
        ->middleware('permission:delete')
        ->name('kasir.pelanggan.destroy');

    // ================= LAYANAN (With Permission) =================
    Route::get('/layanan', [LayananController::class, 'index'])
        ->middleware('permission:view')
        ->defaults('from', 'transaksi')
        ->name('kasir.layanan.index');

    Route::get('/layanan/create', [LayananController::class, 'createKasir'])
        ->middleware('permission:add')
        ->name('kasir.layanan.create');

    Route::get('/layanan/{from}/create', [LayananController::class, 'layananCreateKasir'])
        ->middleware('permission:add')
        ->whereIn('from', ['transaksi','dashboard'])
        ->name('kasir.layanan.layanan_create');

    Route::post('/layanan/store', [LayananController::class, 'storeKasir'])
        ->middleware('permission:add')
        ->name('kasir.layanan.store');

    Route::get('/layanan/{id}/edit', [LayananController::class, 'edit'])
        ->middleware('permission:edit')
        ->whereNumber('id')
        ->name('kasir.layanan.edit');

    Route::put('/layanan/{id}', [LayananController::class, 'update'])
        ->middleware('permission:edit')
        ->whereNumber('id')
        ->name('kasir.layanan.update');

    Route::delete('/layanan/{id}', [LayananController::class, 'destroyKasir'])
        ->middleware('permission:delete')
        ->whereNumber('id')
        ->name('kasir.layanan.destroy');

    Route::get('/layanan/{id}/duplicate', [LayananController::class, 'duplicateKasir'])
        ->middleware('permission:add')
        ->whereNumber('id')
        ->name('kasir.layanan.duplicate');

    // ================= JENIS LAYANAN (With Permission) =================
    Route::prefix('layanan')->group(function () {
        Route::get('{from}/jenis/add', [LayananController::class, 'sessionCreateJenisKasir'])
            ->middleware('permission:add')
            ->name('kasir.session.create') 
            ->whereNumber('from');

        Route::post('{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenisKasir'])
            ->middleware('permission:add')
            ->name('kasir.session.store');

        Route::get('{id_layanan}/jenis/create', [LayananController::class, 'createJenisKasir'])
            ->middleware('permission:add')
            ->name('kasir.jenis.create')
            ->whereNumber('id_layanan');

        Route::post('{id_layanan}/jenis/store', [LayananController::class, 'storeJenisKasir'])
            ->middleware('permission:add')
            ->name('kasir.jenis.store')
            ->whereNumber('id_layanan');

        Route::get('jenis/{id}/edit', [LayananController::class, 'editJenisKasir'])
            ->middleware('permission:edit')
            ->name('kasir.layanan.jenis.edit');

        Route::post('{id_layanan}/jenis/store', [LayananController::class, 'storeJenisKasir'])
            ->middleware('permission:add')
            ->name('kasir.layanan.jenis.store');

        Route::post('{from}/jenis/add-edit-kasir', [LayananController::class, 'addJenisEdit'])
            ->middleware('permission:edit')
            ->name('kasir.layanan.jenis.add.edit');
    });

    Route::get('/layanan/{layanan}/jenis/tambah', function ($layanan) {
        $satuan = Satuan::all();
        return view('kasir.layanan.tambah_jenis_layanan_create', [
            'from'       => $layanan,
            'id_layanan' => $layanan,
            'satuan'     => $satuan,
        ]);
    })->middleware('permission:add')->name('kasir.layanan.jenis.tambah');

    // ================= PESANAN ONLINE (With Permission) =================
    Route::prefix('pesanan-online')->group(function () {
        Route::get('/', [PesananOnlineController::class, 'indexKasir'])
            ->middleware('permission:view')
            ->name('kasir.pesanan.online.index');

        Route::get('/{id}/detail', [PesananOnlineController::class, 'detailKasir'])
            ->middleware('permission:view')
            ->name('kasir.pesanan.online.detail');

        Route::get('/{id}/terima', [PesananOnlineController::class, 'terimaKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pesanan.online.terima');

        Route::get('/{id}/tolak', [PesananOnlineController::class, 'tolakKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pesanan.online.tolak');

        Route::get('/{id}/proses', [PesananOnlineController::class, 'prosesKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pesanan.online.proses');

        Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbilKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pesanan.online.siap_di_ambil');

        Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesaiKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pesanan.online.selesai');

        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayarKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pesanan.online.bayar');

        Route::delete('/{id}', [PesananOnlineController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->name('kasir.pesanan.online.destroy');

            Route::get('/{id}/pickup/driver', [PesananOnlineController::class, 'listDriverPickup'])
    ->name('pesanan.online.pickup.driver');

    Route::post('/{id}/pickup/assign-driver', [PesananOnlineController::class, 'assignDriverPickup'])
    ->name('pesanan.online.pickup.assign');

    });

    // ================= RIWAYAT (With Permission) =================
    Route::prefix('riwayat')->group(function () {
        Route::get('/', [RiwayatController::class, 'indexKasir'])
            ->middleware('permission:view')
            ->name('kasir.riwayat.index');

        Route::get('/{id}/detail', [RiwayatController::class, 'detailKasir'])
            ->middleware('permission:view')
            ->name('kasir.riwayat.detail');

        Route::get('/{id}/show', [RiwayatController::class, 'showKasir'])
            ->middleware('permission:view')
            ->name('kasir.riwayat.show');

        Route::get('/{id}/edit', [RiwayatController::class, 'editKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.edit');

        Route::put('/{id}', [RiwayatController::class, 'updateKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.update');

        Route::delete('/{id}', [RiwayatController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->name('kasir.riwayat.destroy');

        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrderKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.proses');

        Route::get('/{id}/batal', [RiwayatController::class, 'batalOrderKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.batal');

        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrderKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.selesai');

        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbilKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.siap_di_ambil');

        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.bayar');

        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPage'])
            ->middleware('permission:add')
            ->name('kasir.riwayat.addlayanan');

        Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayananKasir'])
            ->middleware('permission:add')
            ->name('kasir.riwayat.storelayanan');

        Route::post('/detail/{id}/update', [RiwayatController::class, 'updateDetail'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.updateDetail');

        Route::delete('/detail/{id}', [RiwayatController::class, 'deleteDetail'])
            ->middleware('permission:delete')
            ->name('kasir.riwayat.deleteDetail');

        Route::post('/kasir/riwayat/{id}/bayar', [App\Http\Controllers\Web\RiwayatController::class, 'bayarKasir'])
            ->middleware('permission:edit')
            ->name('kasir.riwayat.bayar.submit');
    });

    // ================= SATUAN (With Permission) =================
    Route::prefix('satuan')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'satuanIndexKasir'])
            ->middleware('permission:view')
            ->name('kasir.satuan.index');

        Route::get('/create', [SatuanParfumController::class, 'satuanCreateKasir'])
            ->middleware('permission:add')
            ->name('kasir.satuan.create');

        Route::post('/store', [SatuanParfumController::class, 'satuanStoreKasir'])
            ->middleware('permission:add')
            ->name('kasir.satuan.store');

        Route::get('/{id}/edit', [SatuanParfumController::class, 'satuanEditKasir'])
            ->middleware('permission:edit')
            ->name('kasir.satuan.edit');

        Route::put('/{id}', [SatuanParfumController::class, 'satuanUpdateKasir'])
            ->middleware('permission:edit')
            ->name('kasir.satuan.update');

        Route::delete('/{id}', [SatuanParfumController::class, 'satuanDestroyKasir'])
            ->middleware('permission:delete')
            ->name('kasir.satuan.destroy');
    });

    // ================= PARFUM (With Permission) =================
    Route::prefix('parfum')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'parfumIndexKasir'])
            ->middleware('permission:view')
            ->name('kasir.parfum.index');

        Route::get('/create', [SatuanParfumController::class, 'parfumCreateKasir'])
            ->middleware('permission:add')
            ->name('kasir.parfum.create');

        Route::post('/store', [SatuanParfumController::class, 'parfumStoreKasir'])
            ->middleware('permission:add')
            ->name('kasir.parfum.store');

        Route::get('/{id}/edit', [SatuanParfumController::class, 'parfumEditKasir'])
            ->middleware('permission:edit')
            ->name('kasir.parfum.edit');

        Route::put('/{id}', [SatuanParfumController::class, 'parfumUpdateKasir'])
            ->middleware('permission:edit')
            ->name('kasir.parfum.update');

        Route::delete('/{id}', [SatuanParfumController::class, 'parfumDestroyKasir'])
            ->middleware('permission:delete')
            ->name('kasir.parfum.destroy');
    });

    // ================= PENGELUARAN (With Permission) =================
    Route::prefix('pengeluaran')->group(function () {
        Route::get('/', [LaporanController::class, 'indexKasir'])
            ->middleware('permission:view')
            ->name('kasir.pengeluaran.index');

        Route::get('/create', [LaporanController::class, 'createKasir'])
            ->middleware('permission:add')
            ->name('kasir.pengeluaran.create');

        Route::post('/', [LaporanController::class, 'storeKasir'])
            ->middleware('permission:add')
            ->name('kasir.pengeluaran.store');

        Route::get('/{id}/edit', [LaporanController::class, 'editKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pengeluaran.edit');

        Route::put('/{id}', [LaporanController::class, 'updateKasir'])
            ->middleware('permission:edit')
            ->name('kasir.pengeluaran.update');

        Route::delete('/{id}', [LaporanController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->name('kasir.pengeluaran.destroy');
    });

    // ================= PENGATURAN (No Permission - Everyone Can Access) =================
    Route::prefix('pengaturan')->group(function () {
        Route::get('/', [PengaturanController::class, 'indexKasir'])
            ->name('kasir.pengaturan.index');

        Route::post('/update', [PengaturanController::class, 'updateKasir'])
            ->name('kasir.pengaturan.update');

        Route::post('/omzet', [PengaturanController::class, 'updateOmzetKasir'])
            ->name('kasir.pengaturan.omzet');

        Route::post('/backup', [PengaturanController::class, 'backupKasir'])
            ->name('kasir.pengaturan.backup');

        Route::post('/restore', [PengaturanController::class, 'restoreKasir'])
            ->name('kasir.pengaturan.restore');

        Route::get('/metode-bayar', [PengaturanController::class, 'metodeBayarKasir'])
            ->name('kasir.pengaturan.metode');

        Route::post('/metode-bayar/store', [PengaturanController::class, 'storeMetodeBayarKasir'])
            ->name('kasir.pengaturan.metode.store');

        Route::delete('/metode-bayar/{id}', [PengaturanController::class, 'deleteMetodeBayarKasir'])
            ->name('kasir.pengaturan.metode.delete');
    });

    // ================= LAPORAN (View Permission Only) =================
    Route::get('/laporan', [LaporanController::class, 'laporanIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.index');

    Route::get('laporan/transaksi/index', [LaporanController::class, 'transaksiIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.transaksi.index');

    Route::get('laporan/kasir/index', [LaporanController::class, 'kasirIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.kasir.index');

    Route::get('laporan/bayar/index', [LaporanController::class, 'bayarIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.bayar.index');

    Route::get('laporan/pengeluaran/index', [LaporanController::class, 'pengeluaranIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.pengeluaran.index');

    Route::get('laporan/satuan/index', [LaporanController::class, 'satuanIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.satuan.index');

    Route::get('laporan/pelanggan/index', [LaporanController::class, 'pelangganIndexKasir'])
        ->middleware('permission:view')
        ->name('kasir.laporan.pelanggan.index');

    // ================= CHANGE PASSWORD (No Permission) =================
    Route::get('/change-password', [ChangePasswordController::class, 'indexKasir'])
        ->name('kasir.change.password');

    Route::post('/change-password', [ChangePasswordController::class, 'updateKasir'])
        ->name('kasir.password.update');
});
/*
|--------------------------------------------------------------------------
| ADMIN AREA (Super Admin & Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth:admin')->group(function () {

        Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])
            ->name('admin.dashboard');

    // ================= USER MANAGER (Super Admin Only - Role Check) =================
    Route::prefix('manager')->name('manager.')
        ->middleware('superadmin')  // <- Cleaner approach
        ->group(function () {
        Route::get('/', [UserManagerController::class, 'index'])->name('index');
        Route::get('/akses/{user_type}/{user_id}', [UserManagerController::class, 'aksesUser'])->name('akses');
        Route::post('/admin', [AuthWebController::class, 'storeAdmin'])->name('admin.store');
        Route::post('/kasir', [UserManagerController::class, 'storeKasir'])->name('kasir.store');
        Route::post('/permission/user', [UserManagerController::class, 'saveUserPermission'])->name('permission.save.user');
        Route::get('/menu-role/create', [UserManagerController::class, 'create'])->name('create');
        Route::post('/menu-role', [UserManagerController::class, 'store'])->name('store');
        Route::put('/menu-role/{id}', [UserManagerController::class, 'update'])->name('update');
        Route::delete('/menu-role/{id}', [UserManagerController::class, 'destroy'])->name('destroy');
        Route::get('/role/hak-akses', [UserManagerController::class, 'hakRole'])->name('role.hak');
        Route::post('/role/hak-akses', [UserManagerController::class, 'saveHakRole'])->name('role.hak.save');
        Route::get('/admin/create', function () {
            $roles = \App\Models\Role::all();
            return view('manager.admin.create', compact('roles'));
        })->name('admin.create');
        Route::get('/kasir/create', function () {
            return view('manager.kasir.create');
        })->name('kasir.create');
        Route::post('/status', [UserManagerController::class, 'updateStatus'])->name('update.status');
    });

    // ================= PELANGGAN (With Permission) =================
    Route::prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::get('/', [AuthWebController::class, 'pelangganIndex'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/create', [AuthWebController::class, 'create'])
            ->middleware('permission:add')
            ->name('create');
        
        Route::post('/store', [AuthWebController::class, 'store'])
            ->middleware('permission:add')
            ->name('store');
        
        Route::get('/{id}/edit', [AuthWebController::class, 'pelangganEdit'])
            ->middleware('permission:edit')
            ->name('edit');
        
        Route::put('/{id}', [AuthWebController::class, 'pelangganUpdate'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::delete('/{id}', [AuthWebController::class, 'pelangganDestroy'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= TRANSAKSI (Minimal Permission) =================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        // Core transaksi - all admin can access
        Route::get('/pelanggan', [TransaksiController::class, 'pilihPelanggan'])->name('pelanggan');
        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelanggan'])->name('setPelanggan');
        Route::get('/create', [TransaksiController::class, 'create'])->name('create');
        Route::post('/checkout', [TransaksiController::class, 'checkout'])->name('checkout');
        Route::post('/bayar', [TransaksiController::class, 'bayar'])->name('bayar');
        Route::get('/print/{id}', [TransaksiController::class, 'print'])->name('print');
        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayanan'])->name('temp_store_layanan');
        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeterangan'])->name('updateKeterangan');
        
        // Edit/Delete - need permission
        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayanan'])
            ->middleware('permission:edit')
            ->name('addLayanan');
        
        Route::post('/remove/{id}', [TransaksiController::class, 'remove'])
            ->middleware('permission:delete')
            ->name('remove');
        
        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenis'])
            ->name('addJenis');
        
        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
            return redirect()->route('admin.dashboard');
        })->name('reset');
    });

    // ================= LAYANAN (With Permission) =================
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/', [LayananController::class, 'index'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/create', [LayananController::class, 'create'])
            ->middleware('permission:add')
            ->name('create');
        
        Route::post('/store', [LayananController::class, 'store'])
            ->middleware('permission:add')
            ->name('store');
        
        Route::get('/{id}/edit', [LayananController::class, 'edit'])
            ->middleware('permission:edit')
            ->name('edit');
        
        Route::put('/{id}', [LayananController::class, 'update'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::delete('/{id}', [LayananController::class, 'destroy'])
            ->middleware('permission:delete')
            ->name('destroy');
        
        Route::get('/{id}/duplicate', [LayananController::class, 'duplicate'])
            ->middleware('permission:add')
            ->name('duplicate');
        
        // Jenis Layanan
        Route::get('/{from}/jenis/add', [LayananController::class, 'sessionCreateJenis'])
            ->middleware('permission:add')
            ->name('session.create'); // Changed from session.create to layanan.session.create for clarity
        
        Route::post('/{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenis'])
            ->middleware('permission:add')
            ->name('session.store');
        
        Route::get('/{id_layanan}/jenis/create', [LayananController::class, 'createJenis'])
            ->middleware('permission:add')
            ->name('jenis.create');
        
        Route::post('/{id_layanan}/jenis/store', [LayananController::class, 'storeJenis'])
            ->middleware('permission:add')
            ->name('jenis.store');
        
        Route::get('/jenis/{id_jenis}/edit', [LayananController::class, 'editJenis'])
            ->middleware('permission:edit')
            ->name('jenis.edit');
        
        Route::put('/jenis/{id}', [LayananController::class, 'updateJenis'])
            ->middleware('permission:edit')
            ->name('jenis.update');
        
        Route::delete('/jenis/{id_jenis}/destroy', [LayananController::class, 'destroyJenis'])
            ->middleware('permission:delete')
            ->name('jenis.destroy');
        
        Route::post('/{from}/jenis/add-edit', [LayananController::class, 'addJenisEdit'])
            ->middleware('permission:edit')
            ->name('jenis.add.edit');
    });

  // ================= PESANAN ONLINE (With Permission) =================
Route::prefix('pesanan-online')->name('pesanan.online.')->group(function () {
    
    // Index & Detail
    Route::get('/', [PesananOnlineController::class, 'index'])
        ->middleware('permission:view')
        ->name('index');
    
    Route::get('/{id}/detail', [PesananOnlineController::class, 'detail'])
        ->middleware('permission:view')
        ->name('detail');
    
    // 🔥 UPDATE DATA PESANAN (ISI DATA)
    Route::put('/{id}/update-data', [PesananOnlineController::class, 'updateData'])
        ->middleware('permission:edit')
        ->name('updateData');
    
    // 🔥 KONFIRMASI PESANAN (KIRIM WA/SMS)
    Route::post('/{id}/konfirmasi', [PesananOnlineController::class, 'konfirmasiPesanan'])
        ->middleware('permission:edit')
        ->name('konfirmasi');
    
    // Status Management - PAKAI GET (karena pakai <a href> di view)
    Route::get('/{id}/proses', [PesananOnlineController::class, 'proses'])
        ->middleware('permission:edit')
        ->name('proses');
    
    Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesai'])
        ->middleware('permission:edit')
        ->name('selesai');
    
    Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbil'])
        ->middleware('permission:edit')
        ->name('siap_di_ambil');
    
    // Payment & Delete
    Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayar'])
        ->middleware('permission:edit')
        ->name('bayar');
    
    Route::delete('/{id}', [PesananOnlineController::class, 'destroy'])
        ->middleware('permission:delete')
        ->name('destroy');
    
    // Delivery
    Route::get('/delivery', [PesananOnlineController::class, 'listDeliveryOnline'])
        ->name('delivery');
    
    Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriver'])->name('assign-driver'); // ✅ TAMBAH INI
    
    Route::get('/{id}/list-driver', [PesananOnlineController::class, 'listDriver'])->name('list-driver'); // ✅ TAMBAH INI
});

    // ================= RIWAYAT (With Permission) =================
    Route::prefix('riwayat')->name('riwayat.')->group(function () {
        Route::get('/', [RiwayatController::class, 'index'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/{id}/detail', [RiwayatController::class, 'detail'])
            ->middleware('permission:view')
            ->name('detail');
        
        Route::get('/{id}/show', [RiwayatController::class, 'show'])
            ->middleware('permission:view')
            ->name('show');
        
        Route::get('/{id}/edit', [RiwayatController::class, 'edit'])
            ->middleware('permission:edit')
            ->name('edit');
        
        Route::put('/{id}', [RiwayatController::class, 'update'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::delete('/{id}', [RiwayatController::class, 'destroy'])
            ->middleware('permission:delete')
            ->name('destroy');
        
        // Status changes - need edit permission
        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrder'])
            ->middleware('permission:edit')
            ->name('proses');
        
        Route::patch('/{id}/batal', [RiwayatController::class, 'batalOrder'])
            ->middleware('permission:edit')
            ->name('batal');
        
        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrder'])
            ->middleware('permission:edit')
            ->name('selesai');
        
        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbil'])
            ->middleware('permission:edit')
            ->name('siap_di_ambil');
        
        // Layanan dalam riwayat
        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPage'])
            ->middleware('permission:add')
            ->name('add_layanan_page');
        
        Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayanan'])
            ->middleware('permission:add')
            ->name('add_layanan_store');
        
        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarSubmit'])
            ->middleware('permission:edit')
            ->name('bayar.submit');
        
        Route::put('/layanan/{id}', [RiwayatController::class, 'updateLayanan'])
            ->middleware('permission:edit')
            ->name('update_layanan');
        
        Route::delete('/layanan/{id}', [RiwayatController::class, 'destroyLayanan'])
            ->middleware('permission:delete')
            ->name('layanan.destroy');
        
        Route::get('/layanan/{id}/edit', [RiwayatController::class, 'editLayanan'])
            ->middleware('permission:edit')
            ->name('edit_layanan');
        
        Route::put('/detail/{id}/update', [RiwayatController::class, 'updateDetail'])
            ->middleware('permission:edit')
            ->name('update_detail');
        
        Route::delete('/detail/{id}', [RiwayatController::class, 'deleteDetail'])
            ->middleware('permission:delete')
            ->name('delete_detail');

        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriver'])->name('assign-driver'); // ✅ TAMBAH INI
    });

    // ================= SATUAN (With Permission) =================
    Route::prefix('satuan')->name('satuan.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'satuanIndex'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/create', [SatuanParfumController::class, 'satuanCreate'])
            ->middleware('permission:add')
            ->name('create');
        
        Route::post('/store', [SatuanParfumController::class, 'satuanStore'])
            ->middleware('permission:add')
            ->name('store');
        
        Route::get('/{id}/edit', [SatuanParfumController::class, 'satuanEdit'])
            ->middleware('permission:edit')
            ->name('edit');
        
        Route::put('/{id}', [SatuanParfumController::class, 'satuanUpdate'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::delete('/{id}', [SatuanParfumController::class, 'satuanDestroy'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= PARFUM (With Permission) =================
    Route::prefix('parfum')->name('parfum.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'parfumIndex'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/create', [SatuanParfumController::class, 'parfumCreate'])
            ->middleware('permission:add')
            ->name('create');
        
        Route::post('/store', [SatuanParfumController::class, 'parfumStore'])
            ->middleware('permission:add')
            ->name('store');
        
        Route::get('/{id}/edit', [SatuanParfumController::class, 'parfumEdit'])
            ->middleware('permission:edit')
            ->name('edit');
        
        Route::put('/{id}', [SatuanParfumController::class, 'parfumUpdate'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::delete('/{id}', [SatuanParfumController::class, 'parfumDestroy'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= PENGELUARAN (With Permission) =================
    Route::prefix('pengeluaran')->name('pengeluaran.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/create', [LaporanController::class, 'create'])
            ->middleware('permission:add')
            ->name('create');
        
        Route::post('/', [LaporanController::class, 'store'])
            ->middleware('permission:add')
            ->name('store');
        
        Route::get('/{id}/edit', [LaporanController::class, 'edit'])
            ->middleware('permission:edit')
            ->name('edit');
        
        Route::put('/{id}', [LaporanController::class, 'update'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::delete('/{id}', [LaporanController::class, 'destroy'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= PENGATURAN (No Permission - Everyone Can Access) =================
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::post('/update', [PengaturanController::class, 'update'])->name('update');
        Route::post('/omzet', [PengaturanController::class, 'updateOmzet'])->name('omzet');
        Route::post('/backup', [PengaturanController::class, 'backup'])->name('backup');
        Route::post('/restore', [PengaturanController::class, 'restore'])->name('restore');
        Route::get('/metode-bayar', [PengaturanController::class, 'metodeBayar'])->name('metode');
        Route::post('/metode-bayar/store', [PengaturanController::class, 'storeMetodeBayar'])->name('metode.store');
        Route::delete('/metode-bayar/{id}', [PengaturanController::class, 'deleteMetodeBayar'])->name('metode.delete');
    });

    // ================= LAPORAN (View Permission Only) =================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'laporanIndex'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/transaksi/index', [LaporanController::class, 'transaksiIndex'])
            ->middleware('permission:view')
            ->name('transaksi.index');
        
        Route::get('/kasir/index', [LaporanController::class, 'kasirIndex'])
            ->middleware('permission:view')
            ->name('kasir.index');
        
        Route::get('/bayar/index', [LaporanController::class, 'bayarIndex'])
            ->middleware('permission:view')
            ->name('bayar.index');
        
        Route::get('/pengeluaran/index', [LaporanController::class, 'pengeluaranIndex'])
            ->middleware('permission:view')
            ->name('pengeluaran.index');
        
        Route::get('/satuan/index', [LaporanController::class, 'satuanIndex'])
            ->middleware('permission:view')
            ->name('satuan.index');
        
        Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndex'])
            ->middleware('permission:view')
            ->name('pelanggan.index');
    });

    // ================= CHANGE PASSWORD (No Permission) =================
    Route::get('/change-password', [ChangePasswordController::class, 'index'])
        ->name('change.password');
    
    Route::post('/change-password', [ChangePasswordController::class, 'update'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN2 AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin2')->middleware('auth:admin2')->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [AuthWebController::class, 'admin2Dashboard'])
        ->name('admin2.dashboard');

    Route::prefix('layanan')->group(function () {

    Route::get('/', [LayananController::class, 'indexAdmin2'])
        ->name('admin2.layanan.index');

    Route::get('/create', [LayananController::class, 'createAdmin2'])
        ->name('admin2.layanan_create');

    Route::post('/store', [LayananController::class, 'storeAdmin2'])
        ->name('admin2.layanan.store');

    Route::get('/{id}/edit', [LayananController::class, 'editAdmin2'])
        ->whereNumber('id')
        ->name('admin2.layanan.edit');

    Route::put('/{id}', [LayananController::class, 'updateAdmin2'])
        ->whereNumber('id')
        ->name('admin2.layanan.update');

    Route::delete('/{id}', [LayananController::class, 'destroyAdmin2'])
        ->whereNumber('id')
        ->name('admin2.layanan.destroy');

    Route::get('/{id}/duplicate', [LayananController::class, 'duplicateAdmin2'])
        ->whereNumber('id')
        ->name('admin2.layanan.duplicate');

    // ---- JENIS LAYANAN ----
    Route::get('{from}/jenis/add', [LayananController::class, 'sessionCreateJenisAdmin2'])
        ->name('admin2.session.create');

    Route::post('{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenisAdmin2'])
        ->name('admin2.session.store');

    Route::get('{id_layanan}/jenis/create', [LayananController::class, 'createJenisAdmin2'])
        ->name('admin2.jenis.create');

    Route::post('{id_layanan}/jenis/store', [LayananController::class, 'storeJenisAdmin2'])
        ->name('admin2.jenis.store');

    Route::get('jenis/{id}/edit', [LayananController::class, 'editJenisAdmin2'])
        ->name('admin2.layanan.jenis.edit');

    Route::put('jenis/{id}', [LayananController::class, 'updateJenisAdmin2'])
        ->name('admin2.layanan.jenis.update');

    Route::delete('jenis/{id}', [LayananController::class, 'destroyJenisAdmin2'])
        ->name('admin2.jenis.destroy');

    Route::post('{from}/jenis/add-edit', [LayananController::class, 'addJenisEditAdmin2'])
        ->name('admin2.layanan.jenis.add.edit');
        Route::get(
        '/{layanan}/jenis/tambah',
        function ($layanan) {

            $satuan = Satuan::all(); // 🔥 INI YANG KURANG

            return view('admin2.layanan.tambah_jenis_layanan_edit', [
            'from'       => $layanan,
            'id_layanan' => $layanan,
            'satuan'     => $satuan,
        ]);
        }
    )->name('admin2.layanan.jenis.tambah');
});

 Route::prefix('pesanan-online')->group(function () {
        Route::get('/', [PesananOnlineController::class, 'indexAdmin2'])->name('admin2.pesanan.online.index');
        Route::get('/{id}/detail', [PesananOnlineController::class, 'detailAdmin2'])->name('admin2.pesanan.online.detail');
        Route::get('/{id}/terima', [PesananOnlineController::class, 'terimaAdmin2'])->name('admin2.pesanan.online.terima');
        Route::get('/{id}/tolak', [PesananOnlineController::class, 'tolakAdmin2'])->name('admin2.pesanan.online.tolak');
        Route::get('/{id}/proses', [PesananOnlineController::class, 'prosesAdmin2'])->name('admin2.pesanan.online.proses');
        Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbilAdmin2'])->name('admin2.pesanan.online.siap_di_ambil');
        Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesaiAdmin2'])->name('admin2.pesanan.online.selesai');
        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayarAdmin2'])->name('admin2.pesanan.online.bayar');
        Route::delete('/{id}', [PesananOnlineController::class, 'destroyAdmin2'])->name('admin2.pesanan.online.destroy');
        Route::get('/{id}/list-driver', [PesananOnlineController::class, 'listDriver'])->name('list-driver'); // ✅ TAMBAH INI
                Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriver'])->name('assign-driver'); // ✅ TAMBAH INI
    });

    Route::prefix('riwayat')->group(function () {
            Route::get('/', [RiwayatController::class, 'indexAdmin2'])->name('admin2.riwayat.index');
            Route::get('/{id}/detail', [RiwayatController::class, 'detailAdmin2'])->name('admin2.riwayat.detail');
            Route::get('/{id}/show', [RiwayatController::class, 'showAdmin2'])->name('admin2.riwayat.show');
            Route::get('/{id}/edit', [RiwayatController::class, 'editAdmin2'])->name('admin2.riwayat.edit');
            Route::put('/{id}', [RiwayatController::class, 'updateAdmin2'])->name('admin2.riwayat.update');
            Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrderAdmin2'])->name('admin2.riwayat.proses');
            Route::patch('/admin2/riwayat/{id}/batal', [RiwayatController::class, 'admin2.batalOrder'])
            ->name('riwayat.batal');
            Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrdeAdmin2r'])->name('admin2.riwayat.selesai');
            Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbilAdmin2'])->name('admin2.riwayat.siap_di_ambil');

            // Layanan dalam riwayat
            Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPageAdmin2'])->name('admin2.riwayat.add_layanan_page');
            Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayananAdmin2'])->name('admin2.riwayat.add_layanan_store');
            Route::post('/{id}/bayar', [RiwayatController::class, 'bayarSubmitAdmin2'])->name('admin2.riwayat.bayar.submit');
            Route::put('/layanan/{id}', [RiwayatController::class, 'updateLayananAdmin2'])->name('admin2.riwayat.update_layanan');
            Route::delete('/layanan/{id}', [RiwayatController::class, 'destroyLayananAdmin2'])->name('admin2.riwayat.layanan.destroy');
            Route::get('/layanan/{id}/edit', [RiwayatController::class, 'editLayananAdmin2'])->name('admin2.riwayat.edit_layanan');
            Route::put('detail/{id}/update', [RiwayatController::class, 'updateDetailAdmin2'])->name('admin2.riwayat.update_detail');
            Route::delete('/admin2/riwayat/detail/{id}', [RiwayatController::class, 'deleteDetailAdmin2'])->name('admin2.riwayat.delete_detail');
            Route::delete('/{id}', [RiwayatController::class, 'destroyAdmin2'])->name('admin2.riwayat.destroy');
        });

    // ================= TRANSAKSI =================
    Route::prefix('transaksi')->group(function () {

        // ================= TRANSAKSI =================
        Route::get('/pelanggan', [TransaksiController::class, 'pelangganAdmin2'])
            ->name('admin2.transaksi.pelanggan');

        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelangganAdmin2'])
            ->name('admin2.transaksi.setPelanggan');

        Route::get('/create', [TransaksiController::class, 'createAdmin2'])
            ->name('admin2.transaksi.create');

        Route::get('/checkout', [TransaksiController::class, 'checkoutAdmin2'])
            ->name('admin2.transaksi.checkout');

        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayananAdmin2'])
        ->middleware('permission:layanan,add')
        ->name('admin2.transaksi.addLayanan');

        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayananAdmin2'])
            ->name('admin2.transaksi.temp_store_layanan');

        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeteranganAdmin2'])
            ->name('admin2.transaksi.updateKeterangan');

        Route::post('/bayar', [TransaksiController::class, 'bayarAdmin2'])
            ->name('admin2.transaksi.bayar');

        Route::get('/print/{id}', [TransaksiController::class, 'printAdmin2'])
            ->name('admin2.transaksi.print');

        Route::post('/remove/{id}', [TransaksiController::class, 'removeAdmin2'])
            ->name('admin2.transaksi.remove');

        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
            return redirect()->route('admin2.dashboard');
        })->name('admin2.transaksi.reset');

        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenisAdmin2']
            )->name('admin2.transaksi.addJenis');
        });
});
