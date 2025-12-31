<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuRole;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $menuRoute, $action = 'view')
    {
        // ambil user admin / kasir
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('kasir')->check()) {
            $user = Auth::guard('kasir')->user();
        } else {
            abort(401);
        }

        $permission = MenuRole::where('role_id', $user->role_id)
            ->whereHas('menu', function ($q) use ($menuRoute) {
                $q->where('route', $menuRoute);
            })
            ->first();

        if (!$permission || !($permission->{'can_'.$action} ?? false)) {
            abort(403, 'Anda tidak memiliki hak akses');
        }

        return $next($request);
    }
}
