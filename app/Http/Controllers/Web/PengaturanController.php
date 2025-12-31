<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    // =============================
    // INDEX
    // =============================
    public function index()
    {
        $pengaturan = DB::table('pengaturan')
            ->pluck('value', 'key')
            ->toArray();

        return view('pengaturan.index', compact('pengaturan'));
    }

    // =============================
    // UPDATE PENGATURAN UMUM
    // =============================
    public function update(Request $request)
    {
        $request->validate([
            'nama_outlet'   => 'required|string|max:255',
            'alamat_outlet' => 'required|string',
            'foto_outlet'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'nama_outlet'],
            ['value' => $request->nama_outlet]
        );

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'alamat_outlet'],
            ['value' => $request->alamat_outlet]
        );

        if ($request->hasFile('foto_outlet')) {

            $path = $request->file('foto_outlet')
                ->store('outlet', 'public');

            DB::table('pengaturan')->updateOrInsert(
                ['key' => 'foto_outlet'],
                ['value' => $path]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    }

    // =============================
    // UPDATE OMZET
    // =============================
    public function updateOmzet(Request $request)
    {
        $request->validate([
            'hitung_omzet_dari' => 'required|in:selesai,lunas'
        ]);

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'hitung_omzet_dari'],
            ['value' => $request->hitung_omzet_dari]
        );

        return response()->json([
            'status' => true,
            'message' => 'Pengaturan omzet disimpan'
        ]);
    }

    // =============================
    // BACKUP (DUMMY)
    // =============================
    public function backup()
    {
        return response()->json([
            'status' => true,
            'message' => 'Backup berhasil'
        ]);
    }

    // =============================
    // RESTORE (DUMMY)
    // =============================
    public function restore()
    {
        return response()->json([
            'status' => true,
            'message' => 'Restore berhasil'
        ]);
    }

     // =============================
    // HALAMAN METODE PEMBAYARAN
    // =============================
    public function metodeBayar()
    {
        $metode = DB::table('metode_bayar')
            ->orderBy('nama_metode_bayar')
            ->get();

        return view('pengaturan.metode_bayar', compact('metode'));
    }

    // =============================
    // SIMPAN METODE PEMBAYARAN
    // =============================
    public function storeMetodeBayar(Request $request)
    {
        $request->validate([
            'nama_metode_bayar' => 'required|string|max:100'
        ]);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true]);
    }

    // =============================
    // HAPUS METODE PEMBAYARAN
    // =============================
    public function deleteMetodeBayar($id)
    {
        DB::table('metode_bayar')
            ->where('id_metode_bayar', $id)
            ->delete();

        return response()->json(['status' => true]);
    }

     // =============================
    // INDEX
    // =============================
    public function indexKasir()
    {
        $pengaturan = DB::table('pengaturan')
            ->pluck('value', 'key')
            ->toArray();

        return view('kasir.pengaturan.index', compact('pengaturan'));
    }

    // =============================
    // UPDATE PENGATURAN UMUM
    // =============================
    public function updateKasir(Request $request)
    {
        $request->validate([
            'nama_outlet'   => 'required|string|max:255',
            'alamat_outlet' => 'required|string',
            'foto_outlet'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'nama_outlet'],
            ['value' => $request->nama_outlet]
        );

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'alamat_outlet'],
            ['value' => $request->alamat_outlet]
        );

        if ($request->hasFile('foto_outlet')) {

            $path = $request->file('foto_outlet')
                ->store('outlet', 'public');

            DB::table('pengaturan')->updateOrInsert(
                ['key' => 'foto_outlet'],
                ['value' => $path]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    }

    // =============================
    // UPDATE OMZET
    // =============================
    public function updateOmzetKasir(Request $request)
    {
        $request->validate([
            'hitung_omzet_dari' => 'required|in:selesai,lunas'
        ]);

        DB::table('pengaturan')->updateOrInsert(
            ['key' => 'hitung_omzet_dari'],
            ['value' => $request->hitung_omzet_dari]
        );

        return response()->json([
            'status' => true,
            'message' => 'Pengaturan omzet disimpan'
        ]);
    }

    // =============================
    // BACKUP (DUMMY)
    // =============================
    public function backupKasir()
    {
        return response()->json([
            'status' => true,
            'message' => 'Backup berhasil'
        ]);
    }

    // =============================
    // RESTORE (DUMMY)
    // =============================
    public function restoreKasir()
    {
        return response()->json([
            'status' => true,
            'message' => 'Restore berhasil'
        ]);
    }

     // =============================
    // HALAMAN METODE PEMBAYARAN
    // =============================
    public function metodeBayarKasir()
    {
        $metode = DB::table('metode_bayar')
            ->orderBy('nama_metode_bayar')
            ->get();

        return view('kasir.pengaturan.metode_bayar', compact('metode'));
    }

    // =============================
    // SIMPAN METODE PEMBAYARAN
    // =============================
    public function storeMetodeBayarKasir(Request $request)
    {
        $request->validate([
            'nama_metode_bayar' => 'required|string|max:100'
        ]);

        DB::table('metode_bayar')->insert([
            'nama_metode_bayar' => $request->nama_metode_bayar,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => true]);
    }

    // =============================
    // HAPUS METODE PEMBAYARAN
    // =============================
    public function deleteMetodeBayarKasir($id)
    {
        DB::table('metode_bayar')
            ->where('id_metode_bayar', $id)
            ->delete();

        return response()->json(['status' => true]);
    }
}
