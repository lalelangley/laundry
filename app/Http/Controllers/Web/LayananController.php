<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Layanan;
use App\Models\JenisLayanan;
use App\Models\Satuan;  

class LayananController extends Controller
{
    // =========================
    // INDEX LAYANAN
    // =========================
    public function index()
    {
        $layananUtama = Layanan::with('jenis')->get();
        return view('admin.layanan', compact('layananUtama'));
    }

    // =========================
    // CREATE LAYANAN
    // =========================
    public function create()
    {
        return view('admin.layanan_create');
    }

    // =========================
    // STORE LAYANAN
    // =========================
// Store Layanan + jenis dari session
    public function store(Request $request)
{
    $request->validate([
        'nama_layanan' => 'required|string|max:255',
        'proses' => 'nullable|array',
    ]);

    // 1. Simpan layanan utama
    $layanan = Layanan::create([
        'nama_layanan' => $request->nama_layanan,
        'proses' => $request->proses ? implode(',', $request->proses) : null,
    ]);

    // 2. Simpan semua jenis dari session (jika ada)
    $jenisList = session()->get("jenis_baru_0", []); // 0 = id layanan baru
    foreach($jenisList as $j) {
        JenisLayanan::create([
            'id_layanan' => $layanan->id_layanan,
            'nama_jenis' => $j['nama_jenis'] ?? $j['nama'] ?? null,
            'id_satuan' => $j['id_satuan'] ?? null,
            'harga' => $j['harga'] ?? 0,
            'lama' => $j['lama'] ?? null,
            'lama_satuan' => $j['lama_satuan'] ?? null,
            'keterangan' => $j['keterangan'] ?? null,
            'gambar' => $j['gambar'] ?? null,
        ]);
    }

    // 3. Hapus session sementara
    session()->forget("jenis_baru_0");

    return redirect()->route('layanan.index')
                     ->with('success', 'Layanan dan jenis berhasil disimpan!');
}

    // =========================
    // EDIT LAYANAN
    // =========================
public function edit($id)
{
    if($id == 0) {
        return redirect()->route('layanan.create');
    }

    $layanan = Layanan::with('jenis')->findOrFail($id);
    $jenisBaru = session()->get("jenis_baru_{$id}", []);
    return view('admin.layanan_edit', compact('layanan', 'jenisBaru'));
}



    // =========================
    // UPDATE LAYANAN
    // =========================
public function update(Request $request, $id)
{
    $layanan = Layanan::findOrFail($id);

    // Update nama & proses
    $layanan->update([
        'nama_layanan' => $request->nama_layanan,
        'proses'       => implode(',', $request->proses ?? []),
    ]);

    // Update jenis lama jika ada
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

    // Simpan jenis baru dari session
    $jenisBaru = session()->get("jenis_baru_{$id}", []);
    foreach ($jenisBaru as $jb) {
        JenisLayanan::create([
            'id_layanan'  => $layanan->id_layanan,
            'nama_jenis'  => $jb['nama'] ?? null,
            'harga'       => $jb['harga'] ?? 0,
            'id_satuan'   => $jb['id_satuan'] ?? null,
            'lama'        => $jb['lama'] ?? null,
            'lama_satuan' => $jb['lama_satuan'] ?? null,
            'keterangan'  => $jb['keterangan'] ?? null,
            'gambar'      => $jb['gambar'] ?? null,
        ]);
    }

    // Clear session
    session()->forget("jenis_baru_{$id}");

    // Redirect ke daftar layanan
    return redirect()->route('layanan.index')
                     ->with('success', 'Layanan berhasil diupdate!');
}

    // =========================
    // CREATE JENIS LAYANAN
    // =========================
public function createJenis($id_layanan, Request $request)
{
    $mode = $request->query('mode', 'create'); // 'create' atau 'edit'
    $from = $request->query('from', $id_layanan); // default dari id_layanan
    $satuan = Satuan::all(); // untuk dropdown satuan

    if ($mode === 'edit') {
        return view('admin.tambah_jenis_layanan_edit', compact('id_layanan', 'mode', 'from', 'satuan'));
    } else {
        return view('admin.tambah_jenis_layanan_create', compact('id_layanan', 'mode', 'from', 'satuan'));
    }
}


// STORE JENIS
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

        return redirect()->route('layanan.create')->with('success', 'Jenis layanan berhasil ditambahkan.');
    }

    // =========================
    // EDIT JENIS LAYANAN
    // =========================
// EDIT JENIS
public function editJenis($id)
{
    $jenis = JenisLayanan::findOrFail($id);
    $satuan = Satuan::all();
    $from = request()->query('from', 0);

    return view('admin.edit_jenis_layanan', compact('jenis', 'satuan', 'from'));
}

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
    // DELETE LAYANAN
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
    // DUPLICATE LAYANAN
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


    // Clear semua session jenis sementara
    public function clearJenisSession()
    {
        session()->forget('jenis_baru');
        return back();
    }

    // ===========================
    // Create Layanan + Jenis (Session-like placeholder)
    // ===========================

    // Halaman create jenis layanan untuk layanan yang belum disimpan
public function sessionCreateJenis($from, Request $request)
{
    $mode = $request->query('mode', 'create'); // default 'create'

    // Ambil semua satuan dari database
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

    // Store jenis layanan placeholder
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
    'nama_jenis'  => $request->nama_jenis,   // pastikan ini
    'id_satuan'   => $request->id_satuan,
    'harga'       => $request->harga,
    'lama'        => $request->lama,
    'lama_satuan' => $request->lama_satuan,
    'keterangan'  => $request->keterangan,
];


    session()->put("jenis_baru_{$from}", $jenis_baru);

    // Redirect ke halaman create layanan
    return redirect()->route('layanan.create')
                     ->with('success', 'Jenis layanan berhasil ditambahkan sementara.');
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

    // Ambil session jenis baru dari layanan $from
    $jenis = session()->get("jenis_baru_{$from}", []);
    $jenis[] = [
        'nama'        => $request->nama_jenis,
        'id_satuan'   => $request->id_satuan,
        'harga'       => $request->harga,
        'lama'        => $request->lama,
        'lama_satuan' => $request->lama_satuan,
        'keterangan'  => $request->keterangan,
        'gambar'      => $gambar,
    ];

    session()->put("jenis_baru_{$from}", $jenis);

    // **Redirect ke halaman edit layanan**
    return redirect()->route('layanan.edit', $from)
                     ->with('success', 'Jenis layanan berhasil ditambahkan!');
}


// di App\Http\Controllers\Web\LayananController.php

public function addJenisSessionForm($from)
{
    // Ambil daftar satuan untuk form
    $satuanList = \App\Models\Satuan::all();

    return view('admin.tambah_jenis_layanan_edit', [
        'from' => $from,
        'satuanList' => $satuanList
    ]);
}


}


