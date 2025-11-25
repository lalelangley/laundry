<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\LayananController;
use App\Http\Controllers\Web\SatuanParfumController;
use App\Models\Satuan;

// LOGIN
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthWebController::class, 'processLogin'])->name('login.process');

// LOGOUT
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
    Route::get('/dashboard', [AuthWebController::class, 'adminDashboard'])->name('admin.dashboard');

    // LAYANAN
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::get('/layanan/create', [LayananController::class, 'create'])->name('layanan.create');
    Route::post('/layanan/store', [LayananController::class, 'store'])->name('layanan.store');

    // JENIS LAYANAN
    Route::get('/layanan/jenis/create', [LayananController::class, 'createJenis'])->name('jenis_layanan.create');
    Route::post('/layanan/jenis/store', [LayananController::class, 'storeJenis'])->name('jenis_layanan.store');
});

// SATUAN
Route::get('/satuan', [SatuanParfumController::class, 'satuanIndex'])->name('satuan.index');
Route::get('/satuan/create', [SatuanParfumController::class, 'satuanCreate'])->name('satuan.create');
Route::post('/satuan/store', [SatuanParfumController::class, 'satuanStore'])->name('satuan.store');
Route::post('/satuan/update/{id}', [SatuanParfumController::class, 'satuanUpdate'])->name('satuan.update');
Route::delete('/satuan/delete/{id}', [SatuanParfumController::class, 'satuanDestroy'])->name('satuan.delete');

// PARFUM
Route::get('/parfum', [SatuanParfumController::class, 'parfumIndex'])->name('parfum.index');
Route::get('/parfum/create', [SatuanParfumController::class, 'parfumCreate'])->name('parfum.create');
Route::post('/parfum/store', [SatuanParfumController::class, 'parfumStore'])->name('parfum.store');
Route::post('/parfum/update/{id}', [SatuanParfumController::class, 'parfumUpdate'])->name('parfum.update');
Route::delete('/parfum/delete/{id}', [SatuanParfumController::class, 'parfumDestroy'])->name('parfum.delete');
