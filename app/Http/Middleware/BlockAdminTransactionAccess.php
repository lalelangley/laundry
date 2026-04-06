<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockAdminTransactionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            return $next($request);
        }

        $roleId = (int) ($admin->role_id ?? 0);

        if (in_array($roleId, [1, 2], true)) {
            $redirectRoute = $roleId === 1 ? 'laporan.index' : 'admin2.laporan.index';

            return redirect()
                ->route($redirectRoute)
                ->with('error', 'Akses transaksi untuk Admin dan Admin2 dinonaktifkan. Gunakan menu laporan.');
        }

        return $next($request);
    }
}
