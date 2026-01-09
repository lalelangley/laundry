<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    // =========================
    // ADMIN (SUPER ADMIN)
    // =========================
    
    /**
     * Display the change password form
     */
    public function index()
    {
        return view('password.index');
    }

    /**
     * Update the password
     */
    public function update(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Get authenticated admin
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        // Check if new password is same as old password
        if (Hash::check($request->new_password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password baru tidak boleh sama dengan password lama'
            ], 422);
        }

        // Update password
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    // =========================
    // KASIR
    // =========================

    public function indexKasir()
    {
        return view('kasir.password.index');
    }

    /**
     * Update the password
     */
    public function updateKasir(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Get authenticated kasir
        $kasir = Auth::guard('kasir')->user();

        if (!$kasir) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password, $kasir->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        // Check if new password is same as old password
        if (Hash::check($request->new_password, $kasir->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password baru tidak boleh sama dengan password lama'
            ], 422);
        }

        // Update password
        $kasir->password = Hash::make($request->new_password);
        $kasir->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    // =========================
    // ADMIN2 (ADMIN BIASA)
    // =========================

    public function indexAdmin2()
    {
        return view('admin2.password.index');
    }

    /**
     * Update the password - ADMIN2
     */
    public function updateAdmin2(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // ✅ FIX: Ganti guard admin2 → admin
        $admin2 = Auth::guard('admin')->user();

        if (!$admin2) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        // ✅ OPTIONAL: Pastikan yang login adalah admin biasa (role_id = 2)
        if ($admin2->role_id != 2) {
            return response()->json([
                'status' => false,
                'message' => 'Hanya admin biasa yang bisa mengakses halaman ini'
            ], 403);
        }

        // Check if old password is correct
        if (!Hash::check($request->old_password, $admin2->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        // Check if new password is same as old password
        if (Hash::check($request->new_password, $admin2->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Password baru tidak boleh sama dengan password lama'
            ], 422);
        }

        // Update password
        $admin2->password = Hash::make($request->new_password);
        $admin2->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}