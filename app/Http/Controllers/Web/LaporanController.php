<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BladeTableExport;
use App\Exports\TransaksiExport;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

/**
 * LaporanController
 *
 * Controller utama untuk mengelola seluruh fitur laporan dan pengeluaran
 * pada sistem aplikasi laundry berbasis Laravel (MVC / Class-Object).
 *
 * Konsep OOP yang digunakan:
 * - Class          : LaporanController extends Controller (pewarisan / inheritance)
 * - Object         : Instansiasi Pengeluaran, Transaksi, Spreadsheet, Pdf, dll
 * - Method         : Setiap fungsi di bawah adalah method dari class ini
 * - Enkapsulasi    : Method private hanya bisa diakses dari dalam class
 *
 * Mendukung tiga jenis user: Admin (Super Admin), Kasir, dan Admin2 (Admin Biasa).
 *
 * Fitur yang ditangani:
 * - CRUD Pengeluaran (Admin, Kasir, Admin2)
 * - Laporan Transaksi
 * - Laporan Kasir
 * - Laporan Metode Bayar
 * - Laporan Pengeluaran
 * - Laporan Pelanggan
 * - Laporan Satuan
 * - Laporan Driver
 * - Export Excel menggunakan Laravel Excel
 * - Export transaksi memakai class export khusus
 * - Export laporan lain memakai Blade table yang dikirim ke Laravel Excel
 * - Export PDF menggunakan barryvdh/laravel-dompdf
 * - Export CSV menggunakan native PHP fputcsv + response()->stream()
 *
 * Kaitan dengan unit kompetensi:
 * - Unit 1: memanfaatkan query, agregasi, relasi data, dan filter laporan
 * - Unit 3: menunjukkan eksekusi source code dari request sampai file download
 * - Unit 4: menerapkan struktur method yang terpisah untuk query, export, dan helper
 * - Unit 5: memakai array, switch/match, dan perulangan untuk membentuk data laporan
 * - Unit 6: menjadi bukti dokumentasi fungsi/modul program
 * - Unit 7: membantu debugging karena setiap format export dipisah dan mudah diuji
 */
class LaporanController extends Controller
{
    // =========================================
    // PENGELUARAN - ADMIN (SUPER ADMIN)
    // =========================================

    /**
     * [METHOD] index()
     * Menampilkan daftar seluruh pengeluaran untuk Admin.
     *
     * Konsep yang digunakan:
     * - Object   : Pengeluaran::orderBy() → menggunakan Eloquent Model sebagai object
     * - Method   : orderBy(), get() adalah method dari object Eloquent
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // [VALIDASI AKSES] Hanya user dengan izin 'view' pada modul 'pengeluaran' yang bisa masuk
        requirePermission('pengeluaran', 'view');

        // [OBJECT + METHOD] Memanggil method orderBy dan get dari class Pengeluaran (Eloquent Model)
        $pengeluaran = Pengeluaran::query();

        match ($request->get('sort', 'terbaru')) {
            'terlama' => $pengeluaran->orderBy('id_pengeluaran', 'asc'),
            'nominal_tertinggi' => $pengeluaran->orderBy('nominal', 'desc'),
            'nominal_terendah' => $pengeluaran->orderBy('nominal', 'asc'),
            default => $pengeluaran->orderBy('id_pengeluaran', 'desc'),
        };

        $pengeluaran = $pengeluaran
            ->paginate(10)
            ->withQueryString();

        return view('pengeluaran.index', compact('pengeluaran'));
    }

    /**
     * [METHOD] create()
     * Menampilkan form tambah pengeluaran untuk Admin.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // [VALIDASI AKSES] Hanya user dengan izin 'add' pada modul 'pengeluaran'
        requirePermission('pengeluaran', 'add');

        return view('pengeluaran.create');
    }

    /**
     * [METHOD] store()
     * Menyimpan data pengeluaran baru ke database (Admin).
     *
     * Konsep yang digunakan:
     * - Array      : $request->all() mengandung data array dari form
     * - Object     : Pengeluaran::create() memanggil method create dari class Eloquent
     * - Method     : create() adalah method static dari Eloquent Model
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // [VALIDASI AKSES] Hanya user dengan izin 'add' pada modul 'pengeluaran'
        requirePermission('pengeluaran', 'add');

        /**
         * [ARRAY] Data yang dikirim ke method create() berbentuk array asosiatif.
         * Array asosiatif adalah array dengan key => value (bukan index numerik).
         * Contoh: ['nama_pengeluaran' => 'Listrik', 'nominal' => 50000, ...]
         */
        Pengeluaran::create([
            'nama_pengeluaran'    => $request->nama_pengeluaran,   // key => value
            'nominal'             => $request->nominal,             // key => value
            'catatan'             => $request->catatan,             // key => value
            'tanggal_pengeluaran' => $request->tanggal,             // key => value
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * [METHOD] edit()
     * Menampilkan form edit pengeluaran untuk Admin.
     *
     * Konsep yang digunakan:
     * - Object     : Pengeluaran::findOrFail() mengembalikan object model Pengeluaran
     * - Error Handling : findOrFail() otomatis melempar exception 404 jika data tidak ditemukan
     *
     * @param  int  $id  ID pengeluaran yang akan diedit
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // [VALIDASI AKSES] Hanya user dengan izin 'edit' pada modul 'pengeluaran'
        requirePermission('pengeluaran', 'edit');

        /**
         * [OBJECT + ERROR HANDLING]
         * findOrFail() adalah method Eloquent yang:
         * 1. Mencari data berdasarkan primary key ($id)
         * 2. Jika ditemukan → mengembalikan object Pengeluaran
         * 3. Jika tidak ditemukan → otomatis melempar ModelNotFoundException (HTTP 404)
         * Ini adalah bentuk penanganan error/galat bawaan Laravel.
         */
        $item = Pengeluaran::findOrFail($id);

        return view('pengeluaran.edit', compact('item'));
    }

    /**
     * [METHOD] update()
     * Memperbarui data pengeluaran di database (Admin).
     *
     * Konsep yang digunakan:
     * - Array      : Data update dikirim sebagai array asosiatif
     * - Object     : $item adalah object dari class Pengeluaran
     * - Method     : update() adalah method dari object $item
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  ID pengeluaran yang akan diupdate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // [VALIDASI AKSES]
        requirePermission('pengeluaran', 'edit');

        // [OBJECT] $item adalah instance (object) dari class Pengeluaran
        $item = Pengeluaran::findOrFail($id);

        /**
         * [ARRAY + METHOD] method update() menerima array asosiatif sebagai parameter.
         * update() akan mengupdate kolom sesuai key yang diberikan.
         */
        $item->update([
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'nominal'             => $request->nominal,
            'catatan'             => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    /**
     * [METHOD] destroy()
     * Menghapus data pengeluaran dari database (Admin).
     *
     * Konsep yang digunakan:
     * - Object       : $item adalah object Pengeluaran
     * - Method       : delete() adalah method dari object Eloquent
     * - Error Handle : findOrFail() melempar 404 jika data tidak ada
     *
     * @param  int  $id  ID pengeluaran yang akan dihapus
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // [VALIDASI AKSES]
        requirePermission('pengeluaran', 'delete');

        // [OBJECT + ERROR HANDLING] findOrFail melempar 404 jika tidak ditemukan
        $item = Pengeluaran::findOrFail($id);

        // [METHOD] delete() adalah method dari object Eloquent untuk menghapus record
        $item->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================================
    // PENGELUARAN - KASIR
    // =========================================

    /**
     * [METHOD] indexKasir()
     * Menampilkan daftar pengeluaran untuk Kasir.
     * Data diurutkan berdasarkan tanggal pengeluaran dan waktu created_at secara descending.
     *
     * @return \Illuminate\View\View
     */
    public function indexKasir(Request $request)
    {
        requirePermission('pengeluaran', 'view');

        $pengeluaran = Pengeluaran::query();

        match ($request->get('sort', 'terbaru')) {
            'terlama' => $pengeluaran->orderBy('tanggal_pengeluaran', 'asc')->orderBy('created_at', 'asc'),
            'nominal_tertinggi' => $pengeluaran->orderBy('nominal', 'desc'),
            'nominal_terendah' => $pengeluaran->orderBy('nominal', 'asc'),
            default => $pengeluaran->orderBy('tanggal_pengeluaran', 'desc')->orderBy('created_at', 'desc'),
        };

        $pengeluaran = $pengeluaran
            ->paginate(10)
            ->withQueryString();

        return view('kasir.pengeluaran.index', compact('pengeluaran'));
    }

    /**
     * [METHOD] createKasir()
     * Menampilkan form tambah pengeluaran untuk Kasir.
     *
     * @return \Illuminate\View\View
     */
    public function createKasir()
    {
        requirePermission('pengeluaran', 'add');

        return view('kasir.pengeluaran.create');
    }

    /**
     * [METHOD] storeKasir()
     * Menyimpan data pengeluaran baru ke database (Kasir).
     *
     * Konsep yang digunakan:
     * - Array        : Data form dikirim sebagai array ke create()
     * - Validasi form: $request->validate() memastikan input sesuai aturan
     * - Error Handle : Jika validasi gagal, Laravel otomatis melempar ValidationException
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeKasir(Request $request)
    {
        requirePermission('pengeluaran', 'add');

        /**
         * [VALIDASI FORM + ERROR HANDLING]
         * validate() memeriksa input sesuai aturan yang didefinisikan dalam array.
         * Jika validasi gagal → Laravel melempar ValidationException dan
         * mengarahkan kembali ke form dengan pesan error (penanganan galat otomatis).
         *
         * Aturan validasi (array asosiatif):
         * - 'required'     : field wajib diisi
         * - 'string'       : harus berupa teks
         * - 'max:255'      : maksimal 255 karakter
         * - 'numeric'      : harus berupa angka
         * - 'date'         : harus berupa format tanggal yang valid
         */
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255', // wajib, string, maks 255 karakter
            'nominal'          => 'required|numeric',         // wajib, harus angka
            'tanggal'          => 'required|date',            // wajib, harus format tanggal
        ]);

        // [ARRAY] Data dikirim sebagai array asosiatif ke method create()
        Pengeluaran::create([
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'nominal'             => $request->nominal,
            'catatan'             => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * [METHOD] editKasir()
     * Menampilkan form edit pengeluaran untuk Kasir.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editKasir($id)
    {
        requirePermission('pengeluaran', 'edit');

        $item = Pengeluaran::findOrFail($id);
        return view('kasir.pengeluaran.edit', compact('item'));
    }

    /**
     * [METHOD] updateKasir()
     * Memperbarui data pengeluaran di database (Kasir).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateKasir(Request $request, $id)
    {
        requirePermission('pengeluaran', 'edit');

        // [VALIDASI FORM + ERROR HANDLING] Validasi input sebelum update
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal'          => 'required|numeric',
            'tanggal'          => 'required|date',
        ]);

        $item = Pengeluaran::findOrFail($id);

        // [ARRAY] Data update dikirim sebagai array asosiatif
        $item->update([
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'nominal'             => $request->nominal,
            'catatan'             => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    /**
     * [METHOD] destroyKasir()
     * Menghapus data pengeluaran dari database (Kasir).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyKasir($id)
    {
        requirePermission('pengeluaran', 'delete');

        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================================
    // PENGELUARAN - ADMIN2 (ADMIN BIASA)
    // =========================================

    /**
     * [METHOD] indexAdmin2()
     * Menampilkan daftar pengeluaran untuk Admin2.
     *
     * @return \Illuminate\View\View
     */
    public function indexAdmin2(Request $request)
    {
        requirePermission('pengeluaran', 'view');

        $pengeluaran = Pengeluaran::query();

        match ($request->get('sort', 'terbaru')) {
            'terlama' => $pengeluaran->orderBy('tanggal_pengeluaran', 'asc')->orderBy('created_at', 'asc'),
            'nominal_tertinggi' => $pengeluaran->orderBy('nominal', 'desc'),
            'nominal_terendah' => $pengeluaran->orderBy('nominal', 'asc'),
            default => $pengeluaran->orderBy('tanggal_pengeluaran', 'desc')->orderBy('created_at', 'desc'),
        };

        $pengeluaran = $pengeluaran
            ->paginate(10)
            ->withQueryString();

        return view('admin2.pengeluaran.index', compact('pengeluaran'));
    }

    /**
     * [METHOD] createAdmin2()
     * Menampilkan form tambah pengeluaran untuk Admin2.
     *
     * @return \Illuminate\View\View
     */
    public function createAdmin2()
    {
        requirePermission('pengeluaran', 'add');

        return view('admin2.pengeluaran.create');
    }

    /**
     * [METHOD] storeAdmin2()
     * Menyimpan data pengeluaran baru ke database (Admin2).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAdmin2(Request $request)
    {
        requirePermission('pengeluaran', 'add');

        // [VALIDASI FORM + ERROR HANDLING]
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal'          => 'required|numeric',
            'tanggal'          => 'required|date',
        ]);

        // [ARRAY] Data dikirim sebagai array asosiatif
        Pengeluaran::create([
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'nominal'             => $request->nominal,
            'catatan'             => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * [METHOD] editAdmin2()
     * Menampilkan form edit pengeluaran untuk Admin2.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function editAdmin2($id)
    {
        requirePermission('pengeluaran', 'edit');

        $item = Pengeluaran::findOrFail($id);
        return view('admin2.pengeluaran.edit', compact('item'));
    }

    /**
     * [METHOD] updateAdmin2()
     * Memperbarui data pengeluaran di database (Admin2).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdmin2(Request $request, $id)
    {
        requirePermission('pengeluaran', 'edit');

        // [VALIDASI FORM]
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal'          => 'required|numeric',
            'tanggal'          => 'required|date',
        ]);

        $item = Pengeluaran::findOrFail($id);

        // [ARRAY] Data update dikirim sebagai array asosiatif
        $item->update([
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'nominal'             => $request->nominal,
            'catatan'             => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    /**
     * [METHOD] destroyAdmin2()
     * Menghapus data pengeluaran dari database (Admin2).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAdmin2($id)
    {
        requirePermission('pengeluaran', 'delete');

        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================================
    // LAPORAN INDEX (Dashboard Laporan)
    // =========================================

    /**
     * [METHOD] laporanIndex()
     * Menampilkan halaman utama/dashboard laporan untuk Admin.
     *
     * @return \Illuminate\View\View
     */
    public function laporanIndex()
    {
        requirePermission('laporan', 'view');

        return view('laporan.index');
    }

    /**
     * [METHOD] laporanIndexKasir()
     * Menampilkan halaman utama/dashboard laporan untuk Kasir.
     *
     * @return \Illuminate\View\View
     */
    public function laporanIndexKasir()
    {
        requirePermission('laporan', 'view');

        return view('kasir.laporan.index');
    }

    /**
     * [METHOD] laporanIndexAdmin2()
     * Menampilkan halaman utama/dashboard laporan untuk Admin2.
     *
     * @return \Illuminate\View\View
     */
    public function laporanIndexAdmin2()
    {
        requirePermission('laporan', 'view');

        return view('admin2.laporan.index');
    }

    // =========================================
    // LAPORAN TRANSAKSI
    // =========================================

    /**
     * [METHOD] transaksiIndex()
     * Menampilkan laporan transaksi dengan filter, pencarian, dan sorting (Admin).
     *
     * Konsep yang digunakan:
     * - Percabangan (if)     : Untuk mengecek apakah filter aktif atau tidak
     * - Percabangan (switch) : Untuk menentukan jenis sorting yang dipilih
     * - Array                : $request->status, $request->bayar, $request->jenis berupa array input
     * - Object               : $query adalah object Builder dari Eloquent
     * - Method               : with(), whereBetween(), where(), whereIn(), paginate() adalah method chaining
     *
     * Filter yang tersedia:
     * - Rentang tanggal (dari - sampai), default: 1 bulan terakhir
     * - Pencarian berdasarkan nama pelanggan, no HP, atau ID transaksi
     * - Status transaksi (antrian, proses, selesai, dll)
     * - Status pembayaran (lunas, belum lunas, DP)
     * - Jenis transaksi
     * - Sorting (terbaru, terlama, nominal tertinggi/terendah)
     *
     * Catatan: Query dijalankan dua kali — satu untuk paginasi, satu untuk menghitung total omzet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function transaksiIndex(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Menggunakan operator null coalescing (??) sebagai percabangan ringkas.
        // Jika $request->dari tidak ada/null → gunakan nilai default (1 bulan lalu)
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        /**
         * [OBJECT + METHOD] $query adalah object dari Query Builder Eloquent.
         * with() → method untuk eager loading relasi (menghindari N+1 query problem).
         * whereBetween() → method filter rentang tanggal.
         */
        $query = Transaksi::with(['pelanggan', 'metodeBayar', 'kasir'])
            ->whereBetween('tgl_transaksi', [
                $tglAwal . ' 00:00:00',
                $tglAkhir . ' 23:59:59'
            ]);

        /**
         * [PERCABANGAN - if] Cek apakah parameter pencarian 'q' dikirim.
         * filled() mengembalikan true jika field tidak kosong dan tidak null.
         */
        if ($request->filled('q')) {
            $q = $request->q;
            // [PERCABANGAN - closure] Grouping kondisi OR dalam WHERE
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_pelanggan', 'like', "%$q%")
                    ->orWhere('no_hp', 'like', "%$q%")
                    ->orWhere('id_transaksi', 'like', "%$q%");
            });
        }

        /**
         * [PERCABANGAN - if] Filter status transaksi.
         * [ARRAY] $request->status adalah array berisi satu atau lebih nilai status
         * yang dikirim dari form (checkbox multiple). Contoh: ['selesai', 'proses']
         * whereIn() menerima array sebagai parameter kedua.
         */
        if ($request->filled('status')) {
            $query->whereIn('status_transaksi', $request->status); // $request->status = array
        }

        // [PERCABANGAN - if] Filter status pembayaran (array dari form)
        if ($request->filled('bayar')) {
            $query->whereIn('status_bayar', $request->bayar); // $request->bayar = array
        }

        // [PERCABANGAN - if] Filter jenis transaksi (array dari form)
        if ($request->filled('jenis')) {
            $query->whereIn('jenis_transaksi', $request->jenis); // $request->jenis = array
        }

        /**
         * [PERCABANGAN - switch] Menentukan jenis pengurutan data.
         * switch lebih efisien dari if-elseif beruntun untuk kondisi dengan banyak nilai.
         * Default: terbaru (jika parameter tidak dikenali atau tidak dikirim).
         */
        $sortBy = $request->sort ?? 'terbaru'; // [PERCABANGAN] default sort = 'terbaru'
        switch ($sortBy) {
            case 'terlama':
                $query->orderBy('tgl_transaksi', 'ASC');
                break;
            case 'nominal_tertinggi':
                $query->orderBy('total_bayar', 'DESC');
                break;
            case 'nominal_terendah':
                $query->orderBy('total_bayar', 'ASC');
                break;
            case 'terbaru':
            default:
                $query->orderBy('tgl_transaksi', 'DESC');
        }

        /**
         * [METHOD + PAGING] paginate(10) membagi hasil query menjadi halaman-halaman
         * dengan 10 data per halaman. withQueryString() mempertahankan parameter filter
         * di URL saat berpindah halaman → mendukung fitur paging pada tampilan data.
         */
        $transaksi = $query->paginate(10)->withQueryString();

        /**
         * [PERCABANGAN + ARRAY] Query kedua untuk menghitung total omzet dan jumlah transaksi
         * dari SEMUA halaman (bukan hanya halaman aktif).
         * Filter yang sama diterapkan kembali agar total konsisten dengan data yang ditampilkan.
         */
        $queryTotal = Transaksi::whereBetween('tgl_transaksi', [
            $tglAwal . ' 00:00:00',
            $tglAkhir . ' 23:59:59'
        ]);

        // [PERCABANGAN] Terapkan filter yang sama pada queryTotal
        if ($request->filled('q')) {
            $q = $request->q;
            $queryTotal->where(function ($sub) use ($q) {
                $sub->where('nama_pelanggan', 'like', "%$q%")
                    ->orWhere('no_hp', 'like', "%$q%")
                    ->orWhere('id_transaksi', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            $queryTotal->whereIn('status_transaksi', $request->status);
        }

        if ($request->filled('bayar')) {
            $queryTotal->whereIn('status_bayar', $request->bayar);
        }

        if ($request->filled('jenis')) {
            $queryTotal->whereIn('jenis_transaksi', $request->jenis);
        }

        /**
         * [ARRAY] Data dikirim ke view menggunakan array asosiatif.
         * Setiap key pada array ini akan menjadi variabel yang bisa diakses di view.
         */
        return view('laporan.transaksi.index', [
            'transaksi'  => $transaksi,
            'tglAwal'    => $tglAwal,
            'tglAkhir'   => $tglAkhir,
            'totalOmzet' => $queryTotal->sum('total_bayar'), // total semua halaman
            'jumlah'     => $queryTotal->count(),            // jumlah semua halaman
        ]);
    }

    /**
     * [METHOD] transaksiIndexKasir()
     * Menampilkan laporan transaksi untuk Kasir.
     * Logika identik dengan transaksiIndex(), hanya berbeda view yang dituju.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function transaksiIndexKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        // [VALIDASI AKSES] Memastikan hanya role yang memiliki izin laporan
        // yang dapat mengakses data transaksi versi kasir.

        // [PERCABANGAN] Default tanggal jika tidak ada input
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        /**
         * [CLASS-OBJECT + METHOD]
         * Transaksi::with(...) menghasilkan object query builder.
         * Object ini dipakai untuk chaining method filter dan sorting.
         */
        $query = Transaksi::with(['pelanggan', 'metodeBayar', 'kasir'])
            ->whereBetween('tgl_transaksi', [
                $tglAwal . ' 00:00:00',
                $tglAkhir . ' 23:59:59'
            ]);

        // [PERCABANGAN + METHOD] Filter pencarian teks berdasarkan beberapa kolom.
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_pelanggan', 'like', "%$q%")
                    ->orWhere('no_hp', 'like', "%$q%")
                    ->orWhere('id_transaksi', 'like', "%$q%");
            });
        }

        // [ARRAY] request('status') berupa array checkbox dari form filter.
        // [PERCABANGAN] whereIn dijalankan hanya jika array tersebut terisi.
        if ($request->filled('status')) {
            $query->whereIn('status_transaksi', $request->status);
        }

        // [ARRAY] request('bayar') berisi multi pilihan status pembayaran.
        if ($request->filled('bayar')) {
            $query->whereIn('status_bayar', $request->bayar);
        }

        // [ARRAY] request('jenis') berisi pilihan jenis transaksi online/offline.
        if ($request->filled('jenis')) {
            $query->whereIn('jenis_transaksi', $request->jenis);
        }

        // [PERCABANGAN - switch] Sorting data berdasarkan pilihan user.
        $sortBy = $request->sort ?? 'terbaru';
        switch ($sortBy) {
            case 'terlama':
                $query->orderBy('tgl_transaksi', 'ASC');
                break;
            case 'nominal_tertinggi':
                $query->orderBy('total_bayar', 'DESC');
                break;
            case 'nominal_terendah':
                $query->orderBy('total_bayar', 'ASC');
                break;
            case 'terbaru':
            default:
                $query->orderBy('tgl_transaksi', 'DESC');
        }

        // [PAGING] paginate(10) membagi data menjadi beberapa halaman.
        // withQueryString() menjaga filter tetap ada saat page berpindah.
        $transaksi = $query->paginate(10)->withQueryString();

        /**
         * [OBJECT + METHOD]
         * Query kedua digunakan khusus untuk menghitung total omzet
         * dan jumlah transaksi seluruh hasil filter, bukan hanya page aktif.
         */
        $queryTotal = Transaksi::whereBetween('tgl_transaksi', [
            $tglAwal . ' 00:00:00',
            $tglAkhir . ' 23:59:59'
        ]);

        // [PERCABANGAN] Filter yang sama diterapkan kembali agar hasil ringkasan konsisten.
        if ($request->filled('q')) {
            $q = $request->q;
            $queryTotal->where(function ($sub) use ($q) {
                $sub->where('nama_pelanggan', 'like', "%$q%")
                    ->orWhere('no_hp', 'like', "%$q%")
                    ->orWhere('id_transaksi', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            $queryTotal->whereIn('status_transaksi', $request->status);
        }

        if ($request->filled('bayar')) {
            $queryTotal->whereIn('status_bayar', $request->bayar);
        }

        if ($request->filled('jenis')) {
            $queryTotal->whereIn('jenis_transaksi', $request->jenis);
        }

        /**
         * [ARRAY ASOSIATIF]
         * Data dikirim ke view sebagai array key => value agar mudah
         * dipakai di Blade untuk list data, ringkasan, dan paging.
         */
        return view('kasir.laporan.transaksi.index', [
            'transaksi'  => $transaksi,
            'tglAwal'    => $tglAwal,
            'tglAkhir'   => $tglAkhir,
            'totalOmzet' => $queryTotal->sum('total_bayar'),
            'jumlah'     => $queryTotal->count(),
        ]);
    }

    /**
     * [METHOD] transaksiIndexAdmin2()
     * Menampilkan laporan transaksi untuk Admin2.
     * Logika identik dengan transaksiIndex(), hanya berbeda view yang dituju.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function transaksiIndexAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        // [VALIDASI AKSES] Hanya user yang lolos permission view laporan
        // yang dapat mengakses laporan transaksi untuk panel Admin2.

        // [PERCABANGAN] Menetapkan nilai default tanggal bila input kosong.
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        /**
         * [CLASS-OBJECT + METHOD]
         * Query Builder Eloquent dibentuk dari model Transaksi lalu
         * di-chain dengan eager loading relasi dan filter tanggal.
         */
        $query = Transaksi::with(['pelanggan', 'metodeBayar', 'kasir'])
            ->whereBetween('tgl_transaksi', [
                $tglAwal . ' 00:00:00',
                $tglAkhir . ' 23:59:59'
            ]);

        // [PERCABANGAN + METHOD] Pencarian lintas beberapa kolom.
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_pelanggan', 'like', "%$q%")
                    ->orWhere('no_hp', 'like', "%$q%")
                    ->orWhere('id_transaksi', 'like', "%$q%");
            });
        }

        // [ARRAY] request('status') berasal dari checkbox multiple pada form.
        if ($request->filled('status')) {
            $query->whereIn('status_transaksi', $request->status);
        }

        // [ARRAY] request('bayar') dipakai untuk filter multi status pembayaran.
        if ($request->filled('bayar')) {
            $query->whereIn('status_bayar', $request->bayar);
        }

        // [ARRAY] request('jenis') dipakai untuk filter online/offline.
        if ($request->filled('jenis')) {
            $query->whereIn('jenis_transaksi', $request->jenis);
        }

        // [PERCABANGAN - switch] Menentukan urutan data transaksi.
        $sortBy = $request->sort ?? 'terbaru';
        switch ($sortBy) {
            case 'terlama':
                $query->orderBy('tgl_transaksi', 'ASC');
                break;
            case 'nominal_tertinggi':
                $query->orderBy('total_bayar', 'DESC');
                break;
            case 'nominal_terendah':
                $query->orderBy('total_bayar', 'ASC');
                break;
            case 'terbaru':
            default:
                $query->orderBy('tgl_transaksi', 'DESC');
        }

        // [PAGING] Data dibagi 10 per halaman dan query string tetap dipertahankan.
        $transaksi = $query->paginate(10)->withQueryString();

        // [OBJECT] Query kedua dipakai untuk ringkasan total omzet dan jumlah transaksi.
        $queryTotal = Transaksi::whereBetween('tgl_transaksi', [
            $tglAwal . ' 00:00:00',
            $tglAkhir . ' 23:59:59'
        ]);

        // [PERCABANGAN] Seluruh filter diterapkan ulang agar summary sesuai data list.
        if ($request->filled('q')) {
            $q = $request->q;
            $queryTotal->where(function ($sub) use ($q) {
                $sub->where('nama_pelanggan', 'like', "%$q%")
                    ->orWhere('no_hp', 'like', "%$q%")
                    ->orWhere('id_transaksi', 'like', "%$q%");
            });
        }

        if ($request->filled('status')) {
            $queryTotal->whereIn('status_transaksi', $request->status);
        }

        if ($request->filled('bayar')) {
            $queryTotal->whereIn('status_bayar', $request->bayar);
        }

        if ($request->filled('jenis')) {
            $queryTotal->whereIn('jenis_transaksi', $request->jenis);
        }

        // [ARRAY ASOSIATIF] Mengirim seluruh data yang dibutuhkan view admin2.
        return view('admin2.laporan.transaksi.index', [
            'transaksi'  => $transaksi,
            'tglAwal'    => $tglAwal,
            'tglAkhir'   => $tglAkhir,
            'totalOmzet' => $queryTotal->sum('total_bayar'),
            'jumlah'     => $queryTotal->count(),
        ]);
    }

    // =========================================
    // LAPORAN KASIR
    // =========================================

    /**
     * [METHOD] kasirIndex()
     * Menampilkan laporan kinerja kasir untuk Admin.
     *
     * Konsep yang digunakan:
     * - Perulangan (map/foreach) : Iterasi setiap kasir untuk menghitung statistiknya
     * - Array                    : $stats adalah array ringkasan statistik keseluruhan
     * - Object                   : $kasirList adalah Collection object Laravel
     * - Method                   : map(), where(), count(), sum() adalah method dari Collection
     * - Percabangan              : Implisit pada where() untuk filter status transaksi
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function kasirIndex(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Default rentang tanggal: awal bulan ini hingga hari ini
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        // [OBJECT + METHOD] Mengambil semua kasir aktif dari database
        $kasirList = DB::table('kasir')
            ->where('status', 'aktif')
            ->select('id_kasir', 'nama_kasir', 'no_hp', 'gambar')
            ->get();

        /**
         * [PERULANGAN - map()] map() adalah method perulangan pada Collection Laravel.
         * Setiap item $kasir dalam $kasirList diproses dan dikembalikan sebagai item baru.
         * Ini setara dengan foreach() tapi mengembalikan Collection baru.
         *
         * use ($tglAwal, $tglAkhir) → variabel luar yang digunakan di dalam closure.
         */
        $data = $kasirList->map(function ($kasir) use ($tglAwal, $tglAkhir) {
            // [OBJECT + METHOD] Query transaksi per kasir dalam rentang tanggal
            $transaksi = DB::table('transaksi')
                ->where('id_kasir', $kasir->id_kasir)
                ->whereBetween('tgl_transaksi', [
                    $tglAwal . ' 00:00:00',
                    $tglAkhir . ' 23:59:59'
                ])
                ->get();

            /**
             * [METHOD + PERCABANGAN] where() pada Collection melakukan filter dengan
             * membandingkan nilai field. count() menghitung jumlah hasil filter.
             * Ini setara dengan perulangan + percabangan manual:
             *   $jumlah = 0;
             *   foreach ($transaksi as $t) {
             *       if ($t->status_transaksi == 'antrian') $jumlah++;
             *   }
             */
            $kasir->antrian    = $transaksi->where('status_transaksi', 'antrian')->count();
            $kasir->proses     = $transaksi->where('status_transaksi', 'proses')->count();
            $kasir->siap_ambil = $transaksi->where('status_transaksi', 'siap_di_ambil')->count();
            $kasir->selesai    = $transaksi->where('status_transaksi', 'selesai')->count();

            /**
             * [ARRAY + PERCABANGAN] whereIn() menerima array nilai untuk dicek.
             * Setara dengan: if (status == 'batal' || status == 'ditolak')
             */
            $kasir->batal = $transaksi->whereIn('status_transaksi', ['batal', 'ditolak'])->count();

            // [METHOD] sum() menjumlahkan field total_bayar dari transaksi yang selesai
            $kasir->total_pendapatan = $transaksi
                ->where('status_transaksi', 'selesai')
                ->sum('total_bayar');

            $kasir->total_transaksi = $transaksi->count();

            return $kasir; // Kembalikan object kasir yang sudah ditambahi properti baru
        });

        // [METHOD + PERULANGAN] sortByDesc() mengurutkan Collection berdasarkan nilai field
        $data = (match ($request->get('sort', 'pendapatan_tertinggi')) {
            'pendapatan_terendah' => $data->sortBy('total_pendapatan'),
            'transaksi_terbanyak' => $data->sortByDesc('total_transaksi'),
            'nama_az' => $data->sortBy('nama_kasir', SORT_NATURAL | SORT_FLAG_CASE),
            'nama_za' => $data->sortByDesc('nama_kasir', SORT_NATURAL | SORT_FLAG_CASE),
            default => $data->sortByDesc('total_pendapatan'),
        })->values();
        $topKasir = $data->first();
        $summary = [
            'total_kasir' => $data->count(),
            'total_transaksi' => $data->sum('total_transaksi'),
            'total_pendapatan' => $data->sum('total_pendapatan'),
        ];
        $data = $this->paginateCollection($data, 10);

        return view('laporan.kasir.index', compact('data', 'tglAwal', 'tglAkhir', 'topKasir', 'summary'));
    }

    /**
     * [METHOD] kasirIndexAdmin2()
     * Menampilkan laporan kinerja kasir untuk Admin2.
     * Logika identik dengan kasirIndex(), hanya berbeda view yang dituju.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function kasirIndexAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $kasirList = DB::table('kasir')
            ->where('status', 'aktif')
            ->select('id_kasir', 'nama_kasir', 'no_hp', 'gambar')
            ->get();

        // [PERULANGAN - map()] Iterasi setiap kasir untuk menghitung statistik
        $data = $kasirList->map(function ($kasir) use ($tglAwal, $tglAkhir) {
            $transaksi = DB::table('transaksi')
                ->where('id_kasir', $kasir->id_kasir)
                ->whereBetween('tgl_transaksi', [
                    $tglAwal . ' 00:00:00',
                    $tglAkhir . ' 23:59:59'
                ])
                ->get();

            $kasir->antrian    = $transaksi->where('status_transaksi', 'antrian')->count();
            $kasir->proses     = $transaksi->where('status_transaksi', 'proses')->count();
            $kasir->siap_ambil = $transaksi->where('status_transaksi', 'siap_di_ambil')->count();
            $kasir->selesai    = $transaksi->where('status_transaksi', 'selesai')->count();
            $kasir->batal      = $transaksi->whereIn('status_transaksi', ['batal', 'ditolak'])->count();

            $kasir->total_pendapatan = $transaksi
                ->where('status_transaksi', 'selesai')
                ->sum('total_bayar');

            $kasir->total_transaksi = $transaksi->count();

            return $kasir;
        });

        $data = (match ($request->get('sort', 'pendapatan_tertinggi')) {
            'pendapatan_terendah' => $data->sortBy('total_pendapatan'),
            'transaksi_terbanyak' => $data->sortByDesc('total_transaksi'),
            'nama_az' => $data->sortBy('nama_kasir', SORT_NATURAL | SORT_FLAG_CASE),
            'nama_za' => $data->sortByDesc('nama_kasir', SORT_NATURAL | SORT_FLAG_CASE),
            default => $data->sortByDesc('total_pendapatan'),
        })->values();
        $topKasir = $data->first();
        $summary = [
            'total_kasir' => $data->count(),
            'total_transaksi' => $data->sum('total_transaksi'),
            'total_pendapatan' => $data->sum('total_pendapatan'),
        ];
        $data = (match ($request->get('sort', 'total_tertinggi')) {
            'total_terendah' => $data->sortBy('total_pengiriman'),
            'nama_az' => $data->sortBy('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'nama_za' => $data->sortByDesc('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'sukses_tertinggi' => $data->sortByDesc('terkirim'),
            default => $data->sortByDesc('total_pengiriman'),
        })->values();

        $data = $this->paginateCollection($data, 10);

        return view('admin2.laporan.kasir.index', compact('data', 'tglAwal', 'tglAkhir', 'topKasir', 'summary'));
    }

    // =========================================
    // LAPORAN METODE BAYAR
    // =========================================

    /**
     * [METHOD] bayarIndex()
     * Menampilkan laporan penggunaan metode pembayaran untuk Admin.
     *
     * Konsep yang digunakan:
     * - Object  : DB::table() mengembalikan Query Builder object
     * - Method  : leftJoin(), select(), groupBy(), orderBy(), get() adalah method chaining
     * - Array   : select() menerima array nama kolom; groupBy() menerima array kolom
     *
     * Menggunakan LEFT JOIN agar metode bayar yang belum pernah dipakai
     * tetap muncul dengan nilai total_penggunaan = 0.
     * Hanya menghitung transaksi yang berstatus 'selesai'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function bayarIndex(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Default tanggal
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        /**
         * [OBJECT + METHOD] DB::table() mengembalikan object Query Builder.
         * leftJoin() → semua metode bayar tampil meski belum digunakan.
         * COUNT() dengan alias → menghitung jumlah transaksi per metode bayar.
         */
        $query = DB::table('metode_bayar')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('metode_bayar.id_metode_bayar', '=', 'transaksi.id_metode_bayar')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'metode_bayar.id_metode_bayar',
                'metode_bayar.nama_metode_bayar',
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan') // fungsi agregat SQL
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar');

        match ($request->get('sort', 'penggunaan_tertinggi')) {
            'penggunaan_terendah' => $query->orderBy('total_penggunaan', 'asc'),
            'nama_za' => $query->orderBy('metode_bayar.nama_metode_bayar', 'desc'),
            'nama_az' => $query->orderBy('metode_bayar.nama_metode_bayar', 'asc'),
            default => $query->orderBy('total_penggunaan', 'desc')->orderBy('metode_bayar.nama_metode_bayar'),
        };

        $totalPenggunaan = (clone $query)->get()->sum('total_penggunaan');
        $data = $query->paginate(10)->withQueryString();

        return view('laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir', 'totalPenggunaan'));
    }

    /**
     * [METHOD] bayarIndexKasir()
     * Menampilkan laporan metode bayar untuk Kasir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function bayarIndexKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $query = DB::table('metode_bayar')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('metode_bayar.id_metode_bayar', '=', 'transaksi.id_metode_bayar')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'metode_bayar.id_metode_bayar',
                'metode_bayar.nama_metode_bayar',
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar');

        match ($request->get('sort', 'penggunaan_tertinggi')) {
            'penggunaan_terendah' => $query->orderBy('total_penggunaan', 'asc'),
            'nama_za' => $query->orderBy('metode_bayar.nama_metode_bayar', 'desc'),
            'nama_az' => $query->orderBy('metode_bayar.nama_metode_bayar', 'asc'),
            default => $query->orderBy('total_penggunaan', 'desc')->orderBy('metode_bayar.nama_metode_bayar'),
        };

        $totalPenggunaan = (clone $query)->get()->sum('total_penggunaan');
        $data = $query->paginate(10)->withQueryString();

        return view('kasir.laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir', 'totalPenggunaan'));
    }

    /**
     * [METHOD] bayarIndexAdmin2()
     * Menampilkan laporan metode bayar untuk Admin2.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function bayarIndexAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $query = DB::table('metode_bayar')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('metode_bayar.id_metode_bayar', '=', 'transaksi.id_metode_bayar')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'metode_bayar.id_metode_bayar',
                'metode_bayar.nama_metode_bayar',
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar');

        match ($request->get('sort', 'penggunaan_tertinggi')) {
            'penggunaan_terendah' => $query->orderBy('total_penggunaan', 'asc'),
            'nama_za' => $query->orderBy('metode_bayar.nama_metode_bayar', 'desc'),
            'nama_az' => $query->orderBy('metode_bayar.nama_metode_bayar', 'asc'),
            default => $query->orderBy('total_penggunaan', 'desc')->orderBy('metode_bayar.nama_metode_bayar'),
        };

        $totalPenggunaan = (clone $query)->get()->sum('total_penggunaan');
        $data = $query->paginate(10)->withQueryString();

        return view('admin2.laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir', 'totalPenggunaan'));
    }

    // =========================================
    // LAPORAN PENGELUARAN
    // =========================================

    /**
     * [METHOD] pengeluaranIndex()
     * Menampilkan laporan pengeluaran untuk Admin.
     * Mendukung filter berdasarkan rentang tanggal.
     *
     * Konsep yang digunakan:
     * - Object      : $query adalah object Query Builder
     * - Percabangan : if untuk mengecek ketersediaan filter tanggal
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pengeluaranIndex(Request $request)
{
    requirePermission('laporan', 'view');

    $tglAwal  = $request->dari ?? null;
    $tglAkhir = $request->sampai ?? null;

    $query = Pengeluaran::query();

    if ($request->filled('dari') && $request->filled('sampai')) {
        $query->whereBetween('tanggal_pengeluaran', [$tglAwal, $tglAkhir]);
    }

    if ($request->filled('q')) {
        $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
    }

    match ($request->get('sort', 'terbaru')) {
        'terlama' => $query->orderBy('tanggal_pengeluaran', 'asc')->orderBy('created_at', 'asc'),
        'nominal_tertinggi' => $query->orderBy('nominal', 'desc'),
        'nominal_terendah' => $query->orderBy('nominal', 'asc'),
        default => $query->orderBy('tanggal_pengeluaran', 'desc')->orderBy('created_at', 'desc'),
    };

    // Hitung total dari SEMUA data (bukan hanya halaman aktif)
    $totalNominal = (clone $query)->sum('nominal');
    $totalItem    = (clone $query)->count();

    $pengeluaran = $query->paginate(10)->withQueryString();

    return view('laporan.pengeluaran.index', compact(
        'pengeluaran', 'tglAwal', 'tglAkhir', 'totalNominal', 'totalItem'
    ));
}

    /**
     * [METHOD] pengeluaranIndexKasir()
     * Menampilkan laporan pengeluaran untuk Kasir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pengeluaranIndexKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? null;
        $tglAkhir = $request->sampai ?? null;

        $query = Pengeluaran::query();

        // [PERCABANGAN] Filter tanggal jika kedua parameter tersedia
        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_pengeluaran', [
                $request->dari,
                $request->sampai
            ]);
        }

        if ($request->filled('q')) {
            $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
        }

        match ($request->get('sort', 'terbaru')) {
            'terlama' => $query->orderBy('tanggal_pengeluaran', 'asc')->orderBy('created_at', 'asc'),
            'nominal_tertinggi' => $query->orderBy('nominal', 'desc'),
            'nominal_terendah' => $query->orderBy('nominal', 'asc'),
            default => $query->orderBy('tanggal_pengeluaran', 'desc')->orderBy('created_at', 'desc'),
        };

        $totalNominal = (clone $query)->sum('nominal');
        $totalItem = (clone $query)->count();
        $pengeluaran = $query->paginate(10)->withQueryString();

        return view('kasir.laporan.pengeluaran.index', compact(
            'pengeluaran',
            'tglAwal',
            'tglAkhir',
            'totalNominal',
            'totalItem'
        ));
    }

    /**
     * [METHOD] pengeluaranIndexAdmin2()
     * Menampilkan laporan pengeluaran untuk Admin2.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pengeluaranIndexAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? null;
        $tglAkhir = $request->sampai ?? null;

        $query = Pengeluaran::query();

        // [PERCABANGAN] Filter tanggal jika kedua parameter tersedia
        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_pengeluaran', [
                $request->dari,
                $request->sampai
            ]);
        }

        if ($request->filled('q')) {
            $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
        }

        match ($request->get('sort', 'terbaru')) {
            'terlama' => $query->orderBy('tanggal_pengeluaran', 'asc')->orderBy('created_at', 'asc'),
            'nominal_tertinggi' => $query->orderBy('nominal', 'desc'),
            'nominal_terendah' => $query->orderBy('nominal', 'asc'),
            default => $query->orderBy('tanggal_pengeluaran', 'desc')->orderBy('created_at', 'desc'),
        };

        $totalNominal = (clone $query)->sum('nominal');
        $totalItem = (clone $query)->count();
        $pengeluaran = $query->paginate(10)->withQueryString();

        return view('admin2.laporan.pengeluaran.index', compact(
            'pengeluaran',
            'tglAwal',
            'tglAkhir',
            'totalNominal',
            'totalItem'
        ));
    }

    // =========================================
    // LAPORAN PELANGGAN
    // =========================================

    /**
     * [METHOD] pelangganIndex()
     * Menampilkan laporan ringkasan pelanggan untuk Admin.
     *
     * Konsep yang digunakan:
     * - Object     : $query adalah Eloquent Query Builder object
     * - Array      : selectRaw() menggunakan kolom-kolom dalam bentuk daftar (array implisit)
     * - Percabangan: if untuk filter, switch untuk sorting
     * - Method     : groupBy(), orderByDesc(), get() adalah method dari object query
     *
     * Mengelompokkan transaksi berdasarkan pelanggan (hanya yang memiliki akun)
     * dan menghitung total transaksi serta total belanja masing-masing pelanggan.
     *
     * Sorting yang tersedia:
     * - belanja_tertinggi (default), belanja_terendah, transaksi_terbanyak, nama_az
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pelangganIndex(Request $request)
    {
        requirePermission('laporan', 'view');

        /**
         * [OBJECT + METHOD] selectRaw() memungkinkan penulisan raw SQL dalam query Eloquent.
         * COUNT(*) → menghitung jumlah baris (transaksi) per pelanggan.
         * SUM(total_bayar) → menjumlahkan total belanja per pelanggan.
         * whereNotNull() → hanya ambil pelanggan yang terdaftar (bukan walk-in/transaksi tanpa akun).
         */
        $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
        ')->whereNotNull('id_pelanggan');

        // [PERCABANGAN - if] Filter rentang tanggal (opsional)
        if ($request->dari && $request->sampai) {
            $query->whereBetween('tgl_transaksi', [
                $request->dari . ' 00:00:00',
                $request->sampai . ' 23:59:59'
            ]);
        }

        // [PERCABANGAN - if] Filter pencarian berdasarkan nama atau no HP
        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->q . '%');
            });
        }

        // [METHOD] groupBy() wajib saat menggunakan fungsi agregat (COUNT, SUM)
        $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

        /**
         * [PERCABANGAN - switch] Pilihan sorting berdasarkan parameter dari request.
         * switch lebih bersih dari if-elseif untuk kondisi dengan banyak kemungkinan.
         */
        switch ($request->sort) {
            case 'belanja_terendah':
                $query->orderBy('total_belanja', 'asc');
                break;
            case 'transaksi_terbanyak':
                $query->orderByDesc('total_transaksi');
                break;
            case 'nama_az':
                $query->orderBy('nama_pelanggan', 'asc');
                break;
            case 'belanja_tertinggi':
            default:
                $query->orderByDesc('total_belanja');
        }

        $data = $query->get();
        $topPelanggan = $data->first();
        $summary = [
            'total_pelanggan' => $data->count(),
            'total_transaksi' => $data->sum('total_transaksi'),
            'total_belanja' => $data->sum('total_belanja'),
        ];
        $data = (match ($request->get('sort', 'total_tertinggi')) {
            'total_terendah' => $data->sortBy('total_pengiriman'),
            'nama_az' => $data->sortBy('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'nama_za' => $data->sortByDesc('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'sukses_tertinggi' => $data->sortByDesc('terkirim'),
            default => $data->sortByDesc('total_pengiriman'),
        })->values();

        $data = $this->paginateCollection($data, 10);

        return view('laporan.pelanggan.index', compact('data', 'topPelanggan', 'summary'));
    }

    /**
     * [METHOD] pelangganIndexKasir()
     * Menampilkan laporan pelanggan untuk Kasir.
     * Logika identik dengan pelangganIndex(), hanya berbeda view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pelangganIndexKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
        ')->whereNotNull('id_pelanggan');

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tgl_transaksi', [
                $request->dari . ' 00:00:00',
                $request->sampai . ' 23:59:59'
            ]);
        }

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->q . '%');
            });
        }

        $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

        // [PERCABANGAN - switch] Sorting
        switch ($request->sort) {
            case 'belanja_terendah':
                $query->orderBy('total_belanja', 'asc');
                break;
            case 'transaksi_terbanyak':
                $query->orderByDesc('total_transaksi');
                break;
            case 'nama_az':
                $query->orderBy('nama_pelanggan', 'asc');
                break;
            case 'belanja_tertinggi':
            default:
                $query->orderByDesc('total_belanja');
        }

        $data = $query->get();
        $topPelanggan = $data->first();
        $summary = [
            'total_pelanggan' => $data->count(),
            'total_transaksi' => $data->sum('total_transaksi'),
            'total_belanja' => $data->sum('total_belanja'),
        ];
        $data = (match ($request->get('sort', 'total_tertinggi')) {
            'total_terendah' => $data->sortBy('total_pengiriman'),
            'nama_az' => $data->sortBy('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'nama_za' => $data->sortByDesc('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'sukses_tertinggi' => $data->sortByDesc('terkirim'),
            default => $data->sortByDesc('total_pengiriman'),
        })->values();

        $data = $this->paginateCollection($data, 10);

        return view('kasir.laporan.pelanggan.index', compact('data', 'topPelanggan', 'summary'));
    }

    /**
     * [METHOD] pelangganIndexAdmin2()
     * Menampilkan laporan pelanggan untuk Admin2.
     * Logika identik dengan pelangganIndex(), hanya berbeda view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function pelangganIndexAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
        ')->whereNotNull('id_pelanggan');

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tgl_transaksi', [
                $request->dari . ' 00:00:00',
                $request->sampai . ' 23:59:59'
            ]);
        }

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->q . '%');
            });
        }

        $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

        switch ($request->sort) {
            case 'belanja_terendah':
                $query->orderBy('total_belanja', 'asc');
                break;
            case 'transaksi_terbanyak':
                $query->orderByDesc('total_transaksi');
                break;
            case 'nama_az':
                $query->orderBy('nama_pelanggan', 'asc');
                break;
            case 'belanja_tertinggi':
            default:
                $query->orderByDesc('total_belanja');
        }

        $data = $query->get();
        $topPelanggan = $data->first();
        $summary = [
            'total_pelanggan' => $data->count(),
            'total_transaksi' => $data->sum('total_transaksi'),
            'total_belanja' => $data->sum('total_belanja'),
        ];
        $data = $this->paginateCollection($data, 10);

        return view('admin2.laporan.pelanggan.index', compact('data', 'topPelanggan', 'summary'));
    }

    // =========================================
    // LAPORAN SATUAN
    // =========================================

    /**
     * [METHOD] satuanIndex()
     * Menampilkan laporan penggunaan satuan laundry untuk Admin.
     *
     * Konsep yang digunakan:
     * - Object  : DB::table() mengembalikan Query Builder object
     * - Method  : leftJoin(), select(), groupBy(), orderBy(), get() method chaining
     * - Array   : select() berisi daftar kolom (array implisit)
     *
     * Menggunakan double LEFT JOIN (satuan → detail_transaksi → transaksi)
     * agar satuan yang belum pernah digunakan tetap muncul dengan qty = 0.
     * Menggunakan COALESCE untuk menggantikan NULL dengan 0.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function satuanIndex(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Default tanggal
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();
        /**
         * [OBJECT + METHOD] Double LEFT JOIN:
         * 1. satuan → detail_transaksi  : ambil detail yang menggunakan satuan ini
         * 2. detail_transaksi → transaksi: filter hanya transaksi yang sudah selesai
         *
         * COALESCE(SUM(qty), 0): Jika tidak ada transaksi → SUM akan NULL → COALESCE ganti dengan 0
         * Ini mencegah nilai NULL ditampilkan ke user.
         */
        $data = DB::table('satuan')
            ->leftJoin('detail_transaksi', 'satuan.id_satuan', '=', 'detail_transaksi.id_satuan')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'satuan.id_satuan',
                'satuan.nama_satuan',
                // COALESCE: jika SUM bernilai NULL → kembalikan 0 (penanganan nilai kosong)
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan');

        match ($request->get('sort', 'qty_tertinggi')) {
            'qty_terendah' => $data->orderBy('total_qty', 'asc'),
            'nama_za' => $data->orderBy('satuan.nama_satuan', 'desc'),
            'nama_az' => $data->orderBy('satuan.nama_satuan', 'asc'),
            default => $data->orderBy('total_qty', 'desc')->orderBy('satuan.nama_satuan'),
        };

        $data = $data->paginate(10)->withQueryString();

        $totalQty = $data->sum('total_qty');

        return view('laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir', 'totalQty'));
    }

    /**
     * [METHOD] satuanIndexKasir()
     * Menampilkan laporan satuan untuk Kasir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function satuanIndexKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('satuan')
            ->leftJoin('detail_transaksi', 'satuan.id_satuan', '=', 'detail_transaksi.id_satuan')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'satuan.id_satuan',
                'satuan.nama_satuan',
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan');

        match ($request->get('sort', 'qty_tertinggi')) {
            'qty_terendah' => $data->orderBy('total_qty', 'asc'),
            'nama_za' => $data->orderBy('satuan.nama_satuan', 'desc'),
            'nama_az' => $data->orderBy('satuan.nama_satuan', 'asc'),
            default => $data->orderBy('total_qty', 'desc')->orderBy('satuan.nama_satuan'),
        };

        $data = $data->paginate(10)->withQueryString();

        $totalQty = $data->sum('total_qty');

        return view('kasir.laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir', 'totalQty'));
    }

    /**
     * [METHOD] satuanIndexAdmin2()
     * Menampilkan laporan satuan untuk Admin2.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function satuanIndexAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('satuan')
            ->leftJoin('detail_transaksi', 'satuan.id_satuan', '=', 'detail_transaksi.id_satuan')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'satuan.id_satuan',
                'satuan.nama_satuan',
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan');

        match ($request->get('sort', 'qty_tertinggi')) {
            'qty_terendah' => $data->orderBy('total_qty', 'asc'),
            'nama_za' => $data->orderBy('satuan.nama_satuan', 'desc'),
            'nama_az' => $data->orderBy('satuan.nama_satuan', 'asc'),
            default => $data->orderBy('total_qty', 'desc')->orderBy('satuan.nama_satuan'),
        };

        $data = $data->paginate(10)->withQueryString();

        $totalQty = $data->sum('total_qty');

        return view('admin2.laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir', 'totalQty'));
    }

    // =========================================
    // LAPORAN DRIVER
    // =========================================

    /**
     * [METHOD] driver()
     * Menampilkan laporan kinerja driver untuk Admin.
     *
     * Konsep yang digunakan:
     * - Object     : DB::table() mengembalikan Query Builder object
     * - Array      : $stats adalah array asosiatif ringkasan statistik semua driver
     * - Method     : join(), select(), groupBy(), orderByDesc(), get() method chaining
     * - Perulangan : sum() pada Collection secara internal melakukan iterasi semua item
     *
     * Menggunakan conditional COUNT (CASE WHEN) di SQL untuk menghitung
     * statistik per jenis dan status pengiriman dalam satu query yang efisien.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function driver(Request $request)
    {
        requirePermission('laporan', 'view');

        ['data' => $data, 'stats' => $stats, 'tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir] =
            $this->getDriverReportData($request);

        $data = $this->paginateCollection($data, 10);

        return view('laporan.driver.index', compact('data', 'stats', 'tglAwal', 'tglAkhir'));
    }

    /**
     * [METHOD] driverKasir()
     * Menampilkan laporan kinerja driver untuk Kasir.
     * Logika identik dengan driver(), hanya berbeda view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function driverKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        ['data' => $data, 'stats' => $stats, 'tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir] =
            $this->getDriverReportData($request);

        $data = $this->paginateCollection($data, 10);

        return view('kasir.laporan.driver.index', compact('data', 'stats', 'tglAwal', 'tglAkhir'));
    }

    /**
     * [METHOD] driverAdmin2()
     * Menampilkan laporan kinerja driver untuk Admin2.
     * Logika identik dengan driver(), hanya berbeda view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function driverAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        ['data' => $data, 'stats' => $stats, 'tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir] =
            $this->getDriverReportData($request);

        $data = $this->paginateCollection($data, 10);

        return view('admin2.laporan.driver.index', compact('data', 'stats', 'tglAwal', 'tglAkhir'));
    }

    // =========================================
    // EXPORT TRANSAKSI (Excel via Maatwebsite)
    // =========================================

    /**
     * [METHOD] exportTransaksi()
     * Mengekspor data transaksi ke file Excel untuk Admin.
     *
     * Library yang digunakan: Maatwebsite\Excel (Laravel Excel)
     * Cara kerja:
     * 1. Validasi parameter input
     * 2. Sanitasi nama file menggunakan regex (preg_replace)
     * 3. Instansiasi class TransaksiExport (object) dengan parameter filter
     * 4. Excel::download() memanggil export dan mengirim file ke browser
     *
     * Konsep yang digunakan:
     * - Object       : new TransaksiExport(...) → instansiasi object dari class export
     * - Method       : Excel::download() adalah static method dari Facade Maatwebsite\Excel
     * - Array        : validate() menerima array aturan validasi
     * - Error Handle : validate() otomatis melempar ValidationException jika gagal
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportTransaksi(Request $request)
    {
        requirePermission('laporan', 'view');

        $validated = $this->validateTransaksiExportRequest($request);

        if (($validated['format'] ?? 'excel') === 'pdf') {
            return $this->exportTransaksiPdf($request);
        }

        $namaFile = $this->makeTransaksiExportFilename($request->nama_file, 'xlsx');

        return Excel::download(
            new TransaksiExport(
                $validated['filter_type'],
                $validated['tanggal_awal'],
                $validated['tanggal_akhir'],
                $validated['status_bayar']
            ),
            $namaFile
        );
    }

    /**
     * [METHOD] exportTransaksiKasir()
     * Mengekspor data transaksi ke file Excel untuk Kasir.
     * Logika identik dengan exportTransaksi().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportTransaksiKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        return $this->exportTransaksi($request);
    }

    /**
     * [METHOD] exportTransaksiAdmin2()
     * Mengekspor data transaksi ke file Excel untuk Admin2.
     * Logika identik dengan exportTransaksiKasir().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportTransaksiAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        return $this->exportTransaksi($request);
    }

    // =========================================
    // EXPORT PENGELUARAN (Excel / PDF / CSV)
    // =========================================

    /**
     * [METHOD] exportPengeluaran()
     * Mengekspor laporan pengeluaran untuk Admin.
     * Mendukung tiga format: excel (default), pdf, dan csv.
     *
     * Konsep yang digunakan:
     * - Percabangan (if)     : Filter tanggal dan pencarian
     * - Percabangan (switch) : Pemilihan format export
     * - Method               : Delegasi ke private method sesuai format
     * - Object               : $query adalah Query Builder object
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed  Response sesuai format yang diminta
     */
    public function exportPengeluaran(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Default ke Excel jika format tidak dispesifikasikan
        $format = $request->format ?? 'excel';

        // [OBJECT + METHOD] Bangun query dengan filter opsional
        $query = Pengeluaran::query();

        // [PERCABANGAN - if] Terapkan filter tanggal jika tersedia
        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal_pengeluaran', [$request->dari, $request->sampai]);
        }

        // [PERCABANGAN - if] Terapkan filter pencarian nama jika tersedia
        if ($request->q) {
            $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
        }

        $query->orderBy('tanggal_pengeluaran', 'desc');
        $pengeluaran = $query->get();

        /**
         * [PERCABANGAN - switch] Pilih format export berdasarkan parameter.
         * Setiap case mendelegasikan ke method private yang sesuai.
         * Ini menerapkan prinsip Single Responsibility: satu method = satu tanggung jawab.
         */
        switch ($format) {
            case 'pdf':
                return $this->exportPengeluaranPdf($pengeluaran, $request);  // [METHOD CALL]
            case 'csv':
                return $this->exportPengeluaranCsv($pengeluaran, $request);  // [METHOD CALL]
            case 'excel':
            default:
                return $this->exportPengeluaranExcel($pengeluaran, $request); // [METHOD CALL]
        }
    }

    /**
     * [METHOD] exportPengeluaranKasir()
     * Mengekspor laporan pengeluaran untuk Kasir.
     * Logika identik dengan exportPengeluaran().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function exportPengeluaranKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        $format = $request->format ?? 'excel';

        $query = Pengeluaran::query();

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal_pengeluaran', [$request->dari, $request->sampai]);
        }

        if ($request->q) {
            $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
        }

        $query->orderBy('tanggal_pengeluaran', 'desc');
        $pengeluaran = $query->get();

        // [PERCABANGAN - switch] Pilih format export
        switch ($format) {
            case 'pdf':
                return $this->exportPengeluaranPdf($pengeluaran, $request);
            case 'csv':
                return $this->exportPengeluaranCsv($pengeluaran, $request);
            case 'excel':
            default:
                return $this->exportPengeluaranExcel($pengeluaran, $request);
        }
    }

    /**
     * [METHOD] exportPengeluaranAdmin2()
     * Mengekspor laporan pengeluaran untuk Admin2.
     * Logika identik dengan exportPengeluaran().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function exportPengeluaranAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $format = $request->format ?? 'excel';

        $query = Pengeluaran::query();

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal_pengeluaran', [$request->dari, $request->sampai]);
        }

        if ($request->q) {
            $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
        }

        $query->orderBy('tanggal_pengeluaran', 'desc');
        $pengeluaran = $query->get();

        // [PERCABANGAN - switch] Pilih format export
        switch ($format) {
            case 'pdf':
                return $this->exportPengeluaranPdf($pengeluaran, $request);
            case 'csv':
                return $this->exportPengeluaranCsv($pengeluaran, $request);
            case 'excel':
            default:
                return $this->exportPengeluaranExcel($pengeluaran, $request);
        }
    }

    // =========================================
    // PRIVATE HELPERS - Export Pengeluaran
    // =========================================

    /**
     * Menghasilkan file Excel laporan pengeluaran.
     *
     * Pola yang dipakai:
     * - data disusun dulu di controller
     * - tabel dirender lewat Blade
     * - Laravel Excel mengubah view itu jadi file .xlsx
     */
    private function exportPengeluaranExcel($pengeluaran, $request)
    {
        $rows = $pengeluaran->values()->map(function ($item, $index) {
            return [
                $index + 1,
                Carbon::parse($item->tanggal_pengeluaran)->format('d/m/Y'),
                $item->nama_pengeluaran,
                (float) $item->nominal,
            ];
        })->all();

        return $this->downloadExcelTable(
            'Laporan Pengeluaran',
            ['No', 'Tanggal', 'Nama Pengeluaran', 'Nominal'],
            $rows,
            'Laporan_Pengeluaran_' . Carbon::now()->format('Y-m-d_His') . '.xlsx',
            [
                'Periode' => $this->formatPeriod($request->dari, $request->sampai),
                'Dicetak' => Carbon::now()->format('d/m/Y H:i:s'),
            ],
            ['TOTAL', '', '', (float) $pengeluaran->sum('nominal')]
        );
    }

    /**
     * [PRIVATE METHOD] exportPengeluaranPdf()
     * Menghasilkan file PDF laporan pengeluaran menggunakan DomPDF.
     *
     * Library: barryvdh/laravel-dompdf
     * Cara kerja DomPDF:
     * 1. Render view Blade menjadi HTML
     * 2. Konversi HTML + CSS ke format PDF
     * 3. Kirim file PDF ke browser sebagai download
     *
     * Konsep yang digunakan:
     * - Object : Pdf::loadView() mengembalikan object Dompdf
     * - Array  : $data adalah array asosiatif yang dikirim ke view
     * - Method : loadView(), setPaper(), download() adalah method dari object Pdf
     *
     * @param  \Illuminate\Support\Collection  $pengeluaran  Data pengeluaran
     * @param  \Illuminate\Http\Request         $request      Request untuk filter info
     * @return \Illuminate\Http\Response  Response berisi file PDF
     */
    private function exportPengeluaranPdf($pengeluaran, $request)
    {
        /**
         * [ARRAY ASOSIATIF] $data berisi semua variabel yang dibutuhkan oleh view PDF.
         * Setiap key akan menjadi variabel yang tersedia di view Blade.
         */
        $data = [
            'pengeluaran'   => $pengeluaran,              // Collection data pengeluaran
            'total'         => $pengeluaran->sum('nominal'), // Total semua nominal
            'dari'          => $request->dari,             // Filter tanggal awal
            'sampai'        => $request->sampai,           // Filter tanggal akhir
            'tanggal_cetak' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'), // Timestamp cetak
        ];

        /**
         * [OBJECT + METHOD] Pdf::loadView() adalah Facade yang:
         * 1. Merender view 'laporan.pengeluaran.pdf' dengan data $data
         * 2. Mengonversi hasil HTML ke format PDF menggunakan DomPDF engine
         * 3. Mengembalikan object Dompdf yang bisa dikonfigurasi lebih lanjut
         */
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pengeluaran.pdf', $data);
        $pdf->setPaper('a4', 'portrait'); // [METHOD] Set ukuran dan orientasi kertas

        $fileName = 'Laporan_Pengeluaran_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.pdf';

        // [METHOD] download() membuat response HTTP dengan file PDF sebagai attachment
        return $pdf->download($fileName);
    }

    /**
     * [PRIVATE METHOD] exportPengeluaranCsv()
     * Menghasilkan file CSV laporan pengeluaran.
     *
     * Library: Native PHP (fputcsv) + Laravel response()->stream()
     * CSV = Comma Separated Values, format teks sederhana yang bisa dibuka di Excel.
     * Cara kerja:
     * 1. Buat HTTP response dengan header CSV
     * 2. Stream data baris per baris menggunakan callback function
     * 3. fputcsv() menulis setiap baris sebagai teks CSV ke output stream
     *
     * Konsep yang digunakan:
     * - Array      : $headers (HTTP headers) dan setiap baris CSV dikirim sebagai array
     * - Perulangan : foreach untuk iterasi data pengeluaran
     * - Percabangan: if untuk mengecek filter tanggal
     * - Method     : fputcsv(), fopen(), fclose(), response()->stream()
     *
     * Keunggulan stream():
     * - File besar dapat dikirim tanpa memuat seluruh data ke RAM sekaligus
     * - Lebih hemat memori dibanding membuat string besar lalu mengirimnya sekali
     *
     * @param  \Illuminate\Support\Collection  $pengeluaran  Data pengeluaran
     * @param  \Illuminate\Http\Request         $request      Request untuk filter info
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportPengeluaranCsv($pengeluaran, $request)
    {
        $fileName = 'Laporan_Pengeluaran_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.csv';

        /**
         * [ARRAY ASOSIATIF] HTTP headers untuk response CSV.
         * Browser menggunakan headers ini untuk:
         * - Mengenali tipe konten sebagai file CSV
         * - Menampilkan dialog "Save As" untuk download
         * - Menonaktifkan cache
         */
        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',        // Tipe MIME untuk CSV
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"', // Nama file download
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        /**
         * [CLOSURE / CALLBACK FUNCTION] $callback adalah fungsi anonim yang dijalankan
         * saat Laravel men-stream response ke browser.
         * use ($pengeluaran, $request) → mengakses variabel dari scope luar.
         */
        $callback = function () use ($pengeluaran, $request) {
            /**
             * [METHOD] fopen('php://output', 'w') membuka stream output PHP untuk ditulis.
             * php://output → buffer output PHP yang terhubung langsung ke response HTTP.
             * 'w' → mode write (hanya tulis).
             */
            $file = fopen('php://output', 'w');

            /**
             * [PERCABANGAN + ARRAY] UTF-8 BOM (Byte Order Mark).
             * BOM adalah tiga byte khusus (EF BB BF) di awal file UTF-8.
             * Diperlukan agar Microsoft Excel Windows tidak salah membaca
             * karakter non-ASCII (seperti é, ä, atau karakter Indonesia).
             * fprintf() menulis format string ke file handle.
             */
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // [METHOD] fputcsv() menulis array sebagai satu baris CSV
            // Setiap elemen array = satu kolom, dipisahkan koma, dikutip jika perlu
            fputcsv($file, ['LAPORAN PENGELUARAN']); // Baris judul

            // [PERCABANGAN - if] Tentukan teks periode berdasarkan filter
            $dateRange = 'Periode: ';
            if ($request->dari && $request->sampai) {
                $dateRange .= \Carbon\Carbon::parse($request->dari)->format('d/m/Y')
                    . ' - '
                    . \Carbon\Carbon::parse($request->sampai)->format('d/m/Y');
            } else {
                $dateRange .= 'Semua Data';
            }
            fputcsv($file, [$dateRange]);                                     // Baris periode
            fputcsv($file, ['Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s')]); // Baris timestamp
            fputcsv($file, []);                                                // Baris kosong pemisah

            /**
             * [ARRAY] Header kolom ditulis sebagai array.
             * fputcsv() akan mengonversi array ini menjadi: No,Tanggal,Nama Pengeluaran,Nominal
             */
            fputcsv($file, ['No', 'Tanggal', 'Nama Pengeluaran', 'Nominal']);

            // Variabel counter dan akumulator
            $no    = 1;
            $total = 0;

            /**
             * [PERULANGAN - foreach] Iterasi setiap item pengeluaran.
             * Setiap iterasi menulis satu baris data ke file CSV.
             * Data dikirim sebagai array: [nomor, tanggal, nama, nominal]
             */
            foreach ($pengeluaran as $item) {
                fputcsv($file, [
                    $no++,                                                              // Nomor urut
                    \Carbon\Carbon::parse($item->tanggal_pengeluaran)->format('d/m/Y'), // Tanggal formatted
                    $item->nama_pengeluaran,                                             // Nama pengeluaran
                    $item->nominal                                                       // Nominal angka
                ]);
                $total += $item->nominal; // Akumulasi total
            }

            // Baris total di akhir file
            fputcsv($file, ['', '', 'TOTAL', $total]);

            fclose($file); // Tutup file handle
        };

        // [METHOD] response()->stream() mengirim $callback sebagai HTTP streaming response
        return response()->stream($callback, 200, $headers);
    }

    // =========================================
    // EXPORT PELANGGAN (Excel / PDF / CSV)
    // =========================================

    /**
     * [METHOD] exportPelanggan()
     * Mengekspor laporan pelanggan untuk Admin.
     * Data diambil via helper getPelangganData() yang dipakai bersama semua role.
     *
     * Konsep yang digunakan:
     * - Method      : getPelangganData() adalah private method sebagai helper
     * - Percabangan : switch untuk memilih format export
     * - Object      : $data adalah Collection object Laravel
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function exportPelanggan(Request $request)
    {
        requirePermission('laporan', 'view');

        $format = $request->format ?? 'excel';

        /**
         * [METHOD CALL] Memanggil private method getPelangganData() untuk mendapatkan data.
         * Memisahkan logic pengambilan data ke method tersendiri
         * mencegah duplikasi kode antar exportPelanggan, exportPelangganKasir, exportPelangganAdmin2.
         */
        $data = $this->getPelangganData($request);

        // [PERCABANGAN - switch] Pilih format export
        switch ($format) {
            case 'pdf':
                return $this->exportPelangganPdf($data, $request);
            case 'csv':
                return $this->exportPelangganCsv($data, $request);
            case 'excel':
            default:
                return $this->exportPelangganExcel($data, $request);
        }
    }

    /**
     * [METHOD] exportPelangganKasir()
     * Mengekspor laporan pelanggan untuk Kasir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function exportPelangganKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        $format = $request->format ?? 'excel';
        $data   = $this->getPelangganData($request); // [METHOD CALL] Ambil data via helper

        // [PERCABANGAN - switch]
        switch ($format) {
            case 'pdf':
                return $this->exportPelangganPdf($data, $request);
            case 'csv':
                return $this->exportPelangganCsv($data, $request);
            case 'excel':
            default:
                return $this->exportPelangganExcel($data, $request);
        }
    }

    /**
     * [METHOD] exportPelangganAdmin2()
     * Mengekspor laporan pelanggan untuk Admin2.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function exportPelangganAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        $format = $request->format ?? 'excel';
        $data   = $this->getPelangganData($request); // [METHOD CALL] Ambil data via helper

        // [PERCABANGAN - switch]
        switch ($format) {
            case 'pdf':
                return $this->exportPelangganPdf($data, $request);
            case 'csv':
                return $this->exportPelangganCsv($data, $request);
            case 'excel':
            default:
                return $this->exportPelangganExcel($data, $request);
        }
    }

    // =========================================
    // PRIVATE HELPERS - Export Pelanggan
    // =========================================

    /**
     * [PRIVATE METHOD] getPelangganData()
     * Mengambil data pelanggan dengan filter dan sorting dari request.
     *
     * Digunakan bersama oleh exportPelanggan, exportPelangganKasir, dan exportPelangganAdmin2
     * untuk menghindari duplikasi kode query (prinsip DRY - Don't Repeat Yourself).
     *
     * Konsep yang digunakan:
     * - Object      : $query adalah Eloquent Query Builder object
     * - Percabangan : if untuk filter, switch untuk sorting
     * - Method      : selectRaw(), whereNotNull(), whereBetween(), groupBy(), get()
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection  Koleksi data pelanggan
     */
    private function getPelangganData($request)
    {
        /**
         * [OBJECT + METHOD] Query pelanggan dengan agregasi (COUNT, SUM).
         * whereNotNull('id_pelanggan') → hanya pelanggan terdaftar (bukan transaksi walk-in).
         */
        $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
        ')->whereNotNull('id_pelanggan');

        // [PERCABANGAN - if] Filter rentang tanggal
        if ($request->dari && $request->sampai) {
            $query->whereBetween('tgl_transaksi', [
                $request->dari . ' 00:00:00',
                $request->sampai . ' 23:59:59'
            ]);
        }

        // [PERCABANGAN - if] Filter pencarian nama atau no HP
        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->q . '%');
            });
        }

        $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

        // [PERCABANGAN - switch] Sorting
        switch ($request->sort) {
            case 'belanja_terendah':
                $query->orderBy('total_belanja', 'asc');
                break;
            case 'transaksi_terbanyak':
                $query->orderByDesc('total_transaksi');
                break;
            case 'nama_az':
                $query->orderBy('nama_pelanggan', 'asc');
                break;
            case 'belanja_tertinggi':
            default:
                $query->orderByDesc('total_belanja');
        }

        return $query->get(); // Kembalikan Collection hasil query
    }

    /**
     * Menghasilkan file Excel laporan pelanggan.
     *
     * Polanya sama seperti export Excel lain:
     * - susun baris tabel
     * - kirim ke Blade
     * - download lewat Laravel Excel
     */
    private function exportPelangganExcel($data, $request)
    {
        $rows = $data->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nama_pelanggan,
                $item->no_hp,
                (int) $item->total_transaksi,
                (float) $item->total_belanja,
            ];
        })->all();

        return $this->downloadExcelTable(
            'Laporan Pelanggan',
            ['No', 'Nama Pelanggan', 'No HP', 'Total Transaksi', 'Total Belanja'],
            $rows,
            'Laporan_Pelanggan_' . Carbon::now()->format('Y-m-d_His') . '.xlsx',
            [
                'Periode' => $this->formatPeriod($request->dari, $request->sampai),
                'Dicetak' => Carbon::now()->format('d/m/Y H:i:s'),
            ],
            ['TOTAL', '', '', '', (float) $data->sum('total_belanja')]
        );
    }

    /**
     * [PRIVATE METHOD] exportPelangganPdf()
     * Menghasilkan file PDF laporan pelanggan menggunakan DomPDF.
     *
     * @param  \Illuminate\Support\Collection  $data     Data pelanggan
     * @param  \Illuminate\Http\Request         $request  Request untuk filter
     * @return \Illuminate\Http\Response
     */
    private function exportPelangganPdf($data, $request)
    {
        /**
         * [ARRAY ASOSIATIF] Data yang dikirim ke view Blade untuk di-render sebagai PDF.
         * Berisi ringkasan statistik yang dihitung dari Collection.
         */
        $pdfData = [
            'data'           => $data,
            'totalPelanggan' => $data->count(),                 // [METHOD] Hitung jumlah pelanggan
            'totalTransaksi' => $data->sum('total_transaksi'),  // [METHOD] Jumlah semua transaksi
            'totalBelanja'   => $data->sum('total_belanja'),    // [METHOD] Total semua belanja
            'dari'           => $request->dari,
            'sampai'         => $request->sampai,
            'tanggal_cetak'  => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
        ];

        // [OBJECT + METHOD] Buat PDF dari view Blade menggunakan DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pelanggan.pdf', $pdfData);
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'Laporan_Pelanggan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * [PRIVATE METHOD] exportPelangganCsv()
     * Menghasilkan file CSV laporan pelanggan.
     *
     * Konsep yang digunakan:
     * - Array      : Header HTTP ($headers) dan baris CSV sebagai array
     * - Perulangan : foreach untuk iterasi data pelanggan
     * - Percabangan: if untuk filter tanggal
     * - Method     : fputcsv(), fopen(), fclose(), response()->stream()
     *
     * @param  \Illuminate\Support\Collection  $data     Data pelanggan
     * @param  \Illuminate\Http\Request         $request  Request untuk filter
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportPelangganCsv($data, $request)
    {
        $fileName = 'Laporan_Pelanggan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.csv';

        // [ARRAY ASOSIATIF] HTTP headers untuk response CSV
        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        // [CLOSURE] Fungsi callback yang dieksekusi saat streaming dimulai
        $callback = function () use ($data, $request) {
            $file = fopen('php://output', 'w');

            // [ARRAY] UTF-8 BOM untuk kompatibilitas Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['LAPORAN PELANGGAN']);

            // [PERCABANGAN] Tentukan periode
            $dateRange = 'Periode: ';
            if ($request->dari && $request->sampai) {
                $dateRange .= \Carbon\Carbon::parse($request->dari)->format('d/m/Y')
                    . ' - '
                    . \Carbon\Carbon::parse($request->sampai)->format('d/m/Y');
            } else {
                $dateRange .= 'Semua Data';
            }
            fputcsv($file, [$dateRange]);
            fputcsv($file, ['Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []); // Baris kosong

            // [ARRAY] Header kolom CSV
            fputcsv($file, ['No', 'Nama Pelanggan', 'No HP', 'Total Transaksi', 'Total Belanja']);

            $no           = 1;
            $totalBelanja = 0;

            /**
             * [PERULANGAN - foreach] Tulis setiap baris data pelanggan ke CSV.
             * fputcsv() mengonversi array menjadi baris CSV dengan koma sebagai pemisah.
             */
            foreach ($data as $item) {
                fputcsv($file, [
                    $no++,
                    $item->nama_pelanggan,
                    $item->no_hp,
                    $item->total_transaksi,
                    $item->total_belanja
                ]);
                $totalBelanja += $item->total_belanja; // Akumulasi
            }

            fputcsv($file, ['', '', '', 'TOTAL', $totalBelanja]); // Baris total

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================
    // EXPORT KASIR (Excel)
    // =========================================

    /**
     * Mengekspor laporan kinerja kasir.
     *
     * Jika format `pdf`, data dikirim ke DomPDF.
     * Jika format `excel`, data dirender sebagai tabel Blade lalu diunduh via Laravel Excel.
     */
    public function exportKasir(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Default rentang tanggal
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        // [OBJECT + METHOD] Ambil semua kasir aktif
        $kasirList = DB::table('kasir')
            ->where('status', 'aktif')
            ->select('id_kasir', 'nama_kasir', 'no_hp', 'gambar')
            ->get();

        /**
         * [PERULANGAN - map()] Iterasi setiap kasir untuk menghitung statistik transaksinya.
         * map() setara foreach namun mengembalikan Collection baru.
         */
        $data = $kasirList->map(function ($kasir) use ($tglAwal, $tglAkhir) {
            $transaksi = DB::table('transaksi')
                ->where('id_kasir', $kasir->id_kasir)
                ->whereBetween('tgl_transaksi', [
                    $tglAwal . ' 00:00:00',
                    $tglAkhir . ' 23:59:59'
                ])
                ->get();

            // [METHOD + PERCABANGAN] Hitung statistik per status
            $kasir->antrian    = $transaksi->where('status_transaksi', 'antrian')->count();
            $kasir->proses     = $transaksi->where('status_transaksi', 'proses')->count();
            $kasir->siap_ambil = $transaksi->where('status_transaksi', 'siap_di_ambil')->count();
            $kasir->selesai    = $transaksi->where('status_transaksi', 'selesai')->count();

            // [ARRAY + PERCABANGAN] whereIn menerima array status
            $kasir->batal = $transaksi->whereIn('status_transaksi', ['batal', 'ditolak'])->count();

            // Total pendapatan hanya dari transaksi selesai
            $kasir->total_pendapatan = $transaksi
                ->where('status_transaksi', 'selesai')
                ->sum('total_bayar');

            $kasir->total_transaksi = $transaksi->count();

            return $kasir;
        });

        $data = $data->sortByDesc('total_pendapatan')->values();

        if ($request->format === 'pdf') {
            return $this->exportKasirPdf($data, $tglAwal, $tglAkhir);
        }

        $rows = $data->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nama_kasir,
                $item->no_hp,
                (int) $item->antrian,
                (int) $item->proses,
                (int) $item->siap_ambil,
                (int) $item->selesai,
                (int) $item->batal,
                (int) $item->total_transaksi,
                (float) $item->total_pendapatan,
            ];
        })->all();

        return $this->downloadExcelTable(
            'Laporan Kinerja Kasir',
            ['No', 'Nama Kasir', 'No HP', 'Antrian', 'Proses', 'Siap Ambil', 'Selesai', 'Batal', 'Total Transaksi', 'Total Pendapatan'],
            $rows,
            'Laporan_Kasir_' . Carbon::now()->format('Y-m-d_His') . '.xlsx',
            [
                'Periode' => $this->formatPeriod($tglAwal, $tglAkhir),
                'Dicetak' => Carbon::now()->format('d/m/Y H:i:s'),
            ],
            ['TOTAL', '', '', '', '', '', '', '', '', (float) $data->sum('total_pendapatan')]
        );
    }

    /**
     * [METHOD] exportKasirAdmin2()
     * Mengekspor laporan kasir untuk Admin2.
     * Mendelegasikan ke exportKasir() karena logikanya identik.
     * Ini menerapkan prinsip DRY (Don't Repeat Yourself).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportKasirAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');

        // [METHOD CALL] Delegasi ke exportKasir() — tidak perlu duplikasi kode
        return $this->exportKasir($request);
    }

    // =========================================
    // EXPORT METODE BAYAR (Excel)
    // =========================================

    /**
     * Mengekspor laporan metode pembayaran.
     *
     * Format PDF memakai DomPDF.
     * Format Excel memakai Laravel Excel dengan Blade table.
     */
    public function exportBayar(Request $request)
    {
        requirePermission('laporan', 'view');

        // [PERCABANGAN] Default tanggal
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        /**
         * [OBJECT + METHOD] LEFT JOIN agar metode bayar tanpa transaksi tetap tampil.
         * COUNT() menghitung jumlah transaksi selesai per metode bayar.
         */
        $data = DB::table('metode_bayar')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('metode_bayar.id_metode_bayar', '=', 'transaksi.id_metode_bayar')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'metode_bayar.id_metode_bayar',
                'metode_bayar.nama_metode_bayar',
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar')
            ->orderBy('metode_bayar.id_metode_bayar')
            ->get();

        if ($request->format === 'pdf') {
            return $this->exportBayarPdf($data, $tglAwal, $tglAkhir);
        }

        $rows = $data->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nama_metode_bayar,
                (int) $item->total_penggunaan,
            ];
        })->all();

        return $this->downloadExcelTable(
            'Laporan Metode Pembayaran',
            ['No', 'Metode Pembayaran', 'Total Penggunaan'],
            $rows,
            'Laporan_Metode_Bayar_' . Carbon::now()->format('Y-m-d_His') . '.xlsx',
            [
                'Periode' => $this->formatPeriod($tglAwal, $tglAkhir),
                'Dicetak' => Carbon::now()->format('d/m/Y H:i:s'),
            ],
            ['TOTAL', '', (int) $data->sum('total_penggunaan')]
        );
    }

    /**
     * [METHOD] exportBayarKasir()
     * Export metode bayar untuk Kasir — mendelegasikan ke exportBayar().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportBayarKasir(Request $request)
    {
        requirePermission('laporan', 'view');
        return $this->exportBayar($request); // [METHOD CALL] Delegasi
    }

    /**
     * [METHOD] exportBayarAdmin2()
     * Export metode bayar untuk Admin2 — mendelegasikan ke exportBayar().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportBayarAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');
        return $this->exportBayar($request); // [METHOD CALL] Delegasi
    }

    // =========================================
    // EXPORT SATUAN (Excel)
    // =========================================

    /**
     * Mengekspor laporan satuan.
     *
     * Query tetap sama, hanya cara membuat file Excel yang diseragamkan
     * ke Laravel Excel + Blade table.
     */
   public function exportSatuan(Request $request)
    {
        requirePermission('laporan', 'view');

        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('satuan')
            ->leftJoin('detail_transaksi', 'satuan.id_satuan', '=', 'detail_transaksi.id_satuan')
            ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                    ->where('transaksi.status_transaksi', 'selesai')
                    ->whereBetween('transaksi.tgl_transaksi', [
                        $tglAwal . ' 00:00:00',
                        $tglAkhir . ' 23:59:59'
                    ]);
            })
            ->select(
                'satuan.id_satuan',
                'satuan.nama_satuan',
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan')
            ->orderBy('satuan.nama_satuan')
            ->get();

        // [PERCABANGAN] Cek format DULU sebelum bikin spreadsheet
        if ($request->format === 'pdf') {
            return $this->exportSatuanPdf($data, $tglAwal, $tglAkhir);
        }

        $rows = $data->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nama_satuan,
                (float) $item->total_qty,
            ];
        })->all();

        return $this->downloadExcelTable(
            'Laporan Satuan',
            ['No', 'Nama Satuan', 'Total Qty'],
            $rows,
            'Laporan_Satuan_' . Carbon::now()->format('Y-m-d_His') . '.xlsx',
            [
                'Periode' => $this->formatPeriod($tglAwal, $tglAkhir),
                'Dicetak' => Carbon::now()->format('d/m/Y H:i:s'),
            ],
            ['TOTAL', '', (float) $data->sum('total_qty')]
        );
    }

    /**
     * [PRIVATE METHOD] exportSatuanPdf()
     * Menghasilkan file PDF laporan satuan menggunakan DomPDF.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @param  string  $tglAwal
     * @param  string  $tglAkhir
     * @return \Illuminate\Http\Response
     */
    private function exportSatuanPdf($data, $tglAwal, $tglAkhir)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.satuan.satuan-pdf', [
            'data'     => $data,
            'tglAwal'  => $tglAwal,
            'tglAkhir' => $tglAkhir,
        ])->setPaper('a4', 'portrait');

        $fileName = 'Laporan_Satuan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($fileName);
    }
    /**
     * [METHOD] exportSatuanKasir()
     * Export satuan untuk Kasir — mendelegasikan ke exportSatuan().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportSatuanKasir(Request $request)
    {
        requirePermission('laporan', 'view');
        return $this->exportSatuan($request); // [METHOD CALL] Delegasi
    }

    /**
     * [METHOD] exportSatuanAdmin2()
     * Export satuan untuk Admin2 — mendelegasikan ke exportSatuan().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportSatuanAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');
        return $this->exportSatuan($request); // [METHOD CALL] Delegasi
    }
    // =========================================
    // EXPORT DRIVER (Excel)
    // =========================================

    /**
     * Mengekspor laporan kinerja driver.
     *
     * Perhitungan data tetap dari query yang sama.
     * Output file memakai pola yang seragam:
     * - PDF lewat DomPDF
     * - Excel lewat Laravel Excel + Blade table
     */
    public function exportDriver(Request $request)
    {
        requirePermission('laporan', 'view');

        ['data' => $data, 'tglAwal' => $tglAwal, 'tglAkhir' => $tglAkhir] =
            $this->getDriverReportData($request);

        if ($request->format === 'pdf') {
            return $this->exportDriverPdf($data, $tglAwal, $tglAkhir);
        }

        $rows = $data->values()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->nama_driver,
                $item->no_telp,
                (int) $item->total_pengiriman,
                (int) $item->total_pickup,
                (int) $item->total_antar,
                (int) $item->terkirim,
                (int) $item->gagal,
                (int) $item->dalam_proses,
            ];
        })->all();

        return $this->downloadExcelTable(
            'Laporan Kinerja Driver',
            ['No', 'Nama Driver', 'No HP', 'Total Pengiriman', 'Pickup', 'Antar', 'Terkirim', 'Gagal', 'Dalam Proses'],
            $rows,
            'Laporan_Driver_' . Carbon::now()->format('Y-m-d_His') . '.xlsx',
            [
                'Periode' => $this->formatPeriod($tglAwal, $tglAkhir),
                'Dicetak' => Carbon::now()->format('d/m/Y H:i:s'),
            ],
            ['TOTAL', '', '', (int) $data->sum('total_pengiriman'), '', '', '', '', '']
        );
    }

    private function downloadExcelTable(
        string $title,
        array $headings,
        array $rows,
        string $fileName,
        array $meta = [],
        ?array $footerRow = null
    ) {
        return Excel::download(
            new BladeTableExport($title, $headings, $rows, $meta, $footerRow),
            $fileName
        );
    }

    private function formatPeriod(?string $from, ?string $to): string
    {
        if (!$from || !$to) {
            return 'Semua Data';
        }

        return Carbon::parse($from)->format('d/m/Y') . ' - ' . Carbon::parse($to)->format('d/m/Y');
    }

    /**
     * Mengambil dataset laporan driver dari master driver + delivery.
     * LEFT JOIN dipakai agar driver aktif tetap muncul walaupun belum punya delivery.
     */
    private function getDriverReportData(Request $request): array
    {
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $statusTerkirim = ['delivered', 'selesai'];
        $statusGagal = ['failed', 'batal'];
        $statusProses = [
            'pending',
            'menunggu',
            'accepted',
            'proses',
            'on_the_way_to_pickup',
            'picked_up',
            'on_the_way_to_laundry',
            'arrived_at_laundry',
            'on_the_way_to_deliver',
            'on_the_way_to_customer',
        ];

        $quote = static fn (array $values) => collect($values)
            ->map(fn ($value) => "'" . str_replace("'", "''", $value) . "'")
            ->implode(', ');

        $data = DB::table('driver')
            ->leftJoin('delivery', function ($join) use ($tglAwal, $tglAkhir) {
                $join->on('delivery.id_driver', '=', 'driver.id_driver')
                    ->whereBetween(DB::raw('DATE(COALESCE(delivery.waktu, delivery.created_at))'), [$tglAwal, $tglAkhir]);
            })
            ->where('driver.status', 'aktif')
            ->select(
                'driver.id_driver',
                'driver.nama_driver',
                'driver.no_telp',
                DB::raw('COUNT(delivery.id_delivery) as total_pengiriman'),
                DB::raw('COUNT(CASE WHEN delivery.jenis = "pickup" THEN 1 END) as total_pickup'),
                DB::raw('COUNT(CASE WHEN delivery.jenis = "antar" THEN 1 END) as total_antar'),
                DB::raw('COUNT(CASE WHEN delivery.status IN (' . $quote($statusTerkirim) . ') THEN 1 END) as terkirim'),
                DB::raw('COUNT(CASE WHEN delivery.status IN (' . $quote($statusGagal) . ') THEN 1 END) as gagal'),
                DB::raw('COUNT(CASE WHEN delivery.status IN (' . $quote($statusProses) . ') THEN 1 END) as dalam_proses')
            )
            ->groupBy('driver.id_driver', 'driver.nama_driver', 'driver.no_telp')
            ->get();

        $data = (match ($request->get('sort', 'total_tertinggi')) {
            'total_terendah' => $data->sortBy('total_pengiriman'),
            'sukses_tertinggi' => $data->sortByDesc('terkirim'),
            'nama_az' => $data->sortBy('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            'nama_za' => $data->sortByDesc('nama_driver', SORT_NATURAL | SORT_FLAG_CASE),
            default => $data->sortByDesc('total_pengiriman'),
        })->values();

        $stats = [
            'total_driver_aktif' => $data->count(),
            'total_pengiriman'   => $data->sum('total_pengiriman'),
            'total_pickup'       => $data->sum('total_pickup'),
            'total_antar'        => $data->sum('total_antar'),
            'total_terkirim'     => $data->sum('terkirim'),
            'total_gagal'        => $data->sum('gagal'),
            'total_proses'       => $data->sum('dalam_proses'),
        ];

        return compact('data', 'stats', 'tglAwal', 'tglAkhir');
    }

    /**
     * [METHOD] exportDriverKasir()
     * Export driver untuk Kasir — mendelegasikan ke exportDriver().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportDriverKasir(Request $request)
    {
        requirePermission('laporan', 'view');
        return $this->exportDriver($request); // [METHOD CALL] Delegasi
    }

    /**
     * [METHOD] exportDriverAdmin2()
     * Export driver untuk Admin2 — mendelegasikan ke exportDriver().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function exportDriverAdmin2(Request $request)
    {
        requirePermission('laporan', 'view');
        return $this->exportDriver($request); // [METHOD CALL] Delegasi
    }

    private function exportKasirPdf($data, $tglAwal, $tglAkhir)
    {
        $pdf = Pdf::loadView('laporan.kasir.pdf', [
            'data' => $data,
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
            'tanggal_cetak' => Carbon::now()->format('d/m/Y H:i:s'),
            'totalPendapatan' => $data->sum('total_pendapatan'),
            'totalTransaksi' => $data->sum('total_transaksi'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Kasir_' . Carbon::now()->format('Y-m-d_His') . '.pdf');
    }

    private function exportBayarPdf($data, $tglAwal, $tglAkhir)
    {
        $pdf = Pdf::loadView('laporan.bayar.pdf', [
            'data' => $data,
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
            'tanggal_cetak' => Carbon::now()->format('d/m/Y H:i:s'),
            'totalPenggunaan' => $data->sum('total_penggunaan'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Laporan_Metode_Bayar_' . Carbon::now()->format('Y-m-d_His') . '.pdf');
    }

    private function validateTransaksiExportRequest(Request $request): array
    {
        return $request->validate([
            'filter_type'   => 'required|in:tanggal_masuk,tanggal_selesai,tanggal_bayar',
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'status_bayar'  => 'required|in:semua,lunas,belum_lunas,DP,dp',
            'format'        => 'nullable|in:excel,pdf',
        ]);
    }

    private function makeTransaksiExportFilename(?string $namaFile, string $extension): string
    {
        $baseName = $namaFile ?: 'Laporan_Transaksi_' . date('d-m-Y');
        $baseName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $baseName);

        return $baseName . '.' . $extension;
    }

    private function getTransaksiExportCollection(Request $request)
    {
        $validated = $this->validateTransaksiExportRequest($request);
        $statusBayar = strtoupper($validated['status_bayar']) === 'DP' ? 'DP' : $validated['status_bayar'];

        $query = Transaksi::query()
            ->with(['metodeBayar', 'kasir'])
            ->select('transaksi.*');

        switch ($validated['filter_type']) {
            case 'tanggal_selesai':
                $query->whereNotNull('tgl_lunas')
                    ->whereBetween(DB::raw('DATE(tgl_lunas)'), [
                        $validated['tanggal_awal'],
                        $validated['tanggal_akhir'],
                    ]);
                break;

            case 'tanggal_bayar':
                $query->whereNotNull('tgl_lunas')
                    ->whereBetween(DB::raw('DATE(tgl_lunas)'), [
                        $validated['tanggal_awal'],
                        $validated['tanggal_akhir'],
                    ]);
                break;

            case 'tanggal_masuk':
            default:
                $query->whereBetween('tgl_transaksi', [
                    $validated['tanggal_awal'] . ' 00:00:00',
                    $validated['tanggal_akhir'] . ' 23:59:59',
                ]);
                break;
        }

        if ($statusBayar !== 'semua') {
            $query->where('status_bayar', $statusBayar);
        }

        return $query->orderBy('tgl_transaksi', 'desc')->get();
    }

    private function exportTransaksiPdf(Request $request)
    {
        $validated = $this->validateTransaksiExportRequest($request);
        $data = $this->getTransaksiExportCollection($request);
        $namaFile = $this->makeTransaksiExportFilename($request->nama_file, 'pdf');

        $labelFilter = match ($validated['filter_type']) {
            'tanggal_selesai' => 'Tanggal Selesai',
            'tanggal_bayar' => 'Tanggal Bayar',
            default => 'Tanggal Masuk Pesanan',
        };

        $pdf = Pdf::loadView('laporan.transaksi.pdf', [
            'data' => $data,
            'tanggalAwal' => $validated['tanggal_awal'],
            'tanggalAkhir' => $validated['tanggal_akhir'],
            'filterTypeLabel' => $labelFilter,
            'statusBayar' => $validated['status_bayar'],
            'tanggalCetak' => Carbon::now()->format('d/m/Y H:i:s'),
            'totalOmzet' => $data->sum('total_bayar'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($namaFile);
    }

    private function exportDriverPdf($data, $tglAwal, $tglAkhir)
    {
        $pdf = Pdf::loadView('laporan.driver.pdf', [
            'data' => $data,
            'tglAwal' => $tglAwal,
            'tglAkhir' => $tglAkhir,
            'tanggal_cetak' => Carbon::now()->format('d/m/Y H:i:s'),
            'stats' => [
                'total_driver_aktif' => $data->count(),
                'total_pengiriman' => $data->sum('total_pengiriman'),
                'total_pickup' => $data->sum('total_pickup'),
                'total_antar' => $data->sum('total_antar'),
                'total_terkirim' => $data->sum('terkirim'),
                'total_gagal' => $data->sum('gagal'),
                'total_proses' => $data->sum('dalam_proses'),
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Driver_' . Carbon::now()->format('Y-m-d_His') . '.pdf');
    }

    private function paginateCollection($items, $perPage = 10)
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $collection = collect($items);
        $results = $collection->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }
}
