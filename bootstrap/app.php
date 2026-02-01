<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies for ngrok and other reverse proxies
        $middleware->trustProxies(at: '*');

        // Add ForceHttps to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\ForceHttps::class,
            \App\Http\Middleware\SetCacheHeaders::class,
        ]);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'guest.admin' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
            'force.https' => \App\Http\Middleware\ForceHttps::class,
            'rate.limit' => \App\Http\Middleware\RateLimit::class,
            'rate.limit.forms' => \App\Http\Middleware\RateLimitForms::class,
            'chatbot.ratelimit' => \App\Http\Middleware\ChatbotRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
