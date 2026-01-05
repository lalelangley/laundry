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
    // =============================
    // INDEX USER MANAGER
    // =============================
    public function index()
    {
        $adminLogin = auth('admin')->user(); // ambil user via guard

        if (!$adminLogin) {
            abort(403, 'Silahkan login terlebih dahulu');
        }

        $admins = Admin::whereIn('role_id', [1,2])
            ->orderBy('role_id')
            ->get();

        $kasirs = Kasir::orderBy('created_at', 'desc')->get();
        
        $drivers = Driver::orderBy('created_at', 'desc')->get(); // ✅ FIXED

        return view('manager.index', compact('admins','kasirs','drivers','adminLogin')); // ✅ FIXED
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

        // 🔒 HANYA ROLE ADMIN
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
        // ❌ SALAH: Admin::find(session('admin_id'))
        // ✅ BENAR: auth('admin')->user()
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

        // Hapus permission lama untuk role tersebut
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


    public function storeKasir(Request $request)
    {
        $request->validate([
            'nama_kasir' => 'required|string|max:100',
            'no_hp'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:6',
        ]);

        $kasir = Kasir::create([
            'nama_kasir' => $request->nama_kasir,
            'no_hp'      => $request->no_hp,
            'password'   => Hash::make($request->password),
           'role_id' => 2, // KASIR // otomatis Kasir
        ]);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Kasir berhasil ditambahkan');
    }

    public function updateStatus(Request $request)
    {
        $adminLogin = auth('admin')->user(); // ← gunakan guard admin

        if (!$adminLogin || $adminLogin->role_id != 1) {
            abort(403);
        }

        $request->validate([
            'user_type' => 'required|in:admin,kasir,driver', // ✅ FIXED
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
            Driver::where('id_driver', $request->user_id) // ✅ FIXED
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

        // 🔥 TAMBAH DI SINI
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

        return view('manager.menu-role.hak', compact(
            'roles',
            'menus',
            'permissions',
            'selectedRoleId',
            'menuActions' // ⬅️ JANGAN LUPA
        ));
    }

    // =======================
    // SAVE HAK AKSES ROLE
    // =======================
    public function saveHakRole(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role_id != 1) abort(403);

        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        // hapus dulu
        MenuRole::where('role_id', $request->role_id)->delete();

        foreach ($request->menus ?? [] as $menuId) {

            // 🔒 pastikan menu milik role tsb
            $menu = Menu::where('id', $menuId)
                ->where('role_id', $request->role_id)
                ->first();

            if (!$menu) continue;

            $perms = $request->permissions[$menuId] ?? [];

            MenuRole::create([
                'role_id'    => $request->role_id,
                'menu_id'    => $menuId,
                'can_view'   => in_array('view', $perms),
                'can_add'    => in_array('add', $perms),
                'can_edit'   => in_array('edit', $perms),
                'can_delete' => in_array('delete', $perms),
            ]);
        }

        return back()->with('success', 'Hak akses role berhasil disimpan');
    }

    // =======================
    // AKSES USER
    // =======================
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
    public function indexAdmin2()
    {
        $admin = auth('admin')->user();
        
        // Cek apakah Admin2
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Akses ditolak. Hanya Admin2 yang dapat mengakses halaman ini.');
        }

        $kasirs = Kasir::orderBy('created_at', 'desc')->get();
        $drivers = Driver::orderBy('created_at', 'desc')->get(); // ✅ FIXED

        return view('admin2.manager.index', compact('kasirs', 'drivers', 'admin')); // ✅ FIXED
    }

    // =============================
    // ADMIN2 - FORM CREATE KASIR
    // =============================
    public function createKasirAdmin2()
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        return view('admin2.manager.kasir.create'); // ✅ FIX
    }

    // =============================
    // ADMIN2 - STORE KASIR
    // =============================
    public function storeKasirAdmin2(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $request->validate([
            'nama_kasir' => 'required|string|max:100',
            'no_hp'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:6',
        ]);

        Kasir::create([
            'nama_kasir' => $request->nama_kasir,
            'no_hp'      => $request->no_hp,
            'password'   => Hash::make($request->password),
            'role_id'    => 3, // Role Kasir
            'status'     => 'aktif',
        ]);

        return redirect()
            ->route('admin2.manager.index') // ✅ FIX
            ->with('success', 'Kasir berhasil ditambahkan');
    }

    // =============================
    // ADMIN2 - AKSES KASIR
    // =============================
    public function aksesKasirAdmin2($id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3; // Role Kasir
        $role = Role::findOrFail($roleId);

        // Menu yang tersedia untuk Kasir
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

        // Menu actions untuk Kasir
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

        return view('admin2.manager.menu-role.hak', compact( // ✅ FIX
            'kasir', 'role', 'menus', 'permissions', 'menuActions'
        ));
    }

    // =============================
    // ADMIN2 - SAVE AKSES KASIR
    // =============================
    public function saveAksesKasirAdmin2(Request $request, $id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3; // Role Kasir

        $request->validate([
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        // Hapus permission lama untuk role Kasir
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

    // =============================
    // ADMIN2 - UPDATE STATUS KASIR
    // =============================
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

    // =============================
    // ADMIN2 - HAK AKSES KASIR (GENERAL)
    // =============================
    public function hakAksesKasir()
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $roleId = 3; // Role Kasir
        $role = Role::findOrFail($roleId);

        // Menu yang tersedia untuk Kasir
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

    // =============================
    // ADMIN2 - SAVE HAK AKSES KASIR (GENERAL)
    // =============================
    public function saveHakAksesKasir(Request $request)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403);
        }

        $roleId = 3; // Role Kasir

        $request->validate([
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        // Hapus permission lama untuk role Kasir
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

    // =============================
    // ADMIN2 - MENU ROLE AKSES (PER KASIR)
    // =============================
    public function menuRoleAksesAdmin2($id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Akses ditolak. Hanya Admin2 yang dapat mengakses halaman ini.');
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3; // Role Kasir
        $role = Role::findOrFail($roleId);
            // Menu yang tersedia untuk Kasir
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

        // Menu actions untuk Kasir
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

    // =============================
    // ADMIN2 - SAVE MENU ROLE AKSES (PER KASIR)
    // =============================
    public function saveMenuRoleAksesAdmin2(Request $request, $id)
    {
        $admin = auth('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Akses ditolak. Hanya Admin2 yang dapat mengakses halaman ini.');
        }

        $kasir = Kasir::findOrFail($id);
        $roleId = 3; // Role Kasir

        $request->validate([
            'menus' => 'array',
            'permissions' => 'array',
        ]);

        // Hapus permission lama untuk role Kasir
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

    // ===============================
    // STORE DRIVER
    // ===============================
    public function storeDriver(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah driver');
        }

        $request->validate([
            'nama_driver' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'no_kendaraan' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Driver::create([
            'nama_driver' => $request->nama_driver,
            'no_telp' => $request->no_telp,
            'no_kendaraan' => strtoupper($request->no_kendaraan),
            'password' => bcrypt($request->password),
            'status' => $request->status,
        ]);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Driver berhasil ditambahkan');
    }

    // ===============================
    // EDIT DRIVER - SHOW FORM
    // ===============================
    public function editDriver($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa edit driver');
        }

        $driver = Driver::findOrFail($id);
        
        return view('manager.driver.edit', compact('driver'));
    }

    // ===============================
    // UPDATE DRIVER
    // ===============================
    public function updateDriver(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update driver');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'nama_driver' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'no_kendaraan' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $updateData = [
            'nama_driver' => $request->nama_driver,
            'no_telp' => $request->no_telp,
            'no_kendaraan' => strtoupper($request->no_kendaraan),
            'status' => $request->status,
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $driver->update($updateData);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Driver berhasil diupdate');
    }

    // ===============================
    // DELETE DRIVER
    // ===============================
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

    // ===============================
    // KASIR
    // ===============================
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
            'user_id' => 'required',
            'status' => 'required|in:aktif,nonaktif'
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

    public function storeDriverKasir(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa menambah driver');
        }

        $request->validate([
            'nama_driver' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'no_kendaraan' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Driver::create([
            'nama_driver' => $request->nama_driver,
            'no_telp' => $request->no_telp,
            'no_kendaraan' => strtoupper($request->no_kendaraan),
            'password' => bcrypt($request->password),
            'status' => $request->status,
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

    public function updateDriverKasir(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang bisa update driver');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'nama_driver' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'no_kendaraan' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $updateData = [
            'nama_driver' => $request->nama_driver,
            'no_telp' => $request->no_telp,
            'no_kendaraan' => strtoupper($request->no_kendaraan),
            'status' => $request->status,
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

    // ===============================
    // ADMIN2 DRIVER METHODS
    // ===============================
    public function createDriverAdmin2()
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa menambah driver');
        }

        return view('admin2.manager.driver.create');
    }

    public function storeDriverAdmin2(Request $request)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa menambah driver');
        }

        $request->validate([
            'nama_driver' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'no_kendaraan' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        Driver::create([
            'nama_driver' => $request->nama_driver,
            'no_telp' => $request->no_telp,
            'no_kendaraan' => strtoupper($request->no_kendaraan),
            'password' => bcrypt($request->password),
            'status' => $request->status,
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

    public function updateDriverAdmin2(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 2) {
            abort(403, 'Hanya Admin2 yang bisa update driver');
        }

        $driver = Driver::findOrFail($id);

        $request->validate([
            'nama_driver' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'no_kendaraan' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $updateData = [
            'nama_driver' => $request->nama_driver,
            'no_telp' => $request->no_telp,
            'no_kendaraan' => strtoupper($request->no_kendaraan),
            'status' => $request->status,
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

    // ===============================
    // ADMIN - EDIT
    // ===============================
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

    // ===============================
    // ADMIN - UPDATE
    // ===============================
    public function updateAdmin(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengupdate admin');
        }

        $adminData = Admin::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $id . ',id_admin',
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|in:1,2',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $updateData = [
            'nama' => $request->nama,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'status' => $request->status,
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $adminData->update($updateData);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Data admin berhasil diupdate');
    }

    // ===============================
    // KASIR - EDIT
    // ===============================
    public function editKasir($id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengedit kasir');
        }

        $kasir = Kasir::findOrFail($id);

        return view('manager.kasir.edit', compact('kasir'));
    }

    // ===============================
    // KASIR - UPDATE
    // ===============================
    public function updateKasir(Request $request, $id)
    {
        $admin = auth()->guard('admin')->user();
        
        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengupdate kasir');
        }

        $kasir = Kasir::findOrFail($id);

        $request->validate([
            'nama_kasir' => 'required|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $updateData = [
            'nama_kasir' => $request->nama_kasir,
            'no_hp' => $request->no_hp,
            'status' => $request->status,
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $kasir->update($updateData);

        return redirect()
            ->route('manager.index')
            ->with('success', 'Data kasir berhasil diupdate');
    }
}