<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ Tambahkan check untuk avoid error saat migration/seeding
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
                    // ✅ Cek guard dengan lebih hati-hati
                    if (Auth::guard('admin')->check()) {
                        $user = Auth::guard('admin')->user();
                        if ($user && isset($user->role_id)) {
                            $roleId = $user->role_id;
                            $guardType = 'admin';
                        }
                    }
                    elseif (Auth::guard('kasir')->check()) {
                        $user = Auth::guard('kasir')->user();
                        if ($user) {
                            $roleId = 3;
                            $guardType = 'kasir';
                        }
                    }

                    // ✅ Load menu hanya jika roleId valid
                    if ($roleId && DB::table('menu')->exists()) {
                        $menus = DB::table('menu')
                            ->join('menu_role', function($join) use ($roleId) {
                                $join->on('menu.id', '=', 'menu_role.menu_id')
                                     ->where('menu_role.role_id', '=', $roleId);
                            })
                            ->where('menu.status', 1)
                            ->where('menu_role.can_view', 1)
                            ->orderBy('menu.urutan', 'asc')
                            ->select('menu.*')
                            ->get();
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