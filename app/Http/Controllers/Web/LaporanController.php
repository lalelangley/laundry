<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;  // ✅ TAMBAH INI
use App\Exports\TransaksiExport; 
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    // =========================
    // INDEX PENGELUARAN - ADMIN
    // =========================
    public function index()
    {
        $pengeluaran = Pengeluaran::orderBy('id_pengeluaran', 'DESC')->get();
        return view('pengeluaran.index', compact('pengeluaran'));
    }

    // =========================
    // INDEX PENGELUARAN - KASIR
    // =========================
    public function indexKasir()
    {
        $kasir = auth('kasir')->user();
        
        if (!$kasir) {
            abort(403, 'Silakan login terlebih dahulu');
        }

        $pengeluaran = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kasir.pengeluaran.index', compact('pengeluaran'));
    }

    // =========================
    // INDEX PENGELUARAN - ADMIN2
    // =========================
    public function indexAdmin2()
    {
        $admin2 = auth('admin')->user();
        
        if (!$admin2 || $admin2->role_id != 2) {
            abort(403, 'Akses ditolak');
        }

        $pengeluaran = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin2.pengeluaran.index', compact('pengeluaran'));
    }

    // =========================
    // CREATE - ADMIN
    // =========================
    public function create()
    {
        return view('pengeluaran.create');
    }

    // =========================
    // CREATE - KASIR
    // =========================
    public function createKasir()
    {
        return view('kasir.pengeluaran.create');
    }

    // =========================
    // CREATE - ADMIN2
    // =========================
    public function createAdmin2()
    {
        return view('admin2.pengeluaran.create');
    }

    // =========================
    // STORE - ADMIN
    // =========================
    public function store(Request $request)
    {
        Pengeluaran::create([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    // =========================
    // STORE - KASIR
    // =========================
    public function storeKasir(Request $request)
    {
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        Pengeluaran::create([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    // =========================
    // STORE - ADMIN2
    // =========================
    public function storeAdmin2(Request $request)
    {
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        Pengeluaran::create([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    // =========================
    // EDIT - ADMIN
    // =========================
    public function edit($id)
    {
        $item = Pengeluaran::findOrFail($id);
        return view('pengeluaran.edit', compact('item'));
    }

    // =========================
    // EDIT - KASIR
    // =========================
    public function editKasir($id)
    {
        $item = Pengeluaran::findOrFail($id);
        return view('kasir.pengeluaran.edit', compact('item'));
    }

    // =========================
    // EDIT - ADMIN2
    // =========================
    public function editAdmin2($id)
    {
        $item = Pengeluaran::findOrFail($id);
        return view('admin2.pengeluaran.edit', compact('item'));
    }

    // =========================
    // UPDATE - ADMIN
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
    // UPDATE - KASIR
    // =========================
    public function updateKasir(Request $request, $id)
    {
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        $item = Pengeluaran::findOrFail($id);
        $item->update([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    // =========================
    // UPDATE - ADMIN2
    // =========================
    public function updateAdmin2(Request $request, $id)
    {
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'tanggal' => 'required|date',
        ]);

        $item = Pengeluaran::findOrFail($id);
        $item->update([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    // =========================
    // DESTROY - ADMIN
    // =========================
    public function destroy($id)
    {
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================
    // DESTROY - KASIR
    // =========================
    public function destroyKasir($id)
    {
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================
    // DESTROY - ADMIN2
    // =========================
    public function destroyAdmin2($id)
    {
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // ===============================
    // LAPORAN INDEX - ADMIN
    // ===============================
    public function laporanIndex()
    {
        return view('laporan.index');
    }

    // ===============================
    // LAPORAN INDEX - KASIR
    // ===============================
    public function laporanIndexKasir()
    {
        return view('kasir.laporan.index');
    }

    // ===============================
    // LAPORAN INDEX - ADMIN2
    // ===============================
    public function laporanIndexAdmin2()
    {
        return view('admin2.laporan.index');
    }

    // ===============================
    // LAPORAN TRANSAKSI - ADMIN
    // ===============================
   public function transaksiIndex(Request $request)
{
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    // ✅ FIXED: Hapus 'detail.jenis.satuan'
    $query = Transaksi::with([
        'pelanggan',
        'metodeBayar',
    ])
        ->where('status_transaksi', 'selesai')
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

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
    // LAPORAN TRANSAKSI - KASIR
    // ===============================
    public function transaksiIndexKasir(Request $request)
{
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    // ✅ FIXED: Hapus 'detail.jenis.satuan'
    $query = Transaksi::with([
        'pelanggan',
        'metodeBayar',
    ])
        ->where('status_transaksi', 'selesai')
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('nama_pelanggan', 'like', "%$q%")
                ->orWhere('no_hp', 'like', "%$q%")
                ->orWhere('id_transaksi', 'like', "%$q%");
        });
    }

    $transaksi = $query->orderBy('tgl_transaksi', 'DESC')->get();

    return view('kasir.laporan.transaksi.index', [
        'transaksi'  => $transaksi,
        'tglAwal'    => $tglAwal,
        'tglAkhir'   => $tglAkhir,
        'totalOmzet' => $transaksi->sum('total_bayar'),
        'jumlah'     => $transaksi->count(),
    ]);
}

    // ===============================
    // LAPORAN TRANSAKSI - ADMIN2
    // ===============================
   public function transaksiIndexAdmin2(Request $request)
{
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    // ✅ FIXED: Hapus 'detail.jenis.satuan'
    $query = Transaksi::with([
        'pelanggan',
        'metodeBayar',
    ])
        ->where('status_transaksi', 'selesai')
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('nama_pelanggan', 'like', "%$q%")
                ->orWhere('no_hp', 'like', "%$q%")
                ->orWhere('id_transaksi', 'like', "%$q%");
        });
    }

    $transaksi = $query->orderBy('tgl_transaksi', 'DESC')->get();

    return view('admin2.laporan.transaksi.index', [
        'transaksi'  => $transaksi,
        'tglAwal'    => $tglAwal,
        'tglAkhir'   => $tglAkhir,
        'totalOmzet' => $transaksi->sum('total_bayar'),
        'jumlah'     => $transaksi->count(),
    ]);
}

    // ===============================
    // LAPORAN KASIR - ADMIN (FIXED QUERY)
    // ===============================
    public function kasirIndex(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $kasirList = DB::table('kasir')
            ->where('status', 'aktif')
            ->select('id_kasir', 'nama_kasir', 'no_hp', 'gambar')
            ->get();

        $data = $kasirList->map(function($kasir) use ($tglAwal, $tglAkhir) {
            $transaksi = DB::table('transaksi')
                ->where('id_kasir', $kasir->id_kasir)
                ->whereBetween('tgl_transaksi', [
                    $tglAwal . ' 00:00:00',
                    $tglAkhir . ' 23:59:59'
                ])
                ->get();

            $kasir->antrian = $transaksi->where('status_transaksi', 'antrian')->count();
            $kasir->proses = $transaksi->where('status_transaksi', 'proses')->count();
            $kasir->siap_ambil = $transaksi->where('status_transaksi', 'siap_di_ambil')->count();
            $kasir->selesai = $transaksi->where('status_transaksi', 'selesai')->count();
            $kasir->batal = $transaksi->whereIn('status_transaksi', ['batal', 'ditolak'])->count();

            $kasir->total_pendapatan = $transaksi
                ->where('status_transaksi', 'selesai')
                ->sum('total_bayar');

            $kasir->total_transaksi = $transaksi->count();

            return $kasir;
        });

        $data = $data->sortByDesc('total_pendapatan')->values();

        return view('laporan.kasir.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN KASIR - ADMIN2
    // ===============================
    public function kasirIndexAdmin2(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $kasirList = DB::table('kasir')
            ->where('status', 'aktif')
            ->select('id_kasir', 'nama_kasir', 'no_hp', 'gambar')
            ->get();

        $data = $kasirList->map(function($kasir) use ($tglAwal, $tglAkhir) {
            $transaksi = DB::table('transaksi')
                ->where('id_kasir', $kasir->id_kasir)
                ->whereBetween('tgl_transaksi', [
                    $tglAwal . ' 00:00:00',
                    $tglAkhir . ' 23:59:59'
                ])
                ->get();

            $kasir->antrian = $transaksi->where('status_transaksi', 'antrian')->count();
            $kasir->proses = $transaksi->where('status_transaksi', 'proses')->count();
            $kasir->siap_ambil = $transaksi->where('status_transaksi', 'siap_di_ambil')->count();
            $kasir->selesai = $transaksi->where('status_transaksi', 'selesai')->count();
            $kasir->batal = $transaksi->whereIn('status_transaksi', ['batal', 'ditolak'])->count();

            $kasir->total_pendapatan = $transaksi
                ->where('status_transaksi', 'selesai')
                ->sum('total_bayar');

            $kasir->total_transaksi = $transaksi->count();

            return $kasir;
        });

        $data = $data->sortByDesc('total_pendapatan')->values();

        return view('admin2.laporan.kasir.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN METODE BAYAR - ADMIN
    // ===============================
    public function bayarIndex(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('metode_bayar')
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
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar')
            ->orderBy('metode_bayar.id_metode_bayar')
            ->get();

        return view('laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN METODE BAYAR - KASIR
    // ===============================
    public function bayarIndexKasir(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('metode_bayar')
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
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar')
            ->orderBy('metode_bayar.id_metode_bayar')
            ->get();

        return view('kasir.laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN METODE BAYAR - ADMIN2
    // ===============================
    public function bayarIndexAdmin2(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('metode_bayar')
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
                DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
            )
            ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar')
            ->orderBy('metode_bayar.id_metode_bayar')
            ->get();

        return view('admin2.laporan.bayar.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN PENGELUARAN - ADMIN
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
    // LAPORAN PENGELUARAN - KASIR
    // ===============================
    public function pengeluaranIndexKasir(Request $request)
    {
        $query = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC');

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_pengeluaran', [
                $request->dari,
                $request->sampai
            ]);
        }

        $pengeluaran = $query->get();

        return view('kasir.laporan.pengeluaran.index', compact('pengeluaran'));
    }

    // ===============================
    // LAPORAN PENGELUARAN - ADMIN2
    // ===============================
    public function pengeluaranIndexAdmin2(Request $request)
    {
        $query = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC');

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_pengeluaran', [
                $request->dari,
                $request->sampai
            ]);
        }

        $pengeluaran = $query->get();

        return view('admin2.laporan.pengeluaran.index', compact('pengeluaran'));
    }

    // ===============================
    // LAPORAN PELANGGAN - ADMIN
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
            ->whereNotNull('id_pelanggan')
            ->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp')
            ->orderByDesc('total_belanja')
            ->get();

        return view('laporan.pelanggan.index', compact('data'));
    }

    // ===============================
    // LAPORAN PELANGGAN - KASIR
    // ===============================
    public function pelangganIndexKasir()
    {
        $data = Transaksi::selectRaw('
                id_pelanggan,
                nama_pelanggan,
                no_hp,
                COUNT(*) as total_transaksi,
                SUM(total_bayar) as total_belanja
        ')
            ->whereNotNull('id_pelanggan')
            ->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp')
            ->orderByDesc('total_belanja')
            ->get();

        return view('kasir.laporan.pelanggan.index', compact('data'));
    }

    // ===============================
    // LAPORAN PELANGGAN - ADMIN2
    // ===============================
    public function pelangganIndexAdmin2()
    {
        $data = Transaksi::selectRaw('
                id_pelanggan,
                nama_pelanggan,
                no_hp,
                COUNT(*) as total_transaksi,
                SUM(total_bayar) as total_belanja
        ')
            ->whereNotNull('id_pelanggan')
            ->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp')
            ->orderByDesc('total_belanja')
            ->get();

        return view('admin2.laporan.pelanggan.index', compact('data'));
    }

    // ===============================
    // LAPORAN SATUAN - ADMIN
    // ===============================
    public function satuanIndex(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('satuan')
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
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan')
            ->orderBy('satuan.nama_satuan')
            ->get();

        return view('laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN SATUAN - KASIR
    // ===============================
    public function satuanIndexKasir(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('satuan')
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
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan')
            ->orderBy('satuan.nama_satuan')
            ->get();

        return view('kasir.laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir'));
    }

    // ===============================
    // LAPORAN SATUAN - ADMIN2
    // ===============================
    public function satuanIndexAdmin2(Request $request)
    {
        $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

        $data = DB::table('satuan')
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
                DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
            )
            ->groupBy('satuan.id_satuan', 'satuan.nama_satuan')
            ->orderBy('satuan.nama_satuan')
            ->get();

        return view('admin2.laporan.satuan.index', compact('data', 'tglAwal', 'tglAkhir'));
    }
   // ===============================
// LAPORAN DRIVER - ADMIN
// ===============================
public function driver(Request $request)
{
    $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    // Query data driver dari tabel delivery
    $data = DB::table('delivery')
        ->join('driver', 'delivery.id_driver', '=', 'driver.id_driver')
        ->whereBetween(DB::raw('DATE(delivery.waktu)'), [$tglAwal, $tglAkhir])
        ->select(
            'driver.id_driver',
            'driver.nama_driver',
            'driver.no_telp',
            DB::raw('COUNT(delivery.id_delivery) as total_pengiriman'),
            DB::raw('COUNT(CASE WHEN delivery.jenis = "pickup" THEN 1 END) as total_pickup'),
            DB::raw('COUNT(CASE WHEN delivery.jenis = "antar" THEN 1 END) as total_antar'),
            DB::raw('COUNT(CASE WHEN delivery.status = "delivered" THEN 1 END) as terkirim'),
            DB::raw('COUNT(CASE WHEN delivery.status = "failed" THEN 1 END) as gagal'),
            DB::raw('COUNT(CASE WHEN delivery.status IN ("pending", "accepted", "on_the_way_to_pickup", "picked_up", "on_the_way_to_deliver") THEN 1 END) as dalam_proses')
        )
        ->groupBy('driver.id_driver', 'driver.nama_driver', 'driver.no_telp')
        ->orderByDesc('total_pengiriman')
        ->get();

    // Hitung statistik untuk cards
    $stats = [
        'total_driver_aktif' => $data->count(),
        'total_pengiriman' => $data->sum('total_pengiriman'),
        'total_pickup' => $data->sum('total_pickup'),
        'total_antar' => $data->sum('total_antar'),
        'total_terkirim' => $data->sum('terkirim'),
        'total_gagal' => $data->sum('gagal'),
        'total_proses' => $data->sum('dalam_proses'),
    ];

    return view('laporan.driver.index', compact('data', 'stats', 'tglAwal', 'tglAkhir'));
}

// ===============================
// LAPORAN DRIVER - KASIR
// ===============================
public function driverKasir(Request $request)
{
    $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    // Query data driver dari tabel delivery
    $data = DB::table('delivery')
        ->join('driver', 'delivery.id_driver', '=', 'driver.id_driver')
        ->whereBetween(DB::raw('DATE(delivery.waktu)'), [$tglAwal, $tglAkhir])
        ->select(
            'driver.id_driver',
            'driver.nama_driver',
            'driver.no_telp',
            DB::raw('COUNT(delivery.id_delivery) as total_pengiriman'),
            DB::raw('COUNT(CASE WHEN delivery.jenis = "pickup" THEN 1 END) as total_pickup'),
            DB::raw('COUNT(CASE WHEN delivery.jenis = "antar" THEN 1 END) as total_antar'),
            DB::raw('COUNT(CASE WHEN delivery.status = "delivered" THEN 1 END) as terkirim'),
            DB::raw('COUNT(CASE WHEN delivery.status = "failed" THEN 1 END) as gagal'),
            DB::raw('COUNT(CASE WHEN delivery.status IN ("pending", "accepted", "on_the_way_to_pickup", "picked_up", "on_the_way_to_deliver") THEN 1 END) as dalam_proses')
        )
        ->groupBy('driver.id_driver', 'driver.nama_driver', 'driver.no_telp')
        ->orderByDesc('total_pengiriman')
        ->get();

    // Hitung statistik untuk cards
    $stats = [
        'total_driver_aktif' => $data->count(),
        'total_pengiriman' => $data->sum('total_pengiriman'),
        'total_pickup' => $data->sum('total_pickup'),
        'total_antar' => $data->sum('total_antar'),
        'total_terkirim' => $data->sum('terkirim'),
        'total_gagal' => $data->sum('gagal'),
        'total_proses' => $data->sum('dalam_proses'),
    ];

    return view('kasir.laporan.driver.index', compact('data', 'stats', 'tglAwal', 'tglAkhir'));
}

// ===============================
// LAPORAN DRIVER - ADMIN2
// ===============================
public function driverAdmin2(Request $request)
{
    $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    // Query data driver dari tabel delivery
    $data = DB::table('delivery')
        ->join('driver', 'delivery.id_driver', '=', 'driver.id_driver')
        ->whereBetween(DB::raw('DATE(delivery.waktu)'), [$tglAwal, $tglAkhir])
        ->select(
            'driver.id_driver',
            'driver.nama_driver',
            'driver.no_telp',
            DB::raw('COUNT(delivery.id_delivery) as total_pengiriman'),
            DB::raw('COUNT(CASE WHEN delivery.jenis = "pickup" THEN 1 END) as total_pickup'),
            DB::raw('COUNT(CASE WHEN delivery.jenis = "antar" THEN 1 END) as total_antar'),
            DB::raw('COUNT(CASE WHEN delivery.status = "delivered" THEN 1 END) as terkirim'),
            DB::raw('COUNT(CASE WHEN delivery.status = "failed" THEN 1 END) as gagal'),
            DB::raw('COUNT(CASE WHEN delivery.status IN ("pending", "accepted", "on_the_way_to_pickup", "picked_up", "on_the_way_to_deliver") THEN 1 END) as dalam_proses')
        )
        ->groupBy('driver.id_driver', 'driver.nama_driver', 'driver.no_telp')
        ->orderByDesc('total_pengiriman')
        ->get();

    // Hitung statistik untuk cards
    $stats = [
        'total_driver_aktif' => $data->count(),
        'total_pengiriman' => $data->sum('total_pengiriman'),
        'total_pickup' => $data->sum('total_pickup'),
        'total_antar' => $data->sum('total_antar'),
        'total_terkirim' => $data->sum('terkirim'),
        'total_gagal' => $data->sum('gagal'),
        'total_proses' => $data->sum('dalam_proses'),
    ];

    return view('admin2.laporan.driver.index', compact('data', 'stats', 'tglAwal', 'tglAkhir'));
}
 // ✅ METHOD BARU: Export Excel
public function exportTransaksi(Request $request)
{
    $request->validate([
        'filter_type' => 'required|in:tanggal_masuk,tanggal_selesai,tanggal_bayar',
        'tanggal_awal' => 'required|date',
        'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        'status_bayar' => 'in:semua,belum_lunas,DP,lunas'
    ]);

    $namaFile = $request->nama_file ?: 'Laporan_Transaksi_' . date('d-m-Y');
    $namaFile = preg_replace('/[^A-Za-z0-9\-_]/', '_', $namaFile) . '.xlsx';

    return Excel::download(
        new TransaksiExport(
            $request->filter_type,
            $request->tanggal_awal,
            $request->tanggal_akhir,
            $request->status_bayar
        ),
        $namaFile
    );
}

     // ✅ METHOD BARU: Export Excel
    public function exportTransaksiAdmin2(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:tanggal_masuk,tanggal_selesai,tanggal_bayar',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'status_bayar' => 'in:semua,lunas,belum_lunas,dp'
        ]);

        $namaFile = $request->nama_file ?: 'Laporan_Transaksi_' . date('d-m-Y');
        $namaFile = preg_replace('/[^A-Za-z0-9\-_]/', '_', $namaFile) . '.xlsx';

        return Excel::download(
            new TransaksiExport(
                $request->filter_type,
                $request->tanggal_awal,
                $request->tanggal_akhir,
                $request->status_bayar
            ),
            $namaFile
        );
    }
     // ✅ METHOD BARU: Export Excel
    public function exportTransaksiKasir(Request $request)
    {
        $request->validate([
            'filter_type' => 'required|in:tanggal_masuk,tanggal_selesai,tanggal_bayar',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'status_bayar' => 'in:semua,lunas,belum_lunas,dp'
        ]);

        $namaFile = $request->nama_file ?: 'Laporan_Transaksi_' . date('d-m-Y');
        $namaFile = preg_replace('/[^A-Za-z0-9\-_]/', '_', $namaFile) . '.xlsx';

        return Excel::download(
            new TransaksiExport(
                $request->filter_type,
                $request->tanggal_awal,
                $request->tanggal_akhir,
                $request->status_bayar
            ),
            $namaFile
        );
    }
}