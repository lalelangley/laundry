<?php
// File: app/Helpers/PermissionHelper.php

if (!function_exists('canView')) {
    function canView(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'view');
    }
}

if (!function_exists('canAdd')) {
    function canAdd(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'add');
    }
}

if (!function_exists('canEdit')) {
    function canEdit(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'edit');
    }
}

if (!function_exists('canDelete')) {
    function canDelete(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'delete');
    }
}

if (!function_exists('requirePermission')) {
    /**
     * ✅ FOOLPROOF - Bypass Super Admin dengan type juggling
     */
    function requirePermission(string $menuIdentifier, string $action): void
    {
        // ✅ BYPASS SUPER ADMIN - Cast to int untuk handle string/int
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if ($user && (int)$user->role_id === 1) {
            return; // Super Admin ALWAYS bypass
        }
        
        // Check permission untuk user biasa
        if (!checkPermission($menuIdentifier, $action)) {
            abort(403, "Anda tidak memiliki izin untuk {$action} di menu ini");
        }
    }
}

if (!function_exists('isMenuActive')) {
    function isMenuActive(string $menuIdentifier): bool
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return false;
        }
        
        // ✅ Cast to int
        if ((int)$user->role_id === 1) {
            return true;
        }
        
        $permission = \App\Models\MenuRole::whereHas('menu', function($query) use ($menuIdentifier) {
                $query->where('route', 'like', "%{$menuIdentifier}%")
                      ->orWhere('nama_menu', 'like', "%{$menuIdentifier}%");
            })
            ->where('role_id', $user->role_id)
            ->first();
        
        if (!$permission) {
            return false;
        }
        
        return $permission->is_active ?? false;
    }
}

if (!function_exists('checkPermission')) {
    function checkPermission(string $menuIdentifier, string $action): bool
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return false;
        }
        
        // ✅ Cast to int - Handle both string "1" and integer 1
        if ((int)$user->role_id === 1) {
            return true;
        }
        
        $permission = \App\Models\MenuRole::whereHas('menu', function($query) use ($menuIdentifier, $user) {
                $query->where('role_id', $user->role_id)
                      ->where(function($q) use ($menuIdentifier) {
                          $q->where('route', 'like', "%{$menuIdentifier}%")
                            ->orWhere('nama_menu', 'like', "%{$menuIdentifier}%");
                      });
            })
            ->where('role_id', $user->role_id)
            ->first();
        
        if (!$permission) {
            return false;
        }
        
        // Check toggle ON/OFF
        if (!$permission->is_active) {
            return false;
        }
        
        // Check specific permission
        return $permission->{"can_{$action}"} ?? false;
    }
}

if (!function_exists('getUserPermissions')) {
    function getUserPermissions(): array
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return [];
        }
        
        // ✅ Cast to int
        if ((int)$user->role_id === 1) {
            return [
                'all' => true,
                'view' => true,
                'add' => true,
                'edit' => true,
                'delete' => true,
            ];
        }
        
        $permissions = \App\Models\MenuRole::where('role_id', $user->role_id)
            ->with('menu')
            ->get()
            ->mapWithKeys(function($perm) {
                $menuKey = $perm->menu->route ?? $perm->menu->nama_menu;
                return [
                    $menuKey => [
                        'active' => $perm->is_active,
                        'view' => $perm->can_view,
                        'add' => $perm->can_add,
                        'edit' => $perm->can_edit,
                        'delete' => $perm->can_delete,
                    ]
                ];
            })
            ->toArray();
        
        return $permissions;
    }
}