<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Driver;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =========================
    // REGISTER (khusus pelanggan)
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

        // ✅ Register tanpa jk, biarkan null (diisi saat edit profil)
        $pelanggan = Pelanggan::create([
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp'          => $request->no_hp,
            'alamat'         => $request->alamat,
            'password'       => Hash::make($request->password),
            'jk'             => null,
            'gambar'         => null,
        ]);

        // Generate token Sanctum
        $token = $pelanggan->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil',
            'role'    => 'pelanggan',
            'token'   => $token,
            'data'    => [
                'id_pelanggan'   => $pelanggan->id_pelanggan,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
                'no_hp'          => $pelanggan->no_hp,
                'alamat'         => $pelanggan->alamat,
                'jk'             => $pelanggan->jk,
                'gambar'         => $pelanggan->gambar,
            ]
        ], 201);
    }


    // =========================
    // LOGIN
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'no_telp'  => 'required',
            'password' => 'required',
        ]);

        $role = null;
        $user = null;

        // ============================
        // CEK PELANGGAN
        // ============================
        $user = Pelanggan::where('no_hp', $request->no_telp)->first();
        if ($user) {
            $role = 'pelanggan';
        }

        // ============================
        // JIKA TIDAK ADA → CEK DRIVER
        // ============================
        if (!$user) {
            $user = Driver::where('no_telp', $request->no_telp)->first();
            if ($user) {
                $role = 'driver';
            }
        }

        // ============================
        // NO TELP TIDAK DITEMUKAN
        // ============================
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Nomor telepon tidak ditemukan'
            ], 404);
        }

        // ============================
        // CEK PASSWORD
        // ============================
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password salah'
            ], 401);
        }

        // ============================
        // LOGIN BERHASIL → BUAT TOKEN
        // ============================
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ Format response data sesuai role
        $responseData = null;
        if ($role === 'pelanggan') {
            $responseData = [
                'id_pelanggan'   => $user->id_pelanggan,
                'nama_pelanggan' => $user->nama_pelanggan,
                'no_hp'          => $user->no_hp,
                'alamat'         => $user->alamat,
                'jk'             => $user->jk,
                'gambar'         => $user->gambar,
            ];
        } elseif ($role === 'driver') {
            $responseData = [
                'id_driver'   => $user->id_driver,
                'nama_driver' => $user->nama_driver,
                'no_telp'     => $user->no_telp,
                'alamat'      => $user->alamat,
                'gambar'      => $user->gambar,
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil',
            'role'    => $role,
            'token'   => $token,
            'data'    => $responseData,
        ], 200);
    }


    // =========================
    // LOGOUT
    // =========================
    public function logout(Request $request)
    {
        // Hapus token aktif
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}