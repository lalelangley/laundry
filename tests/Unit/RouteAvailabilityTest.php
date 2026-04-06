<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteAvailabilityTest extends TestCase
{
    public function test_required_documentation_and_api_routes_exist(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes());

        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'privacy-policy/telegram-bot'));
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'telegram/webhook'));
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'api/transaksi/share'));
    }
}
