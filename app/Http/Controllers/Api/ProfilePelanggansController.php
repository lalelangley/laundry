<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

        $pelanggan->gambar_url = $pelanggan->gambar
            ? url("storage/" . $pelanggan->gambar)
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

        $validator = Validator::make($request->all(), [
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp'          => 'required|string|max:20',
            'alamat'         => 'nullable|string',
            'jk'             => 'nullable|in:L,P',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $pelanggan->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat ?? $pelanggan->alamat,
            'jk'             => $request->jk ?? $pelanggan->jk,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diperbarui',
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
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'gambar' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'File harus berupa gambar (jpeg, png, jpg) dan maksimal 2MB',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('gambar')) {
            if ($pelanggan->gambar && Storage::disk('public')->exists($pelanggan->gambar)) {
                Storage::disk('public')->delete($pelanggan->gambar);
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pelanggan', $filename, 'public');

            $pelanggan->gambar = $path;
            $pelanggan->save();

            return response()->json([
                "status" => true,
                "message" => "Foto profil berhasil diperbarui",
                "gambar_url" => url('storage/' . $path)
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Tidak ada file gambar yang diupload"
        ], 400);
    }
}