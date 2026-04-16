<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Allow CORS for all API routes and web routes that act as APIs
        $middleware->api(prepend: [
            \App\Http\Middleware\HandleCors::class,
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'auth.session' => \App\Http\Middleware\EnsureUserIsAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
