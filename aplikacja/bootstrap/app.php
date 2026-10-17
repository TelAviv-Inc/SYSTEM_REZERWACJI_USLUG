<?php

use App\Http\Middleware\EnsureHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => EnsureHasRole::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            'throttle:web', // limits defined in AppServiceProvider
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
