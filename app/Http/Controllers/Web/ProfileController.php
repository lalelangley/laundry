<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    // =============================
    // FORM EDIT PROFILE ADMIN
    // =============================
    public function editAdmin()
    {
        $admin = auth()->guard('admin')->user();

        return view('profile.edit', compact('admin'));
    }

    // =============================
    // UPDATE PROFILE ADMIN
    // =============================
    public function updateAdmin(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin,email,' . $admin->id_admin . ',id_admin',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'nama'  => $request->nama,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return back()->with('success', 'Profile berhasil diperbarui');
    }

    // =============================
    // FORM EDIT PROFILE ADMIN
    // =============================
    public function editAdmin2()
    {
        $admin = auth()->guard('admin')->user();

        return view('profile.edit', compact('admin'));
    }

    // =============================
    // UPDATE PROFILE ADMIN
    // =============================
    public function updateAdmin2(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin,email,' . $admin->id_admin . ',id_admin',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'nama'  => $request->nama,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return back()->with('success', 'Profile berhasil diperbarui');
    }

    public function editKasir()
{
    $kasir = auth()->guard('kasir')->user();
    return view('profile.edit', compact('kasir'));
}

public function updateKasir(Request $request)
{
    $kasir = auth()->guard('kasir')->user();

    $request->validate([
        'nama_kasir' => 'required|string|max:100',
        'no_hp'      => 'required|string|max:20',
        'password'   => 'nullable|min:6|confirmed',
        'gambar'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $kasir->nama_kasir = $request->nama_kasir;
    $kasir->no_hp = $request->no_hp;

    if ($request->hasFile('gambar')) {
        if ($kasir->gambar && Storage::disk('public')->exists($kasir->gambar)) {
            Storage::disk('public')->delete($kasir->gambar);
        }

        $kasir->gambar = $request->file('gambar')->store('kasir', 'public');
    }

    if ($request->filled('password')) {
        $kasir->password = Hash::make($request->password);
    }

    $kasir->save();

    return back()->with('success', 'Profil kasir berhasil diperbarui');
}

}
