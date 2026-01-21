<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MenuRole;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
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
        // ✅ Get authenticated user (support both admin and kasir guard)
        $user = auth('admin')->user() ?? auth('kasir')->user();

        if (!$user) {
            abort(403, 'Silakan login terlebih dahulu');
        }

        // ✅ Super Admin bypass - Cast to int untuk konsistensi
        if ((int)$user->role_id === 1) {
            return $next($request);
        }

        // ✅ Get current route name
        $routeName = Route::currentRouteName();

        if (!$routeName) {
            abort(403, 'Route tidak valid');
        }

        // ✅ BYPASS ROUTES - Allow access without checking permissions
        $bypassPatterns = [
            'dashboard',
            'profile',
            'change.password',
            'password.update',
            'logout',
            'reset',
        ];

        foreach ($bypassPatterns as $pattern) {
            if (str_contains($routeName, $pattern)) {
                return $next($request);
            }
        }

        // ✅ Extract menu identifier from route
        $menuIdentifier = $this->extractMenuFromRoute($routeName);

        if (!$menuIdentifier) {
            Log::warning("Menu identifier not found for route: {$routeName}");
            abort(403, 'Menu tidak ditemukan');
        }

        // ✅ Check permission menggunakan helper yang sudah diperbaiki
        if (!checkPermission($menuIdentifier, $permission)) {
            Log::info("Permission denied", [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'route' => $routeName,
                'menu' => $menuIdentifier,
                'permission' => $permission,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Anda tidak memiliki akses untuk melakukan aksi ini'
                ], 403);
            }

            abort(403, "Anda tidak memiliki hak akses untuk {$permission} pada menu {$menuIdentifier}");
        }

        return $next($request);
    }

    /**
     * ✅ Extract menu identifier from route name
     * 
     * Contoh transformasi:
     * - kasir.pesanan.online.index → pesanan.online
     * - admin.layanan.jenis.create → layanan
     * - admin2.riwayat.edit → riwayat
     */
    private function extractMenuFromRoute(string $routeName): ?string
    {
        // ✅ Remove prefix (kasir., admin., admin2., manager.)
        $routeName = preg_replace('/^(kasir|admin2?|manager)\./', '', $routeName);

        // ✅ Map of route patterns to menu identifiers (ORDER MATTERS!)
        $menuMap = [
            'pesanan.online' => 'pesanan.online',  // ✅ PENTING: Cek yang spesifik dulu
            'transaksi'      => 'transaksi',
            'riwayat'        => 'riwayat',
            'layanan'        => 'layanan',
            'satuan'         => 'satuan',
            'parfum'         => 'parfum',
            'pelanggan'      => 'pelanggan',
            'pengeluaran'    => 'pengeluaran',
            'laporan'        => 'laporan',
            'manager'        => 'manager',
        ];

        // ✅ Check dengan urutan prioritas
        foreach ($menuMap as $pattern => $menu) {
            if (str_starts_with($routeName, $pattern)) {
                return $menu;
            }
        }

        // ✅ Fallback: ambil segment pertama
        $segments = explode('.', $routeName);
        $firstSegment = $segments[0] ?? null;

        // ✅ Validasi bahwa segment ini ada di menu map
        if ($firstSegment && in_array($firstSegment, $menuMap)) {
            return $firstSegment;
        }

        return null;
    }
}