<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Satuan;
use App\Models\Parfum;

class SatuanParfumController extends Controller
{
    // ================================
    // SATUAN
    // ================================

    public function satuanIndex()
    {
        $satuan = Satuan::orderBy('nama_satuan')->get();
        return view('satuan.index', compact('satuan'));
    }

    public function satuanCreate()
    {
        return view('satuan.create');
    }

    public function satuanStore(Request $request)
    {
        $request->validate(['nama_satuan' => 'required|max:50']);

        Satuan::create(['nama_satuan' => $request->nama_satuan]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function satuanUpdate(Request $request, $id)
    {
        $request->validate(['nama_satuan' => 'required|max:50']);

        Satuan::where('id_satuan', $id)->update([
            'nama_satuan' => $request->nama_satuan
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diupdate.');
    }

    public function satuanDestroy($id)
    {
        Satuan::where('id_satuan', $id)->delete();
        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }

    // ================================
    // PARFUM
    // ================================

    public function parfumIndex()
    {
        $parfum = Parfum::orderBy('nama_parfum')->get();
        return view('parfum.index', compact('parfum'));
    }

    public function parfumCreate()
    {
        return view('parfum.create');
    }

    public function parfumStore(Request $request)
    {
        $request->validate(['nama_parfum' => 'required|max:100']);

        Parfum::create(['nama_parfum' => $request->nama_parfum]);

        return redirect()->route('parfum.index')->with('success', 'Parfum berhasil ditambahkan.');
    }

    public function parfumUpdate(Request $request, $id)
    {
        $request->validate(['nama_parfum' => 'required|max:100']);

        Parfum::where('id_parfum', $id)->update([
            'nama_parfum' => $request->nama_parfum
        ]);

        return redirect()->route('parfum.index')->with('success', 'Parfum berhasil diupdate.');
    }

    public function parfumDestroy($id)
    {
        Parfum::where('id_parfum', $id)->delete();

        return redirect()->route('parfum.index')->with('success', 'Parfum berhasil dihapus.');
    }
}
