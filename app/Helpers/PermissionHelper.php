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
            abort(403, "Anda tidak memiliki hak akses untuk {$action} pada menu ini");
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

        if ((int)$user->role_id === 1) {
            return true;
        }

        $permission = \App\Models\MenuRole::join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->where('menu_role.role_id', $user->role_id)
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

        // Super Admin bypass
        if ((int)$user->role_id === 1) {
            return true;
        }

        // ✅ PERBAIKAN UTAMA: Ambil semua menu milik role ini dulu,
        // lalu cek apakah current route cocok dengan salah satu route menu yang ada.
        // Ini menghindari masalah partial matching yang salah.
        $currentRoute = request()->route() ? request()->route()->getName() : '';

        $permissions = \App\Models\MenuRole::join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->where('menu_role.role_id', $user->role_id)
            ->select('menu_role.*', 'menu.route as menu_route', 'menu.nama_menu')
            ->get();

        // Cari permission yang paling cocok
        $permission = null;

        foreach ($permissions as $perm) {
            $menuRoute = $perm->menu_route ?? '';
            $menuName  = strtolower($perm->nama_menu ?? '');
            $identifier = strtolower($menuIdentifier);

            // 1. Exact match route
            if ($menuRoute === $menuIdentifier) {
                $permission = $perm;
                break;
            }

            // 2. Current route starts with menu route (misal: kasir.transaksi.create cocok dengan kasir.transaksi.create)
            if ($menuRoute && str_starts_with($currentRoute, $menuRoute)) {
                $permission = $perm;
                break;
            }

            // 3. Identifier contains menu route segment
            if ($menuRoute && str_contains($menuRoute, $identifier)) {
                $permission = $perm;
            }

            // 4. Match nama menu
            if ($menuName && str_contains($menuName, $identifier)) {
                $permission = $perm;
            }
        }

        // ✅ LOG untuk debug
        \Log::info("CheckPermission", [
            'identifier'       => $menuIdentifier,
            'current_route'    => $currentRoute,
            'role'             => $user->role_id,
            'action'           => $action,
            'permission_found' => $permission ? $permission->toArray() : null,
        ]);

        if (!$permission) {
            \Log::warning("Permission not found", [
                'menu_identifier' => $menuIdentifier,
                'user_role'       => $user->role_id,
                'action'          => $action,
            ]);
            return false;
        }

        // Cek apakah menu aktif
        if (!$permission->is_active) {
            \Log::info("Menu inactive", [
                'menu_identifier' => $menuIdentifier,
                'user_role'       => $user->role_id,
            ]);
            return false;
        }

        // Cek permission spesifik
        $columnName    = "can_{$action}";
        $hasPermission = (bool) ($permission->{$columnName} ?? false);

        if (!$hasPermission) {
            \Log::info("Permission denied", [
                'menu_identifier' => $menuIdentifier,
                'user_role'       => $user->role_id,
                'action'          => $action,
                'column'          => $columnName,
                'value'           => $permission->{$columnName} ?? 'null',
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

        if ((int)$user->role_id === 1) {
            return [
                'all'    => true,
                'view'   => true,
                'add'    => true,
                'edit'   => true,
                'delete' => true,
            ];
        }

        $permissions = \App\Models\MenuRole::where('menu_role.role_id', $user->role_id)
            ->join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->select('menu_role.*', 'menu.route', 'menu.nama_menu')
            ->get()
            ->mapWithKeys(function($perm) {
                $menuKey = $perm->route ?? $perm->nama_menu;
                return [
                    $menuKey => [
                        'active' => $perm->is_active,
                        'view'   => $perm->can_view,
                        'add'    => $perm->can_add,
                        'edit'   => $perm->can_edit,
                        'delete' => $perm->can_delete,
                    ]
                ];
            })
            ->toArray();

        return $permissions;
    }
}