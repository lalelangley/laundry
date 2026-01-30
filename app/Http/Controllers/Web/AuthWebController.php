<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Kasir;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Menu;
use App\Models\MenuRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthWebController extends Controller
{
    // =============================
    // LOGIN (No Permission Check)
    // =============================
    public function showLogin()
    {
        $admins = Admin::all();
        $kasirs = Kasir::all();

        return view('auth.login', compact('admins', 'kasirs'));
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'pin' => 'required',
        ]);

        if (!str_contains($request->user_id, '-')) {
            return back()->with('error', 'Pilih pengguna terlebih dahulu');
        }

        [$role, $id] = explode('-', $request->user_id);
        $pin = trim($request->pin);

        if ($role === 'admin') {
            $admin = Admin::find($id);
            if (!$admin || !Hash::check($pin, $admin->password) || $admin->status !== 'aktif') {
                return back()->with('error', 'PIN salah atau user tidak aktif');
            }

            auth()->guard('admin')->login($admin);
            $request->session()->regenerate();

            if ($admin->role_id == 1) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('admin2.dashboard');
            }
        }

        if ($role === 'kasir') {
            $kasir = Kasir::find($id);
            if (!$kasir || !Hash::check($pin, $kasir->password) || $kasir->status !== 'aktif') {
                return back()->with('error', 'PIN salah atau user tidak aktif');
            }

            auth()->guard('kasir')->login($kasir);
            $request->session()->regenerate();
            
            return redirect()->route('kasir.dashboard');
        }

        return back()->with('error', 'Role tidak dikenali');
    }

   // =============================
// DASHBOARD ADMIN (No Permission Check)
// =============================
public function adminDashboard()
{
    $admin = auth()->guard('admin')->user();

    if (!$admin) {
        return redirect()->route('login.show')
            ->with('error', 'Silakan login dulu');
    }

    $totalPelanggan  = Pelanggan::count();
    $totalKasir      = Kasir::count();
    $totalTransaksi  = Transaksi::where('jenis_transaksi', 'offline')->count();
    
    $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
        ->whereDate('tgl_transaksi', today())
        ->where('status_bayar', 'lunas')
        ->sum('total_bayar');

    // ✅ NOTIFIKASI & ALERT DATA - FIXED VERSION
$transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
    ->where('status_transaksi', 'antrian')
    ->where('jenis_transaksi', 'online')  // ← ONLINE ONLY
    ->count();

$pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
    ->where('status_bayar', 'lunas')
    ->where('jenis_transaksi', 'online')  // ← ONLINE ONLY
    ->count();

$belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
    ->where('jenis_transaksi', 'online')  // ← ONLINE ONLY
    ->whereNotIn('status_transaksi', ['batal', 'selesai'])
    ->count();

// ✅ Transaksi online yang butuh driver
$butuhPickup = DB::table('delivery')
    ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
    ->where('delivery.jenis', 'pickup')
    ->whereNull('delivery.id_driver')
    ->where('transaksi.jenis_transaksi', 'online')
    ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal']) // ← INI KEY-nya!
    ->count();

$butuhAntar = DB::table('delivery')
    ->where('jenis', 'antar')
    ->whereNull('id_driver')
    ->count();

// ✅ Estimasi selesai hari ini - ONLINE ONLY
$harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
    ->where('jenis_transaksi', 'online')  // ← ONLINE ONLY
    ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
    ->count();

// ✅ Terlambat - ONLINE ONLY
$terlambat = Transaksi::where('tgl_estimasi', '<', now())
    ->where('jenis_transaksi', 'online')  // ← ONLINE ONLY
    ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
    ->count();

// ✅ Siap diambil - ONLINE ONLY
$siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
    ->where('jenis_transaksi', 'online')  // ← ONLINE ONLY
    ->count();

    $orders = Transaksi::query()
        ->orderBy('id_transaksi', 'DESC')
        ->get()
        ->map(function($o) {
            $o->deadline = $o->tgl_estimasi 
                ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                : '-';
            
            if ($o->tgl_estimasi) {
                $estimasi = Carbon::parse($o->tgl_estimasi);
                
                if ($estimasi->isPast() && $o->status_transaksi !== 'selesai') {
                    $o->deadline_status = 'terlambat';
                } elseif ($estimasi->diffInHours(now()) <= 24 && $o->status_transaksi !== 'selesai') {
                    $o->deadline_status = 'mendesak';
                } else {
                    $o->deadline_status = 'normal';
                }
            } else {
                $o->deadline_status = 'no_deadline';
            }
            
            return $o;
        });

    return view('admin.dashboard', compact(
        'admin',
        'totalPelanggan',
        'totalKasir',
        'totalTransaksi',
        'totalOmzet',
        'orders',
        // Notifikasi
        'transaksiMasukHariIni',
        'pembayaranLunasHariIni',
        'belumLunas',
        'butuhPickup',
        'butuhAntar',
        'harusSelesaiHariIni',
        'terlambat',
        'siapDiambil'
    ));
}
// =============================
// DASHBOARD ADMIN2 (No Permission Check)
// =============================
public function admin2Dashboard()
{
    $admin = auth()->guard('admin')->user();
    
    if (!$admin || $admin->role_id == 1) {
        return redirect()->route('login.show')
            ->with('error', 'Silakan login sebagai admin biasa');
    }

    // ========================================
    // STATISTIK OFFLINE (untuk cards)
    // ========================================
    $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
        ->whereDate('tgl_transaksi', today())
        ->where('status_bayar', 'lunas')
        ->sum('total_bayar');

    $masuk = Transaksi::where('status_transaksi', 'antrian')
        ->where('jenis_transaksi', 'offline')
        ->count();

    $harusSelesai = Transaksi::whereDate('tgl_estimasi', Carbon::today())
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->where('jenis_transaksi', 'offline')
        ->count();

    $terlambat = Transaksi::whereDate('tgl_estimasi', '<', Carbon::today())
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->where('jenis_transaksi', 'offline')
        ->count();

    // ========================================
    // NOTIFIKASI ONLINE (untuk notification panel)
    // ========================================
    
    // Transaksi masuk hari ini (Online + Antrian)
    $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
        ->where('status_transaksi', 'antrian')
        ->where('jenis_transaksi', 'online')
        ->count();

    // Belum lunas (Online + Belum Lunas + BELUM Selesai/Batal)
    $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
        ->where('jenis_transaksi', 'online')
        ->whereNotIn('status_transaksi', ['batal', 'selesai'])
        ->count();

    // ✅ Butuh Pickup (JOIN + Filter Status Transaksi)
    $butuhPickup = DB::table('delivery')
        ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
        ->where('delivery.jenis', 'pickup')
        ->whereNull('delivery.id_driver')
        ->where('transaksi.jenis_transaksi', 'online')
        ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
        ->count();

    // ✅ Butuh Antar (JOIN + Filter Status Transaksi)
    $butuhAntar = DB::table('delivery')
        ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
        ->where('delivery.jenis', 'antar')
        ->whereNull('delivery.id_driver')
        ->where('transaksi.jenis_transaksi', 'online')
        ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
        ->count();

    // Harus selesai hari ini (Online + Estimasi Today + Belum Selesai)
    $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
        ->where('jenis_transaksi', 'online')
        ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
        ->count();

    // Terlambat Online (untuk notifikasi, beda dengan $terlambat yang offline)
    $terlambatOnline = Transaksi::where('tgl_estimasi', '<', now()->startOfDay())
        ->where('jenis_transaksi', 'online')
        ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
        ->count();

    // ========================================
    // AMBIL SEMUA TRANSAKSI DENGAN DEADLINE STATUS
    // ========================================
    $orders = Transaksi::query()
        ->orderBy('id_transaksi', 'DESC')
        ->get()
        ->map(function($o) {
            $o->deadline = $o->tgl_estimasi 
                ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                : '-';
            
            if ($o->tgl_estimasi) {
                $estimasi = Carbon::parse($o->tgl_estimasi);
                
                if ($estimasi->isPast() && $o->status_transaksi !== 'selesai') {
                    $o->deadline_status = 'terlambat';
                } elseif ($estimasi->diffInHours(now()) <= 24 && $o->status_transaksi !== 'selesai') {
                    $o->deadline_status = 'mendesak';
                } else {
                    $o->deadline_status = 'normal';
                }
            } else {
                $o->deadline_status = 'no_deadline';
            }
            
            return $o;
        });

    // ========================================
    // RETURN VIEW DENGAN SEMUA VARIABEL
    // ========================================
    return view('admin2.dashboard', compact(
        'admin',
        // Statistik Offline (cards)
        'masuk',
        'harusSelesai',
        'terlambat',
        'totalOmzet',
        // Data
        'orders',
        // Notifikasi Online (notification panel)
        'transaksiMasukHariIni',
        'belumLunas',
        'butuhPickup',
        'butuhAntar',
        'harusSelesaiHariIni',
        'terlambatOnline'
    ));
}

   // =============================
// DASHBOARD KASIR (No Permission Check)
// =============================
public function kasirDashboard()
{
    // ✅ FIX: Use kasir guard instead of admin guard
    $kasir = auth()->guard('kasir')->user();
    
    if (!$kasir) {
        return redirect()->route('login.show')
            ->with('error', 'Silakan login sebagai kasir');
    }

    // ========================================
    // STATISTIK OFFLINE (untuk cards)
    // ========================================
    $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
        ->whereDate('tgl_transaksi', today())
        ->where('status_bayar', 'lunas')
        ->sum('total_bayar');

    $masuk = Transaksi::where('status_transaksi', 'antrian')
        ->where('jenis_transaksi', 'offline')
        ->count();

    $harusSelesai = Transaksi::whereDate('tgl_estimasi', Carbon::today())
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->where('jenis_transaksi', 'offline')
        ->count();

    $terlambat = Transaksi::whereDate('tgl_estimasi', '<', Carbon::today())
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->where('jenis_transaksi', 'offline')
        ->count();

    // ========================================
    // NOTIFIKASI ONLINE (untuk notification panel)
    // ========================================
    
    // Transaksi masuk hari ini (Online + Antrian)
    $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
        ->where('status_transaksi', 'antrian')
        ->where('jenis_transaksi', 'online')
        ->count();

    // Belum lunas (Online + Belum Lunas + BELUM Selesai/Batal)
    $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
        ->where('jenis_transaksi', 'online')
        ->whereNotIn('status_transaksi', ['batal', 'selesai'])
        ->count();

    // ✅ Butuh Pickup (JOIN + Filter Status Transaksi)
    $butuhPickup = DB::table('delivery')
        ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
        ->where('delivery.jenis', 'pickup')
        ->whereNull('delivery.id_driver')
        ->where('transaksi.jenis_transaksi', 'online')
        ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
        ->count();

    // ✅ Butuh Antar (JOIN + Filter Status Transaksi)
    $butuhAntar = DB::table('delivery')
        ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
        ->where('delivery.jenis', 'antar')
        ->whereNull('delivery.id_driver')
        ->where('transaksi.jenis_transaksi', 'online')
        ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
        ->count();

    // Harus selesai hari ini (Online + Estimasi Today + Belum Selesai)
    $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
        ->where('jenis_transaksi', 'online')
        ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
        ->count();

    // Terlambat Online (untuk notifikasi, beda dengan $terlambat yang offline)
    $terlambatOnline = Transaksi::where('tgl_estimasi', '<', now()->startOfDay())
        ->where('jenis_transaksi', 'online')
        ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
        ->count();

    // ========================================
    // AMBIL SEMUA TRANSAKSI DENGAN DEADLINE STATUS
    // ========================================
    $orders = Transaksi::query()
        ->orderBy('id_transaksi', 'DESC')
        ->get()
        ->map(function($o) {
            $o->deadline = $o->tgl_estimasi 
                ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                : '-';
            
            if ($o->tgl_estimasi) {
                $estimasi = Carbon::parse($o->tgl_estimasi);
                
                if ($estimasi->isPast() && $o->status_transaksi !== 'selesai') {
                    $o->deadline_status = 'terlambat';
                } elseif ($estimasi->diffInHours(now()) <= 24 && $o->status_transaksi !== 'selesai') {
                    $o->deadline_status = 'mendesak';
                } else {
                    $o->deadline_status = 'normal';
                }
            } else {
                $o->deadline_status = 'no_deadline';
            }
            
            return $o;
        });

    // ========================================
    // RETURN VIEW DENGAN SEMUA VARIABEL
    // ✅ FIX: Change $admin to $kasir
    // ========================================
    return view('kasir.dashboard', compact(
        'kasir',  // ← CHANGED from 'admin'
        // Statistik Offline (cards)
        'masuk',
        'harusSelesai',
        'terlambat',
        'totalOmzet',
        // Data
        'orders',
        // Notifikasi Online (notification panel)
        'transaksiMasukHariIni',
        'belumLunas',
        'butuhPickup',
        'butuhAntar',
        'harusSelesaiHariIni',
        'terlambatOnline'
    ));
}

    // =============================
    // PELANGGAN - LIST (VIEW)
    // =============================
    public function pelangganIndex()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('pelanggan', 'view');
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('pelanggan.index', compact('pelanggan'));
    }

    public function pelangganIndexKasir()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('pelanggan', 'view');
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('kasir.pelanggan.index', compact('pelanggan'));
    }

    public function pelangganIndexAdmin2()
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('pelanggan', 'view');
        
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('admin2.pelanggan.index', compact('pelanggan'));
    }

    // =============================
    // PELANGGAN - CREATE (ADD)
    // =============================
    public function Create()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pelanggan', 'add');
        
        return view('pelanggan.create');
    }

    public function createKasir()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pelanggan', 'add');
        
        return view('kasir.pelanggan.create');
    }

    public function createAdmin2()
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pelanggan', 'add');
        
        return view('admin2.pelanggan.create');
    }

    // =============================
    // PELANGGAN - STORE (ADD)
    // =============================
    public function store(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pelanggan', 'add');
        
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $gambarPath = 'pelanggan/' . $filename;
        }

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        if ($request->from === 'transaksi') {
            return redirect()
                ->route('transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function storeKasir(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pelanggan', 'add');
        
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $gambarPath = 'pelanggan/' . $filename;
        }

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        if ($request->from === 'transaksi') {
            return redirect()
                ->route('kasir.transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }

        return redirect()
            ->route('kasir.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function storeAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('pelanggan', 'add');
        
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $gambarPath = 'pelanggan/' . $filename;
        }

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        if ($request->from === 'transaksi') {
            return redirect()
                ->route('admin2.transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }

        return redirect()
            ->route('admin2.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // =============================
    // PELANGGAN - EDIT (EDIT)
    // =============================
    public function pelangganEdit($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pelanggan', 'edit');
        
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function pelangganEditKasir($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pelanggan', 'edit');
        
        $pelanggan = Pelanggan::findOrFail($id);
        return view('kasir.pelanggan.edit', compact('pelanggan'));
    }

    public function pelangganEditAdmin2($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pelanggan', 'edit');
        
        $pelanggan = Pelanggan::findOrFail($id);
        return view('admin2.pelanggan.edit', compact('pelanggan'));
    }

    // =============================
    // PELANGGAN - UPDATE (EDIT)
    // =============================
    public function pelangganUpdate(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pelanggan', 'edit');
        
        $pelanggan = Pelanggan::findOrFail($id);

        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('gambar')) {
            if ($pelanggan->gambar && file_exists(public_path('images/' . $pelanggan->gambar))) {
                unlink(public_path('images/' . $pelanggan->gambar));
            }
            
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $pelanggan->gambar = 'pelanggan/' . $filename;
        }

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function pelangganUpdateKasir(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pelanggan', 'edit');
        
        $pelanggan = Pelanggan::findOrFail($id);

        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('gambar')) {
            if ($pelanggan->gambar && file_exists(public_path('images/' . $pelanggan->gambar))) {
                unlink(public_path('images/' . $pelanggan->gambar));
            }
            
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $pelanggan->gambar = 'pelanggan/' . $filename;
        }

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
        ]);

        return redirect()->route('kasir.pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function pelangganUpdateAdmin2(Request $request, $id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('pelanggan', 'edit');
        
        $pelanggan = Pelanggan::findOrFail($id);

        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('gambar')) {
            if ($pelanggan->gambar && file_exists(public_path('images/' . $pelanggan->gambar))) {
                unlink(public_path('images/' . $pelanggan->gambar));
            }
            
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $pelanggan->gambar = 'pelanggan/' . $filename;
        }

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
        ]);

        return redirect()->route('admin2.pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    // =============================
    // PELANGGAN - DELETE (DELETE)
    // =============================
    public function pelangganDestroy($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('pelanggan', 'delete');
        
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

    public function pelangganDestroyKasir($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('pelanggan', 'delete');
        
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return redirect()->route('kasir.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

    public function pelangganDestroyAdmin2($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('pelanggan', 'delete');
        
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return redirect()->route('admin2.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

    // =============================
    // MANAGER INDEX (Super Admin Only - No Permission Check)
    // =============================
    public function managerIndex()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Anda tidak memiliki akses');
        }

        $admins    = Admin::with('role')->orderBy('nama')->get();
        $roles = Role::whereIn('id', [1, 2])->get();
        $menus     = Menu::all();
        $menuRoles = MenuRole::with(['role', 'menu'])
            ->orderBy('role_id')
            ->orderBy('menu_id')
            ->get();

        return view('manager.index', compact(
            'admins',
            'roles',
            'menus',
            'menuRoles'
        ));
    }

    // =============================
    // TAMBAH ADMIN (Super Admin Only - No Permission Check)
    // =============================
    public function storeAdmin(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah admin');
        }

        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin,email',
            'password' => 'required|string|min:6',
            'role_id'  => 'required|in:1,2',
        ]);

        Admin::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => $request->role_id,
            'status'   => 'aktif',
        ]);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Admin berhasil ditambahkan');
    }

    // =============================
    // TAMBAH PELANGGAN DARI MANAGER (Super Admin Only)
    // =============================
    public function storePelanggan(Request $request)
    {
        // ✅ CHECK: Only Super Admin can add pelanggan from manager
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah pelanggan dari manager');
        }
        
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email|unique:pelanggan,email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            if (!file_exists(public_path('images/pelanggan'))) {
                mkdir(public_path('images/pelanggan'), 0755, true);
            }
            
            $file->move(public_path('images/pelanggan'), $filename);
            $gambarPath = 'pelanggan/' . $filename;
        }

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath,
            'password'       => bcrypt($request->password ?? '123456'),
        ]);

        return back()->with('success', 'Pelanggan berhasil ditambahkan');
    }

    // =============================
    // UPDATE PRIVILEGE ROLE (Super Admin Only - No Permission Check)
    // =============================
    public function updateRolePrivilege(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update privilege');
        }

        $request->validate([
            'menu_role_id' => 'required|exists:menu_roles,id',
        ]);

        $menuRole = MenuRole::findOrFail($request->menu_role_id);

        $menuRole->update([
            'can_view'   => $request->has('can_view'),
            'can_add'    => $request->has('can_add'),
            'can_edit'   => $request->has('can_edit'),
            'can_delete' => $request->has('can_delete'),
        ]);

        return back()->with('success', 'Hak akses berhasil diperbarui');
    }
}