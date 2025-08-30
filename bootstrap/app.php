<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IsAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\SetApiLocale::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'is_admin' => IsAdmin::class,
            'is_super_admin' => \App\Http\Middleware\IsSuperAdminMiddleware::class,
            'is_user' => \App\Http\Middleware\IsUserMiddleware::class,
            'set_api_locale' => \App\Http\Middleware\SetApiLocale::class,
            'QrCode' => SimpleSoftwareIO\QrCode\Facades\QrCode::class,
            
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
