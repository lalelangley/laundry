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
        $roleId = auth('kasir')->check() ? 3 : (isset($user->role_id) ? (int) $user->role_id : null);

        if (!$user) {
            return $this->handleUnauthenticated($request);
        }

        // ✅ Super Admin bypass - Cast to int untuk konsistensi
        if ($roleId === 1) {
            return $next($request);
        }

        // ✅ Validate permission parameter
        if (!in_array($permission, ['view', 'add', 'edit', 'delete'])) {
            Log::error("Invalid permission parameter", [
                'permission' => $permission,
                'route' => Route::currentRouteName()
            ]);
            abort(500, 'Invalid permission configuration');
        }

        // ✅ Get current route name
        $routeName = Route::currentRouteName();

        if (!$routeName) {
            Log::warning("Route name not found", ['url' => $request->url()]);
            abort(403, 'Route tidak valid');
        }

        // ✅ BYPASS ROUTES - Allow access without checking permissions
        if ($this->shouldBypassPermissionCheck($routeName)) {
            return $next($request);
        }

        // ✅ Extract menu identifier from route
        $menuIdentifier = $this->extractMenuFromRoute($routeName);

        if (!$menuIdentifier) {
            Log::warning("Menu identifier not found for route", [
                'route' => $routeName,
                'user_id' => $user->id_kasir ?? $user->id_admin ?? null,
                'role_id' => $roleId
            ]);
            abort(403, 'Menu tidak ditemukan');
        }

        // ✅ Check permission menggunakan helper yang sudah diperbaiki
        if (!checkPermission($menuIdentifier, $permission)) {
            if ($this->canFallbackToViewPermission($request, $routeName, $permission, $menuIdentifier)) {
                Log::info("Permission fallback to view", [
                    'user_id' => $user->id_kasir ?? $user->id_admin ?? null,
                    'role_id' => $roleId,
                    'route' => $routeName,
                    'menu' => $menuIdentifier,
                    'requested_permission' => $permission,
                ]);

                return $next($request);
            }

            return $this->handlePermissionDenied(
                $request,
                $user,
                $roleId,
                $routeName,
                $menuIdentifier,
                $permission
            );
        }

        return $next($request);
    }

    private function canFallbackToViewPermission(
        Request $request,
        string $routeName,
        string $permission,
        string $menuIdentifier
    ): bool {
        if (!$request->isMethod('get')) {
            return false;
        }

        if (!in_array($permission, ['add', 'edit'])) {
            return false;
        }

        $viewFallbackRoutes = [
            '.create',
            '.edit',
            '.confirm',
            '.pelanggan',
            '.detail',
            '.print',
            '.form',
        ];

        foreach ($viewFallbackRoutes as $suffix) {
            if (str_ends_with($routeName, $suffix)) {
                return checkPermission($menuIdentifier, 'view');
            }
        }

        return false;
    }

    /**
     * ✅ Check if route should bypass permission check
     * 
     * @param string $routeName
     * @return bool
     */
    private function shouldBypassPermissionCheck(string $routeName): bool
    {
        $bypassPatterns = [
            'dashboard',
            'profile',
            'change.password',
            'password.update',
            'logout',
            'reset',
            'fcm-token',
        ];

        foreach ($bypassPatterns as $pattern) {
            if (str_contains($routeName, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ Extract menu identifier from route name
     * 
     * Contoh transformasi:
     * - kasir.pesanan.online.index → pesanan online
     * - admin.layanan.jenis.create → layanan
     * - admin2.riwayat.edit → riwayat
     * - kasir.transaksi.create → transaksi
     * - kasir.pengaturan.index → pengaturan
     */
    private function extractMenuFromRoute(string $routeName): ?string
    {
        // ✅ Remove prefix (kasir., admin., admin2., manager.)
        $cleaned = preg_replace('/^(kasir|admin2?|manager)\./', '', $routeName);

        // ✅ Map of route patterns to menu identifiers
        // IMPORTANT: Order matters! Check specific patterns first
        $menuMap = [
            // Special cases with multiple words (check first!)
            'pesanan.online'  => 'pesanan online',
            'metode.bayar'    => 'metode bayar',
            'user.manager'    => 'user manager',
            
            // Single word menus
            'transaksi'       => 'transaksi',
            'riwayat'         => 'riwayat',
            'layanan'         => 'layanan',
            'satuan'          => 'satuan',
            'parfum'          => 'parfum',
            'pelanggan'       => 'pelanggan',
            'pengeluaran'     => 'pengeluaran',
            'pengaturan'      => 'pengaturan',
            'laporan'         => 'laporan',
            'manager'         => 'manager',
        ];

        // ✅ Check dengan urutan prioritas (longest match first)
        foreach ($menuMap as $pattern => $menuIdentifier) {
            if (str_starts_with($cleaned, $pattern)) {
                Log::debug("Menu matched", [
                    'route' => $routeName,
                    'cleaned' => $cleaned,
                    'pattern' => $pattern,
                    'menu_identifier' => $menuIdentifier
                ]);
                return $menuIdentifier;
            }
        }

        // ✅ Fallback: ambil segment pertama dan cek apakah valid
        $segments = explode('.', $cleaned);
        $firstSegment = $segments[0] ?? null;

        if ($firstSegment) {
            // Check if first segment is a valid menu
            if (in_array($firstSegment, $menuMap)) {
                return $firstSegment;
            }
            
            // Check if it's in the keys
            if (array_key_exists($firstSegment, $menuMap)) {
                return $menuMap[$firstSegment];
            }
        }

        Log::warning("Menu identifier extraction failed", [
            'route' => $routeName,
            'cleaned' => $cleaned,
            'segments' => $segments
        ]);

        return null;
    }

    /**
     * ✅ Handle unauthenticated user
     */
    private function handleUnauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Silakan login terlebih dahulu'
            ], 401);
        }

        return redirect()->route('login')
            ->with('error', 'Silakan login terlebih dahulu');
    }

    /**
     * ✅ Handle permission denied with proper logging and response
     */
    private function handlePermissionDenied(
        Request $request,
        $user,
        ?int $roleId,
        string $routeName,
        string $menuIdentifier,
        string $permission
    ): Response {
        // ✅ Log permission denial
        Log::info("Permission denied", [
            'user_id' => $user->id_kasir ?? $user->id_admin ?? null,
            'role_id' => $roleId,
            'route' => $routeName,
            'menu' => $menuIdentifier,
            'permission' => $permission,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // ✅ Handle JSON requests (API)
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Anda tidak memiliki akses untuk melakukan aksi ini'
            ], 403);
        }

        // ✅ Handle web requests with SweetAlert
        $actionText = $this->getActionText($permission);
        $menuName = ucwords($menuIdentifier);

        return redirect()->back()->with('swal', [
            'icon' => 'error',
            'title' => 'Akses Ditolak!',
            'html' => "
                <div class='text-left'>
                    <p class='text-gray-700 mb-3'>
                        Anda tidak memiliki hak akses untuk <strong>{$actionText}</strong> 
                        pada menu <strong>{$menuName}</strong>
                    </p>
                    <div class='bg-red-50 border-l-4 border-red-500 p-4 rounded mt-4'>
                        <p class='font-semibold text-red-800 mb-1'>
                            <i class='bi bi-info-circle-fill'></i> Butuh Akses?
                        </p>
                        <p class='text-sm text-red-600'>
                            Hubungi administrator untuk mendapatkan izin akses.
                        </p>
                    </div>
                </div>
            ",
            'confirmButtonColor' => '#ef4444',
            'confirmButtonText' => '<i class="bi bi-arrow-left"></i> Kembali',
            'width' => '500px'
        ]);
    }

    /**
     * ✅ Get Indonesian text for permission action
     */
    private function getActionText(string $permission): string
    {
        $actionMap = [
            'view' => 'melihat',
            'add' => 'menambah',
            'edit' => 'mengubah',
            'delete' => 'menghapus',
        ];

        return $actionMap[$permission] ?? $permission;
    }
}
