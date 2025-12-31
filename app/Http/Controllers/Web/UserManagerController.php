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

    return view('manager.index', compact('admins','kasirs','adminLogin'));
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
    $admin = Admin::find(session('admin_id'));

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
        $admin = Admin::find(session('admin_id'));

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
        $admin = Admin::find(session('admin_id'));

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
        $admin = Admin::find(session('admin_id'));

        if ($admin->role_id != 1) {
            abort(403);
        }

        MenuRole::findOrFail($id)->delete();

        return back()->with('success', 'Hak akses dihapus');
    }

  public function saveUserPermission(Request $request)
{
   $admin = Admin::find(session('admin_id'));
    if (!$admin || $admin->role_id != 1) abort(403);

    $request->validate([
        'role_id' => 'required|in:1,2',
        'permissions' => 'array'
    ]);

    $permissions = $request->permissions ?? [];

    MenuRole::where('role_id', $request->role_id)->delete();

    foreach ($permissions as $menu_id => $perms) {
        MenuRole::create([
            'role_id'    => $request->role_id,
            'menu_id'    => $menu_id,
            'can_view'   => in_array('view', $perms),
            'can_add'    => in_array('add', $perms),
            'can_edit'   => in_array('edit', $perms),
            'can_delete' => in_array('delete', $perms),
        ]);
    }

    return back()->with('success', 'Hak akses role berhasil diperbarui');
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
        'role_id'    => 3, // otomatis Kasir
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
        'user_type' => 'required|in:admin,kasir',
        'user_id'   => 'required',
        'status'    => 'required|in:aktif,nonaktif',
    ]);

    if ($request->user_type === 'admin') {
        Admin::where('id_admin', $request->user_id)
            ->update(['status' => $request->status]);
    } else {
        Kasir::where('id_kasir', $request->user_id)
            ->update(['status' => $request->status]);
    }

    return back()->with('success', 'Status berhasil diubah');
}


public function hakRole()
{
    $selectedRoleId = request('role_id') ?? 1;

    $adminLogin = auth('admin')->user();
    if (!$adminLogin || $adminLogin->role_id != 1) abort(403);

    $roles = Role::orderBy('id')->get();
    $menus = Menu::orderBy('urutan')->get();

    // Ambil permissions hanya yang ada di DB
    $permissions = MenuRole::where('role_id', $selectedRoleId)
        ->get()
        ->keyBy('menu_id'); // <-- key by menu_id

    return view('manager.menu-role.hak', compact(
        'roles', 'menus', 'permissions', 'selectedRoleId'
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

    $roleId = $request->role_id;
    $menusChecked = $request->menus ?? [];
    $permissionsInput = $request->permissions ?? [];

    // Hapus semua dulu
    MenuRole::where('role_id', $roleId)->delete();

    foreach ($menusChecked as $menuId) {
        $perms = $permissionsInput[$menuId] ?? [];
        MenuRole::create([
            'role_id'    => $roleId,
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

    // pastikan role_id ada
    $roleId = $user->role_id ?? ($type === 'kasir' ? 3 : null);
    $role = Role::findOrFail($roleId);

    $menus = Menu::orderBy('urutan')->get();
    $permissions = MenuRole::where('role_id', $role->id)
        ->get()
        ->keyBy('menu_id');

    return view('manager.menu-role.akses', compact(
        'user', 'role', 'menus', 'permissions'
    ));
}

}