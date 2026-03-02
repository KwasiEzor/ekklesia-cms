<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: 'api',
        then: function (): void {
            RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)->by(
                (tenant('id') ?? 'global').'|'.$request->ip()
            ));

            RateLimiter::for('auth', fn (Request $request): Limit => Limit::perMinute(5)->by(
                $request->ip()
            ));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
