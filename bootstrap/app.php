<?php

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
            'custom.auth' => \App\Http\Middleware\CustomAuth::class,
            'api.timeout' => \App\Http\Middleware\ApiTimeout::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'api.cache' => \App\Http\Middleware\ApiCacheHeaders::class,
            'auth.file' => \App\Http\Middleware\AuthenticateFileAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
