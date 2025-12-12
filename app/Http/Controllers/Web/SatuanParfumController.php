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

    public function satuanIndex(Request $request)
    {
        $query = Satuan::query();

        // Search
        if ($request->search) {
            $query->where('nama_satuan', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->sort == 'asc') {
            $query->orderBy('nama_satuan', 'asc');
        } else if ($request->sort == 'desc') {
            $query->orderBy('nama_satuan', 'desc');
        } else {
            $query->orderBy('nama_satuan', 'asc');
        }

        $satuan = $query->get();

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

    public function satuanEdit($id)
{
    $satuan = Satuan::where('id_satuan', $id)->firstOrFail();
    return view('satuan.edit', compact('satuan'));
}


    // ================================
    // PARFUM
    // ================================

    public function parfumIndex(Request $request)
    {
        $query = Parfum::query();

        // Search
        if ($request->search) {
            $query->where('nama_parfum', 'like', '%' . $request->search . '%');
        }

        // Sort
        if ($request->sort == 'asc') {
            $query->orderBy('nama_parfum', 'asc');
        } else if ($request->sort == 'desc') {
            $query->orderBy('nama_parfum', 'desc');
        } else {
            $query->orderBy('nama_parfum', 'asc');
        }

        $parfum = $query->get();

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

        public function parfumEdit($id)
    {
        $parfum = Parfum::where('id_parfum', $id)->firstOrFail();
        return view('parfum.edit', compact('parfum'));
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
