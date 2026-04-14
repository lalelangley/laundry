<?php

/**
 * ============================================================
 * FILE    : TransaksiController.php
 * LOKASI  : App\Http\Controllers\Web
 * FUNGSI  : Mengelola seluruh alur transaksi laundry untuk
 *           tiga role pengguna: Super Admin, Admin (Admin2),
 *           dan Kasir. Setiap role memiliki method, session
 *           key, dan permission check tersendiri agar data
 *           antar role tidak saling bertabrakan.
 *
 * KONSEP PBO YANG DITERAPKAN:
 *   1. Inheritance   → extends Controller (mewarisi helper Laravel)
 *   2. Enkapsulasi   → private methods menyembunyikan logika internal
 *   3. Abstraksi     → pemanggil tidak perlu tahu detail implementasi
 *   4. Dependency Injection → Request disuntikkan otomatis oleh Laravel
 * ============================================================
 */

namespace App\Http\Controllers\Web;

// ── Base class Laravel yang diwarisi oleh controller ini ──────────────────────
use App\Http\Controllers\Controller;

// ── Facade Laravel untuk HTTP client, Cache, Log, dan Mail ───────────────────
use Illuminate\Support\Facades\Http;    // Untuk kirim request ke Telegram API
use Illuminate\Support\Facades\Cache;   // Untuk menyimpan/membaca kode Telegram sementara
use Illuminate\Support\Facades\Log;     // Untuk mencatat error & debug ke storage/logs
use Illuminate\Support\Facades\Mail;    // Untuk mengirim email ringkasan transaksi

// ── Kelas Request Laravel (Dependency Injection) ─────────────────────────────
use Illuminate\Http\Request;            // Diinjeksikan otomatis ke setiap method

// ── Model-model Eloquent yang digunakan controller ini ────────────────────────
use App\Models\Pelanggan;       // Model tabel pelanggan
use App\Models\Layanan;         // Model tabel layanan (cuci, setrika, dll)
use App\Models\Transaksi;       // Model tabel transaksi (header)
use App\Models\MetodeBayar;     // Model tabel metode pembayaran (cash, transfer, dll)
use App\Models\Parfum;          // Model tabel parfum pilihan
use App\Models\JenisLayanan;    // Model tabel jenis layanan (reguler, express, dll)

/**
 * Class TransaksiController
 *
 * INHERITANCE: Mewarisi class Controller bawaan Laravel.
 * Dengan "extends Controller", class ini otomatis mendapat:
 *   - Helper view(), redirect(), response()
 *   - Middleware support
 *   - Authorize & validate helpers
 *
 * Semua method di class ini bersifat "public" (bisa dipanggil router)
 * kecuali helper internal yang dibuat "private" (enkapsulasi).
 */
class TransaksiController extends Controller
{
    // ══════════════════════════════════════════════════════════════════
    // PRIVATE HELPER METHOD — ENKAPSULASI & ABSTRAKSI
    // Method ini TIDAK bisa dipanggil dari luar class.
    // Tujuannya: memusatkan logika query pelanggan di satu tempat
    // agar tidak ditulis berulang di pilihPelanggan, pelangganKasir,
    // dan pelangganAdmin2.
    // ══════════════════════════════════════════════════════════════════

    /**
     * buildPelangganPickerQuery()
     *
     * Membangun Eloquent query builder untuk pencarian dan pengurutan
     * data pelanggan. Digunakan bersama oleh ketiga role.
     *
     * @param  Request $request  Berisi parameter 'search' dan 'sort'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildPelangganPickerQuery(Request $request)
    {
        // Mulai query dari model Pelanggan (SELECT * FROM pelanggan)
        $query = Pelanggan::query();

        // Ambil parameter pencarian, hapus spasi di awal/akhir, default kosong
        $search = trim((string) $request->get('search', ''));

        // Jika ada kata kunci pencarian, tambahkan kondisi WHERE
        if ($search !== '') {
            // Gunakan closure agar OR tidak mengganggu kondisi AND lainnya
            // Menghasilkan: WHERE (nama_pelanggan LIKE '%...%' OR email LIKE '%...%' OR no_hp LIKE '%...%')
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_pelanggan', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('no_hp', 'like', '%' . $search . '%');
            });
        }

        // Ambil parameter urutan, default 'nama_asc'
        $sort = $request->get('sort', 'nama_asc');

        // PHP 8 match expression — lebih bersih dari if-elseif berantai
        // Setiap case menambahkan ORDER BY ke query
        match ($sort) {
            'nama_desc' => $query->orderBy('nama_pelanggan', 'desc'),  // Z → A
            'terbaru'   => $query->orderBy('id_pelanggan', 'desc'),    // ID terbesar dulu
            'terlama'   => $query->orderBy('id_pelanggan', 'asc'),     // ID terkecil dulu
            default     => $query->orderBy('nama_pelanggan', 'asc'),   // A → Z (default)
        };

        // Kembalikan query builder (belum dieksekusi ke database)
        // Pemanggil bisa melanjutkan dengan ->paginate(), ->get(), dll.
        return $query;
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 1 — SUPER ADMIN: HALAMAN AWAL TRANSAKSI
    // ══════════════════════════════════════════════════════════════════

    /**
     * index()
     *
     * Menampilkan halaman daftar transaksi untuk Super Admin.
     * Jika request tidak membawa parameter 'keep', session cart
     * akan dibersihkan agar tidak ada sisa transaksi sebelumnya.
     *
     * Route  : GET /transaksi
     * Role   : Super Admin
     * View   : admin.transaksi.index
     */
    public function index(Request $request)
    {
        // Cek apakah user Super Admin punya hak 'view' pada modul transaksi
        // Jika tidak, akan dilempar exception / redirect ke halaman error
        requirePermission('transaksi', 'view');

        // Bersihkan session cart KECUALI jika URL membawa ?keep=1
        // Contoh: saat kembali dari halaman pilih layanan, pakai ?keep=1
        if (!$request->has('keep')) {
            session()->forget('detail_transaksi');
        }

        // Kirim data ke view:
        //   pelanggan → data pelanggan yang sudah dipilih (jika ada)
        //   detail    → item layanan di keranjang (jika ada)
        return view('admin.transaksi.index', [
            'pelanggan' => session('pelanggan'),
            'detail'    => session('detail_transaksi', []),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 2 — SUPER ADMIN: HALAMAN BUAT TRANSAKSI BARU
    // ══════════════════════════════════════════════════════════════════

    /**
     * create()
     *
     * Menampilkan halaman form pembuatan transaksi baru untuk Super Admin.
     * Halaman ini hanya butuh permission 'view' karena belum ada aksi
     * perubahan data — perubahan data dijaga oleh endpoint addLayanan, bayar, dll.
     *
     * Route  : GET /transaksi/create
     * Role   : Super Admin
     * View   : transaksi.create
     */
    public function create()
    {
        // Cukup permission view — aksi simpan data dijaga method lain
        requirePermission('transaksi', 'view');

        return view('transaksi.create', [
            'pelanggan'  => session('pelanggan'),           // Pelanggan yang dipilih
            'detail'     => session('detail_transaksi', []), // Isi keranjang
            'keterangan' => session('keterangan_transaksi'), // Catatan tambahan
            // Hitung total harga: jumlahkan (harga × qty) semua item di keranjang
            'total'      => array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
            ),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 3 — PILIH PELANGGAN (PER ROLE)
    // ══════════════════════════════════════════════════════════════════

    /**
     * pilihPelanggan()
     *
     * Menampilkan daftar pelanggan untuk dipilih oleh Super Admin.
     * Menggunakan helper buildPelangganPickerQuery() agar logika
     * pencarian tidak ditulis ulang.
     *
     * Route  : GET /transaksi/pelanggan
     * Role   : Super Admin
     */
    public function pilihPelanggan(Request $request)
    {
        requirePermission('transaksi', 'view');

        // Panggil private helper — abstraksi, pemanggil tidak tahu detail query
        // ->paginate(10) → ambil 10 data per halaman
        // ->withQueryString() → pertahankan parameter search/sort di URL pagination
        $pelanggan = $this->buildPelangganPickerQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('transaksi.pelanggan', compact('pelanggan'));
    }

    /**
     * setPelanggan()
     *
     * Menyimpan data pelanggan yang dipilih Super Admin ke dalam session.
     * Setelah tersimpan, redirect ke halaman buat transaksi.
     *
     * Session key: 'pelanggan' (khusus Super Admin)
     *
     * @param  int $id  ID pelanggan yang dipilih
     * Route  : GET /transaksi/pelanggan/{id}/set
     * Role   : Super Admin
     */
    public function setPelanggan($id)
    {
        // Butuh permission 'add' karena ini awal dari proses pembuatan transaksi
        requirePermission('transaksi', 'add');

        // Cari pelanggan di database berdasarkan ID
        $p = Pelanggan::find($id);

        // Jika pelanggan tidak ditemukan, kembali ke halaman sebelumnya dengan pesan error
        if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

        // Simpan data pelanggan ke session dengan key 'pelanggan' (khusus Super Admin)
        // PENTING: Key berbeda per role agar tidak saling menimpa
        session([
            'pelanggan' => [
                'id_pelanggan'   => $p->id_pelanggan,
                'nama_pelanggan' => $p->nama_pelanggan,
                'no_hp'          => $p->no_hp,
                'foto'           => $p->gambar,
            ]
        ]);

        // Arahkan ke halaman buat transaksi Super Admin
        return redirect()->route('transaksi.create');
    }

    /**
     * setPelangganKasir()
     *
     * Menyimpan data pelanggan yang dipilih Kasir ke session.
     * Session key 'pelanggan_kasir' berbeda dari 'pelanggan' milik Super Admin
     * untuk mencegah data antar role saling tertimpa.
     *
     * Kasir juga menyimpan data tambahan: email, telegram_chat_id, telegram_username
     * karena Kasir yang bertanggung jawab mengirim notifikasi ke pelanggan.
     *
     * @param  int $id  ID pelanggan
     * Route  : GET /kasir/transaksi/pelanggan/{id}/set
     * Role   : Kasir
     */
    public function setPelangganKasir($id)
    {
        // Kasir cukup permission view untuk memilih pelanggan
        requirePermission('transaksi', 'view');

        $p = Pelanggan::find($id);
        if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

        // Simpan ke session dengan key KHUSUS KASIR: 'pelanggan_kasir'
        session([
            'pelanggan_kasir' => [
                'id_pelanggan'      => $p->id_pelanggan,
                'nama_pelanggan'    => $p->nama_pelanggan,
                'no_hp'             => $p->no_hp,
                'email'             => $p->email,             // Untuk kirim notifikasi email
                'telegram_chat_id'  => $p->telegram_chat_id, // Untuk kirim notifikasi Telegram
                'telegram_username' => $p->telegram_username,
                // Normalisasi path foto: pastikan selalu diawali 'pelanggan/'
                'foto'              => $p->gambar
                    ? (str_starts_with($p->gambar, 'pelanggan/')
                        ? $p->gambar
                        : 'pelanggan/' . $p->gambar)
                    : null,
            ]
        ]);

        return redirect()->route('kasir.transaksi.create');
    }

    /**
     * setPelangganAdmin2()
     *
     * Menyimpan data pelanggan yang dipilih Admin ke session.
     * Session key 'pelanggan_transaksi' khusus untuk role Admin.
     *
     * @param  int $id  ID pelanggan
     * Route  : GET /admin2/transaksi/pelanggan/{id}/set
     * Role   : Admin
     */
    public function setPelangganAdmin2($id)
    {
        requirePermission('transaksi', 'view');

        $p = Pelanggan::find($id);
        if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

        // Session key KHUSUS ADMIN: 'pelanggan_transaksi'
        session([
            'pelanggan_transaksi' => [
                'id_pelanggan'   => $p->id_pelanggan,
                'nama_pelanggan' => $p->nama_pelanggan,
                'no_hp'          => $p->no_hp,
                // Normalisasi path foto sama seperti Kasir
                'foto'           => $p->gambar
                    ? (str_starts_with($p->gambar, 'pelanggan/')
                        ? $p->gambar
                        : 'pelanggan/' . $p->gambar)
                    : null,
            ]
        ]);

        return redirect()->route('admin2.transaksi.create');
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 4 — TAMBAH LAYANAN KE KERANJANG (PER ROLE)
    // ══════════════════════════════════════════════════════════════════

    /**
     * addLayanan()
     *
     * Menambahkan layanan ke keranjang transaksi Super Admin.
     * Data layanan diambil dari database berdasarkan ID layanan,
     * kemudian ditambahkan ke session 'detail_transaksi'.
     *
     * Session 'detail_transaksi' dipakai BERSAMA oleh ketiga role
     * karena pada satu waktu hanya satu role yang aktif membuat transaksi.
     *
     * @param  Request $request  Berisi id_jenis_layanan, qty, keterangan, parfum
     * @param  int     $id       ID layanan
     * Route  : POST /transaksi/layanan/{id}/add
     * Role   : Super Admin
     * Return : JSON
     */
    public function addLayanan(Request $request, $id)
    {
        // Butuh permission 'add' karena ini menulis data ke session
        requirePermission('transaksi', 'add');

        // Eager load relasi jenis dan satuan sekaligus agar tidak N+1 query
        $layanan = Layanan::with('jenis.satuan')->find($id);
        if (!$layanan) {
            return response()->json(['error' => 'Layanan tidak ditemukan'], 404);
        }

        // Tentukan jenis layanan: dari request atau ambil yang pertama dari relasi
        $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;

        // Cari jenis yang sesuai dari koleksi yang sudah di-eager load (tidak query ulang)
        $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();
        if (!$jenis) {
            return response()->json(['error' => 'Jenis layanan tidak ditemukan'], 404);
        }

        // Ambil cart yang ada di session, default array kosong jika belum ada
        $cart = session()->get('detail_transaksi', []);

        // Tambahkan item baru ke akhir array cart
        $cart[] = [
            'id_layanan'       => $layanan->id_layanan,
            'nama_layanan'     => $layanan->nama_layanan,
            'id_jenis_layanan' => $jenis->id_jenis_layanan,
            'jenis'            => $jenis->nama_jenis,
            'harga'            => $jenis->harga,
            'qty'              => $request->qty ?? 1,           // Default qty = 1
            'satuan'           => $jenis->satuan->nama_satuan ?? '',
            'keterangan'       => $request->keterangan ?? '-',
            'id_parfum'        => $request->parfum ?? null,
            // Ambil nama parfum langsung saat ditambahkan, bukan saat checkout
            'parfum_nama'      => $request->parfum
                ? Parfum::find($request->parfum)?->nama_parfum
                : null,
        ];

        // Simpan cart yang sudah diperbarui kembali ke session
        session()->put('detail_transaksi', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 5 — HAPUS LAYANAN DARI KERANJANG (PER ROLE)
    // ══════════════════════════════════════════════════════════════════

    /**
     * remove()
     *
     * Menghapus satu item dari keranjang transaksi Super Admin
     * berdasarkan index array-nya.
     *
     * Mengapa by index? Karena satu layanan bisa ditambahkan lebih dari
     * sekali dengan qty berbeda, sehingga tidak bisa pakai ID layanan
     * sebagai pengenal unik.
     *
     * @param  int $index  Posisi item di array (0, 1, 2, ...)
     * Route  : GET /transaksi/remove/{index}
     * Role   : Super Admin
     */
    public function remove($index)
    {
        // Hapus item = mengubah cart = butuh permission 'edit'
        requirePermission('transaksi', 'edit');

        $cart = session('detail_transaksi', []);

        // Pastikan index yang diminta benar-benar ada di array
        if (isset($cart[$index])) {
            unset($cart[$index]); // Hapus elemen di posisi $index

            // Re-index array: setelah unset, index bisa loncat misal 0,2,3
            // array_values() memastikan index kembali urut 0,1,2,...
            $cart = array_values($cart);
        }

        session()->put('detail_transaksi', $cart);

        return back()->with('success', 'Layanan berhasil dihapus dari keranjang');
    }

    /**
     * removeAdmin2()
     *
     * Sama seperti remove() tapi untuk role Admin.
     * Dipisah agar jika suatu saat Admin butuh perlakuan berbeda
     * (misal: log khusus, validasi tambahan), bisa diubah tanpa
     * mempengaruhi Super Admin.
     *
     * @param  int $index  Posisi item di array
     * Route  : GET /admin2/transaksi/remove/{index}
     * Role   : Admin
     */
    public function removeAdmin2($index)
    {
        requirePermission('transaksi', 'edit');

        $cart = session('detail_transaksi', []);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart); // Re-index agar tetap urut
        }

        session()->put('detail_transaksi', $cart);

        return back()->with('success', 'Layanan berhasil dihapus dari keranjang');
    }

    /**
     * removeKasir()
     *
     * Sama seperti remove() tapi untuk role Kasir.
     *
     * @param  int $index  Posisi item di array
     * Route  : GET /kasir/transaksi/remove/{index}
     * Role   : Kasir
     */
    public function removeKasir($index)
    {
        requirePermission('transaksi', 'edit');

        $cart = session('detail_transaksi', []);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart);
        }

        session()->put('detail_transaksi', $cart);

        return back()->with('success', 'Layanan berhasil dihapus dari keranjang');
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 6 — HALAMAN KONFIRMASI / CHECKOUT (PER ROLE)
    // ══════════════════════════════════════════════════════════════════

    /**
     * checkout()
     *
     * Menyimpan transaksi Super Admin langsung ke database TANPA
     * melewati halaman review terlebih dahulu (simplified checkout).
     * Method ini membuat record Transaksi + DetailTransaksi sekaligus.
     *
     * Route  : POST /transaksi/checkout
     * Role   : Super Admin
     */
    public function checkout(Request $request)
    {
        requirePermission('transaksi', 'add');

        try {
            // Ambil data dari session — key 'pelanggan_transaksi' untuk Super Admin di flow ini
            $pelanggan = session('pelanggan_transaksi');
            $detail    = session('detail_transaksi', []);

            // Validasi: pelanggan dan cart harus ada isinya
            if (!$pelanggan || count($detail) === 0) {
                return redirect()
                    ->route('transaksi.create')
                    ->with('error', 'Data transaksi tidak lengkap');
            }

            // Hitung total harga dari semua item di keranjang
            $total = array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
            );

            // Buat record header transaksi di tabel 'transaksi'
            $transaksi = \App\Models\Transaksi::create([
                'id_pelanggan' => $pelanggan['id_pelanggan'],
                'tanggal'      => now()->format('Y-m-d H:i:s'), // Format datetime lengkap
                'total_harga'  => $total,
                'status'       => 'proses',
                'keterangan'   => session('keterangan_transaksi'),
                'id_kasir'     => auth()->id(), // ID user yang sedang login
            ]);

            // Loop setiap item di keranjang, buat record detail transaksi
            foreach ($detail as $d) {
                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $transaksi->id_transaksi, // FK ke header
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'qty'              => $d['qty'],
                    'harga'            => $d['harga'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                ]);
            }

            // Bersihkan semua session yang berkaitan dengan transaksi ini
            session()->forget([
                'pelanggan_transaksi',
                'detail_transaksi',
                'keterangan_transaksi'
            ]);

            return redirect()
                ->route('riwayat.index')
                ->with('success', 'Transaksi berhasil disimpan');

        } catch (\Throwable $e) {
            // Catat error ke log untuk debugging
            \Log::error($e);

            return redirect()
                ->route('transaksi.confirm')
                ->with('error', 'Gagal menyimpan transaksi');
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN 7 — PROSES PEMBAYARAN / BAYAR (PER ROLE)
    // Ini adalah method INTI yang menyimpan transaksi lengkap dengan
    // data pembayaran (diskon, DP, metode bayar, status bayar, dll.)
    // ══════════════════════════════════════════════════════════════════

    /**
     * bayar()
     *
     * Memproses pembayaran transaksi untuk Super Admin.
     * Menerima data pembayaran via AJAX (JSON request),
     * menghitung total akhir setelah diskon, menentukan status
     * pembayaran, menyimpan ke database, lalu mengembalikan JSON.
     *
     * Alur status bayar:
     *   langsung_bayar=1 ATAU dp >= total → LUNAS
     *   dp > 0 tapi < total              → DP
     *   dp = 0                           → BELUM LUNAS
     *
     * Session key : 'pelanggan' (Super Admin)
     * Route        : POST /transaksi/bayar
     * Role         : Super Admin
     * Return       : JSON
     */
    public function bayar(Request $request)
    {
        try {
            // Log untuk debugging — memudahkan trace jika ada error di production
            \Log::info('🟢 BAYAR METHOD CALLED');
            \Log::info('Request data:', $request->all());

            // Ambil data pelanggan dari session KHUSUS Super Admin
            $pelanggan = session('pelanggan');
            $detail    = session('detail_transaksi', []);

            \Log::info('Session pelanggan:', $pelanggan ? ['found' => true] : ['found' => false]);
            \Log::info('Session detail count:', ['count' => count($detail)]);

            // Guard clause: batalkan jika data tidak lengkap
            if (!$pelanggan || empty($detail)) {
                \Log::error('❌ Validation failed: pelanggan or detail empty');
                return response()->json(['error' => 'Transaksi tidak valid'], 400);
            }

            // ── Ambil semua parameter pembayaran dari request ──────────────
            $diskon        = floatval($request->input('diskon', 0));        // Nilai diskon
            $dp            = floatval($request->input('dp', 0));            // Uang muka
            $langsungBayar = intval($request->input('langsung_bayar', 0)); // 1 = bayar penuh sekarang
            $keterangan    = $request->input('keterangan', '-');            // Catatan transaksi
            $idMetodeBayar = $request->input('id_metode_bayar', 1);        // Cash default
            $tglEstimasi   = $request->input('tgl_estimasi', now());       // Estimasi selesai

            // Hitung total awal sebelum diskon
            $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

            // ── Proses diskon ──────────────────────────────────────────────
            $tipeDiskon = $request->input('tipe_diskon', 'nominal');
            if ($tipeDiskon === 'percent') {
                // Konversi persentase ke nilai nominal: misal 10% dari 100.000 = 10.000
                $diskon = $totalAwal * ($diskon / 100);
            }

            // Validasi: diskon tidak boleh melebihi total harga
            if ($diskon > $totalAwal) {
                return response()->json([
                    'message' => 'Diskon tidak boleh lebih besar dari total harga.'
                ], 422);
            }

            // Total setelah diskon, minimum 0 (tidak bisa negatif)
            $totalAkhir = max($totalAwal - $diskon, 0);

            // ── Tentukan status pembayaran berdasarkan kondisi ─────────────
            $totalBayar = $dp;

            if ($langsungBayar === 1 || $dp >= $totalAkhir) {
                // Bayar penuh / lunas
                $totalBayar  = $totalAkhir;
                $statusBayar = 'lunas';
                $tglLunas    = now()->format('Y-m-d H:i:s'); // Catat waktu lunas
                $dp          = 0; // Reset DP karena sudah lunas
            } elseif ($dp > 0) {
                // Baru bayar sebagian (DP)
                $statusBayar = 'DP';
                $tglLunas    = null; // Belum ada tanggal lunas
            } else {
                // Belum bayar sama sekali
                $statusBayar = 'belum_lunas';
                $tglLunas    = null;
            }

            \Log::info('💰 Calculated values:', [
                'totalAwal'   => $totalAwal,
                'diskon'      => $diskon,
                'totalAkhir'  => $totalAkhir,
                'statusBayar' => $statusBayar
            ]);

            // ── Simpan header transaksi ke database ────────────────────────
            $trans = Transaksi::create([
                'id_pelanggan'     => $pelanggan['id_pelanggan'],
                'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
                'no_hp'            => $pelanggan['no_hp'],
                'total_harga'      => $totalAwal,      // Total sebelum diskon
                'total_bayar'      => $totalBayar,     // Jumlah yang dibayar sekarang
                'dp'               => $dp,             // Sisa DP jika belum lunas
                'diskon'           => $diskon,
                'status_bayar'     => $statusBayar,    // lunas / DP / belum_lunas
                'status_transaksi' => 'antrian',       // Status awal selalu antrian
                'jenis_transaksi'  => 'offline',       // Transaksi di kasir = offline
                'keterangan'       => $keterangan,
                'tgl_transaksi'    => now()->format('Y-m-d H:i:s'),
                'tgl_estimasi'     => $tglEstimasi,
                'tgl_lunas'        => $tglLunas,
                'id_kasir'         => auth()->id() ?? 1,
                'nama_kasir'       => auth()->user()->name ?? 'Admin',
                'id_metode_bayar'  => $idMetodeBayar,
            ]);

            \Log::info('✅ Transaction created:', ['id' => $trans->id_transaksi]);

            // ── Simpan detail (item) transaksi ke database ─────────────────
            foreach ($detail as $d) {
                \Log::info('Detail item:', $d);

                // Ambil id_satuan dari relasi jenis → satuan
                // Ini dilakukan saat simpan agar data historis tidak berubah
                // meskipun satuan di master data diubah di kemudian hari
                $jenis    = \App\Models\JenisLayanan::with('satuan')
                    ->where('id_jenis_layanan', $d['id_jenis_layanan'])
                    ->first();
                $idSatuan = $jenis?->satuan?->id_satuan ?? null;

                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $trans->id_transaksi,
                    'id_layanan'       => $d['id_layanan'],
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                    'harga'            => $d['harga'],
                    'qty'              => $d['qty'],
                    'id_satuan'        => $idSatuan,
                ]);
            }

            // Bersihkan session setelah transaksi berhasil disimpan
            session()->forget(['pelanggan', 'detail_transaksi', 'keterangan_transaksi']);

            \Log::info('✅ SUCCESS - Transaction saved');

            // Kembalikan data yang dibutuhkan frontend untuk menampilkan struk
            return response()->json([
                'success'          => true,
                'id_transaksi'     => $trans->id_transaksi,
                'total'            => $totalAkhir,
                'bayar'            => $totalBayar,
                'nama'             => $pelanggan['nama_pelanggan'],
                'hp'               => $pelanggan['no_hp'],
                'email'            => $pelanggan['email'] ?? null,
                'telegram_chat_id' => $pelanggan['telegram_chat_id'] ?? null,
                'status_bayar'     => $statusBayar,
                'diskon'           => $diskon,
                'total_bayar'      => $totalBayar,
                'tgl_lunas'        => $tglLunas,
            ]);

        } catch (\Exception $e) {
            // Tangkap semua exception, log detail lengkap untuk debugging
            \Log::error('❌ ERROR in bayar():', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // KASIR — CREATE, PILIH PELANGGAN, BAYAR, CHECKOUT, KONFIRMASI
    // ══════════════════════════════════════════════════════════════════

    /**
     * createKasir()
     *
     * Halaman buat transaksi baru untuk role Kasir.
     * Membaca session 'pelanggan_kasir' (bukan 'pelanggan').
     *
     * Route : GET /kasir/transaksi/create
     * Role  : Kasir
     */
    public function createKasir()
    {
        requirePermission('transaksi', 'view');

        return view('kasir.transaksi.create', [
            'pelanggan'  => session('pelanggan_kasir'),  // Key khusus Kasir
            'detail'     => session('detail_transaksi', []),
            'keterangan' => session('keterangan_transaksi'),
            'total'      => array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
            ),
        ]);
    }

    /**
     * pelangganKasir()
     *
     * Daftar pelanggan untuk dipilih oleh Kasir.
     * Menggunakan helper yang sama dengan Super Admin & Admin (abstraksi).
     *
     * Route : GET /kasir/transaksi/pelanggan
     * Role  : Kasir
     */
    public function pelangganKasir(Request $request)
    {
        requirePermission('transaksi', 'view');

        $pelanggan = $this->buildPelangganPickerQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('kasir.transaksi.pelanggan', compact('pelanggan'));
    }

    /**
     * bayarKasir()
     *
     * Memproses pembayaran transaksi untuk role Kasir.
     * Logika perhitungan identik dengan bayar() milik Super Admin,
     * perbedaannya HANYA pada session key yang dibaca:
     *   Super Admin → 'pelanggan'
     *   Kasir       → 'pelanggan_kasir'
     *
     * Dipisah agar tiap role bisa dikembangkan secara independen
     * di masa depan tanpa risiko mempengaruhi role lain.
     *
     * Route  : POST /kasir/transaksi/bayar
     * Role   : Kasir
     * Return : JSON
     */
    public function bayarKasir(Request $request)
    {
        try {
            \Log::info('🟢 BAYAR KASIR METHOD CALLED');
            \Log::info('Request data:', $request->all());

            // Baca dari session KHUSUS KASIR
            $pelanggan = session('pelanggan_kasir');
            $detail    = session('detail_transaksi', []);

            \Log::info('Session pelanggan:', $pelanggan
                ? ['found' => true, 'data' => $pelanggan]
                : ['found' => false]);
            \Log::info('Session detail count:', ['count' => count($detail)]);

            if (!$pelanggan || empty($detail)) {
                \Log::error('❌ Validation failed: pelanggan or detail empty');
                return response()->json([
                    'error' => 'Transaksi tidak valid. Silakan pilih pelanggan dan layanan terlebih dahulu.'
                ], 400);
            }

            $diskon        = floatval($request->input('diskon', 0));
            $dp            = floatval($request->input('dp', 0));
            $langsungBayar = intval($request->input('langsung_bayar', 0));
            $keterangan    = $request->input('keterangan', '-');
            $idMetodeBayar = $request->input('id_metode_bayar', 1);
            $tglEstimasi   = $request->input('tgl_estimasi', now());

            $totalAwal  = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));
            $tipeDiskon = $request->input('tipe_diskon', 'nominal');

            if ($tipeDiskon === 'percent') {
                $diskon = $totalAwal * ($diskon / 100);
            }

            if ($diskon > $totalAwal) {
                return response()->json([
                    'message' => 'Diskon tidak boleh lebih besar dari total harga.'
                ], 422);
            }

            $totalAkhir = max($totalAwal - $diskon, 0);
            $totalBayar = $dp;

            if ($langsungBayar === 1 || $dp >= $totalAkhir) {
                $totalBayar  = $totalAkhir;
                $statusBayar = 'lunas';
                $tglLunas    = now()->format('Y-m-d H:i:s');
                $dp          = 0;
            } elseif ($dp > 0) {
                $statusBayar = 'DP';
                $tglLunas    = null;
            } else {
                $statusBayar = 'belum_lunas';
                $tglLunas    = null;
            }

            \Log::info('💰 Calculated values:', [
                'totalAwal'   => $totalAwal,
                'diskon'      => $diskon,
                'totalAkhir'  => $totalAkhir,
                'statusBayar' => $statusBayar
            ]);

            $trans = Transaksi::create([
                'id_pelanggan'     => $pelanggan['id_pelanggan'],
                'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
                'no_hp'            => $pelanggan['no_hp'],
                'total_harga'      => $totalAwal,
                'total_bayar'      => $totalBayar,
                'dp'               => $dp,
                'diskon'           => $diskon,
                'status_bayar'     => $statusBayar,
                'status_transaksi' => 'antrian',
                'jenis_transaksi'  => 'offline',
                'keterangan'       => $keterangan,
                'tgl_transaksi'    => now()->format('Y-m-d H:i:s'),
                'tgl_estimasi'     => $tglEstimasi,
                'tgl_lunas'        => $tglLunas,
                'id_kasir'         => auth()->id() ?? 1,
                'nama_kasir'       => auth()->user()->name ?? 'Kasir',
                'id_metode_bayar'  => $idMetodeBayar,
            ]);

            \Log::info('✅ Transaction created:', ['id' => $trans->id_transaksi]);

            foreach ($detail as $d) {
                $jenis    = \App\Models\JenisLayanan::with('satuan')
                    ->where('id_jenis_layanan', $d['id_jenis_layanan'])
                    ->first();
                $idSatuan = $jenis?->satuan?->id_satuan ?? null;

                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $trans->id_transaksi,
                    'id_layanan'       => $d['id_layanan'],
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                    'harga'            => $d['harga'],
                    'qty'              => $d['qty'],
                    'id_satuan'        => $idSatuan,
                ]);
            }

            // Bersihkan session KHUSUS KASIR setelah transaksi tersimpan
            session()->forget(['pelanggan_kasir', 'detail_transaksi', 'keterangan_transaksi']);

            \Log::info('✅ SUCCESS - Transaction saved');

            return response()->json([
                'success'          => true,
                'id_transaksi'     => $trans->id_transaksi,
                'total'            => $totalAkhir,
                'bayar'            => $totalBayar,
                'nama'             => $pelanggan['nama_pelanggan'],
                'hp'               => $pelanggan['no_hp'],
                'email'            => $pelanggan['email'] ?? null,
                'telegram_chat_id' => $pelanggan['telegram_chat_id'] ?? null,
                'status_bayar'     => $statusBayar,
                'diskon'           => $diskon,
                'total_bayar'      => $totalBayar,
                'tgl_lunas'        => $tglLunas,
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERROR in bayarKasir():', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // ADMIN — CREATE, PILIH PELANGGAN, BAYAR, CHECKOUT, KONFIRMASI
    // ══════════════════════════════════════════════════════════════════

    /**
     * createAdmin2()
     *
     * Halaman buat transaksi baru untuk role Admin.
     * Perbedaan dengan Super Admin: view Admin juga membutuhkan
     * data parfum untuk ditampilkan di form pilihan layanan.
     *
     * Route : GET /admin2/transaksi/create
     * Role  : Admin
     */
    public function createAdmin2()
    {
        requirePermission('transaksi', 'view');

        return view('admin2.transaksi.create', [
            'pelanggan'  => session('pelanggan_transaksi'), // Key khusus Admin
            'detail'     => session('detail_transaksi', []),
            'keterangan' => session('keterangan_transaksi'),
            'parfum'     => \App\Models\Parfum::all(),      // Daftar parfum untuk pilihan
            'total'      => array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
            ),
        ]);
    }

    /**
     * pelangganAdmin2()
     *
     * Daftar pelanggan untuk dipilih oleh Admin.
     *
     * Route : GET /admin2/transaksi/pelanggan
     * Role  : Admin
     */
    public function pelangganAdmin2(Request $request)
    {
        requirePermission('transaksi', 'view');

        $pelanggan = $this->buildPelangganPickerQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('admin2.transaksi.pelanggan', compact('pelanggan'));
    }

    /**
     * checkoutKasir()
     *
     * Menyimpan transaksi Kasir ke database (simplified, tanpa data pembayaran lengkap).
     * Status bayar default 'belum_lunas' — pembayaran diurus terpisah.
     *
     * Route : POST /kasir/transaksi/checkout
     * Role  : Kasir
     */
    public function checkoutKasir(Request $request)
    {
        requirePermission('transaksi', 'add');

        try {
            $pelanggan = session('pelanggan_kasir'); // Key khusus Kasir
            $detail    = session('detail_transaksi', []);

            if (!$pelanggan || count($detail) === 0) {
                return redirect()
                    ->route('kasir.transaksi.create')
                    ->with('error', 'Data transaksi tidak lengkap');
            }

            $total = array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
            );

            $transaksi = \App\Models\Transaksi::create([
                'id_pelanggan'     => $pelanggan['id_pelanggan'],
                'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
                'no_hp'            => $pelanggan['no_hp'],
                'total_harga'      => $total,
                'status_transaksi' => 'antrian',     // Masuk antrian dulu
                'status_bayar'     => 'belum_lunas', // Belum ada pembayaran
                'jenis_transaksi'  => 'offline',
                'keterangan'       => session('keterangan_transaksi'),
                'tgl_transaksi'    => now()->format('Y-m-d H:i:s'),
                'id_kasir'         => auth()->id(),
                'nama_kasir'       => auth()->user()->name ?? 'Kasir',
            ]);

            foreach ($detail as $d) {
                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $transaksi->id_transaksi,
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'qty'              => $d['qty'],
                    'harga'            => $d['harga'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                ]);
            }

            // Bersihkan session Kasir
            session()->forget([
                'pelanggan_kasir',
                'detail_transaksi',
                'keterangan_transaksi'
            ]);

            return redirect()
                ->route('kasir.riwayat.index')
                ->with('success', 'Transaksi berhasil disimpan');

        } catch (\Throwable $e) {
            \Log::error($e);

            return redirect()
                ->route('kasir.transaksi.confirm')
                ->with('error', 'Gagal menyimpan transaksi');
        }
    }

    /**
     * checkoutAdmin2()
     *
     * Menyimpan transaksi Admin ke database.
     *
     * Route : POST /admin2/transaksi/checkout
     * Role  : Admin
     */
    public function checkoutAdmin2(Request $request)
    {
        requirePermission('transaksi', 'add');

        try {
            $pelanggan = session('pelanggan_transaksi'); // Key khusus Admin
            $detail    = session('detail_transaksi', []);

            if (!$pelanggan || count($detail) === 0) {
                return redirect()
                    ->route('admin2.transaksi.create')
                    ->with('error', 'Data transaksi tidak lengkap');
            }

            $total = array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
            );

            $transaksi = \App\Models\Transaksi::create([
                'id_pelanggan' => $pelanggan['id_pelanggan'],
                'tanggal'      => now()->format('Y-m-d H:i:s'),
                'total_harga'  => $total,
                'status'       => 'proses',
                'keterangan'   => session('keterangan_transaksi'),
                'id_kasir'     => auth()->id(),
            ]);

            foreach ($detail as $d) {
                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $transaksi->id_transaksi,
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'qty'              => $d['qty'],
                    'harga'            => $d['harga'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                ]);
            }

            // Bersihkan session Admin
            session()->forget([
                'pelanggan_transaksi',
                'detail_transaksi',
                'keterangan_transaksi'
            ]);

            return redirect()
                ->route('admin2.riwayat.index')
                ->with('success', 'Transaksi berhasil disimpan');

        } catch (\Throwable $e) {
            \Log::error($e);

            return redirect()
                ->route('transaksi.confirm')
                ->with('error', 'Gagal menyimpan transaksi');
        }
    }

    /**
     * bayarAdmin2()
     *
     * Memproses pembayaran transaksi untuk role Admin.
     * Logika identik dengan bayar() dan bayarKasir(),
     * perbedaan HANYA pada session key: 'pelanggan_transaksi'.
     *
     * Route  : POST /admin2/transaksi/bayar
     * Role   : Admin
     * Return : JSON
     */
    public function bayarAdmin2(Request $request)
    {
        try {
            \Log::info('🟢 BAYAR ADMIN2 METHOD CALLED');
            \Log::info('Request data:', $request->all());

            // Baca dari session KHUSUS ADMIN
            $pelanggan = session('pelanggan_transaksi');
            $detail    = session('detail_transaksi', []);

            \Log::info('Session pelanggan:', $pelanggan
                ? ['found' => true, 'data' => $pelanggan]
                : ['found' => false]);
            \Log::info('Session detail count:', ['count' => count($detail)]);

            if (!$pelanggan || empty($detail)) {
                \Log::error('❌ Validation failed: pelanggan or detail empty');
                return response()->json([
                    'error' => 'Transaksi tidak valid. Silakan pilih pelanggan dan layanan terlebih dahulu.'
                ], 400);
            }

            $diskon        = floatval($request->input('diskon', 0));
            $dp            = floatval($request->input('dp', 0));
            $langsungBayar = intval($request->input('langsung_bayar', 0));
            $keterangan    = $request->input('keterangan', '-');
            $idMetodeBayar = $request->input('id_metode_bayar', 1);
            $tglEstimasi   = $request->input('tgl_estimasi', now());

            $totalAwal  = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));
            $tipeDiskon = $request->input('tipe_diskon', 'nominal');

            if ($tipeDiskon === 'percent') {
                $diskon = $totalAwal * ($diskon / 100);
            }

            if ($diskon > $totalAwal) {
                return response()->json([
                    'message' => 'Diskon tidak boleh lebih besar dari total harga.'
                ], 422);
            }

            $totalAkhir = max($totalAwal - $diskon, 0);
            $totalBayar = $dp;

            if ($langsungBayar === 1 || $dp >= $totalAkhir) {
                $totalBayar  = $totalAkhir;
                $statusBayar = 'lunas';
                $tglLunas    = now()->format('Y-m-d H:i:s');
                $dp          = 0;
            } elseif ($dp > 0) {
                $statusBayar = 'DP';
                $tglLunas    = null;
            } else {
                $statusBayar = 'belum_lunas';
                $tglLunas    = null;
            }

            \Log::info('💰 Calculated values:', [
                'totalAwal'   => $totalAwal,
                'diskon'      => $diskon,
                'totalAkhir'  => $totalAkhir,
                'statusBayar' => $statusBayar
            ]);

            $trans = Transaksi::create([
                'id_pelanggan'     => $pelanggan['id_pelanggan'],
                'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
                'no_hp'            => $pelanggan['no_hp'],
                'total_harga'      => $totalAwal,
                'total_bayar'      => $totalBayar,
                'dp'               => $dp,
                'diskon'           => $diskon,
                'status_bayar'     => $statusBayar,
                'status_transaksi' => 'antrian',
                'jenis_transaksi'  => 'offline',
                'keterangan'       => $keterangan,
                'tgl_transaksi'    => now()->format('Y-m-d H:i:s'),
                'tgl_estimasi'     => $tglEstimasi,
                'tgl_lunas'        => $tglLunas,
                'id_kasir'         => auth()->id() ?? 1,
                'nama_kasir'       => auth()->user()->name ?? 'Admin',
                'id_metode_bayar'  => $idMetodeBayar,
            ]);

            \Log::info('✅ Transaction created:', ['id' => $trans->id_transaksi]);

            foreach ($detail as $d) {
                $jenis    = \App\Models\JenisLayanan::with('satuan')
                    ->where('id_jenis_layanan', $d['id_jenis_layanan'])
                    ->first();
                $idSatuan = $jenis?->satuan?->id_satuan ?? null;

                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $trans->id_transaksi,
                    'id_layanan'       => $d['id_layanan'],
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                    'harga'            => $d['harga'],
                    'qty'              => $d['qty'],
                    'id_satuan'        => $idSatuan,
                ]);
            }

            // Bersihkan session KHUSUS ADMIN setelah transaksi tersimpan
            session()->forget(['pelanggan_transaksi', 'detail_transaksi', 'keterangan_transaksi']);

            \Log::info('✅ SUCCESS - Transaction saved');

            return response()->json([
                'success'      => true,
                'total'        => $totalAkhir,
                'bayar'        => $totalBayar,
                'nama'         => $pelanggan['nama_pelanggan'],
                'hp'           => $pelanggan['no_hp'],
                'status_bayar' => $statusBayar,
                'diskon'       => $diskon,
                'total_bayar'  => $totalBayar,
                'tgl_lunas'    => $tglLunas,
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERROR in bayarAdmin2():', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * confirmAdmin2()
     *
     * Menampilkan halaman review/konfirmasi sebelum Admin menyimpan transaksi.
     * Berbeda dengan checkout(), method ini hanya MENAMPILKAN data —
     * belum ada yang disimpan ke database.
     *
     * Route : GET /admin2/transaksi/confirm
     * Role  : Admin
     */
    public function confirmAdmin2()
    {
        requirePermission('transaksi', 'view');

        $pelanggan  = session('pelanggan_transaksi');
        $detail     = session('detail_transaksi', []);
        $keterangan = session('keterangan_transaksi', '');

        // Log untuk debug jika halaman konfirmasi tidak tampil benar
        \Log::info('Admin2 Confirm:', [
            'has_pelanggan' => !empty($pelanggan),
            'detail_count'  => count($detail)
        ]);

        // Guard: redirect ke halaman create jika data tidak lengkap
        if (!$pelanggan) {
            return redirect()
                ->route('admin2.transaksi.create')
                ->with('error', 'Pelanggan belum dipilih');
        }

        if (count($detail) === 0) {
            return redirect()
                ->route('admin2.transaksi.create')
                ->with('error', 'Belum ada layanan yang dipilih');
        }

        // Hitung total untuk ditampilkan di halaman konfirmasi
        $totalHarga = array_sum(
            array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
        );

        // Ambil semua metode bayar untuk ditampilkan di dropdown
        $metode_bayar = \App\Models\MetodeBayar::all();

        return view('admin2.transaksi.checkout', compact(
            'pelanggan',
            'detail',
            'keterangan',
            'totalHarga',
            'metode_bayar'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    // ADD LAYANAN — KASIR & ADMIN
    // ══════════════════════════════════════════════════════════════════

    /**
     * addLayananKasir()
     *
     * Menambahkan layanan ke keranjang Kasir.
     * Logika identik dengan addLayanan() milik Super Admin.
     * Dipisah agar routing per role tetap bersih dan independen.
     *
     * Route  : POST /kasir/transaksi/layanan/{id}/add
     * Role   : Kasir
     * Return : JSON
     */
    public function addLayananKasir(Request $request, $id)
    {
        requirePermission('transaksi', 'add');

        $layanan = Layanan::with('jenis.satuan')->find($id);
        if (!$layanan) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan'], 404);
        }

        $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
        $jenis   = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

        if (!$jenis) {
            return response()->json(['success' => false, 'message' => 'Jenis layanan tidak ditemukan'], 404);
        }

        $cart = session()->get('detail_transaksi', []);

        $cart[] = [
            'id_layanan'       => $layanan->id_layanan,
            'nama_layanan'     => $layanan->nama_layanan,
            'id_jenis_layanan' => $jenis->id_jenis_layanan,
            'jenis'            => $jenis->nama_jenis,
            'harga'            => $jenis->harga,
            'qty'              => $request->qty ?? 1,
            'satuan'           => $jenis->satuan->nama_satuan ?? '',
            'keterangan'       => $request->keterangan ?? '-',
            'id_parfum'        => $request->parfum ?? null,
            'parfum_nama'      => $request->parfum
                ? Parfum::find($request->parfum)?->nama_parfum
                : null,
        ];

        session()->put('detail_transaksi', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan (Kasir)',
        ]);
    }

    /**
     * addLayananAdmin2()
     *
     * Menambahkan layanan ke keranjang Admin.
     * Logika identik dengan addLayananKasir().
     *
     * Route  : POST /admin2/transaksi/layanan/{id}/add
     * Role   : Admin
     * Return : JSON
     */
    public function addLayananAdmin2(Request $request, $id)
    {
        requirePermission('transaksi', 'add');

        $layanan = Layanan::with('jenis.satuan')->find($id);
        if (!$layanan) {
            return response()->json(['success' => false, 'message' => 'Layanan tidak ditemukan'], 404);
        }

        $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
        $jenis   = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

        if (!$jenis) {
            return response()->json(['success' => false, 'message' => 'Jenis layanan tidak ditemukan'], 404);
        }

        $cart = session()->get('detail_transaksi', []);

        $cart[] = [
            'id_layanan'       => $layanan->id_layanan,
            'nama_layanan'     => $layanan->nama_layanan,
            'id_jenis_layanan' => $jenis->id_jenis_layanan,
            'jenis'            => $jenis->nama_jenis,
            'harga'            => $jenis->harga,
            'qty'              => $request->qty ?? 1,
            'satuan'           => $jenis->satuan->nama_satuan ?? '',
            'keterangan'       => $request->keterangan ?? '-',
            'id_parfum'        => $request->parfum ?? null,
            'parfum_nama'      => $request->parfum
                ? Parfum::find($request->parfum)?->nama_parfum
                : null,
        ];

        session()->put('detail_transaksi', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan (Admin)',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // ADD JENIS — SEMUA ROLE (DENGAN DELEGASI)
    // ══════════════════════════════════════════════════════════════════

    /**
     * addJenis()
     *
     * Menambahkan jenis layanan ke keranjang berdasarkan ID jenis langsung
     * (berbeda dengan addLayanan yang berdasarkan ID layanan induk).
     *
     * CATATAN PENTING: Ada potensi bug — Log::info dipanggil sebelum
     * null check pada $jenis. Jika $jenis null, kode akan crash di baris
     * $jenis->id_jenis_layanan sebelum sampai ke guard "if (!$jenis)".
     *
     * @param  Request $request
     * @param  int     $idJenis  ID jenis layanan
     * Route  : POST /transaksi/jenis/{idJenis}/add
     * Role   : Semua (Super Admin, Kasir, Admin)
     * Return : JSON
     */
    public function addJenis(Request $request, $idJenis)
    {
        requirePermission('transaksi', 'add');

        try {
            // Validasi input sebelum diproses
            $request->validate([
                'qty'    => 'required|numeric|min:0.01',
                'parfum' => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::find($idJenis);

            // ⚠️ PERHATIAN: Log ini dipanggil sebelum null check di bawah
            // Jika $jenis null, baris ini akan menyebabkan error "Attempt to read property on null"
            \Log::info('Jenis data:', [
                'id_jenis'   => $jenis->id_jenis_layanan,
                'id_layanan' => $jenis->id_layanan,
            ]);

            if (!$jenis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis layanan tidak ditemukan',
                ], 404);
            }

            // Eager load relasi layanan (induk) dan satuan sekaligus
            $jenis->load(['layanan', 'satuan']);

            $cart = session()->get('detail_transaksi', []);

            $newItem = [
                'id_layanan'       => $jenis->id_layanan,
                'nama_layanan'     => $jenis->layanan->nama_layanan ?? '-',
                'id_jenis_layanan' => $jenis->id_jenis_layanan,
                'jenis'            => $jenis->nama_jenis,
                'harga'            => $jenis->harga,
                'qty'              => $request->qty,
                'satuan'           => $jenis->satuan->nama_satuan ?? '',
                'keterangan'       => '-',
                'id_parfum'        => $request->parfum,
                'parfum_nama'      => $request->parfum
                    ? \App\Models\Parfum::find($request->parfum)?->nama_parfum
                    : null,
                'gambar'           => $jenis->gambar, // Gambar untuk ditampilkan di cart UI
            ];

            $cart[] = $newItem;
            session()->put('detail_transaksi', $cart);

            return response()->json([
                'success'    => true,
                'message'    => 'Jenis layanan berhasil ditambahkan',
                'data'       => $newItem,
                'cart_count' => count($cart) // Jumlah item di cart untuk update badge UI
            ]);

        } catch (\Exception $e) {
            \Log::error('Error addJenis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * addJenisKasir()
     *
     * Wrapper addJenis() untuk route Kasir.
     * Cek permission dilakukan di sini, lalu delegasikan ke addJenis().
     * Ini contoh DELEGASI — menghindari duplikasi logika.
     *
     * Route  : POST /kasir/transaksi/jenis/{idJenis}/add
     * Role   : Kasir
     */
    public function addJenisKasir(Request $request, $idJenis)
    {
        requirePermission('transaksi', 'add');
        // Delegasi ke addJenis() — logika sama, tidak perlu ditulis ulang
        return $this->addJenis($request, $idJenis);
    }

    /**
     * addJenisAdmin2()
     *
     * Wrapper addJenis() untuk route Admin.
     *
     * Route  : POST /admin2/transaksi/jenis/{idJenis}/add
     * Role   : Admin
     */
    public function addJenisAdmin2(Request $request, $idJenis)
    {
        requirePermission('transaksi', 'add');
        // Delegasi ke addJenis() — ini contoh penerapan DRY yang benar
        return $this->addJenis($request, $idJenis);
    }

    // ══════════════════════════════════════════════════════════════════
    // HALAMAN KONFIRMASI / CHECKOUT (PER ROLE)
    // ══════════════════════════════════════════════════════════════════

    /**
     * confirm()
     *
     * Halaman review sebelum Super Admin menyimpan transaksi.
     * Data ditampilkan dari session, belum ada yang tersimpan ke DB.
     *
     * Route : GET /transaksi/confirm
     * Role  : Super Admin
     */
    public function confirm()
    {
        requirePermission('transaksi', 'view');

        $pelanggan  = session('pelanggan'); // Key Super Admin
        $detail     = session('detail_transaksi', []);
        $keterangan = session('keterangan_transaksi', '');

        if (!$pelanggan) {
            return redirect()
                ->route('transaksi.create')
                ->with('error', 'Pelanggan belum dipilih');
        }

        if (count($detail) === 0) {
            return redirect()
                ->route('transaksi.create')
                ->with('error', 'Belum ada layanan yang dipilih');
        }

        $totalHarga   = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));
        $metode_bayar = \App\Models\MetodeBayar::all();

        return view('transaksi.checkout', compact(
            'pelanggan',
            'detail',
            'keterangan',
            'totalHarga',
            'metode_bayar'
        ));
    }

    /**
     * confirmKasir()
     *
     * Halaman review sebelum Kasir menyimpan transaksi.
     * Membaca dari session 'pelanggan_kasir'.
     *
     * Route : GET /kasir/transaksi/confirm
     * Role  : Kasir
     */
    public function confirmKasir()
    {
        requirePermission('transaksi', 'view');

        $pelanggan  = session('pelanggan_kasir'); // Key Kasir
        $detail     = session('detail_transaksi', []);
        $keterangan = session('keterangan_transaksi', '');

        \Log::info('Kasir Confirm:', [
            'has_pelanggan' => !empty($pelanggan),
            'detail_count'  => count($detail)
        ]);

        if (!$pelanggan) {
            return redirect()
                ->route('kasir.transaksi.create')
                ->with('error', 'Pelanggan belum dipilih');
        }

        if (count($detail) === 0) {
            return redirect()
                ->route('kasir.transaksi.create')
                ->with('error', 'Belum ada layanan yang dipilih');
        }

        $totalHarga   = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));
        $metode_bayar = \App\Models\MetodeBayar::all();

        return view('kasir.transaksi.checkout', compact(
            'pelanggan',
            'detail',
            'keterangan',
            'totalHarga',
            'metode_bayar'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    // BAGIAN NOTIFIKASI — KIRIM RINGKASAN VIA EMAIL / TELEGRAM
    // ══════════════════════════════════════════════════════════════════

    /**
     * shareKasir()
     *
     * Mengirim ringkasan transaksi ke pelanggan melalui Email atau Telegram.
     * Method ini menangani dua channel sekaligus berdasarkan parameter 'channel'.
     *
     * Alur Telegram:
     *   1. Cek apakah ada kode/chat_id di request
     *   2. Resolve ke numeric chat_id via Cache (kode sementara) atau langsung jika numerik
     *   3. Kirim pesan via Telegram Bot API
     *   4. Simpan chat_id ke profil pelanggan untuk pengiriman berikutnya
     *
     * @param  Request $request  Berisi id_transaksi, channel, recipient
     * Route  : POST /kasir/transaksi/share
     * Role   : Kasir
     * Return : JSON
     */
    public function shareKasir(Request $request)
    {
        requirePermission('transaksi', 'view');

        // Validasi input dengan pesan error yang informatif
        $validated = $request->validate([
            'id_transaksi' => 'required|exists:transaksi,id_transaksi',
            'channel'      => 'required|in:email,telegram',
            'recipient'    => 'nullable|string|max:255',
        ], [
            'id_transaksi.required' => 'ID transaksi wajib dikirim.',
            'id_transaksi.exists'   => 'Transaksi tidak ditemukan.',
            'channel.required'      => 'Channel pengiriman wajib dipilih.',
            'channel.in'            => 'Channel pengiriman tidak valid.',
        ]);

        // Eager load semua relasi yang dibutuhkan untuk buildShareMessage()
        $transaksi = Transaksi::with(['detail.layanan', 'detail.jenis', 'pelanggan'])
            ->where('id_transaksi', $validated['id_transaksi'])
            ->firstOrFail();

        // Bangun teks pesan menggunakan private helper (abstraksi)
        $message = $this->buildShareMessage($transaksi);

        // ── Kirim via Email ────────────────────────────────────────────
        if ($validated['channel'] === 'email') {
            $recipient = trim((string) ($validated['recipient'] ?? ''));

            // Jika tidak ada email di request, fallback ke email pelanggan di database
            if ($recipient === '') {
                $recipient = (string) ($transaksi->pelanggan?->email ?? '');
            }

            if ($recipient === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tujuan belum diisi.'
                ], 422);
            }

            // Validasi format email menggunakan PHP built-in filter
            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format email tujuan tidak valid.'
                ], 422);
            }

            try {
                // Kirim email menggunakan view 'emails.transaksi-share'
                Mail::send('emails.transaksi-share', [
                    'transaksi' => $transaksi,
                ], function ($mail) use ($recipient, $transaksi) {
                    $mail->to($recipient)
                        ->subject('Ringkasan Pembayaran Transaksi #' . $transaksi->id_transaksi);
                });
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim share transaksi via email', [
                    'id_transaksi' => $transaksi->id_transaksi,
                    'recipient'    => $recipient,
                    'error'        => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim ke email. Periksa konfigurasi mailer.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ringkasan pembayaran berhasil dikirim ke email.'
            ]);
        }

        // ── Kirim via Telegram ─────────────────────────────────────────
        $botToken              = config('services.telegram.bot_token');
        $storedChatId          = trim((string) ($transaksi->pelanggan?->telegram_chat_id ?? ''));
        $storedTelegramUsername = $transaksi->pelanggan?->telegram_username;
        $rawRecipient          = trim((string) ($validated['recipient'] ?? ''));
        $fallbackRecipient     = trim((string) config('services.telegram.default_chat_id'));
        $chatId                = '';
        $telegramUsername      = $storedTelegramUsername;

        if (empty($botToken)) {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum dikonfigurasi.'
            ], 422);
        }

        // Prioritas resolusi chat_id:
        //   1. Dari input request (kode atau ID numerik)
        //   2. Dari profil pelanggan di database
        //   3. Dari fallback config (default_chat_id)
        if ($rawRecipient !== '') {
            $resolvedTelegram = $this->resolveTelegramRecipient($rawRecipient);
            $chatId           = $resolvedTelegram['chat_id'];
            $telegramUsername = $resolvedTelegram['username'] ?? $telegramUsername;
        } elseif ($storedChatId !== '') {
            $chatId = $storedChatId;
        } elseif ($fallbackRecipient !== '') {
            $resolvedTelegram = $this->resolveTelegramRecipient($fallbackRecipient);
            $chatId           = $resolvedTelegram['chat_id'];
            $telegramUsername = $resolvedTelegram['username'] ?? $telegramUsername;
            $rawRecipient     = $fallbackRecipient;
        }

        if ($rawRecipient === '' && $storedChatId === '' && $fallbackRecipient === '') {
            return response()->json([
                'success' => false,
                'message' => 'Kode Telegram belum diisi dan akun Telegram pelanggan belum terhubung.'
            ], 422);
        }

        if ($chatId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Kode Telegram tidak valid atau sudah kedaluwarsa. Jika pelanggan pernah terhubung, cek data Telegram pelanggan. Jika belum, minta pelanggan kirim /start lagi ke bot untuk mendapatkan kode baru.'
            ], 422);
        }

        try {
            // Kirim pesan ke Telegram via Bot API
            $response = Http::asForm()
                ->timeout(15) // Timeout 15 detik
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text'    => $message,
                ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim share transaksi ke Telegram', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id'      => $chatId,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Telegram gagal. Coba lagi sebentar.'
            ], 500);
        }

        if (!$response->successful()) {
            Log::warning('Telegram API menolak pengiriman share transaksi', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id'      => $chatId,
                'response'     => $response->json(),
            ]);

            $telegramDescription = (string) data_get($response->json(), 'description', '');
            $message             = 'Gagal mengirim ke Telegram.';

            // Berikan pesan error yang lebih spesifik jika chat tidak ditemukan
            if (str_contains(strtolower($telegramDescription), 'chat not found')) {
                $message = 'Chat Telegram tidak ditemukan. Untuk chat pribadi, kirim pesan dulu ke bot lalu gunakan chat ID numerik dari getUpdates.';
            }

            return response()->json([
                'success'           => false,
                'message'           => $message,
                'telegram_response' => $response->json(),
            ], 500);
        }

        // Jika berhasil, simpan chat_id ke profil pelanggan untuk pengiriman berikutnya
        $this->storeTelegramChatIdForPelanggan($transaksi, $chatId, $telegramUsername);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pembayaran berhasil dikirim ke Telegram.'
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS — TELEGRAM
    // ══════════════════════════════════════════════════════════════════

    /**
     * resolveTelegramRecipient()
     *
     * Mengubah input penerima (bisa berupa kode sementara atau ID numerik)
     * menjadi chat_id yang valid untuk Telegram Bot API.
     *
     * Dua strategi resolusi:
     *   1. Cari di Cache — kode sementara yang dibuat saat pelanggan /start bot
     *   2. Langsung pakai jika input sudah berformat numerik (ID langsung)
     *
     * ENKAPSULASI: method ini private, hanya bisa dipanggil dari dalam class.
     *
     * @param  string $recipient  Kode atau chat_id
     * @return array              ['chat_id' => string, 'username' => string|null]
     */
    private function resolveTelegramRecipient(string $recipient): array
    {
        if ($recipient === '') {
            return ['chat_id' => '', 'username' => null];
        }

        // Coba cari di file cache — kode sementara disimpan saat pelanggan /start bot
        $payload = Cache::store('file')->get($this->telegramCodeCacheKey($recipient));

        if (is_array($payload) && !empty($payload['chat_id'])) {
            return [
                'chat_id'  => (string) $payload['chat_id'],
                'username' => $payload['username'] ?? null,
            ];
        }

        // Jika input sudah berformat angka (termasuk negatif untuk grup), pakai langsung
        if (preg_match('/^-?\d+$/', $recipient)) {
            return ['chat_id' => $recipient, 'username' => null];
        }

        // Tidak ditemukan di cache dan bukan numerik — tidak valid
        return ['chat_id' => '', 'username' => null];
    }

    /**
     * storeTelegramChatIdForPelanggan()
     *
     * Menyimpan chat_id Telegram ke profil pelanggan setelah pengiriman berhasil.
     * Dengan begitu pengiriman berikutnya tidak perlu input kode lagi.
     *
     * Strategi pencarian pelanggan (cascading):
     *   1. Dari relasi transaksi->pelanggan (sudah di-eager load)
     *   2. Query by id_pelanggan jika relasi belum ter-load
     *   3. Query by nomor HP jika id_pelanggan tidak ada
     *
     * ENKAPSULASI: method ini private, hanya bisa dipanggil dari dalam class.
     *
     * @param  Transaksi    $transaksi
     * @param  string       $chatId
     * @param  string|null  $telegramUsername
     */
    private function storeTelegramChatIdForPelanggan(
        Transaksi $transaksi,
        string $chatId,
        ?string $telegramUsername = null
    ): void {
        if ($chatId === '') return;

        // Strategi 1: Dari relasi yang sudah di-load
        $pelanggan = $transaksi->pelanggan;

        // Strategi 2: Query by id_pelanggan
        if (!$pelanggan && !empty($transaksi->id_pelanggan)) {
            $pelanggan = Pelanggan::find($transaksi->id_pelanggan);
        }

        // Strategi 3: Query by nomor HP (normalize dulu — hapus non-digit)
        if (!$pelanggan) {
            $normalizedNoHp = preg_replace('/[^0-9]/', '', (string) $transaksi->no_hp);

            if ($normalizedNoHp !== '') {
                // Cari pelanggan yang no_hp-nya cocok setelah dinormalisasi
                $pelanggan = Pelanggan::get()->first(function ($item) use ($normalizedNoHp) {
                    return preg_replace('/[^0-9]/', '', (string) $item->no_hp) === $normalizedNoHp;
                });
            }
        }

        if (!$pelanggan) {
            Log::info('Share Telegram berhasil, tapi pelanggan tidak ditemukan untuk simpan chat id.', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id'      => $chatId,
            ]);
            return;
        }

        $payload = ['telegram_chat_id' => $chatId];
        if (!empty($telegramUsername)) {
            $payload['telegram_username'] = $telegramUsername;
        }

        // Perbaiki relasi transaksi-pelanggan jika belum terhubung
        if (!$transaksi->id_pelanggan ||
            (int) $transaksi->id_pelanggan !== (int) $pelanggan->id_pelanggan) {
            $transaksi->update(['id_pelanggan' => $pelanggan->id_pelanggan]);
            $transaksi->setRelation('pelanggan', $pelanggan);
        }

        // Hanya update jika chat_id berubah — hindari query tidak perlu
        if (empty($pelanggan->telegram_chat_id) ||
            (string) $pelanggan->telegram_chat_id !== $chatId) {
            $pelanggan->update($payload);
        }
    }

    /**
     * telegramCodeCacheKey()
     *
     * Menghasilkan key Cache yang konsisten untuk menyimpan/membaca
     * kode Telegram sementara.
     *
     * Format: 'telegram_link_code:{kode}'
     *
     * ENKAPSULASI: private, hanya untuk internal class.
     *
     * @param  string $code  Kode sementara
     * @return string        Key untuk Cache
     */
    private function telegramCodeCacheKey(string $code): string
    {
        return 'telegram_link_code:' . trim($code);
    }

    /**
     * buildShareMessage()
     *
     * Membangun teks pesan ringkasan transaksi yang dikirim ke pelanggan.
     * Format sama untuk Email maupun Telegram.
     *
     * Logika harga per item:
     *   1. Gunakan harga yang tersimpan di detail (historis, tidak berubah)
     *   2. Jika tidak ada, hitung dari subtotal ÷ qty
     *   3. Jika tidak ada subtotal, ambil dari harga master jenis layanan
     *
     * ENKAPSULASI: private, hanya bisa dipanggil dari shareKasir().
     * ABSTRAKSI: shareKasir() tidak perlu tahu cara format pesan dibuat.
     *
     * @param  Transaksi $transaksi  Object transaksi dengan relasi detail
     * @return string                Teks pesan lengkap
     */
    private function buildShareMessage(Transaksi $transaksi): string
    {
        // Bangun baris detail per item menggunakan collection map
        $detailLines = $transaksi->detail->map(function ($item) {
            $qty            = (float) $item->qty;
            $storedHarga    = (float) ($item->harga ?? 0);
            $storedSubtotal = (float) ($item->subtotal ?? 0);
            $hargaJenis     = (float) ($item->jenis?->harga ?? 0);

            // Cascading fallback untuk mendapat harga satuan yang akurat
            $hargaSatuan = $storedHarga > 0
                ? $storedHarga
                : ($qty > 0 && $storedSubtotal > 0
                    ? $storedSubtotal / $qty
                    : $hargaJenis);

            $subtotal = $storedSubtotal > 0 ? $storedSubtotal : ($hargaSatuan * $qty);
            $layanan  = $item->layanan?->nama_layanan ?? 'Layanan';
            $jenis    = $item->jenis?->nama_jenis ? ' (' . $item->jenis->nama_jenis . ')' : '';

            return '- ' . $layanan . $jenis . ': '
                . $item->qty . ' x Rp' . number_format($hargaSatuan, 0, ',', '.')
                . ' = Rp' . number_format($subtotal, 0, ',', '.');
        })->implode("\n"); // Gabungkan semua baris dengan newline

        // Susun teks pesan lengkap
        return "Halo {$transaksi->nama_pelanggan},\n\n"
            . "Pembayaran transaksi laundry Anda berhasil dicatat.\n"
            . "ID Transaksi: {$transaksi->id_transaksi}\n"
            . "Tanggal: " . \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d/m/Y H:i') . "\n"
            . "Status Bayar: " . strtoupper((string) $transaksi->status_bayar) . "\n"
            . "Total Harga: Rp" . number_format((float) $transaksi->total_harga, 0, ',', '.') . "\n"
            . "Diskon: Rp" . number_format((float) $transaksi->diskon, 0, ',', '.') . "\n"
            . "Total Bayar: Rp" . number_format((float) $transaksi->total_bayar, 0, ',', '.') . "\n"
            . (!empty($transaksi->tgl_estimasi)
                ? "Estimasi Selesai: "
                    . \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y H:i') . "\n"
                : '')
            . (!empty($detailLines) ? "\nDetail Layanan:\n{$detailLines}\n" : '')
            . "\nTerima kasih telah menggunakan layanan kami.";
    }

    // ══════════════════════════════════════════════════════════════════
    // UPDATE KETERANGAN — SIMPAN CATATAN TRANSAKSI KE SESSION
    // ══════════════════════════════════════════════════════════════════

    /**
     * updateKeterangan()
     *
     * Menyimpan catatan/keterangan transaksi ke session untuk Super Admin.
     * Dipanggil via AJAX saat user mengetik di kolom keterangan.
     * Tidak ada permission check karena hanya mengubah session sendiri.
     *
     * Route  : POST /transaksi/keterangan
     * Role   : Super Admin
     * Return : JSON
     */
    public function updateKeterangan(Request $request)
    {
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

    /**
     * updateKeteranganKasir()
     *
     * Menyimpan keterangan transaksi ke session untuk Kasir.
     * Menggunakan key session yang sama ('keterangan_transaksi')
     * karena keterangan tidak perlu dipisah per role.
     *
     * Route  : POST /kasir/transaksi/keterangan
     * Role   : Kasir
     * Return : JSON
     */
    public function updateKeteranganKasir(Request $request)
    {
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

    /**
     * updateKeteranganAdmin2()
     *
     * Menyimpan keterangan transaksi ke session untuk Admin.
     *
     * Route  : POST /admin2/transaksi/keterangan
     * Role   : Admin
     * Return : JSON
     */
    public function updateKeteranganAdmin2(Request $request)
    {
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════
    // METHOD LAINNYA
    // ══════════════════════════════════════════════════════════════════

    /**
     * print()
     *
     * Menampilkan halaman cetak struk transaksi.
     * Eager load relasi pelanggan dan detail agar tidak N+1 query.
     *
     * @param  int $id  ID transaksi
     * Route : GET /transaksi/{id}/print
     * Role  : Semua yang punya permission view
     */
    public function print($id)
    {
        requirePermission('transaksi', 'view');

        // findOrFail() akan throw 404 jika transaksi tidak ditemukan
        $transaksi = Transaksi::with('pelanggan', 'detail')->findOrFail($id);

        return view('transaksi.print', compact('transaksi'));
    }

    /**
     * riwayat()
     *
     * Menampilkan riwayat semua transaksi untuk Super Admin.
     * Diurutkan dari yang terbaru (DESC by id_transaksi).
     *
     * Route : GET /transaksi/riwayat
     * Role  : Super Admin
     */
    public function riwayat()
    {
        requirePermission('transaksi', 'view');

        // Ambil semua transaksi beserta detailnya, terbaru di atas
        $data = Transaksi::with('detail')
            ->orderBy('id_transaksi', 'DESC')
            ->get();

        return view('admin.transaksi.riwayat', compact('data'));
    }

    /**
     * tempStoreLayanan()
     *
     * Menyimpan layanan sementara ke session sebelum transaksi dibuat.
     * Digunakan untuk flow multi-step dimana layanan dikumpulkan dulu
     * sebelum pelanggan dipilih.
     *
     * Berbeda dengan addLayanan() yang menyimpan ke 'detail_transaksi',
     * method ini menyimpan ke 'layanan_temp' sebagai draft sementara.
     *
     * Route  : POST /transaksi/layanan/temp
     * Role   : Super Admin
     * Return : JSON
     */
    public function tempStoreLayanan(Request $request)
    {
        requirePermission('transaksi', 'add');

        // Validasi input sebelum disimpan ke session
        $request->validate([
            'id_jenis_layanan' => 'required|exists:jenis_layanan,id_layanan',
            'qty'              => 'required|numeric|min:0.01',
            'parfum'           => 'nullable|exists:parfum,id_parfum',
        ]);

        // Ambil data layanan sementara yang sudah ada, default array kosong
        $layananSementara = session()->get('layanan_temp', []);

        // Tambahkan item baru ke draft
        $layananSementara[] = [
            'id_jenis_layanan' => $request->id_jenis_layanan,
            'qty'              => $request->qty,
            'parfum'           => $request->parfum,
        ];

        session(['layanan_temp' => $layananSementara]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan sementara!',
            'data'    => $layananSementara
        ]);
    }
}