<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Storage;
>>>>>>> ec71b7f8041b236f868e90c29181cb01e7e93c8d

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

<<<<<<< HEAD
=======
        // BIKIN URL LENGKAP
        $pelanggan->gambar_url = $pelanggan->gambar
            ? asset("storage/" . $pelanggan->gambar)
            : null;

>>>>>>> ec71b7f8041b236f868e90c29181cb01e7e93c8d
        return response()->json([
            'status' => true,
            'data'   => $pelanggan
        ]);
    }

<<<<<<< HEAD
    // Update profile berdasarkan id
=======
>>>>>>> ec71b7f8041b236f868e90c29181cb01e7e93c8d
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

<<<<<<< HEAD
    // Update foto profile berdasarkan id
    public function updateGambar(Request $request, $id)
    {
        $request->validate([
            'gambar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $pelanggan = Pelanggan::find($id);
        if (!$pelanggan) {
            return response()->json([
                'status' => false,
                'message' => 'Pelanggan tidak ditemukan'
            ], 404);
        }

        $path = $request->file('gambar')->store('pelanggan', 'public');

        $pelanggan->update([
            'gambar' => $path,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Foto profile berhasil diupdate',
            'gambar'  => $path,
        ]);
    }
}
=======
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

>>>>>>> ec71b7f8041b236f868e90c29181cb01e7e93c8d
