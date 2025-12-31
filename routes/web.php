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

/*
|--------------------------------------------------------------------------
| KASIR ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('kasir')->middleware('auth:kasir')->name('kasir.')->group(function () {
    
    // Dashboard (No Permission)
    Route::get('/dashboard', [AuthWebController::class, 'kasirDashboard'])->name('dashboard');
    
    Route::post('/logout', function (): RedirectResponse {
        Auth::guard('kasir')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/kasir/login');
    })->name('logout');

    // ========================================
    // PELANGGAN
    // ========================================
    Route::prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::get('/', [AuthWebController::class, 'pelangganIndexKasir'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [AuthWebController::class, 'createKasir'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [AuthWebController::class, 'storeKasir'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [AuthWebController::class, 'pelangganEditKasir'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [AuthWebController::class, 'pelangganUpdateKasir'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [AuthWebController::class, 'pelangganDestroyKasir'])
            ->middleware('permission:delete')->name('destroy');
    });

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

    // ========================================
    // LAYANAN
    // ========================================
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/', [LayananController::class, 'index'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [LayananController::class, 'createKasir'])
            ->middleware('permission:add')->name('create');
        Route::get('/{from}/create', [LayananController::class, 'layananCreateKasir'])
            ->middleware('permission:add')->whereIn('from', ['transaksi','dashboard'])->name('layanan_create');
        Route::post('/store', [LayananController::class, 'storeKasir'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [LayananController::class, 'edit'])
            ->middleware('permission:edit')->whereNumber('id')->name('edit');
        Route::put('/{id}', [LayananController::class, 'update'])
            ->middleware('permission:edit')->whereNumber('id')->name('update');
        Route::delete('/{id}', [LayananController::class, 'destroyKasir'])
            ->middleware('permission:delete')->whereNumber('id')->name('destroy');
        Route::get('/{id}/duplicate', [LayananController::class, 'duplicateKasir'])
            ->middleware('permission:add')->whereNumber('id')->name('duplicate');
        
        // Jenis Layanan
        Route::get('{from}/jenis/add', [LayananController::class, 'sessionCreateJenisKasir'])
            ->middleware('permission:add')->name('session.create')->whereNumber('from');
        Route::post('{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenisKasir'])
            ->middleware('permission:add')->name('session.store');
        Route::get('{id_layanan}/jenis/create', [LayananController::class, 'createJenisKasir'])
            ->middleware('permission:add')->name('jenis.create')->whereNumber('id_layanan');
        Route::post('{id_layanan}/jenis/store', [LayananController::class, 'storeJenisKasir'])
            ->middleware('permission:add')->name('jenis.store')->whereNumber('id_layanan');
        Route::get('jenis/{id}/edit', [LayananController::class, 'editJenisKasir'])
            ->middleware('permission:edit')->name('jenis.edit');
        Route::post('{from}/jenis/add-edit-kasir', [LayananController::class, 'addJenisEdit'])
            ->middleware('permission:edit')->name('jenis.add.edit');
        Route::get('/{layanan}/jenis/tambah', function ($layanan) {
            $satuan = Satuan::all();
            return view('kasir.layanan.tambah_jenis_layanan_create', [
                'from' => $layanan,
                'id_layanan' => $layanan,
                'satuan' => $satuan,
            ]);
        })->middleware('permission:add')->name('jenis.tambah');
    });

    // ========================================
    // RIWAYAT
    // ========================================
    Route::prefix('riwayat')->name('riwayat.')->group(function () {
        Route::get('/', [RiwayatController::class, 'indexKasir'])
            ->middleware('permission:view')->name('index');
        Route::get('/{id}/detail', [RiwayatController::class, 'detailKasir'])
            ->middleware('permission:view')->name('detail');
        Route::get('/{id}/show', [RiwayatController::class, 'showKasir'])
            ->middleware('permission:view')->name('show');
        Route::get('/{id}/edit', [RiwayatController::class, 'editKasir'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [RiwayatController::class, 'updateKasir'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [RiwayatController::class, 'destroyKasir'])
            ->middleware('permission:delete')->name('destroy');
        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrderKasir'])
            ->middleware('permission:edit')->name('proses');
        Route::get('/{id}/batal', [RiwayatController::class, 'batalOrderKasir'])
            ->middleware('permission:edit')->name('batal');
        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrderKasir'])
            ->middleware('permission:edit')->name('selesai');
        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbilKasir'])
            ->middleware('permission:edit')->name('siap_di_ambil');
        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarKasir'])
            ->middleware('permission:edit')->name('bayar');
        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPage'])
            ->middleware('permission:add')->name('addlayanan');
        Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayananKasir'])
            ->middleware('permission:add')->name('storelayanan');
        Route::post('/detail/{id}/update', [RiwayatController::class, 'updateDetail'])
            ->middleware('permission:edit')->name('updateDetail');
        Route::delete('/detail/{id}', [RiwayatController::class, 'deleteDetail'])
            ->middleware('permission:delete')->name('deleteDetail');
    });

    // ========================================
    // SATUAN & PARFUM
    // ========================================
    Route::prefix('satuan')->name('satuan.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'satuanIndexKasir'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [SatuanParfumController::class, 'satuanCreateKasir'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [SatuanParfumController::class, 'satuanStoreKasir'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [SatuanParfumController::class, 'satuanEditKasir'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [SatuanParfumController::class, 'satuanUpdateKasir'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [SatuanParfumController::class, 'satuanDestroyKasir'])
            ->middleware('permission:delete')->name('destroy');
    });

    Route::prefix('parfum')->name('parfum.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'parfumIndexKasir'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [SatuanParfumController::class, 'parfumCreateKasir'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [SatuanParfumController::class, 'parfumStoreKasir'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [SatuanParfumController::class, 'parfumEditKasir'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [SatuanParfumController::class, 'parfumUpdateKasir'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [SatuanParfumController::class, 'parfumDestroyKasir'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // PENGELUARAN
    // ========================================
    Route::prefix('pengeluaran')->name('pengeluaran.')->group(function () {
        Route::get('/', [LaporanController::class, 'indexKasir'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [LaporanController::class, 'createKasir'])
            ->middleware('permission:add')->name('create');
        Route::post('/', [LaporanController::class, 'storeKasir'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [LaporanController::class, 'editKasir'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [LaporanController::class, 'updateKasir'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [LaporanController::class, 'destroyKasir'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // PESANAN ONLINE
    // ========================================
    Route::prefix('pesanan-online')->name('pesanan.online.')->group(function () {
        Route::get('/', [PesananOnlineController::class, 'indexKasir'])
            ->middleware('permission:view')->name('index');
        Route::get('/{id}/detail', [PesananOnlineController::class, 'detailKasir'])
            ->middleware('permission:view')->name('detail');
        Route::get('/{id}/terima', [PesananOnlineController::class, 'terimaKasir'])
            ->middleware('permission:edit')->name('terima');
        Route::get('/{id}/tolak', [PesananOnlineController::class, 'tolakKasir'])
            ->middleware('permission:edit')->name('tolak');
        Route::get('/{id}/proses', [PesananOnlineController::class, 'prosesKasir'])
            ->middleware('permission:edit')->name('proses');
        Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbilKasir'])
            ->middleware('permission:edit')->name('siap_di_ambil');
        Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesaiKasir'])
            ->middleware('permission:edit')->name('selesai');
        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayarKasir'])
            ->middleware('permission:edit')->name('bayar');
        Route::delete('/{id}', [PesananOnlineController::class, 'destroyKasir'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // PENGATURAN & LAPORAN (No Permission)
    // ========================================
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'indexKasir'])->name('index');
        Route::post('/update', [PengaturanController::class, 'updateKasir'])->name('update');
        Route::post('/omzet', [PengaturanController::class, 'updateOmzetKasir'])->name('omzet');
        Route::post('/backup', [PengaturanController::class, 'backupKasir'])->name('backup');
        Route::post('/restore', [PengaturanController::class, 'restoreKasir'])->name('restore');
        Route::get('/metode-bayar', [PengaturanController::class, 'metodeBayarKasir'])->name('metode');
        Route::post('/metode-bayar/store', [PengaturanController::class, 'storeMetodeBayarKasir'])->name('metode.store');
        Route::delete('/metode-bayar/{id}', [PengaturanController::class, 'deleteMetodeBayarKasir'])->name('metode.delete');
    });

    Route::get('/laporan', [LaporanController::class, 'laporanIndexKasir'])
        ->middleware('permission:view')->name('laporan.index');
    Route::get('/laporan/transaksi/index', [LaporanController::class, 'transaksiIndexKasir'])
        ->middleware('permission:view')->name('laporan.transaksi.index');
    Route::get('/laporan/kasir/index', [LaporanController::class, 'kasirIndexKasir'])
        ->middleware('permission:view')->name('laporan.kasir.index');
    Route::get('/laporan/bayar/index', [LaporanController::class, 'bayarIndexKasir'])
        ->middleware('permission:view')->name('laporan.bayar.index');
    Route::get('/laporan/pengeluaran/index', [LaporanController::class, 'pengeluaranIndexKasir'])
        ->middleware('permission:view')->name('laporan.pengeluaran.index');
    Route::get('/laporan/satuan/index', [LaporanController::class, 'satuanIndexKasir'])
        ->middleware('permission:view')->name('laporan.satuan.index');
    Route::get('/laporan/pelanggan/index', [LaporanController::class, 'pelangganIndexKasir'])
        ->middleware('permission:view')->name('laporan.pelanggan.index');

    Route::get('/change-password', [ChangePasswordController::class, 'indexKasir'])->name('change.password');
    Route::post('/change-password', [ChangePasswordController::class, 'updateKasir'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    
    // Dashboard (No Permission)
    Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])->name('dashboard');

    // ========================================
    // USER MANAGER (Super Admin Only)
    // ========================================
    Route::prefix('manager')->name('manager.')->middleware('superadmin')->group(function () {
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

    // ========================================
    // PELANGGAN
    // ========================================
    Route::prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::get('/', [AuthWebController::class, 'pelangganIndex'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [AuthWebController::class, 'create'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [AuthWebController::class, 'store'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [AuthWebController::class, 'pelangganEdit'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [AuthWebController::class, 'pelangganUpdate'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [AuthWebController::class, 'pelangganDestroy'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // TRANSAKSI (Minimal Permission)
    // ========================================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        Route::get('/pelanggan', [TransaksiController::class, 'pilihPelanggan'])->name('pelanggan');
        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelanggan'])->name('setPelanggan');
        Route::get('/create', [TransaksiController::class, 'create'])->name('create');
        Route::post('/checkout', [TransaksiController::class, 'checkout'])->name('checkout');
        Route::post('/bayar', [TransaksiController::class, 'bayar'])->name('bayar');
        Route::get('/print/{id}', [TransaksiController::class, 'print'])->name('print');
        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayanan'])->name('temp_store_layanan');
        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeterangan'])->name('updateKeterangan');
        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayanan'])
            ->middleware('permission:edit')->name('addLayanan');
        Route::post('/remove/{id}', [TransaksiController::class, 'remove'])
            ->middleware('permission:delete')->name('remove');
        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenis'])->name('addJenis');
        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
            return redirect()->route('admin.dashboard');
        })->name('reset');
    });

    // ========================================
    // LAYANAN
    // ========================================
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/', [LayananController::class, 'index'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [LayananController::class, 'create'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [LayananController::class, 'store'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [LayananController::class, 'edit'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [LayananController::class, 'update'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [LayananController::class, 'destroy'])
            ->middleware('permission:delete')->name('destroy');
        Route::get('/{id}/duplicate', [LayananController::class, 'duplicate'])
            ->middleware('permission:add')->name('duplicate');
        
        // Jenis Layanan
        Route::get('/{from}/jenis/add', [LayananController::class, 'sessionCreateJenis'])
            ->middleware('permission:add')->name('session.create');
        Route::post('/{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenis'])
            ->middleware('permission:add')->name('session.store');
        Route::get('/{id_layanan}/jenis/create', [LayananController::class, 'createJenis'])
            ->middleware('permission:add')->name('jenis.create');
        Route::post('/{id_layanan}/jenis/store', [LayananController::class, 'storeJenis'])
            ->middleware('permission:add')->name('jenis.store');
        Route::get('/jenis/{id_jenis}/edit', [LayananController::class, 'editJenis'])
            ->middleware('permission:edit')->name('jenis.edit');
        Route::put('/jenis/{id}', [LayananController::class, 'updateJenis'])
            ->middleware('permission:edit')->name('jenis.update');
        Route::delete('/jenis/{id_jenis}/destroy', [LayananController::class, 'destroyJenis'])
            ->middleware('permission:delete')->name('jenis.destroy');
        Route::post('/{from}/jenis/add-edit', [LayananController::class, 'addJenisEdit'])
            ->middleware('permission:edit')->name('jenis.add.edit');
    });

    // ========================================
    // PESANAN ONLINE
    // ========================================
    Route::prefix('pesanan-online')->name('pesanan.online.')->group(function () {
        Route::get('/', [PesananOnlineController::class, 'index'])
            ->middleware('permission:view')->name('index');
        Route::get('/{id}/detail', [PesananOnlineController::class, 'detail'])
            ->middleware('permission:view')->name('detail');
        Route::post('/{id}/terima', [PesananOnlineController::class, 'terima'])
            ->middleware('permission:edit')->name('terima');
        Route::post('/{id}/tolak', [PesananOnlineController::class, 'tolak'])
            ->middleware('permission:edit')->name('tolak');
        Route::post('/{id}/proses', [PesananOnlineController::class, 'proses'])
            ->middleware('permission:edit')->name('proses');
        Route::post('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbil'])
            ->middleware('permission:edit')->name('siap_di_ambil');
        Route::post('/{id}/selesai', [PesananOnlineController::class, 'selesai'])
            ->middleware('permission:edit')->name('selesai');
        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayar'])
            ->middleware('permission:edit')->name('bayar');
        Route::delete('/{id}', [PesananOnlineController::class, 'destroy'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // RIWAYAT
    // ========================================
    Route::prefix('riwayat')->name('riwayat.')->group(function () {
        Route::get('/', [RiwayatController::class, 'index'])
            ->middleware('permission:view')->name('index');
        Route::get('/{id}/detail', [RiwayatController::class, 'detail'])
            ->middleware('permission:view')->name('detail');
        Route::get('/{id}/show', [RiwayatController::class, 'show'])
            ->middleware('permission:view')->name('show');
        Route::get('/{id}/edit', [RiwayatController::class, 'edit'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [RiwayatController::class, 'update'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [RiwayatController::class, 'destroy'])
            ->middleware('permission:delete')->name('destroy');
        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrder'])
            ->middleware('permission:edit')->name('proses');
        Route::patch('/{id}/batal', [RiwayatController::class, 'batalOrder'])
            ->middleware('permission:edit')->name('batal');
        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrder'])
            ->middleware('permission:edit')->name('selesai');
        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbil'])
            ->middleware('permission:edit')->name('siap_di_ambil');
        
        // Layanan dalam riwayat
        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPage'])
            ->middleware('permission:add')->name('add_layanan_page');
        Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayanan'])
            ->middleware('permission:add')->name('add_layanan_store');
        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarSubmit'])
            ->middleware('permission:edit')->name('bayar.submit');
        Route::put('/layanan/{id}', [RiwayatController::class, 'updateLayanan'])
            ->middleware('permission:edit')->name('update_layanan');
        Route::delete('/layanan/{id}', [RiwayatController::class, 'destroyLayanan'])
            ->middleware('permission:delete')->name('layanan.destroy');
        Route::get('/layanan/{id}/edit', [RiwayatController::class, 'editLayanan'])
            ->middleware('permission:edit')->name('edit_layanan');
        Route::put('/detail/{id}/update', [RiwayatController::class, 'updateDetail'])
            ->middleware('permission:edit')->name('update_detail');
        Route::delete('/detail/{id}', [RiwayatController::class, 'deleteDetail'])
            ->middleware('permission:delete')->name('delete_detail');
    });

    // ========================================
    // SATUAN & PARFUM
    // ========================================
    Route::prefix('satuan')->name('satuan.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'satuanIndex'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [SatuanParfumController::class, 'satuanCreate'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [SatuanParfumController::class, 'satuanStore'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [SatuanParfumController::class, 'satuanEdit'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [SatuanParfumController::class, 'satuanUpdate'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [SatuanParfumController::class, 'satuanDestroy'])
            ->middleware('permission:delete')->name('destroy');
    });

    Route::prefix('parfum')->name('parfum.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'parfumIndex'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [SatuanParfumController::class, 'parfumCreate'])
            ->middleware('permission:add')->name('create');
        Route::post('/store', [SatuanParfumController::class, 'parfumStore'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [SatuanParfumController::class, 'parfumEdit'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [SatuanParfumController::class, 'parfumUpdate'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [SatuanParfumController::class, 'parfumDestroy'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // PENGELUARAN
    // ========================================
    Route::prefix('pengeluaran')->name('pengeluaran.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])
            ->middleware('permission:view')->name('index');
        Route::get('/create', [LaporanController::class, 'create'])
            ->middleware('permission:add')->name('create');
        Route::post('/', [LaporanController::class, 'store'])
            ->middleware('permission:add')->name('store');
        Route::get('/{id}/edit', [LaporanController::class, 'edit'])
            ->middleware('permission:edit')->name('edit');
        Route::put('/{id}', [LaporanController::class, 'update'])
            ->middleware('permission:edit')->name('update');
        Route::delete('/{id}', [LaporanController::class, 'destroy'])
            ->middleware('permission:delete')->name('destroy');
    });

    // ========================================
    // PENGATURAN & LAPORAN (No Permission)
    // ========================================
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

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'laporanIndex'])
            ->middleware('permission:view')->name('index');
        Route::get('/transaksi/index', [LaporanController::class, 'transaksiIndex'])
            ->middleware('permission:view')->name('transaksi.index');
        Route::get('/kasir/index', [LaporanController::class, 'kasirIndex'])
            ->middleware('permission:view')->name('kasir.index');
        Route::get('/bayar/index', [LaporanController::class, 'bayarIndex'])
            ->middleware('permission:view')->name('bayar.index');
        Route::get('/pengeluaran/index', [LaporanController::class, 'pengeluaranIndex'])
            ->middleware('permission:view')->name('pengeluaran.index');
        Route::get('/satuan/index', [LaporanController::class, 'satuanIndex'])
            ->middleware('permission:view')->name('satuan.index');
        Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndex'])
            ->middleware('permission:view')->name('pelanggan.index');
    });

    Route::get('/change-password', [ChangePasswordController::class, 'index'])->name('change.password');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.update');
});