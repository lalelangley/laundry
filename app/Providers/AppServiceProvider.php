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

        if (Auth::guard('kasir')->check()) {

            // ✅ KASIR: ROLE_ID = 2
            $menus = Menu::where('role_id', 2)
                ->where('status', 1)
                ->orderBy('urutan')
                ->get();

        } elseif (Auth::guard('admin')->check()) {

            $admin = Auth::guard('admin')->user();

            $menus = Menu::where('role_id', $admin->role_id)
                ->where('status', 1)
                ->orderBy('urutan')
                ->get();

        } else {
            $menus = collect();
        }

        $view->with('menus', $menus);
    });
}
}
