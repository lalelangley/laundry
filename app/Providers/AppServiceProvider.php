<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.sidebar', function ($view) {
            $menus = collect();

            // Cek guard admin
            if (Auth::guard('admin')->check()) {
                $roleId = Auth::guard('admin')->user()->role_id;
                
                $menus = Menu::where('role_id', $roleId)
                             ->where('status', 1)
                             ->orderBy('urutan')
                             ->get();
            }
            // Cek guard admin2
            elseif (Auth::guard('admin2')->check()) {
                $roleId = Auth::guard('admin2')->user()->role_id;
                
                $menus = Menu::where('role_id', $roleId)
                             ->where('status', 1)
                             ->orderBy('urutan')
                             ->get();
            }
            // Cek guard kasir
            elseif (Auth::guard('kasir')->check()) {
                // Kasir selalu role_id = 3
                $menus = Menu::where('role_id', 3)
                             ->where('status', 1)
                             ->orderBy('urutan')
                             ->get();
            }

            $view->with('menus', $menus);
        });
    }
}