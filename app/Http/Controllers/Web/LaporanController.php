<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransaksiExport; 
use Illuminate\Support\Facades\Auth;

// ✅ TAMBAHKAN INI untuk export pengeluaran
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // =========================================
    // PENGELUARAN - ADMIN
    // =========================================
    
    public function index()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('pengeluaran', 'view');
        
        $pengeluaran = Pengeluaran::orderBy('id_pengeluaran', 'DESC')->get();
        return view('pengeluaran.index', compact('pengeluaran'));
    }

    public function create()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pengeluaran', 'add');
        
        return view('pengeluaran.create');
    }

    public function store(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pengeluaran', 'add');
        
        Pengeluaran::create([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pengeluaran', 'edit');
        
        $item = Pengeluaran::findOrFail($id);
        return view('pengeluaran.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pengeluaran', 'edit');
        
        $item = Pengeluaran::findOrFail($id);
        $item->update([
            'nama_pengeluaran' => $request->nama_pengeluaran,
            'nominal' => $request->nominal,
            'catatan' => $request->catatan,
            'tanggal_pengeluaran' => $request->tanggal,
        ]);

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('pengeluaran', 'delete');
        
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================================
    // PENGELUARAN - KASIR
    // =========================================
    
    public function indexKasir()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('pengeluaran', 'view');

        $pengeluaran = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kasir.pengeluaran.index', compact('pengeluaran'));
    }

    public function createKasir()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pengeluaran', 'add');
        
        return view('kasir.pengeluaran.create');
    }

    public function storeKasir(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pengeluaran', 'add');
        
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

    public function editKasir($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pengeluaran', 'edit');
        
        $item = Pengeluaran::findOrFail($id);
        return view('kasir.pengeluaran.edit', compact('item'));
    }

    public function updateKasir(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pengeluaran', 'edit');
        
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

    public function destroyKasir($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('pengeluaran', 'delete');
        
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================================
    // PENGELUARAN - ADMIN2
    // =========================================
    
    public function indexAdmin2()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('pengeluaran', 'view');

        $pengeluaran = Pengeluaran::orderBy('tanggal_pengeluaran', 'DESC')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin2.pengeluaran.index', compact('pengeluaran'));
    }

    public function createAdmin2()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pengeluaran', 'add');
        
        return view('admin2.pengeluaran.create');
    }

    public function storeAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pengeluaran', 'add');
        
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

    public function editAdmin2($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pengeluaran', 'edit');
        
        $item = Pengeluaran::findOrFail($id);
        return view('admin2.pengeluaran.edit', compact('item'));
    }

    public function updateAdmin2(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pengeluaran', 'edit');
        
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

    public function destroyAdmin2($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('pengeluaran', 'delete');
        
        $item = Pengeluaran::findOrFail($id);
        $item->delete();

        return redirect()->route('admin2.pengeluaran.index')->with('success', 'Data berhasil dihapus');
    }

    // =========================================
    // LAPORAN INDEX (Dashboard Laporan)
    // =========================================
    
    public function laporanIndex()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
        return view('laporan.index');
    }

    public function laporanIndexKasir()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
        return view('kasir.laporan.index');
    }

    public function laporanIndexAdmin2()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
        return view('admin2.laporan.index');
    }

    // =========================================
    // LAPORAN TRANSAKSI
    // =========================================
/// ADMIN
public function transaksiIndex(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $query = Transaksi::with(['pelanggan', 'metodeBayar', 'kasir'])
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

    // Filter Search
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('nama_pelanggan', 'like', "%$q%")
                ->orWhere('no_hp', 'like', "%$q%")
                ->orWhere('id_transaksi', 'like', "%$q%");
        });
    }

    // ✅ Filter Status Transaksi
    if ($request->filled('status')) {
        $query->whereIn('status_transaksi', $request->status);
    }

    // ✅ Filter Status Pembayaran
    if ($request->filled('bayar')) {
        $query->whereIn('status_bayar', $request->bayar);
    }

    // ✅ Filter Jenis Transaksi
    if ($request->filled('jenis')) {
        $query->whereIn('jenis_transaksi', $request->jenis);
    }

    // ✅ Sorting
    $sortBy = $request->sort ?? 'terbaru';
    switch($sortBy) {
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

    // Pagination (10 per halaman)
    $transaksi = $query->paginate(10)->withQueryString();
    
    // Hitung total omzet dan jumlah dari semua data (dengan filter yang sama)
    $queryTotal = Transaksi::whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);
    
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

    return view('laporan.transaksi.index', [
        'transaksi'  => $transaksi,
        'tglAwal'    => $tglAwal,
        'tglAkhir'   => $tglAkhir,
        'totalOmzet' => $queryTotal->sum('total_bayar'),
        'jumlah'     => $queryTotal->count(),
    ]);
}

// KASIR
public function transaksiIndexKasir(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $query = Transaksi::with(['pelanggan', 'metodeBayar', 'kasir'])
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

    // Filter Search
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('nama_pelanggan', 'like', "%$q%")
                ->orWhere('no_hp', 'like', "%$q%")
                ->orWhere('id_transaksi', 'like', "%$q%");
        });
    }

    // ✅ Filter Status Transaksi
    if ($request->filled('status')) {
        $query->whereIn('status_transaksi', $request->status);
    }

    // ✅ Filter Status Pembayaran
    if ($request->filled('bayar')) {
        $query->whereIn('status_bayar', $request->bayar);
    }

    // ✅ Filter Jenis Transaksi
    if ($request->filled('jenis')) {
        $query->whereIn('jenis_transaksi', $request->jenis);
    }

    // ✅ Sorting
    $sortBy = $request->sort ?? 'terbaru';
    switch($sortBy) {
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

    // Pagination (10 per halaman)
    $transaksi = $query->paginate(10)->withQueryString();
    
    // Hitung total omzet dan jumlah dari semua data (dengan filter yang sama)
    $queryTotal = Transaksi::whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);
    
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

    return view('kasir.laporan.transaksi.index', [
        'transaksi'  => $transaksi,
        'tglAwal'    => $tglAwal,
        'tglAkhir'   => $tglAkhir,
        'totalOmzet' => $queryTotal->sum('total_bayar'),
        'jumlah'     => $queryTotal->count(),
    ]);
}

// ADMIN2
public function transaksiIndexAdmin2(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $query = Transaksi::with(['pelanggan', 'metodeBayar', 'kasir'])
        ->whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);

    // Filter Search
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->where('nama_pelanggan', 'like', "%$q%")
                ->orWhere('no_hp', 'like', "%$q%")
                ->orWhere('id_transaksi', 'like', "%$q%");
        });
    }

    // ✅ Filter Status Transaksi
    if ($request->filled('status')) {
        $query->whereIn('status_transaksi', $request->status);
    }

    // ✅ Filter Status Pembayaran
    if ($request->filled('bayar')) {
        $query->whereIn('status_bayar', $request->bayar);
    }

    // ✅ Filter Jenis Transaksi
    if ($request->filled('jenis')) {
        $query->whereIn('jenis_transaksi', $request->jenis);
    }

    // ✅ Sorting
    $sortBy = $request->sort ?? 'terbaru';
    switch($sortBy) {
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

    // Pagination (10 per halaman)
    $transaksi = $query->paginate(10)->withQueryString();
    
    // Hitung total omzet dan jumlah dari semua data (dengan filter yang sama)
    $queryTotal = Transaksi::whereBetween('tgl_transaksi', [
            $tglAwal.' 00:00:00',
            $tglAkhir.' 23:59:59'
        ]);
    
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
    
    public function kasirIndex(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function kasirIndexAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    // =========================================
    // LAPORAN METODE BAYAR
    // =========================================
    
    public function bayarIndex(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function bayarIndexKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function bayarIndexAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    // =========================================
    // LAPORAN PENGELUARAN
    // =========================================
    
    public function pengeluaranIndex(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function pengeluaranIndexKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function pengeluaranIndexAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    // =========================================
    // LAPORAN PELANGGAN
    // =========================================
// ADMIN
public function pelangganIndex(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
    ')->whereNotNull('id_pelanggan');

    // Filter tanggal
    if ($request->dari && $request->sampai) {
        $query->whereBetween('tgl_transaksi', [
            $request->dari . ' 00:00:00',
            $request->sampai . ' 23:59:59'
        ]);
    }

    // Search
    if ($request->q) {
        $query->where(function($q) use ($request) {
            $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
              ->orWhere('no_hp', 'like', '%' . $request->q . '%');
        });
    }

    $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

    // Sorting
    switch($request->sort) {
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

    return view('laporan.pelanggan.index', compact('data'));
}

// KASIR
public function pelangganIndexKasir(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
    ')->whereNotNull('id_pelanggan');

    // Filter tanggal
    if ($request->dari && $request->sampai) {
        $query->whereBetween('tgl_transaksi', [
            $request->dari . ' 00:00:00',
            $request->sampai . ' 23:59:59'
        ]);
    }

    // Search
    if ($request->q) {
        $query->where(function($q) use ($request) {
            $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
              ->orWhere('no_hp', 'like', '%' . $request->q . '%');
        });
    }

    $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

    // Sorting
    switch($request->sort) {
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

    return view('kasir.laporan.pelanggan.index', compact('data'));
}

// ADMIN2
public function pelangganIndexAdmin2(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
    ')->whereNotNull('id_pelanggan');

    // Filter tanggal
    if ($request->dari && $request->sampai) {
        $query->whereBetween('tgl_transaksi', [
            $request->dari . ' 00:00:00',
            $request->sampai . ' 23:59:59'
        ]);
    }

    // Search
    if ($request->q) {
        $query->where(function($q) use ($request) {
            $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
              ->orWhere('no_hp', 'like', '%' . $request->q . '%');
        });
    }

    $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

    // Sorting
    switch($request->sort) {
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

    return view('admin2.laporan.pelanggan.index', compact('data'));
}
    // =========================================
    // LAPORAN SATUAN
    // =========================================
    
    public function satuanIndex(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function satuanIndexKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function satuanIndexAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    // =========================================
    // LAPORAN DRIVER
    // =========================================
    
    public function driver(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

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

    public function driverKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

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

    public function driverAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
        $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
        $tglAkhir = $request->sampai ?? now()->toDateString();

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

    // =========================================
    // EXPORT EXCEL
    // =========================================
    
    public function exportTransaksi(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function exportTransaksiKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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

    public function exportTransaksiAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('laporan', 'view');
        
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
public function exportPengeluaran(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $format = $request->format ?? 'excel';

    // Build query
    $query = Pengeluaran::query();

    if ($request->dari && $request->sampai) {
        $query->whereBetween('tanggal_pengeluaran', [$request->dari, $request->sampai]);
    }

    if ($request->q) {
        $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
    }

    $query->orderBy('tanggal_pengeluaran', 'desc');
    $pengeluaran = $query->get();

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
 * Export Pengeluaran untuk KASIR
 */
public function exportPengeluaranKasir(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $format = $request->format ?? 'excel';

    // Build query
    $query = Pengeluaran::query();

    if ($request->dari && $request->sampai) {
        $query->whereBetween('tanggal_pengeluaran', [$request->dari, $request->sampai]);
    }

    if ($request->q) {
        $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
    }

    $query->orderBy('tanggal_pengeluaran', 'desc');
    $pengeluaran = $query->get();

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
 * Export Pengeluaran untuk ADMIN2
 */
public function exportPengeluaranAdmin2(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $format = $request->format ?? 'excel';

    // Build query
    $query = Pengeluaran::query();

    if ($request->dari && $request->sampai) {
        $query->whereBetween('tanggal_pengeluaran', [$request->dari, $request->sampai]);
    }

    if ($request->q) {
        $query->where('nama_pengeluaran', 'like', '%' . $request->q . '%');
    }

    $query->orderBy('tanggal_pengeluaran', 'desc');
    $pengeluaran = $query->get();

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
// PRIVATE METHODS - Export Helper Functions
// =========================================

private function exportPengeluaranExcel($pengeluaran, $request)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Laundry System')
        ->setTitle('Laporan Pengeluaran')
        ->setSubject('Laporan Pengeluaran')
        ->setDescription('Laporan data pengeluaran');

    // Header Info
    $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN');
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Date range info
    $dateRange = 'Periode: ';
    if ($request->dari && $request->sampai) {
        $dateRange .= \Carbon\Carbon::parse($request->dari)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($request->sampai)->format('d/m/Y');
    } else {
        $dateRange .= 'Semua Data';
    }
    $sheet->setCellValue('A2', $dateRange);
    $sheet->mergeCells('A2:D2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Print date
    $sheet->setCellValue('A3', 'Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:D3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Table headers
    $row = 5;
    $headers = ['No', 'Tanggal', 'Nama Pengeluaran', 'Nominal'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . $row, $header);
        $sheet->getStyle($column . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FCD34D'); // Yellow-400
        $sheet->getStyle($column . $row)->getFont()->setBold(true);
        $sheet->getStyle($column . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $column++;
    }

    // Data rows
    $row = 6;
    $no = 1;
    $totalNominal = 0;

    foreach ($pengeluaran as $item) {
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->tanggal_pengeluaran)->format('d/m/Y'));
        $sheet->setCellValue('C' . $row, $item->nama_pengeluaran);
        $sheet->setCellValue('D' . $row, $item->nominal);
        
        // Format currency
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        
        // Center align number column
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $totalNominal += $item->nominal;
        $row++;
    }

    // Total row
    $sheet->setCellValue('C' . $row, 'TOTAL');
    $sheet->setCellValue('D' . $row, $totalNominal);
    $sheet->getStyle('C' . $row . ':D' . $row)->getFont()->setBold(true);
    $sheet->getStyle('C' . $row . ':D' . $row)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FED7AA'); // Orange-200
    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');

    // Apply borders
    $sheet->getStyle('A5:D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Auto-size columns
    foreach (range('A', 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Generate file
    $fileName = 'Laporan_Pengeluaran_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

private function exportPengeluaranPdf($pengeluaran, $request)
{
    $data = [
        'pengeluaran' => $pengeluaran,
        'total' => $pengeluaran->sum('nominal'),
        'dari' => $request->dari,
        'sampai' => $request->sampai,
        'tanggal_cetak' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pengeluaran.pdf', $data);
    $pdf->setPaper('a4', 'portrait');
    
    $fileName = 'Laporan_Pengeluaran_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.pdf';
    
    return $pdf->download($fileName);
}

private function exportPengeluaranCsv($pengeluaran, $request)
{
    $fileName = 'Laporan_Pengeluaran_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function() use ($pengeluaran, $request) {
        $file = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header info
        fputcsv($file, ['LAPORAN PENGELUARAN']);
        
        $dateRange = 'Periode: ';
        if ($request->dari && $request->sampai) {
            $dateRange .= \Carbon\Carbon::parse($request->dari)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($request->sampai)->format('d/m/Y');
        } else {
            $dateRange .= 'Semua Data';
        }
        fputcsv($file, [$dateRange]);
        fputcsv($file, ['Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s')]);
        fputcsv($file, []); // Empty row

        // Column headers
        fputcsv($file, ['No', 'Tanggal', 'Nama Pengeluaran', 'Nominal']);

        // Data rows
        $no = 1;
        $total = 0;
        foreach ($pengeluaran as $item) {
            fputcsv($file, [
                $no++,
                \Carbon\Carbon::parse($item->tanggal_pengeluaran)->format('d/m/Y'),
                $item->nama_pengeluaran,
                $item->nominal
            ]);
            $total += $item->nominal;
        }

        // Total row
        fputcsv($file, ['', '', 'TOTAL', $total]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

// =========================================
// EXPORT PELANGGAN - Tambahkan method ini ke LaporanController.php
// =========================================

/**
 * Export Pelanggan untuk ADMIN
 */
public function exportPelanggan(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $format = $request->format ?? 'excel';
    
    // Get data pelanggan
    $data = $this->getPelangganData($request);

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
 * Export Pelanggan untuk KASIR
 */
public function exportPelangganKasir(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $format = $request->format ?? 'excel';
    
    // Get data pelanggan
    $data = $this->getPelangganData($request);

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
 * Export Pelanggan untuk ADMIN2
 */
public function exportPelangganAdmin2(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    $format = $request->format ?? 'excel';
    
    // Get data pelanggan
    $data = $this->getPelangganData($request);

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
// PRIVATE METHODS - Export Helper Functions
// =========================================

private function getPelangganData($request)
{
    $query = Transaksi::selectRaw('
            id_pelanggan,
            nama_pelanggan,
            no_hp,
            COUNT(*) as total_transaksi,
            SUM(total_bayar) as total_belanja
    ')->whereNotNull('id_pelanggan');

    // Filter tanggal
    if ($request->dari && $request->sampai) {
        $query->whereBetween('tgl_transaksi', [
            $request->dari . ' 00:00:00',
            $request->sampai . ' 23:59:59'
        ]);
    }

    // Search
    if ($request->q) {
        $query->where(function($q) use ($request) {
            $q->where('nama_pelanggan', 'like', '%' . $request->q . '%')
              ->orWhere('no_hp', 'like', '%' . $request->q . '%');
        });
    }

    $query->groupBy('id_pelanggan', 'nama_pelanggan', 'no_hp');

    // Sorting
    switch($request->sort) {
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

    return $query->get();
}

private function exportPelangganExcel($data, $request)
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Laundry System')
        ->setTitle('Laporan Pelanggan')
        ->setSubject('Laporan Pelanggan')
        ->setDescription('Laporan data pelanggan');

    // Header Info
    $sheet->setCellValue('A1', 'LAPORAN PELANGGAN');
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Date range info
    $dateRange = 'Periode: ';
    if ($request->dari && $request->sampai) {
        $dateRange .= \Carbon\Carbon::parse($request->dari)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($request->sampai)->format('d/m/Y');
    } else {
        $dateRange .= 'Semua Data';
    }
    $sheet->setCellValue('A2', $dateRange);
    $sheet->mergeCells('A2:E2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Print date
    $sheet->setCellValue('A3', 'Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:E3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Table headers
    $row = 5;
    $headers = ['No', 'Nama Pelanggan', 'No HP', 'Total Transaksi', 'Total Belanja'];
    $column = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($column . $row, $header);
        $sheet->getStyle($column . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FCD34D'); // Yellow-400
        $sheet->getStyle($column . $row)->getFont()->setBold(true);
        $sheet->getStyle($column . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $column++;
    }

    // Data rows
    $row = 6;
    $no = 1;
    $totalTransaksi = 0;
    $totalBelanja = 0;

    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $item->nama_pelanggan);
        $sheet->setCellValue('C' . $row, $item->no_hp);
        $sheet->setCellValue('D' . $row, $item->total_transaksi);
        $sheet->setCellValue('E' . $row, $item->total_belanja);
        
        // Format currency
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        
        // Center align
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $totalTransaksi += $item->total_transaksi;
        $totalBelanja += $item->total_belanja;
        $row++;
    }

    // Total row
    $sheet->setCellValue('D' . $row, 'TOTAL');
    $sheet->setCellValue('E' . $row, $totalBelanja);
    $sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);
    $sheet->getStyle('D' . $row . ':E' . $row)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FED7AA'); // Orange-200
    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Apply borders
    $sheet->getStyle('A5:E' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Generate file
    $fileName = 'Laporan_Pelanggan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

private function exportPelangganPdf($data, $request)
{
    $pdfData = [
        'data' => $data,
        'totalPelanggan' => $data->count(),
        'totalTransaksi' => $data->sum('total_transaksi'),
        'totalBelanja' => $data->sum('total_belanja'),
        'dari' => $request->dari,
        'sampai' => $request->sampai,
        'tanggal_cetak' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
    ];

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pelanggan.pdf', $pdfData);
    $pdf->setPaper('a4', 'portrait');
    
    $fileName = 'Laporan_Pelanggan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.pdf';
    
    return $pdf->download($fileName);
}

private function exportPelangganCsv($data, $request)
{
    $fileName = 'Laporan_Pelanggan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];

    $callback = function() use ($data, $request) {
        $file = fopen('php://output', 'w');
        
        // UTF-8 BOM for Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header info
        fputcsv($file, ['LAPORAN PELANGGAN']);
        
        $dateRange = 'Periode: ';
        if ($request->dari && $request->sampai) {
            $dateRange .= \Carbon\Carbon::parse($request->dari)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($request->sampai)->format('d/m/Y');
        } else {
            $dateRange .= 'Semua Data';
        }
        fputcsv($file, [$dateRange]);
        fputcsv($file, ['Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s')]);
        fputcsv($file, []); // Empty row

        // Column headers
        fputcsv($file, ['No', 'Nama Pelanggan', 'No HP', 'Total Transaksi', 'Total Belanja']);

        // Data rows
        $no = 1;
        $totalTransaksi = 0;
        $totalBelanja = 0;
        
        foreach ($data as $item) {
            fputcsv($file, [
                $no++,
                $item->nama_pelanggan,
                $item->no_hp,
                $item->total_transaksi,
                $item->total_belanja
            ]);
            $totalTransaksi += $item->total_transaksi;
            $totalBelanja += $item->total_belanja;
        }

        // Total row
        fputcsv($file, ['', '', '', 'TOTAL', $totalBelanja]);

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
// =========================================
// EXPORT KASIR (EXCEL ONLY)
// Tambahkan method ini ke LaporanController.php
// =========================================

/**
 * Export Kasir Excel - ADMIN
 */
public function exportKasir(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
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

    // Buat Excel
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $sheet->setCellValue('A1', 'LAPORAN KINERJA KASIR');
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Periode
    $periode = 'Periode: ' . \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y');
    $sheet->setCellValue('A2', $periode);
    $sheet->mergeCells('A2:J2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Tanggal cetak
    $sheet->setCellValue('A3', 'Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:J3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Header tabel
    $row = 5;
    $headers = ['No', 'Nama Kasir', 'No HP', 'Antrian', 'Proses', 'Siap Ambil', 'Selesai', 'Batal', 'Total Transaksi', 'Total Pendapatan'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FCD34D');
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $col++;
    }

    // Data
    $row = 6;
    $no = 1;
    $totalPendapatan = 0;
    $totalTransaksi = 0;

    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $item->nama_kasir);
        $sheet->setCellValue('C' . $row, $item->no_hp);
        $sheet->setCellValue('D' . $row, $item->antrian);
        $sheet->setCellValue('E' . $row, $item->proses);
        $sheet->setCellValue('F' . $row, $item->siap_ambil);
        $sheet->setCellValue('G' . $row, $item->selesai);
        $sheet->setCellValue('H' . $row, $item->batal);
        $sheet->setCellValue('I' . $row, $item->total_transaksi);
        $sheet->setCellValue('J' . $row, $item->total_pendapatan);
        
        // Format currency
        $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
        
        // Center align
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $row . ':I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $totalPendapatan += $item->total_pendapatan;
        $totalTransaksi += $item->total_transaksi;
        $row++;
    }

    // Total
    $sheet->setCellValue('I' . $row, 'TOTAL');
    $sheet->setCellValue('J' . $row, $totalPendapatan);
    $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
    $sheet->getStyle('I' . $row . ':J' . $row)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('FED7AA');
    $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Border
    $sheet->getStyle('A5:J' . $row)->getBorders()->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    // Auto width
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Download
    $fileName = 'Laporan_Kasir_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

/**
 * Export Kasir Excel - ADMIN2
 */
public function exportKasirAdmin2(Request $request)
{
    // ✅ CHECK PERMISSION VIEW
    requirePermission('laporan', 'view');
    
    // Panggil method yang sama
    return $this->exportKasir($request);
}

// =========================================
// TAMBAHKAN METHOD EXPORT YANG BELUM ADA
// Copy-paste method ini ke LaporanController.php (sebelum closing brace })
// =========================================

// ========== EXPORT METODE BAYAR ==========
public function exportBayar(Request $request)
{
    requirePermission('laporan', 'view');
    
    $tglAwal  = $request->dari ?? now()->subMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

    $data = DB::table('metode_bayar')
        ->leftJoin('transaksi', function ($join) use ($tglAwal, $tglAkhir) {
            $join->on('metode_bayar.id_metode_bayar', '=', 'transaksi.id_metode_bayar')
                ->where('transaksi.status_transaksi', 'selesai')
                ->whereBetween('transaksi.tgl_transaksi', [$tglAwal.' 00:00:00', $tglAkhir.' 23:59:59']);
        })
        ->select(
            'metode_bayar.id_metode_bayar',
            'metode_bayar.nama_metode_bayar',
            DB::raw('COUNT(transaksi.id_transaksi) as total_penggunaan')
        )
        ->groupBy('metode_bayar.id_metode_bayar', 'metode_bayar.nama_metode_bayar')
        ->orderBy('metode_bayar.id_metode_bayar')
        ->get();

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'LAPORAN METODE PEMBAYARAN');
    $sheet->mergeCells('A1:C1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $periode = 'Periode: ' . \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y');
    $sheet->setCellValue('A2', $periode);
    $sheet->mergeCells('A2:C2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:C3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $row = 5;
    $headers = ['No', 'Metode Pembayaran', 'Total Penggunaan'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCD34D');
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $col++;
    }

    $row = 6;
    $no = 1;
    $totalPenggunaan = 0;

    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $item->nama_metode_bayar);
        $sheet->setCellValue('C' . $row, $item->total_penggunaan);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $totalPenggunaan += $item->total_penggunaan;
        $row++;
    }

    $sheet->setCellValue('B' . $row, 'TOTAL');
    $sheet->setCellValue('C' . $row, $totalPenggunaan);
    $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
    $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7AA');
    $sheet->getStyle('A5:C' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $fileName = 'Laporan_Metode_Bayar_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportBayarKasir(Request $request) {
    requirePermission('laporan', 'view');
    return $this->exportBayar($request);
}

public function exportBayarAdmin2(Request $request) {
    requirePermission('laporan', 'view');
    return $this->exportBayar($request);
}


// ========== EXPORT SATUAN ==========
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
                ->whereBetween('transaksi.tgl_transaksi', [$tglAwal.' 00:00:00', $tglAkhir.' 23:59:59']);
        })
        ->select(
            'satuan.id_satuan',
            'satuan.nama_satuan',
            DB::raw('COALESCE(SUM(detail_transaksi.qty),0) as total_qty')
        )
        ->groupBy('satuan.id_satuan', 'satuan.nama_satuan')
        ->orderBy('satuan.nama_satuan')
        ->get();

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'LAPORAN SATUAN');
    $sheet->mergeCells('A1:C1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $periode = 'Periode: ' . \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y');
    $sheet->setCellValue('A2', $periode);
    $sheet->mergeCells('A2:C2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:C3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $row = 5;
    $headers = ['No', 'Nama Satuan', 'Total Qty'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCD34D');
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $col++;
    }

    $row = 6;
    $no = 1;
    $totalQty = 0;

    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $item->nama_satuan);
        $sheet->setCellValue('C' . $row, $item->total_qty);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $totalQty += $item->total_qty;
        $row++;
    }

    $sheet->setCellValue('B' . $row, 'TOTAL');
    $sheet->setCellValue('C' . $row, $totalQty);
    $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
    $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7AA');
    $sheet->getStyle('A5:C' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $fileName = 'Laporan_Satuan_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportSatuanKasir(Request $request) {
    requirePermission('laporan', 'view');
    return $this->exportSatuan($request);
}

public function exportSatuanAdmin2(Request $request) {
    requirePermission('laporan', 'view');
    return $this->exportSatuan($request);
}


// ========== EXPORT DRIVER ==========
public function exportDriver(Request $request)
{
    requirePermission('laporan', 'view');
    
    $tglAwal  = $request->dari ?? now()->startOfMonth()->toDateString();
    $tglAkhir = $request->sampai ?? now()->toDateString();

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

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'LAPORAN KINERJA DRIVER');
    $sheet->mergeCells('A1:I1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $periode = 'Periode: ' . \Carbon\Carbon::parse($tglAwal)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($tglAkhir)->format('d/m/Y');
    $sheet->setCellValue('A2', $periode);
    $sheet->mergeCells('A2:I2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Dicetak: ' . \Carbon\Carbon::now()->format('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:I3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $row = 5;
    $headers = ['No', 'Nama Driver', 'No HP', 'Total Pengiriman', 'Pickup', 'Antar', 'Terkirim', 'Gagal', 'Dalam Proses'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FCD34D');
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $col++;
    }

    $row = 6;
    $no = 1;
    $totalPengiriman = 0;

    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $no++);
        $sheet->setCellValue('B' . $row, $item->nama_driver);
        $sheet->setCellValue('C' . $row, $item->no_telp);
        $sheet->setCellValue('D' . $row, $item->total_pengiriman);
        $sheet->setCellValue('E' . $row, $item->total_pickup);
        $sheet->setCellValue('F' . $row, $item->total_antar);
        $sheet->setCellValue('G' . $row, $item->terkirim);
        $sheet->setCellValue('H' . $row, $item->gagal);
        $sheet->setCellValue('I' . $row, $item->dalam_proses);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $row . ':I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $totalPengiriman += $item->total_pengiriman;
        $row++;
    }

    $sheet->setCellValue('C' . $row, 'TOTAL');
    $sheet->setCellValue('D' . $row, $totalPengiriman);
    $sheet->getStyle('C' . $row . ':D' . $row)->getFont()->setBold(true);
    $sheet->getStyle('C' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FED7AA');
    $sheet->getStyle('A5:I' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $fileName = 'Laporan_Driver_' . \Carbon\Carbon::now()->format('Y-m-d_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

public function exportDriverKasir(Request $request) {
    requirePermission('laporan', 'view');
    return $this->exportDriver($request);
}

public function exportDriverAdmin2(Request $request) {
    requirePermission('laporan', 'view');
    return $this->exportDriver($request);
}
}