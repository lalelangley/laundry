<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Satuan;
use App\Models\Parfum;

class SatuanParfumController extends Controller
{
    // ================================
    // SATUAN - ADMIN
    // ================================

    public function satuanIndex(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('satuan', 'view');
        
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
        // ✅ CHECK PERMISSION ADD
        requirePermission('satuan', 'add');
        
        return view('satuan.create', [
            'from' => $request->from,
            'id_layanan' => $request->id_layanan,
            'id_jenis' => $request->id_jenis
        ]);
    }

    public function satuanStore(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required',
        ], [
            'nama_satuan.required' => 'Nama Satuan tidak boleh kosong.',
        ]);
        // ✅ CHECK PERMISSION ADD
        requirePermission('satuan', 'add');
        
        $request->validate(['nama_satuan' => 'required|max:50']);

        $satuan = Satuan::create([
            'nama_satuan' => $request->nama_satuan
        ]);

        // 1. Dari TAMBAH JENIS (CREATE)
        if ($request->from === 'create-jenis' && $request->id_layanan) {
            return redirect()->route('layanan.jenis.create', [
                'id_layanan' => $request->id_layanan,
                'new_satuan' => $satuan->id_satuan
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }

        // 2. Dari EDIT JENIS
        if ($request->from === 'edit-jenis' && $request->id_layanan && $request->id_jenis) {
            return redirect()->route('layanan.jenis.edit', [
                'id_jenis' => $request->id_jenis,
                'from' => $request->id_layanan,
                'new_satuan' => $satuan->id_satuan
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }

        // 3. Dari menu satuan
        return redirect()->route('satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function satuanEdit($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('satuan', 'edit');
        
        $satuan = Satuan::where('id_satuan', $id)->firstOrFail();
        return view('satuan.edit', compact('satuan'));
    }

    public function satuanUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_satuan' => 'required',
        ], [
           'nama_satuan.required' => 'Nama Satuan tidak boleh kosong.',
        ]);

        // ✅ CHECK PERMISSION EDIT
        requirePermission('satuan', 'edit');
        
        $request->validate(['nama_satuan' => 'required|max:50']);

        Satuan::where('id_satuan', $id)->update([
            'nama_satuan' => $request->nama_satuan
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diupdate.');
    }

    public function satuanDestroy($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('satuan', 'delete');
        
        Satuan::where('id_satuan', $id)->delete();

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }

    // ================================
    // PARFUM - ADMIN
    // ================================

    public function parfumIndex(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('parfum', 'view');
        
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
        // ✅ CHECK PERMISSION ADD
        requirePermission('parfum', 'add');
        
        return view('parfum.create');
    }

    public function parfumStore(Request $request)
    {
         $request->validate([
            'nama_parfum' => 'required',
        ], [
            'nama_parfum.required' => 'Nama Parfum tidak boleh kosong.',
        ]);

        // ✅ CHECK PERMISSION ADD
        requirePermission('parfum', 'add');
        
        $request->validate(['nama_parfum' => 'required|max:100']);

        Parfum::create(['nama_parfum' => $request->nama_parfum]);

        return redirect()->route('parfum.index')->with('success', 'Parfum berhasil ditambahkan.');
    }

    public function parfumEdit($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('parfum', 'edit');
        
        $parfum = Parfum::where('id_parfum', $id)->firstOrFail();
        return view('parfum.edit', compact('parfum'));
    }

    public function parfumUpdate(Request $request, $id)
    {
        $request->validate([
        'nama_parfum' => 'required',
        ], [
            'nama_parfum.required' => 'Nama Parfum tidak boleh kosong.',
        ]);
        
        // ✅ CHECK PERMISSION EDIT
        requirePermission('parfum', 'edit');
        
        $request->validate(['nama_parfum' => 'required|max:100']);

        Parfum::where('id_parfum', $id)->update([
            'nama_parfum' => $request->nama_parfum
        ]);

        return redirect()->route('parfum.index')->with('success', 'Parfum berhasil diupdate.');
    }

    public function parfumDestroy($id)
    {
        // ✅ CHECK PERMISSION DELETE
        requirePermission('parfum', 'delete');
        
        Parfum::where('id_parfum', $id)->delete();

        return redirect()->route('parfum.index')->with('success', 'Parfum berhasil dihapus.');
    }

    // ================================
    // SATUAN - KASIR
    // ================================

    public function satuanIndexKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('satuan', 'view');
        
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

    public function satuanCreateKasir(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('satuan', 'add');
        
        return view('kasir.satuan.create', [
            'from' => $request->from,
            'id_layanan' => $request->id_layanan,
            'id_jenis' => $request->id_jenis
        ]);
    }

    public function satuanStoreKasir(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required',
        ], [
            'nama_satuan.required' => 'Nama Satuan tidak boleh kosong.',
        ]);

        // ✅ CHECK PERMISSION ADD
        requirePermission('satuan', 'add');
        
        $request->validate([
            'nama_satuan' => 'required|max:50'
        ]);

        $satuan = Satuan::create([
            'nama_satuan' => $request->nama_satuan
        ]);

        // 1. Dari TAMBAH JENIS (CREATE)
        if ($request->from === 'create-jenis' && $request->id_layanan) {
            return redirect()->route('kasir.layanan.jenis.create', [
                'id_layanan' => $request->id_layanan,
                'new_satuan' => $satuan->id_satuan
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }

        // 2. Dari EDIT JENIS
        if ($request->from === 'edit-jenis' && $request->id_layanan && $request->id_jenis) {
            return redirect()->route('kasir.layanan.jenis.edit', [
                'id' => $request->id_jenis,
                'new_satuan' => $satuan->id_satuan
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }

        // 3. Default ke index satuan
        return redirect()->route('kasir.satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function satuanEditKasir($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('satuan', 'edit');
        
        $satuan = Satuan::where('id_satuan', $id)->firstOrFail();
        return view('kasir.satuan.edit', compact('satuan'));
    }

    public function satuanUpdateKasir(Request $request, $id)
    {
        $request->validate([
            'nama_satuan' => 'required',
        ], [
           'nama_satuan.required' => 'Nama Satuan tidak boleh kosong.',
        ]);

        // ✅ CHECK PERMISSION EDIT
        requirePermission('satuan', 'edit');
        
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
        // ✅ CHECK PERMISSION DELETE
        requirePermission('satuan', 'delete');
        
        Satuan::where('id_satuan', $id)->delete();

        return redirect()->route('kasir.satuan.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }

    // ================================
    // PARFUM - KASIR
    // ================================

    public function parfumIndexKasir(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('parfum', 'view');
        
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
        // ✅ CHECK PERMISSION ADD
        requirePermission('parfum', 'add');
        
        return view('kasir.parfum.create');
    }

    public function parfumStoreKasir(Request $request)
    {

         $request->validate([
            'nama_parfum' => 'required',
        ], [
            'nama_parfum.required' => 'Nama Parfum tidak boleh kosong.',
        ]);
        
        // ✅ CHECK PERMISSION ADD
        requirePermission('parfum', 'add');
        
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
        // ✅ CHECK PERMISSION EDIT
        requirePermission('parfum', 'edit');
        
        $parfum = Parfum::where('id_parfum', $id)->firstOrFail();
        return view('kasir.parfum.edit', compact('parfum'));
    }

    public function parfumUpdateKasir(Request $request, $id)
    {
        $request->validate([
        'nama_parfum' => 'required',
        ], [
            'nama_parfum.required' => 'Nama Parfum tidak boleh kosong.',
        ]);
        
        // ✅ CHECK PERMISSION EDIT
        requirePermission('parfum', 'edit');
        
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
        // ✅ CHECK PERMISSION DELETE
        requirePermission('parfum', 'delete');
        
        Parfum::where('id_parfum', $id)->delete();

        return redirect()->route('kasir.parfum.index')
            ->with('success', 'Parfum berhasil dihapus.');
    }

    // ================================
    // PARFUM - ADMIN2
    // ================================

    public function IndexAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('parfum', 'view');
        
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
        // ✅ CHECK PERMISSION ADD
        requirePermission('parfum', 'add');
        
        return view('admin2.parfum.create');
    }

    public function parfumStoreAdmin2(Request $request)
    {
        $request->validate([
            'nama_parfum' => 'required',
        ], [
            'nama_parfum.required' => 'Nama Parfum tidak boleh kosong.',
        ]);

        // ✅ CHECK PERMISSION ADD
        requirePermission('parfum', 'add');
        
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
        // ✅ CHECK PERMISSION EDIT
        requirePermission('parfum', 'edit');
        
        $parfum = Parfum::where('id_parfum', $id)->firstOrFail();
        return view('admin2.parfum.edit', compact('parfum'));
    }

    public function parfumUpdateAdmin2(Request $request, $id)
    {
        $request->validate([
        'nama_parfum' => 'required',
        ], [
            'nama_parfum.required' => 'Nama Parfum tidak boleh kosong.',
        ]);
        
        // ✅ CHECK PERMISSION EDIT
        requirePermission('parfum', 'edit');
        
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
        // ✅ CHECK PERMISSION DELETE
        requirePermission('parfum', 'delete');
        
        Parfum::where('id_parfum', $id)->delete();

        return redirect()->route('admin2.parfum.index')
            ->with('success', 'Parfum berhasil dihapus.');
    }

    // ================================
    // SATUAN - ADMIN2
    // ================================

    public function satuanIndexAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION VIEW
        requirePermission('satuan', 'view');
        
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

    public function satuanCreateAdmin2(Request $request)
    {
        // ✅ CHECK PERMISSION ADD
        requirePermission('satuan', 'add');
        
        return view('admin2.satuan.create', [
            'from' => $request->from,
            'id_layanan' => $request->id_layanan,
            'id_jenis' => $request->id_jenis
        ]);
    }

    public function satuanStoreAdmin2(Request $request)
    {
        $request->validate([
            'nama_satuan' => 'required',
        ], [
            'nama_satuan.required' => 'Nama Satuan tidak boleh kosong.',
        ]);
        // ✅ CHECK PERMISSION ADD
        requirePermission('satuan', 'add');
        
        $request->validate([
            'nama_satuan' => 'required|max:50'
        ]);

        $satuan = Satuan::create([
            'nama_satuan' => $request->nama_satuan
        ]);

        // 1. Dari TAMBAH JENIS (CREATE)
        if ($request->from === 'create-jenis' && $request->id_layanan) {
            return redirect()->route('admin2.layanan.jenis.create', [
                'id_layanan' => $request->id_layanan,
                'new_satuan' => $satuan->id_satuan
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }

        // 2. Dari EDIT JENIS
        if ($request->from === 'edit-jenis' && $request->id_layanan && $request->id_jenis) {
            return redirect()->route('admin2.layanan.jenis.edit', [
                'id' => $request->id_jenis,
                'new_satuan' => $satuan->id_satuan
            ])->with('success', 'Satuan berhasil ditambahkan.');
        }

        // 3. Default ke index satuan
        return redirect()->route('admin2.satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function satuanEditAdmin2($id)
    {
        // ✅ CHECK PERMISSION EDIT
        requirePermission('satuan', 'edit');
        
        $satuan = Satuan::where('id_satuan', $id)->firstOrFail();
        return view('admin2.satuan.edit', compact('satuan'));
    }

    public function satuanUpdateAdmin2(Request $request, $id)
    {
        $request->validate([
            'nama_satuan' => 'required',
        ], [
           'nama_satuan.required' => 'Nama Satuan tidak boleh kosong.',
        ]);

        // ✅ CHECK PERMISSION EDIT
        requirePermission('satuan', 'edit');
        
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
        // ✅ CHECK PERMISSION DELETE
        requirePermission('satuan', 'delete');
        
        Satuan::where('id_satuan', $id)->delete();

        return redirect()->route('admin2.satuan.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}