<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pelanggan;

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

        return response()->json([
            'status' => true,
            'data'   => $pelanggan
        ]);
    }

    // Update profile berdasarkan id
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
