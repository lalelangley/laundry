<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // app/Http/Middleware/CheckRole.php
public function handle($request, Closure $next, ...$roles)
{
    $user = auth()->user();

    if (!in_array($user->role->nama_role, $roles)) {
        abort(403, 'Unauthorized');
    }

    return $next($request);
}

}
