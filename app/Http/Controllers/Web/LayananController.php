<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Layanan;

class LayananController extends Controller
{
    // =========================
    // INDEX LAYANAN
    // =========================
    public function index()
    {
        // Ambil semua layanan utama
        $layananUtama = Layanan::where('tipe', 'layanan')->get();

        // Ambil semua jenis layanan
        $jenisLayanan = Layanan::where('tipe', 'jenis')->get()->groupBy('nama_layanan');

        return view('admin.layanan', compact('layananUtama', 'jenisLayanan'));
    }

    // =========================
    // FORM CREATE LAYANAN
    // =========================
    public function create()
    {
        $jenisBaru = session('jenis_baru', []);
        return view('admin.layanan_create', compact('jenisBaru'));
    }

    // =========================
    // SIMPAN LAYANAN & JENIS BARU
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string',
            'proses' => 'nullable|array',
            'jenis_baru' => 'required|array', 
        ]);

        // Simpan layanan utama
        $layanan = Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'tipe' => 'layanan',
            'proses' => $request->proses ? implode(', ', $request->proses) : null,
        ]);

        // Simpan semua jenis layanan baru
        foreach ($request->jenis_baru as $j) {
            Layanan::create([
                'nama_layanan' => $request->nama_layanan, // hubungkan dengan nama layanan utama
                'nama_jenis' => $j['nama'],
                'satuan' => $j['satuan'] ?? null,
                'harga' => $j['harga'] ?? 0,
                'tipe' => 'jenis',
                'gambar' => $j['gambar'] ?? null,
            ]);
        }

        session()->forget('jenis_baru');

        return redirect()->route('layanan.index')->with('success', 'Layanan & jenis layanan berhasil ditambahkan');
    }

    // =========================
// FORM CREATE JENIS LAYANAN
// =========================
public function createJenis()
{
    return view('admin.tambah_jenis_layanan'); // pastikan view ini ada
}

// =========================
// SIMPAN JENIS LAYANAN
// =========================
public function storeJenis(Request $request)
{
    $request->validate([
        'nama_jenis' => 'required|string',
        'harga' => 'required|numeric',
        'satuan' => 'required|string',
        'gambar' => 'nullable|image|max:2048',
    ]);

    $gambarPath = $request->file('gambar')?->store('layanan');

    // Simpan jenis layanan ke session
    $jenis_baru = session()->get('jenis_baru', []);
    $jenis_baru[] = [
        'nama' => $request->nama_jenis,
        'harga' => $request->harga,
        'satuan' => $request->satuan,
        'gambar' => $gambarPath,
    ];
    session()->put('jenis_baru', $jenis_baru);

    return redirect()->route('layanan.create')->with('success', 'Jenis layanan berhasil ditambahkan');
}
}
