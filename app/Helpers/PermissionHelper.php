<?php
// File: app/Helpers/PermissionHelper.php

use Illuminate\Support\Str;

// ============================================================
// HELPER PERMISSION APLIKASI
// Fungsi:
// 1. Menentukan role user aktif
// 2. Mengecek hak akses view/add/edit/delete
// 3. Memaksa akses dengan abort(403) bila tidak punya hak
// 4. Menyediakan daftar permission untuk view/sidebar
//
// Konsep yang tampak:
// - Function helper
// - Percabangan if/foreach
// - Array asosiatif
// - Class-object dan query builder
// - Penanganan error/akses ditolak
// ============================================================

if (!function_exists('resolveUserRoleId')) {
    function resolveUserRoleId($user): ?int
    {
        // [PERCABANGAN] Bila user kosong, role tidak dapat ditentukan.
        if (!$user) {
            return null;
        }

        // [PERCABANGAN] Guard kasir dipetakan manual ke role_id 3.
        if (auth('kasir')->check()) {
            return 3;
        }

        // [PENYESUAIAN TIPE DATA] Pastikan role_id berupa integer.
        return isset($user->role_id) ? (int) $user->role_id : null;
    }
}

if (!function_exists('canView')) {
    function canView(string $menuIdentifier): bool
    {
        // [METHOD] Wrapper agar pemanggilan lebih semantik di controller/view.
        return checkPermission($menuIdentifier, 'view');
    }
}

if (!function_exists('canAdd')) {
    function canAdd(string $menuIdentifier): bool
    {
        // [METHOD] Wrapper untuk aksi tambah.
        return checkPermission($menuIdentifier, 'add');
    }
}

if (!function_exists('canEdit')) {
    function canEdit(string $menuIdentifier): bool
    {
        // [METHOD] Wrapper untuk aksi ubah/edit.
        return checkPermission($menuIdentifier, 'edit');
    }
}

if (!function_exists('canDelete')) {
    function canDelete(string $menuIdentifier): bool
    {
        // [METHOD] Wrapper untuk aksi hapus.
        return checkPermission($menuIdentifier, 'delete');
    }
}

if (!function_exists('requirePermission')) {
    function requirePermission(string $menuIdentifier, string $action): void
    {
        // [PERCABANGAN] User aktif bisa berasal dari guard admin atau kasir.
        $user = auth('admin')->user() ?? auth('kasir')->user();
        $roleId = resolveUserRoleId($user);
        
        // [PERCABANGAN] Super Admin selalu bypass seluruh pemeriksaan permission.
        if ($user && $roleId === 1) {
            return; // Super Admin ALWAYS bypass
        }

        // [PENANGANAN ERROR] Abort 403 bila user tidak berhak mengakses aksi.
        if (!checkPermission($menuIdentifier, $action)) {
            abort(403, "Anda tidak memiliki hak akses untuk {$action} pada menu ini");
        }
    }
}

if (!function_exists('isMenuActive')) {
    function isMenuActive(string $menuIdentifier): bool
    {
        // [OBJECT] Ambil user aktif untuk menentukan role.
        $user = auth('admin')->user() ?? auth('kasir')->user();
        $roleId = resolveUserRoleId($user);
        
        if (!$user) {
            return false;
        }

        // [PERCABANGAN] Menu selalu dianggap aktif untuk Super Admin.
        if ($roleId === 1) {
            return true;
        }

        // [CLASS-OBJECT + METHOD] Join tabel menu_role dan menu untuk cek status aktif.
        $permission = \App\Models\MenuRole::join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->where('menu_role.role_id', $roleId)
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
        // [PERCABANGAN] Tentukan user dan role aktif.
        $user = auth('admin')->user() ?? auth('kasir')->user();
        $roleId = resolveUserRoleId($user);
        
        if (!$user) {
            return false;
        }

        // [PERCABANGAN] Super Admin bypass seluruh permission.
        if ($roleId === 1) {
            return true;
        }

        // [METHOD] Ambil nama route saat ini untuk dicocokkan dengan data menu.
        $currentRoute = request()->route() ? request()->route()->getName() : '';

        // [OBJECT + METHOD] Ambil semua permission milik role dari database.
        $permissions = \App\Models\MenuRole::join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->where('menu_role.role_id', $roleId)
            ->select('menu_role.*', 'menu.route as menu_route', 'menu.nama_menu')
            ->get();

        // [PENAMPUNG DATA] Variabel untuk menyimpan permission yang paling cocok.
        $permission = null;

        // [PERULANGAN] Cek satu per satu permission untuk menemukan kecocokan terbaik.
        foreach ($permissions as $perm) {
            $menuRoute = $perm->menu_route ?? '';
            $menuName  = strtolower($perm->nama_menu ?? '');
            $identifier = strtolower($menuIdentifier);
            $menuBaseRoute = $menuRoute && Str::endsWith($menuRoute, '.index')
                ? Str::beforeLast($menuRoute, '.index')
                : $menuRoute;

            // [PERCABANGAN] 1. Prioritas tertinggi: exact match route.
            if ($menuRoute === $currentRoute) {
                $permission = $perm;
                break;
            }

            // [PERCABANGAN] 2. Cek keluarga route dari menu utama, mis. layanan.index -> layanan.jenis.create
            if ($menuBaseRoute && (
                $currentRoute === $menuBaseRoute
                || str_starts_with($currentRoute, $menuBaseRoute . '.')
            )) {
                $permission = $perm;
                break;
            }

            // [PERCABANGAN] 3. Cek route prefix match.
            if ($menuRoute && str_starts_with($currentRoute, $menuRoute)) {
                $permission = $perm;
                break;
            }

            // [PERCABANGAN] 4. Cek identifier ada di route saat ini.
            if (str_contains(strtolower($currentRoute), $identifier)) {
                $permission = $perm;
            }

            // [PERCABANGAN] 5. Cek nama menu mengandung identifier.
            if ($menuName && str_contains($menuName, $identifier)) {
                $permission = $perm;
            }
        }

        // [LOGGING] Catat hasil pengecekan untuk kebutuhan debug/white box testing.
        \Log::info("CheckPermission", [
            'identifier'       => $menuIdentifier,
            'current_route'    => $currentRoute,
            'role'             => $roleId,
            'action'           => $action,
            'permission_found' => $permission ? $permission->toArray() : null,
        ]);

        if (!$permission) {
            \Log::warning("Permission not found", [
                'menu_identifier' => $menuIdentifier,
                'user_role'       => $roleId,
                'action'          => $action,
            ]);
            return false;
        }

        // [PERCABANGAN] Menu harus aktif terlebih dahulu sebelum cek aksi spesifik.
        if (!$permission->is_active) {
            \Log::info("Menu inactive", [
                'menu_identifier' => $menuIdentifier,
                'user_role'       => $roleId,
            ]);
            return false;
        }

        // [PERCABANGAN DINAMIS] Tentukan nama kolom hak akses berdasarkan action.
        $columnName    = "can_{$action}";
        $hasPermission = (bool) ($permission->{$columnName} ?? false);

        // [PERCABANGAN] Fallback tertentu: add/edit boleh ikut can_view jika dikonfigurasi demikian.
        if (!$hasPermission) {
            if (in_array($action, ['add', 'edit'], true) && (bool) ($permission->can_view ?? false)) {
                \Log::info("Permission fallback to view in helper", [
                    'menu_identifier' => $menuIdentifier,
                    'user_role'       => $roleId,
                    'action'          => $action,
                    'fallback_from'   => $columnName,
                    'fallback_to'     => 'can_view',
                ]);

                return true;
            }

            \Log::info("Permission denied", [
                'menu_identifier' => $menuIdentifier,
                'user_role'       => $roleId,
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
        // [PERCABANGAN] Ambil user dan role yang sedang aktif.
        $user = auth('admin')->user() ?? auth('kasir')->user();
        $roleId = resolveUserRoleId($user);
        
        if (!$user) {
            return [];
        }

        // [ARRAY ASOSIATIF] Super Admin mendapat semua hak akses.
        if ($roleId === 1) {
            return [
                'all'    => true,
                'view'   => true,
                'add'    => true,
                'edit'   => true,
                'delete' => true,
            ];
        }

        // [OBJECT + METHOD] Ambil permission role lalu ubah ke array key => value.
        $permissions = \App\Models\MenuRole::where('menu_role.role_id', $roleId)
            ->join('menu', 'menu_role.menu_id', '=', 'menu.id')
            ->select('menu_role.*', 'menu.route', 'menu.nama_menu')
            ->get()
            ->mapWithKeys(function($perm) {
                // [ARRAY] Setiap menu dipetakan ke status active/view/add/edit/delete.
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
