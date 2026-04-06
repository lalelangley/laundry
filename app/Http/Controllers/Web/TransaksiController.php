<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
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
    // ==========================
    // 1. HALAMAN AWAL TRANSAKSI - ADMIN
    // ==========================
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
    public function create()
    {
        // Halaman awal transaksi cukup butuh akses view.
        // Aksi perubahan data tetap dijaga oleh endpoint yang memakai permission:add.
        requirePermission('transaksi', 'view');
        
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
    public function pilihPelanggan()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')
            ->paginate(10)
            ->withQueryString();
        return view('transaksi.pelanggan', compact('pelanggan'));
    }

    public function setPelanggan($id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
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

public function setPelangganKasir($id)
{
    requirePermission('transaksi', 'view');
    
    $p = Pelanggan::find($id);
    if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

    session([
        'pelanggan_kasir' => [  // ✅ FIX: Gunakan key khusus kasir
            'id_pelanggan'   => $p->id_pelanggan,
            'nama_pelanggan' => $p->nama_pelanggan,
            'no_hp'          => $p->no_hp,
            'email'          => $p->email,
            'foto'           => $p->gambar 
                ? (str_starts_with($p->gambar, 'pelanggan/') 
                    ? $p->gambar 
                    : 'pelanggan/' . $p->gambar)
                : null,
        ]
    ]);

    return redirect()->route('kasir.transaksi.create');
}
    public function setPelangganAdmin2($id)
{
    requirePermission('transaksi', 'view');
        
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
     * ✅ REMOVE LAYANAN BY INDEX (ADMIN)
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
     * ✅ REMOVE LAYANAN BY INDEX (ADMIN2)
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
     * ✅ REMOVE LAYANAN BY INDEX (KASIR)
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
public function bayar(Request $request)
{
    try {
        \Log::info('🟢 BAYAR METHOD CALLED');
        \Log::info('Request data:', $request->all());
        
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

        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

        $tipeDiskon = $request->input('tipe_diskon', 'nominal');
        if ($tipeDiskon === 'percent') {
            $diskon = $totalAwal * ($diskon / 100);
        }

        $totalAkhir = max($totalAwal - $diskon, 0);

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
    public function createKasir()
{
    // Halaman awal transaksi cukup butuh akses view.
    // Aksi perubahan data tetap dijaga oleh endpoint yang memakai permission:add.
    requirePermission('transaksi', 'view');
    
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
    public function pelangganKasir()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')
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
public function bayarKasir(Request $request)
{
    try {
        \Log::info('🟢 BAYAR KASIR METHOD CALLED');
        \Log::info('Request data:', $request->all());
        
        // ✅ FIX: Gunakan 'pelanggan_kasir' sesuai dengan setPelangganKasir()
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
            'nama_kasir'       => auth()->user()->name ?? 'Kasir',
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

        // ✅ FIX: Forget 'pelanggan_kasir' bukan 'pelanggan'
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
    public function pelangganAdmin2()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')
            ->paginate(10)
            ->withQueryString();
        return view('admin2.transaksi.pelanggan', compact('pelanggan'));
    }

    // ==========================
// KASIR - CHECKOUT (DIPERBAIKI)
// ==========================
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

    public function addJenisKasir(Request $request, $idJenis)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        // Same logic as addJenis...
        return $this->addJenis($request, $idJenis);
    }

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

public function confirmKasir()
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('transaksi', 'view');
    
    // ✅ FIX: Gunakan 'pelanggan_kasir'
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

        $botToken = config('services.telegram.bot_token');
        $chatId = trim((string) ($validated['recipient'] ?? config('services.telegram.default_chat_id')));

        if (empty($botToken)) {
            return response()->json([
                'success' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum dikonfigurasi.'
            ], 422);
        }

        if ($chatId === '') {
            return response()->json([
                'success' => false,
                'message' => 'Chat ID Telegram belum diisi.'
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

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan pembayaran berhasil dikirim ke Telegram.'
        ]);
    }

    private function buildShareMessage(Transaksi $transaksi): string
    {
        $detailLines = $transaksi->detail->map(function ($item) {
            $subtotal = (float) $item->harga * (float) $item->qty;
            $layanan = $item->layanan?->nama_layanan ?? 'Layanan';
            $jenis = $item->jenis?->nama_jenis ? ' (' . $item->jenis->nama_jenis . ')' : '';

            return '- ' . $layanan . $jenis . ': ' .
                $item->qty . ' x Rp' . number_format((float) $item->harga, 0, ',', '.') .
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
            (!empty($transaksi->tgl_estimasi) ? "Estimasi Selesai: " . \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y H:i') . "\n" : '') .
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
