<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('admin_id')) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Silakan login dulu');
        }

        return $next($request);
    }
}
