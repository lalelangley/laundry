<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\MenuRole;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Admin;
use App\Models\Kasir;
use App\Models\Driver;

class UserManagerController extends Controller
{
    private function applyManagerSort($query, string $sort, string $nameColumn, string $idColumn)
    {
        return match ($sort) {
            'nama_desc' => $query->orderBy($nameColumn, 'desc'),
            'terlama' => $query->orderBy($idColumn, 'asc'),
            default => $query->orderBy($nameColumn, 'asc'),
        };
    }

    // =============================
    // INDEX USER MANAGER
    // =============================
    public function index(Request $request)
    {
        $adminLogin = auth('admin')->user();

        if (!$adminLogin) {
            abort(403, 'Silahkan login terlebih dahulu');
        }

        $admins = $this->applyManagerSort(
            Admin::whereIn('role_id', [1,2])->orderBy('role_id'),
            $request->get('admins_sort', 'nama_asc'),
            'nama',
            'id_admin'
        )
            ->paginate(10, ['*'], 'admins_page')
            ->withQueryString();

        $kasirs = $this->applyManagerSort(
            Kasir::query(),
            $request->get('kasirs_sort', 'nama_asc'),
            'nama_kasir',
            'id_kasir'
        )
            ->paginate(10, ['*'], 'kasirs_page')
            ->withQueryString();
        
        $drivers = $this->applyManagerSort(
            Driver::query(),
            $request->get('drivers_sort', 'nama_asc'),
            'nama_driver',
            'id_driver'
        )
            ->paginate(10, ['*'], 'drivers_page')
            ->withQueryString();

        return view('manager.index', compact('admins','kasirs','drivers','adminLogin'));
    }

    // =============================
    // HALAMAN AKSES PER USER
    // =============================
    public function aksesRole($role_id)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role_id != 1) abort(403);

        $role  = Role::findOrFail($role_id);
        $menus = Menu::orderBy('urutan')->get();

        // auto full akses untuk ADMIN
        if ($role_id == 1) {
            foreach ($menus as $menu) {
                MenuRole::firstOrCreate(
                    ['role_id' => 1, 'menu_id' => $menu->id],
                    [
                        'can_view' => 1,
                        'can_add' => 1,
                        'can_edit' => 1,
                        'can_delete' => 1,
                    ]
                );
            }
        }

        $permissions = MenuRole::where('role_id', $role_id)
            ->get()
            ->keyBy('menu_id');

        return view('manager.menu-role.akses', compact(
            'role', 'menus', 'permissions'
        ));
    }


    // =============================
    // FORM TAMBAH MENU ROLE
    // =============================
    public function create()
    {
        $admin = auth('admin')->user();

        if (!$admin || $admin->role_id != 1) {
            abort(403);
        }

        $roles = Role::whereIn('id', [1, 2])->get();
        $menus = Menu::all();

        return view('manager.menu-role.create', compact('roles', 'menus'));
    }


    // =============================
    // SIMPAN MENU ROLE
    // =============================
    public function store(Request $request)
    {
        $admin = auth('admin')->user();

        if ($admin->role_id != 1) {
            abort(403);
        }

        $request->validate([
            'role_id' => 'required',
            'menu_id' => 'required',
        ]);

        MenuRole::updateOrCreate(
            [
                'role_id' => $request->role_id,
                'menu_id' => $request->menu_id,
            ],
            [
                'can_view'   => $request->boolean('can_view'),
                'can_add'    => $request->boolean('can_add'),
                'can_edit'   => $request->boolean('can_edit'),
                'can_delete' => $request->boolean('can_delete'),
            ]
        );

        return redirect()->route('manager.index')
            ->with('success', 'Hak akses berhasil disimpan');
    }

    // =============================
    // UPDATE MENU ROLE
    // =============================
    public function update(Request $request, $id)
    {
        $admin = auth('admin')->user();

        if ($admin->role_id != 1) {
            abort(403);
        }

        $menuRole = MenuRole::findOrFail($id);

        $menuRole->update([
            'can_view'   => $request->boolean('can_view'),
            'can_add'    => $request->boolean('can_add'),
            'can_edit'   => $request->boolean('can_edit'),
            'can_delete' => $request->boolean('can_delete'),
        ]);

        return back()->with('success', 'Hak akses diperbarui');
    }

    // =============================
    // HAPUS MENU ROLE
    // =============================
    public function destroy($id)
    {
        $admin = auth('admin')->user();

        if ($admin->role_id != 1) {
            abort(403);
        }

        MenuRole::findOrFail($id)->delete();

        return back()->with('success', 'Hak akses dihapus');
    }

    public function saveUserPermission(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Akses ditolak. Hanya Super Admin yang dapat mengakses.');
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'menus' => 'array',
            'permissions' => 'array'
        ]);

        $roleId = $request->role_id;

        MenuRole::where('role_id', $roleId)->delete();

        foreach ($request->menus ?? [] as $menuId) {
            $menu = Menu::where('id', $menuId)
                ->where('role_id', $roleId)
                ->first();

            if (!$menu) continue;

            $perms = $request->permissions[$menuId] ?? [];

            MenuRole::create([
                'role_id'    => $roleId,
                'menu_id'    => $menuId,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
            ]);
        }

        return back()->with('success', 'Hak akses user berhasil diperbarui');
    }


    // =============================
    // TAMBAH KASIR - SUPER ADMIN
    // Validasi: nama_kasir & no_hp unik
    // =============================
    public function storeKasir(Request $request)
    {
        $request->validate([
            'nama_kasir' => 'required|string|max:100|unique:kasir,nama_kasir',
            'email'      => 'required|email|max:255|unique:kasir,email',
            'no_hp'      => 'nullable|string|max:20|unique:kasir,no_hp',
            'password'   => 'required|string|min:6',
        ], [
            'nama_kasir.unique' => 'Nama kasir sudah terdaftar, gunakan nama lain.',
            'email.required'    => 'Email kasir wajib diisi.',
            'email.email'       => 'Format email kasir tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh kasir lain.',
            'no_hp.unique'      => 'Nomor HP sudah digunakan oleh kasir lain.',
        ]);

        Kasir::create([
            'nama_kasir' => $request->nama_kasir,
            'email'      => $request->email,
            'no_hp'      => $request->no_hp,
            'password'   => Hash::make($request->password),
            'role_id'    => 3,
        ]);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Kasir berhasil ditambahkan');
    }

    public function updateStatus(Request $request)
    {
        $adminLogin = auth('admin')->user();

        if (!$adminLogin || $adminLogin->role_id != 1) {
            abort(403);
        }

        $request->validate([
            'user_type' => 'required|in:admin,kasir,driver',
            'user_id'   => 'required',
            'status'    => 'required|in:aktif,nonaktif',
        ]);

        if ($request->user_type === 'admin') {
            Admin::where('id_admin', $request->user_id)
                ->update(['status' => $request->status]);
        } elseif ($request->user_type === 'kasir') {
            Kasir::where('id_kasir', $request->user_id)
                ->update(['status' => $request->status]);
        } else {
            Driver::where('id_driver', $request->user_id)
                ->update(['status' => $request->status]);
        }

        return back()->with('success', 'Status berhasil diubah');
    }

    public function updateStatusAdmin2(Request $request)
    {
        $adminLogin = auth('admin')->user();

        if (!$adminLogin || $adminLogin->role_id != 2) {
            abort(403);
        }

        $request->validate([
            'user_type' => 'required|in:admin,kasir,driver',
            'user_id'   => 'required',
            'status'    => 'required|in:aktif,nonaktif',
        ]);

        if ($request->user_type === 'admin') {
            Admin::where('id_admin', $request->user_id)
                ->update(['status' => $request->status]);
        } elseif ($request->user_type === 'kasir') {
            Kasir::where('id_kasir', $request->user_id)
                ->update(['status' => $request->status]);
        } else {
            Driver::where('id_driver', $request->user_id)
                ->update(['status' => $request->status]);
        }

        return back()->with('success', 'Status berhasil diubah');
    }

    public function hakRole()
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role_id != 1) abort(403);

        $selectedRoleId = request('role_id') ?? 1;

        $roles = Role::orderBy('id')->get();

        $menus = Menu::where('role_id', $selectedRoleId)
            ->where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) use ($selectedRoleId) {
                $q->where('status', 1)
                  ->where('role_id', $selectedRoleId)
                  ->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();

        $permissions = MenuRole::where('role_id', $selectedRoleId)
            ->get()
            ->keyBy('menu_id');

        $menuActions = [
            'layanan'         => ['view','add','edit','delete'],
            'satuan'          => ['view','add','edit','delete'],
            'parfum'          => ['view','add','edit','delete'],
            'pelanggan'       => ['view','add','edit','delete'],
            'pengeluaran'     => ['view','add','edit','delete'],
            'transaksi'       => ['view','edit','delete'],
            'pesanan_online'  => ['view','edit','delete'],
            'riwayat'         => ['view','add','edit','delete'],
            'metode_bayar'    => ['view','add','delete'],
            'laporan'         => ['view'],
            'pengaturan'      => ['view','edit','add','delete','restore','hapus_backup','password','logout'],
            'data'            => ['view','edit','delete','restore','hapus_backup','password','logout'],
            'user_manager'    => ['view','add','edit','delete'],
        ];

        return view('manager.menu-role.hak', compact(
            'roles',
            'menus',
            'permissions',
            'selectedRoleId',
            'menuActions'
        ));
    }


    public function hakRoleAdmin2()
    {
        $admin = auth('admin')->user();
        
        if (!$admin || !in_array((int)$admin->role_id, [1, 2])) {
            abort(403, 'Akses ditolak. Hanya Super Admin dan Admin2 yang dapat mengatur hak akses.');
        }
        
        $selectedRoleId = request('role_id') ?? 3;
        
        $roles = Role::orderBy('id')->get();
        
        $role = Role::findOrFail($selectedRoleId);
        
        $menus = Menu::where('role_id', $selectedRoleId)
            ->where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) use ($selectedRoleId) {
                $q->where('status', 1)
                    ->where('role_id', $selectedRoleId)
                    ->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();
        
        $permissions = MenuRole::where('role_id', $selectedRoleId)
            ->get()
            ->keyBy('menu_id');
        
        $menuActions = [
            'layanan'         => ['view','add','edit','delete'],
            'satuan'          => ['view','add','edit','delete'],
            'parfum'          => ['view','add','edit','delete'],
            'pelanggan'       => ['view','add','edit','delete'],
            'pengeluaran'     => ['view','add','edit','delete'],
            'transaksi'       => ['view','edit','delete'],
            'pesanan_online'  => ['view','edit','delete'],
            'riwayat'         => ['view','add','edit','delete'],
            'metode_bayar'    => ['view','add','delete'],
            'laporan'         => ['view'],
            'pengaturan'      => ['view','edit','add','delete','restore','hapus_backup','password','logout'],
            'data'            => ['view','edit','delete','restore','hapus_backup','password','logout'],
        ];
        
        return view('admin2.manager.menu-role.hak', compact(
            'role',
            'roles',
            'menus',
            'permissions',
            'selectedRoleId',
            'menuActions'
        ));
    }


    public function saveHakRole(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || (int)$admin->role_id !== 1) {
            abort(403, 'Hanya Super Admin yang dapat mengubah hak akses');
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'menus' => 'array',
        ]);

        $roleId = $request->role_id;

        MenuRole::where('role_id', $roleId)->delete();

        $allMenus = Menu::where('role_id', $roleId)
            ->where('status', 1)
            ->get();

        foreach ($allMenus as $menu) {
            $menuId = $menu->id;
            $menuData = $request->menus[$menuId] ?? null;

            if (!$menuData) {
                MenuRole::create([
                    'role_id'    => $roleId,
                    'menu_id'    => $menuId,
                    'is_active'  => false,
                    'can_view'   => false,
                    'can_add'    => false,
                    'can_edit'   => false,
                    'can_delete' => false,
                    'can_cancel' => false,
                    'can_change_password' => false,
                    'can_restore_data' => false,
                    'show_delete_backup' => false,
                    'show_logout' => false,
                    'can_access_settings' => false,
                ]);
                continue;
            }

            $isActive = isset($menuData['active']) && $menuData['active'] == 1;
            $perms = $menuData['permissions'] ?? [];

            MenuRole::create([
                'role_id'    => $roleId,
                'menu_id'    => $menuId,
                'is_active'  => $isActive,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
                'can_cancel' => in_array('cancel', $perms),
                'can_change_password' => in_array('password', $perms),
                'can_restore_data' => in_array('restore', $perms),
                'show_delete_backup' => in_array('hapus_backup', $perms),
                'show_logout' => in_array('logout', $perms),
                'can_access_settings' => in_array('view', $perms),
            ]);
        }

        return back()->with('success', 'Hak akses role berhasil disimpan');
    }


    public function saveHakRoleAdmin2(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || !in_array((int)$admin->role_id, [1, 2])) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah hak akses kasir');
        }

        $request->validate([
            'menus' => 'array',
        ]);

        $roleId = 3;

        MenuRole::where('role_id', $roleId)->delete();

        $allMenus = Menu::where('role_id', $roleId)
            ->where('status', 1)
            ->get();

        foreach ($allMenus as $menu) {
            $menuId = $menu->id;
            $menuData = $request->menus[$menuId] ?? null;

            if (!$menuData) {
                MenuRole::create([
                    'role_id'    => $roleId,
                    'menu_id'    => $menuId,
                    'is_active'  => false,
                    'can_view'   => false,
                    'can_add'    => false,
                    'can_edit'   => false,
                    'can_delete' => false,
                    'can_cancel' => false,
                    'can_change_password' => false,
                    'can_restore_data' => false,
                    'show_delete_backup' => false,
                    'show_logout' => false,
                    'can_access_settings' => false,
                ]);
                continue;
            }

            $isActive = isset($menuData['active']) && $menuData['active'] == 1;
            $perms = $menuData['permissions'] ?? [];

            MenuRole::create([
                'role_id'    => $roleId,
                'menu_id'    => $menuId,
                'is_active'  => $isActive,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
                'can_cancel' => in_array('cancel', $perms),
                'can_change_password' => in_array('password', $perms),
                'can_restore_data' => in_array('restore', $perms),
                'show_delete_backup' => in_array('hapus_backup', $perms),
                'show_logout' => in_array('logout', $perms),
                'can_access_settings' => in_array('view', $perms),
            ]);
        }

        return back()->with('success', 'Hak akses kasir berhasil disimpan');
    }

    public function aksesUser($type, $id)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role_id != 1) abort(403);

        $user = $type === 'admin'
            ? Admin::findOrFail($id)
            : Kasir::findOrFail($id);

        $roleId = $user->role_id;
        $role   = Role::findOrFail($roleId);

        $menus = Menu::where('role_id', $roleId)
            ->orderBy('urutan')
            ->get();

        $permissions = MenuRole::where('role_id', $roleId)
            ->get()
            ->keyBy('menu_id');

        return view('manager.menu-role.akses', compact(
            'user', 'role', 'menus', 'permissions'
        ));
    }

    // =============================
    // ADMIN2 - INDEX KELOLA KASIR
    // =============================
    public function indexAdmin2(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Akses ditolak. Hanya Admin2 yang dapat mengakses halaman ini.');
        }

        $kasirs = $this->applyManagerSort(
            Kasir::query(),
            $request->get('kasirs_sort', 'nama_asc'),
            'nama_kasir',
            'id_kasir'
        )
            ->paginate(10, ['*'], 'kasirs_page')
            ->withQueryString();
        $drivers = $this->applyManagerSort(
            Driver::query(),
            $request->get('drivers_sort', 'nama_asc'),
            'nama_driver',
            'id_driver'
        )
            ->paginate(10, ['*'], 'drivers_page')
            ->withQueryString();

        return view('admin2.manager.index', compact('kasirs', 'drivers', 'admin'));
    }

    public function createKasirAdmin2()
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        return view('admin2.manager.kasir.create');
    }

    // =============================
    // TAMBAH KASIR - ADMIN2
    // Validasi: nama_kasir & no_hp unik
    // =============================
    public function storeKasirAdmin2(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $request->validate([
            'nama_kasir' => 'required|string|max:100|unique:kasir,nama_kasir',
            'email'      => 'required|email|max:255|unique:kasir,email',
            'no_hp'      => 'nullable|string|max:20|unique:kasir,no_hp',
            'password'   => 'required|string|min:6',
        ], [
            'nama_kasir.unique' => 'Nama kasir sudah terdaftar, gunakan nama lain.',
            'email.required'    => 'Email kasir wajib diisi.',
            'email.email'       => 'Format email kasir tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh kasir lain.',
            'no_hp.unique'      => 'Nomor HP sudah digunakan oleh kasir lain.',
        ]);

        Kasir::create([
            'nama_kasir' => $request->nama_kasir,
            'email'      => $request->email,
            'no_hp'      => $request->no_hp,
            'password'   => Hash::make($request->password),
            'role_id'    => 3,
            'status'     => 'aktif',
        ]);

        return redirect()
            ->route('admin2.manager.index')
            ->with('success', 'Kasir berhasil ditambahkan');
    }

    public function aksesKasirAdmin2($id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3;
        $role = Role::findOrFail($roleId);

        $menus = Menu::where('role_id', $roleId)
            ->where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) use ($roleId) {
                $q->where('status', 1)
                  ->where('role_id', $roleId)
                  ->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();

        $permissions = MenuRole::where('role_id', $roleId)
            ->get()
            ->keyBy('menu_id');

        $menuActions = [
            'layanan'       => ['view','add','edit','delete'],
            'satuan'        => ['view','add','edit','delete'],
            'parfum'        => ['view','add','edit','delete'],
            'pelanggan'     => ['view','add','edit','delete'],
            'pengeluaran'   => ['view','add','edit','delete'],
            'transaksi'     => ['view','edit','delete'],
            'metode-bayar'  => ['view','add','edit','delete'],
            'laporan'       => ['view'],
            'data'          => ['view','edit','delete'],
        ];

        return view('admin2.manager.menu-role.hak', compact(
            'kasir', 'role', 'menus', 'permissions', 'menuActions'
        ));
    }

    public function saveAksesKasirAdmin2(Request $request, $id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3;

        $request->validate([
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        MenuRole::where('role_id', $roleId)->delete();

        foreach ($request->menus ?? [] as $menuId) {
            $menu = Menu::where('id', $menuId)
                ->where('role_id', $roleId)
                ->first();

            if (!$menu) continue;

            $perms = $request->permissions[$menuId] ?? [];

            MenuRole::create([
                'role_id'    => $roleId,
                'menu_id'    => $menuId,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
            ]);
        }

        return back()->with('success', 'Hak akses kasir berhasil diperbarui');
    }

    public function updateStatusKasirAdmin2(Request $request, $id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Kasir::where('id_kasir', $id)
            ->update(['status' => $request->status]);

        return back()->with('success', 'Status kasir berhasil diubah');
    }

    public function hakAksesKasir()
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $roleId = 3;
        $role = Role::findOrFail($roleId);

        $menus = Menu::where('role_id', $roleId)
            ->where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('urutan')
            ->get();

        $permissions = MenuRole::where('role_id', $roleId)
            ->get()
            ->keyBy('menu_id');

        return view('admin2.manager.menu-role.hak', compact(
            'role', 'menus', 'permissions'
        ));
    }

    public function saveHakAksesKasir(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $roleId = 3;

        $request->validate([
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        MenuRole::where('role_id', $roleId)->delete();

        foreach ($request->menus ?? [] as $menuId) {
            $menu = Menu::where('id', $menuId)
                ->where('role_id', $roleId)
                ->first();

            if (!$menu) continue;

            $perms = $request->permissions[$menuId] ?? [];

            MenuRole::create([
                'role_id'    => $roleId,
                'menu_id'    => $menuId,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
            ]);
        }

        return redirect()
            ->route('admin2.manager.index')
            ->with('success', 'Hak akses kasir berhasil diperbarui');
    }

    public function menuRoleAksesAdmin2($id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Akses ditolak. Hanya Admin2 yang dapat mengakses halaman ini.');
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3;
        $role = Role::findOrFail($roleId);

        $menus = Menu::where('role_id', $roleId)
            ->where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) use ($roleId) {
                $q->where('status', 1)
                  ->where('role_id', $roleId)
                  ->orderBy('urutan');
            }])
            ->orderBy('urutan')
            ->get();

        $permissions = MenuRole::where('role_id', $roleId)
            ->get()
            ->keyBy('menu_id');

        $menuActions = [
            'layanan'       => ['view','add','edit','delete'],
            'satuan'        => ['view','add','edit','delete'],
            'parfum'        => ['view','add','edit','delete'],
            'pelanggan'     => ['view','add','edit','delete'],
            'pengeluaran'   => ['view','add','edit','delete'],
            'transaksi'     => ['view','edit','delete'],
            'metode-bayar'  => ['view','add','edit','delete'],
            'laporan'       => ['view'],
            'data'          => ['view','edit','delete'],
        ];

        return view('admin2.manager.menu-role.akses', compact(
            'kasir', 'role', 'menus', 'permissions', 'menuActions'
        ));
    }

    public function saveMenuRoleAksesAdmin2(Request $request, $id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Akses ditolak. Hanya Admin2 yang dapat mengakses halaman ini.');
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3;

        $request->validate([
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        MenuRole::where('role_id', $roleId)->delete();

        foreach ($request->menus ?? [] as $menuId) {
            $menu = Menu::where('id', $menuId)
                ->where('role_id', $roleId)
                ->first();

            if (!$menu) continue;

            $perms = $request->permissions[$menuId] ?? [];

            MenuRole::create([
                'role_id'    => $roleId,
                'menu_id'    => $menuId,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
            ]);
        }

        return back()->with('success', 'Hak akses kasir berhasil diperbarui');
    }

    // ===============================
    // CREATE DRIVER - SHOW FORM
    // ===============================
    public function createDriver()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah driver');
        }

        return view('manager.driver.create');
    }

    // =============================
    // TAMBAH DRIVER - SUPER ADMIN
    // Validasi: nama_driver & no_telp unik
    // =============================
    public function storeDriver(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah driver');
        }

        $request->validate([
            'nama_driver' => 'required|string|max:255|unique:driver,nama_driver',
            'no_telp'     => 'required|string|max:20|unique:driver,no_telp',
            'password'    => 'required|string|min:6',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama_driver.unique' => 'Nama driver sudah terdaftar, gunakan nama lain.',
            'no_telp.unique'     => 'Nomor telepon sudah digunakan oleh driver lain.',
        ]);

        Driver::create([
            'nama_driver' => $request->nama_driver,
            'no_telp'     => $request->no_telp,
            'password'    => bcrypt($request->password),
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Driver berhasil ditambahkan');
    }

    public function editDriver($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa edit driver');
        }

        $driver = Driver::findOrFail($id);
        
        return view('manager.driver.edit', compact('driver'));
    }

    // =============================
    // EDIT DRIVER - SUPER ADMIN
    // Validasi: nama_driver & no_telp unik (ignore diri sendiri)
    // =============================
    public function updateDriver(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update driver');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'nama_driver' => 'required|string|max:255|unique:driver,nama_driver,' . $driver->id_driver . ',id_driver',
            'no_telp'     => 'required|string|max:20|unique:driver,no_telp,' . $driver->id_driver . ',id_driver',
            'password'    => 'nullable|string|min:6',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama_driver.unique' => 'Nama driver sudah digunakan oleh driver lain.',
            'no_telp.unique'     => 'Nomor telepon sudah digunakan oleh driver lain.',
        ]);

        $updateData = [
            'nama_driver' => $request->nama_driver,
            'no_telp'     => $request->no_telp,
            'status'      => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $driver->update($updateData);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Driver berhasil diupdate');
    }

    public function destroyDriver($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa hapus driver');
        }

        $driver = Driver::findOrFail($id);
        $driver->delete();

        return redirect()
            ->route('manager.index')
            ->with('success', 'Driver berhasil dihapus');
    }

    public function indexKasir()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang memiliki akses');
        }

        $admins = \App\Models\Admin::orderBy('nama')->get();
        $kasirs = \App\Models\Kasir::orderBy('nama_kasir')->get();
        $drivers = Driver::orderBy('nama_driver')->get();

        return view('kasir.manager.index', compact('admins', 'kasirs', 'drivers'));
    }

    public function updateStatusKasir(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update status');
        }

        $request->validate([
            'user_type' => 'required|in:admin,kasir,driver',
            'user_id'   => 'required',
            'status'    => 'required|in:aktif,nonaktif'
        ]);

        if ($request->user_type === 'admin') {
            $user = \App\Models\Admin::findOrFail($request->user_id);
        } elseif ($request->user_type === 'kasir') {
            $user = \App\Models\Kasir::findOrFail($request->user_id);
        } else {
            $user = Driver::findOrFail($request->user_id);
        }

        $user->update(['status' => $request->status]);

        return back()->with('success', ucfirst($request->user_type) . ' berhasil diupdate');
    }

    public function createDriverKasir()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah driver');
        }

        return view('kasir.manager.driver.create');
    }

    // =============================
    // TAMBAH DRIVER - KASIR VIEW (SUPER ADMIN)
    // Validasi: nama_driver & no_telp unik
    // =============================
    public function storeDriverKasir(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah driver');
        }

        $request->validate([
            'nama_driver' => 'required|string|max:255|unique:driver,nama_driver',
            'no_telp'     => 'required|string|max:20|unique:driver,no_telp',
            'password'    => 'required|string|min:6',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama_driver.unique' => 'Nama driver sudah terdaftar, gunakan nama lain.',
            'no_telp.unique'     => 'Nomor telepon sudah digunakan oleh driver lain.',
        ]);

        Driver::create([
            'nama_driver' => $request->nama_driver,
            'no_telp'     => $request->no_telp,
            'password'    => bcrypt($request->password),
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('kasir.manager.index')
            ->with('success', 'Driver berhasil ditambahkan');
    }

    public function editDriverKasir($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa edit driver');
        }

        $driver = Driver::findOrFail($id);
        
        return view('kasir.manager.driver.edit', compact('driver'));
    }

    // =============================
    // EDIT DRIVER - KASIR VIEW (SUPER ADMIN)
    // Validasi: nama_driver & no_telp unik (ignore diri sendiri)
    // =============================
    public function updateDriverKasir(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update driver');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'nama_driver' => 'required|string|max:255|unique:driver,nama_driver,' . $driver->id_driver . ',id_driver',
            'no_telp'     => 'required|string|max:20|unique:driver,no_telp,' . $driver->id_driver . ',id_driver',
            'password'    => 'nullable|string|min:6',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama_driver.unique' => 'Nama driver sudah digunakan oleh driver lain.',
            'no_telp.unique'     => 'Nomor telepon sudah digunakan oleh driver lain.',
        ]);

        $updateData = [
            'nama_driver' => $request->nama_driver,
            'no_telp'     => $request->no_telp,
            'status'      => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $driver->update($updateData);

        return redirect()
            ->route('kasir.manager.index')
            ->with('success', 'Driver berhasil diupdate');
    }

    public function destroyDriverKasir($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa hapus driver');
        }

        $driver = Driver::findOrFail($id);
        $driver->delete();

        return redirect()
            ->route('kasir.manager.index')
            ->with('success', 'Driver berhasil dihapus');
    }

    public function createDriverAdmin2()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa menambah driver');
        }

        return view('admin2.manager.driver.create');
    }

    // =============================
    // TAMBAH DRIVER - ADMIN2
    // Validasi: nama_driver & no_telp unik
    // =============================
    public function storeDriverAdmin2(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa menambah driver');
        }

        $request->validate([
            'nama_driver' => 'required|string|max:255|unique:driver,nama_driver',
            'no_telp'     => 'required|string|max:20|unique:driver,no_telp',
            'password'    => 'required|string|min:6',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama_driver.unique' => 'Nama driver sudah terdaftar, gunakan nama lain.',
            'no_telp.unique'     => 'Nomor telepon sudah digunakan oleh driver lain.',
        ]);

        Driver::create([
            'nama_driver' => $request->nama_driver,
            'no_telp'     => $request->no_telp,
            'password'    => bcrypt($request->password),
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('admin2.manager.index')
            ->with('success', 'Driver berhasil ditambahkan');
    }

    public function editDriverAdmin2($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa edit driver');
        }

        $driver = Driver::findOrFail($id);
        
        return view('admin2.manager.driver.edit', compact('driver'));
    }

    // =============================
    // EDIT DRIVER - ADMIN2
    // Validasi: nama_driver & no_telp unik (ignore diri sendiri)
    // =============================
    public function updateDriverAdmin2(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa update driver');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'nama_driver' => 'required|string|max:255|unique:driver,nama_driver,' . $driver->id_driver . ',id_driver',
            'no_telp'     => 'required|string|max:20|unique:driver,no_telp,' . $driver->id_driver . ',id_driver',
            'password'    => 'nullable|string|min:6',
            'status'      => 'required|in:aktif,nonaktif',
        ], [
            'nama_driver.unique' => 'Nama driver sudah digunakan oleh driver lain.',
            'no_telp.unique'     => 'Nomor telepon sudah digunakan oleh driver lain.',
        ]);

        $updateData = [
            'nama_driver' => $request->nama_driver,
            'no_telp'     => $request->no_telp,
            'status'      => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $driver->update($updateData);

        return redirect()
            ->route('admin2.manager.index')
            ->with('success', 'Driver berhasil diupdate');
    }

    public function destroyDriverAdmin2($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa hapus driver');
        }

        $driver = Driver::findOrFail($id);
        $driver->delete();

        return redirect()
            ->route('admin2.manager.index')
            ->with('success', 'Driver berhasil dihapus');
    }

    public function editAdmin($id)
    {
        $admin = auth()->guard('admin')->user();
            
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengedit admin');
        }

        $adminData = Admin::findOrFail($id);
        $roles = Role::whereIn('id', [1, 2])->get();

        return view('manager.admin.edit', compact('adminData', 'roles'));
    }

    // =============================
    // EDIT ADMIN - SUPER ADMIN
    // Validasi: nama & email unik (ignore diri sendiri)
    // =============================
    public function updateAdmin(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengupdate admin');
        }

        $adminData = Admin::findOrFail($id);

        $request->validate([
            'nama'     => 'required|string|max:255|unique:admin,nama,' . $id . ',id_admin',
            'email'    => 'required|email|unique:admin,email,' . $id . ',id_admin',
            'password' => 'nullable|string|min:6',
            'role_id'  => 'required|in:1,2',
            'status'   => 'required|in:aktif,nonaktif',
        ], [
            'nama.unique'  => 'Nama admin sudah digunakan oleh admin lain.',
            'email.unique' => 'Email sudah digunakan oleh admin lain.',
        ]);

        $updateData = [
            'nama'    => $request->nama,
            'email'   => $request->email,
            'role_id' => $request->role_id,
            'status'  => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $adminData->update($updateData);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Data admin berhasil diupdate');
    }

    public function editKasir($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengedit kasir');
        }

        $kasir = Kasir::findOrFail($id);

        return view('manager.kasir.edit', compact('kasir'));
    }

    public function editKasirAdmin2($id)
    {
        $admin = auth()->guard('admin')->user();

        if (!in_array($admin->role_id, [1, 2])) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit kasir');
        }

        $kasir = Kasir::findOrFail($id);

        return view('admin2.manager.kasir.edit', compact('kasir'));
    }

    // =============================
    // EDIT KASIR - ADMIN2
    // Validasi: nama_kasir & no_hp unik (ignore diri sendiri)
    // =============================
    public function updateKasirAdmin2(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin || !in_array($admin->role_id, [1, 2])) {
            abort(403, 'Anda tidak memiliki izin untuk mengupdate kasir');
        }

        $kasir = Kasir::findOrFail($id);

        $request->validate([
            'nama_kasir' => 'required|string|max:100|unique:kasir,nama_kasir,' . $kasir->id_kasir . ',id_kasir',
            'email'      => 'required|email|max:255|unique:kasir,email,' . $kasir->id_kasir . ',id_kasir',
            'no_hp'      => 'nullable|string|max:20|unique:kasir,no_hp,' . $kasir->id_kasir . ',id_kasir',
            'password'   => 'nullable|string|min:6',
            'status'     => 'required|in:aktif,nonaktif',
        ], [
            'nama_kasir.unique' => 'Nama kasir sudah digunakan oleh kasir lain.',
            'email.required'    => 'Email kasir wajib diisi.',
            'email.email'       => 'Format email kasir tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh kasir lain.',
            'no_hp.unique'      => 'Nomor HP sudah digunakan oleh kasir lain.',
        ]);

        $updateData = [
            'nama_kasir' => $request->nama_kasir,
            'email'      => $request->email,
            'no_hp'      => $request->no_hp,
            'status'     => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $kasir->update($updateData);

        return redirect()
            ->route('admin2.manager.index')
            ->with('success', 'Data kasir berhasil diupdate');
    }

    // =============================
    // EDIT KASIR - SUPER ADMIN
    // Validasi: nama_kasir & no_hp unik (ignore diri sendiri)
    // =============================
    public function updateKasir(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengupdate kasir');
        }

        $kasir = Kasir::findOrFail($id);

        $request->validate([
            'nama_kasir' => 'required|string|max:100|unique:kasir,nama_kasir,' . $kasir->id_kasir . ',id_kasir',
            'email'      => 'required|email|max:255|unique:kasir,email,' . $kasir->id_kasir . ',id_kasir',
            'no_hp'      => 'nullable|string|max:20|unique:kasir,no_hp,' . $kasir->id_kasir . ',id_kasir',
            'password'   => 'nullable|string|min:6',
            'status'     => 'required|in:aktif,nonaktif',
        ], [
            'nama_kasir.unique' => 'Nama kasir sudah digunakan oleh kasir lain.',
            'email.required'    => 'Email kasir wajib diisi.',
            'email.email'       => 'Format email kasir tidak valid.',
            'email.unique'      => 'Email sudah digunakan oleh kasir lain.',
            'no_hp.unique'      => 'Nomor HP sudah digunakan oleh kasir lain.',
        ]);

        $updateData = [
            'nama_kasir' => $request->nama_kasir,
            'email'      => $request->email,
            'no_hp'      => $request->no_hp,
            'status'     => $request->status,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $kasir->update($updateData);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Data kasir berhasil diupdate');
    }
    
    private function checkAdminRole($roleId)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role_id != $roleId) abort(403);
        return $admin;
    }

    // =============================
    // QUICK SAVE HAK ROLE - SUPER ADMIN
    // =============================
    public function quickSaveHakRole(Request $request)
    {
        try {
            $admin = auth('admin')->user();
            
            if (!$admin || (int)$admin->role_id !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Super Admin yang dapat mengubah hak akses.'
                ], 403);
            }

            $request->validate([
                'role_id' => 'required|exists:roles,id',
                'menus'   => 'required|array',
            ]);

            $roleId = $request->role_id;
            
            \Log::info('Quick Save Request (Super Admin):', [
                'admin_id' => $admin->id_admin,
                'role_id'  => $roleId,
                'menus'    => $request->menus
            ]);

            foreach ($request->menus as $menuId => $data) {
                $isActive    = isset($data['active']) && $data['active'] == 1;
                $permissions = $data['permissions'] ?? [];

                \Log::info("Processing menu {$menuId}:", [
                    'is_active'   => $isActive,
                    'permissions' => $permissions
                ]);

                \DB::table('menu_role')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'menu_id' => $menuId
                    ],
                    [
                        'is_active'            => $isActive,
                        'can_view'             => in_array('view', $permissions),
                        'can_add'              => in_array('add', $permissions),
                        'can_edit'             => in_array('edit', $permissions),
                        'can_delete'           => in_array('delete', $permissions),
                        'can_cancel'           => in_array('cancel', $permissions),
                        'can_change_password'  => in_array('password', $permissions),
                        'can_restore_data'     => in_array('restore', $permissions),
                        'show_delete_backup'   => in_array('hapus_backup', $permissions),
                        'show_logout'          => in_array('logout', $permissions),
                        'can_access_settings'  => in_array('view', $permissions),
                        'updated_at'           => now()
                    ]
                );
            }

            \Log::info('Quick save successful (Super Admin)');

            return response()->json([
                'success' => true,
                'message' => 'Hak akses berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            \Log::error('Quick save error (Super Admin):', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    // =============================
    // QUICK SAVE HAK ROLE - ADMIN2
    // =============================
    public function quickSaveHakRoleAdmin2(Request $request)
    {
        try {
            $admin = auth('admin')->user();
            
            if (!$admin || !in_array((int)$admin->role_id, [1, 2])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak'
                ], 403);
            }

            // Admin2 hanya bisa edit kasir (role_id = 3)
            $roleId = 3;
            
            \Log::info('Quick Save Request (Admin2):', [
                'admin_id' => $admin->id_admin,
                'role_id'  => $roleId,
                'menus'    => $request->menus
            ]);

            foreach ($request->menus as $menuId => $data) {
                $isActive    = isset($data['active']) && $data['active'] == 1;
                $permissions = $data['permissions'] ?? [];

                \Log::info("Processing menu {$menuId}:", [
                    'is_active'   => $isActive,
                    'permissions' => $permissions
                ]);

                \DB::table('menu_role')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'menu_id' => $menuId
                    ],
                    [
                        'is_active'            => $isActive,
                        'can_view'             => in_array('view', $permissions),
                        'can_add'              => in_array('add', $permissions),
                        'can_edit'             => in_array('edit', $permissions),
                        'can_delete'           => in_array('delete', $permissions),
                        'can_cancel'           => in_array('cancel', $permissions),
                        'can_change_password'  => in_array('password', $permissions),
                        'can_restore_data'     => in_array('restore', $permissions),
                        'show_delete_backup'   => in_array('hapus_backup', $permissions),
                        'show_logout'          => in_array('logout', $permissions),
                        'can_access_settings'  => in_array('view', $permissions),
                        'updated_at'           => now()
                    ]
                );
            }

            \Log::info('Quick save successful (Admin2)');

            return response()->json([
                'success' => true,
                'message' => 'Hak akses kasir berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            \Log::error('Quick save error (Admin2):', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}   
