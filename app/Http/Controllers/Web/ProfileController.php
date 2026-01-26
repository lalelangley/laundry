<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

        return view('admin2.profile.edit', compact('admin')); // ✅ BENAR
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

  // ==================== KASIR METHODS ====================
public function editKasir()
{
    // Ambil dari auth guard kasir
    $kasir = auth('kasir')->user();
    
    if (!$kasir) {
        return redirect()->route('kasir.dashboard')->with('error', 'Silakan login terlebih dahulu');
    }
    
    return view('kasir.profile.edit', compact('kasir'));
}

public function updateKasir(Request $request)
{
    // Ambil kasir dari auth guard
    $kasir = auth('kasir')->user();
    
    if (!$kasir) {
        return response()->json([
            'status' => false,
            'message' => 'Session expired'
        ], 401);
    }
    
    $request->validate([
        'nama_kasir' => 'required|string|max:255',
        'no_hp' => 'required|string|max:15',
        'password' => 'nullable|min:6|confirmed',
        'gambar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);
    
    $updateData = [
        'nama_kasir' => $request->nama_kasir,
        'no_hp' => $request->no_hp,
        'updated_at' => now(),
    ];
    
    // Upload foto jika ada
    if ($request->hasFile('gambar')) {
        // Hapus foto lama
        if ($kasir->gambar && Storage::disk('public')->exists($kasir->gambar)) {
            Storage::disk('public')->delete($kasir->gambar);
        }
        
        $path = $request->file('gambar')->store('kasir', 'public');
        $updateData['gambar'] = $path;
    }
    
    // Update password jika diisi
    if ($request->filled('password')) {
        $updateData['password'] = bcrypt($request->password);
    }
    
    // Update menggunakan model/query builder
    DB::table('kasir')
        ->where('id_kasir', $kasir->id_kasir)
        ->update($updateData);
    
    return response()->json([
        'status' => true,
        'message' => 'Profile berhasil diperbarui'
    ]);
}
}
