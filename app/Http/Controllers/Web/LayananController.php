<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Layanan;
use App\Models\JenisLayanan;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuRole;
use App\Models\Menu;
use App\Models\Satuan; 

/**
 * LayananController
 * 
 * Controller untuk mengelola data layanan laundry, meliputi:
 * - CRUD Layanan dan Jenis Layanan
 * - Duplikasi layanan
 * - Manajemen session jenis layanan sementara
 * - Mendukung tiga panel: Admin, Kasir, Admin2
 */
class LayananController extends Controller
{
    private function buildLayananQuery(Request $request)
    {
        $query = Layanan::with(['jenis.satuan']);
        $search = trim((string) $request->get('search', ''));

        if ($search !== '') {
            $query->where('nama_layanan', 'like', '%' . $search . '%');
        }

        $sort = $request->get('sort', 'default');

        match ($sort) {
            'az' => $query->orderBy('nama_layanan', 'asc'),
            'za' => $query->orderBy('nama_layanan', 'desc'),
            'terlama' => $query->orderBy('id_layanan', 'asc'),
            default => $query->orderBy('id_layanan', 'desc'),
        };

        return $query;
    }

    // =========================
    // INDEX LAYANAN
    // Menampilkan daftar semua layanan utama beserta jenis dan satuannya
    // =========================
    public function index(Request $request)
    {
        // Validasi akses: user harus punya izin 'view' pada menu layanan
        requirePermission('layanan', 'view');
        
        // Ambil semua data parfum untuk ditampilkan di form layanan
        $parfum = \App\Models\Parfum::all();
        
        // Eager load relasi jenis -> satuan untuk menghindari N+1 query
        $layananUtama = $this->buildLayananQuery($request)
            ->paginate(10)
            ->withQueryString();

        // ======================
        // KASIR - tampilkan view kasir dengan izin dinamis per aksi
        // ======================
        if (Auth::guard('kasir')->check()) {
            $kasir = Auth::guard('kasir')->user();

            return view('kasir.layanan.layanan', [
                'layananUtama'     => $layananUtama,
                'parfum'           => $parfum,
                'from'             => 'kasir',
                'canAddLayanan'    => canAdd('layanan'),    // izin tambah data
                'canEditLayanan'   => canEdit('layanan'),   // izin ubah data
                'canDeleteLayanan' => canDelete('layanan'), // izin hapus data
            ]);
        }

        // ======================
        // ADMIN (SUPER) - tampilkan view admin utama
        // ======================
        if (Auth::guard('admin')->check()) {
            return view('admin.layanan', [
                'layananUtama' => $layananUtama,
                'parfum'       => $parfum,
                'from'         => 'admin',
            ]);
        }

        // Jika bukan kasir maupun admin, tolak akses
        abort(403);
    }

    // =========================
    // INDEX LAYANAN - ADMIN2
    // Khusus untuk admin dengan role_id = 2
    // =========================
    public function indexAdmin2(Request $request)
    {
        requirePermission('layanan', 'view');
        
        // Pastikan yang mengakses adalah admin dengan role_id = 2
        $admin2 = Auth::guard('admin')->user();
        if (!$admin2 || $admin2->role_id != 2) {
            abort(403, 'Hanya admin biasa yang bisa akses');
        }

        // Eager load relasi jenis dan satuan
        $layananUtama = $this->buildLayananQuery($request)
            ->paginate(10)
            ->withQueryString();
        $parfum = \App\Models\Parfum::all();

        return view('admin2.layanan.layanan', [
            'layananUtama' => $layananUtama,
            'parfum'       => $parfum,
            'from'         => 'admin2',
        ]);
    }

    // =========================
    // INDEX PENGELUARAN - KASIR
    // Menampilkan daftar pengeluaran kasir diurutkan dari terbaru
    // =========================
    public function indexKasir(Request $request)
    {
        requirePermission('layanan', 'view');
        
        $kasir = auth('kasir')->user();
        
        // Pastikan kasir sudah login sebelum mengakses halaman ini
        if (!$kasir) {
            abort(403, 'Silakan login terlebih dahulu');
        }

        // Ambil data pengeluaran diurutkan berdasarkan tanggal dan waktu input (terbaru dulu)
        $pengeluaran = \App\Models\Pengeluaran::orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kasir.pengeluaran.index', compact('pengeluaran'));
    }

    // =========================
    // CREATE LAYANAN - ADMIN
    // Menampilkan form tambah layanan baru untuk panel Admin
    // =========================
    public function create()
    {
        requirePermission('layanan', 'add');
        
        // ID sementara = 0 karena layanan belum tersimpan ke database
        $layanan_id = 0;
        
        // Ambil data jenis layanan baru dari session (belum disimpan ke DB)
        $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);

        // Ambil jenis layanan yang sudah ada, unik berdasarkan kombinasi field penting
        // Menggunakan perulangan implisit oleh unique() untuk deduplikasi data
        $jenisLama = \App\Models\JenisLayanan::with('satuan')
            ->orderBy('nama_jenis')
            ->get()
            ->unique(function ($item) {
                // Gabungkan field sebagai kunci unik untuk menghindari duplikasi tampilan
                return $item->nama_jenis.'-'.$item->harga.'-'.$item->id_satuan
                    .'-'.$item->lama.'-'.$item->lama_satuan;
            });

        // Ambil data pendukung form: satuan dan parfum
        $satuan = Satuan::all();
        $parfum = \App\Models\Parfum::all();

        return view('admin.layanan_create', compact('jenisBaru', 'jenisLama', 'satuan', 'parfum'))
            ->with('from', request('from'));
    }

    // =========================
    // CREATE LAYANAN - KASIR
    // Menampilkan form tambah layanan untuk panel Kasir
    // =========================
    public function CreateKasir(Request $request)
    {
        requirePermission('layanan', 'add');
        
        $layanan_id = 0;
        
        // Ambil array jenis layanan sementara dari session kasir
        $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);
        $satuan    = Satuan::all();
        $parfum    = \App\Models\Parfum::all();

        return view('kasir.layanan.layanan_create', compact('jenisBaru', 'satuan', 'parfum'));
    }

    // =========================
    // CREATE LAYANAN - ADMIN2
    // Menampilkan form tambah layanan untuk panel Admin2
    // =========================
    public function createAdmin2(Request $request)
    {
        requirePermission('layanan', 'add');
        
        $layanan_id = 0;
        $jenisBaru  = session()->get("jenis_baru_{$layanan_id}", []);
        $satuan     = Satuan::all();
        $parfum     = \App\Models\Parfum::all();

        return view('admin2.layanan.layanan_create', compact('jenisBaru', 'satuan', 'parfum'));
    }

    // =========================
    // STORE LAYANAN - ADMIN
    // Menyimpan layanan baru beserta jenis-jenisnya ke database
    // =========================
    public function store(Request $request)
    {
        requirePermission('layanan', 'add');
        
        // Validasi: nama layanan wajib diisi
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
        ]);

        $from      = $request->input('from');
        $layanan_id = 0;
        
        // Ambil array jenis baru dari session dan jenis lama yang dipilih dari form
        $jenisBaru       = session()->get("jenis_baru_{$layanan_id}", []);
        $jenisLamaDipilih = $request->input('jenis_lama', []);

        // Validasi: minimal harus ada 1 jenis layanan (baru atau lama)
        if (count($jenisBaru) == 0 && count($jenisLamaDipilih) == 0) {
            return back()
                ->withErrors(['jenis_kosong' => 'Pilih minimal 1 jenis layanan.'])
                ->withInput();
        }

        // Proses input array 'proses' — bisa berupa array atau string CSV
        $prosesInput  = $request->input('proses', []);
        $prosesToStore = [];
        
        if (is_array($prosesInput)) {
            // Jika sudah berupa array, trim setiap elemennya
            $prosesToStore = array_map('trim', $prosesInput);
        } elseif (!empty($prosesInput)) {
            // Jika berupa string, pecah berdasarkan koma lalu trim
            $prosesToStore = array_map('trim', explode(',', $prosesInput));
        }

        // Simpan layanan baru ke database
        // array_values() untuk reset index, array_filter() untuk hapus nilai kosong
        $layanan = \App\Models\Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => json_encode(array_values(array_filter($prosesToStore))),
        ]);

        // Perulangan: simpan setiap jenis layanan BARU dari session ke database
        foreach ($jenisBaru as $jb) {
            // Lewati jenis yang tidak memiliki nama
            if (empty($jb['nama_jenis'])) {
                continue;
            }

            \App\Models\JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama_jenis'],
                'harga'       => $jb['harga']       ?? 0,
                'id_satuan'   => $jb['id_satuan']   ?? null,
                'lama'        => $jb['lama']         ?? null,
                'lama_satuan' => $jb['lama_satuan']  ?? null,
                'keterangan'  => $jb['keterangan']  ?? null,
                'gambar'      => $jb['gambar']       ?? null,
            ]);
        }

        // Perulangan: duplikasi jenis layanan LAMA yang dipilih user ke layanan baru
        if (!empty($jenisLamaDipilih)) {
            foreach ($jenisLamaDipilih as $idJenis) {
                // Cari data jenis asli berdasarkan ID
                $jenis = \App\Models\JenisLayanan::find($idJenis);
                if ($jenis) {
                    // Buat salinan dengan id_layanan yang baru
                    \App\Models\JenisLayanan::create([
                        'id_layanan'  => $layanan->id_layanan,
                        'nama_jenis'  => $jenis->nama_jenis,
                        'harga'       => $jenis->harga,
                        'id_satuan'   => $jenis->id_satuan,
                        'lama'        => $jenis->lama,
                        'lama_satuan' => $jenis->lama_satuan,
                        'keterangan'  => $jenis->keterangan,
                        'gambar'      => $jenis->gambar,
                    ]);
                }
            }
        }

        // Hapus data session jenis sementara setelah berhasil disimpan
        session()->forget("jenis_baru_{$layanan_id}");

        // Jika berasal dari halaman transaksi, redirect kembali ke transaksi
        if ($from === 'transaksi') {
            return redirect()->route('transaksi.fromLayanan', [
                'layanan_id' => $layanan->id_layanan
            ]);
        }

        return redirect()->route('layanan.index')
            ->with('success', 'Layanan berhasil ditambahkan');
    }

    // =========================
    // STORE LAYANAN - KASIR
    // Menyimpan layanan baru dari panel Kasir
    // =========================
    public function storeKasir(Request $request)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'jenis_lama'   => 'array',
            'proses'       => 'array|required',
        ]);

        // Buat layanan baru tanpa proses (kasir tidak input proses)
        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
        ]);

        // Perulangan: salin jenis lama yang dipilih ke layanan baru
        if ($request->has('jenis_lama')) {
            foreach ($request->jenis_lama as $idJenis) {
                $jenis = JenisLayanan::find($idJenis);
                if ($jenis) {
                    JenisLayanan::create([
                        'id_layanan'  => $layanan->id_layanan,
                        'nama_jenis'  => $jenis->nama_jenis,
                        'harga'       => $jenis->harga,
                        'id_satuan'   => $jenis->id_satuan,
                        'lama'        => $jenis->lama,
                        'lama_satuan' => $jenis->lama_satuan,
                        'keterangan'  => $jenis->keterangan,
                        'gambar'      => $jenis->gambar,
                    ]);
                }
            }
        }

        // Ambil array jenis baru dari session (key default = 0 untuk layanan baru)
        $jenisBaru = session()->get("jenis_baru_0", []);
        
        // Perulangan: simpan setiap jenis baru dari session ke database via relasi
        foreach ($jenisBaru as $jb) {
            $layanan->jenis()->create([
                'nama_jenis'  => $jb['nama_jenis'],
                'harga'       => $jb['harga'],
                'id_satuan'   => $jb['id_satuan']  ?? null,
                'lama'        => $jb['lama']        ?? null,
                'lama_satuan' => $jb['lama_satuan'] ?? null,
                'keterangan'  => $jb['keterangan'] ?? null,
            ]);
        }

        // Bersihkan session setelah data tersimpan
        session()->forget("jenis_baru_0");

        return redirect()->route('kasir.layanan.index')
            ->with('success', 'Layanan berhasil dibuat!');
    }

    // =========================
    // STORE LAYANAN - ADMIN2
    // Menyimpan layanan baru dari panel Admin2
    // =========================
    public function storeAdmin2(Request $request)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'jenis_lama'   => 'array',
            'proses'       => 'array|required',
        ]);

        // Gabungkan array proses menjadi string CSV sebelum disimpan
        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => implode(',', $request->proses),
        ]);

        // Perulangan: salin jenis lama yang dipilih ke layanan baru
        if ($request->has('jenis_lama')) {
            foreach ($request->jenis_lama as $idJenis) {
                $jenis = JenisLayanan::find($idJenis);
                if ($jenis) {
                    JenisLayanan::create([
                        'id_layanan'  => $layanan->id_layanan,
                        'nama_jenis'  => $jenis->nama_jenis,
                        'harga'       => $jenis->harga,
                        'id_satuan'   => $jenis->id_satuan,
                        'lama'        => $jenis->lama,
                        'lama_satuan' => $jenis->lama_satuan,
                        'keterangan'  => $jenis->keterangan,
                        'gambar'      => $jenis->gambar,
                    ]);
                }
            }
        }

        // Ambil jenis baru dari session lalu filter yang tidak lengkap datanya
        $jenisBaru = session()->get("jenis_baru_0", []);
        $jenisBaru = array_filter($jenisBaru, function($jb) {
            // Hanya simpan jenis yang memiliki nama, harga, dan satuan
            return !empty($jb['nama_jenis']) && !empty($jb['harga']) && !empty($jb['id_satuan']);
        });

        // Perulangan: simpan setiap jenis yang lolos filter ke database
        foreach ($jenisBaru as $jb) {
            $layanan->jenis()->create([
                'nama_jenis'  => $jb['nama_jenis'],
                'harga'       => $jb['harga']       ?? 0,
                'id_satuan'   => $jb['id_satuan'],
                'lama'        => $jb['lama']         ?? null,
                'lama_satuan' => $jb['lama_satuan']  ?? null,
                'keterangan'  => $jb['keterangan']  ?? null,
                'gambar'      => $jb['gambar']       ?? null,
            ]);
        }

        session()->forget("jenis_baru_0");

        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil dibuat!');
    }

    // =========================
    // EDIT LAYANAN
    // Menampilkan form edit layanan berdasarkan ID, untuk Admin dan Kasir
    // =========================
    public function edit($id)
    {
        requirePermission('layanan', 'edit');
        
        // Jika ID = 0, arahkan ke halaman create
        if ($id == 0) {
            return redirect()->route('layanan.create');
        }

        $satuan  = Satuan::all();
        
        // Cari layanan beserta jenis-jenisnya, lempar 404 jika tidak ditemukan
        $layanan   = Layanan::with('jenis')->findOrFail($id);
        
        // Ambil array jenis baru sementara dari session untuk layanan ini
        $jenisBaru = session()->get("jenis_baru_{$id}", []);

        // Tampilkan view sesuai guard yang aktif
        if (Auth::guard('kasir')->check()) {
            return view('kasir.layanan.layanan_edit', compact('layanan', 'jenisBaru'));
        }

        if (Auth::guard('admin')->check()) {
            return view('admin.layanan_edit', [
                'layanan'    => $layanan,
                'jenisBaru'  => $jenisBaru,
                'id_layanan' => $layanan->id_layanan,
                'satuan'     => $satuan,
            ]);
        }

        abort(403);
    }

    // =========================
    // EDIT LAYANAN - ADMIN2
    // =========================
    public function editAdmin2($id)
    {
        requirePermission('layanan', 'edit');
        
        $admin2 = Auth::guard('admin')->user();
        if (!$admin2 || $admin2->role_id != 2) {
            abort(403, 'Hanya admin biasa yang bisa akses');
        }

        // Eager load jenis beserta satuan untuk kebutuhan form edit
        $layanan = Layanan::with('jenis.satuan')->findOrFail($id);

        return view('admin2.layanan.layanan_edit', [
            'layanan' => $layanan,
            'from'    => 'admin2',
        ]);
    }

    // =========================
    // UPDATE LAYANAN - ADMIN2
    // Memperbarui data layanan beserta jenis-jenisnya dari panel Admin2
    // =========================
    public function updateAdmin2(Request $request, $id)
    {
        requirePermission('layanan', 'edit');
        
        $layanan = Layanan::findOrFail($id);

        // Update nama layanan dan proses (array diubah ke string CSV)
        $layanan->update([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => implode(',', $request->proses ?? []),
        ]);

        // Perulangan: update setiap jenis layanan lama yang diedit
        // Key array adalah id_jenis, value adalah data yang diubah
        if ($request->has('jenis_lama')) {
            foreach ($request->jenis_lama as $idJenis => $data) {
                $jenis = JenisLayanan::find($idJenis);
                if ($jenis) {
                    $jenis->update([
                        'nama_jenis'  => $data['nama']       ?? $jenis->nama_jenis,
                        'harga'       => $data['harga']      ?? $jenis->harga,
                        'id_satuan'   => $data['id_satuan']  ?? $jenis->id_satuan,
                        'lama'        => $data['lama']       ?? $jenis->lama,
                        'lama_satuan' => $data['lama_satuan'] ?? $jenis->lama_satuan,
                        'keterangan'  => $data['keterangan'] ?? $jenis->keterangan,
                    ]);
                }
            }
        }

        // Ambil array jenis baru dari session dan simpan ke database
        $jenisBaru = session()->get("jenis_baru_{$id}", []);
        foreach ($jenisBaru as $jb) {
            JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama_jenis']  ?? null,
                'harga'       => $jb['harga']        ?? 0,
                'id_satuan'   => $jb['id_satuan']   ?? null,
                'lama'        => $jb['lama']          ?? null,
                'lama_satuan' => $jb['lama_satuan']  ?? null,
                'keterangan'  => $jb['keterangan']  ?? null,
                'gambar'      => $jb['gambar']        ?? null,
            ]);
        }

        // Jika ada request update jenis spesifik beserta gambarnya
        if ($request->has('id_jenis_layanan')) {
            $jenis = JenisLayanan::find($request->id_jenis_layanan);

            if ($jenis) {
                $jenis->update([
                    'nama_jenis'  => $request->nama_jenis,
                    'harga'       => $request->harga,
                    'id_satuan'   => $request->id_satuan,
                    'lama'        => $request->lama,
                    'lama_satuan' => $request->lama_satuan,
                    'keterangan'  => $request->keterangan,
                ]);

                // Jika ada file gambar baru, simpan dan update path-nya
                if ($request->hasFile('gambar')) {
                    $file     = $request->file('gambar');
                    $namaFile = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/jenis', $namaFile);
                    $jenis->update(['gambar' => $namaFile]);
                }
            }
        }

        // Bersihkan session jenis sementara setelah update selesai
        session()->forget("jenis_baru_{$id}");

        if ($request->from === 'transaksi') {
            return redirect()->route('admin2.transaksi.create')
                ->with('success', 'Layanan berhasil diupdate');
        }

        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil diupdate');
    }

    // =========================
    // UPDATE LAYANAN - ADMIN
    // Memperbarui data layanan dari panel Admin utama
    // =========================
    public function update(Request $request, $id)
    {
        requirePermission('layanan', 'edit');
        
        $layanan = Layanan::findOrFail($id);

        $layanan->update([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => implode(',', $request->proses ?? []),
        ]);

        // Perulangan: update jenis layanan lama berdasarkan ID sebagai key array
        if ($request->has('jenis_lama')) {
            foreach ($request->jenis_lama as $idJenis => $data) {
                $jenis = JenisLayanan::find($idJenis);
                if ($jenis) {
                    $jenis->update([
                        'nama_jenis'  => $data['nama']        ?? $jenis->nama_jenis,
                        'harga'       => $data['harga']       ?? $jenis->harga,
                        'id_satuan'   => $data['id_satuan']   ?? $jenis->id_satuan,
                        'lama'        => $data['lama']        ?? $jenis->lama,
                        'lama_satuan' => $data['lama_satuan'] ?? $jenis->lama_satuan,
                        'keterangan'  => $data['keterangan']  ?? $jenis->keterangan,
                    ]);
                }
            }
        }

        // Perulangan: simpan jenis baru dari session ke database
        $jenisBaru = session()->get("jenis_baru_{$id}", []);
        foreach ($jenisBaru as $jb) {
            JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama_jenis']  ?? null,
                'harga'       => $jb['harga']        ?? 0,
                'id_satuan'   => $jb['id_satuan']   ?? null,
                'lama'        => $jb['lama']          ?? null,
                'lama_satuan' => $jb['lama_satuan']  ?? null,
                'keterangan'  => $jb['keterangan']  ?? null,
                'gambar'      => $jb['gambar']        ?? null,
            ]);
        }

        // Update jenis spesifik jika dikirim dari form inline edit
        if ($request->has('id_jenis_layanan')) {
            $jenis = JenisLayanan::find($request->id_jenis_layanan);

            if ($jenis) {
                $jenis->update([
                    'nama_jenis'  => $request->nama_jenis,
                    'harga'       => $request->harga,
                    'id_satuan'   => $request->id_satuan,
                    'lama'        => $request->lama,
                    'lama_satuan' => $request->lama_satuan,
                    'keterangan'  => $request->keterangan,
                ]);

                // Jika ada gambar baru, hapus lama lalu simpan yang baru
                if ($request->hasFile('gambar')) {
                    $file     = $request->file('gambar');
                    $namaFile = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/jenis', $namaFile);
                    $jenis->update(['gambar' => $namaFile]);
                }
            }
        }

        session()->forget("jenis_baru_{$id}");

        // Redirect ke transaksi jika update dipicu dari halaman transaksi
        if ($request->from === 'transaksi') {
            return redirect()->route('transaksi.create')
                ->with('success', 'Layanan berhasil diupdate');
        }

        // Redirect ke halaman index sesuai guard yang aktif
        if (Auth::guard('kasir')->check()) {
            return redirect()->route('kasir.layanan.index')
                ->with('success', 'Layanan berhasil diupdate');
        }

        return redirect()->route('layanan.index')
            ->with('success', 'Layanan berhasil diupdate');
    }

    // =========================
    // DELETE LAYANAN - ADMIN
    // Menghapus layanan beserta semua jenis-jenisnya
    // =========================
    public function destroy($id)
    {
        requirePermission('layanan', 'delete');
        
        $layanan = Layanan::findOrFail($id);
        
        // Hapus semua jenis layanan yang terkait terlebih dahulu (hindari orphan data)
        JenisLayanan::where('id_layanan', $layanan->id_layanan)->delete();
        
        // Hapus layanan utamanya
        $layanan->delete();

        return redirect()->route('layanan.index')
            ->with('success', 'Layanan berhasil dihapus');
    }

    // =========================
    // DELETE LAYANAN - KASIR
    // =========================
    public function destroyKasir($id)
    {
        requirePermission('layanan', 'delete');
        
        $layanan = Layanan::findOrFail($id);
        JenisLayanan::where('id_layanan', $layanan->id_layanan)->delete();
        $layanan->delete();

        return redirect()->route('kasir.layanan.index')
            ->with('success', 'Layanan berhasil dihapus!');
    }

    // =========================
    // DELETE LAYANAN - ADMIN2
    // =========================
    public function destroyAdmin2($id)
    {
        requirePermission('layanan', 'delete');
        
        $layanan = Layanan::findOrFail($id);
        JenisLayanan::where('id_layanan', $layanan->id_layanan)->delete();
        $layanan->delete();

        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil dihapus!');
    }

    // =========================
    // DUPLICATE LAYANAN - ADMIN
    // Menduplikasi layanan beserta seluruh jenis-jenisnya
    // replicate() menyalin semua atribut model tanpa primary key
    // =========================
    public function duplicate($id)
    {
        // Duplikasi dianggap sebagai aksi 'tambah' sehingga cek izin 'add'
        requirePermission('layanan', 'add');
        
        // Eager load jenis agar bisa diiterasi setelah duplikasi
        $layanan = Layanan::with('jenis')->findOrFail($id);

        // Buat salinan layanan utama dengan nama tambahan ' (Copy)'
        $new = $layanan->replicate();
        $new->nama_layanan .= ' (Copy)';
        $new->save();

        // Perulangan: duplikasi setiap jenis layanan ke layanan salinan
        foreach ($layanan->jenis as $jenis) {
            $j = $jenis->replicate(); // salin semua atribut jenis
            $j->id_layanan = $new->id_layanan; // ganti ke ID layanan baru
            $j->save();
        }

        return redirect()->back()->with('success', 'Layanan berhasil diduplikat!');
    }

    // =========================
    // DUPLICATE LAYANAN - KASIR
    // =========================
    public function duplicateKasir($id)
    {
        requirePermission('layanan', 'add');
        
        $layanan = Layanan::with('jenis')->findOrFail($id);

        $new = $layanan->replicate();
        $new->nama_layanan .= ' (Copy)';
        $new->save();

        // Perulangan: salin setiap jenis layanan ke entitas layanan baru
        foreach ($layanan->jenis as $jenis) {
            $j = $jenis->replicate();
            $j->id_layanan = $new->id_layanan;
            $j->save();
        }

        return redirect()->route('kasir.layanan.index')
            ->with('success', 'Layanan berhasil diduplikat!');
    }

    // =========================
    // DUPLICATE LAYANAN - ADMIN2
    // =========================
    public function duplicateAdmin2($id)
    {
        requirePermission('layanan', 'add');
        
        $layanan = Layanan::with('jenis')->findOrFail($id);

        $new = $layanan->replicate();
        $new->nama_layanan .= ' (Copy)';
        $new->save();

        // Perulangan: salin setiap jenis layanan ke layanan duplikat
        foreach ($layanan->jenis as $jenis) {
            $j = $jenis->replicate();
            $j->id_layanan = $new->id_layanan;
            $j->save();
        }

        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil diduplikat!');
    }

    // =========================
    // CREATE JENIS LAYANAN - ADMIN
    // Menampilkan form tambah jenis layanan baru untuk layanan tertentu
    // =========================
    public function createJenis($id_layanan, Request $request)
    {
        requirePermission('layanan', 'add');
        
        // Mode: 'create' untuk tambah baru, 'edit' untuk edit jenis yang sudah ada
        $mode     = $request->query('mode', 'create');
        $from     = $request->query('from', $id_layanan);
        $satuan   = Satuan::all();
        $id_jenis = $request->query('id_jenis');

        return view('admin.tambah_jenis_layanan_create', compact('id_layanan', 'mode', 'from', 'satuan'));
    }

    // =========================
    // EDIT JENIS - ADMIN
    // Menampilkan form edit jenis layanan berdasarkan ID jenis
    // =========================
    public function editJenis($id_jenis)
    {
        requirePermission('layanan', 'edit');
        
        $jenis      = \App\Models\JenisLayanan::findOrFail($id_jenis);
        $satuan     = \App\Models\Satuan::all();
        $id_layanan = $jenis->id_layanan;

        return view('admin.edit_jenis_layanan', compact('jenis', 'satuan', 'id_layanan'));
    }

    // =========================
    // CREATE JENIS LAYANAN - KASIR
    // Menampilkan form tambah/edit jenis layanan untuk panel Kasir
    // =========================
    public function createJenisKasir($id_layanan, Request $request)
    {
        requirePermission('layanan', 'add');
        
        $mode     = $request->query('mode', 'create');
        $from     = $request->query('from', $id_layanan);
        $satuan   = Satuan::all();
        $id_jenis = $request->query('id_jenis');

        // Percabangan: tampilkan view berbeda tergantung mode create atau edit
        if ($mode === 'edit') {
            return view('kasir.layanan.tambah_jenis_layanan_edit',
                compact('id_layanan', 'mode', 'from', 'satuan', 'id_jenis'));
        } else {
            return view('kasir.layanan.tambah_jenis_layanan_create',
                compact('id_layanan', 'mode', 'from', 'satuan'));
        }
    }

    // =========================
    // CREATE JENIS LAYANAN - ADMIN2
    // =========================
    public function createJenisAdmin2($id_layanan, Request $request)
    {
        requirePermission('layanan', 'add');
        
        $admin2 = Auth::guard('admin')->user();
        if (!$admin2 || $admin2->role_id != 2) {
            abort(403, 'Hanya admin biasa yang bisa akses');
        }

        $mode     = $request->query('mode', 'create');
        $from     = $request->query('from', $id_layanan);
        $id_jenis = $request->query('id_jenis');
        $satuan   = Satuan::all();

        // Percabangan: pilih view berdasarkan mode yang diterima
        if ($mode === 'edit') {
            return view('admin2.layanan.tambah_jenis_layanan_edit',
                compact('id_layanan', 'mode', 'from', 'satuan', 'id_jenis'));
        }

        return view('admin2.layanan.tambah_jenis_layanan_create',
            compact('id_layanan', 'mode', 'from', 'satuan'));
    }

    // =========================
    // STORE JENIS - ADMIN
    // Menyimpan jenis layanan baru ke dalam session (belum ke DB)
    // Data akan disimpan ke DB saat layanan di-store
    // =========================
    public function storeJenis(Request $request)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|integer',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        // Key session untuk layanan baru (id = 0)
        $key       = 'jenis_baru_0';
        $jenis_baru = session()->get($key, []); // ambil array lama, default kosong

        // Proses upload gambar jika ada
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file       = $request->file('gambar');
            $filename   = time() . '_' . $file->getClientOriginalName();
            $path       = $file->storeAs('jenis', $filename, 'public');
            $gambarPath = $path;
        }

        // Tambahkan jenis baru ke dalam array session (append)
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarPath,
        ];

        // Simpan array yang sudah diperbarui kembali ke session
        session()->put($key, $jenis_baru);

        return redirect()->route('layanan.create', ['from' => $request->from ?? 'transaksi'])
            ->with('success', 'Jenis layanan berhasil ditambahkan.');
    }

    // =========================
    // STORE JENIS - KASIR
    // Menyimpan jenis layanan sementara ke session untuk panel Kasir
    // =========================
    public function storeJenisKasir(Request $request, $id_layanan)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|integer',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        $gambarName = null;
        if ($request->hasFile('gambar')) {
            $file       = $request->file('gambar');
            $gambarName = time() . '_' . $file->getClientOriginalName();
            
            // Buat folder jika belum ada sebelum menyimpan gambar
            if (!file_exists(public_path('images/jenis'))) {
                mkdir(public_path('images/jenis'), 0755, true);
            }
            
            $file->move(public_path('images/jenis'), $gambarName);
        }

        // Ambil array session berdasarkan id_layanan, lalu append data baru
        $jenis_baru   = session()->get("jenis_baru_{$id_layanan}", []);
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
        ];

        session()->put("jenis_baru_{$id_layanan}", $jenis_baru);

        return redirect()->route('kasir.layanan.layanan_create', ['from' => $request->from ?? 'transaksi'])
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // =========================
    // STORE JENIS - ADMIN2
    // =========================
    public function storeJenisAdmin2(Request $request, $id_layanan)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|integer',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        $gambarName = null;
        if ($request->hasFile('gambar')) {
            $file       = $request->file('gambar');
            $gambarName = time() . '_' . $file->getClientOriginalName();
            
            if (!file_exists(public_path('images/jenis'))) {
                mkdir(public_path('images/jenis'), 0755, true);
            }
            
            $file->move(public_path('images/jenis'), $gambarName);
        }

        // Append data jenis baru ke array session berdasarkan id_layanan
        $jenis_baru   = session()->get("jenis_baru_{$id_layanan}", []);
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarName,
        ];

        session()->put("jenis_baru_{$id_layanan}", $jenis_baru);

        return redirect()->route('admin2.layanan.create')
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // =========================
    // EDIT JENIS - KASIR
    // Menampilkan form edit jenis layanan untuk panel Kasir
    // =========================
    public function editJenisKasir($id)
    {
        requirePermission('layanan', 'edit');
        
        $jenis  = JenisLayanan::findOrFail($id);
        $satuan = Satuan::all();

        return view('kasir.layanan.edit_jenis_layanan', [
            'jenis'  => $jenis,
            'satuan' => $satuan,
            'from'   => request('from')
        ]);
    }

    // =========================
    // EDIT JENIS - ADMIN2
    // =========================
    public function editJenisAdmin2($id)
    {
        requirePermission('layanan', 'edit');
        
        $jenis  = JenisLayanan::findOrFail($id);
        $satuan = Satuan::all();
        
        return view('admin2.layanan.edit_jenis_layanan', [
            'jenis'  => $jenis,
            'satuan' => $satuan,
        ]);
    }

    // =========================
    // UPDATE JENIS - ADMIN2
    // Memperbarui data jenis layanan termasuk upload gambar baru
    // =========================
    public function updateJenisAdmin2(Request $request, $id)
    {
        requirePermission('layanan', 'edit');
        
        $jenis = JenisLayanan::findOrFail($id);
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string|max:50',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        // Default: gunakan gambar lama jika tidak ada upload baru
        $gambarPath = $jenis->gambar;

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama dari storage jika ada, sebelum simpan yang baru
            if ($jenis->gambar && Storage::disk('public')->exists($jenis->gambar)) {
                Storage::disk('public')->delete($jenis->gambar);
            }
            $file       = $request->file('gambar');
            $filename   = time() . '_' . $file->getClientOriginalName();
            $gambarPath = $file->storeAs('jenis', $filename, 'public');
        }

        $jenis->update([
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarPath, // bisa gambar lama atau baru
        ]);

        return redirect()->route('admin2.layanan.edit', $request->from ?? $jenis->id_layanan)
            ->with('success', 'Jenis layanan berhasil diperbarui.');
    }

    // =========================
    // UPDATE JENIS - KASIR
    // =========================
    public function updateJenisKasir(Request $request, $id)
    {
        requirePermission('layanan', 'edit');
        
        $jenis = JenisLayanan::findOrFail($id);
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string|max:50',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        // Default ke gambar lama jika tidak ada upload
        $gambarPath = $jenis->gambar;

        if ($request->hasFile('gambar')) {
            // Hapus file gambar lama dari disk sebelum simpan yang baru
            if ($jenis->gambar && Storage::disk('public')->exists($jenis->gambar)) {
                Storage::disk('public')->delete($jenis->gambar);
            }
            $file       = $request->file('gambar');
            $filename   = time() . '_' . $file->getClientOriginalName();
            $gambarPath = $file->storeAs('jenis', $filename, 'public');
        }

        $jenis->update([
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarPath,
        ]);

        return redirect()->route('kasir.layanan.edit', $request->from ?? $jenis->id_layanan)
            ->with('success', 'Jenis layanan berhasil diperbarui.');
    }

    // =========================
    // UPDATE JENIS - ADMIN
    // =========================
    public function updateJenis(Request $request, $id)
    {
        requirePermission('layanan', 'edit');
        
        $jenis = JenisLayanan::findOrFail($id);

        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string|max:50',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        // Default: pertahankan gambar lama
        $gambarPath = $jenis->gambar;

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada sebelum menyimpan gambar baru
            if ($jenis->gambar && \Storage::disk('public')->exists($jenis->gambar)) {
                \Storage::disk('public')->delete($jenis->gambar);
            }

            $file       = $request->file('gambar');
            $filename   = time() . '_' . $file->getClientOriginalName();
            $gambarPath = $file->storeAs('jenis', $filename, 'public');
        }

        // Update semua field jenis, gambar selalu disertakan (lama atau baru)
        $jenis->update([
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarPath,
        ]);

        return redirect()->route('layanan.edit', $request->from ?? $jenis->id_layanan)
            ->with('success', 'Jenis layanan berhasil diperbarui.');
    }

    // =========================
    // SESSION METHODS - ADMIN
    // =========================

    // Menghapus semua data jenis sementara dari session
    public function clearJenisSession()
    {
        session()->forget('jenis_baru');
        return back();
    }

    // Menambahkan jenis layanan ke session saat proses create (tanpa simpan ke DB dulu)
    public function sessionCreateJenis(Request $request, $id)
    {
        requirePermission('layanan', 'add');
        
        $admin = Auth::guard('admin')->user();
        if (!$admin) abort(403);

        // Ambil array jenis yang sudah ada di session untuk layanan ini
        $jenisBaru = session()->get("jenis_baru_{$id}", []);
        
        // Append data jenis baru ke array session
        $jenisBaru[] = [
            'nama_jenis'  => $request->nama_jenis  ?? null,
            'harga'       => $request->harga        ?? 0,
            'id_satuan'   => $request->id_satuan   ?? null,
            'lama'        => $request->lama          ?? null,
            'lama_satuan' => $request->lama_satuan  ?? null,
            'keterangan'  => $request->keterangan  ?? null,
        ];

        session()->put("jenis_baru_{$id}", $jenisBaru);

        return redirect()->back()->with('success', 'Jenis layanan berhasil ditambahkan ke session');
    }

    // Validasi dan simpan jenis baru ke session saat layanan baru dibuat
    public function sessionStoreJenis(Request $request, $from)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        $key        = 'jenis_baru_0';
        $jenis_baru = session()->get($key, []); // array jenis sementara

        // Proses upload gambar jika ada file yang dikirim
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file       = $request->file('gambar');
            $filename   = time() . '_' . $file->getClientOriginalName();
            $path       = $file->storeAs('jenis', $filename, 'public');
            $gambarPath = $path;
        }

        // Tambahkan ke array dan simpan kembali ke session
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarPath,
        ];

        session()->put($key, $jenis_baru);

        return redirect()->route('layanan.create')
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // Menambahkan jenis ke session saat proses EDIT layanan yang sudah ada
    public function addJenisEdit(Request $request, $from)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'nullable|numeric',
            'lama_satuan' => 'nullable|string',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file       = $request->file('gambar');
            $filename   = time() . '_' . $file->getClientOriginalName();
            $path       = $file->storeAs('jenis', $filename, 'public');
            $gambarPath = $path;
        }

        // Ambil array jenis dari session untuk layanan yang sedang diedit
        $jenis   = session()->get("jenis_baru_{$from}", []);
        $jenis[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambarPath,
        ];

        session()->put("jenis_baru_{$from}", $jenis);

        return redirect()->route('layanan.edit', $from)
            ->with('success', 'Jenis layanan berhasil ditambahkan!');
    }

    // Menambahkan jenis ke session saat edit layanan di panel Admin2
    public function addJenisEditAdmin2(Request $request, $from)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'nullable|numeric',
            'lama_satuan' => 'nullable|string',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        // Upload gambar ke folder public/images
        $gambar = null;
        if ($request->hasFile('gambar')) {
            $filename = time().'_'.$request->file('gambar')->getClientOriginalName();
            $request->file('gambar')->move(public_path('images'), $filename);
            $gambar = $filename;
        }

        // Append ke array session berdasarkan ID layanan yang sedang diedit
        $jenis   = session()->get("jenis_baru_{$from}", []);
        $jenis[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambar,
        ];

        session()->put("jenis_baru_{$from}", $jenis);

        return redirect()->route('admin2.layanan.edit', $from)
            ->with('success', 'Jenis layanan berhasil ditambahkan!');
    }

    // Menampilkan form tambah jenis untuk layanan yang sedang diedit (Admin)
    public function addJenisSessionForm($from)
    {
        requirePermission('layanan', 'add');
        
        $satuanList = \App\Models\Satuan::all();

        return view('admin.tambah_jenis_layanan_edit', [
            'from'       => $from,
            'satuanList' => $satuanList
        ]);
    }

    // =========================
    // SESSION METHODS - KASIR
    // =========================

    // Menampilkan form tambah/edit jenis layanan di session untuk panel Kasir
    public function sessionCreateJenisKasir($from, Request $request)
    {
        requirePermission('layanan', 'add');
        
        $mode   = $request->query('mode', 'create');
        $satuan = Satuan::all();

        // Percabangan: tampilkan view edit jika mode = 'edit'
        if ($mode === 'edit') {
            $id_jenis = $request->query('id_jenis');

            if (!$id_jenis) {
                abort(404, 'ID jenis tidak ditemukan');
            }

            return view('kasir.layanan.tambah_jenis_layanan_edit', [
                'from'     => $from,
                'satuan'   => $satuan,
                'id_jenis' => $id_jenis
            ]);
        }

        // Jika mode create, langsung redirect kembali ke halaman edit
        return redirect()->route('kasir.layanan.edit', $from)
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // Menyimpan jenis baru ke session saat edit layanan di panel Kasir
    public function sessionStoreJenisKasir(Request $request, $from)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan'  => 'nullable|string',
        ]);

        // Ambil array jenis dari session, append data baru, lalu simpan kembali
        $jenis_baru   = session()->get("jenis_baru_{$from}", []);
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
        ];

        session()->put("jenis_baru_{$from}", $jenis_baru);

        return redirect()->route('kasir.layanan.layanan_create', ['from' => 'dashboard'])
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // Simpan jenis baru ke session kasir dengan dukungan upload gambar
    public function storeJenisKasirSession(Request $request, $from)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        // Upload gambar ke folder public/images
        $gambar = null;
        if ($request->hasFile('gambar')) {
            $file     = $request->file('gambar');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $gambar   = $filename;
        }

        // Append data jenis baru ke array session
        $jenis_baru   = session()->get("jenis_baru_{$from}", []);
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
            'gambar'      => $gambar,
        ];

        session()->put("jenis_baru_{$from}", $jenis_baru);

        return redirect()->route('kasir.layanan.layanan_create')
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // =========================
    // SESSION METHODS - ADMIN2
    // =========================

    // Menambahkan jenis ke session untuk layanan yang sedang dikelola Admin2
    public function sessionCreateJenisAdmin2(Request $request, $id)
    {
        requirePermission('layanan', 'add');
        
        $admin2 = Auth::guard('admin')->user();
        if (!$admin2 || $admin2->role_id != 2) {
            abort(403, 'Hanya admin biasa yang bisa akses');
        }

        // Ambil array jenis dari session lalu append data baru
        $jenisBaru   = session()->get("jenis_baru_{$id}", []);
        $jenisBaru[] = [
            'nama_jenis'  => $request->nama_jenis  ?? null,
            'harga'       => $request->harga        ?? 0,
            'id_satuan'   => $request->id_satuan   ?? null,
            'lama'        => $request->lama          ?? null,
            'lama_satuan' => $request->lama_satuan  ?? null,
            'keterangan'  => $request->keterangan  ?? null,
        ];

        session()->put("jenis_baru_{$id}", $jenisBaru);

        return redirect()->back()->with('success', 'Jenis layanan berhasil ditambahkan ke session');
    }

    // Validasi dan simpan jenis baru ke session Admin2 saat proses create
    public function sessionStoreJenisAdmin2(Request $request, $from)
    {
        requirePermission('layanan', 'add');
        
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan'  => 'nullable|string',
        ]);

        // Append ke array session yang sudah ada
        $jenis_baru   = session()->get("jenis_baru_{$from}", []);
        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
        ];

        session()->put("jenis_baru_{$from}", $jenis_baru);

        return redirect()->route('admin2.layanan.layanan_create', ['from' => 'dashboard'])
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    // =========================
    // RIWAYAT METHOD
    // Menambahkan layanan ke dalam session riwayat transaksi
    // =========================
    public function addLayanan(Request $request, $id)
    {
        // Cek izin hanya untuk kasir (admin tidak dibatasi di sini)
        if (Auth::guard('kasir')->check()) {
            requirePermission('layanan', 'add');
        }

        $idRiwayat = $id;
        $idLayanan = $request->id_layanan;

        // Validasi: id_layanan wajib dikirim lewat request
        if (!$idLayanan) {
            return response()->json([
                'success' => false,
                'message' => 'ID layanan tidak ditemukan'
            ], 422);
        }

        // Cari layanan berdasarkan ID
        $layanan = \App\Models\Layanan::find($idLayanan);
        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan'
            ], 404);
        }

        // Ambil jenis pertama dari layanan (default)
        $jenis = $layanan->jenis()->first();
        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan'
            ], 404);
        }

        $qty   = $request->qty ?? 1;  // default qty = 1 jika tidak diisi
        $parfum = $request->parfum;

        // Key session unik per riwayat transaksi
        $key = "riwayat_{$idRiwayat}_layanan";

        // Ambil array layanan yang sudah ada di session riwayat ini
        $riwayatLayanan = session()->get($key, []);

        // Append layanan yang dipilih ke dalam array riwayat session
        $riwayatLayanan[] = [
            'id_layanan'   => $layanan->id_layanan,
            'nama_layanan' => $layanan->nama_layanan,
            'qty'          => $qty,
            'harga'        => $jenis->harga * $qty,  // harga total = harga satuan × qty
            'jenis'        => $jenis->nama_jenis,
            'satuan'       => $jenis->satuan->nama_satuan ?? '-',
            'id_parfum'    => $parfum,
        ];

        session()->put($key, $riwayatLayanan);

        return response()->json(['success' => true]);
    }

    // Simpan ID layanan ke session lalu redirect ke form transaksi
    public function fromLayanan(Request $request)
    {
        $layanan_id = $request->layanan_id;
        
        // Simpan ID layanan yang dipilih ke session untuk digunakan di form transaksi
        session(['selected_layanan_for_qty' => $layanan_id]);
        
        return redirect()->route('transaksi.create');
    }
}
