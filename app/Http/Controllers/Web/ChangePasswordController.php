<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * ChangePasswordController
 *
 * Controller untuk menangani perubahan password pada tiga jenis user:
 * - Super Admin (guard: admin, role_id: 1)
 * - Kasir (guard: kasir)
 * - Admin Biasa / Admin2 (guard: admin, role_id: 2)
 */
class ChangePasswordController extends Controller
{
    // =========================
    // SUPER ADMIN
    // =========================

    /**
     * Menampilkan halaman form ganti password untuk Super Admin.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('password.index');
    }

    /**
     * Memproses perubahan password untuk Super Admin.
     *
     * Alur:
     * 1. Validasi input (password lama, password baru, konfirmasi)
     * 2. Ambil user yang sedang login via guard 'admin'
     * 3. Verifikasi password lama
     * 4. Pastikan password baru berbeda dari password lama
     * 5. Simpan password baru (di-hash)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Validasi input: password lama wajib ada, password baru minimal 8 karakter dan harus dikonfirmasi
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min'      => 'Password baru minimal 8 karakter',
            'new_password.confirmed'=> 'Konfirmasi password tidak cocok',
        ]);

        // Jika validasi gagal, kembalikan pesan error pertama dengan status 422
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Ambil data admin yang sedang login menggunakan guard 'admin'
        $admin = Auth::guard('admin')->user();

        // Jika user tidak ditemukan (sesi habis atau tidak terautentikasi), kembalikan 401
        if (!$admin) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        // Periksa apakah password lama yang dimasukkan sesuai dengan yang tersimpan di database
        if (!Hash::check($request->old_password, $admin->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        // Pastikan password baru tidak sama dengan password lama (untuk keamanan)
        if (Hash::check($request->new_password, $admin->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password baru tidak boleh sama dengan password lama'
            ], 422);
        }

        // Hash password baru dan simpan ke database
        $admin->password = Hash::make($request->new_password);
        $admin->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    // =========================
    // KASIR
    // =========================

    /**
     * Menampilkan halaman form ganti password untuk Kasir.
     *
     * @return \Illuminate\View\View
     */
    public function indexKasir()
    {
        return view('kasir.password.index');
    }

    /**
     * Memproses perubahan password untuk Kasir.
     *
     * Alur sama seperti Super Admin, namun menggunakan guard 'kasir'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateKasir(Request $request)
    {
        // Validasi input: password lama wajib ada, password baru minimal 8 karakter dan harus dikonfirmasi
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min'      => 'Password baru minimal 8 karakter',
            'new_password.confirmed'=> 'Konfirmasi password tidak cocok',
        ]);

        // Jika validasi gagal, kembalikan pesan error pertama dengan status 422
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Ambil data kasir yang sedang login menggunakan guard 'kasir'
        $kasir = Auth::guard('kasir')->user();

        // Jika user tidak ditemukan (sesi habis atau tidak terautentikasi), kembalikan 401
        if (!$kasir) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        // Periksa apakah password lama yang dimasukkan sesuai dengan yang tersimpan di database
        if (!Hash::check($request->old_password, $kasir->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        // Pastikan password baru tidak sama dengan password lama (untuk keamanan)
        if (Hash::check($request->new_password, $kasir->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password baru tidak boleh sama dengan password lama'
            ], 422);
        }

        // Hash password baru dan simpan ke database
        $kasir->password = Hash::make($request->new_password);
        $kasir->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    // =========================
    // ADMIN BIASA (ADMIN2)
    // =========================

    /**
     * Menampilkan halaman form ganti password untuk Admin Biasa (Admin2).
     *
     * @return \Illuminate\View\View
     */
    public function indexAdmin2()
    {
        return view('admin2.password.index');
    }

    /**
     * Memproses perubahan password untuk Admin Biasa (Admin2).
     *
     * Catatan: Admin2 menggunakan guard 'admin' yang sama dengan Super Admin,
     * namun dibedakan berdasarkan role_id = 2.
     *
     * Alur:
     * 1. Validasi input
     * 2. Ambil user yang login via guard 'admin'
     * 3. Pastikan role_id = 2 (admin biasa), bukan Super Admin
     * 4. Verifikasi password lama
     * 5. Pastikan password baru berbeda dari password lama
     * 6. Simpan password baru (di-hash)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAdmin2(Request $request)
    {
        // Validasi input: password lama wajib ada, password baru minimal 8 karakter dan harus dikonfirmasi
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'old_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min'      => 'Password baru minimal 8 karakter',
            'new_password.confirmed'=> 'Konfirmasi password tidak cocok',
        ]);

        // Jika validasi gagal, kembalikan pesan error pertama dengan status 422
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Ambil data admin yang sedang login menggunakan guard 'admin'
        // (Admin2 berbagi guard dengan Super Admin, dibedakan oleh role_id)
        $admin2 = Auth::guard('admin')->user();

        // Jika user tidak ditemukan (sesi habis atau tidak terautentikasi), kembalikan 401
        if (!$admin2) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan'
            ], 401);
        }

        // Pastikan user yang mengakses adalah Admin Biasa (role_id = 2), bukan Super Admin
        // Ini mencegah Super Admin menggunakan endpoint khusus Admin2
        if ($admin2->role_id != 2) {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya admin biasa yang bisa mengakses halaman ini'
            ], 403);
        }

        // Periksa apakah password lama yang dimasukkan sesuai dengan yang tersimpan di database
        if (!Hash::check($request->old_password, $admin2->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password lama tidak sesuai'
            ], 422);
        }

        // Pastikan password baru tidak sama dengan password lama (untuk keamanan)
        if (Hash::check($request->new_password, $admin2->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password baru tidak boleh sama dengan password lama'
            ], 422);
        }

        // Hash password baru dan simpan ke database
        $admin2->password = Hash::make($request->new_password);
        $admin2->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password berhasil diubah'
        ]);
    }
}