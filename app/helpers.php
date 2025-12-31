<?php

use App\Models\MenuRole;
use Illuminate\Support\Facades\Auth;

function can($menuRoute, $action = 'view')
{
    $user = Auth::guard('admin')->check()
        ? Auth::guard('admin')->user()
        : Auth::guard('kasir')->user();

    if (!$user) return false;

    $permission = MenuRole::where('role_id', $user->role_id)
        ->whereHas('menu', function ($q) use ($menuRoute) {
            $q->where('route', $menuRoute);
        })
        ->first();

    return $permission && ($permission->{'can_'.$action} ?? false);
}
