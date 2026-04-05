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
    // LANDING PAGE (No Permission Check - Public)
    // =============================
    public function landingPage()
    {
        // Hitung jumlah pesanan yang masuk hari ini
        $pesanan_hari_ini = Transaksi::whereDate('tgl_transaksi', Carbon::today())
            ->count();

        // Hitung transaksi yang sedang dalam proses (antrian atau sedang diproses)
        $dalam_proses = Transaksi::whereIn('status_transaksi', ['antrian', 'proses'])
            ->count();

        // Hitung transaksi yang sudah selesai atau siap diambil/diantar
        $selesai = Transaksi::whereIn('status_transaksi', ['siap_di_ambil', 'siap_di_antar', 'selesai'])
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

    // =============================
    // LOGIN (No Permission Check)
    // =============================

    /**
     * Tampilkan halaman login.
     * Jika user sudah login, langsung redirect ke dashboard sesuai role.
     */
   public function showLogin()
{
    // Jika sudah login sebagai admin, redirect ke dashboard sesuai role_id
    if (Auth::guard('admin')->check()) {
        $admin = Auth::guard('admin')->user();
        return redirect()->route($admin->role_id == 1 ? 'admin.dashboard' : 'admin2.dashboard');
    }

    // Jika sudah login sebagai kasir, redirect ke dashboard kasir
    if (Auth::guard('kasir')->check()) {
        return redirect()->route('kasir.dashboard');
    }

    return view('auth.login');
}

public function processLogin(Request $request)
{
    $type = $request->login_type;

    if (!in_array($type, ['admin', 'kasir'])) {
        return back()->with('error', 'Role pengguna tidak valid.');
    }

    if ($type === 'admin') {
        $request->validate([
            'email'          => 'required|email',
            'password_admin' => 'required|string',
        ], [
            'email.required'          => 'Email wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'password_admin.required' => 'Password wajib diisi.',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password_admin, $admin->password)) {
            return back()->with('error', 'Email atau password salah.')
                ->withInput(['email' => $request->email, 'login_type' => $type]);
        }

        if ($admin->status !== 'aktif') {
            return back()->with('error', 'Akun Anda tidak aktif. Hubungi super admin.')
                ->withInput(['login_type' => $type]);
        }

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        $route = $admin->role_id == 1 ? 'admin.dashboard' : 'admin2.dashboard';
        return redirect()->route($route)->with('success', 'Selamat datang, ' . $admin->nama . '!');
    }

    if ($type === 'kasir') {
        $request->validate([
            'no_hp'          => 'required|string',
            'password_kasir' => 'required|string',
        ], [
            'no_hp.required'          => 'No. HP wajib diisi.',
            'password_kasir.required' => 'Password wajib diisi.',
        ]);

        $kasir = Kasir::where('no_hp', $request->no_hp)->first();

        if (!$kasir || !Hash::check($request->password_kasir, $kasir->password)) {
            return back()->with('error', 'No. HP atau password salah.')
                ->withInput(['no_hp' => $request->no_hp, 'login_type' => $type]);
        }

        if ($kasir->status !== 'aktif') {
            return back()->with('error', 'Akun Anda tidak aktif. Hubungi admin.')
                ->withInput(['login_type' => $type]);
        }

        Auth::guard('kasir')->login($kasir);
        $request->session()->regenerate();

        return redirect()->route('kasir.dashboard')->with('success', 'Selamat datang, ' . $kasir->nama_kasir . '!');
    }

    return back()->with('error', 'Role tidak dikenali.');
}

    // =============================
    // DASHBOARD ADMIN SUPER (role_id = 1)
    // =============================
    public function adminDashboard()
    {
        // Pastikan user sudah login sebagai admin
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')
                ->with('error', 'Silakan login dulu');
        }

        // Statistik utama dashboard
        $totalPelanggan = Pelanggan::count();
        $totalKasir     = Kasir::count();
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'offline')->count();

        // Total omzet hari ini dari semua transaksi yang lunas
        $totalOmzet = Transaksi::where('status_bayar', 'lunas')
            ->whereDate('tgl_lunas', today())
            ->sum('total_bayar');

        // Jumlah transaksi online yang masuk hari ini dengan status antrian
        $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
            ->where('status_transaksi', 'antrian')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Jumlah transaksi online yang sudah lunas hari ini
        $pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
            ->where('status_bayar', 'lunas')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Jumlah transaksi online yang belum lunas dan belum batal/selesai
        $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereNotIn('status_transaksi', ['batal', 'selesai'])
            ->count();

        // Jumlah transaksi online yang butuh driver pickup tapi belum ada driver
        $butuhPickup = DB::table('delivery')
            ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('delivery.jenis', 'pickup')
            ->whereNull('delivery.id_driver')
            ->where('transaksi.jenis_transaksi', 'online')
            ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
            ->count();

        // Jumlah delivery antar yang belum ada driver
        $butuhAntar = DB::table('delivery')
            ->where('jenis', 'antar')
            ->whereNull('id_driver')
            ->count();

        // Jumlah transaksi online yang estimasi selesai hari ini tapi belum selesai
        $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
            ->count();

        // Jumlah transaksi online yang sudah melewati estimasi tapi belum selesai (terlambat)
        $terlambat = Transaksi::where('tgl_estimasi', '<', now())
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
            ->count();

        // Jumlah transaksi online yang siap diambil pelanggan
        $siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Ambil semua transaksi dengan info deadline dan status deadline
        $orders = Transaksi::query()
            ->orderBy('id_transaksi', 'DESC')
            ->get()
            ->map(function ($o) {
                // Format tanggal estimasi untuk tampilan
                $o->deadline = $o->tgl_estimasi
                    ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                    : '-';

                // Tentukan status deadline: terlambat, mendesak, atau normal
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
    // DASHBOARD ADMIN BIASA (role_id = 2)
    // =============================
    public function admin2Dashboard()
    {
        // Pastikan user sudah login sebagai admin
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')
                ->with('error', 'Silakan login dulu');
        }

        // Statistik utama dashboard
        $totalPelanggan = Pelanggan::count();
        $totalKasir     = Kasir::count();
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'offline')->count();

        // Total omzet offline hari ini yang sudah lunas
        $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
            ->whereDate('tgl_transaksi', today())
            ->where('status_bayar', 'lunas')
            ->sum('total_bayar');

        // Jumlah transaksi online yang masuk hari ini dengan status antrian
        $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
            ->where('status_transaksi', 'antrian')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Jumlah transaksi online yang sudah lunas hari ini
        $pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
            ->where('status_bayar', 'lunas')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Jumlah transaksi online yang belum lunas dan belum batal/selesai
        $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereNotIn('status_transaksi', ['batal', 'selesai'])
            ->count();

        // Jumlah transaksi online yang butuh driver pickup tapi belum ada driver
        $butuhPickup = DB::table('delivery')
            ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('delivery.jenis', 'pickup')
            ->whereNull('delivery.id_driver')
            ->where('transaksi.jenis_transaksi', 'online')
            ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
            ->count();

        // Jumlah delivery antar yang belum ada driver
        $butuhAntar = DB::table('delivery')
            ->where('jenis', 'antar')
            ->whereNull('id_driver')
            ->count();

        // Jumlah transaksi online yang estimasi selesai hari ini tapi belum selesai
        $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
            ->count();

        // Jumlah transaksi online yang sudah melewati estimasi tapi belum selesai (terlambat)
        $terlambat = Transaksi::where('tgl_estimasi', '<', now())
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
            ->count();

        // Jumlah transaksi online yang siap diambil pelanggan
        $siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Ambil semua transaksi dengan info deadline dan status deadline
        $orders = Transaksi::query()
            ->orderBy('id_transaksi', 'DESC')
            ->get()
            ->map(function ($o) {
                // Format tanggal estimasi untuk tampilan
                $o->deadline = $o->tgl_estimasi
                    ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                    : '-';

                // Tentukan status deadline: terlambat, mendesak, atau normal
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
    // DASHBOARD KASIR
    // =============================
    public function kasirDashboard()
    {
        // Pastikan user sudah login sebagai kasir
        $kasir = auth()->guard('kasir')->user();

        if (!$kasir) {
            return redirect()->route('login')
                ->with('error', 'Silakan login dulu');
        }

        // Statistik utama dashboard
        $totalPelanggan = Pelanggan::count();
        $totalKasir     = Kasir::count();
        $totalTransaksi = Transaksi::where('jenis_transaksi', 'offline')->count();

        // Total omzet offline hari ini yang sudah lunas
        $totalOmzet = Transaksi::where('jenis_transaksi', 'offline')
            ->whereDate('tgl_transaksi', today())
            ->where('status_bayar', 'lunas')
            ->sum('total_bayar');

        // Jumlah transaksi online yang masuk hari ini dengan status antrian
        $transaksiMasukHariIni = Transaksi::whereDate('tgl_transaksi', today())
            ->where('status_transaksi', 'antrian')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Jumlah transaksi online yang sudah lunas hari ini
        $pembayaranLunasHariIni = Transaksi::whereDate('tgl_lunas', today())
            ->where('status_bayar', 'lunas')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Jumlah transaksi online yang belum lunas dan belum batal/selesai
        $belumLunas = Transaksi::where('status_bayar', 'belum_lunas')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereNotIn('status_transaksi', ['batal', 'selesai'])
            ->count();

        // Jumlah transaksi online yang butuh driver pickup tapi belum ada driver
        $butuhPickup = DB::table('delivery')
            ->join('transaksi', 'delivery.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('delivery.jenis', 'pickup')
            ->whereNull('delivery.id_driver')
            ->where('transaksi.jenis_transaksi', 'online')
            ->whereNotIn('transaksi.status_transaksi', ['selesai', 'batal'])
            ->count();

        // Jumlah delivery antar yang belum ada driver
        $butuhAntar = DB::table('delivery')
            ->where('jenis', 'antar')
            ->whereNull('id_driver')
            ->count();

        // Jumlah transaksi online yang estimasi selesai hari ini tapi belum selesai
        $harusSelesaiHariIni = Transaksi::whereDate('tgl_estimasi', today())
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
            ->count();

        // Jumlah transaksi online yang sudah melewati estimasi tapi belum selesai (terlambat)
        $terlambat = Transaksi::where('tgl_estimasi', '<', now())
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->whereIn('status_transaksi', ['antrian', 'proses', 'selesai_dicuci'])
            ->count();

        // Jumlah transaksi online yang siap diambil pelanggan
        $siapDiambil = Transaksi::where('status_transaksi', 'siap_di_ambil')
            ->where('jenis_transaksi', 'online') // ← ONLINE ONLY
            ->count();

        // Ambil semua transaksi dengan info deadline dan status deadline
        $orders = Transaksi::query()
            ->orderBy('id_transaksi', 'DESC')
            ->get()
            ->map(function ($o) {
                // Format tanggal estimasi untuk tampilan
                $o->deadline = $o->tgl_estimasi
                    ? Carbon::parse($o->tgl_estimasi)->format('d M Y, H:i')
                    : '-';

                // Tentukan status deadline: terlambat, mendesak, atau normal
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
    // PELANGGAN - LIST (VIEW)
    // =============================

    // Halaman daftar pelanggan untuk Super Admin
    public function pelangganIndex()
    {
        // Cek permission view pelanggan
        requirePermission('pelanggan', 'view');
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('pelanggan.index', compact('pelanggan'));
    }

    // Halaman daftar pelanggan untuk Kasir
    public function pelangganIndexKasir()
    {
        // Cek permission view pelanggan
        requirePermission('pelanggan', 'view');
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('kasir.pelanggan.index', compact('pelanggan'));
    }

    // Halaman daftar pelanggan untuk Admin Biasa
    public function pelangganIndexAdmin2()
    {
        // Cek permission view pelanggan
        requirePermission('pelanggan', 'view');
        $pelanggan = Pelanggan::orderBy('nama_pelanggan', 'ASC')->get();
        return view('admin2.pelanggan.index', compact('pelanggan'));
    }

    // =============================
    // PELANGGAN - CREATE (FORM TAMBAH)
    // =============================

    // Form tambah pelanggan untuk Super Admin
    public function Create()
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');
        return view('pelanggan.create');
    }

    // Form tambah pelanggan untuk Kasir
    public function createKasir()
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');
        return view('kasir.pelanggan.create');
    }

    // Form tambah pelanggan untuk Admin Biasa
    public function createAdmin2()
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');
        return view('admin2.pelanggan.create');
    }

    // =============================
    // HELPER: VALIDASI FORM PELANGGAN
    // TC-09: Nama wajib diisi
    // TC-10: No HP wajib diisi
    // TC-11: No HP hanya angka, 10-15 digit
    // TC-12: Format email harus valid
    // =============================
    private function validatePelanggan(Request $request): void
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'email'          => 'nullable|email',
            'jk'             => 'required|in:L,P',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            // TC-09: Nama tidak boleh kosong
            'nama_pelanggan.required' => 'Nama Pelanggan tidak boleh kosong.',
            'nama_pelanggan.max'      => 'Nama Pelanggan maksimal 255 karakter.',
            // TC-10: No HP tidak boleh kosong
            'no_hp.required'          => 'No Handphone tidak boleh kosong.',
            // TC-11: No HP harus angka dan panjang 10-15 digit
            'no_hp.min'               => 'No Handphone minimal 10 digit.',
            'no_hp.max'               => 'No Handphone maksimal 15 digit.',
            'no_hp.regex'             => 'No Handphone hanya boleh berisi angka.',
            // TC-12: Format email harus valid
            'email.email'             => 'Format Email tidak valid.',
            // Validasi lainnya
            'jk.required'             => 'Jenis Kelamin wajib dipilih.',
            'alamat.required'         => 'Alamat tidak boleh kosong.',
            'gambar.image'            => 'File harus berupa gambar.',
            'gambar.mimes'            => 'Format gambar harus jpg, jpeg, atau png.',
            'gambar.max'              => 'Ukuran gambar maksimal 2MB.',
        ]);
    }

    // =============================
    // HELPER: PROSES UPLOAD GAMBAR PELANGGAN
    // Mengembalikan path gambar baru, atau path gambar lama jika tidak ada upload
    // =============================
    private function uploadGambar(Request $request, ?string $gambarLama = null): ?string
    {
        // Jika tidak ada file baru, kembalikan gambar lama (untuk kasus update)
        if (!$request->hasFile('gambar')) {
            return $gambarLama;
        }

        // Hapus gambar lama dari storage jika ada (untuk kasus update)
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

    // =============================
    // PELANGGAN - STORE (SIMPAN DATA BARU)
    // =============================

    // Simpan pelanggan baru oleh Super Admin
    public function store(Request $request)
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request);

        // Proses upload gambar jika ada
        $gambarPath = $this->uploadGambar($request);

        // Simpan data pelanggan baru ke database
        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        // Redirect ke halaman transaksi jika datang dari transaksi, atau ke index pelanggan
        if ($request->from === 'transaksi') {
            return redirect()
                ->route('transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // Simpan pelanggan baru oleh Kasir
    public function storeKasir(Request $request)
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request);

        // Proses upload gambar jika ada
        $gambarPath = $this->uploadGambar($request);

        // Simpan data pelanggan baru ke database
        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        // Redirect ke halaman transaksi kasir jika datang dari transaksi
        if ($request->from === 'transaksi') {
            return redirect()
                ->route('kasir.transaksi.pelanggan')
                ->with('success', 'Pelanggan berhasil ditambahkan!');
        }

        return redirect()
            ->route('kasir.pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    // Simpan pelanggan baru oleh Admin Biasa
    public function storeAdmin2(Request $request)
    {
        // Cek permission add pelanggan
        requirePermission('pelanggan', 'add');

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request);

        // Proses upload gambar jika ada
        $gambarPath = $this->uploadGambar($request);

        // Simpan data pelanggan baru ke database
        Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'gambar'         => $gambarPath,
            'password'       => bcrypt('123456'),
            'jk'             => $request->jk,
            'email'          => $request->email,
        ]);

        // Redirect ke halaman transaksi admin2 jika datang dari transaksi
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
    // PELANGGAN - EDIT (FORM EDIT)
    // =============================

    // Form edit pelanggan untuk Super Admin
    public function pelangganEdit($id)
    {
        // Cek permission edit pelanggan
        requirePermission('pelanggan', 'edit');
        $pelanggan = Pelanggan::findOrFail($id);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    // Form edit pelanggan untuk Kasir
    public function pelangganEditKasir($id)
    {
        // Cek permission edit pelanggan
        requirePermission('pelanggan', 'edit');
        $pelanggan = Pelanggan::findOrFail($id);
        return view('kasir.pelanggan.edit', compact('pelanggan'));
    }

    // Form edit pelanggan untuk Admin Biasa
    public function pelangganEditAdmin2($id)
    {
        // Cek permission edit pelanggan
        requirePermission('pelanggan', 'edit');
        $pelanggan = Pelanggan::findOrFail($id);
        return view('admin2.pelanggan.edit', compact('pelanggan'));
    }

    // =============================
    // PELANGGAN - UPDATE (SIMPAN PERUBAHAN)
    // =============================

    // Update data pelanggan oleh Super Admin
    public function pelangganUpdate(Request $request, $id)
    {
        // Cek permission edit pelanggan
        requirePermission('pelanggan', 'edit');

        $pelanggan = Pelanggan::findOrFail($id);

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request);

        // Proses upload gambar baru, hapus gambar lama jika ada
        $gambarPath = $this->uploadGambar($request, $pelanggan->gambar);

        // Simpan perubahan data pelanggan ke database
        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath, // ✅ gambar ikut diupdate
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    // Update data pelanggan oleh Kasir
    public function pelangganUpdateKasir(Request $request, $id)
    {
        // Cek permission edit pelanggan
        requirePermission('pelanggan', 'edit');

        $pelanggan = Pelanggan::findOrFail($id);

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request);

        // Proses upload gambar baru, hapus gambar lama jika ada
        $gambarPath = $this->uploadGambar($request, $pelanggan->gambar);

        // Simpan perubahan data pelanggan ke database
        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath, // ✅ gambar ikut diupdate
        ]);

        return redirect()->route('kasir.pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    // Update data pelanggan oleh Admin Biasa
    public function pelangganUpdateAdmin2(Request $request, $id)
    {
        // Cek permission edit pelanggan
        requirePermission('pelanggan', 'edit');

        $pelanggan = Pelanggan::findOrFail($id);

        // Jalankan validasi form (TC-09 s/d TC-12)
        $this->validatePelanggan($request);

        // Proses upload gambar baru, hapus gambar lama jika ada
        $gambarPath = $this->uploadGambar($request, $pelanggan->gambar);

        // Simpan perubahan data pelanggan ke database
        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'email'          => $request->email,
            'jk'             => $request->jk,
            'gambar'         => $gambarPath, // ✅ gambar ikut diupdate
        ]);

        return redirect()->route('admin2.pelanggan.index')->with('success', 'Pelanggan berhasil diupdate!');
    }

    // =============================
    // PELANGGAN - DELETE (HAPUS DATA)
    // =============================

    // Hapus pelanggan oleh Super Admin
    public function pelangganDestroy($id)
    {
        // Cek permission delete pelanggan
        requirePermission('pelanggan', 'delete');
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();
        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

    // Hapus pelanggan oleh Kasir
    public function pelangganDestroyKasir($id)
    {
        // Cek permission delete pelanggan
        requirePermission('pelanggan', 'delete');
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->delete();
        return redirect()->route('kasir.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }

    // Hapus pelanggan oleh Admin Biasa
    public function pelangganDestroyAdmin2($id)
    {
        // Cek permission delete pelanggan
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
        // Hanya Super Admin (role_id = 1) yang bisa akses halaman manager
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Anda tidak memiliki akses');
        }

        // Ambil semua data yang dibutuhkan halaman manager
        $admins    = Admin::with('role')->orderBy('nama')->get();
        $roles     = Role::whereIn('id', [1, 2])->get();
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
        // Hanya Super Admin yang bisa menambah admin baru
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah admin');
        }

        // Validasi data admin baru
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin,email',
            'password' => 'required|string|min:6',
            'role_id'  => 'required|in:1,2',
        ]);

        // Simpan admin baru ke database
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
        // Hanya Super Admin yang bisa menambah pelanggan dari halaman manager
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah pelanggan dari manager');
        }

        // Validasi data pelanggan dengan tambahan unique email
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'no_hp'          => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'email'          => 'nullable|email|unique:pelanggan,email',
            'jk'             => 'required|in:L,P',
            'alamat'         => 'required|string',
            'gambar'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'nama_pelanggan.required' => 'Nama Pelanggan tidak boleh kosong.',
            'no_hp.required'          => 'No Handphone tidak boleh kosong.',
            'no_hp.min'               => 'No Handphone minimal 10 digit.',
            'no_hp.max'               => 'No Handphone maksimal 15 digit.',
            'no_hp.regex'             => 'No Handphone hanya boleh berisi angka.',
            'email.email'             => 'Format Email tidak valid.',
            'email.unique'            => 'Email sudah digunakan pelanggan lain.',
            'jk.required'             => 'Jenis Kelamin wajib dipilih.',
            'alamat.required'         => 'Alamat tidak boleh kosong.',
            'gambar.image'            => 'File harus berupa gambar.',
            'gambar.mimes'            => 'Format gambar harus jpg, jpeg, atau png.',
            'gambar.max'              => 'Ukuran gambar maksimal 2MB.',
        ]);

        // Proses upload gambar jika ada
        $gambarPath = $this->uploadGambar($request);

        // Simpan pelanggan baru ke database
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
        // Hanya Super Admin yang bisa mengubah hak akses role
        $admin = auth()->guard('admin')->user();
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update privilege');
        }

        // Validasi menu_role_id harus ada di tabel menu_roles
        $request->validate([
            'menu_role_id' => 'required|exists:menu_roles,id',
        ]);

        $menuRole = MenuRole::findOrFail($request->menu_role_id);

        // Update hak akses: view, add, edit, delete berdasarkan checkbox yang dicentang
        $menuRole->update([
            'can_view'   => $request->has('can_view'),
            'can_add'    => $request->has('can_add'),
            'can_edit'   => $request->has('can_edit'),
            'can_delete' => $request->has('can_delete'),
        ]);

        return back()->with('success', 'Hak akses berhasil diperbarui');
    }
}