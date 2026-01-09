<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    protected $table = 'menu_role';

    protected $fillable = [
        'role_id',
        'menu_id',
        'is_active',          // ← TAMBAH INI
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'can_cancel',         // ← TAMBAH INI
        'can_change_password', // ← TAMBAH INI
        'can_restore_data',    // ← TAMBAH INI
        'show_delete_backup',  // ← TAMBAH INI
        'show_logout',         // ← TAMBAH INI
        'can_access_settings', // ← TAMBAH INI
    ];

    // ← TAMBAH CASTING UNTUK BOOLEAN
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

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // ← TAMBAH HELPER METHOD
    /**
     * Check apakah role punya permission tertentu untuk menu tertentu
     */
    public static function hasPermission($roleId, $menuId, $permission)
    {
        $menuRole = self::where('role_id', $roleId)
            ->where('menu_id', $menuId)
            ->first();

        if (!$menuRole) {
            return false;
        }

        return $menuRole->{$permission} ?? false;
    }

    /**
     * Get all permissions untuk role tertentu
     */
    public static function getRolePermissions($roleId)
    {
        return self::where('role_id', $roleId)->get()->keyBy('menu_id');
    }
}