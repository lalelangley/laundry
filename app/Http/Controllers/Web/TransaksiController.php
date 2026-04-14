<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Layanan;
use App\Models\Transaksi;
use App\Models\MetodeBayar;
use App\Models\Parfum;
use App\Models\JenisLayanan;

class TransaksiController extends Controller
{
    /**
     * Controller ini menangani alur transaksi laundry.
     *
     * Penjelasan konsep coding yang bisa dipresentasikan:
     * - OOP        : file ini adalah CLASS controller yang mewarisi fitur dari parent class Controller.
     * - Method     : setiap function di dalam class ini adalah METHOD yang menjalankan tugas tertentu.
     * - Object     : saat route memanggil controller, Laravel membuat object dari class ini.
     * - Struktur data:
     *   - Request  : object berisi input dari form/browser.
     *   - Session  : menyimpan data transaksi sementara sebelum masuk database.
     *   - Array    : detail transaksi disusun sebagai array item layanan.
     *   - Model    : Transaksi, Pelanggan, Layanan, dll dipakai sebagai representasi tabel database.
     * - Percabangan: if/elseif dipakai untuk menentukan alur bayar, diskon, dan validasi.
     * - Perulangan : foreach dipakai saat menyimpan semua detail item transaksi.
     *
     * Dipakai oleh:
     * - admin
     * - kasir
     * - admin2
     *
     * Proses utamanya:
     * - pilih pelanggan
     * - tambah layanan
     * - hitung total, diskon, dan DP
     * - simpan transaksi
     * - kirim ringkasan via email atau Telegram
     *
     * Kaitan dengan unit kompetensi:
     * - Unit 1: memakai struktur data request, session, model, dan relasi database
     * - Unit 3: menjalankan source code berdasarkan route, request, dan response
     * - Unit 4: menerapkan validasi, permission, dan penanganan error dasar
     * - Unit 5: memakai array, percabangan, perulangan implisit, dan fungsi bantu
     * - Unit 6: memberi dokumentasi method agar modul mudah dijelaskan
     * - Unit 7: menyiapkan log, try-catch, dan alur debug saat transaksi gagal
     *
     * Catatan istilah untuk presentasi/ujian:
     * - Semua function yang berada di dalam class ini disebut METHOD.
     * - Method public dipanggil dari route/HTTP request lalu mengatur alur transaksi.
     * - Method private dipakai sebagai helper method/fungsi bantu internal controller.
     * - "Fungsi" berarti kegunaan dari method.
     * - "Prosedur" berarti urutan langkah yang dikerjakan di dalam method.
     */

    /**
     * Menyiapkan query daftar pelanggan untuk fitur cari dan urut.
     *
     * Langkahnya:
     * - ambil kata kunci pencarian
     * - filter nama, email, atau nomor HP
     * - urutkan hasil sesuai pilihan user
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildPelangganPickerQuery(Request $request)
    {
        /*
         * SECTION: OOP dan Struktur Data
         * - $request adalah OBJECT Request dari Laravel.
         * - $query adalah object Query Builder dari model Pelanggan.
         * - Method ini menunjukkan bahwa filter data bisa dibangun bertahap
         *   menggunakan input request dan percabangan.
         */
        $query = Pelanggan::query();
        $search = trim((string) $request->get('search', ''));

        if ($search !== '') {
            // Group where ini membuat pencarian nama/email/no hp tetap berada dalam satu blok OR.
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_pelanggan', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('no_hp', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->get('sort', 'nama_asc');

        // match() dipakai supaya pilihan urut lebih ringkas dibanding banyak if-else.
        match ($sort) {
            'nama_desc' => $query->orderBy('nama_pelanggan', 'desc'),
            'terbaru' => $query->orderBy('id_pelanggan', 'desc'),
            'terlama' => $query->orderBy('id_pelanggan', 'asc'),
            default => $query->orderBy('nama_pelanggan', 'asc'),
        };

        return $query;
    }

    // ==========================
    // 1. HALAMAN AWAL TRANSAKSI - ADMIN
    // ==========================
    /**
     * METHOD public: index()
     * Fungsi:
     * - menampilkan halaman awal transaksi admin
     * - membaca pelanggan dan keranjang transaksi dari session
     *
     * Prosedur:
     * - cek permission view
     * - reset detail transaksi bila bukan mode keep
     * - kirim data session ke view admin.transaksi.index
     */
    public function index(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        if (!$request->has('keep')) {
            session()->forget('detail_transaksi');
        }

        return view('admin.transaksi.index', [
            'pelanggan' => session('pelanggan'),
            'detail'    => session('detail_transaksi', []),
        ]);
    }

    // ==========================
    // 2. HALAMAN CREATE - ADMIN
    // ==========================
    /**
     * METHOD public: create()
     * Fungsi:
     * - menampilkan form/create transaksi admin
     * - menghitung total sementara dari item yang masih ada di session
     *
     * Prosedur:
     * - cek permission view
     * - ambil pelanggan, detail, dan keterangan dari session
     * - hitung total harga dari array detail_transaksi
     * - tampilkan view transaksi.create
     */
    public function create()
    {
        // Halaman awal transaksi cukup butuh akses view.
        // Aksi perubahan data tetap dijaga oleh endpoint yang memakai permission:add.
        requirePermission('transaksi', 'view');
        
        /*
         * SECTION: Struktur Data Session dan Array
         * - Session dipakai sebagai penyimpanan sementara seperti keranjang transaksi.
         * - detail_transaksi berbentuk ARRAY yang berisi item layanan.
         * - Perhitungan total memakai array_map() untuk membuat subtotal,
         *   lalu array_sum() untuk menjumlahkan semua subtotal.
         */
        return view('transaksi.create', [
            'pelanggan'  => session('pelanggan'),
            'detail'     => session('detail_transaksi', []),
            'keterangan' => session('keterangan_transaksi'),
            'total'      => array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
            ),
        ]);
    }

    // ==========================
    // 3. PILIH PELANGGAN - ADMIN
    // ==========================
    /**
     * METHOD public: pilihPelanggan()
     * Fungsi:
     * - menampilkan daftar pelanggan yang bisa dipilih untuk transaksi admin
     *
     * Prosedur:
     * - cek permission view
     * - panggil helper method buildPelangganPickerQuery()
     * - paginate hasil query
     * - kirim ke view transaksi.pelanggan
     */
    public function pilihPelanggan(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan = $this->buildPelangganPickerQuery($request)
            ->paginate(10)
            ->withQueryString();
        return view('transaksi.pelanggan', compact('pelanggan'));
    }

    /**
     * METHOD public: setPelanggan()
     * Fungsi:
     * - menyimpan pelanggan terpilih ke session transaksi admin
     *
     * Prosedur:
     * - cek permission add
     * - cari pelanggan berdasarkan id
     * - jika tidak ada, kembalikan error
     * - salin data penting pelanggan ke session
     * - redirect ke halaman create transaksi
     */
    public function setPelanggan($id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        /*
         * SECTION: OOP Model dan Session
         * - Pelanggan::find() adalah METHOD dari model Eloquent untuk mencari data berdasarkan id.
         * - Hasil data pelanggan disimpan ke session dalam bentuk ARRAY asosiatif
         *   agar bisa dipakai ulang pada proses transaksi berikutnya.
         */
        $p = Pelanggan::find($id);
        if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

        session([
            'pelanggan' => [
                'id_pelanggan'   => $p->id_pelanggan,
                'nama_pelanggan' => $p->nama_pelanggan,
                'no_hp'          => $p->no_hp,
                'foto'           => $p->gambar,
            ]
        ]);

        return redirect()->route('transaksi.create');
    }

    /**
     * METHOD public: setPelangganKasir()
     * Fungsi:
     * - menyimpan pelanggan yang dipilih kasir ke session transaksi kasir
     *
     * Prosedur:
     * - cek permission view
     * - cari pelanggan
     * - salin identitas pelanggan beserta data Telegram ke session pelanggan_kasir
     * - redirect ke halaman create transaksi kasir
     *
     * @param int|string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setPelangganKasir($id)
    {
        requirePermission('transaksi', 'view');
        
        $p = Pelanggan::find($id);
        if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

        session([
            'pelanggan_kasir' => [
                'id_pelanggan'   => $p->id_pelanggan,
                'nama_pelanggan' => $p->nama_pelanggan,
                'no_hp'          => $p->no_hp,
                'email'          => $p->email,
                'telegram_chat_id' => $p->telegram_chat_id,
                'telegram_username' => $p->telegram_username,
                'foto'           => $p->gambar 
                    ? (str_starts_with($p->gambar, 'pelanggan/') 
                        ? $p->gambar 
                        : 'pelanggan/' . $p->gambar)
                    : null,
            ]
        ]);

        return redirect()->route('kasir.transaksi.create');
    }

    /**
     * METHOD public: setPelangganAdmin2()
     * Fungsi:
     * - menyimpan pelanggan terpilih ke session transaksi panel admin2
     *
     * Prosedur:
     * - cek permission view
     * - cari pelanggan
     * - simpan data pelanggan ke key session khusus admin2
     * - redirect ke halaman create admin2
     */
    public function setPelangganAdmin2($id)
    {
        requirePermission('transaksi', 'view');
        
        // Admin2 memakai key session berbeda agar alur antar panel tidak saling bentrok.
        $p = Pelanggan::find($id);
        if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

        session([
            'pelanggan_transaksi' => [
                'id_pelanggan'   => $p->id_pelanggan,
                'nama_pelanggan' => $p->nama_pelanggan,
                'no_hp'          => $p->no_hp,
                'foto'           => $p->gambar 
                    ? (str_starts_with($p->gambar, 'pelanggan/') 
                        ? $p->gambar 
                        : 'pelanggan/' . $p->gambar)
                    : null,
            ]
        ]);

        return redirect()->route('admin2.transaksi.create');
    }

    // ==========================
    // 4. TAMBAH LAYANAN - ADMIN
    // ==========================
    /**
     * METHOD public: addLayanan()
     * Fungsi:
     * - menambahkan satu layanan ke keranjang transaksi admin
     *
     * Prosedur:
     * - cek permission add
     * - cari layanan beserta jenis dan satuannya
     * - tentukan jenis layanan yang dipilih
     * - bentuk item cart dari request + data database
     * - simpan item ke session detail_transaksi
     * - kembalikan response JSON
     */
    public function addLayanan(Request $request, $id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $layanan = Layanan::with('jenis.satuan')->find($id);
        if (!$layanan) {
            return response()->json(['error' => 'Layanan tidak ditemukan'], 404);
        }

        $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
        $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

        if (!$jenis) {
            return response()->json(['error' => 'Jenis layanan tidak ditemukan'], 404);
        }

        $cart = session()->get('detail_transaksi', []);

        $cart[] = [
            'id_layanan'        => $layanan->id_layanan,
            'nama_layanan'      => $layanan->nama_layanan,
            'id_jenis_layanan'  => $jenis->id_jenis_layanan,
            'jenis'             => $jenis->nama_jenis,
            'harga'             => $jenis->harga,
            'qty'               => $request->qty ?? 1,
            'satuan'            => $jenis->satuan->nama_satuan ?? '',
            'keterangan'        => $request->keterangan ?? '-',
            'id_parfum'         => $request->parfum ?? null,
            'parfum_nama'       => $request->parfum ? Parfum::find($request->parfum)?->nama_parfum : null,
        ];

        session()->put('detail_transaksi', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan',
        ]);
    }
// ==========================
    // 5. HAPUS LAYANAN - ADMIN
    // ==========================
    
    /**
     * METHOD public: remove()
     * Fungsi:
     * - menghapus item layanan pada cart admin berdasarkan index array session
     *
     * Prosedur:
     * - cek permission edit
     * - ambil cart dari session
     * - hapus item berdasarkan index
     * - rapikan ulang index array
     * - simpan kembali ke session
     * - redirect back dengan pesan sukses
     *
     * @param int $index - Index array (0, 1, 2, dst)
     */
    public function remove($index)
    {
        // ✅ CHECK PERMISSION EDIT (remove = edit cart)
        requirePermission('transaksi', 'edit');
        
        $cart = session('detail_transaksi', []);
        
        // ✅ HAPUS BERDASARKAN INDEX
        if (isset($cart[$index])) {
            unset($cart[$index]);
            
            // ✅ RE-INDEX ARRAY (penting biar index tetep 0,1,2,...)
            $cart = array_values($cart);
        }
        
        session()->put('detail_transaksi', $cart);
        
        return back()->with('success', 'Layanan berhasil dihapus dari keranjang');
    }
    
    /**
     * METHOD public: removeAdmin2()
     * Fungsi:
     * - menghapus item layanan pada cart admin2
     *
     * @param int $index - Index array
     */
    public function removeAdmin2($index)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('transaksi', 'edit');
        
        $cart = session('detail_transaksi', []);
        
        // ✅ HAPUS BERDASARKAN INDEX
        if (isset($cart[$index])) {
            unset($cart[$index]);
            
            // ✅ RE-INDEX ARRAY
            $cart = array_values($cart);
        }
        
        session()->put('detail_transaksi', $cart);
        
        return back()->with('success', 'Layanan berhasil dihapus dari keranjang');
    }
    
    /**
     * METHOD public: removeKasir()
     * Fungsi:
     * - menghapus item layanan pada cart kasir
     *
     * @param int $index - Index array
     */
    public function removeKasir($index)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('transaksi', 'edit');
        
        $cart = session('detail_transaksi', []);
        
        // ✅ HAPUS BERDASARKAN INDEX
        if (isset($cart[$index])) {
            unset($cart[$index]);
            
            // ✅ RE-INDEX ARRAY
            $cart = array_values($cart);
        }
        
        session()->put('detail_transaksi', $cart);
        
        return back()->with('success', 'Layanan berhasil dihapus dari keranjang');
    }

    // ==========================
    // 6. HALAMAN CHECKOUT - ADMIN
    // ==========================
    /**
     * METHOD public: checkout()
     * Fungsi:
     * - menyimpan transaksi admin dari data session ke database
     *
     * Prosedur:
     * - cek permission add
     * - ambil pelanggan dan detail dari session
     * - validasi data transaksi tidak kosong
     * - hitung total
     * - simpan header transaksi
     * - simpan setiap detail item ke tabel detail_transaksi
     * - hapus session transaksi
     * - redirect ke riwayat
     */
    public function checkout(Request $request)
    {
        requirePermission('transaksi', 'add');
        
        try {
            $pelanggan = session('pelanggan_transaksi');
            $detail    = session('detail_transaksi', []);

            if (!$pelanggan || count($detail) === 0) {
                return redirect()
                    ->route('transaksi.create')
                    ->with('error', 'Data transaksi tidak lengkap');
            }

            // Hitung total seluruh item layanan yang dipilih.
            $total = array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
            );

            // Simpan header transaksi utama.
            $transaksi = \App\Models\Transaksi::create([
                'id_pelanggan' => $pelanggan['id_pelanggan'],
                'tanggal'      => now()->format('Y-m-d H:i:s'),
                'total_harga'  => $total,
                'status'       => 'proses',
                'keterangan'   => session('keterangan_transaksi'),
                'id_kasir'     => auth()->id(),
            ]);

            // Simpan rincian item layanan sebagai detail transaksi.
            foreach ($detail as $d) {
                \App\Models\DetailTransaksi::create([
                    'id_transaksi'     => $transaksi->id_transaksi,
                    'id_jenis_layanan' => $d['id_jenis_layanan'],
                    'qty'              => $d['qty'],
                    'harga'            => $d['harga'],
                    'id_parfum'        => $d['id_parfum'] ?? null,
                ]);
            }

            // Bersihkan session transaksi setelah data berhasil diproses.
            session()->forget([
                'pelanggan_transaksi',
                'detail_transaksi',
                'keterangan_transaksi'
            ]);

            return redirect()
                ->route('riwayat.index')
                ->with('success', 'Transaksi berhasil disimpan');

        } catch (\Throwable $e) {
            \Log::error($e);

            return redirect()
                ->route('transaksi.confirm')
                ->with('error', 'Gagal menyimpan transaksi');
        }
    }

    // ==========================
    // 7. SIMPAN TRANSAKSI - ADMIN
    // ==========================
    /**
     * METHOD public: bayar()
     * Fungsi:
     * - memproses pembayaran transaksi admin secara lengkap
     * - menghitung diskon, DP, status bayar, lalu menyimpan transaksi dan detailnya
     *
     * Prosedur:
     * - baca pelanggan dan keranjang dari session
     * - validasi transaksi tidak kosong
     * - baca input diskon, DP, metode bayar, dan estimasi
     * - hitung total awal dan total akhir
     * - tentukan status bayar: lunas, DP, atau belum_lunas
     * - simpan transaksi utama
     * - simpan detail transaksi satu per satu
     * - hapus session transaksi
     * - kirim response JSON sukses/gagal
     */
    public function bayar(Request $request)
    {
    try {
        \Log::info('🟢 BAYAR METHOD CALLED');
        \Log::info('Request data:', $request->all());
        
        /*
         * SECTION: Struktur Data Transaksi
         * - Session menyimpan pelanggan dan detail_transaksi sementara.
         * - $detail adalah ARRAY item layanan yang nantinya dihitung lalu disimpan ke database.
         * - $request membawa input pembayaran seperti diskon, DP, metode bayar, dan estimasi.
         */
        $pelanggan = session('pelanggan');
        $detail    = session('detail_transaksi', []);
        
        \Log::info('Session pelanggan:', $pelanggan ? ['found' => true] : ['found' => false]);
        \Log::info('Session detail count:', ['count' => count($detail)]);

        if (!$pelanggan || empty($detail)) {
            \Log::error('❌ Validation failed: pelanggan or detail empty');
            return response()->json(['error' => 'Transaksi tidak valid'], 400);
        }

        $diskon       = floatval($request->input('diskon', 0));
        $dp           = floatval($request->input('dp', 0));
        $langsungBayar= intval($request->input('langsung_bayar', 0));
        $keterangan   = $request->input('keterangan', '-');
        $idMetodeBayar= $request->input('id_metode_bayar', 1);
        $tglEstimasi  = $request->input('tgl_estimasi', now());

        /*
         * SECTION: Logika Hitungan
         * - subtotal item = harga x qty
         * - totalAwal = jumlah semua subtotal
         * - jika tipe diskon percent, nilai diskon diubah dulu ke nominal
         * - totalAkhir = totalAwal - diskon
         */
        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

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

        /*
         * SECTION: Percabangan Status Bayar
         * - if pertama: transaksi dianggap lunas
         * - elseif kedua: transaksi dianggap DP
         * - else: transaksi belum lunas
         * Bagian ini adalah contoh percabangan bisnis pada sistem.
         */
        $totalBayar = $dp;
        if ($langsungBayar === 1 || $dp >= $totalAkhir) {
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now()->format('Y-m-d H:i:s'); // ✅ Format lengkap
            $dp = 0;
        } elseif ($dp > 0) {
            $statusBayar = 'DP';
            $tglLunas = null;
        } else {
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
        }
        
        \Log::info('💰 Calculated values:', [
            'totalAwal' => $totalAwal,
            'diskon' => $diskon,
            'totalAkhir' => $totalAkhir,
            'statusBayar' => $statusBayar
        ]);

        /*
         * SECTION: OOP dan Penyimpanan Data
         * - Transaksi::create() adalah METHOD model Eloquent untuk menyimpan header transaksi.
         * - Header transaksi berisi ringkasan transaksi utama.
         * - Detail item akan disimpan terpisah ke tabel detail_transaksi.
         */
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
            'tgl_transaksi'    => now()->format('Y-m-d H:i:s'), // ✅ FIX: Format lengkap dengan jam
            'tgl_estimasi'     => $tglEstimasi,
            'tgl_lunas'        => $tglLunas,
            'id_kasir'         => auth()->id() ?? 1,
            'nama_kasir'       => auth()->user()->name ?? 'Admin',
            'id_metode_bayar'  => $idMetodeBayar,
        ]);
        
        \Log::info('✅ Transaction created:', ['id' => $trans->id_transaksi]);

        /*
         * SECTION: Perulangan dan Relasi Data
         * - foreach dipakai untuk memproses setiap item detail layanan.
         * - Setiap item disimpan ke tabel detail transaksi.
         * - Pemisahan header dan detail menunjukkan struktur data relasional pada database.
         */
        foreach ($detail as $d) {
            \Log::info('Detail item:', $d);
            $jenis = \App\Models\JenisLayanan::with('satuan')
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

        session()->forget(['pelanggan', 'detail_transaksi', 'keterangan_transaksi']);
        
        \Log::info('✅ SUCCESS - Transaction saved');

        return response()->json([
            'success'      => true,
            'id_transaksi' => $trans->id_transaksi,
            'total'        => $totalAkhir,
            'bayar'        => $totalBayar,
            'nama'         => $pelanggan['nama_pelanggan'],
            'hp'           => $pelanggan['no_hp'],
            'email'        => $pelanggan['email'] ?? null,
            'telegram_chat_id' => $pelanggan['telegram_chat_id'] ?? null,
            'status_bayar' => $statusBayar,
            'diskon'       => $diskon,
            'total_bayar'  => $totalBayar,
            'tgl_lunas'    => $tglLunas,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('❌ ERROR in bayar():', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
    }

    // ==========================
    // KASIR - CREATE TRANSAKSI
    // ==========================
    /**
     * METHOD public: createKasir()
     * Fungsi:
     * - menampilkan form transaksi kasir
     * - membaca data transaksi sementara milik kasir dari session
     */
public function createKasir()
{
    // Halaman awal transaksi cukup butuh akses view.
    // Aksi perubahan data tetap dijaga oleh endpoint yang memakai permission:add.
    requirePermission('transaksi', 'view');
    
    /*
     * SECTION: Session Role Kasir
     * - Kasir memakai key session pelanggan_kasir agar data transaksi role kasir
     *   tidak bercampur dengan data transaksi admin.
     * - Total tetap dihitung dari array detail_transaksi yang ada di session.
     */
    return view('kasir.transaksi.create', [
        'pelanggan'  => session('pelanggan_kasir'),  // ✅ FIX
        'detail'     => session('detail_transaksi', []),
        'keterangan' => session('keterangan_transaksi'),
        'total'      => array_sum(
            array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
        ),
    ]);
}

    // ==========================
    // KASIR - PILIH PELANGGAN
    // ==========================
    /**
     * METHOD public: pelangganKasir()
     * Fungsi:
     * - menampilkan daftar pelanggan untuk transaksi kasir
     */
    public function pelangganKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan = $this->buildPelangganPickerQuery($request)
            ->paginate(10)
            ->withQueryString();
        return view('kasir.transaksi.pelanggan', compact('pelanggan'));
    }

    // ==========================
    // KASIR - BAYAR
    // ==========================
  // ==========================
// KASIR - BAYAR (DIPERBAIKI - SAMAKAN DENGAN SUPER ADMIN)
// ==========================
    /**
     * METHOD public: bayarKasir()
     * Fungsi:
     * - menyimpan transaksi kasir lengkap dengan detail layanan dan status bayar
     *
     * Prosedur:
     * - ambil pelanggan_kasir dan detail_transaksi dari session
     * - validasi transaksi tidak kosong
     * - hitung total, diskon, DP, dan status bayar
     * - simpan header transaksi
     * - simpan item detail transaksi
     * - kosongkan session kasir
     * - kirim JSON hasil pembayaran
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bayarKasir(Request $request)
    {
        try {
            \Log::info('🟢 BAYAR KASIR METHOD CALLED');
            \Log::info('Request data:', $request->all());
            
            // Ambil data pelanggan dan keranjang transaksi dari session kasir.
            $pelanggan = session('pelanggan_kasir');
            $detail    = session('detail_transaksi', []);
            
            \Log::info('Session pelanggan:', $pelanggan ? ['found' => true, 'data' => $pelanggan] : ['found' => false]);
            \Log::info('Session detail count:', ['count' => count($detail)]);

            if (!$pelanggan || empty($detail)) {
                \Log::error('❌ Validation failed: pelanggan or detail empty');
                return response()->json([
                    'error' => 'Transaksi tidak valid. Silakan pilih pelanggan dan layanan terlebih dahulu.'
                ], 400);
            }

            // Baca input pembayaran dari form checkout.
            $diskon       = floatval($request->input('diskon', 0));
            $dp           = floatval($request->input('dp', 0));
            $langsungBayar= intval($request->input('langsung_bayar', 0));
            $keterangan   = $request->input('keterangan', '-');
            $idMetodeBayar= $request->input('id_metode_bayar', 1);
            $tglEstimasi  = $request->input('tgl_estimasi', now());

            /*
             * SECTION: Logika Hitungan Kasir
             * - Rumus dasarnya sama: harga x qty, lalu dijumlahkan.
             * - Ini menunjukkan reuse logic yang sama pada role berbeda.
             */
            $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

            // Dukung diskon nominal atau persentase.
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

            // Tentukan status pembayaran berdasarkan kombinasi langsung bayar dan DP.
            $totalBayar = $dp;
            if ($langsungBayar === 1 || $dp >= $totalAkhir) {
                $totalBayar = $totalAkhir;
                $statusBayar = 'lunas';
                $tglLunas = now()->format('Y-m-d H:i:s');
                $dp = 0;
            } elseif ($dp > 0) {
                $statusBayar = 'DP';
                $tglLunas = null;
            } else {
                $statusBayar = 'belum_lunas';
                $tglLunas = null;
            }
            
            \Log::info('💰 Calculated values:', [
                'totalAwal' => $totalAwal,
                'diskon' => $diskon,
                'totalAkhir' => $totalAkhir,
                'statusBayar' => $statusBayar
            ]);

            /*
             * SECTION: OOP dan Struktur Data Kasir
             * - Model Transaksi dipakai lagi untuk menyimpan header transaksi.
             * - Struktur penyimpanan tetap dipisah antara header dan detail.
             * - Ini memperlihatkan desain kode yang konsisten antar role.
             */
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

            // Simpan item-item layanan ke tabel detail transaksi.
            foreach ($detail as $d) {
                $jenis = \App\Models\JenisLayanan::with('satuan')
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

            // Bersihkan session agar transaksi berikutnya dimulai dari state kosong.
            session()->forget(['pelanggan_kasir', 'detail_transaksi', 'keterangan_transaksi']);
            
            \Log::info('✅ SUCCESS - Transaction saved');

            return response()->json([
                'success'      => true,
                'id_transaksi' => $trans->id_transaksi,
                'total'        => $totalAkhir,
                'bayar'        => $totalBayar,
                'nama'         => $pelanggan['nama_pelanggan'],
                'hp'           => $pelanggan['no_hp'],
                'email'        => $pelanggan['email'] ?? null,
                'telegram_chat_id' => $pelanggan['telegram_chat_id'] ?? null,
                'status_bayar' => $statusBayar,
                'diskon'       => $diskon,
                'total_bayar'  => $totalBayar,
                'tgl_lunas'    => $tglLunas,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('❌ ERROR in bayarKasir():', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    // ==========================
    // ADMIN2 - CREATE TRANSAKSI
    // ==========================
    /**
     * METHOD public: createAdmin2()
     * Fungsi:
     * - menampilkan form transaksi untuk panel admin2
     */
    public function createAdmin2()
    {
        // Halaman awal transaksi cukup butuh akses view.
        // Aksi perubahan data tetap dijaga oleh endpoint yang memakai permission:add.
        requirePermission('transaksi', 'view');
        
        return view('admin2.transaksi.create', [
            'pelanggan'  => session('pelanggan_transaksi'),
            'detail'     => session('detail_transaksi', []),
            'keterangan' => session('keterangan_transaksi'),
            'parfum'     => \App\Models\Parfum::all(),
            'total'      => array_sum(
                array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
            ),
        ]);
    }

    // ==========================
    // ADMIN2 - PILIH PELANGGAN
    // ==========================
    /**
     * METHOD public: pelangganAdmin2()
     * Fungsi:
     * - menampilkan daftar pelanggan untuk transaksi admin2
     */
    public function pelangganAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan = $this->buildPelangganPickerQuery($request)
            ->paginate(10)
            ->withQueryString();
        return view('admin2.transaksi.pelanggan', compact('pelanggan'));
    }

// ==========================
// KASIR - CHECKOUT (DIPERBAIKI)
// ==========================
/**
 * METHOD public: checkoutKasir()
 * Fungsi:
 * - menyimpan transaksi checkout kasir langsung dari session ke database
 */
public function checkoutKasir(Request $request)
{
    requirePermission('transaksi', 'add');
    
    try {
        // ✅ FIX: Gunakan 'pelanggan_kasir'
        $pelanggan = session('pelanggan_kasir');
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
            'status_transaksi' => 'antrian',
            'status_bayar'     => 'belum_lunas',
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

        // ✅ FIX: Forget 'pelanggan_kasir'
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
// =============================
// ADMIN2 - CHECKOUT (DIPERBAIKI)
// =============================
/**
 * METHOD public: checkoutAdmin2()
 * Fungsi:
 * - menyimpan transaksi checkout admin2 dari session ke database
 */
 public function checkoutAdmin2(Request $request)
{
    requirePermission('transaksi', 'add');
    
    try {
        $pelanggan = session('pelanggan_transaksi');
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
            'tanggal'      => now()->format('Y-m-d H:i:s'), // ✅ FIX
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
// =============================
// ADMIN2 - BAYAR (DIPERBAIKI)
// =============================
// =============================
// ADMIN2 - BAYAR (DIPERBAIKI - SAMAKAN DENGAN SUPER ADMIN)
// =============================
/**
 * METHOD public: bayarAdmin2()
 * Fungsi:
 * - memproses pembayaran transaksi admin2
 * - alurnya sama seperti admin, tetapi memakai session pelanggan_transaksi
 */
public function bayarAdmin2(Request $request)
{
    try {
        \Log::info('🟢 BAYAR ADMIN2 METHOD CALLED');
        \Log::info('Request data:', $request->all());
        
        // ✅ FIX: Gunakan 'pelanggan_transaksi' sesuai dengan setPelangganAdmin2()
        $pelanggan = session('pelanggan_transaksi');
        $detail    = session('detail_transaksi', []);
        
        \Log::info('Session pelanggan:', $pelanggan ? ['found' => true, 'data' => $pelanggan] : ['found' => false]);
        \Log::info('Session detail count:', ['count' => count($detail)]);

        if (!$pelanggan || empty($detail)) {
            \Log::error('❌ Validation failed: pelanggan or detail empty');
            return response()->json([
                'error' => 'Transaksi tidak valid. Silakan pilih pelanggan dan layanan terlebih dahulu.'
            ], 400);
        }

        $diskon       = floatval($request->input('diskon', 0));
        $dp           = floatval($request->input('dp', 0));
        $langsungBayar= intval($request->input('langsung_bayar', 0));
        $keterangan   = $request->input('keterangan', '-');
        $idMetodeBayar= $request->input('id_metode_bayar', 1);
        $tglEstimasi  = $request->input('tgl_estimasi', now());

        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

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
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now()->format('Y-m-d H:i:s');
            $dp = 0;
        } elseif ($dp > 0) {
            $statusBayar = 'DP';
            $tglLunas = null;
        } else {
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
        }
        
        \Log::info('💰 Calculated values:', [
            'totalAwal' => $totalAwal,
            'diskon' => $diskon,
            'totalAkhir' => $totalAkhir,
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
            $jenis = \App\Models\JenisLayanan::with('satuan')
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

        // ✅ FIX: Forget 'pelanggan_transaksi' bukan 'pelanggan'
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
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
// =============================
// ADMIN2 - CONFIRM (DIPERBAIKI)
// =============================
/**
 * METHOD public: confirmAdmin2()
 * Fungsi:
 * - menampilkan halaman konfirmasi checkout admin2 sebelum proses bayar
 */
public function confirmAdmin2()
{
    requirePermission('transaksi', 'view');
    
    $pelanggan  = session('pelanggan_transaksi');
    $detail     = session('detail_transaksi', []);
    $keterangan = session('keterangan_transaksi', '');

    \Log::info('Admin2 Confirm:', [
        'has_pelanggan' => !empty($pelanggan),
        'detail_count' => count($detail)
    ]);

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

    $totalHarga = array_sum(
        array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
    );

    $metode_bayar = \App\Models\MetodeBayar::all();

    return view('admin2.transaksi.checkout', compact(
        'pelanggan',
        'detail',
        'keterangan',
        'totalHarga',
        'metode_bayar'
    ));
}


    // ==========================
    // ADD LAYANAN METHODS
    // ==========================
    /**
     * METHOD public: addLayananKasir()
     * Fungsi:
     * - menambahkan layanan ke cart transaksi kasir
     */
    public function addLayananKasir(Request $request, $id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $layanan = Layanan::with('jenis.satuan')->find($id);
        if (!$layanan) {
            return response()->json(['success'=>false,'message' => 'Layanan tidak ditemukan'], 404);
        }

        $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
        $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

        if (!$jenis) {
            return response()->json(['success'=>false,'message' => 'Jenis layanan tidak ditemukan'], 404);
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
            'parfum_nama'      => $request->parfum ? Parfum::find($request->parfum)?->nama_parfum : null,
        ];

        session()->put('detail_transaksi', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan (Kasir)',
        ]);
    }

    /**
     * METHOD public: addLayananAdmin2()
     * Fungsi:
     * - menambahkan layanan ke cart transaksi admin2
     */
    public function addLayananAdmin2(Request $request, $id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $layanan = Layanan::with('jenis.satuan')->find($id);
        if (!$layanan) {
            return response()->json(['success'=>false,'message' => 'Layanan tidak ditemukan'], 404);
        }

        $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
        $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

        if (!$jenis) {
            return response()->json(['success'=>false,'message' => 'Jenis layanan tidak ditemukan'], 404);
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
            'parfum_nama'      => $request->parfum ? Parfum::find($request->parfum)?->nama_parfum : null,
        ];

        session()->put('detail_transaksi', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan (Admin)',
        ]);
    }

    // ==========================
    // ADD JENIS METHODS
    // ==========================
    /**
     * METHOD public: addJenis()
     * Fungsi:
     * - menambahkan jenis layanan tertentu ke cart transaksi
     *
     * Prosedur:
     * - validasi qty dan parfum
     * - cari jenis layanan
     * - load relasi layanan dan satuan
     * - bentuk item cart
     * - simpan ke session detail_transaksi
     * - kirim response JSON
     */
    public function addJenis(Request $request, $idJenis)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        try {
            $request->validate([
                'qty'    => 'required|numeric|min:0.01',
                'parfum' => 'nullable|exists:parfum,id_parfum',
            ]);

            $jenis = JenisLayanan::find($idJenis);
            
            \Log::info('Jenis data:', [
                'id_jenis'   => $jenis->id_jenis_layanan,
                'id_layanan' => $jenis->id_layanan,  // ← cek ini null atau tidak
            ]);
            
            if (!$jenis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis layanan tidak ditemukan',
                ], 404);
            }

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
                'gambar'           => $jenis->gambar,
            ];

            $cart[] = $newItem;
            session()->put('detail_transaksi', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Jenis layanan berhasil ditambahkan',
                'data' => $newItem,
                'cart_count' => count($cart)
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
     * METHOD public: addJenisKasir()
     * Fungsi:
     * - wrapper method untuk memakai logika addJenis() pada panel kasir
     */
    public function addJenisKasir(Request $request, $idJenis)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        // Same logic as addJenis...
        return $this->addJenis($request, $idJenis);
    }

    /**
     * METHOD public: addJenisAdmin2()
     * Fungsi:
     * - wrapper method untuk memakai logika addJenis() pada panel admin2
     */
    public function addJenisAdmin2(Request $request, $idJenis)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        // Same logic as addJenis...
        return $this->addJenis($request, $idJenis);
    }

    // ==========================
    // CONFIRM METHODS
    // ==========================
    /**
     * METHOD public: confirm()
     * Fungsi:
     * - menampilkan halaman konfirmasi checkout admin
     * - validasi pelanggan dan detail sudah dipilih sebelum bayar
     */
    public function confirm()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan  = session('pelanggan');
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

        $totalHarga = array_sum(
            array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
        );

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
     * METHOD public: confirmKasir()
     * Fungsi:
     * - menampilkan halaman konfirmasi checkout untuk kasir
     *
     * Prosedur:
     * - ambil pelanggan dan detail dari session kasir
     * - validasi data transaksi
     * - hitung total harga
     * - tampilkan view checkout kasir
     */
    public function confirmKasir()
    {
        requirePermission('transaksi', 'view');
        
        $pelanggan  = session('pelanggan_kasir');
        $detail     = session('detail_transaksi', []);
        $keterangan = session('keterangan_transaksi', '');

        \Log::info('Kasir Confirm:', [
            'has_pelanggan' => !empty($pelanggan),
            'detail_count' => count($detail)
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

        $totalHarga = array_sum(
            array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
        );

        $metode_bayar = \App\Models\MetodeBayar::all();

        return view('kasir.transaksi.checkout', compact(
            'pelanggan',
            'detail',
            'keterangan',
            'totalHarga',
            'metode_bayar'
        ));
    }

    /**
     * METHOD public: shareKasir()
     * Fungsi:
     * - mengirim ringkasan transaksi kasir ke email atau Telegram
     *
     * Prosedur:
     * - validasi request channel dan id transaksi
     * - ambil data transaksi lengkap
     * - bangun isi pesan share
     * - jika channel email, validasi email dan kirim via Mail
     * - jika channel Telegram, resolve chat id lalu kirim via HTTP API
     * - simpan chat id pelanggan jika pengiriman Telegram sukses
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareKasir(Request $request)
    {
        requirePermission('transaksi', 'view');

        $validated = $request->validate([
            'id_transaksi' => 'required|exists:transaksi,id_transaksi',
            'channel' => 'required|in:email,telegram',
            'recipient' => 'nullable|string|max:255',
        ], [
            'id_transaksi.required' => 'ID transaksi wajib dikirim.',
            'id_transaksi.exists' => 'Transaksi tidak ditemukan.',
            'channel.required' => 'Channel pengiriman wajib dipilih.',
            'channel.in' => 'Channel pengiriman tidak valid.',
        ]);

        $transaksi = Transaksi::with(['detail.layanan', 'detail.jenis', 'pelanggan'])
            ->where('id_transaksi', $validated['id_transaksi'])
            ->firstOrFail();

        $message = $this->buildShareMessage($transaksi);

        if ($validated['channel'] === 'email') {
            $recipient = trim((string) ($validated['recipient'] ?? ''));

            if ($recipient === '') {
                $recipient = (string) ($transaksi->pelanggan?->email ?? '');
            }

            if ($recipient === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email tujuan belum diisi.'
                ], 422);
            }

            if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format email tujuan tidak valid.'
                ], 422);
            }

            try {
                Mail::send('emails.transaksi-share', [
                    'transaksi' => $transaksi,
                ], function ($mail) use ($recipient, $transaksi) {
                    $mail->to($recipient)
                        ->subject('Ringkasan Pembayaran Transaksi #' . $transaksi->id_transaksi);
                });
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim share transaksi via email', [
                    'id_transaksi' => $transaksi->id_transaksi,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
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

        // Siapkan kredensial dan fallback penerima Telegram.
        $botToken = config('services.telegram.bot_token');
        $storedChatId = trim((string) ($transaksi->pelanggan?->telegram_chat_id ?? ''));
        $storedTelegramUsername = $transaksi->pelanggan?->telegram_username;
        $rawRecipient = trim((string) ($validated['recipient'] ?? ''));
        $fallbackRecipient = trim((string) config('services.telegram.default_chat_id'));
        $chatId = '';
        $telegramUsername = $storedTelegramUsername;

        if (empty($botToken)) {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum dikonfigurasi.'
            ], 422);
        }

        // Prioritas penerima Telegram:
        // 1. Input dari popup share
        // 2. Chat id yang sudah tersimpan di data pelanggan
        // 3. Default chat id dari konfigurasi
        if ($rawRecipient !== '') {
            $resolvedTelegram = $this->resolveTelegramRecipient($rawRecipient);
            $chatId = $resolvedTelegram['chat_id'];
            $telegramUsername = $resolvedTelegram['username'] ?? $telegramUsername;
        } elseif ($storedChatId !== '') {
            $chatId = $storedChatId;
        } elseif ($fallbackRecipient !== '') {
            $resolvedTelegram = $this->resolveTelegramRecipient($fallbackRecipient);
            $chatId = $resolvedTelegram['chat_id'];
            $telegramUsername = $resolvedTelegram['username'] ?? $telegramUsername;
            $rawRecipient = $fallbackRecipient;
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
            $response = Http::asForm()
                ->timeout(15)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim share transaksi ke Telegram', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Telegram gagal. Coba lagi sebentar.'
            ], 500);
        }

        if (!$response->successful()) {
            Log::warning('Telegram API menolak pengiriman share transaksi', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);

            $telegramDescription = (string) data_get($response->json(), 'description', '');
            $message = 'Gagal mengirim ke Telegram.';

            if (str_contains(strtolower($telegramDescription), 'chat not found')) {
                $message = 'Chat Telegram tidak ditemukan. Untuk chat pribadi, kirim pesan dulu ke bot lalu gunakan chat ID numerik dari getUpdates.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'telegram_response' => $response->json(),
            ], 500);
        }

        $this->storeTelegramChatIdForPelanggan($transaksi, $chatId, $telegramUsername);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pembayaran berhasil dikirim ke Telegram.'
        ]);
    }

    /**
     * METHOD private/helper: resolveTelegramRecipient()
     * Fungsi:
     * - helper method untuk mengubah input penerima Telegram menjadi chat id final
     *
     * Karena private, method ini tidak dipanggil route langsung.
     * Ia hanya dipakai oleh method lain di controller ini.
     *
     * @param string $recipient
     * @return array{chat_id:string, username:?string}
     */
    private function resolveTelegramRecipient(string $recipient): array
    {
        if ($recipient === '') {
            return [
                'chat_id' => '',
                'username' => null,
            ];
        }

        $payload = Cache::store('file')->get($this->telegramCodeCacheKey($recipient));

        if (is_array($payload) && !empty($payload['chat_id'])) {
            return [
                'chat_id' => (string) $payload['chat_id'],
                'username' => $payload['username'] ?? null,
            ];
        }

        if (preg_match('/^-?\d+$/', $recipient)) {
            return [
                'chat_id' => $recipient,
                'username' => null,
            ];
        }

        return [
            'chat_id' => '',
            'username' => null,
        ];
    }

    /**
     * METHOD private/helper: storeTelegramChatIdForPelanggan()
     * Fungsi:
     * - helper method untuk menyimpan chat id Telegram ke data pelanggan
     *   setelah proses share berhasil
     *
     * Karena private, method ini adalah fungsi bantu internal controller.
     *
     * @param Transaksi $transaksi
     * @param string $chatId
     * @param string|null $telegramUsername
     * @return void
     */
    private function storeTelegramChatIdForPelanggan(Transaksi $transaksi, string $chatId, ?string $telegramUsername = null): void
    {
        if ($chatId === '') {
            return;
        }

        $pelanggan = $transaksi->pelanggan;

        if (!$pelanggan && !empty($transaksi->id_pelanggan)) {
            $pelanggan = Pelanggan::find($transaksi->id_pelanggan);
        }

        if (!$pelanggan) {
            $normalizedNoHp = preg_replace('/[^0-9]/', '', (string) $transaksi->no_hp);

            if ($normalizedNoHp !== '') {
                $pelanggan = Pelanggan::get()->first(function ($item) use ($normalizedNoHp) {
                    return preg_replace('/[^0-9]/', '', (string) $item->no_hp) === $normalizedNoHp;
                });
            }
        }

        if (!$pelanggan) {
            Log::info('Share Telegram berhasil, tapi pelanggan tidak ditemukan untuk simpan chat id.', [
                'id_transaksi' => $transaksi->id_transaksi,
                'chat_id' => $chatId,
            ]);

            return;
        }

        $payload = ['telegram_chat_id' => $chatId];
        if (!empty($telegramUsername)) {
            $payload['telegram_username'] = $telegramUsername;
        }

        if (!$transaksi->id_pelanggan || (int) $transaksi->id_pelanggan !== (int) $pelanggan->id_pelanggan) {
            $transaksi->update(['id_pelanggan' => $pelanggan->id_pelanggan]);
            $transaksi->setRelation('pelanggan', $pelanggan);
        }

        if (empty($pelanggan->telegram_chat_id) || (string) $pelanggan->telegram_chat_id !== $chatId) {
            $pelanggan->update($payload);
        }
    }

    /**
     * Membuat key cache standar untuk kode link Telegram.
     *
     * @param string $code
     * @return string
     */
    private function telegramCodeCacheKey(string $code): string
    {
        return 'telegram_link_code:' . trim($code);
    }

    /**
     * Menyusun isi pesan ringkasan transaksi yang dibagikan ke pelanggan.
     *
     * @param Transaksi $transaksi
     * @return string
     */
    private function buildShareMessage(Transaksi $transaksi): string
    {
        $detailLines = $transaksi->detail->map(function ($item) {
            $qty = (float) $item->qty;
            $storedHarga = (float) ($item->harga ?? 0);
            $storedSubtotal = (float) ($item->subtotal ?? 0);
            $hargaJenis = (float) ($item->jenis?->harga ?? 0);
            $hargaSatuan = $storedHarga > 0
                ? $storedHarga
                : ($qty > 0 && $storedSubtotal > 0
                    ? $storedSubtotal / $qty
                    : $hargaJenis);
            $subtotal = $storedSubtotal > 0 ? $storedSubtotal : ($hargaSatuan * $qty);
            $layanan = $item->layanan?->nama_layanan ?? 'Layanan';
            $jenis = $item->jenis?->nama_jenis ? ' (' . $item->jenis->nama_jenis . ')' : '';

            return '- ' . $layanan . $jenis . ': ' .
                $item->qty . ' x Rp' . number_format($hargaSatuan, 0, ',', '.') .
                ' = Rp' . number_format($subtotal, 0, ',', '.');
        })->implode("\n");

        return "Halo {$transaksi->nama_pelanggan},\n\n" .
            "Pembayaran transaksi laundry Anda berhasil dicatat.\n" .
            "ID Transaksi: {$transaksi->id_transaksi}\n" .
            "Tanggal: " . \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d/m/Y H:i') . "\n" .
            "Status Bayar: " . strtoupper((string) $transaksi->status_bayar) . "\n" .
            "Total Harga: Rp" . number_format((float) $transaksi->total_harga, 0, ',', '.') . "\n" .
            "Diskon: Rp" . number_format((float) $transaksi->diskon, 0, ',', '.') . "\n" .
            "Total Bayar: Rp" . number_format((float) $transaksi->total_bayar, 0, ',', '.') . "\n" .
            (!empty($transaksi->tgl_estimasi) ? "Estimasi Selesai: " . \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y') . "\n" : '') .
            (!empty($detailLines) ? "\nDetail Layanan:\n{$detailLines}\n" : '') .
            "\nTerima kasih telah menggunakan layanan kami.";
    }

    // ==========================
    // UPDATE KETERANGAN METHODS
    // ==========================
    public function updateKeterangan(Request $request)
    {
        // No permission check - just updating session
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

    public function updateKeteranganKasir(Request $request)
    {
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

    public function updateKeteranganAdmin2(Request $request)
    {
        session(['keterangan_transaksi' => $request->keterangan]);
        return response()->json(['success' => true]);
    }

    // ==========================
    // OTHER METHODS
    // ==========================
    public function print($id)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $transaksi = Transaksi::with('pelanggan', 'detail')->findOrFail($id);
        return view('transaksi.print', compact('transaksi'));
    }

    public function riwayat()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $data = Transaksi::with('detail')->orderBy('id_transaksi', 'DESC')->get();
        return view('admin.transaksi.riwayat', compact('data'));
    }

    public function tempStoreLayanan(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $request->validate([
            'id_jenis_layanan' => 'required|exists:jenis_layanan,id_layanan',
            'qty' => 'required|numeric|min:0.01',
            'parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $layananSementara = session()->get('layanan_temp', []);

        $layananSementara[] = [
            'id_jenis_layanan' => $request->id_jenis_layanan,
            'qty' => $request->qty,
            'parfum' => $request->parfum,
        ];

        session(['layanan_temp' => $layananSementara]);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan sementara!',
            'data' => $layananSementara
        ]);
    }
}
