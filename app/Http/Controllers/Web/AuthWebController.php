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

/**
 * ============================================================
 * CLASS: AuthWebController
 * Syarat: Terdapat kode program dengan konsep class-object
 * ============================================================
 * Controller utama untuk menangani autentikasi dan manajemen
 * data pada aplikasi KasminiLaundry.
 * Mencakup: login, dashboard, pelanggan, manager, privilege.
 * ============================================================
 */
class AuthWebController extends Controller
{
    // ============================================================
    // METHOD: landingPage
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // ============================================================
    // Menampilkan halaman landing page publik dengan statistik
    // ringkasan transaksi dan pelanggan terdaftar.
    // Tidak memerlukan autentikasi (akses publik).
    // ============================================================
    public function landingPage()
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses percabangan
        // Syarat: Data pada basis data dapat diakses sesuai kebutuhan
        // ------------------------------------------------------------
        // Hitung jumlah pesanan yang masuk hari ini
        $pesanan_hari_ini = Transaksi::whereDate('tgl_transaksi', Carbon::today())
            ->count();

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array status transaksi yang sedang dalam proses
        // ------------------------------------------------------------
        $statusDalamProses = ['antrian', 'proses'];
        $dalam_proses = Transaksi::whereIn('status_transaksi', $statusDalamProses)
            ->count();

        // Array status transaksi yang sudah selesai atau siap diambil/diantar
        $statusSelesai = ['siap_di_ambil', 'siap_di_antar', 'selesai'];
        $selesai = Transaksi::whereIn('status_transaksi', $statusSelesai)
            ->count();

        // Hitung total seluruh pelanggan terdaftar
        $total_pelanggan = Pelanggan::count();

        // Hitung total transaksi dalam bulan dan tahun berjalan
        $transaksi_bulan_ini = Transaksi::whereYear('tgl_transaksi', Carbon::now()->year)
            ->whereMonth('tgl_transaksi', Carbon::now()->month)
            ->count();

        // Hitung pendapatan bulan ini, hanya dari transaksi yang sudah lunas
        $pendapatan_bulan_ini = Transaksi::whereYear('tgl_transaksi', Carbon::now()->year)
            ->whereMonth('tgl_transaksi', Carbon::now()->month)
            ->where('status_bayar', 'lunas')
            ->sum('total_bayar');

        return view('auth.landing-page', compact(
            'pesanan_hari_ini',
            'dalam_proses',
            'selesai',
            'total_pelanggan',
            'transaksi_bulan_ini',
            'pendapatan_bulan_ini'
        ));
    }

    // ============================================================
    // METHOD: showLogin
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // ============================================================
    // Menampilkan halaman login.
    // Jika user sudah login, langsung redirect ke dashboard
    // sesuai role masing-masing (percabangan role).
    // ============================================================
    public function showLogin()
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses percabangan
        // Syarat: Validasi akses setiap role berfungsi dengan baik
        // ------------------------------------------------------------
        // Cek apakah sudah login sebagai admin
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            // Percabangan: arahkan ke dashboard sesuai role_id admin
            return redirect()->route($admin->role_id == 1 ? 'admin.dashboard' : 'admin2.dashboard');
        }

        // Cek apakah sudah login sebagai kasir
        if (Auth::guard('kasir')->check()) {
            return redirect()->route('kasir.dashboard');
        }

        return view('auth.login');
    }

    // ============================================================
    // METHOD: processLogin
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Terdapat penanganan error/galat pada kode program
    // Syarat: Validasi form sesuai kebutuhan
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================
    // Memproses login berdasarkan role (admin/kasir).
    // Admin menggunakan email + password,
    // Kasir menggunakan no_hp + password.
    // ============================================================
    public function processLogin(Request $request)
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses percabangan
        // Ambil login_type dari form (admin/kasir)
        // ------------------------------------------------------------
        $type = $request->login_type;

        // ------------------------------------------------------------
        // Syarat: Terdapat penanganan error/galat pada kode program
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array role yang diizinkan login ke sistem
        // ------------------------------------------------------------
        $roleYangDiizinkan = ['admin', 'kasir'];
        if (!in_array($type, $roleYangDiizinkan)) {
            return back()->with('error', 'Role pengguna tidak valid.');
        }

        if ($type === 'admin') {
            // ------------------------------------------------------------
            // Syarat: Validasi form sesuai kebutuhan
            // Syarat: Terdapat penanganan error/galat pada kode program
            // Validasi input server-side untuk login admin
            // ------------------------------------------------------------
            $request->validate([
                'email'          => 'required|email',
                'password_admin' => 'required|string',
            ], [
                'email.required'          => 'Email wajib diisi.',
                'email.email'             => 'Format email tidak valid.',
                'password_admin.required' => 'Password wajib diisi.',
            ]);

            // Cari admin berdasarkan email di database
            $admin = Admin::where('email', $request->email)->first();

            // ------------------------------------------------------------
            // Syarat: Terdapat penanganan error/galat pada kode program
            // Syarat: Terdapat kode program untuk proses percabangan
            // Cek: admin ditemukan dan password cocok (Hash::check = case-sensitive)
            // ------------------------------------------------------------
            if (!$admin || !Hash::check($request->password_admin, $admin->password)) {
                return back()->with('error', 'Email atau password salah.')
                    ->withInput(['email' => $request->email, 'login_type' => $type]);
            }

            // Percabangan: cek status aktif admin sebelum diizinkan masuk
            if ($admin->status !== 'aktif') {
                return back()->with('error', 'Akun Anda tidak aktif. Hubungi super admin.')
                    ->withInput(['login_type' => $type]);
            }

            // Login admin dan regenerate session untuk keamanan
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();

            // Percabangan: arahkan ke dashboard sesuai role_id
            $dashboardRoute = $admin->role_id == 1 ? 'admin.dashboard' : 'admin2.dashboard';
            return redirect()->route($dashboardRoute)
                ->with('success', 'Selamat datang, ' . $admin->nama . '!');
        }

        if ($type === 'kasir') {
            // ------------------------------------------------------------
            // Syarat: Validasi form sesuai kebutuhan
            // Syarat: Terdapat penanganan error/galat pada kode program
            // Validasi input server-side untuk login kasir
            // ------------------------------------------------------------
            $request->validate([
                'no_hp'          => 'required|string',
                'password_kasir' => 'required|string',
            ], [
                'no_hp.required'          => 'No. HP wajib diisi.',
                'password_kasir.required' => 'Password wajib diisi.',
            ]);

            // Cari kasir berdasarkan no_hp di database
            $kasir = Kasir::where('no_hp', $request->no_hp)->first();

            // ------------------------------------------------------------
            // Syarat: Terdapat penanganan error/galat pada kode program
            // Cek: kasir ditemukan dan password cocok
            // ------------------------------------------------------------
            if (!$kasir || !Hash::check($request->password_kasir, $kasir->password)) {
                return back()->with('error', 'No. HP atau password salah.')
                    ->withInput(['no_hp' => $request->no_hp, 'login_type' => $type]);
            }

            // Percabangan: cek status aktif kasir sebelum diizinkan masuk
            if ($kasir->status !== 'aktif') {
                return back()->with('error', 'Akun Anda tidak aktif. Hubungi admin.')
                    ->withInput(['login_type' => $type]);
            }

            // Login kasir dan regenerate session untuk keamanan
            Auth::guard('kasir')->login($kasir);
            $request->session()->regenerate();

            return redirect()->route('kasir.dashboard')
                ->with('success', 'Selamat datang, ' . $kasir->nama_kasir . '!');
        }

        // ------------------------------------------------------------
        // Syarat: Terdapat penanganan error/galat pada kode program
        // Fallback jika role tidak dikenali
        // ------------------------------------------------------------
        return back()->with('error', 'Role tidak dikenali.');
    }

    // ============================================================
    // METHOD: adminDashboard
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================
    // Dashboard khusus Super Admin (role_id = 1).
    // Menampilkan statistik transaksi, notifikasi, dan daftar order.
    // ============================================================
    public function adminDashboard()
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat penanganan error/galat pada kode program
        // Syarat: Validasi akses setiap role berfungsi dengan baik
        // ------------------------------------------------------------
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return redirect()->route('login')->with('error', 'Silakan login dulu');
        }

        // Statistik utama dashboard
        $totalPelanggan = Pelanggan::count();
        $totalKasir     = Kasir::count();
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'offline')->count();

        // Total omzet hari ini dari semua transaksi yang lunas
        $totalOmzet = Transaksi::where('status_bayar', 'lunas')
            ->whereDate('tgl_lunas', today())
            ->sum('total_bayar');

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array status untuk filter notifikasi transaksi
        // ------------------------------------------------------------
        $statusBelumSelesai = ['antrian', 'proses', 'selesai_dicuci'];
        $statusDibatalkan   = ['batal', 'selesai'];

        // Jumlah transaksi online yang masuk hari ini dengan status antrian
        $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
            ->where('status_transaksi', 'antrian')
            ->where('jenis_transaksi', 'online')
            ->count();

        // Jumlah transaksi online yang sudah lunas hari ini
        $pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
            ->where('status_bayar', 'lunas')
            ->where('jenis_transaksi', 'online')
            ->count();

        // Jumlah transaksi online yang belum lunas dan belum batal/selesai
        $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
            ->where('jenis_transaksi', 'online')
            ->whereNotIn('status_transaksi', $statusDibatalkan)
            ->count();

        // Jumlah transaksi online yang butuh driver pickup tapi belum ada driver
        $butuhPickup = DB::table('delivery')
            ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('delivery.jenis', 'pickup')
            ->whereNull('delivery.id_driver')
            ->where('transaksi.jenis_transaksi', 'online')
            ->whereNotIn('transaksi.status_transaksi', $statusDibatalkan)
            ->count();

        // Jumlah delivery antar yang belum ada driver
        $butuhAntar = DB::table('delivery')
            ->where('jenis', 'antar')
            ->whereNull('id_driver')
            ->count();

        // Jumlah transaksi online yang estimasi selesai hari ini tapi belum selesai
        $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusBelumSelesai)
            ->count();

        // Jumlah transaksi online yang sudah melewati estimasi tapi belum selesai
        $terlambat = Transaksi::where('tgl_estimasi', '<', now())
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusBelumSelesai)
            ->count();

        // Jumlah transaksi online yang siap diambil pelanggan
        $siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
            ->where('jenis_transaksi', 'online')
            ->count();

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses perulangan
        // Syarat: Terdapat kode program untuk proses percabangan
        // Perulangan: iterasi setiap transaksi untuk menentukan
        // status deadline (terlambat / mendesak / normal)
        // ------------------------------------------------------------
        $orders = Transaksi::query()
            ->orderBy('id_transaksi', 'DESC')
            ->get()
            ->map(function ($o) {
                // Format tanggal estimasi untuk tampilan
                $o->deadline = $o->tgl_estimasi
                    ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                    : '-';

                // Percabangan: tentukan status deadline setiap transaksi
                if ($o->tgl_estimasi) {
                    $estimasi = Carbon::parse($o->tgl_estimasi);

                    if ($estimasi->isPast() && $o->status_transaksi !== 'selesai') {
                        // Transaksi melewati estimasi dan belum selesai
                        $o->deadline_status = 'terlambat';
                    } elseif ($estimasi->diffInHours(now()) <= 24 && $o->status_transaksi !== 'selesai') {
                        // Transaksi mendekati deadline dalam 24 jam
                        $o->deadline_status = 'mendesak';
                    } else {
                        // Transaksi masih dalam batas waktu normal
                        $o->deadline_status = 'normal';
                    }
                } else {
                    // Transaksi tidak memiliki estimasi waktu
                    $o->deadline_status = 'no_deadline';
                }

                return $o;
            });

        return view('admin.dashboard', compact(
            'admin', 'totalPelanggan', 'totalKasir', 'totalTransaksi', 'totalOmzet', 'orders',
            'transaksiMasukHariIni', 'pembayaranLunasHariIni', 'belumLunas',
            'butuhPickup', 'butuhAntar', 'harusSelesaiHariIni', 'terlambat', 'siapDiambil'
        ));
    }

    // ============================================================
    // METHOD: admin2Dashboard
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================
    // Dashboard khusus Admin Biasa (role_id = 2).
    // Statistik difokuskan pada transaksi offline.
    // ============================================================
    public function admin2Dashboard()
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat penanganan error/galat pada kode program
        // Syarat: Validasi akses setiap role berfungsi dengan baik
        // ------------------------------------------------------------
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return redirect()->route('login')->with('error', 'Silakan login dulu');
        }

        $totalPelanggan = Pelanggan::count();
        $totalKasir     = Kasir::count();
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'offline')->count();

        // Total omzet offline hari ini yang sudah lunas
        $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
            ->whereDate('tgl_transaksi', today())
            ->where('status_bayar', 'lunas')
            ->sum('total_bayar');

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array status digunakan ulang untuk konsistensi filter
        // ------------------------------------------------------------
        $statusBelumSelesai = ['antrian', 'proses', 'selesai_dicuci'];
        $statusDibatalkan   = ['batal', 'selesai'];

        $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
            ->where('status_transaksi', 'antrian')
            ->where('jenis_transaksi', 'online')
            ->count();

        $pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
            ->where('status_bayar', 'lunas')
            ->where('jenis_transaksi', 'online')
            ->count();

        $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
            ->where('jenis_transaksi', 'online')
            ->whereNotIn('status_transaksi', $statusDibatalkan)
            ->count();

        $butuhPickup = DB::table('delivery')
            ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('delivery.jenis', 'pickup')
            ->whereNull('delivery.id_driver')
            ->where('transaksi.jenis_transaksi', 'online')
            ->whereNotIn('transaksi.status_transaksi', $statusDibatalkan)
            ->count();

        $butuhAntar = DB::table('delivery')
            ->where('jenis', 'antar')
            ->whereNull('id_driver')
            ->count();

        $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusBelumSelesai)
            ->count();

        $terlambat = Transaksi::where('tgl_estimasi', '<', now())
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusBelumSelesai)
            ->count();

        $siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
            ->where('jenis_transaksi', 'online')
            ->count();

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses perulangan
        // Syarat: Terdapat kode program untuk proses percabangan
        // Perulangan map: iterasi tiap transaksi untuk set deadline_status
        // ------------------------------------------------------------
        $orders = Transaksi::query()
            ->orderBy('id_transaksi', 'DESC')
            ->get()
            ->map(function ($o) {
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
            'admin', 'totalPelanggan', 'totalKasir', 'totalTransaksi', 'totalOmzet', 'orders',
            'transaksiMasukHariIni', 'pembayaranLunasHariIni', 'belumLunas',
            'butuhPickup', 'butuhAntar', 'harusSelesaiHariIni', 'terlambat', 'siapDiambil'
        ));
    }

    // ============================================================
    // METHOD: kasirDashboard
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================
    // Dashboard khusus Kasir.
    // Statistik difokuskan pada transaksi offline kasir.
    // ============================================================
    public function kasirDashboard()
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat penanganan error/galat pada kode program
        // Syarat: Validasi akses setiap role berfungsi dengan baik
        // ------------------------------------------------------------
        $kasir = auth()->guard('kasir')->user();
        if (!$kasir) {
            return redirect()->route('login')->with('error', 'Silakan login dulu');
        }

        $totalPelanggan = Pelanggan::count();
        $totalKasir     = Kasir::count();
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'offline')->count();

        $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
            ->whereDate('tgl_transaksi', today())
            ->where('status_bayar', 'lunas')
            ->sum('total_bayar');

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // ------------------------------------------------------------
        $statusBelumSelesai = ['antrian', 'proses', 'selesai_dicuci'];
        $statusDibatalkan   = ['batal', 'selesai'];

        $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
            ->where('status_transaksi', 'antrian')
            ->where('jenis_transaksi', 'online')
            ->count();

        $pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
            ->where('status_bayar', 'lunas')
            ->where('jenis_transaksi', 'online')
            ->count();

        $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
            ->where('jenis_transaksi', 'online')
            ->whereNotIn('status_transaksi', $statusDibatalkan)
            ->count();

        $butuhPickup = DB::table('delivery')
            ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('delivery.jenis', 'pickup')
            ->whereNull('delivery.id_driver')
            ->where('transaksi.jenis_transaksi', 'online')
            ->whereNotIn('transaksi.status_transaksi', $statusDibatalkan)
            ->count();

        $butuhAntar = DB::table('delivery')
            ->where('jenis', 'antar')
            ->whereNull('id_driver')
            ->count();

        $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusBelumSelesai)
            ->count();

        $terlambat = Transaksi::where('tgl_estimasi', '<', now())
            ->where('jenis_transaksi', 'online')
            ->whereIn('status_transaksi', $statusBelumSelesai)
            ->count();

        $siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
            ->where('jenis_transaksi', 'online')
            ->count();

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses perulangan
        // Syarat: Terdapat kode program untuk proses percabangan
        // ------------------------------------------------------------
        $orders = Transaksi::query()
            ->orderBy('id_transaksi', 'DESC')
            ->get()
            ->map(function ($o) {
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
            'kasir', 'totalPelanggan', 'totalKasir', 'totalTransaksi', 'totalOmzet', 'orders',
            'transaksiMasukHariIni', 'pembayaranLunasHariIni', 'belumLunas',
            'butuhPickup', 'butuhAntar', 'harusSelesaiHariIni', 'terlambat', 'siapDiambil'
        ));
    }

    // ============================================================
    // PELANGGAN - LIST (VIEW)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================

    // Halaman daftar pelanggan untuk Super Admin
    public function pelangganIndex()
    {
        // Cek permission view pelanggan
        requirePermission('pelanggan', 'view');
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')
            ->paginate(10)
            ->withQueryString();
        return view('pelanggan.index', compact('pelanggan'));
    }

    // Halaman daftar pelanggan untuk Kasir
    public function pelangganIndexKasir()
    {
        // Cek permission view pelanggan
        requirePermission('pelanggan', 'view');
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')
            ->paginate(10)
            ->withQueryString();
        return view('kasir.pelanggan.index', compact('pelanggan'));
    }

    // Halaman daftar pelanggan untuk Admin Biasa
    public function pelangganIndexAdmin2()
    {
        // Cek permission view pelanggan
        requirePermission('pelanggan', 'view');
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')
            ->paginate(10)
            ->withQueryString();
        return view('admin2.pelanggan.index', compact('pelanggan'));
    }

    // ============================================================
    // PELANGGAN - CREATE (FORM TAMBAH)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================

    // Form tambah pelanggan untuk Super Admin
    public function Create()
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');
        return view('pelanggan.create');
    }

    // Form tambah pelanggan untuk Kasir
    public function createKasir(Request $request)
    {
        if ($request->get('from') === 'transaksi') {
            requirePermission('transaksi', 'view');
        } else {
            requirePermission('pelanggan', 'add');
        }
        return view('kasir.pelanggan.create');
    }

    // Form tambah pelanggan untuk Admin Biasa
    public function createAdmin2(Request $request)
    {
        if ($request->get('from') === 'transaksi') {
            requirePermission('transaksi', 'view');
        } else {
            requirePermission('pelanggan', 'add');
        }
        return view('admin2.pelanggan.create');
    }

    // ============================================================
    // METHOD: validatePelanggan (Private Helper)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi form sesuai kebutuhan
    // Syarat: Terdapat penanganan error/galat pada kode program
    // ============================================================
    // Helper method untuk validasi form pelanggan.
    // Digunakan oleh store, storeKasir, storeAdmin2,
    // pelangganUpdate, pelangganUpdateKasir, pelangganUpdateAdmin2.
    // TC-09: Nama wajib diisi
    // TC-10: No HP wajib diisi
    // TC-11: No HP hanya angka, 10-15 digit
    // TC-12: Format email harus valid
    // ============================================================
    private function validatePelanggan(Request $request, ?int $ignoreId = null): void
    {
        $noHpRule = 'required|string|min:10|max:15|regex:/^[0-9]+$/|unique:pelanggan,no_hp';
        $emailRule = 'nullable|email|unique:pelanggan,email';

        if ($ignoreId) {
            $noHpRule .= ',' . $ignoreId . ',id_pelanggan';
            $emailRule .= ',' . $ignoreId . ',id_pelanggan';
        }

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array rules validasi form pelanggan
        // ------------------------------------------------------------
        $rules = [
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => $noHpRule,
            'email'          => $emailRule,
            'jk'             => 'required|in:L,P',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        // Array pesan error kustom sesuai test case
        $messages = [
            'nama_pelanggan.required' => 'Nama Pelanggan tidak boleh kosong.',       // TC-09
            'nama_pelanggan.max'      => 'Nama Pelanggan maksimal 255 karakter.',
            'no_hp.required'          => 'No Handphone tidak boleh kosong.',          // TC-10
            'no_hp.min'               => 'No Handphone minimal 10 digit.',            // TC-11
            'no_hp.max'               => 'No Handphone maksimal 15 digit.',           // TC-11
            'no_hp.regex'             => 'No Handphone hanya boleh berisi angka.',    // TC-11
            'no_hp.unique'            => 'No Handphone sudah digunakan pelanggan lain.',
            'email.email'             => 'Format Email tidak valid.',                  // TC-12
            'email.unique'            => 'Email sudah digunakan pelanggan lain.',
            'jk.required'             => 'Jenis Kelamin wajib dipilih.',
            'alamat.required'         => 'Alamat tidak boleh kosong.',
            'gambar.image'            => 'File harus berupa gambar.',
            'gambar.mimes'            => 'Format gambar harus jpg, jpeg, atau png.',
            'gambar.max'              => 'Ukuran gambar maksimal 2MB.',
        ];

        $request->validate($rules, $messages);
    }

    // ============================================================
    // METHOD: uploadGambar (Private Helper)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Terdapat kode program untuk proses percabangan
    // ============================================================
    // Helper method untuk proses upload gambar pelanggan.
    // Mengembalikan path gambar baru, atau path lama jika
    // tidak ada file baru yang diupload.
    // ============================================================
    private function uploadGambar(Request $request, ?string $gambarLama = null): ?string
    {
        // Percabangan: jika tidak ada file baru, kembalikan gambar lama
        if (!$request->hasFile('gambar')) {
            return $gambarLama;
        }

        // Hapus gambar lama dari storage jika ada (kasus update)
        if ($gambarLama && file_exists(public_path('images/' . $gambarLama))) {
            unlink(public_path('images/' . $gambarLama));
        }

        $file     = $request->file('gambar');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Buat direktori jika belum ada
        if (!file_exists(public_path('images/pelanggan'))) {
            mkdir(public_path('images/pelanggan'), 0755, true);
        }

        $file->move(public_path('images/pelanggan'), $filename);

        return 'pelanggan/' . $filename;
    }

    // ============================================================
    // PELANGGAN - STORE (SIMPAN DATA BARU)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================

    // Simpan pelanggan baru oleh Super Admin
    public function store(Request $request)
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request, $pelanggan->id_pelanggan);

        // Proses upload gambar jika ada
        $gambarPath = $this->uploadGambar($request);

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array data pelanggan baru yang akan disimpan ke database
        // ------------------------------------------------------------
        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        // Percabangan: redirect ke transaksi atau index pelanggan
        if ($request->from === 'transaksi') {
            return redirect()->route('transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }
        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // Simpan pelanggan baru oleh Kasir
    public function storeKasir(Request $request)
    {
        if ($request->input('from') === 'transaksi') {
            requirePermission('transaksi', 'view');
        } else {
            requirePermission('pelanggan', 'add');
        }
        $this->validatePelanggan($request);
        $gambarPath = $this->uploadGambar($request);

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
            return redirect()->route('kasir.transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }
        return redirect()->route('kasir.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // Simpan pelanggan baru oleh Admin Biasa
    public function storeAdmin2(Request $request)
    {
        if ($request->input('from') === 'transaksi') {
            requirePermission('transaksi', 'view');
        } else {
            requirePermission('pelanggan', 'add');
        }
        $this->validatePelanggan($request);
        $gambarPath = $this->uploadGambar($request);

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
            return redirect()->route('admin2.transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }
        return redirect()->route('admin2.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // ============================================================
    // PELANGGAN - EDIT (FORM EDIT)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // Syarat: Terdapat penanganan error/galat pada kode program
    // ============================================================
    // findOrFail otomatis melempar 404 jika data tidak ditemukan.
    // ============================================================

    public function pelangganEdit($id)
    {
        requirePermission('pelanggan', 'edit');
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function pelangganEditKasir($id)
    {
        requirePermission('pelanggan', 'edit');
        $pelanggan = Pelanggan::findOrFail($id);
        return view('kasir.pelanggan.edit', compact('pelanggan'));
    }

    public function pelangganEditAdmin2($id)
    {
        requirePermission('pelanggan', 'edit');
        $pelanggan = Pelanggan::findOrFail($id);
        return view('admin2.pelanggan.edit', compact('pelanggan'));
    }

    // ============================================================
    // PELANGGAN - UPDATE (SIMPAN PERUBAHAN)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi form sesuai kebutuhan
    // Syarat: Terdapat penanganan error/galat pada kode program
    // ============================================================
    // Menyimpan perubahan data pelanggan ke database.
    // Gambar lama dihapus otomatis jika ada gambar baru.
    // ============================================================

    public function pelangganUpdate(Request $request, $id)
    {
        requirePermission('pelanggan', 'edit');
        $pelanggan  = Pelanggan::findOrFail($id);
        $this->validatePelanggan($request, $pelanggan->id_pelanggan);
        $gambarPath = $this->uploadGambar($request, $pelanggan->gambar);

        // Array data yang akan diupdate ke database
        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath,
        ]);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function pelangganUpdateKasir(Request $request, $id)
    {
        requirePermission('pelanggan', 'edit');
        $pelanggan  = Pelanggan::findOrFail($id);
        $this->validatePelanggan($request, $pelanggan->id_pelanggan);
        $gambarPath = $this->uploadGambar($request, $pelanggan->gambar);

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath,
        ]);

        return redirect()->route('kasir.pelanggan.index')
            ->with('success', 'Pelanggan berhasil diupdate!');
    }

    public function pelangganUpdateAdmin2(Request $request, $id)
    {
        requirePermission('pelanggan', 'edit');
        $pelanggan  = Pelanggan::findOrFail($id);
        $this->validatePelanggan($request, $pelanggan->id_pelanggan);
        $gambarPath = $this->uploadGambar($request, $pelanggan->gambar);

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath,
        ]);

        return redirect()->route('admin2.pelanggan.index')
            ->with('success', 'Pelanggan berhasil diupdate!');
    }

    // ============================================================
    // PELANGGAN - DELETE (HAPUS DATA)
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // Syarat: Terdapat penanganan error/galat pada kode program
    // ============================================================

    public function pelangganDestroy($id)
    {
        requirePermission('pelanggan', 'delete');
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();
        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus!');
    }

    public function pelangganDestroyKasir($id)
    {
        requirePermission('pelanggan', 'delete');
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();
        return redirect()->route('kasir.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus!');
    }

    public function pelangganDestroyAdmin2($id)
    {
        requirePermission('pelanggan', 'delete');
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();
        return redirect()->route('admin2.pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus!');
    }

    // ============================================================
    // METHOD: managerIndex
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // Syarat: Terdapat penanganan error/galat pada kode program
    // ============================================================
    // Halaman manager khusus Super Admin (role_id = 1).
    // Menampilkan daftar admin, role, menu, dan hak akses.
    // ============================================================
    public function managerIndex()
    {
        // ------------------------------------------------------------
        // Syarat: Terdapat kode program untuk proses percabangan
        // Syarat: Validasi akses setiap role berfungsi dengan baik
        // Hanya Super Admin (role_id = 1) yang bisa akses halaman ini
        // ------------------------------------------------------------
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Anda tidak memiliki akses');
        }

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array role_id yang ditampilkan di halaman manager
        // ------------------------------------------------------------
        $roleYangDitampilkan = [1, 2];

        $admins    = Admin::with('role')->orderBy('nama')->get();
        $roles     = Role::whereIn('id', $roleYangDitampilkan)->get();
        $menus     = Menu::all();
        $menuRoles = MenuRole::with(['role', 'menu'])
            ->orderBy('role_id')
            ->orderBy('menu_id')
            ->get();

        return view('manager.index', compact('admins', 'roles', 'menus', 'menuRoles'));
    }

    // ============================================================
    // METHOD: storeAdmin
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi form sesuai kebutuhan
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================
    // Menyimpan data admin baru oleh Super Admin.
    // ============================================================
    public function storeAdmin(Request $request)
    {
        // Hanya Super Admin yang bisa menambah admin baru
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah admin');
        }

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Syarat: Validasi form sesuai kebutuhan
        // Array rules validasi untuk tambah admin baru
        // ------------------------------------------------------------
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin,email',
            'password' => 'required|string|min:6',
            'role_id'  => 'required|in:1,2',
        ]);

        // Array data admin baru yang akan disimpan ke database
        Admin::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => $request->role_id,
            'status'   => 'aktif',
        ]);

        return redirect()->route('manager.index')
            ->with('success', 'Admin berhasil ditambahkan');
    }

    // ============================================================
    // METHOD: storePelanggan
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi form sesuai kebutuhan
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // ============================================================
    // Menyimpan pelanggan baru dari halaman manager oleh Super Admin.
    // ============================================================
    public function storePelanggan(Request $request)
    {
        // Hanya Super Admin yang bisa menambah pelanggan dari halaman manager
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah pelanggan dari manager');
        }

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Syarat: Validasi form sesuai kebutuhan
        // Array rules validasi dengan tambahan unique email
        // ------------------------------------------------------------
        $this->validatePelanggan($request);

        // Proses upload gambar jika ada
        $gambarPath = $this->uploadGambar($request);

        // Array data pelanggan baru yang akan disimpan ke database
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

    // ============================================================
    // METHOD: updateRolePrivilege
    // Syarat: Terdapat kode program yang mengidentifikasi method
    // Syarat: Validasi akses setiap role berfungsi dengan baik
    // Syarat: Terdapat penanganan error/galat pada kode program
    // ============================================================
    // Mengupdate hak akses (privilege) per role oleh Super Admin.
    // ============================================================
    public function updateRolePrivilege(Request $request)
    {
        // Hanya Super Admin yang bisa mengubah hak akses role
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update privilege');
        }

        // ------------------------------------------------------------
        // Syarat: Terdapat penanganan error/galat pada kode program
        // Validasi: menu_role_id harus ada di tabel menu_roles
        // ------------------------------------------------------------
        $request->validate([
            'menu_role_id' => 'required|exists:menu_roles,id',
        ]);

        $menuRole = MenuRole::findOrFail($request->menu_role_id);

        // ------------------------------------------------------------
        // Syarat: Terdapat kode program yang mengidentifikasi data array
        // Array data privilege yang diupdate berdasarkan checkbox form
        // ------------------------------------------------------------
        $menuRole->update([
            'can_view'   => $request->has('can_view'),
            'can_add'    => $request->has('can_add'),
            'can_edit'   => $request->has('can_edit'),
            'can_delete' => $request->has('can_delete'),
        ]);

        return back()->with('success', 'Hak akses berhasil diperbarui');
    }
}
