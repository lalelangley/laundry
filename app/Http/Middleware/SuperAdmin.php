<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Admin;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $adminId = $request->session()->get('admin_id');
        if (!$adminId) {
            return redirect()->route('login.show')->with('error', 'Silakan login dulu');
        }

        $admin = Admin::find($adminId);

        if (!$admin || $admin->role_id != 1) {
            abort(403, 'Anda tidak memiliki akses Super Admin');
        }

        return $next($request);
    }
}
