<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MenuRole;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Get authenticated user (support both admin and kasir guard)
        $user = auth('admin')->user() ?? auth('kasir')->user();

        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        // Super admin (role_id = 1) has all permissions
        if ($user->role_id == 1) {
            return $next($request);
        }

        // Get current route name
        $routeName = $request->route()->getName();

        // ✅ BYPASS ROUTES - Allow access without checking permissions
        $bypassPatterns = [
            'kasir.laporan',      // Bypass semua route laporan kasir
            'admin2.laporan',     // Bypass semua route laporan admin2
        ];

        foreach ($bypassPatterns as $pattern) {
            if (str_contains($routeName, $pattern)) {
                return $next($request); // ✅ Allow access
            }
        }

        // Extract menu identifier from route (e.g., 'kasir.layanan.create' -> 'layanan')
        $menuIdentifier = $this->extractMenuFromRoute($routeName);

        if (!$menuIdentifier) {
            return $next($request); // No specific menu found, allow access
        }

        // Check permission in menu_role table
        $hasPermission = MenuRole::whereHas('menu', function($query) use ($menuIdentifier) {
                $query->where('route', 'like', "%{$menuIdentifier}%");
            })
            ->where('role_id', $user->role_id)
            ->where("can_{$permission}", true)
            ->exists();

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Anda tidak memiliki akses untuk melakukan aksi ini'], 403);
            }
            abort(403, 'Anda tidak memiliki hak akses untuk melakukan aksi ini');
        }

        return $next($request);
    }

    /**
     * Extract menu identifier from route name
     */
    private function extractMenuFromRoute(string $routeName): ?string
    {
        // Map of route patterns to menu identifiers
        $menuMap = [
            'layanan'      => 'layanan',
            'satuan'       => 'satuan',
            'parfum'       => 'parfum',
            'pelanggan'    => 'pelanggan',
            'pengeluaran'  => 'pengeluaran',
            'transaksi'    => 'transaksi',
            'riwayat'      => 'riwayat',
            'laporan'      => 'laporan',
            'manager'      => 'manager',
        ];

        foreach ($menuMap as $pattern => $menu) {
            if (str_contains($routeName, $pattern)) {
                return $menu;
            }
        }

        return null;
    }
}