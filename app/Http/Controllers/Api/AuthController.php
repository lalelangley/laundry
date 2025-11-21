<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Kasir;

class AuthController extends Controller
{
    public function loginAdmin(Request $request)
    {
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['success' => false, 'message' => 'Email atau password salah'], 401);
        }

        return response()->json([
            'success' => true,
            'admin' => $admin,
            'token' => $admin->createToken('admin')->plainTextToken
        ]);
    }

    public function loginKasir(Request $request)
    {
        $kasir = Kasir::find($request->kasir_id);

        if (!$kasir || !Hash::check($request->pin, $kasir->password)) {
            return response()->json(['success' => false, 'message' => 'PIN salah'], 401);
        }

        return response()->json([
            'success' => true,
            'kasir' => $kasir,
            'token' => $kasir->createToken('kasir')->plainTextToken
        ]);
    }
}
