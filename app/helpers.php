<?php

use App\Models\MenuRole;
use Illuminate\Support\Facades\Auth;

// ============================================================
// FILE HELPER GLOBAL APLIKASI
// Fungsi:
// 1. Menyediakan helper permission sederhana
// 2. Menyediakan helper identitas user aktif lintas guard
// 3. Menjaga agar controller dan view lebih ringkas
//
// Konsep yang tampak pada file ini:
// - Function/method helper
// - Percabangan if
// - Class-object melalui Auth facade dan model MenuRole
// - Penanganan kondisi error sederhana saat user belum login
// ============================================================

// ========================================
// HELPER YANG SUDAH ADA (JANGAN DIHAPUS)
// Digunakan untuk cek permission langsung berdasarkan route menu.
// ========================================
if (!function_exists('can')) {
    function can($menuRoute, $action = 'view')
    {
        // [PERCABANGAN] Tentukan user aktif dari guard admin atau kasir.
        $user = Auth::guard('admin')->check()
            ? Auth::guard('admin')->user()
            : Auth::guard('kasir')->user();

        // [PENANGANAN KONDISI] Jika belum ada user login, helper langsung false.
        if (!$user) return false;

        // [CLASS-OBJECT + METHOD] Query ke model MenuRole untuk mencari hak akses.
        $permission = MenuRole::where('role_id', $user->role_id)
            ->whereHas('menu', function ($q) use ($menuRoute) {
                $q->where('route', $menuRoute);
            })
            ->first();

        // [PERCABANGAN DINAMIS] Nama kolom disusun dari action, mis. can_view/can_edit.
        return $permission && ($permission->{'can_'.$action} ?? false);
    }
}

// ========================================
// HELPER BARU - AUTHENTICATION
// Kumpulan helper untuk mendapatkan data user aktif dengan cepat.
// ========================================

if (!function_exists('current_user')) {
    /**
     * Get current authenticated user (admin or kasir)
     */
    function current_user()
    {
        // [PERCABANGAN] Prioritaskan guard admin jika aktif.
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }
        
        // [PERCABANGAN] Jika bukan admin, cek guard kasir.
        if (Auth::guard('kasir')->check()) {
            return Auth::guard('kasir')->user();
        }
        
        // [PENANGANAN KONDISI] Tidak ada user aktif.
        return null;
    }
}

if (!function_exists('current_user_id')) {
    /**
     * Get current authenticated user ID
     */
    function current_user_id()
    {
        // [PERCABANGAN] Ambil ID sesuai guard yang sedang aktif.
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->id();
        }
        
        if (Auth::guard('kasir')->check()) {
            return Auth::guard('kasir')->id();
        }
        
        // [PENANGANAN KONDISI] Null bila tidak ada user login.
        return null;
    }
}

if (!function_exists('current_user_name')) {
    /**
     * Get current authenticated user name
     */
    function current_user_name()
    {
        // [METHOD] Memanggil helper lain agar tidak duplikasi logika.
        $user = current_user();
        
        if (!$user) {
            return 'System';
        }
        
        // [PERCABANGAN NULL COALESCING]
        // Admin punya field `nama`, kasir punya field `nama_kasir`.
        return $user->nama ?? $user->nama_kasir ?? 'Unknown';
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if current user is admin/super admin
     */
    function is_admin()
    {
        // [METHOD] Cek guard admin aktif atau tidak.
        return Auth::guard('admin')->check();
    }
}

if (!function_exists('is_kasir')) {
    /**
     * Check if current user is kasir
     */
    function is_kasir()
    {
        // [METHOD] Cek guard kasir aktif atau tidak.
        return Auth::guard('kasir')->check();
    }
}

if (!function_exists('current_guard')) {
    /**
     * Get current active guard name
     */
    function current_guard()
    {
        // [PERCABANGAN] Identifikasi nama guard aktif untuk dipakai di logika lain.
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }
        
        if (Auth::guard('kasir')->check()) {
            return 'kasir';
        }
        
        // [PENANGANAN KONDISI] Null jika belum ada sesi autentikasi aktif.
        return null;
    }
}
