<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Kasir;
use Illuminate\Support\Facades\Hash;

class AuthWebController extends Controller
{
    // Tampilan login
    public function showLogin()
    {
        $admins = Admin::all();
        $kasirs = Kasir::all();

        return view('auth.login', compact('admins', 'kasirs'));
    }

    // Proses login (Admin & Kasir dalam 1 alur)
    public function processLogin(Request $request)
    {
        // Format user_id = "admin-1" atau "kasir-12"
        [$role, $id] = explode('-', $request->user_id);

        if ($role === 'admin') {
            $admin = Admin::find($id);

            if (!$admin || !Hash::check($request->pin, $admin->password)) {
                return back()->with('error', 'PIN salah');
            }

            session(['admin_id' => $admin->id_admin]);

            return redirect()->route('admin.dashboard');
        }

        if ($role === 'kasir') {
            $kasir = Kasir::find($id);

            if (!$kasir || !Hash::check($request->pin, $kasir->password)) {
                return back()->with('error', 'PIN salah');
            }

            session(['kasir_id' => $kasir->id_kasir]);

            return redirect()->route('kasir.dashboard');
        }

        return back()->with('error', 'Role tidak dikenali');
    }

    // Dashboard admin
    public function adminDashboard()
    {
        $admin = Admin::find(session('admin_id'));

        if (!$admin) {
            return redirect()->route('login.show')->with('error', 'Silakan login dulu');
        }

        return view('admin.dashboard', compact('admin'));
    }

    // Dashboard kasir
    public function kasirDashboard()
    {
        $kasir = Kasir::find(session('kasir_id'));

        if (!$kasir) {
            return redirect()->route('login.show')->with('error', 'Silakan login dulu');
        }

        return view('kasir.dashboard', compact('kasir'));
    }
}
