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
    // 1. HALAMAN AWAL TRANSAKSI
    // ==========================
    public function index(Request $request)
    {
        if (!$request->has('keep')) {
            session()->forget('detail_transaksi');
        }

        return view('admin.transaksi.index', [
            'pelanggan' => session('pelanggan'),
            'detail'    => session('detail_transaksi', []),
        ]);
    }

    // ==========================
    // 2. HALAMAN CREATE
    // ==========================
    public function create()
    {
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
    // 3. PILIH PELANGGAN
    // ==========================
    public function pilihPelanggan()
    {
        $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
        return view('transaksi.pelanggan', compact('pelanggan'));
    }

    public function setPelanggan($id)
    {
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
    $p = Pelanggan::find($id);
    if (!$p) return back()->with('error', 'Pelanggan tidak ditemukan');

    session([
        'pelanggan_transaksi' => [
            'id_pelanggan'   => $p->id_pelanggan,
            'nama_pelanggan' => $p->nama_pelanggan,
            'no_hp'          => $p->no_hp,
            // ✅ FIX: Tambah prefix pelanggan/ kalau belum ada
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
// 4. TAMBAH LAYANAN
// ==========================
public function addLayanan(Request $request, $id)
{
    $layanan = Layanan::with('jenis.satuan')->find($id);
    if (!$layanan) {
        return response()->json(['error' => 'Layanan tidak ditemukan'], 404);
    }

    // Ambil jenis sesuai request, atau default ke first
    $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
    $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

    if (!$jenis) {
        return response()->json(['error' => 'Jenis layanan tidak ditemukan'], 404);
    }

    // Ambil cart lama
    $cart = session()->get('detail_transaksi', []);

    // Tambahkan layanan baru ke cart
    $cart[] = [
        
        'id_layanan'        => $layanan->id_layanan,
        'nama_layanan'      => $layanan->nama_layanan,
        'id_jenis_layanan'  => $jenis->id_jenis_layanan,
        'jenis'             => $jenis->nama_jenis, // <--- ini harus nama_jenis sesuai db
        'harga'             => $jenis->harga,
        'qty'               => $request->qty ?? 1,
        'satuan' => $jenis->satuan->nama_satuan ?? '',
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
    // 5. HAPUS LAYANAN
    // ==========================
    public function remove($id)
    {
        $cart = session('detail_transaksi', []);

        $cart = array_values(array_filter($cart, fn($i) => $i['id_layanan'] != $id));

        session()->put('detail_transaksi', $cart);

        return back();
    }

    // ==========================
    // 5. HAPUS LAYANAN
    // ==========================
    public function removeAdmin2($id)
    {
        $cart = session('detail_transaksi', []);

        $cart = array_values(array_filter($cart, fn($i) => $i['id_layanan'] != $id));

        session()->put('detail_transaksi', $cart);

        return back();
    }

    // ==========================
    // 5. HAPUS LAYANAN
    // ==========================
    public function removeKasir($id)
    {
        $cart = session('detail_transaksi', []);

        $cart = array_values(array_filter($cart, fn($i) => $i['id_layanan'] != $id));

        session()->put('detail_transaksi', $cart);

        return back();
    }
    // ==========================
    // 6. HALAMAN CHECKOUT
    // ==========================
    public function checkout(Request $request)
{
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

        // 🔥 CREATE TRANSAKSI
        $transaksi = \App\Models\Transaksi::create([
            'id_pelanggan' => $pelanggan['id_pelanggan'],
            'tanggal'      => now(),
            'total_harga'  => $total,
            'status'       => 'proses',
            'keterangan'   => session('keterangan_transaksi'),
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

        // 🔥 CLEAR SESSION SETELAH SUKSES
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
            ->route('confirm')
            ->with('error', 'Gagal menyimpan transaksi');
    }
}

    // ==========================
    // 7. SIMPAN TRANSAKSI
    // ==========================
    public function bayar(Request $request)
    {
        $pelanggan = session('pelanggan');
        $detail    = session('detail_transaksi', []);

        if (!$pelanggan || empty($detail)) {
            return response()->json(['error' => 'Transaksi tidak valid'], 400);
        }

        // ================================
        // Ambil input dari JSON fetch
        // ================================
        $diskon       = floatval($request->input('diskon', 0));
        $tipeDiskon   = $request->input('tipe_diskon', 'nominal');
        $dp           = floatval($request->input('dp', 0));
        $langsungBayar= intval($request->input('langsung_bayar', 0));
        $keterangan   = $request->input('keterangan', '-');
        $idMetodeBayar= $request->input('id_metode_bayar', 1);
        $tglEstimasi  = $request->input('tgl_estimasi', now());

        // ================================
        // Hitung total awal & diskon
        // ================================
        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

        if ($tipeDiskon === 'percent') {
            $diskon = $totalAwal * ($diskon / 100);
        }

        $totalAkhir = max($totalAwal - $diskon, 0);

        // ================================
        // Hitung status bayar
        // ================================
        $totalBayar = $dp;
        if ($langsungBayar === 1 || $dp >= $totalAkhir) {
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now();
            $dp = 0; // otomatis 0 kalau lunas
        } elseif ($dp > 0) {
            $statusBayar = 'DP';
            $tglLunas = null;
        } else {
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
        }

        // ================================
        // Simpan transaksi
        // ================================
        // Di method bayar() dan bayarKasir(), tambahkan ini:
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
            'jenis_transaksi'  => 'offline',  // 🔥 TAMBAHKAN INI
            'keterangan'       => $keterangan,
            'tgl_transaksi'    => now(),
            'tgl_estimasi'     => $tglEstimasi,
            'tgl_lunas'        => $tglLunas,
            'id_kasir'         => auth()->id(),
            'id_metode_bayar'  => $idMetodeBayar,
        ]);

        // ================================
        // Simpan detail transaksi
        // ================================
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
    // 7. SIMPAN TRANSAKSI
    // ==========================
    public function bayarKasir(Request $request)
    {
        $pelanggan = session('pelanggan');
        $detail    = session('detail_transaksi', []);

        if (!$pelanggan || empty($detail)) {
            return response()->json(['error' => 'Transaksi tidak valid'], 400);
        }

        // ================================
        // Ambil input dari JSON fetch
        // ================================
        $diskon       = floatval($request->input('diskon', 0));
        $tipeDiskon   = $request->input('tipe_diskon', 'nominal');
        $dp           = floatval($request->input('dp', 0));
        $langsungBayar= intval($request->input('langsung_bayar', 0));
        $keterangan   = $request->input('keterangan', '-');
        $idMetodeBayar= $request->input('id_metode_bayar', 1);
        $tglEstimasi  = $request->input('tgl_estimasi', now());

        // ================================
        // Hitung total awal & diskon
        // ================================
        $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

        if ($tipeDiskon === 'percent') {
            $diskon = $totalAwal * ($diskon / 100);
        }

        $totalAkhir = max($totalAwal - $diskon, 0);

        // ================================
        // Hitung status bayar
        // ================================
        $totalBayar = $dp;
        if ($langsungBayar === 1 || $dp >= $totalAkhir) {
            $totalBayar = $totalAkhir;
            $statusBayar = 'lunas';
            $tglLunas = now();
            $dp = 0; // otomatis 0 kalau lunas
        } elseif ($dp > 0) {
            $statusBayar = 'DP';
            $tglLunas = null;
        } else {
            $statusBayar = 'belum_lunas';
            $tglLunas = null;
        }

        // ================================
        // Simpan transaksi
        // ================================
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
            'keterangan'       => $keterangan,
            'tgl_transaksi'    => now(),
            'tgl_estimasi'     => $tglEstimasi,
            'tgl_lunas'        => $tglLunas,
            'id_kasir'         => auth()->id(),
            'id_metode_bayar'  => $idMetodeBayar,
        ]);

        // ================================
        // Simpan detail transaksi
        // ================================
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
    // 7. SIMPAN TRANSAKSI
    // ==========================
    public function bayarAdmin2(Request $request)
{
    // ✅ GANTI session key
    $pelanggan = session('pelanggan_transaksi');  // ✅ BENAR
    $detail    = session('detail_transaksi', []);

    if (!$pelanggan || empty($detail)) {
        return response()->json(['error' => 'Transaksi tidak valid'], 400);
    }

    // Ambil input
    $diskon        = floatval($request->input('diskon', 0));
    $tipeDiskon    = $request->input('tipe_diskon', 'nominal');
    $dp            = floatval($request->input('dp', 0));
    $langsungBayar = intval($request->input('langsung_bayar', 0));
    $keterangan    = $request->input('keterangan', '-');
    $idMetodeBayar = $request->input('id_metode_bayar', 1);
    $tglEstimasi   = $request->input('tgl_estimasi', now());

    // Hitung total & diskon
    $totalAwal = array_sum(array_map(fn($d) => $d['harga'] * $d['qty'], $detail));

    if ($tipeDiskon === 'percent') {
        $diskon = $totalAwal * ($diskon / 100);
    }

    $totalAkhir = max($totalAwal - $diskon, 0);

    // ✅ LOGIC STATUS BAYAR YANG BENAR
    $totalBayar = $dp;
    $statusBayar = 'belum_lunas';
    $tglLunas = null;

    if ($langsungBayar === 1 || $dp >= $totalAkhir) {
        // Kalau langsung bayar atau DP >= total → LUNAS
        $totalBayar = $totalAkhir;
        $statusBayar = 'lunas';
        $tglLunas = now();
        $dp = 0; // Set DP = 0 kalau lunas
    } elseif ($dp > 0 && $dp < $totalAkhir) {
        // Kalau DP ada tapi kurang dari total → DP
        $statusBayar = 'DP';
    }

    // ✅ CREATE TRANSAKSI DENGAN FIELD YANG BENAR
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
        'jenis_transaksi'  => 'offline',  // ✅ TAMBAHKAN
        'keterangan'       => $keterangan,
        'tgl_transaksi'    => now(),  // ✅ BENAR (bukan 'tanggal')
        'tgl_estimasi'     => $tglEstimasi,
        'tgl_lunas'        => $tglLunas,
        'id_kasir'         => auth()->id(),
        'id_metode_bayar'  => $idMetodeBayar,
    ]);

    // Simpan detail
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

    // ✅ CLEAR SESSION DENGAN KEY YANG BENAR
    session()->forget([
        'pelanggan_transaksi',  // ✅ BENAR
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
        // 8. PRINT
        // ==========================
        public function print($id)
        {
            $transaksi = Transaksi::with('pelanggan', 'detail')->findOrFail($id);
            return view('transaksi.print', compact('transaksi'));
        }

        // ==========================
        // 9. RIWAYAT
        // ==========================
        public function riwayat()
        {
            $data = Transaksi::with('detail')->orderBy('id_transaksi', 'DESC')->get();
            return view('admin.transaksi.riwayat', compact('data'));
        }

        public function updateKeterangan(Request $request)
        {
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

        public function tempStoreLayanan(Request $request)
    {
        $request->validate([
            'id_jenis_layanan' => 'required|exists:jenis_layanan,id_layanan',
            'qty' => 'required|numeric|min:0.01',
            'parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        // Ambil array layanan sementara dari session
        $layananSementara = session()->get('layanan_temp', []);

        // Tambahkan layanan baru
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

// ==========================
// KASIR - PILIH PELANGGAN
// ==========================
public function pelangganKasir()
{
    $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
    return view('kasir.transaksi.pelanggan', compact('pelanggan'));
}

// ==========================
// ADMIN2 - PILIH PELANGGAN
// ==========================
public function pelangganAdmin2()
{
    $pelanggan = Pelanggan::orderBy('nama_pelanggan')->get();
    return view('admin2.transaksi.pelanggan', compact('pelanggan'));
}

// ==========================
// KASIR - CREATE TRANSAKSI
// ==========================
public function createKasir()
{
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
// ADMIN2 - CREATE TRANSAKSI
// ==========================
// ✅ BENAR
public function createAdmin2()
{
    return view('admin2.transaksi.create', [
        'pelanggan'  => session('pelanggan_transaksi'),  // ✅ BENAR
        'detail'     => session('detail_transaksi', []),
        'keterangan' => session('keterangan_transaksi'),
        'parfum'     => \App\Models\Parfum::all(),  // ✅ Tambahkan ini juga
        'total'      => array_sum(
            array_map(fn($d) => $d['harga'] * $d['qty'], session('detail_transaksi', []))
        ),
    ]);
}

// ==========================
// KASIR - CHECKOUT
// ==========================
public function checkoutKasir(Request $request)
{
    try {
        $pelanggan = session('pelanggan_transaksi');
        $detail    = session('detail_transaksi', []);

        if (!$pelanggan || count($detail) === 0) {
            return redirect()
                ->route('kasir.transaksi.create')
                ->with('error', 'Data transaksi tidak lengkap');
        }

        $total = array_sum(
            array_map(fn($d) => $d['harga'] * $d['qty'], $detail)
        );

        // 🔥 CREATE TRANSAKSI
        $transaksi = \App\Models\Transaksi::create([
            'id_pelanggan' => $pelanggan['id_pelanggan'],
            'tanggal'      => now(),
            'total_harga'  => $total,
            'status'       => 'proses',
            'keterangan'   => session('keterangan_transaksi'),
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

        // 🔥 CLEAR SESSION SETELAH SUKSES
        session()->forget([
            'pelanggan_transaksi',
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

        // 🔥 CREATE TRANSAKSI
        $transaksi = \App\Models\Transaksi::create([
            'id_pelanggan' => $pelanggan['id_pelanggan'],
            'tanggal'      => now(),
            'total_harga'  => $total,
            'status'       => 'proses',
            'keterangan'   => session('keterangan_transaksi'),
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

        // 🔥 CLEAR SESSION SETELAH SUKSES
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
// KASIR - TAMBAH LAYANAN
// ==========================
public function addLayananKasir(Request $request, $id)
{
    $layanan = Layanan::with('jenis.satuan')->find($id);
    if (!$layanan) {
        return response()->json(['success'=>false,'message' => 'Layanan tidak ditemukan'], 404);
    }

    // Ambil jenis sesuai request, atau default ke first
    $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
    $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

    if (!$jenis) {
        return response()->json(['success'=>false,'message' => 'Jenis layanan tidak ditemukan'], 404);
    }

    // Ambil cart lama
    $cart = session()->get('detail_transaksi', []);

    // Tambahkan layanan baru ke cart
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

// ==========================
// KASIR - TAMBAH LAYANAN
// ==========================
public function addLayananAdmin2(Request $request, $id)
{
    $layanan = Layanan::with('jenis.satuan')->find($id);
    if (!$layanan) {
        return response()->json(['success'=>false,'message' => 'Layanan tidak ditemukan'], 404);
    }

    // Ambil jenis sesuai request, atau default ke first
    $jenisId = $request->id_jenis_layanan ?? $layanan->jenis->first()?->id_jenis_layanan;
    $jenis = $layanan->jenis->where('id_jenis_layanan', $jenisId)->first();

    if (!$jenis) {
        return response()->json(['success'=>false,'message' => 'Jenis layanan tidak ditemukan'], 404);
    }

    // Ambil cart lama
    $cart = session()->get('detail_transaksi', []);

    // Tambahkan layanan baru ke cart
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
// TAMBAH BERDASARKAN JENIS
// ==========================
public function addJenis(Request $request, $idJenis)
{
    \Log::info("=== ADD JENIS DEBUG ===");
    \Log::info("ID diterima: " . $idJenis);
    \Log::info("Request data: " . json_encode($request->all()));
    
    try {
        $request->validate([
            'qty'    => 'required|numeric|min:0.01',
            'parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        // 🔥 Coba find() dulu (harusnya works karena primary key udah bener)
        $jenis = JenisLayanan::find($idJenis);
        
        \Log::info("Jenis found: " . ($jenis ? 'YES' : 'NO'));
        
        if (!$jenis) {
            \Log::error("Jenis layanan tidak ditemukan dengan ID: {$idJenis}");
            
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan',
                'debug' => [
                    'id_received' => $idJenis,
                    'type' => gettype($idJenis)
                ]
            ], 404);
        }

        // Load relasi
        $jenis->load(['layanan', 'satuan']);
        
        \Log::info("Jenis data: " . json_encode([
            'id' => $jenis->id_jenis_layanan,
            'nama' => $jenis->nama_jenis,
            'harga' => $jenis->harga
        ]));

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
        ];

        $cart[] = $newItem;
        session()->put('detail_transaksi', $cart);

        \Log::info('Item ditambahkan ke cart', $newItem);
        \Log::info('Total items di cart: ' . count($cart));

        return response()->json([
            'success' => true,
            'message' => 'Jenis layanan berhasil ditambahkan',
            'data' => $newItem,
            'cart_count' => count($cart)
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error: ' . json_encode($e->errors()));
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        \Log::error('Error addJenis: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

// ==========================
// TAMBAH BERDASARKAN JENIS
// ==========================
public function addJenisKasir(Request $request, $idJenis)
{
    \Log::info("=== ADD JENIS DEBUG ===");
    \Log::info("ID diterima: " . $idJenis);
    \Log::info("Request data: " . json_encode($request->all()));
    
    try {
        $request->validate([
            'qty'    => 'required|numeric|min:0.01',
            'parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        // 🔥 Coba find() dulu (harusnya works karena primary key udah bener)
        $jenis = JenisLayanan::find($idJenis);
        
        \Log::info("Jenis found: " . ($jenis ? 'YES' : 'NO'));
        
        if (!$jenis) {
            \Log::error("Jenis layanan tidak ditemukan dengan ID: {$idJenis}");
            
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan',
                'debug' => [
                    'id_received' => $idJenis,
                    'type' => gettype($idJenis)
                ]
            ], 404);
        }

        // Load relasi
        $jenis->load(['layanan', 'satuan']);
        
        \Log::info("Jenis data: " . json_encode([
            'id' => $jenis->id_jenis_layanan,
            'nama' => $jenis->nama_jenis,
            'harga' => $jenis->harga
        ]));

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
        ];

        $cart[] = $newItem;
        session()->put('detail_transaksi', $cart);

        \Log::info('Item ditambahkan ke cart', $newItem);
        \Log::info('Total items di cart: ' . count($cart));

        return response()->json([
            'success' => true,
            'message' => 'Jenis layanan berhasil ditambahkan',
            'data' => $newItem,
            'cart_count' => count($cart)
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error: ' . json_encode($e->errors()));
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        \Log::error('Error addJenis: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

public function addJenisAdmin2(Request $request, $idJenis)
{
    \Log::info("=== ADD JENIS DEBUG ===");
    \Log::info("ID diterima: " . $idJenis);
    \Log::info("Request data: " . json_encode($request->all()));
    
    try {
        $request->validate([
            'qty'    => 'required|numeric|min:0.01',
            'parfum' => 'nullable|exists:parfum,id_parfum',
        ]);

        $jenis = JenisLayanan::find($idJenis);
        
        \Log::info("Jenis found: " . ($jenis ? 'YES' : 'NO'));
        
        if (!$jenis) {
            \Log::error("Jenis layanan tidak ditemukan dengan ID: {$idJenis}");
            
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan',
                'debug' => [
                    'id_received' => $idJenis,
                    'type' => gettype($idJenis)
                ]
            ], 404);
        }

        $jenis->load(['layanan', 'satuan']);
        
        \Log::info("Jenis data: " . json_encode([
            'id' => $jenis->id_jenis_layanan,
            'nama' => $jenis->nama_jenis,
            'harga' => $jenis->harga,
            'gambar' => $jenis->gambar, // ✅ Log gambar juga
        ]));

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
            'gambar'           => $jenis->gambar, // ✅✅✅ KUNCI UTAMA
        ];

        $cart[] = $newItem;
        session()->put('detail_transaksi', $cart);

        \Log::info('Item ditambahkan ke cart', $newItem);
        \Log::info('Total items di cart: ' . count($cart));

        return response()->json([
            'success' => true,
            'message' => 'Jenis layanan berhasil ditambahkan',
            'data' => $newItem,
            'cart_count' => count($cart)
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation error: ' . json_encode($e->errors()));
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        \Log::error('Error addJenis: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
/**
 * Halaman konfirmasi checkout
 */
public function confirmAdmin2()
{
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

    // ✅ TAMBAHKAN INI
    $metode_bayar = \App\Models\MetodeBayar::all();

    return view('admin2.transaksi.checkout', compact(
        'pelanggan',
        'detail',
        'keterangan',
        'totalHarga',
        'metode_bayar'  // ✅ TAMBAHKAN INI
    ));
}
public function confirm()
{
    $pelanggan  = session('pelanggan_transaksi');
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

    // ✅ TAMBAHKAN INI
    $metode_bayar = \App\Models\MetodeBayar::all();

    return view('transaksi.checkout', compact(
        'pelanggan',
        'detail',
        'keterangan',
        'totalHarga',
        'metode_bayar'  // ✅ TAMBAHKAN INI
    ));
}
public function confirmKasir()
{
    $pelanggan  = session('pelanggan_transaksi');
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

    // ✅ TAMBAHKAN INI
    $metode_bayar = \App\Models\MetodeBayar::all();

    return view('kasir.transaksi.checkout', compact(
        'pelanggan',
        'detail',
        'keterangan',
        'totalHarga',
        'metode_bayar'  // ✅ TAMBAHKAN INI
    ));
}
}