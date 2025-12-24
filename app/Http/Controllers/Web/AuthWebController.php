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


class AuthWebController extends Controller
{
    // =============================
    // LOGIN
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

        // Login dengan guard admin
        auth()->guard('admin')->login($admin);
        
        // PENTING: Simpan ke session juga
        session(['admin_id' => $admin->id_admin]);
        session(['admin_role_id' => $admin->role_id]);
        
        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Redirect sesuai role
        if ($admin->role_id == 1) { // super admin
            return redirect()->route('admin.dashboard');
        } else { // admin biasa
            return redirect()->route('admin2.dashboard');
        }
    }

    if ($role === 'kasir') {
        $kasir = Kasir::find($id);
        if (!$kasir || !Hash::check($pin, $kasir->password) || $kasir->status !== 'aktif') {
            return back()->with('error', 'PIN salah atau user tidak aktif');
        }

        // Login dengan guard kasir
        auth()->guard('kasir')->login($kasir);
        
        // PENTING: Simpan ke session juga
        session(['kasir_id' => $kasir->id_kasir]);
        
        // Regenerate session untuk keamanan
        $request->session()->regenerate();
        
        return redirect()->route('kasir.dashboard');
    }

    return back()->with('error', 'Role tidak dikenali');
}
public function admin2Dashboard()
{
    $admin = auth()->guard('admin')->user();
    if (!$admin || $admin->role_id == 1) {
        return redirect()->route('login.show')->with('error', 'Silakan login sebagai admin biasa');
    }

    $totalPelanggan  = Pelanggan::count();
    $totalKasir      = Kasir::count();
    $totalTransaksi  = Transaksi::count();
    $totalOmzet      = Transaksi::sum('total_bayar');

    $orders = Transaksi::with(['detail.jenis.satuan', 'pelanggan'])
                ->orderBy('id_transaksi', 'DESC')
                ->get();

    return view('admin2.dashboard', compact(
        'admin',
        'totalPelanggan',
        'totalKasir',
        'totalTransaksi',
        'totalOmzet',
        'orders'
    ));
}

    // =============================
    // DASHBOARD ADMIN
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
        $totalTransaksi  = Transaksi::count();
        $totalOmzet      = Transaksi::sum('total_bayar');

        $orders = Transaksi::with(['detail.jenis.satuan', 'pelanggan'])
                    ->orderBy('id_transaksi', 'DESC')
                    ->get();

        return view('admin.dashboard', compact(
            'admin',
            'totalPelanggan',
            'totalKasir',
            'totalTransaksi',
            'totalOmzet',
            'orders'
        ));
    }

    // =============================
    // DASHBOARD KASIR
    // =============================
public function kasirDashboard()
{
    $kasir = auth()->guard('kasir')->user();
    $totalOmzet      = Transaksi::sum('total_bayar');

   if (!Auth::guard('kasir')->check()) {
    abort(403, 'Kasir belum login');
}



    // =============================
    // CARD DASHBOARD
    // =============================

    // MASUK (antrian)
    $masuk = Transaksi::where('status_transaksi', 'antrian')->count();

    // HARUS SELESAI HARI INI
    $harusSelesai = Transaksi::whereDate('tgl_estimasi', Carbon::today())
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->count();

    // TERLAMBAT
    $terlambat = Transaksi::whereDate('tgl_estimasi', '<', Carbon::today())
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->count();

    // =============================
    // DATA TABLE (ORDER AKTIF)
    // =============================
    $orders = Transaksi::with(['detail.jenis.satuan', 'pelanggan'])
        ->whereIn('status_transaksi', ['antrian', 'proses'])
        ->orderBy('tgl_transaksi', 'DESC')
        ->get();

    return view('kasir.dashboard', compact(
        'kasir',
        'masuk',
        'harusSelesai',
        'terlambat',
        'orders',
        'totalOmzet'
    ));
}


    // =============================
    // LIST PELANGGAN
    // =============================
    public function pelangganIndex()
    {
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('pelanggan.index', compact('pelanggan'));
    }

    // =============================
    // CREATE PELANGGAN
    // =============================
    public function Create()
    {
        return view('pelanggan.create');
    }

    // =============================
    // STORE PELANGGAN
    // =============================
    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email',
            'jk'             => 'required',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|max:2048'
        ]);

        $path = $request->hasFile('gambar') 
            ? $request->file('gambar')->store('pelanggan', 'public') 
            : null;

        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $path,
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

    // =============================
    // EDIT PELANGGAN
    // =============================
    public function pelangganEdit($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    // =============================
    // UPDATE PELANGGAN
    // =============================
    public function pelangganUpdate(Request $request, $id)
    {
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
            $pelanggan->gambar = $request->file('gambar')->store('pelanggan', 'public');
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

    // =============================
    // DELETE PELANGGAN
    // =============================
    public function pelangganDestroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

// =============================
// MANAGER INDEX (Super Admin Only)
// =============================
public function managerIndex()
{
    $admin = auth()->guard('admin')->user();
    if (!$admin || $admin->role_id != 1) {
        abort(403);
    }
    if ($admin->role_id != 1) {
        abort(403, 'Anda tidak memiliki akses');
    }

    $admins    = Admin::with('role')->orderBy('nama')->get();
    $roles     = Role::all();
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
// TAMBAH ADMIN (SUPER ADMIN)
// =============================
public function storeAdmin(Request $request)
{
    
    $admin = Admin::find(session('admin_id'));
    if ($admin->role_id != 1) abort(403);

     $request->validate([
        'nama'     => 'required|string|max:255',
        'email'    => 'required|email|unique:admin,email',
        'password' => 'required|string|min:6',
        'role_id'  => 'required|in:1,2', // ✅ FIX
    ]);

    
    Admin::create([
        'nama'     => $request->nama,
        'email'    => $request->email,
        'password' => bcrypt($request->password),
        'role_id'  => $request->role_id,
        'status'   => 'aktif', // (opsional tapi direkomendasikan)
    ]);

     return redirect()
        ->route('manager.index')
        ->with('success', 'Admin berhasil ditambahkan');
}


// =============================
// TAMBAH PELANGGAN DARI MANAGER
// =============================
public function storePelanggan(Request $request)
{
    $request->validate([
        'nama_pelanggan' => 'required|string|max:255',
        'no_hp'          => 'required|string|max:20',
        'email'          => 'nullable|email|unique:pelanggan,email',
        'jk'             => 'required',
        'alamat'         => 'required|string',
    ]);

    Pelanggan::create([
        'nama_pelanggan' => $request->nama_pelanggan,
        'no_hp'          => $request->no_hp,
        'alamat'         => $request->alamat,
        'email'          => $request->email,
        'jk'             => $request->jk,
        'password'       => bcrypt($request->password ?? '123456'),
    ]);

    return back()->with('success', 'Pelanggan berhasil ditambahkan');
}


// =============================
// UPDATE PRIVILEGE ROLE
// =============================
public function updateRolePrivilege(Request $request)
{
    $admin = Admin::find(session('admin_id'));
    if ($admin->role_id != 1) abort(403);

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
