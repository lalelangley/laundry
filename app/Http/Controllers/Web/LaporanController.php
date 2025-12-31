<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Transaksi;
use App\fascades\DB;

class LaporanController extends Controller
{
    // =========================
    // INDEX
    // =========================
    public function index()
    {
        $pengeluaran = Pengeluaran::orderBy('id_pengeluaran', 'DESC')->get();
        return view('pengeluaran.index', compact('pengeluaran'));
    }

    // =========================
    // CREATE
    // =========================
    public function create()
    {
        return view('pengeluaran.create');
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        Pengeluaran::create([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal, // <- ini yang sebelumnya hilang
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }


    // =========================
    // EDIT
    // =========================
    public function edit($id)
    {
        $item = Pengeluaran::findOrFail($id);
        return view('pengeluaran.edit', compact('item'));
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $item = Pengeluaran::findOrFail($id);

        $item->update([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    // =========================
    // DESTROY
    // =========================
    public function destroy($id)
    {
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

     public function laporanIndex()
    {
        return view('laporan.index');
    }

/// ===============================
// LAPORAN TRANSAKSI
// ===============================
public function transaksiIndex(Request $request)
{
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $query = Transaksi::with([
            'pelanggan',
            'detail.jenis.satuan',
            'metodeBayar',
            'kasir'
        ])
        ->where('status_transaksi', 'selesai')
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

    // 🔍 SEARCH
    if ($request->filled('q')) {
        $q = $request->q;

        $query->where(function ($sub) use ($q) {
            $sub->where('nama_pelanggan', 'like', "%$q%")
                ->orWhere('no_hp', 'like', "%$q%")
                ->orWhere('id_transaksi', 'like', "%$q%");
        });
    }

    $transaksi = $query
        ->orderBy('tgl_transaksi', 'DESC')
        ->get();

    return view('laporan.transaksi.index', [
        'transaksi'  => $transaksi,
        'tglAwal'    => $tglAwal,
        'tglAkhir'   => $tglAkhir,
        'totalOmzet' => $transaksi->sum('total_bayar'),
        'jumlah'     => $transaksi->count(),
    ]);
}

    // ===============================
    // LAPORAN KASIR
    // ===============================
    public function kasirIndex(Request $request)
    {
        $data = Transaksi::selectRaw('
                id_kasir,
                COUNT(*) as total_transaksi,
                SUM(total_bayar) as total_pendapatan
            ')
            ->groupBy('id_kasir')
            ->with('kasir')
            ->orderByDesc('total_pendapatan')
            ->get();

        return view('laporan.kasir.index', compact('data'));
    }

// ===============================
// LAPORAN METODE BAYAR
// ===============================
public function bayarIndex(Request $request)
{
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $data = \DB::table('metode_bayar')
        ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
            $join->on('metode_bayar.id_metode_bayar', '=', 'transaksi.id_metode_bayar')
                 ->where('transaksi.status_transaksi', 'selesai')
                 ->whereBetween('transaksi.tgl_transaksi', [
                     $tglAwal.' 00:00:00',
                     $tglAkhir.' 23:59:59'
                 ]);
        })
        ->select(
            'metode_bayar.id_metode_bayar',
            'metode_bayar.nama_metode_bayar',
            \DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
        )
        ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar')
        ->orderBy('metode_bayar.id_metode_bayar')
        ->get();

    return view('laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir'));
}

    // ===============================
    // LAPORAN PENGELUARAN
    // ===============================
    public function pengeluaranIndex(Request $request)
    {
        $query = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC');

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_pengeluaran', [
                $request->dari,
                $request->sampai
            ]);
        }

        $pengeluaran = $query->get();

        return view('laporan.pengeluaran.index', compact('pengeluaran'));
    }

    // ===============================
    // LAPORAN PELANGGAN
    // ===============================
    public function pelangganIndex()
    {
        $data = Transaksi::selectRaw('
                id_pelanggan,
                nama_pelanggan,
                no_hp,
                COUNT(*) as total_transaksi,
                SUM(total_bayar) as total_belanja
            ')
            ->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp')
            ->orderByDesc('total_belanja')
            ->get();

        return view('laporan.pelanggan.index', compact('data'));
    }


// ===============================
// LAPORAN SATUAN
// ===============================
public function satuanIndex(Request $request)
{
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $data = \DB::table('satuan')
        ->leftJoin('detail_transaksi', 'satuan.id_satuan', '=', 'detail_transaksi.id_satuan')
        ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
            $join->on('detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                 ->where('transaksi.status_transaksi', 'selesai')
                 ->whereBetween('transaksi.tgl_transaksi', [
                     $tglAwal.' 00:00:00',
                     $tglAkhir.' 23:59:59'
                 ]);
        })
        ->select(
            'satuan.id_satuan',
            'satuan.nama_satuan',
            \DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
        )
        ->groupBy('satuan.id_satuan', 'satuan.nama_satuan')
        ->orderBy('satuan.nama_satuan')
        ->get();

    return view('laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir'));
}

}
