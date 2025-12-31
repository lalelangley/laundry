<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Layanan;
use App\Models\JenisLayanan;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuRole;
use App\Models\Menu;
use App\Models\Satuan;  

class LayananController extends Controller
{
    // =========================
    // INDEX LAYANAN
    // =========================
    public function index(Request $request)
{
    $parfum = \App\Models\Parfum::all();
    $layananUtama = Layanan::with(['jenis.satuan'])->get();

    // ======================
    // KASIR
    // ======================
    if (Auth::guard('kasir')->check()) {
        $kasir = Auth::guard('kasir')->user();

        return view('kasir.layanan.layanan', [
            'layananUtama'     => $layananUtama,
            'parfum'           => $parfum,
            'from'             => 'kasir',
            'canAddLayanan'    => true,
            'canEditLayanan'   => true,
            'canDeleteLayanan' => true,
        ]);
    }

    // ======================
    // ADMIN (SUPER)
    // ======================
    if (Auth::guard('admin')->check()) {
        return view('admin.layanan', [
            'layananUtama' => $layananUtama,
            'parfum'       => $parfum,
            'from'         => 'admin',
        ]);
    }

    abort(403);
}

// =========================
// INDEX LAYANAN - ADMIN2
// =========================
public function indexAdmin2(Request $request)
{
    $admin2 = Auth::guard('admin2')->user();
    if (!$admin2) {
        abort(403);
    }

    $layananUtama = Layanan::with(['jenis.satuan'])->get();
    $parfum = \App\Models\Parfum::all();

    return view('admin2.layanan.layanan', [
        'layananUtama' => $layananUtama,
        'parfum'       => $parfum,
        'from'         => 'admin2',
    ]);
}

    // =========================
    // CREATE LAYANAN - ADMIN
    // =========================
    public function create()
    {
        $layanan_id = 0;
        $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);

        $jenisLama = JenisLayanan::selectRaw('MIN(id_jenis_layanan) as id_jenis_layanan, nama_jenis, harga, id_satuan, lama, lama_satuan, keterangan')
            ->groupBy('nama_jenis', 'harga', 'id_satuan', 'lama', 'lama_satuan', 'keterangan')
            ->orderBy('nama_jenis')
            ->get();

        $satuan = Satuan::all();
        $parfum = \App\Models\Parfum::all();

        return view('admin.layanan_create', compact('jenisBaru', 'jenisLama', 'satuan', 'parfum'))->with('from', request('from'));
    }

    // =========================
    // CREATE LAYANAN - KASIR
    // =========================
    public function CreateKasir(Request $request)
    {
        if (!$this->kasirCanAddLayanan()) {
            abort(403);
        }

        $layanan_id = 0;
        $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);
        $satuan = Satuan::all();
        $parfum = \App\Models\Parfum::all();

        return view('kasir.layanan.layanan_create', compact(
            'jenisBaru',
            'satuan',
            'parfum'
        ));
    }

    // =========================
    // CREATE LAYANAN - ADMIN2
    // =========================
    public function createAdmin2(Request $request)
    {
        if (!$this->admin2CanAddLayanan()) {
            abort(403);
        }

        $layanan_id = 0;
        $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);
        $satuan = Satuan::all();
        $parfum = \App\Models\Parfum::all();

        return view('admin2.layanan.layanan_create', compact(
        'jenisBaru',
        'satuan',
        'parfum'
    ));
    }

    
    // =========================
    // STORE LAYANAN - ADMIN
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
        ]);

        $from = $request->input('from');
        $layanan_id = 0;
        $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);
        $jenisLamaDipilih = $request->input('jenis_lama', []);

        if (count($jenisBaru) == 0 && count($jenisLamaDipilih) == 0) {
            return back()
                ->withErrors(['jenis_kosong' => 'Pilih minimal 1 jenis layanan.'])
                ->withInput();
        }

        $prosesInput = $request->input('proses', []);
        $prosesToStore = [];
        if (is_array($prosesInput)) {
            $prosesToStore = array_map('trim', $prosesInput);
        } elseif (!empty($prosesInput)) {
            $prosesToStore = array_map('trim', explode(',', $prosesInput));
        }

        $layanan = \App\Models\Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'proses' => json_encode(array_values(array_filter($prosesToStore))),
        ]);

        foreach ($jenisBaru as $jb) {
            \App\Models\JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama_jenis'] ?? $jb['nama'] ?? null,
                'harga'       => $jb['harga'] ?? 0,
                'id_satuan'   => $jb['id_satuan'] ?? null,
                'lama'        => $jb['lama'] ?? null,
                'lama_satuan' => $jb['lama_satuan'] ?? null,
                'keterangan'  => $jb['keterangan'] ?? null,
                'gambar'      => $jb['gambar'] ?? null,
            ]);
        }

        if (!empty($jenisLamaDipilih)) {
            foreach ($jenisLamaDipilih as $idJenis) {
                $jenis = \App\Models\JenisLayanan::find($idJenis);
                if ($jenis) {
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

        session()->forget("jenis_baru_{$layanan_id}");

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
    // =========================
    public function storeKasir(Request $request)
    {
        if (!$this->kasirCanAddLayanan()) {
            abort(403, 'Anda tidak memiliki hak akses menambah layanan');
        }

        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'jenis_lama' => 'array',
            'proses' => 'array|required',
        ]);

        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
        ]);

        if($request->has('jenis_lama')) {
            foreach($request->jenis_lama as $idJenis) {
                $jenis = JenisLayanan::find($idJenis);
                if($jenis) {
                    JenisLayanan::create([
                        'id_layanan' => $layanan->id_layanan,
                        'nama_jenis' => $jenis->nama_jenis,
                        'harga'      => $jenis->harga,
                        'id_satuan'  => $jenis->id_satuan,
                        'lama'       => $jenis->lama,
                        'lama_satuan'=> $jenis->lama_satuan,
                        'keterangan' => $jenis->keterangan,
                        'gambar'     => $jenis->gambar,
                    ]);
                }
            }
        }

        $jenisBaru = session()->get("jenis_baru_0", []);
        foreach($jenisBaru as $jb) {
            $layanan->jenis()->create([
                'nama_jenis' => $jb['nama_jenis'],
                'harga' => $jb['harga'],
                'id_satuan' => $jb['id_satuan'] ?? null,
                'lama' => $jb['lama'] ?? null,
                'lama_satuan' => $jb['lama_satuan'] ?? null,
                'keterangan' => $jb['keterangan'] ?? null,
            ]);
        }

        session()->forget("jenis_baru_0");

        return redirect()->route('kasir.layanan.index')
            ->with('success', 'Layanan berhasil dibuat!');
    }

    /**
 * Store Layanan - Admin2 (FIXED NULL VALUES)
 */
public function storeAdmin2(Request $request)
{
    if (!$this->admin2CanAddLayanan()) {
        abort(403, 'Anda tidak memiliki hak akses menambah layanan');
    }

    $request->validate([
        'nama_layanan' => 'required|string|max:255',
        'jenis_lama' => 'array',
        'proses' => 'array|required',
    ]);

    $layanan = Layanan::create([
        'nama_layanan' => $request->nama_layanan,
        'proses' => implode(',', $request->proses),
    ]);

    // Handle jenis lama
    if ($request->has('jenis_lama')) {
        foreach ($request->jenis_lama as $idJenis) {
            $jenis = JenisLayanan::find($idJenis);
            if ($jenis) {
                JenisLayanan::create([
                    'id_layanan' => $layanan->id_layanan,
                    'nama_jenis' => $jenis->nama_jenis,
                    'harga'      => $jenis->harga,
                    'id_satuan'  => $jenis->id_satuan,
                    'lama'       => $jenis->lama,
                    'lama_satuan'=> $jenis->lama_satuan,
                    'keterangan' => $jenis->keterangan,
                    'gambar'     => $jenis->gambar,
                ]);
            }
        }
    }

    // Handle jenis baru dari session
    $jenisBaru = session()->get("jenis_baru_0", []);
    
    // Filter out empty/invalid data
    $jenisBaru = array_filter($jenisBaru, function($jb) {
        return !empty($jb['nama_jenis']) && !empty($jb['harga']) && !empty($jb['id_satuan']);
    });

    foreach ($jenisBaru as $jb) {
        $layanan->jenis()->create([
            'nama_jenis' => $jb['nama_jenis'],
            'harga' => $jb['harga'] ?? 0,
            'id_satuan' => $jb['id_satuan'],
            'lama' => $jb['lama'] ?? null,
            'lama_satuan' => $jb['lama_satuan'] ?? null,
            'keterangan' => $jb['keterangan'] ?? null,
            'gambar' => $jb['gambar'] ?? null,
        ]);
    }

    // Clear session
    session()->forget("jenis_baru_0");

    return redirect()->route('admin2.layanan.index')
        ->with('success', 'Layanan berhasil dibuat!');
}

    // =========================
    // EDIT LAYANAN
    // =========================
   public function edit($id)
{
    if ($id == 0) {
        return redirect()->route('layanan.create');
    }

    $layanan   = Layanan::with('jenis')->findOrFail($id);
    $jenisBaru = session()->get("jenis_baru_{$id}", []);

    // =====================
    // KASIR
    // =====================
    if (Auth::guard('kasir')->check()) {
        return view('kasir.layanan.layanan_edit', compact('layanan', 'jenisBaru'));
    }


    // =====================
    // ADMIN (SUPER / BIASA)
    // =====================
    if (Auth::guard('admin')->check()) {
        return view('admin.layanan_edit', compact('layanan', 'jenisBaru'));
    }

    abort(403);
}

public function editAdmin2($id)
{
    $admin2 = Auth::guard('admin2')->user();
    if (!$admin2) {
        abort(403);
    }

    $layanan = Layanan::with('jenis.satuan')->findOrFail($id);

    return view('admin2.layanan.layanan_edit', [
        'layanan' => $layanan,
        'from'    => 'admin2',
    ]);
}

 public function updateAdmin2(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        $layanan->update([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => implode(',', $request->proses ?? []),
        ]);

        if ($request->has('jenis_lama')) {
            foreach ($request->jenis_lama as $idJenis => $data) {
                $jenis = JenisLayanan::find($idJenis);
                if ($jenis) {
                    $jenis->update([
                        'nama_jenis' => $data['nama'] ?? $jenis->nama_jenis,
                        'harga'      => $data['harga'] ?? $jenis->harga,
                        'id_satuan'  => $data['id_satuan'] ?? $jenis->id_satuan,
                        'lama'       => $data['lama'] ?? $jenis->lama,
                        'lama_satuan'=> $data['lama_satuan'] ?? $jenis->lama_satuan,
                        'keterangan' => $data['keterangan'] ?? $jenis->keterangan,
                    ]);
                }
            }
        }

        $jenisBaru = session()->get("jenis_baru_{$id}", []);
        foreach ($jenisBaru as $jb) {
            JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama_jenis'] ?? null,
                'harga'       => $jb['harga'] ?? 0,
                'id_satuan'   => $jb['id_satuan'] ?? null,
                'lama'        => $jb['lama'] ?? null,
                'lama_satuan' => $jb['lama_satuan'] ?? null,
                'keterangan'  => $jb['keterangan'] ?? null,
                'gambar'      => $jb['gambar'] ?? null,
            ]);
        }

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

                if ($request->hasFile('gambar')) {
                    $file = $request->file('gambar');
                    $namaFile = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/jenis', $namaFile);
                    $jenis->update(['gambar' => $namaFile]);
                }
            }
        }

        session()->forget("jenis_baru_{$id}");

        if ($request->from === 'transaksi') {
            return redirect()->route('admin2.transaksi.create')->with('success', 'Layanan berhasil diupdate');
        }
        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil diupdate');
    }
    // =========================
    // UPDATE LAYANAN
    // =========================
    public function update(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);

        $layanan->update([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => implode(',', $request->proses ?? []),
        ]);

        if ($request->has('jenis_lama')) {
            foreach ($request->jenis_lama as $idJenis => $data) {
                $jenis = JenisLayanan::find($idJenis);
                if ($jenis) {
                    $jenis->update([
                        'nama_jenis' => $data['nama'] ?? $jenis->nama_jenis,
                        'harga'      => $data['harga'] ?? $jenis->harga,
                        'id_satuan'  => $data['id_satuan'] ?? $jenis->id_satuan,
                        'lama'       => $data['lama'] ?? $jenis->lama,
                        'lama_satuan'=> $data['lama_satuan'] ?? $jenis->lama_satuan,
                        'keterangan' => $data['keterangan'] ?? $jenis->keterangan,
                    ]);
                }
            }
        }

        $jenisBaru = session()->get("jenis_baru_{$id}", []);
        foreach ($jenisBaru as $jb) {
            JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $jb['nama_jenis'] ?? null,
                'harga'       => $jb['harga'] ?? 0,
                'id_satuan'   => $jb['id_satuan'] ?? null,
                'lama'        => $jb['lama'] ?? null,
                'lama_satuan' => $jb['lama_satuan'] ?? null,
                'keterangan'  => $jb['keterangan'] ?? null,
                'gambar'      => $jb['gambar'] ?? null,
            ]);
        }

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

                if ($request->hasFile('gambar')) {
                    $file = $request->file('gambar');
                    $namaFile = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/jenis', $namaFile);
                    $jenis->update(['gambar' => $namaFile]);
                }
            }
        }

        session()->forget("jenis_baru_{$id}");

        if ($request->from === 'transaksi') {
            return redirect()->route('transaksi.create')->with('success', 'Layanan berhasil diupdate');
        }

        if (Auth::guard('kasir')->check()) {
            return redirect()->route('kasir.layanan.index')
                ->with('success', 'Layanan berhasil diupdate');
        }

        return redirect()->route('layanan.index')
            ->with('success', 'Layanan berhasil diupdate');
    }
     // =========================
    // UPDATE LAYANAN
    // =========================


    // =========================
    // CREATE JENIS LAYANAN - ADMIN
    // =========================
    public function createJenis($id_layanan, Request $request)
    {
        $mode = $request->query('mode', 'create');
        $from = $request->query('from', $id_layanan);
        $satuan = Satuan::all();
        $id_jenis = $request->query('id_jenis');

        if ($mode === 'edit') {
            return view('admin.tambah_jenis_layanan_edit', compact('id_layanan', 'mode', 'from', 'satuan', 'id_jenis'));
        } else {
            return view('admin.tambah_jenis_layanan_create', compact('id_layanan', 'mode', 'from', 'satuan'));
        }
    }

    // =========================
    // CREATE JENIS LAYANAN - KASIR
    // =========================
    public function createJenisKasir($id_layanan, Request $request)
    {
        $mode = $request->query('mode', 'create');
        $from = $request->query('from', $id_layanan);
        $satuan = Satuan::all();
        $id_jenis = $request->query('id_jenis');

        if ($mode === 'edit') {
            return view('kasir.layanan.tambah_jenis_layanan_edit', compact('id_layanan', 'mode', 'from', 'satuan', 'id_jenis'));
        } else {
            return view('kasir.layanan.tambah_jenis_layanan_create', compact('id_layanan', 'mode', 'from', 'satuan'));
        }
    }

// =========================
// CREATE JENIS LAYANAN - ADMIN2
// =========================
public function createJenisAdmin2($id_layanan, Request $request)
{
    $admin2 = Auth::guard('admin2')->user();
    if (!$admin2) abort(403);

    $mode = $request->query('mode', 'create'); // create / edit
    $from = $request->query('from', $id_layanan);
    $id_jenis = $request->query('id_jenis');
    $satuan = Satuan::all();

    if ($mode === 'edit') {
        return view('admin2.layanan.tambah_jenis_layanan_edit', compact(
            'id_layanan', 'mode', 'from', 'satuan', 'id_jenis'
        ));
    }

    return view('admin2.layanan.tambah_jenis_layanan_create', compact(
        'id_layanan', 'mode', 'from', 'satuan'
    ));
}

    // =========================
    // STORE JENIS - ADMIN
    // =========================
    public function storeJenis(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan' => 'required|integer',
            'harga' => 'required|numeric',
            'lama' => 'required|numeric',
            'lama_satuan' => 'required|string',
        ]);

        $jenis_baru = session()->get('jenis_baru', []);

        $jenis_baru[] = [
            'nama_jenis' => $request->nama_jenis,
            'id_satuan' => $request->id_satuan,
            'harga' => $request->harga,
            'lama' => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan' => $request->keterangan,
        ];

        session()->put('jenis_baru', $jenis_baru);

        return redirect()->route('layanan.create', ['from' => $request->from ?? 'transaksi'])->with('success', 'Jenis layanan berhasil ditambahkan.');
    }

    // =========================
    // STORE JENIS - KASIR
    // =========================
    public function storeJenisKasir(Request $request, $id_layanan)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan' => 'required|integer',
            'harga' => 'required|numeric',
            'lama' => 'required|numeric',
            'lama_satuan' => 'required|string',
        ]);

        $jenis_baru = session()->get("jenis_baru_{$id_layanan}", []);

        $jenis_baru[] = [
            'nama_jenis' => $request->nama_jenis,
            'id_satuan' => $request->id_satuan,
            'harga' => $request->harga,
            'lama' => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan' => $request->keterangan,
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
    $request->validate([
        'nama_jenis' => 'required|string|max:255',
        'id_satuan' => 'required|integer',
        'harga' => 'required|numeric',
        'lama' => 'required|numeric',
        'lama_satuan' => 'required|string',
        'gambar' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
    ]);

    // Handle image upload
    $gambarName = null;
    if ($request->hasFile('gambar')) {
        $file = $request->file('gambar');
        $gambarName = time() . '_' . $file->getClientOriginalName();
        
        // Pastikan folder ada
        if (!file_exists(public_path('images/jenis'))) {
            mkdir(public_path('images/jenis'), 0755, true);
        }
        
        $file->move(public_path('images/jenis'), $gambarName);
    }

    $jenis_baru = session()->get("jenis_baru_{$id_layanan}", []);

    $jenis_baru[] = [
        'nama_jenis' => $request->nama_jenis,
        'id_satuan' => $request->id_satuan,
        'harga' => $request->harga,
        'lama' => $request->lama,
        'lama_satuan' => $request->lama_satuan,
        'keterangan' => $request->keterangan,
        'gambar' => $gambarName,
    ];

    session()->put("jenis_baru_{$id_layanan}", $jenis_baru);

    // FIX: Gunakan route yang benar
    return redirect()->route('admin2.layanan_create')
        ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
}

    // =========================
    // EDIT JENIS - ADMIN
    // =========================
    public function editJenis($id)
    {
        $jenis = JenisLayanan::findOrFail($id);
        $satuan = Satuan::all();

        return view('admin.edit_jenis_layanan', [
            'jenis' => $jenis,
            'satuan' => $satuan,
        ]);
    }

    // =========================
    // EDIT JENIS - KASIR
    // =========================
    public function editJenisKasir($id)
    {
        $jenis = JenisLayanan::findOrFail($id);
        $satuan = Satuan::all();

        return view('kasir.layanan.tambah_jenis_layanan_edit', [
            'jenis' => $jenis,
            'satuan' => $satuan,
            'from' => request('from')
        ]);
    }

    // =========================
    // EDIT JENIS - ADMIN2
    // =========================
    public function editJenisAdmin2($id)
{
    $admin2 = Auth::guard('admin2')->user();
    if (!$admin2) {
        abort(403);
    }

    $jenis = JenisLayanan::findOrFail($id);
    $satuan = Satuan::all();

    return view('admin2.layanan.tambah_jenis_layanan_edit', [
        'jenis' => $jenis,
        'satuan' => $satuan,
        'from' => request('from'),
        'id_layanan' => $jenis->id_layanan,
    ]);
}

    // =========================
    // UPDATE JENIS - ADMIN2 (TAMBAHKAN METHOD INI)
    // =========================
    public function updateJenisAdmin2(Request $request, $id)
    {
        $jenis = JenisLayanan::findOrFail($id);

        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan' => 'required|exists:satuan,id_satuan',
            'harga' => 'required|numeric',
            'lama' => 'required|numeric',
            'lama_satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('gambar')) {
            // Delete old image if exists
            if ($jenis->gambar && file_exists(public_path('images/jenis/' . $jenis->gambar))) {
                unlink(public_path('images/jenis/' . $jenis->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Pastikan folder ada
            if (!file_exists(public_path('images/jenis'))) {
                mkdir(public_path('images/jenis'), 0755, true);
            }
            
            $file->move(public_path('images/jenis'), $filename);
            $jenis->gambar = $filename;
        }

        $jenis->update([
            'nama_jenis' => $request->nama_jenis,
            'id_satuan' => $request->id_satuan,
            'harga' => $request->harga,
            'lama' => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin2.layanan.edit', $request->from ?? $jenis->id_layanan)
            ->with('success', 'Jenis layanan berhasil diperbarui.');
    }

    // =========================
    // UPDATE JENIS - ADMIN
    // =========================
    public function updateJenis(Request $request, $id)
    {
        $jenis = JenisLayanan::findOrFail($id);

        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan' => 'required|exists:satuan,id_satuan',
            'harga' => 'required|numeric',
            'lama' => 'required|numeric',
            'lama_satuan' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp',
        ]);

        $jenis->update([
            'nama_jenis' => $request->nama_jenis,
            'id_satuan' => $request->id_satuan,
            'harga' => $request->harga,
            'lama' => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan' => $request->keterangan,
        ]);

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $jenis->gambar = $filename;
            $jenis->save();
        }

        return redirect()->route('layanan.edit', $request->from ?? $jenis->id_layanan)
            ->with('success', 'Jenis layanan berhasil diperbarui.');
    }

    // =========================
    // DELETE LAYANAN - ADMIN
    // =========================
    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);
        JenisLayanan::where('id_layanan', $layanan->id_layanan)->delete();
        $layanan->delete();

        return redirect()->route('layanan.index')
            ->with('success', 'Layanan berhasil dihapus');
    }

    // =========================
    // DELETE LAYANAN - KASIR
    // =========================
    public function destroyKasir($id)
    {
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
        $layanan = Layanan::findOrFail($id);
        JenisLayanan::where('id_layanan', $layanan->id_layanan)->delete();
        $layanan->delete();

        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil dihapus!');
    }

    // =========================
    // DUPLICATE LAYANAN - ADMIN
    // =========================
    public function duplicate($id)
    {
        $layanan = Layanan::with('jenis')->findOrFail($id);

        $new = $layanan->replicate();
        $new->nama_layanan .= ' (Copy)';
        $new->save();

        foreach ($layanan->jenis as $jenis) {
            $j = $jenis->replicate();
            $j->id_layanan = $new->id_layanan;
            $j->save();
        }

        return redirect()->back()->with('success', 'Layanan berhasil diduplikat!');
    }

    // =========================
    // DUPLICATE LAYANAN - KASIR
    // =========================
    public function duplicateKasir($id)
    {
        $layanan = Layanan::with('jenis')->findOrFail($id);

        $new = $layanan->replicate();
        $new->nama_layanan .= ' (Copy)';
        $new->save();

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
        $layanan = Layanan::with('jenis')->findOrFail($id);

        $new = $layanan->replicate();
        $new->nama_layanan .= ' (Copy)';
        $new->save();

        foreach ($layanan->jenis as $jenis) {
            $j = $jenis->replicate();
            $j->id_layanan = $new->id_layanan;
            $j->save();
        }

        return redirect()->route('admin2.layanan.index')
            ->with('success', 'Layanan berhasil diduplikat!');
    }

    // =========================
    // SESSION METHODS - ADMIN
    // =========================
    public function clearJenisSession()
    {
        session()->forget('jenis_baru');
        return back();
    }

    public function sessionCreateJenis($from, Request $request)
    {
        $mode = $request->query('mode', 'create');
        $satuan = Satuan::all();

        if ($mode === 'edit') {
            return view('admin.tambah_jenis_layanan_edit', [
                'from' => $from,
                'satuan' => $satuan
            ]);
        } else {
            return view('admin.tambah_jenis_layanan_create', [
                'from' => $from,
                'satuan' => $satuan
            ]);
        }
    }

    public function sessionStoreJenis(Request $request, $from)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan'  => 'required|exists:satuan,id_satuan',
            'harga'      => 'required|numeric',
            'lama'       => 'required|numeric',
            'lama_satuan'=> 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $jenis_baru = session()->get("jenis_baru_{$from}", []);

        $jenis_baru[] = [
            'nama_jenis'  => $request->nama_jenis,
            'id_satuan'   => $request->id_satuan,
            'harga'       => $request->harga,
            'lama'        => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan'  => $request->keterangan,
        ];

        session()->put("jenis_baru_{$from}", $jenis_baru);

        return redirect()->route('layanan.create', [
            'from' => $from
        ])->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    public function addJenisEdit(Request $request, $from)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan'  => 'required|exists:satuan,id_satuan',
            'harga'      => 'required|numeric',
            'lama'       => 'nullable|numeric',
            'lama_satuan'=> 'nullable|string',
            'keterangan' => 'nullable|string',
            'gambar'     => 'nullable|image|max:2048',
        ]);

        $gambar = null;
        if ($request->hasFile('gambar')) {
            $filename = time().'_'.$request->file('gambar')->getClientOriginalName();
            $request->file('gambar')->move(public_path('images'), $filename);
            $gambar = $filename;
        }

        $jenis = session()->get("jenis_baru_{$from}", []);
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

        return redirect()->route('kasir.layanan.edit', $from)
            ->with('success', 'Jenis layanan berhasil ditambahkan!');
    }
     public function addJenisEditAdmin2(Request $request, $from)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan'  => 'required|exists:satuan,id_satuan',
            'harga'      => 'required|numeric',
            'lama'       => 'nullable|numeric',
            'lama_satuan'=> 'nullable|string',
            'keterangan' => 'nullable|string',
            'gambar'     => 'nullable|image|max:2048',
        ]);

        $gambar = null;
        if ($request->hasFile('gambar')) {
            $filename = time().'_'.$request->file('gambar')->getClientOriginalName();
            $request->file('gambar')->move(public_path('images'), $filename);
            $gambar = $filename;
        }

        $jenis = session()->get("jenis_baru_{$from}", []);
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

    public function addJenisSessionForm($from)
    {
        $satuanList = \App\Models\Satuan::all();

        return view('admin.tambah_jenis_layanan_edit', [
            'from' => $from,
            'satuanList' => $satuanList
        ]);
    }

    // =========================
    // SESSION METHODS - KASIR
    // =========================
    public function sessionCreateJenisKasir($from, Request $request)
    {
        $mode = $request->query('mode', 'create');
        $satuan = Satuan::all();

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

        return redirect()->route('kasir.layanan.edit', $from)
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    public function sessionStoreJenisKasir(Request $request, $from)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan' => 'required|exists:satuan,id_satuan',
            'harga' => 'required|numeric',
            'lama' => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $jenis_baru = session()->get("jenis_baru_{$from}", []);

        $jenis_baru[] = [
            'nama_jenis' => $request->nama_jenis,
            'id_satuan' => $request->id_satuan,
            'harga' => $request->harga,
            'lama' => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan' => $request->keterangan,
        ];

        session()->put("jenis_baru_{$from}", $jenis_baru);

        return redirect()->route('kasir.layanan.layanan_create', ['from' => 'dashboard'])
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }

    public function storeJenisKasirSession(Request $request, $from)
    {
        $request->validate([
            'nama_jenis'  => 'required|string|max:255',
            'id_satuan'   => 'required|exists:satuan,id_satuan',
            'harga'       => 'required|numeric',
            'lama'        => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan'  => 'nullable|string',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        $gambar = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $gambar = $filename;
        }

        $jenis_baru = session()->get("jenis_baru_{$from}", []);

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


// SESSION CREATE JENIS - ADMIN2
// =========================
public function sessionCreateJenisAdmin2(Request $request, $id)
{
    $admin2 = Auth::guard('admin2')->user();
    if (!$admin2) abort(403);

    $jenisBaru = session()->get("jenis_baru_{$id}", []);
    
    $jenisBaru[] = [
        'nama_jenis' => $request->nama_jenis ?? null,
        'harga'      => $request->harga ?? 0,
        'id_satuan'  => $request->id_satuan ?? null,
        'lama'       => $request->lama ?? null,
        'lama_satuan'=> $request->lama_satuan ?? null,
        'keterangan' => $request->keterangan ?? null,
    ];

    session()->put("jenis_baru_{$id}", $jenisBaru);

    return redirect()->back()->with('success', 'Jenis layanan berhasil ditambahkan ke session');
}


    public function sessionStoreJenisAdmin2(Request $request, $from)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:255',
            'id_satuan' => 'required|exists:satuan,id_satuan',
            'harga' => 'required|numeric',
            'lama' => 'required|numeric',
            'lama_satuan' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $jenis_baru = session()->get("jenis_baru_{$from}", []);

        $jenis_baru[] = [
            'nama_jenis' => $request->nama_jenis,
            'id_satuan' => $request->id_satuan,
            'harga' => $request->harga,
            'lama' => $request->lama,
            'lama_satuan' => $request->lama_satuan,
            'keterangan' => $request->keterangan,
        ];

        session()->put("jenis_baru_{$from}", $jenis_baru);

        return redirect()->route('admin2.layanan.layanan_create', ['from' => 'dashboard'])
            ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
    }



    // =========================
    // RIWAYAT METHOD
    // =========================
    public function addLayanan(Request $request, $id)
    {
        if (Auth::guard('kasir')->check() && !$this->kasirCanAddLayanan()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses menambahkan layanan'
            ], 403);
        }

        $idRiwayat = $id;
        $idLayanan = $request->id_layanan;

        if (!$idLayanan) {
            return response()->json([
                'success' => false,
                'message' => 'ID layanan tidak ditemukan'
            ], 422);
        }

        $layanan = \App\Models\Layanan::find($idLayanan);
        if (!$layanan) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan'
            ], 404);
        }

        $jenis = $layanan->jenis()->first();
        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis layanan tidak ditemukan'
            ], 404);
        }

        $qty = $request->qty ?? 1;
        $parfum = $request->parfum;

        $key = "riwayat_{$idRiwayat}_layanan";

        $riwayatLayanan = session()->get($key, []);

        $riwayatLayanan[] = [
            'id_layanan'   => $layanan->id_layanan,
            'nama_layanan' => $layanan->nama_layanan,
            'qty'          => $qty,
            'harga'        => $jenis->harga * $qty,
            'jenis'        => $jenis->nama_jenis,
            'satuan'       => $jenis->satuan->nama_satuan ?? '-',
            'id_parfum'    => $parfum,
        ];

        session()->put($key, $riwayatLayanan);

        return response()->json([
            'success' => true
        ]);
    }

    public function fromLayanan(Request $request)
    {
        $layanan_id = $request->layanan_id;
        session(['selected_layanan_for_qty' => $layanan_id]);
        return redirect()->route('transaksi.create');
    }

    // =========================
    // PERMISSION HELPERS
    // =========================
    private function kasirCanAddLayanan()
    {
        $kasir = Auth::guard('kasir')->user();
        if (!$kasir) return false;

        return MenuRole::where('role_id', $kasir->role_id)
            ->whereHas('menu', function ($q) {
                $q->where('route', 'kasir.layanan.index');
            })
            ->where('can_add', 1)
            ->exists();
    }

    private function admin2CanAddLayanan()
{
    $admin2 = Auth::guard('admin2')->user(); // ✅ BENAR
    if (!$admin2) return false;

    return MenuRole::where('role_id', $admin2->role_id)
        ->whereHas('menu', function ($q) {
            $q->where('route', 'admin2.layanan.index');
        })
        ->where('can_add', 1)
        ->exists();
}
}