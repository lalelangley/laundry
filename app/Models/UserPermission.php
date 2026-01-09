<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = [
        'user_type',
        'user_id',
        'menu_id',
        'is_active',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'can_cancel',
        'can_change_password',
        'can_restore_data',
        'show_delete_backup',
        'show_logout',
        'can_access_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_view' => 'boolean',
        'can_add' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_cancel' => 'boolean',
        'can_change_password' => 'boolean',
        'can_restore_data' => 'boolean',
        'show_delete_backup' => 'boolean',
        'show_logout' => 'boolean',
        'can_access_settings' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Check apakah user punya permission tertentu untuk menu tertentu
     * Priority: User Permission > Role Permission
     */
    public static function hasPermission($userType, $userId, $menuId, $permission)
    {
        // Cek user permission dulu (lebih prioritas)
        $userPerm = self::where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('menu_id', $menuId)
            ->first();

        if ($userPerm) {
            return $userPerm->{$permission} ?? false;
        }

        // Kalau ga ada user permission, fallback ke role permission
        $roleId = self::getUserRoleId($userType, $userId);
        
        if ($roleId) {
            return MenuRole::hasPermission($roleId, $menuId, $permission);
        }

        return false;
    }

    /**
     * Helper untuk get role_id dari user
     */
    private static function getUserRoleId($userType, $userId)
    {
        if ($userType === 'admin') {
            $user = \App\Models\Admin::find($userId);
            return $user->role_id ?? null;
        } elseif ($userType === 'kasir') {
            $user = \App\Models\Kasir::find($userId);
            return $user->role_id ?? 3; // Default role kasir
        }
        
        return null;
    }

    /**
     * Get all permissions untuk user tertentu
     */
    public static function getUserPermissions($userType, $userId)
    {
        return self::where('user_type', $userType)
            ->where('user_id', $userId)
            ->get()
            ->keyBy('menu_id');
    }
}