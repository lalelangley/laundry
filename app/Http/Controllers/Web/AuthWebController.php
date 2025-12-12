<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Kasir;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Hash;

class AuthWebController extends Controller
{
    // Tampilan login
    public function showLogin()
    {
        $admins = Admin::all();
        $kasirs = Kasir::all();

        return view('auth.login', compact('admins', 'kasirs'));
    }

    // Proses login
    public function processLogin(Request $request)
    {
        [$role, $id] = explode('-', $request->user_id);

        if ($role === 'admin') {
            $admin = Admin::find($id);

            if (!$admin || !Hash::check($request->pin, $admin->password)) {
                return back()->with('error', 'PIN salah');
            }

            session(['admin_id' => $admin->id_admin]);
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'kasir') {
            $kasir = Kasir::find($id);

            if (!$kasir || !Hash::check($request->pin, $kasir->password)) {
                return back()->with('error', 'PIN salah');
            }

            session(['kasir_id' => $kasir->id_kasir]);
            return redirect()->route('kasir.dashboard');
        }

        return back()->with('error', 'Role tidak dikenali');
    }

    // Dashboard admin (Sudah FIX)
    public function adminDashboard()
    {
        $admin = Admin::find(session('admin_id'));

        if (!$admin) {
            return redirect()->route('login.show')->with('error', 'Silakan login dulu');
        }

        // Statistik
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
            'orders' // PENTING
        ));
    }

    // Dashboard kasir
    public function kasirDashboard()
    {
        $kasir = Kasir::find(session('kasir_id'));

        if (!$kasir) {
            return redirect()->route('login.show')->with('error', 'Silakan login dulu');
        }

        return view('kasir.dashboard', compact('kasir'));
    }

    // =============================
    // LIST PELANGGAN
    // =============================
    public function pelangganIndex()
    {
        // Ambil semua pelanggan
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();

        // Kirim ke tampilan
        return view('pelanggan.index', compact('pelanggan'));
    }

       public function Create()
    {
        return view('pelanggan.create');
    }

  public function store(Request $request)
{
    $request->validate([
        'nama_pelanggan' => 'required',
        'no_hp' => 'required',
        'email' => 'nullable|email',
        'gender' => 'required',
        'alamat' => 'required',
        'gambar' => 'nullable|image|max:2048'
    ]);

    // Upload gambar
    $path = null;
    if ($request->hasFile('gambar')) {
        $path = $request->file('gambar')->store('pelanggan', 'public');
    }

 Pelanggan::create([
    'nama_pelanggan' => $request->nama_pelanggan,
    'no_hp' => $request->no_hp,
    'alamat' => $request->alamat,
    'gambar' => $path,
    'password' => bcrypt('123456'), // otomatis jadi 123456
]);


    // ==========================
    //  REDIRECT DARI TRANSAKSI
    // ==========================
    if ($request->from === 'transaksi') {
        return redirect()
            ->route('transaksi.pelanggan') // INI YANG BENAR
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // ==========================
    //  REDIRECT NORMAL
    // ==========================
    return redirect()
        ->route('pelanggan.index')
        ->with('success', 'Pelanggan berhasil ditambahkan!');
}



}
