<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

// ===== Default Middleware =====
use App\Http\Middleware\TrustProxies;
use Fruitcake\Cors\HandleCors;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use App\Http\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;

// ===== Custom Middleware =====
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAdmin; // middleware khusus admin
use App\Http\Middleware\SuperAdmin;        // middleware khusus super admin
use App\Http\Middleware\SuperAdminOnly;    // ← TAMBAH: middleware super admin baru
use App\Http\Middleware\CheckPermission;   // ← TAMBAH: middleware check permission
use App\Http\Middleware\RedirectIfAuthenticated;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Middleware groups for web routes.
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Route middleware.
     *
     * Can be assigned to specific routes.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.admin' => \App\Http\Middleware\AuthenticateAdmin::class,
        'super.admin' => \App\Http\Middleware\SuperAdmin::class,       // middleware super admin (existing)
        'superadmin' => \App\Http\Middleware\SuperAdminOnly::class,    // ← TAMBAH: middleware super admin (new - cleaner)
        'permission' => \App\Http\Middleware\CheckPermission::class,   // ← TAMBAH: middleware check permission (CRUD access control)
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}