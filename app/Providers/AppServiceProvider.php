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

    if (auth('admin')->check()) {
        $roleId = auth('admin')->user()->role_id;
    } elseif (auth('kasir')->check()) {
        $roleId = auth('kasir')->user()->role_id;
    } else {
        $view->with('menus', collect());
        return;
    }

    $menus = Menu::whereHas('roles', function ($q) use ($roleId) {
            $q->where('role_id', $roleId)
              ->where('can_view', 1);
        })
        ->where('status', 1)
        ->orderBy('urutan')
        ->get();

    $view->with('menus', $menus);
});
}
}
