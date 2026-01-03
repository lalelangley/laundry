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

    public function satuanCreate(Request $request)
    {
        return view('satuan.create', [
            'from' => $request->from,
            'id'   => $request->id
        ]);
    }

    public function satuanStore(Request $request)
    {

        $request->validate(['nama_satuan' => 'required|max:50']);

        $satuan = Satuan::create([
            'nama_satuan' => $request->nama_satuan
        ]);

        // 1. Dari TAMBAH JENIS (CREATE)
        if ($request->from === 'create-jenis') {
            return redirect()->route('layanan.jenis.create', [
                'id_layanan' => $request->id_layanan,
                'new_satuan' => $satuan->id_satuan   // supaya auto select
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }


       if ($request->from === 'edit-jenis') {
            return redirect()->route('layanan.jenis.edit', [
                'id_jenis' => $request->id_jenis,
                'from' => $request->id_layanan,
                'new_satuan' => $satuan->id_satuan   // <-- kirim id satuan baru
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }


        
        // 3. Dari menu satuan
        return redirect()->route('satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
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

// ================================
// SATUAN (KASIR)
// ================================

public function satuanIndexKasir(Request $request)
{
    $query = Satuan::query();

    if ($request->search) {
        $query->where('nama_satuan', 'like', '%' . $request->search . '%');
    }

    if ($request->sort == 'desc') {
        $query->orderBy('nama_satuan', 'desc');
    } else {
        $query->orderBy('nama_satuan', 'asc');
    }

    $satuan = $query->get();

    return view('kasir.satuan.index', compact('satuan'));
}

public function satuanCreateKasir()
{
    return view('kasir.satuan.create');
}

public function satuanStoreKasir(Request $request)
{
    $request->validate([
        'nama_satuan' => 'required|max:50'
    ]);

    Satuan::create([
        'nama_satuan' => $request->nama_satuan
    ]);

    return redirect()->route('kasir.satuan.index')
        ->with('success', 'Satuan berhasil ditambahkan.');
}

public function satuanEditKasir($id)
{
    $satuan = Satuan::where('id_satuan', $id)->firstOrFail();
    return view('kasir.satuan.edit', compact('satuan'));
}

public function satuanUpdateKasir(Request $request, $id)
{
    $request->validate([
        'nama_satuan' => 'required|max:50'
    ]);

    Satuan::where('id_satuan', $id)->update([
        'nama_satuan' => $request->nama_satuan
    ]);

    return redirect()->route('kasir.satuan.index')
        ->with('success', 'Satuan berhasil diupdate.');
}

public function satuanDestroyKasir($id)
{
    Satuan::where('id_satuan', $id)->delete();

    return redirect()->route('kasir.satuan.index')
        ->with('success', 'Satuan berhasil dihapus.');
}

// ================================
// PARFUM (KASIR)
// ================================

public function parfumIndexKasir(Request $request)
{
    $query = Parfum::query();

    if ($request->search) {
        $query->where('nama_parfum', 'like', '%' . $request->search . '%');
    }

    if ($request->sort == 'desc') {
        $query->orderBy('nama_parfum', 'desc');
    } else {
        $query->orderBy('nama_parfum', 'asc');
    }

    $parfum = $query->get();

    return view('kasir.parfum.index', compact('parfum'));
}

public function parfumCreateKasir()
{
    return view('kasir.parfum.create');
}

public function parfumStoreKasir(Request $request)
{
    $request->validate([
        'nama_parfum' => 'required|max:100'
    ]);

    Parfum::create([
        'nama_parfum' => $request->nama_parfum
    ]);

    return redirect()->route('kasir.parfum.index')
        ->with('success', 'Parfum berhasil ditambahkan.');
}

public function parfumEditKasir($id)
{
    $parfum = Parfum::where('id_parfum', $id)->firstOrFail();
    return view('kasir.parfum.edit', compact('parfum'));
}

public function parfumUpdateKasir(Request $request, $id)
{
    $request->validate([
        'nama_parfum' => 'required|max:100'
    ]);

    Parfum::where('id_parfum', $id)->update([
        'nama_parfum' => $request->nama_parfum
    ]);

    return redirect()->route('kasir.parfum.index')
        ->with('success', 'Parfum berhasil diupdate.');
}

public function parfumDestroyKasir($id)
{
    Parfum::where('id_parfum', $id)->delete();

    return redirect()->route('kasir.parfum.index')
        ->with('success', 'Parfum berhasil dihapus.');
}

// ================================
// PARFUM (ADMIN2)
// ================================

public function IndexAdmin2(Request $request)
{
    $query = Parfum::query();

    if ($request->search) {
        $query->where('nama_parfum', 'like', '%' . $request->search . '%');
    }

    if ($request->sort == 'desc') {
        $query->orderBy('nama_parfum', 'desc');
    } else {
        $query->orderBy('nama_parfum', 'asc');
    }

    $parfum = $query->get();

    return view('admin2.parfum.index', compact('parfum'));
}

public function parfumCreateAdmin2()
{
    return view('admin2.parfum.create');
}

public function parfumStoreAdmin2(Request $request)
{
    $request->validate([
        'nama_parfum' => 'required|max:100'
    ]);

    Parfum::create([
        'nama_parfum' => $request->nama_parfum
    ]);

    return redirect()->route('admin2.parfum.index')
        ->with('success', 'Parfum berhasil ditambahkan.');
}

public function parfumEditAdmin2($id)
{
    $parfum = Parfum::where('id_parfum', $id)->firstOrFail();
    return view('admin2.parfum.edit', compact('parfum'));
}

public function parfumUpdateAdmin2(Request $request, $id)
{
    $request->validate([
        'nama_parfum' => 'required|max:100'
    ]);

    Parfum::where('id_parfum', $id)->update([
        'nama_parfum' => $request->nama_parfum
    ]);

    return redirect()->route('admin2.parfum.index')
        ->with('success', 'Parfum berhasil diupdate.');
}

public function parfumDestroyAdmin2($id)
{
    Parfum::where('id_parfum', $id)->delete();

    return redirect()->route('admin2.parfum.index')
        ->with('success', 'Parfum berhasil dihapus.');
}


// ================================
// SATUAN (KASIR)
// ================================

public function satuanIndexAdmin2(Request $request)
{
    $query = Satuan::query();

    if ($request->search) {
        $query->where('nama_satuan', 'like', '%' . $request->search . '%');
    }

    if ($request->sort == 'desc') {
        $query->orderBy('nama_satuan', 'desc');
    } else {
        $query->orderBy('nama_satuan', 'asc');
    }

    $satuan = $query->get();

    return view('admin2.satuan.index', compact('satuan'));
}

public function satuanCreateAdmin2()
{
    return view('admin2.satuan.create');
}

public function satuanStoreAdmin2(Request $request)
{
    $request->validate([
        'nama_satuan' => 'required|max:50'
    ]);

    Satuan::create([
        'nama_satuan' => $request->nama_satuan
    ]);

    return redirect()->route('admin2.satuan.index')
        ->with('success', 'Satuan berhasil ditambahkan.');
}

public function satuanEditAdmin2($id)
{
    $satuan = Satuan::where('id_satuan', $id)->firstOrFail();
    return view('admin2.satuan.edit', compact('satuan'));
}

public function satuanUpdateAdmin2(Request $request, $id)
{
    $request->validate([
        'nama_satuan' => 'required|max:50'
    ]);

    Satuan::where('id_satuan', $id)->update([
        'nama_satuan' => $request->nama_satuan
    ]);

    return redirect()->route('admin2.satuan.index')
        ->with('success', 'Satuan berhasil diupdate.');
}

public function satuanDestroyAdmin2($id)
{
    Satuan::where('id_satuan', $id)->delete();

    return redirect()->route('admin2.satuan.index')
        ->with('success', 'Satuan berhasil dihapus.');
}

}
