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
            
            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            // ✅ REDIRECT BERDASARKAN ROLE
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

            // Login dengan guard kasir
            auth()->guard('kasir')->login($kasir);
            
            // Regenerate session untuk keamanan
            $request->session()->regenerate();
            
            return redirect()->route('kasir.dashboard');
        }

        return back()->with('error', 'Role tidak dikenali');
    }

    // =============================
    // DASHBOARD ADMIN (SUPER ADMIN) - FIXED
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
        $totalOmzet      = Transaksi::where('jenis_transaksi', 'offline')
            ->whereDate('tgl_transaksi', today())
            ->where('status_bayar', 'lunas')
            ->sum('total_bayar');

        // ✅ FIXED: Hapus 'detail.jenis.satuan'
        $orders = Transaksi::query()
            ->where('jenis_transaksi', 'offline')
            ->whereIn('status_transaksi', ['antrian', 'proses', 'siap_di_ambil', 'pick_up'])
            ->orderBy('tgl_transaksi', 'DESC')
            ->limit(10)
            ->get()
            ->map(function($o) {
                // Format deadline
                $o->deadline = $o->tgl_estimasi 
                    ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                    : '-';
                
                // Status deadline
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
            'orders'
        ));
    }

    // =============================
    // DASHBOARD ADMIN2 - FIXED
    // =============================
    public function admin2Dashboard()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id == 1) {
            return redirect()->route('login.show')
                ->with('error', 'Silakan login sebagai admin biasa');
        }

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

        // ✅ FIXED: Hapus 'detail.jenis.satuan'
        $orders = Transaksi::query()
            ->where('jenis_transaksi', 'offline')
            ->whereIn('status_transaksi', ['antrian', 'proses'])
            ->orderBy('tgl_transaksi', 'DESC')
            ->limit(10)
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

        return view('admin2.dashboard', compact(
            'admin',
            'masuk',
            'harusSelesai',
            'terlambat',
            'orders',
            'totalOmzet'
        ));
    }

    // =============================
    // DASHBOARD KASIR - FIXED
    // =============================
    public function kasirDashboard()
    {
        if (!Auth::guard('kasir')->check()) {
            return redirect()->route('login.show')
                ->with('error', 'Silakan login sebagai kasir');
        }

        $kasir = auth()->guard('kasir')->user();
        
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

        // ✅ FIXED: Hapus 'detail.jenis.satuan'
        $orders = Transaksi::query()
            ->where('jenis_transaksi', 'offline')
            ->whereIn('status_transaksi', ['antrian', 'proses'])
            ->orderBy('tgl_transaksi', 'DESC')
            ->limit(10)
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

    public function pelangganIndexAdmin2()
    {
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('admin2.pelanggan.index', compact('pelanggan'));
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
    // TAMBAH ADMIN (SUPER ADMIN)
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
    // UPDATE PRIVILEGE ROLE
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