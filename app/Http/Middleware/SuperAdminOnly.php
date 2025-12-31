<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('admin')->user();
        
        if (!$user) {
            abort(403, 'Unauthorized access');
        }
        
        if ($user->role_id != 1) {
            abort(403, 'Hanya Super Admin yang dapat mengakses halaman ini');
        }
        
        return $next($request);
    }
}