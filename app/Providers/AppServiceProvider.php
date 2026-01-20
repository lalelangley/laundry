<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            $this->composeMenus();
        }
    }

    protected function composeMenus(): void
    {
        try {
            View::composer(['layouts.sidebar', 'layouts.master'], function ($view) {
                $menus = collect();
                $roleId = null;
                $guardType = null;

                try {
                    if (!Schema::hasTable('menu') || !Schema::hasTable('menu_role')) {
                        $view->with([
                            'menus' => collect(),
                            'guardType' => null,
                            'roleId' => null
                        ]);
                        return;
                    }

                    if (Auth::guard('admin')->check()) {
                        $user = Auth::guard('admin')->user();
                        if ($user && isset($user->role_id)) {
                            $roleId = (int)$user->role_id;
                            $guardType = 'admin';
                        }
                    } elseif (Auth::guard('kasir')->check()) {
                        $user = Auth::guard('kasir')->user();
                        if ($user) {
                            $roleId = 3;
                            $guardType = 'kasir';
                        }
                    }

                    if ($roleId) {
                        // ✅ SUPER ADMIN - Load semua menu aktif
                        if ($roleId === 1) {
                            $menus = DB::table('menu')
                                ->where('status', 1)
                                ->where('role_id', $roleId)
                                ->orderBy('urutan', 'asc')
                                ->get();
                        } else {
                            // ✅ PERBAIKAN: Hapus filter menu.role_id
                            // Menu bisa dibagikan ke berbagai role melalui menu_role
                            $menus = DB::table('menu')
                                ->join('menu_role', function($join) use ($roleId) {
                                    $join->on('menu.id', '=', 'menu_role.menu_id')
                                         ->where('menu_role.role_id', '=', $roleId);
                                })
                                ->where('menu.status', 1)
                                // ❌ HAPUS: ->where('menu.role_id', $roleId)
                                ->where('menu_role.is_active', 1)
                                ->where('menu_role.can_view', 1)
                                ->orderBy('menu.urutan', 'asc')
                                ->select('menu.*')
                                ->distinct()  // ✅ Tambahkan distinct untuk avoid duplikasi
                                ->get();
                        }

                        Log::info("Menus loaded for user", [
                            'role_id' => $roleId,
                            'guard' => $guardType,
                            'menu_count' => $menus->count()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error loading menus: ' . $e->getMessage());
                    $menus = collect();
                    $roleId = null;
                    $guardType = null;
                }

                $view->with([
                    'menus' => $menus,
                    'guardType' => $guardType,
                    'roleId' => $roleId
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Error in view composer: ' . $e->getMessage());
        }
    }
}