<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuRole;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Admin;

class UserManagerController extends Controller
{
    // =============================
    // INDEX USER MANAGER
    // =============================
    public function index()
    {
        $adminLogin = Admin::find(session('admin_id'));

        if (!$adminLogin) {
            abort(403);
        }

        // SUPER ADMIN & ADMIN
        $admins = Admin::whereIn('role_id', [1, 2])
            ->where('is_active', 1)
            ->orderBy('role_id')
            ->get();

        // KASIR (ROLE_ID = 3)
        $kasirs = Admin::where('role_id', 3)
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manager.index', compact(
            'admins',
            'kasirs',
            'adminLogin'
        ));
    }

    // =============================
    // HALAMAN AKSES PER USER
    // =============================
public function akses($user_type, $user_id)
{
    $adminLogin = Admin::find(session('admin_id'));

    if (!$adminLogin || $adminLogin->role_id != 1) {
        abort(403, 'Tidak punya izin mengatur hak akses');
    }

    if ($user_type === 'admin') {
        $user = Admin::findOrFail($user_id);
    } elseif ($user_type === 'kasir') {
        $user = Admin::findOrFail($user_id); // kasir juga di tabel admin
    } else {
        abort(404);
    }

    $menus = Menu::all();

    $userPermissions = MenuRole::where('user_type', $user_type)
        ->where('user_id', $user_id)
        ->get()
        ->keyBy('menu_id');

    return view('manager.menu-role.akses', compact(
        'user',
        'user_type',
        'menus',
        'userPermissions'
    ));
}


    // =============================
    // FORM TAMBAH MENU ROLE
    // =============================
    public function create()
    {
        $admin = Admin::find(session('admin_id'));

        if (!$admin) {
            return redirect()->route('login.show');
        }

        if ($admin->role_id != 1) {
            abort(403);
        }

        $roles = Role::all();
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
    $adminLogin = Admin::find(session('admin_id'));
    if (!$adminLogin || $adminLogin->role_id != 1) {
        abort(403);
    }

    $user_type = $request->user_type;
    $user_id = $request->user_id;
    $permissions = $request->permissions ?? [];

    MenuRole::where('user_type', $user_type)
        ->where('user_id', $user_id)
        ->delete();

    foreach ($permissions as $menu_id => $perms) {
        MenuRole::create([
            'user_type' => $user_type,
            'user_id' => $user_id,
            'menu_id' => $menu_id,
            'can_view' => in_array('view', $perms),
            'can_add' => in_array('add', $perms),
            'can_edit' => in_array('edit', $perms),
            'can_delete' => in_array('delete', $perms),
        ]);
    }

    return back()->with('success', 'Hak akses berhasil diperbarui!');
}

}
