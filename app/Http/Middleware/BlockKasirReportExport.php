<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockKasirReportExport
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('kasir')->check()) {
            return redirect()
                ->route('kasir.laporan.index')
                ->with('error', 'Kasir hanya dapat melihat laporan dan tidak dapat export data.');
        }

        return $next($request);
    }
}
