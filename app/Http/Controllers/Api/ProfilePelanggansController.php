<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Storage;

class ProfilePelanggansController extends Controller
{
    public function show($id)
    {
        $pelanggan = Pelanggan::find($id);

        if (!$pelanggan) {
            return response()->json([
                'status' => false,
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        }

        // BIKIN URL LENGKAP
        $pelanggan->gambar_url = $pelanggan->gambar
            ? asset("storage/" . $pelanggan->gambar)
            : null;

        return response()->json([
            'status' => true,
            'data'   => $pelanggan
        ]);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = Pelanggan::find($id);
        if (!$pelanggan) {
            return response()->json([
                'status' => false,
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp'          => 'required|string|max:20',
            'alamat'         => 'nullable|string',
            'jk'             => 'nullable|in:L,P',
        ]);

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'jk'             => $request->jk,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Profile berhasil diperbarui',
            'data'    => $pelanggan
        ]);
    }

  public function updateGambar(Request $request, $id)
{
    $pelanggan = Pelanggan::find($id);

    if (!$pelanggan) {
        return response()->json([
            "status" => false,
            "message" => "Data pelanggan tidak ditemukan"
        ]);
    }

    if ($request->hasFile('gambar')) {
        $file = $request->file('gambar');
        $path = $file->store('uploads/pelanggan', 'public');

        $pelanggan->gambar = $path;
        $pelanggan->save();

        return response()->json([
            "status" => true,
            "message" => "Berhasil update foto",
            "gambar_url" => asset('storage/' . $path)
        ]);
    }

    return response()->json([
        "status" => false,
        "message" => "Tidak ada file gambar"
    ]);
}
}

