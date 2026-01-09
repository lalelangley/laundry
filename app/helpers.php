<?php

use App\Models\MenuRole;
use Illuminate\Support\Facades\Auth;

// ========================================
// HELPER YANG SUDAH ADA (JANGAN DIHAPUS)
// ========================================
if (!function_exists('can')) {
    function can($menuRoute, $action = 'view')
    {
        $user = Auth::guard('admin')->check()
            ? Auth::guard('admin')->user()
            : Auth::guard('kasir')->user();

        if (!$user) return false;

        $permission = MenuRole::where('role_id', $user->role_id)
            ->whereHas('menu', function ($q) use ($menuRoute) {
                $q->where('route', $menuRoute);
            })
            ->first();

        return $permission && ($permission->{'can_'.$action} ?? false);
    }
}

// ========================================
// HELPER BARU - AUTHENTICATION
// ========================================

if (!function_exists('current_user')) {
    /**
     * Get current authenticated user (admin or kasir)
     */
    function current_user()
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }
        
        if (Auth::guard('kasir')->check()) {
            return Auth::guard('kasir')->user();
        }
        
        return null;
    }
}

if (!function_exists('current_user_id')) {
    /**
     * Get current authenticated user ID
     */
    function current_user_id()
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->id();
        }
        
        if (Auth::guard('kasir')->check()) {
            return Auth::guard('kasir')->id();
        }
        
        return null;
    }
}

if (!function_exists('current_user_name')) {
    /**
     * Get current authenticated user name
     */
    function current_user_name()
    {
        $user = current_user();
        
        if (!$user) {
            return 'System';
        }
        
        // Admin punya field 'nama'
        // Kasir punya field 'nama_kasir'
        return $user->nama ?? $user->nama_kasir ?? 'Unknown';
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if current user is admin/super admin
     */
    function is_admin()
    {
        return Auth::guard('admin')->check();
    }
}

if (!function_exists('is_kasir')) {
    /**
     * Check if current user is kasir
     */
    function is_kasir()
    {
        return Auth::guard('kasir')->check();
    }
}

if (!function_exists('current_guard')) {
    /**
     * Get current active guard name
     */
    function current_guard()
    {
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }
        
        if (Auth::guard('kasir')->check()) {
            return 'kasir';
        }
        
        return null;
    }
}