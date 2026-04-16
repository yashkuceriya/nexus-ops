<?php

use App\Http\Middleware\EnsureTenantActive;
use App\Http\Middleware\EnsureTenantActiveApi;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.active' => EnsureTenantActive::class,
            'tenant.active.api' => EnsureTenantActiveApi::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // $middleware->throttleWithRedis(); // Enable when Redis is available in production
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
