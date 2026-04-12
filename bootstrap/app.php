<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',   // ← WAJIB ADA
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'telegram/webhook',
        ]);

        // GROUP API MIDDLEWARE
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // REGISTER MIDDLEWARE ALIAS
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class, // ← untuk auth:sanctum
            'role' => \App\Http\Middleware\CheckRole::class,           // ← middleware role buatan sendiri
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
