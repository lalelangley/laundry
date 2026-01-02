<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.sidebar', function ($view) {
            $menus = collect();
            $roleId = null;

            // Cek guard admin
            if (Auth::guard('admin')->check()) {
                $roleId = Auth::guard('admin')->user()->role_id;
            }
            // Cek guard admin2
            elseif (Auth::guard('admin2')->check()) {
                $roleId = Auth::guard('admin2')->user()->role_id;
            }
            // Cek guard kasir
            elseif (Auth::guard('kasir')->check()) {
                $roleId = 3; // Kasir selalu role_id = 3
            }

            // Query menggunakan relationship
            if ($roleId) {
                $menus = Menu::whereHas('roles', function($query) use ($roleId) {
                        $query->where('role_id', $roleId)
                              ->where('can_view', 1);
                    })
                    ->where('status', 1)
                    ->with(['roles' => function($query) use ($roleId) {
                        $query->where('role_id', $roleId);
                    }])
                    ->orderBy('urutan', 'asc')
                    ->get();
            }

            $view->with('menus', $menus);
        });
    }
}