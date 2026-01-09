<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
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
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
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
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
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

        return redirect()->route('kasir.transaksi.create');
    }

    public function setPelangganAdmin2($id)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
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
        // ✅ CHECK PERMISSION ADD
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
                'tanggal'      => now(),
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
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $pelanggan = session('pelanggan');
        $detail    = session('detail_transaksi', []);

        if (!$pelanggan || empty($detail)) {
            return response()->json(['error' => 'Transaksi tidak valid'], 400);
        }

        $diskon       = floatval($request->input('diskon', 0));
        $tipeDiskon   = $request->input('tipe_diskon', 'nominal');
        $dp           = floatval($request->input('dp', 0));
        $langsungBayar= intval($request->input('langsung_bayar', 0));
        $keterangan   = $request->input('keterangan', '-');
        $idMetodeBayar= $request->input('id_metode_bayar', 1);
        $tglEstimasi  = $request->input('tgl_estimasi', now());

        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

        if ($tipeDiskon === 'percent') {
            $diskon = $totalAwal * ($diskon / 100);
        }

        $totalAkhir = max($totalAwal - $diskon, 0);

        $totalBayar = $dp;
        if ($langsungBayar === 1 || $dp >= $totalAkhir) {
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now();
            $dp = 0;
        } elseif ($dp > 0) {
            $statusBayar = 'DP';
            $tglLunas = null;
        } else {
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
        }

        $trans = Transaksi::create([
            'id_pelanggan'     => $pelanggan['id_pelanggan'],
            'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
            'no_hp'            => $pelanggan['no_hp'],
            'total_harga'      => $totalAwal,
            'total_bayar'      => $totalBayar,
            'dp'               => $dp,
            'diskon'           => $diskon,
            'tipe_diskon'      => $tipeDiskon,
            'status_bayar'     => $statusBayar,
            'status_transaksi' => 'antrian',
            'jenis_transaksi'  => 'offline',
            'keterangan'       => $keterangan,
            'tgl_transaksi'    => now(),
            'tgl_estimasi'     => $tglEstimasi,
            'tgl_lunas'        => $tglLunas,
            'id_kasir'         => current_user_id(),
            'nama_kasir'       => current_user_name(),
            'id_metode_bayar'  => $idMetodeBayar,
        ]);

        foreach ($detail as $d) {
            $jenis = \App\Models\JenisLayanan::with('satuan')
                ->where('id_jenis_layanan', $d['id_jenis_layanan'])
                ->first();

            $idSatuan = $jenis?->satuan?->id_satuan ?? null;

            $trans->detail()->create([
                'id_layanan'       => $d['id_layanan'],
                'id_jenis_layanan' => $d['id_jenis_layanan'],
                'id_parfum'        => $d['id_parfum'] ?? null,
                'harga'            => $d['harga'],
                'qty'              => $d['qty'],
                'id_satuan'        => $idSatuan,
                'tipe_diskon'      => $tipeDiskon,
            ]);
        }

        session()->forget(['pelanggan', 'detail_transaksi', 'keterangan_transaksi']);

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
    }

    // ==========================
    // KASIR - CREATE TRANSAKSI
    // ==========================
    public function createKasir()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        return view('kasir.transaksi.create', [
            'pelanggan'  => session('pelanggan'),
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
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
        return view('kasir.transaksi.pelanggan', compact('pelanggan'));
    }

    // ==========================
    // KASIR - BAYAR
    // ==========================
    public function bayarKasir(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $pelanggan = session('pelanggan');
        $detail    = session('detail_transaksi', []);

        if (!$pelanggan || empty($detail)) {
            return response()->json(['error' => 'Transaksi tidak valid'], 400);
        }

        $diskon       = floatval($request->input('diskon', 0));
        $tipeDiskon   = $request->input('tipe_diskon', 'nominal');
        $dp           = floatval($request->input('dp', 0));
        $langsungBayar= intval($request->input('langsung_bayar', 0));
        $keterangan   = $request->input('keterangan', '-');
        $idMetodeBayar= $request->input('id_metode_bayar', 1);
        $tglEstimasi  = $request->input('tgl_estimasi', now());

        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

        if ($tipeDiskon === 'percent') {
            $diskon = $totalAwal * ($diskon / 100);
        }

        $totalAkhir = max($totalAwal - $diskon, 0);

        $totalBayar = $dp;
        if ($langsungBayar === 1 || $dp >= $totalAkhir) {
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now();
            $dp = 0;
        } elseif ($dp > 0) {
            $statusBayar = 'DP';
            $tglLunas = null;
        } else {
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
        }

        $trans = Transaksi::create([
            'id_pelanggan'     => $pelanggan['id_pelanggan'],
            'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
            'no_hp'            => $pelanggan['no_hp'],
            'total_harga'      => $totalAwal,
            'total_bayar'      => $totalBayar,
            'dp'               => $dp,
            'diskon'           => $diskon,
            'tipe_diskon'      => $tipeDiskon,
            'status_bayar'     => $statusBayar,
            'status_transaksi' => 'antrian',
            'jenis_transaksi'  => 'offline',
            'keterangan'       => $keterangan,
            'tgl_transaksi'    => now(),
            'tgl_estimasi'     => $tglEstimasi,
            'tgl_lunas'        => $tglLunas,
            'id_kasir'         => current_user_id(),
            'nama_kasir'       => current_user_name(),
            'id_metode_bayar'  => $idMetodeBayar,
        ]);

        foreach ($detail as $d) {
            $jenis = \App\Models\JenisLayanan::with('satuan')
                ->where('id_jenis_layanan', $d['id_jenis_layanan'])
                ->first();

            $idSatuan = $jenis?->satuan?->id_satuan ?? null;

            $trans->detail()->create([
                'id_layanan'       => $d['id_layanan'],
                'id_jenis_layanan' => $d['id_jenis_layanan'],
                'id_parfum'        => $d['id_parfum'] ?? null,
                'harga'            => $d['harga'],
                'qty'              => $d['qty'],
                'id_satuan'        => $idSatuan,
                'tipe_diskon'      => $tipeDiskon,
            ]);
        }

        session()->forget(['pelanggan', 'detail_transaksi', 'keterangan_transaksi']);

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
    }

    // ==========================
    // ADMIN2 - CREATE TRANSAKSI
    // ==========================
    public function createAdmin2()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
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
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
        return view('admin2.transaksi.pelanggan', compact('pelanggan'));
    }

    // ==========================
    // ADMIN2 - BAYAR
    // ==========================
    public function bayarAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        $pelanggan = session('pelanggan_transaksi');
        $detail    = session('detail_transaksi', []);

        if (!$pelanggan || empty($detail)) {
            return response()->json(['error' => 'Transaksi tidak valid'], 400);
        }

        $diskon        = floatval($request->input('diskon', 0));
        $tipeDiskon    = $request->input('tipe_diskon', 'nominal');
        $dp            = floatval($request->input('dp', 0));
        $langsungBayar = intval($request->input('langsung_bayar', 0));
        $keterangan    = $request->input('keterangan', '-');
        $idMetodeBayar = $request->input('id_metode_bayar', 1);
        $tglEstimasi   = $request->input('tgl_estimasi', now());

        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

        if ($tipeDiskon === 'percent') {
            $diskon = $totalAwal * ($diskon / 100);
        }

        $totalAkhir = max($totalAwal - $diskon, 0);

        $totalBayar = $dp;
        $statusBayar = 'belum_lunas';
        $tglLunas = null;

        if ($langsungBayar === 1 || $dp >= $totalAkhir) {
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now();
            $dp = 0;
        } elseif ($dp > 0 && $dp < $totalAkhir) {
            $statusBayar = 'DP';
        }

        $trans = Transaksi::create([
            'id_pelanggan'     => $pelanggan['id_pelanggan'],
            'nama_pelanggan'   => $pelanggan['nama_pelanggan'],
            'no_hp'            => $pelanggan['no_hp'],
            'total_harga'      => $totalAwal,
            'total_bayar'      => $totalBayar,
            'dp'               => $dp,
            'diskon'           => $diskon,
            'tipe_diskon'      => $tipeDiskon,
            'status_bayar'     => $statusBayar,
            'status_transaksi' => 'antrian',
            'jenis_transaksi'  => 'offline',
            'keterangan'       => $keterangan,
            'tgl_transaksi'    => now(),
            'tgl_estimasi'     => $tglEstimasi,
            'tgl_lunas'        => $tglLunas,
            'id_kasir'         => current_user_id(),
            'nama_kasir'       => current_user_name(),
            'id_metode_bayar'  => $idMetodeBayar,
        ]);

        foreach ($detail as $d) {
            $jenis = \App\Models\JenisLayanan::with('satuan')
                ->where('id_jenis_layanan', $d['id_jenis_layanan'])
                ->first();

            $idSatuan = $jenis?->satuan?->id_satuan ?? null;

            $trans->detail()->create([
                'id_layanan'       => $d['id_layanan'],
                'id_jenis_layanan' => $d['id_jenis_layanan'],
                'id_parfum'        => $d['id_parfum'] ?? null,
                'harga'            => $d['harga'],
                'qty'              => $d['qty'],
                'id_satuan'        => $idSatuan,
                'tipe_diskon'      => $tipeDiskon,
            ]);
        }

        session()->forget([
            'pelanggan_transaksi',
            'detail_transaksi',
            'keterangan_transaksi'
        ]);

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
    }

    // ==========================
    // CHECKOUT METHODS
    // ==========================
    public function checkoutKasir(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('transaksi', 'add');
        
        try {
            $pelanggan = session('pelanggan');
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
                'id_pelanggan' => $pelanggan['id_pelanggan'],
                'nama_pelanggan' => $pelanggan['nama_pelanggan'],
                'no_hp' => $pelanggan['no_hp'],
                'total_harga' => $total,
                'status_transaksi' => 'antrian',
                'status_bayar' => 'belum_lunas',
                'jenis_transaksi' => 'offline',
                'keterangan' => session('keterangan_transaksi'),
                'tgl_transaksi' => now(),
                'id_kasir' => auth()->id(),
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
                'pelanggan',
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

    public function checkoutAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
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
                'tanggal'      => now(),
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
                ->route('admin2.transaksi.confirm')
                ->with('error', 'Gagal menyimpan transaksi');
        }
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
        
        $pelanggan  = session('pelanggan');
        $detail     = session('detail_transaksi', []);
        $keterangan = session('keterangan_transaksi', '');

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

    public function confirmAdmin2()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('transaksi', 'view');
        
        $pelanggan  = session('pelanggan_transaksi');
        $detail     = session('detail_transaksi', []);
        $keterangan = session('keterangan_transaksi', '');

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