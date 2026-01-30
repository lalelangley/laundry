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
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\FcmTokenController;
use App\Models\Satuan;

Route::middleware('auth:sanctum')->post('/fcm-token', [FcmTokenController::class, 'store']);

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES - Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');

// ✅ ADMIN LOGOUT
Route::post('/logout', function (): RedirectResponse {
    Auth::guard('admin')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// ✅ KASIR LOGOUT
Route::post('/kasir/logout', function (): RedirectResponse {
    Auth::guard('kasir')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login')->with('success', 'Berhasil logout');
})->name('kasir.logout');

// ✅ ADMIN2 LOGOUT
Route::post('/admin2/logout', function (): RedirectResponse {
    Auth::guard('admin')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login')->with('success', 'Berhasil logout');
})->name('admin2.logout');

/*
|--------------------------------------------------------------------------
| KASIR ROUTES - With Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('kasir')->middleware('auth:kasir')->group(function () {

    // ================= DASHBOARD (No Permission) =================
    Route::get('/dashboard', [AuthWebController::class, 'kasirDashboard'])
        ->name('kasir.dashboard');

    // ================= TRANSAKSI (KASIR) - WITH PERMISSION =================
    Route::prefix('transaksi')->name('kasir.transaksi.')->group(function () {
        // View - butuh permission:view untuk akses halaman transaksi
        Route::get('/pelanggan', [TransaksiController::class, 'pelangganKasir'])
            ->middleware('permission:view')
            ->name('pelanggan');
        
        Route::get('/create', [TransaksiController::class, 'createKasir'])
            ->middleware('permission:view')
            ->name('create');
        
        Route::get('/confirm', [TransaksiController::class, 'confirmKasir'])
            ->middleware('permission:view')
            ->name('confirm');
        
        Route::get('/print/{id}', [TransaksiController::class, 'printKasir'])
            ->middleware('permission:view')
            ->name('print');
        
        // Add/Create - butuh permission:add untuk membuat transaksi baru
        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelangganKasir'])
            ->middleware('permission:add')
            ->name('setPelanggan');
        
        Route::post('/checkout', [TransaksiController::class, 'checkoutKasir'])
            ->middleware('permission:add')
            ->name('checkout');
        
        Route::post('/bayar', [TransaksiController::class, 'bayarKasir'])
            ->middleware('permission:add')
            ->name('bayar');
        
        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayananKasir'])
            ->middleware('permission:add')
            ->name('addLayanan');
        
        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenisKasir'])
            ->middleware('permission:add')
            ->name('addJenis');
        
        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayananKasir'])
            ->middleware('permission:add')
            ->name('temp_store_layanan');
        
        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeteranganKasir'])
            ->middleware('permission:edit')
            ->name('updateKeterangan');
        
        // Delete - butuh permission:delete
        Route::post('/remove/{id}', [TransaksiController::class, 'removeKasir'])
            ->middleware('permission:delete')
            ->name('remove');
        
        // Reset (no permission needed)
        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
            return redirect()->route('kasir.dashboard');
        })->name('reset');
    });

    // ================= PELANGGAN (With Permission) =================
    Route::prefix('pelanggan')->name('kasir.pelanggan.')->group(function () {
        Route::get('/', [AuthWebController::class, 'pelangganIndexKasir'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [AuthWebController::class, 'createKasir'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/store', [AuthWebController::class, 'storeKasir'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [AuthWebController::class, 'pelangganEditKasir'])
            ->middleware('permission:edit')
            ->name('edit');

        Route::put('/{id}', [AuthWebController::class, 'pelangganUpdateKasir'])
            ->middleware('permission:edit')
            ->name('update');

        Route::delete('/{id}', [AuthWebController::class, 'pelangganDestroyKasir'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= LAYANAN (With Permission) =================
    Route::prefix('layanan')->name('kasir.layanan.')->group(function () {
        Route::get('/', [LayananController::class, 'index'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [LayananController::class, 'createKasir'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/store', [LayananController::class, 'storeKasir'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [LayananController::class, 'edit'])
            ->middleware('permission:edit')
            ->whereNumber('id')
            ->name('edit');

        Route::put('/{id}', [LayananController::class, 'update'])
            ->middleware('permission:edit')
            ->whereNumber('id')
            ->name('update');

        Route::delete('/{id}', [LayananController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->whereNumber('id')
            ->name('destroy');

        Route::get('/{id}/duplicate', [LayananController::class, 'duplicateKasir'])
            ->middleware('permission:add')
            ->whereNumber('id')
            ->name('duplicate');

        // ================= JENIS LAYANAN (With Permission) =================
        Route::get('{from}/jenis/add', [LayananController::class, 'sessionCreateJenisKasir'])
            ->middleware('permission:add')
            ->name('jenis.session.create')
            ->whereNumber('from');

        Route::post('{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenisKasir'])
            ->middleware('permission:add')
            ->name('jenis.session.store');

        Route::get('{id_layanan}/jenis/create', [LayananController::class, 'createJenisKasir'])
            ->middleware('permission:add')
            ->name('jenis.create')
            ->whereNumber('id_layanan');

        Route::post('{id_layanan}/jenis/store', [LayananController::class, 'storeJenisKasir'])
            ->middleware('permission:add')
            ->name('jenis.store')
            ->whereNumber('id_layanan');

        Route::get('jenis/{id}/edit', [LayananController::class, 'editJenisKasir'])
            ->middleware('permission:edit')
            ->name('jenis.edit');

        Route::put('jenis/{id}', [LayananController::class, 'updateJenisKasir'])
            ->middleware('permission:edit')
            ->name('jenis.update');

        Route::post('{from}/jenis/add-edit-kasir', [LayananController::class, 'addJenisEdit'])
            ->middleware('permission:edit')
            ->name('jenis.add.edit');

        Route::get('/{layanan}/jenis/tambah', function ($layanan) {
            $satuan = Satuan::all();
            return view('kasir.layanan.tambah_jenis_layanan_create', [
                'from'       => $layanan,
                'id_layanan' => $layanan,
                'satuan'     => $satuan,
            ]);
        })->middleware('permission:add')->name('jenis.tambah');
    });

    // ================= PESANAN ONLINE (KASIR) =================
    Route::prefix('pesanan-online')->name('kasir.pesanan.online.')->group(function () {
        // Index & Detail
        Route::get('/', [PesananOnlineController::class, 'indexKasir'])
            ->middleware('permission:view')
            ->name('index');
            
        Route::get('/{id}/detail', [PesananOnlineController::class, 'detailKasir'])
            ->middleware('permission:view')
            ->name('detail');
        
        // Update & Konfirmasi
        Route::put('/{id}/update-data', [PesananOnlineController::class, 'updateDataKasir'])
            ->middleware('permission:edit')
            ->name('updateData');
            
        Route::post('/{id}/konfirmasi', [PesananOnlineController::class, 'konfirmasiPesananKasir'])
            ->middleware('permission:edit')
            ->name('konfirmasi');
        
        // Status Management
        Route::get('/{id}/proses', [PesananOnlineController::class, 'prosesKasir'])
            ->middleware('permission:edit')
            ->name('proses');

        Route::get('/{id}/selesai-di-cuci', [PesananOnlineController::class, 'selesaiDiCuciKasir'])
            ->middleware('permission:edit')
            ->name('selesai_di_cuci');
            
        Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesaiKasir'])
            ->middleware('permission:edit')
            ->name('selesai');
            
        Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbilKasir'])
            ->middleware('permission:edit')
            ->name('siap_di_ambil');

        Route::get('/{id}/siap-di-antar', [PesananOnlineController::class, 'siapDiAntarKasir'])
            ->middleware('permission:edit')
            ->name('siap_di_antar');
            
        Route::get('/{id}/tolak', [PesananOnlineController::class, 'tolakKasir'])
            ->middleware('permission:edit')
            ->name('tolak');
        
        // Payment & Delete
        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayarKasir'])
            ->middleware('permission:edit')
            ->name('bayar');
            
        Route::delete('/{id}', [PesananOnlineController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->name('destroy');

        // Bukti Pembayaran
        Route::get('/{id}/bukti-pembayaran', [PesananOnlineController::class, 'buktiPembayaran'])
            ->name('bukti-pembayaran');
            
        Route::put('/{id}/simpan-bukti-pembayaran', [PesananOnlineController::class, 'simpanBuktiPembayaran'])
            ->middleware('permission:edit')
            ->name('simpan-bukti-pembayaran');

        // Masukkan Antrian
        Route::put('/{id}/masukkan-antrian', [PesananOnlineController::class, 'masukkanAntrianKasir'])
            ->middleware('permission:edit')
            ->name('masukkan-antrian');
        
        // Delivery
        Route::get('/delivery', [PesananOnlineController::class, 'listDeliveryOnlineKasir'])
            ->name('delivery');
            
        Route::get('/{id}/list-driver', [PesananOnlineController::class, 'listDriverKasir'])
            ->name('list-driver');
        
        // Assign Driver
        Route::post('/{id}/assign-driver-pickup', [PesananOnlineController::class, 'assignDriverPickupKasir'])
            ->middleware('permission:edit')
            ->name('assign-driver-pickup');
            
        Route::post('/{id}/assign-driver-antar', [PesananOnlineController::class, 'assignDriverAntarKasir'])
            ->middleware('permission:edit')
            ->name('assign-driver-antar');
            
        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriverKasir'])
            ->middleware('permission:edit')
            ->name('assign-driver');

        // ✅ FCM Notification
        Route::post('/{id}/send-fcm-notification', [PesananOnlineController::class, 'sendFcmNotificationKasir'])
            ->middleware('permission:edit')
            ->name('send-fcm-notification');

        // Driver Arrive
        Route::get('/{id}/driver-arrive', [PesananOnlineController::class, 'driverArriveAtLaundryKasir'])
            ->middleware('permission:edit')
            ->name('driver-arrive');
    });

    // ================= RIWAYAT (With Permission) =================
    Route::prefix('riwayat')->name('kasir.riwayat.')->group(function () {
        Route::get('/', [RiwayatController::class, 'indexKasir'])
            ->middleware('permission:view')
            ->name('index');
        Route::get('/{id}/detail', [RiwayatController::class, 'detailKasir'])
            ->middleware('permission:view')
            ->name('detail');
        Route::get('/{id}/show', [RiwayatController::class, 'showKasir'])
            ->middleware('permission:view')
            ->name('show');
        Route::get('/{id}/edit', [RiwayatController::class, 'editKasir'])
            ->middleware('permission:edit')
            ->name('edit');
        Route::put('/{id}', [RiwayatController::class, 'updateKasir'])
            ->middleware('permission:edit')
            ->name('update');
        Route::delete('/{id}', [RiwayatController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->name('destroy');
        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrderKasir'])
            ->middleware('permission:edit')
            ->name('proses');
        Route::get('/{id}/batal', [RiwayatController::class, 'batalOrderKasir'])
            ->middleware('permission:edit')
            ->name('batal');
        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrderKasir'])
            ->middleware('permission:edit')
            ->name('selesai');
        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbilKasir'])
            ->middleware('permission:edit')
            ->name('siap_di_ambil');
        
        // ✅ ROUTE BAYAR
        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarSubmitKasir'])
            ->middleware('permission:edit')
            ->whereNumber('id')
            ->name('bayar.submit');
        
        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPageKasir'])
        ->middleware('permission:edit')  // ✅ Ubah ke 'edit'
        ->name('addlayanan');
       Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayananKasir'])
        ->middleware('permission:edit')  // ✅ Ubah ke 'edit'
        ->name('storelayanan');
        Route::post('/detail/{id}/update', [RiwayatController::class, 'updateDetail'])
            ->middleware('permission:edit')
            ->name('updateDetail');
        Route::delete('/detail/{id}', [RiwayatController::class, 'deleteDetail'])
            ->middleware('permission:delete')
            ->name('deleteDetail');

        // ✅ FCM Notification & Assign Driver
        Route::post('/{id}/send-fcm-notification', [PesananOnlineController::class, 'sendFcmNotificationKasir'])
            ->middleware('permission:edit')
            ->name('send-fcm-notification');

        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriverKasir'])
            ->middleware('permission:edit')
            ->name('assign-driver');
    });

    // ================= SATUAN (With Permission) =================
    Route::prefix('satuan')->name('kasir.satuan.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'satuanIndexKasir'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [SatuanParfumController::class, 'satuanCreateKasir'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/store', [SatuanParfumController::class, 'satuanStoreKasir'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [SatuanParfumController::class, 'satuanEditKasir'])
            ->middleware('permission:edit')
            ->name('edit');

        Route::put('/{id}', [SatuanParfumController::class, 'satuanUpdateKasir'])
            ->middleware('permission:edit')
            ->name('update');

        Route::delete('/{id}', [SatuanParfumController::class, 'satuanDestroyKasir'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= PARFUM (With Permission) =================
    Route::prefix('parfum')->name('kasir.parfum.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'parfumIndexKasir'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [SatuanParfumController::class, 'parfumCreateKasir'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/store', [SatuanParfumController::class, 'parfumStoreKasir'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [SatuanParfumController::class, 'parfumEditKasir'])
            ->middleware('permission:edit')
            ->name('edit');

        Route::put('/{id}', [SatuanParfumController::class, 'parfumUpdateKasir'])
            ->middleware('permission:edit')
            ->name('update');

        Route::delete('/{id}', [SatuanParfumController::class, 'parfumDestroyKasir'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= PENGELUARAN (With Permission) =================
    Route::prefix('pengeluaran')->name('kasir.pengeluaran.')->group(function () {
        Route::get('/', [LaporanController::class, 'indexKasir'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [LaporanController::class, 'createKasir'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/', [LaporanController::class, 'storeKasir'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [LaporanController::class, 'editKasir'])
            ->middleware('permission:edit')
            ->name('edit');

        Route::put('/{id}', [LaporanController::class, 'updateKasir'])
            ->middleware('permission:edit')
            ->name('update');

        Route::delete('/{id}', [LaporanController::class, 'destroyKasir'])
            ->middleware('permission:delete')
            ->name('destroy');

        Route::get('/pengeluaran', [LaporanController::class, 'pengeluaranIndexKasir'])->name('pengeluaran.index');
        Route::get('/pengeluaran/export', [LaporanController::class, 'exportPengeluaranKasir'])->name('pengeluaran.export'); // ← TAMBAH INI
        
    });

    // ================= PENGATURAN =================
    Route::prefix('pengaturan')->name('kasir.pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'indexKasir'])->name('index');
        Route::post('/update', [PengaturanController::class, 'updateKasir'])->name('update');
        Route::post('/omzet', [PengaturanController::class, 'updateOmzetKasir'])->name('omzet');
        Route::post('/backup', [PengaturanController::class, 'backupKasir'])->name('backup');
        Route::post('/restore', [PengaturanController::class, 'restoreKasir'])->name('restore');
        
        Route::get('/backups/list', [PengaturanController::class, 'listBackupsKasir'])->name('backups.list');
        Route::get('/backups/download/{filename}', [PengaturanController::class, 'downloadBackupKasir'])->name('backup.download');
        Route::delete('/backups/delete/{filename}', [PengaturanController::class, 'deleteBackupKasir'])->name('backups.delete');
        
        Route::get('/metode-bayar', [PengaturanController::class, 'metodeBayarKasir'])->name('metode');
        Route::post('/metode-bayar/store', [PengaturanController::class, 'storeMetodeBayarKasir'])->name('metode.store');
        Route::delete('/metode-bayar/{id}', [PengaturanController::class, 'deleteMetodeBayarKasir'])->name('metode.delete');
    });

    // ================= LAPORAN (View Permission Only) =================
    Route::prefix('laporan')->name('kasir.laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'laporanIndexKasir'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/transaksi/index', [LaporanController::class, 'transaksiIndexKasir'])
            ->middleware('permission:view')
            ->name('transaksi.index');

        Route::get('/kasir/index', [LaporanController::class, 'kasirIndex'])
            ->middleware('permission:view')
            ->name('kasir.index');

        Route::get('/bayar/export', [LaporanController::class, 'exportBayar']) // ✅ TAMBAHKAN INI
        ->name('bayar.export');
        Route::get('/bayar/index', [LaporanController::class, 'bayarIndexKasir'])
            ->middleware('permission:view')
            ->name('bayar.index');

        Route::get('/pengeluaran/index', [LaporanController::class, 'pengeluaranIndexKasir'])
            ->middleware('permission:view')
            ->name('pengeluaran.index');

        Route::get('/satuan/index', [LaporanController::class, 'satuanIndexKasir'])
            ->middleware('permission:view')
            ->name('satuan.index');

        Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndexKasir'])
            ->middleware('permission:view')
            ->name('pelanggan.index');

        Route::get('/driver/index', [LaporanController::class, 'driverKasir'])
            ->middleware('permission:view')
            ->name('driver.index');

        // ✅ Export Excel
        Route::post('/transaksi/export', [LaporanController::class, 'exportTransaksiKasir'])->name('transaksi.export');
        
            // Satuan
        Route::get('/satuan/index', [LaporanController::class, 'satuanIndexKasir'])->name('satuan.index');
        Route::get('/satuan/export', [LaporanController::class, 'exportSatuanKasir'])->name('satuan.export');
        
        // Pelanggan
        Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndexKasir'])->name('pelanggan.index');
        Route::get('/pelanggan/export', [LaporanController::class, 'exportPelangganKasir'])->name('pelanggan.export');
        
        // Driver
        Route::get('/driver/index', [LaporanController::class, 'driverKasir'])->name('driver.index');
        Route::get('/driver/export', [LaporanController::class, 'exportDriverKasir'])->name('driver.export');

          Route::get('/pengeluaran', [LaporanController::class, 'pengeluaranIndexKasir'])->name('pengeluaran.index');
        Route::get('/pengeluaran/export', [LaporanController::class, 'exportPengeluaranKasir'])->name('pengeluaran.export'); // ← TAMBAH INI
        });

    // ================= CHANGE PASSWORD (No Permission) =================
    Route::get('/change-password', [ChangePasswordController::class, 'indexKasir'])
        ->name('kasir.change.password');

    Route::post('/change-password', [ChangePasswordController::class, 'updateKasir'])
        ->name('kasir.password.update');

    // ================= PROFILE (No Permission) =================
    Route::get('/profile', [ProfileController::class, 'editKasir'])
        ->name('profile.kasir.edit');

    Route::post('/profile', [ProfileController::class, 'updateKasir'])
        ->name('profile.kasir.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA (Super Admin & Admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth:admin')->group(function () {

    Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])
        ->name('admin.dashboard');

    // ================= LOGOUT ADMIN =================
    Route::post('/logout', [AuthWebController::class, 'adminLogout'])
        ->name('admin.logout');

    // ================= USER MANAGER (Super Admin Only) =================
    Route::prefix('manager')
        ->name('manager.')
        ->middleware(['auth:admin'])
        ->group(function () {
            Route::get('/', [UserManagerController::class, 'index'])->name('index');
            Route::get('/akses/{user_type}/{user_id}', [UserManagerController::class, 'aksesUser'])->name('akses');
            Route::post('/role/hak-akses/quick', [UserManagerController::class, 'quickSaveHakRole'])
            ->name('role.hak.quick');
            
            // ✅ ADMIN ROUTES
            Route::prefix('admin')->name('admin.')->group(function() {
                Route::get('/create', function () {
                    $roles = \App\Models\Role::all();
                    return view('manager.admin.create', compact('roles'));
                })->name('create');
                Route::post('/', [AuthWebController::class, 'storeAdmin'])->name('store');
                Route::get('/{id}/edit', [UserManagerController::class, 'editAdmin'])->name('edit');
                Route::put('/{id}', [UserManagerController::class, 'updateAdmin'])->name('update');
            });
            
            // ✅ KASIR ROUTES
            Route::prefix('kasir')->name('kasir.')->group(function() {
                Route::get('/create', function () {
                    return view('manager.kasir.create');
                })->name('create');
                Route::post('/', [UserManagerController::class, 'storeKasir'])->name('store');
                Route::get('/{id}/edit', [UserManagerController::class, 'editKasir'])->name('edit');
                Route::put('/{id}', [UserManagerController::class, 'updateKasir'])->name('update');
            });
            
            // ✅ DRIVER ROUTES
            Route::prefix('driver')->name('driver.')->group(function() {
                Route::get('/create', [UserManagerController::class, 'createDriver'])->name('create');
                Route::post('/', [UserManagerController::class, 'storeDriver'])->name('store');
                Route::get('/{id}/edit', [UserManagerController::class, 'editDriver'])->name('edit');
                Route::put('/{id}', [UserManagerController::class, 'updateDriver'])->name('update');
                Route::delete('/{id}', [UserManagerController::class, 'destroyDriver'])->name('destroy');
            });
            
            // ✅ UPDATE STATUS (untuk Admin, Kasir, Driver)
            Route::post('/status', [UserManagerController::class, 'updateStatus'])->name('update.status');
            
            // ✅ PERMISSION & ROLE
            Route::post('/permission/user', [UserManagerController::class, 'saveUserPermission'])
                ->name('permission.save.user');
            Route::get('/menu-role/create', [UserManagerController::class, 'create'])->name('create');
            Route::post('/menu-role', [UserManagerController::class, 'store'])->name('store');
            Route::put('/menu-role/{id}', [UserManagerController::class, 'update'])->name('update');
            Route::delete('/menu-role/{id}', [UserManagerController::class, 'destroy'])->name('destroy');
            Route::get('/role/hak-akses', [UserManagerController::class, 'hakRole'])->name('role.hak');
            Route::post('/role/hak-akses', [UserManagerController::class, 'saveHakRole'])->name('role.hak.save');
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

    // ================= TRANSAKSI (ADMIN) - WITH PERMISSION =================
    Route::prefix('transaksi')->name('transaksi.')->group(function () {
        // View - butuh permission:view untuk akses halaman transaksi
        Route::get('/pelanggan', [TransaksiController::class, 'pilihPelanggan'])
            ->middleware('permission:view')
            ->name('pelanggan');
        
        Route::get('/create', [TransaksiController::class, 'create'])
            ->middleware('permission:view')
            ->name('create');
        
        Route::get('/checkout', [TransaksiController::class, 'confirm'])
            ->middleware('permission:view')
            ->name('confirm');
        
        Route::get('/print/{id}', [TransaksiController::class, 'print'])
            ->middleware('permission:view')
            ->name('print');
        
        // Add/Create - butuh permission:add untuk membuat transaksi baru
        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelanggan'])
            ->middleware('permission:add')
            ->name('setPelanggan');
        
        Route::post('/checkout', [TransaksiController::class, 'checkout'])
            ->middleware('permission:add')
            ->name('checkout');
        
        Route::post('/bayar', [TransaksiController::class, 'bayar'])
            ->middleware('permission:add')
            ->name('bayar');
        
        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayanan'])
            ->middleware('permission:add')
            ->name('addLayanan');
        
        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenis'])
            ->middleware('permission:add')
            ->name('addJenis');
        
        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayanan'])
            ->middleware('permission:add')
            ->name('temp_store_layanan');
        
        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeterangan'])
            ->middleware('permission:edit')
            ->name('updateKeterangan');
        
        // Delete - butuh permission:delete
        Route::post('/remove/{id}', [TransaksiController::class, 'remove'])
            ->middleware('permission:delete')
            ->name('remove');
        
        // Reset (no permission needed)
        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan', 'keterangan_transaksi']);
            return redirect()->route('admin.dashboard');
        })->name('reset');
    });

    // ================= LAYANAN =================
    Route::prefix('layanan')
        ->name('layanan.')
        ->group(function () {
            Route::get('/', [LayananController::class, 'index'])->name('index');
            Route::get('/create', [LayananController::class, 'create'])->name('create');
            Route::post('/store', [LayananController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [LayananController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LayananController::class, 'update'])->name('update');
            Route::delete('/{id}', [LayananController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/duplicate', [LayananController::class, 'duplicate'])->name('duplicate');

            // JENIS LAYANAN
            Route::get('/{id_layanan}/jenis/create', [LayananController::class, 'createJenis'])->name('jenis.create');
            Route::post('/{id_layanan}/jenis/store', [LayananController::class, 'storeJenis'])->name('jenis.store');
            Route::get('/jenis/{id_jenis}/edit', [LayananController::class, 'editJenis'])->name('jenis.edit');
            Route::put('/jenis/{id_jenis}', [LayananController::class, 'updateJenis'])->name('jenis.update');
            Route::delete('/jenis/{id_jenis}', [LayananController::class, 'destroyJenis'])->name('jenis.destroy');
        });

    // ================= PESANAN ONLINE (With Permission) =================
    Route::prefix('pesanan-online')->name('pesanan.online.')->group(function () {
        Route::post('/{id}/check-terlambat-manual', [PesananOnlineController::class, 'manualCheckTerlambat'])
        ->middleware('permission:edit')
        ->name('check-terlambat-manual');
        // Index & Detail
        Route::get('/', [PesananOnlineController::class, 'index'])
            ->middleware('permission:view')
            ->name('index');
        Route::get('/{id}/detail', [PesananOnlineController::class, 'detail'])
            ->middleware('permission:view')
            ->name('detail');
        
        // Update & Konfirmasi
        Route::put('/{id}/update-data', [PesananOnlineController::class, 'updateData'])
            ->middleware('permission:edit')
            ->name('updateData');
        Route::post('/{id}/konfirmasi', [PesananOnlineController::class, 'konfirmasiPesanan'])
            ->middleware('permission:edit')
            ->name('konfirmasi');
        
        // Status Management
        Route::get('/{id}/proses', [PesananOnlineController::class, 'proses'])
            ->middleware('permission:edit')
            ->name('proses');
        Route::get('/{id}/selesai-di-cuci', [PesananOnlineController::class, 'selesaiDiCuci'])
            ->middleware('permission:edit')
            ->name('selesai_di_cuci');
        Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesai'])
            ->middleware('permission:edit')
            ->name('selesai');
        Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbil'])
            ->middleware('permission:edit')
            ->name('siap_di_ambil');
        Route::get('/{id}/siap-di-antar', [PesananOnlineController::class, 'siapDiAntar'])
            ->middleware('permission:edit')
            ->name('siap_di_antar');
        Route::get('/{id}/tolak', [PesananOnlineController::class, 'tolak'])
            ->middleware('permission:edit')
            ->name('tolak');

        // Payment & Delete
        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayar'])
            ->middleware('permission:edit')
            ->name('bayar');
        Route::delete('/{id}', [PesananOnlineController::class, 'destroy'])
            ->middleware('permission:delete')
            ->name('destroy');

        // Bukti Pembayaran
        Route::get('/{id}/bukti-pembayaran', [PesananOnlineController::class, 'buktiPembayaran'])
            ->name('bukti-pembayaran');
        Route::put('/{id}/simpan-bukti-pembayaran', [PesananOnlineController::class, 'simpanBuktiPembayaran'])
            ->middleware('permission:edit')
            ->name('simpan-bukti-pembayaran');

        // Masukkan Antrian
        Route::put('/{id}/masukkan-antrian', [PesananOnlineController::class, 'masukkanAntrian'])
            ->middleware('permission:edit')
            ->name('masukkan-antrian');

        // Delivery
        Route::get('/delivery', [PesananOnlineController::class, 'listDeliveryOnline'])
            ->name('delivery');
        Route::get('/{id}/list-driver', [PesananOnlineController::class, 'listDriver'])
            ->name('list-driver');
        
        // Assign Driver
        Route::post('/{id}/assign-driver-pickup', [PesananOnlineController::class, 'assignDriverPickup'])
            ->middleware('permission:edit')
            ->name('assign-driver-pickup');
        Route::post('/{id}/assign-driver-antar', [PesananOnlineController::class, 'assignDriverAntar'])
            ->middleware('permission:edit')
            ->name('assign-driver-antar');
        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriver'])
            ->middleware('permission:edit')
            ->name('assign-driver');
        
        // Driver Arrive
        Route::get('/{id}/driver-arrive', [PesananOnlineController::class, 'driverArriveAtLaundry'])
            ->middleware('permission:edit')
            ->name('driver-arrive');

        // ✅ FCM Notification
        Route::post('/{id}/send-fcm-notification', [PesananOnlineController::class, 'sendFcmNotification'])
            ->middleware('permission:edit')
            ->name('send-fcm-notification');
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
        
        // Status changes
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
        
        // Layanan
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
       
        // ✅ FCM Notification & Assign Driver
        Route::post('/{id}/send-fcm-notification', [PesananOnlineController::class, 'sendFcmNotification'])
            ->middleware('permission:edit')
            ->name('send-fcm-notification');

        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriver'])
            ->middleware('permission:edit')
            ->name('assign-driver');
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
        Route::get('/pengeluaran', [LaporanController::class, 'pengeluaranIndex'])->name('pengeluaran.index');
        Route::get('/pengeluaran/export', [LaporanController::class, 'exportPengeluaran'])->name('pengeluaran.export'); // ← TAMBAH INI

        Route::get('/kasir', [LaporanController::class, 'kasirIndex'])->name('kasir.index');
        Route::get('/kasir/export', [LaporanController::class, 'exportKasir'])->name('kasir.export'); // ← Tambahkan ini
    });

    // ================= PENGATURAN (ADMIN) - ✅ WITH PERMISSION =================
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        
        // View routes - butuh permission:view
        Route::get('/', [PengaturanController::class, 'index'])
            ->middleware('permission:view')
            ->name('index');
        
        Route::get('/metode-bayar', [PengaturanController::class, 'metodeBayar'])
            ->middleware('permission:view')
            ->name('metode');
        
        Route::get('/backups/list', [PengaturanController::class, 'listBackups'])
            ->middleware('permission:view')
            ->name('backups.list');
        
        Route::get('/backups/download/{filename}', [PengaturanController::class, 'downloadBackup'])
            ->middleware('permission:view')
            ->name('backups.download');
        
        // Edit/Update routes - butuh permission:edit
        Route::post('/update', [PengaturanController::class, 'update'])
            ->middleware('permission:edit')
            ->name('update');
        
        Route::post('/omzet', [PengaturanController::class, 'updateOmzet'])
            ->middleware('permission:edit')
            ->name('omzet');
        
        Route::post('/backup', [PengaturanController::class, 'backup'])
            ->middleware('permission:edit')
            ->name('backup');
        
        Route::post('/restore', [PengaturanController::class, 'restore'])
            ->middleware('permission:edit')
            ->name('restore');
        
        // Add routes - butuh permission:add
        Route::post('/metode-bayar/store', [PengaturanController::class, 'storeMetodeBayar'])
            ->middleware('permission:add')
            ->name('metode.store');
        
        // Delete routes - butuh permission:delete
        Route::delete('/backups/delete/{filename}', [PengaturanController::class, 'deleteBackup'])
            ->middleware('permission:delete')
            ->name('backups.delete');
        
        Route::delete('/metode-bayar/{id}', [PengaturanController::class, 'deleteMetodeBayar'])
            ->middleware('permission:delete')
            ->name('metode.delete');
    });

// ================= LAPORAN ADMIN (View Permission Only) =================
Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/', [LaporanController::class, 'laporanIndex'])
        ->middleware('permission:view')
        ->name('index');
    
    // Transaksi
    Route::get('/transaksi/index', [LaporanController::class, 'transaksiIndex'])
        ->middleware('permission:view')
        ->name('transaksi.index');
    Route::post('/transaksi/export', [LaporanController::class, 'exportTransaksi'])
        ->name('transaksi.export');
    
    // Kasir
    Route::get('/kasir/index', [LaporanController::class, 'kasirIndex'])
        ->middleware('permission:view')
        ->name('kasir.index');
    Route::get('/kasir/export', [LaporanController::class, 'exportKasir'])
        ->name('kasir.export');
    
    // Bayar
    Route::get('/bayar/index', [LaporanController::class, 'bayarIndex'])
        ->middleware('permission:view')
        ->name('bayar.index');
    
    // Pengeluaran
    Route::get('/pengeluaran/index', [LaporanController::class, 'pengeluaranIndex'])
        ->middleware('permission:view')
        ->name('pengeluaran.index');
    Route::get('/pengeluaran/export', [LaporanController::class, 'exportPengeluaran'])
        ->name('pengeluaran.export');
    
    Route::get('/satuan/export', [LaporanController::class, 'exportSatuanKasir'])->name('satuan.export');
    // Satuan
    Route::get('/satuan/index', [LaporanController::class, 'satuanIndex'])
        ->middleware('permission:view')
        ->name('satuan.index');
    
    // Pelanggan
    Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndex'])
        ->middleware('permission:view')
        ->name('pelanggan.index');
    Route::get('/pelanggan/export', [LaporanController::class, 'exportPelanggan'])
        ->name('pelanggan.export');
    
    // Driver
    Route::get('/driver/index', [LaporanController::class, 'driver'])
        ->middleware('permission:view')
        ->name('driver.index');
    Route::get('/driver/export', [LaporanController::class, 'exportDriverAdmin2'])->name('driver.export');

     // Bayar
    Route::get('/bayar/index', [LaporanController::class, 'bayarIndex'])
        ->middleware('permission:view')
        ->name('bayar.index');
    Route::get('/bayar/export', [LaporanController::class, 'exportBayar']) // ✅ TAMBAHKAN INI
        ->name('bayar.export');
});

    // ================= CHANGE PASSWORD =================
    Route::get('/change-password', [ChangePasswordController::class, 'index'])
        ->name('change.password');
    
    Route::post('/change-password', [ChangePasswordController::class, 'update'])
        ->name('password.update');

    // ================= PROFILE =================
    Route::get('/profile', [ProfileController::class, 'editAdmin'])
        ->name('profile.admin.edit');

    Route::post('/profile', [ProfileController::class, 'updateAdmin'])
        ->name('profile.admin.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN2 AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin2')->middleware('auth:admin')->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [AuthWebController::class, 'admin2Dashboard'])
        ->name('admin2.dashboard');

    // ================= PELANGGAN (With Permission) =================
    Route::prefix('pelanggan')->name('admin2.pelanggan.')->group(function () {
        Route::get('/', [AuthWebController::class, 'pelangganIndexAdmin2'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [AuthWebController::class, 'createAdmin2'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/store', [AuthWebController::class, 'storeAdmin2'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [AuthWebController::class, 'pelangganEditAdmin2'])
            ->middleware('permission:edit')
            ->name('edit');

        Route::put('/{id}', [AuthWebController::class, 'pelangganUpdateAdmin2'])
            ->middleware('permission:edit')
            ->name('update');

        Route::delete('/{id}', [AuthWebController::class, 'pelangganDestroyAdmin2'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= TRANSAKSI (ADMIN2) - WITH PERMISSION =================
    Route::prefix('transaksi')->name('admin2.transaksi.')->group(function () {
        // View - butuh permission:view untuk akses halaman transaksi
        Route::get('/pelanggan', [TransaksiController::class, 'pelangganAdmin2'])
            ->middleware('permission:view')
            ->name('pelanggan');
        
        Route::get('/create', [TransaksiController::class, 'createAdmin2'])
            ->middleware('permission:view')
            ->name('create');
        
        Route::get('/checkout', [TransaksiController::class, 'confirmAdmin2'])
            ->middleware('permission:view')
            ->name('confirm');
        
        Route::get('/print/{id}', [TransaksiController::class, 'printAdmin2'])
            ->middleware('permission:view')
            ->whereNumber('id')
            ->name('print');
        
        // Add/Create - butuh permission:add untuk membuat transaksi baru
        Route::get('/set-pelanggan/{id}', [TransaksiController::class, 'setPelangganAdmin2'])
            ->middleware('permission:add')
            ->whereNumber('id')
            ->name('setPelanggan');
        
        Route::post('/checkout', [TransaksiController::class, 'checkoutAdmin2'])
            ->middleware('permission:add')
            ->name('checkout');
        
        Route::post('/bayar', [TransaksiController::class, 'bayarAdmin2'])
            ->middleware('permission:add')
            ->name('bayar');
        
        Route::post('/add-layanan/{id}', [TransaksiController::class, 'addLayananAdmin2'])
            ->middleware('permission:add')
            ->whereNumber('id')
            ->name('addLayanan');
        
        Route::post('/add-jenis/{id}', [TransaksiController::class, 'addJenisAdmin2'])
            ->middleware('permission:add')
            ->whereNumber('id')
            ->name('addJenis');
        
        Route::post('/temp-store-layanan', [TransaksiController::class, 'tempStoreLayananAdmin2'])
            ->middleware('permission:add')
            ->name('temp_store_layanan');
        
        Route::post('/update-keterangan', [TransaksiController::class, 'updateKeteranganAdmin2'])
            ->middleware('permission:edit')
            ->name('updateKeterangan');
        
        // Delete - butuh permission:delete
        Route::post('/remove/{id}', [TransaksiController::class, 'removeAdmin2'])
            ->middleware('permission:delete')
            ->whereNumber('id')
            ->name('remove');
        
        // Reset (no permission needed)
        Route::get('/reset', function () {
            session()->forget(['detail_transaksi', 'pelanggan_transaksi', 'keterangan_transaksi']);
            return redirect()->route('admin2.dashboard');
        })->name('reset');
    });

    // ================= LAYANAN =================
    Route::prefix('layanan')->name('admin2.layanan.')->group(function () {
        Route::get('/', [LayananController::class, 'indexAdmin2'])
            ->name('index');

        Route::get('/create', [LayananController::class, 'createAdmin2'])
            ->name('create');

        Route::post('/store', [LayananController::class, 'storeAdmin2'])
            ->name('store');

        Route::get('/{id}/edit', [LayananController::class, 'editAdmin2'])
            ->whereNumber('id')
            ->name('edit');

        Route::put('/{id}', [LayananController::class, 'updateAdmin2'])
            ->whereNumber('id')
            ->name('update');

        Route::delete('/{id}', [LayananController::class, 'destroyAdmin2'])
            ->whereNumber('id')
            ->name('destroy');

        Route::get('/{id}/duplicate', [LayananController::class, 'duplicateAdmin2'])
            ->whereNumber('id')
            ->name('duplicate');

        // ---- JENIS LAYANAN ----
        Route::get('{from}/jenis/add', [LayananController::class, 'sessionCreateJenisAdmin2'])
            ->name('jenis.session.create');

        Route::post('{from}/jenis/store-session', [LayananController::class, 'sessionStoreJenisAdmin2'])
            ->name('jenis.session.store');

        Route::get('{id_layanan}/jenis/create', [LayananController::class, 'createJenisAdmin2'])
            ->name('jenis.create');

        Route::post('{id_layanan}/jenis/store', [LayananController::class, 'storeJenisAdmin2'])
            ->name('jenis.store');

        Route::get('jenis/{id}/edit', [LayananController::class, 'editJenisAdmin2'])
            ->whereNumber('id')
            ->name('jenis.edit');

        Route::put('jenis/{id}', [LayananController::class, 'updateJenisAdmin2'])
            ->whereNumber('id')
            ->name('jenis.update');

        Route::delete('jenis/{id}', [LayananController::class, 'destroyJenisAdmin2'])
            ->whereNumber('id')
            ->name('jenis.destroy');

        Route::post('{from}/jenis/add-edit', [LayananController::class, 'addJenisEditAdmin2'])
            ->name('jenis.add.edit');

        Route::get('/{layanan}/jenis/tambah', function ($layanan) {
            $satuan = \App\Models\Satuan::all();
            $parfum = \App\Models\Parfum::all();
            
            return view('admin2.layanan.tambah_jenis_layanan_edit', [
                'from'       => $layanan,
                'id_layanan' => $layanan,
                'satuan'     => $satuan,
                'parfum'     => $parfum,
            ]);
        })->name('jenis.tambah');
    });

    // ================= PARFUM =================
    Route::prefix('parfum')->name('admin2.parfum.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'indexAdmin2'])
            ->name('index');

        Route::get('/create', [SatuanParfumController::class, 'parfumcreateAdmin2'])
            ->name('create');

        Route::post('/store', [SatuanParfumController::class, 'parfumstoreAdmin2'])
            ->name('store');

        Route::get('/{id}/edit', [SatuanParfumController::class, 'parfumeditAdmin2'])
            ->whereNumber('id')
            ->name('edit');

        Route::put('/{id}', [SatuanParfumController::class, 'parfumupdateAdmin2'])
            ->whereNumber('id')
            ->name('update');

        Route::delete('/{id}', [SatuanParfumController::class, 'parfumdestroyAdmin2'])
            ->whereNumber('id')
            ->name('destroy');
    });

    // ================= SATUAN =================
    Route::prefix('satuan')->name('admin2.satuan.')->group(function () {
        Route::get('/', [SatuanParfumController::class, 'satuanindexAdmin2'])
            ->name('index');

        Route::get('/create', [SatuanParfumController::class, 'satuancreateAdmin2'])
            ->name('create');

        Route::post('/store', [SatuanParfumController::class, 'satuanstoreAdmin2'])
            ->name('store');

        Route::get('/{id}/edit', [SatuanParfumController::class, 'satuaneditAdmin2'])
            ->whereNumber('id')
            ->name('edit');

        Route::put('/{id}', [SatuanParfumController::class, 'satuanupdateAdmin2'])
            ->whereNumber('id')
            ->name('update');

        Route::delete('/{id}', [SatuanParfumController::class, 'satuandestroyAdmin2'])
            ->whereNumber('id')
            ->name('destroy');
    });

    // ================= PESANAN ONLINE (ADMIN2) =================
    Route::prefix('pesanan-online')->name('admin2.pesanan.online.')->group(function () {
        // Index & Detail
        Route::get('/', [PesananOnlineController::class, 'indexAdmin2'])
            ->name('index');
            
        Route::get('/{id}/detail', [PesananOnlineController::class, 'detailAdmin2'])
            ->whereNumber('id')
            ->name('detail');
        
        // Update & Konfirmasi
        Route::put('/{id}/update-data', [PesananOnlineController::class, 'updateDataAdmin2'])
            ->whereNumber('id')
            ->name('updateData');
            
        Route::post('/{id}/konfirmasi', [PesananOnlineController::class, 'konfirmasiPesananAdmin2'])
            ->whereNumber('id')
            ->name('konfirmasi');
        
        // Status Management
        Route::get('/{id}/proses', [PesananOnlineController::class, 'prosesAdmin2'])
            ->whereNumber('id')
            ->name('proses');

        Route::get('/{id}/selesai-di-cuci', [PesananOnlineController::class, 'selesaiDiCuciAdmin2'])
            ->whereNumber('id')
            ->name('selesai_di_cuci');
            
        Route::get('/{id}/selesai', [PesananOnlineController::class, 'selesaiAdmin2'])
            ->whereNumber('id')
            ->name('selesai');
            
        Route::get('/{id}/siap-di-ambil', [PesananOnlineController::class, 'siapDiAmbilAdmin2'])
            ->whereNumber('id')
            ->name('siap_di_ambil');

        Route::get('/{id}/siap-di-antar', [PesananOnlineController::class, 'siapDiAntarAdmin2'])
            ->whereNumber('id')
            ->name('siap_di_antar');
            
        Route::get('/{id}/tolak', [PesananOnlineController::class, 'tolakAdmin2'])
            ->whereNumber('id')
            ->name('tolak');
        
        // Payment & Delete
        Route::post('/{id}/bayar', [PesananOnlineController::class, 'bayarAdmin2'])
            ->whereNumber('id')
            ->name('bayar');
            
        Route::delete('/{id}', [PesananOnlineController::class, 'destroyAdmin2'])
            ->whereNumber('id')
            ->name('destroy');

        // Bukti Pembayaran
        Route::get('/{id}/bukti-pembayaran', [PesananOnlineController::class, 'buktiPembayaranAdmin2'])
            ->name('bukti-pembayaran');
            
        Route::put('/{id}/simpan-bukti-pembayaran', [PesananOnlineController::class, 'simpanBuktiPembayaranAdmin2'])
            ->name('simpan-bukti-pembayaran');

        // Masukkan Antrian
        Route::put('/{id}/masukkan-antrian', [PesananOnlineController::class, 'masukkanAntrianAdmin2'])
            ->name('masukkan-antrian');
        
        // Delivery
        Route::get('/delivery', [PesananOnlineController::class, 'listDeliveryOnlineAdmin2'])
            ->name('delivery');
            
        Route::get('/{id}/list-driver', [PesananOnlineController::class, 'listDriverAdmin2'])
            ->name('list-driver');
        
        // Assign Driver
        Route::post('/{id}/assign-driver-pickup', [PesananOnlineController::class, 'assignDriverPickupAdmin2'])
            ->name('assign-driver-pickup');
            
        Route::post('/{id}/assign-driver-antar', [PesananOnlineController::class, 'assignDriverAntarAdmin2'])
            ->name('assign-driver-antar');
            
        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriverAdmin2'])
            ->name('assign-driver');
        
        // Driver Arrive
        Route::get('/{id}/driver-arrive', [PesananOnlineController::class, 'driverArriveAtLaundryAdmin2'])
            ->name('driver-arrive');

        // ✅ FCM Notification
        Route::post('/{id}/send-fcm-notification', [PesananOnlineController::class, 'sendFcmNotificationAdmin2'])
            ->whereNumber('id')
            ->name('send-fcm-notification');
    });

    // ================= RIWAYAT =================
    Route::prefix('riwayat')->name('admin2.riwayat.')->group(function () {
        Route::get('/', [RiwayatController::class, 'indexAdmin2'])
            ->name('index');
        
        Route::get('/{id}/detail', [RiwayatController::class, 'detailAdmin2'])
            ->whereNumber('id')
            ->name('detail');
        
        Route::get('/{id}/show', [RiwayatController::class, 'showAdmin2'])
            ->whereNumber('id')
            ->name('show');
        
        Route::get('/{id}/edit', [RiwayatController::class, 'editAdmin2'])
            ->whereNumber('id')
            ->name('edit');
        
        Route::put('/{id}', [RiwayatController::class, 'updateAdmin2'])
            ->whereNumber('id')
            ->name('update');
        
        Route::get('/{id}/proses', [RiwayatController::class, 'prosesOrderAdmin2'])
            ->whereNumber('id')
            ->name('proses');
        
        Route::patch('/{id}/batal', [RiwayatController::class, 'batalOrderAdmin2'])
            ->whereNumber('id')
            ->name('batal');
        
        Route::get('/{id}/selesai', [RiwayatController::class, 'selesaiOrderAdmin2'])
            ->whereNumber('id')
            ->name('selesai');
        
        Route::get('/{id}/siap-di-ambil', [RiwayatController::class, 'siapDiAmbilAdmin2'])
            ->whereNumber('id')
            ->name('siap_di_ambil');
        
        Route::delete('/{id}', [RiwayatController::class, 'destroyAdmin2'])
            ->whereNumber('id')
            ->name('destroy');
        
        Route::get('/{id}/add-layanan', [RiwayatController::class, 'addLayananPageAdmin2'])
            ->whereNumber('id')
            ->name('addlayanan');
        
        Route::post('/{id}/add-layanan', [RiwayatController::class, 'addLayananAdmin2'])
            ->whereNumber('id')
            ->name('add_layanan_store');
        
        Route::post('/{id}/bayar', [RiwayatController::class, 'bayarSubmitAdmin2'])
            ->whereNumber('id')
            ->name('bayar.submit');
        
        Route::get('/layanan/{id}/edit', [RiwayatController::class, 'editLayananAdmin2'])
            ->whereNumber('id')
            ->name('edit_layanan');
        
        Route::put('/layanan/{id}', [RiwayatController::class, 'updateLayananAdmin2'])
            ->whereNumber('id')
            ->name('update_layanan');
        
        Route::delete('/layanan/{id}', [RiwayatController::class, 'destroyLayananAdmin2'])
            ->whereNumber('id')
            ->name('layanan.destroy');
        
        Route::put('/detail/{id}/update', [RiwayatController::class, 'updateDetailAdmin2'])
            ->whereNumber('id')
            ->name('update_detail');
        
        Route::delete('/detail/{id}', [RiwayatController::class, 'deleteDetailAdmin2'])
            ->whereNumber('id')
            ->name('delete_detail');

        // ✅ FCM Notification & Assign Driver
        Route::post('/{id}/send-fcm-notification', [PesananOnlineController::class, 'sendFcmNotificationAdmin2'])
            ->whereNumber('id')
            ->name('send-fcm-notification');

        Route::post('/{id}/assign-driver', [PesananOnlineController::class, 'assignDriverAdmin2'])
            ->whereNumber('id')
            ->name('assign-driver');
    });

    // ================= PENGELUARAN =================
    Route::prefix('pengeluaran')->name('admin2.pengeluaran.')->group(function () {
        Route::get('/', [LaporanController::class, 'indexAdmin2'])
            ->middleware('permission:view')
            ->name('index');

        Route::get('/create', [LaporanController::class, 'createAdmin2'])
            ->middleware('permission:add')
            ->name('create');

        Route::post('/', [LaporanController::class, 'storeAdmin2'])
            ->middleware('permission:add')
            ->name('store');

        Route::get('/{id}/edit', [LaporanController::class, 'editAdmin2'])
            ->middleware('permission:edit')
            ->name('edit');

        Route::put('/{id}', [LaporanController::class, 'updateAdmin2'])
            ->middleware('permission:edit')
            ->name('update');

        Route::delete('/{id}', [LaporanController::class, 'destroyAdmin2'])
            ->middleware('permission:delete')
            ->name('destroy');
    });

    // ================= USER MANAGER (ADMIN2) - Hanya Kasir & Driver =================
    Route::prefix('manager')->name('admin2.manager.')->group(function () {
        Route::get('/', [UserManagerController::class, 'indexAdmin2'])
            ->name('index');
        Route::post('/status', [UserManagerController::class, 'updateStatusAdmin2'])
            ->name('update.status');
        
        // ✅ HAK AKSES ROLE KASIR
        Route::get('/role/hak-akses', [UserManagerController::class, 'hakRoleAdmin2'])
            ->name('role.hak');
        Route::post('/role/hak-akses', [UserManagerController::class, 'saveHakRoleAdmin2'])
            ->name('role.hak.save');
        
        // ✅ TAMBAH INI - Quick Save untuk Auto-save
        Route::post('/role/hak-akses/quick', [UserManagerController::class, 'quickSaveHakRoleAdmin2'])
            ->name('role.hak.quick');
        
        // Kasir & Driver routes
        Route::prefix('kasir')->name('kasir.')->group(function () {
            Route::get('/create', [UserManagerController::class, 'createKasirAdmin2'])->name('create');
            Route::post('/', [UserManagerController::class, 'storeKasirAdmin2'])->name('store');
            Route::get('/{id}/edit', [UserManagerController::class, 'editKasirAdmin2'])->name('edit');
            // ✅ TAMBAHKAN ROUTE UPDATE INI
            Route::put('/{id}', [UserManagerController::class, 'updateKasirAdmin2'])->name('update');
        });
        
        Route::prefix('driver')->name('driver.')->group(function () {
            Route::get('/create', [UserManagerController::class, 'createDriverAdmin2'])->name('create');
            Route::post('/', [UserManagerController::class, 'storeDriverAdmin2'])->name('store');
            Route::get('/{id}/edit', [UserManagerController::class, 'editDriverAdmin2'])->name('edit');
            Route::put('/{id}', [UserManagerController::class, 'updateDriverAdmin2'])->name('update');
            Route::delete('/{id}', [UserManagerController::class, 'destroyDriverAdmin2'])->name('destroy');
        });
    });

// ================= LAPORAN ADMIN2 =================
Route::prefix('laporan')->name('admin2.laporan.')->group(function () {
    Route::get('/', [LaporanController::class, 'laporanIndexAdmin2'])->name('index');
    
    // Transaksi
    Route::get('/transaksi/index', [LaporanController::class, 'transaksiIndexAdmin2'])->name('transaksi.index');
    Route::post('/transaksi/export', [LaporanController::class, 'exportTransaksiAdmin2'])->name('transaksi.export');
    
    // Kasir
    Route::get('/kasir/index', [LaporanController::class, 'kasirIndexAdmin2'])->name('kasir.index');
    Route::get('/kasir/export', [LaporanController::class, 'exportKasirAdmin2'])->name('kasir.export');
    
    // Bayar
    Route::get('/bayar/index', [LaporanController::class, 'bayarIndexAdmin2'])->name('bayar.index');
    
    // Pengeluaran
    Route::get('/pengeluaran/index', [LaporanController::class, 'pengeluaranIndexAdmin2'])->name('pengeluaran.index');
    Route::get('/pengeluaran/export', [LaporanController::class, 'exportPengeluaranAdmin2'])->name('pengeluaran.export');
    
    // Satuan
    Route::get('/satuan/index', [LaporanController::class, 'satuanIndexAdmin2'])->name('satuan.index');
    
    // Pelanggan
    Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndexAdmin2'])->name('pelanggan.index');
    Route::get('/pelanggan/export', [LaporanController::class, 'exportPelangganAdmin2'])->name('pelanggan.export');
    
    // Driver
    Route::get('/driver/index', [LaporanController::class, 'driverAdmin2'])->name('driver.index');

     // Kasir
    Route::get('/kasir/index', [LaporanController::class, 'kasirIndexAdmin2'])->name('kasir.index');
    Route::get('/kasir/export', [LaporanController::class, 'exportKasirAdmin2'])->name('kasir.export');
    
    // Metode Bayar
    Route::get('/bayar/index', [LaporanController::class, 'bayarIndexAdmin2'])->name('bayar.index');
    Route::get('/bayar/export', [LaporanController::class, 'exportBayarAdmin2'])->name('bayar.export');
    
    // Pengeluaran
    Route::get('/pengeluaran/index', [LaporanController::class, 'pengeluaranIndexAdmin2'])->name('pengeluaran.index');
    Route::get('/pengeluaran/export', [LaporanController::class, 'exportPengeluaranAdmin2'])->name('pengeluaran.export');
    
    // Satuan
    Route::get('/satuan/index', [LaporanController::class, 'satuanIndexAdmin2'])->name('satuan.index');
    Route::get('/satuan/export', [LaporanController::class, 'exportSatuanAdmin2'])->name('satuan.export');
    
    // Pelanggan
    Route::get('/pelanggan/index', [LaporanController::class, 'pelangganIndexAdmin2'])->name('pelanggan.index');
    Route::get('/pelanggan/export', [LaporanController::class, 'exportPelangganAdmin2'])->name('pelanggan.export');
    
    // Driver
    Route::get('/driver/index', [LaporanController::class, 'driverAdmin2'])->name('driver.index');
    Route::get('/driver/export', [LaporanController::class, 'exportDriverAdmin2'])->name('driver.export');
});

    // ================= PENGATURAN ADMIN2 =================
    Route::prefix('pengaturan')->name('admin2.pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'indexAdmin2'])->name('index');
        Route::post('/update', [PengaturanController::class, 'updateAdmin2'])->name('update');
        Route::post('/omzet', [PengaturanController::class, 'updateOmzetAdmin2'])->name('omzet');
        Route::post('/backup', [PengaturanController::class, 'backupAdmin2'])->name('backup');
        Route::post('/restore', [PengaturanController::class, 'restoreAdmin2'])->name('restore');
        
        Route::get('/backups/list', [PengaturanController::class, 'listBackupsAdmin2'])->name('backups.list');
        Route::get('/backups/download/{filename}', [PengaturanController::class, 'downloadBackupAdmin2'])->name('backup.download');
        Route::delete('/backups/delete/{filename}', [PengaturanController::class, 'deleteBackupAdmin2'])->name('backups.delete');
        
        Route::get('/metode-bayar', [PengaturanController::class, 'metodeBayarAdmin2'])->name('metode');
        Route::post('/metode-bayar/store', [PengaturanController::class, 'storeMetodeBayarAdmin2'])->name('metode.store');
        Route::delete('/metode-bayar/{id}', [PengaturanController::class, 'deleteMetodeBayarAdmin2'])->name('metode.delete');
    });

    // ================= CHANGE PASSWORD =================
    Route::get('/change-password', [ChangePasswordController::class, 'indexAdmin2'])
        ->name('admin2.change.password');
    
    Route::post('/change-password', [ChangePasswordController::class, 'updateAdmin2'])
        ->name('admin2.password.update');

    // ================= PROFILE =================
    Route::get('/profile', [ProfileController::class, 'editAdmin2'])
        ->name('profile.admin2.edit');

    Route::post('/profile', [ProfileController::class, 'updateAdmin2'])
        ->name('profile.admin2.update');
});