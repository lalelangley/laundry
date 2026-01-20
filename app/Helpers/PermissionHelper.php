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
    function requirePermission(string $menuIdentifier, string $action): void
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if ($user && (int)$user->role_id === 1) {
            return; // Super Admin ALWAYS bypass
        }

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

        // ✅ Super Admin bypass
        if ((int)$user->role_id === 1) {
            return true;
        }

        // ✅ PERBAIKAN: Hapus filter menu.role_id
        $permission = \App\Models\MenuRole::join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->where('menu_role.role_id', $user->role_id)
            // ❌ HAPUS: ->where('menu.role_id', $user->role_id)
            ->where(function($query) use ($menuIdentifier) {
                $query->where('menu.route', 'like', "%{$menuIdentifier}%")
                      ->orWhere('menu.nama_menu', 'like', "%{$menuIdentifier}%");
            })
            ->select('menu_role.*')
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

        // ✅ Super Admin bypass
        if ((int)$user->role_id === 1) {
            return true;
        }

        // ✅ Normalisasi menu identifier
        $normalizedIdentifiers = [
            $menuIdentifier,
            str_replace('.', '-', $menuIdentifier),
            str_replace('.', ' ', $menuIdentifier),
            ucwords(str_replace('.', ' ', $menuIdentifier)),
        ];

        // ✅ PERBAIKAN UTAMA: Hapus filter menu.role_id
        $permission = \App\Models\MenuRole::join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->where('menu_role.role_id', $user->role_id)
            // ❌ HAPUS LINE INI: ->where('menu.role_id', $user->role_id)
            ->where(function($query) use ($normalizedIdentifiers) {
                foreach ($normalizedIdentifiers as $identifier) {
                    $query->orWhere('menu.route', 'like', "%{$identifier}%")
                          ->orWhere('menu.nama_menu', 'like', "%{$identifier}%");
                }
            })
            ->select('menu_role.*')
            ->first();

        if (!$permission) {
            \Log::warning("Permission not found", [
                'menu_identifier' => $menuIdentifier,
                'user_role' => $user->role_id,
                'action' => $action,
                'tried_identifiers' => $normalizedIdentifiers
            ]);
            return false;
        }

        // ✅ Check toggle ON/OFF
        if (!$permission->is_active) {
            \Log::info("Menu inactive", [
                'menu_identifier' => $menuIdentifier,
                'user_role' => $user->role_id
            ]);
            return false;
        }

        // ✅ Check specific permission
        $columnName = "can_{$action}";
        $hasPermission = $permission->{$columnName} ?? false;
        
        if (!$hasPermission) {
            \Log::info("Permission denied", [
                'menu_identifier' => $menuIdentifier,
                'user_role' => $user->role_id,
                'action' => $action,
                'column' => $columnName,
                'value' => $permission->{$columnName} ?? 'null'
            ]);
        }

        return $hasPermission;
    }
}

if (!function_exists('getUserPermissions')) {
    function getUserPermissions(): array
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return [];
        }

        // ✅ Super Admin bypass
        if ((int)$user->role_id === 1) {
            return [
                'all' => true,
                'view' => true,
                'add' => true,
                'edit' => true,
                'delete' => true,
            ];
        }

        // ✅ PERBAIKAN: Hapus filter menu.role_id
        $permissions = \App\Models\MenuRole::where('menu_role.role_id', $user->role_id)
            ->join('menu', 'menu_role.menu_id', '=', 'menu.id')
            // ❌ HAPUS: ->where('menu.role_id', $user->role_id)
            ->select('menu_role.*', 'menu.route', 'menu.nama_menu')
            ->get()
            ->mapWithKeys(function($perm) {
                $menuKey = $perm->route ?? $perm->nama_menu;
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