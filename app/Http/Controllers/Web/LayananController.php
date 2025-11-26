<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Layanan;
use App\Models\JenisLayanan;

class LayananController extends Controller
{
    // =========================
    // INDEX
    // =========================
public function index()
{
    $layananUtama = Layanan::with('jenis')->get();

  (view('admin.layanan')->getPath());


    return view('admin.layanan', compact('layananUtama'));
}

    // =========================
    // FORM CREATE LAYANAN
    // =========================
    public function create()
    {
        return view('admin.layanan_create');
    }

    // =========================
    // SIMPAN LAYANAN
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:255',
            'proses'       => 'required|array',
        ]);

        $jenis = session()->get('jenis_baru', []);

        if (count($jenis) === 0) {
            return back()->withErrors(['Tambahkan minimal 1 jenis layanan terlebih dahulu']);
        }

        // Simpan layanan utama
        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'proses'       => implode(',', $request->proses),
        ]);

        // Simpan jenis layanan
        foreach ($jenis as $item) {
            JenisLayanan::create([
                'id_layanan'  => $layanan->id_layanan,
                'nama_jenis'  => $item['nama'],
                'harga'       => $item['harga'],
                'satuan'      => $item['satuan'],
                'lama'        => $item['lama'] ?? null,
                'lama_satuan' => $item['lama_satuan'] ?? null,
                'keterangan'  => $item['keterangan'] ?? null,
                'gambar'      => $item['gambar'] ?? null,
            ]);
        }

        session()->forget('jenis_baru');

        return redirect()->route('layanan.index')
                         ->with('success', 'Layanan berhasil disimpan!');
    }

    // =========================
    // FORM TAMBAH JENIS
    // =========================
    public function createJenis()
    {
        return view('admin.tambah_jenis_layanan');
    }

    // =========================
    // SIMPAN JENIS (Session)
    // =========================
    public function storeJenis(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required',
            'harga'      => 'required|numeric',
            'satuan'     => 'required',
            'gambar'     => 'nullable|image|max:2048'
        ]);

        // Upload gambar ke /public/images
        $gambar = null;
        if ($request->hasFile('gambar')) {
            $filename = time() . '_' . $request->file('gambar')->getClientOriginalName();
            $request->file('gambar')->move(public_path('images'), $filename);
            $gambar = $filename;
        }

        // Simpan ke session
        $jenis = [
            'nama'   => $request->nama_jenis,
            'harga'  => $request->harga,
            'satuan' => $request->satuan,
            'gambar' => $gambar
        ];

        session()->push('jenis_baru', $jenis);

        return redirect()->route('layanan.create')
                         ->with('success', 'Jenis layanan ditambahkan');
    }

   public function edit($id)
{
    // Ambil layanan yang sedang dipilih user
    $layanan = Layanan::with('jenis')->findOrFail($id);

    // Ambil jenis baru dari session (kalau ada)
    $jenisBaru = session('jenis_baru', []);

    return view('admin.layanan_edit', compact('layanan', 'jenisBaru'));
}

public function update(Request $request, $id)
{
    $layanan = Layanan::with('jenis')->findOrFail($id);

    $request->validate([
        'nama_layanan' => 'required|string|max:255',
        'proses' => 'required|array|min:1',
    ]);

    // UPDATE LAYANAN
    $layanan->update([
        'nama_layanan' => $request->nama_layanan,
        'proses'       => implode(',', $request->proses),
    ]);

    // UPDATE JENIS LAMA
    if ($request->old_jenis) {
        foreach ($request->old_jenis as $idJenis => $data) {
            $jenis = JenisLayanan::find($idJenis);
            if ($jenis) {
                $jenis->update([
                    'nama_jenis'  => $data['nama_jenis'],
                    'harga'       => $data['harga'],
                    'satuan'      => $data['satuan'],
                    'lama'        => $data['lama'] ?? null,
                    'lama_satuan' => $data['lama_satuan'] ?? null,
                    'keterangan'  => $data['keterangan'] ?? null,
                ]);
            }
        }
    }

    // TAMBAH JENIS BARU
    $jenisBaru = session()->get('jenis_baru', []);
    foreach ($jenisBaru as $item) {
        JenisLayanan::create([
            'id_layanan'  => $layanan->id_layanan,
            'nama_jenis'  => $item['nama'],
            'harga'       => $item['harga'],
            'satuan'      => $item['satuan'],
            'lama'        => $item['lama'] ?? null,
            'lama_satuan' => $item['lama_satuan'] ?? null,
            'keterangan'  => $item['keterangan'] ?? null,
            'gambar'      => $item['gambar'] ?? null,
        ]);
    }

    session()->forget('jenis_baru');

    return redirect()->route('layanan.index')
                     ->with('success', 'Layanan berhasil diperbarui');
}
public function duplicate($id)
{
    $layanan = Layanan::with('jenis')->findOrFail($id);

    // duplicate layanan utama
    $new = $layanan->replicate();
    $new->nama_layanan = $layanan->nama_layanan . ' (Copy)';
    $new->save();

    // duplicate jenis-jenisnya
    foreach ($layanan->jenis as $jenis) {
        $j = $jenis->replicate();
        $j->layanan_id = $new->id;
        $j->save();
    }

    return redirect()->back()->with('success', 'Layanan berhasil diduplikat!');
}
public function destroy($id)
{
    $layanan = Layanan::findOrFail($id);

    // Hapus semua jenis layanan terkait
    JenisLayanan::where('id_layanan', $layanan->id_layanan)->delete();

    // Hapus layanan utama
    $layanan->delete();

    return redirect()->route('layanan.index')
                     ->with('success', 'Layanan berhasil dihapus');
}
public function editJenis($id)
{
    // Redirect langsung ke halaman edit layanan
    $jenis = JenisLayanan::findOrFail($id);
    return redirect()->route('layanan.edit', $jenis->id_layanan);
}

}
