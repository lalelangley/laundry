<?php

// File: app/Helpers/PermissionHelper.php

if (!function_exists('canView')) {
    /**
     * Check if user can view a menu
     */
    function canView(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'view');
    }
}

if (!function_exists('canAdd')) {
    /**
     * Check if user can add/create in a menu
     */
    function canAdd(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'add');
    }
}

if (!function_exists('canEdit')) {
    /**
     * Check if user can edit in a menu
     */
    function canEdit(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'edit');
    }
}

if (!function_exists('canDelete')) {
    /**
     * Check if user can delete in a menu
     */
    function canDelete(string $menuIdentifier): bool
    {
        return checkPermission($menuIdentifier, 'delete');
    }
}

if (!function_exists('checkPermission')) {
    /**
     * Main permission checker
     */
    function checkPermission(string $menuIdentifier, string $action): bool
    {
        // Get authenticated user
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return false;
        }

        // Super admin has all permissions
        if ($user->role_id == 1) {
            return true;
        }

        // Check in menu_role table
        $permission = \App\Models\MenuRole::whereHas('menu', function($query) use ($menuIdentifier) {
                $query->where('route', 'like', "%{$menuIdentifier}%")
                      ->orWhere('nama_menu', 'like', "%{$menuIdentifier}%");
            })
            ->where('role_id', $user->role_id)
            ->first();

        if (!$permission) {
            return false;
        }

        return $permission->{"can_{$action}"} ?? false;
    }
}

if (!function_exists('getUserPermissions')) {
    /**
     * Get all permissions for current user
     */
    function getUserPermissions(): array
    {
        $user = auth('admin')->user() ?? auth('kasir')->user();
        
        if (!$user) {
            return [];
        }

        // Super admin has all permissions
        if ($user->role_id == 1) {
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