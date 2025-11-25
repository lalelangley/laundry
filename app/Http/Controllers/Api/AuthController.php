<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =========================
    // REGISTER
    // =========================
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp'          => 'required|string|max:20|unique:pelanggan,no_hp',
            'alamat'         => 'required|string',
            'password'       => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'password'       => Hash::make($request->password),
            'jk'             => null,
            'gambar'         => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data'    => $pelanggan,
        ], 201);
    }


    // =========================
    // LOGIN
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'no_hp'    => 'required',
            'password' => 'required',
        ]);

        $pelanggan = Pelanggan::where('no_hp', $request->no_hp)->first();

        if (!$pelanggan) {
            return response()->json([
                'status'  => false,
                'message' => 'Nomor HP tidak ditemukan'
            ], 404);
        }

        if (!Hash::check($request->password, $pelanggan->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password salah'
            ], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil',
            'data'    => $pelanggan
        ], 200);
    }
}
