<?php

use App\Http\Middleware\EnsureClientProjectAccess;
use App\Http\Middleware\EnsureClientUserIsActive;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',

        then: function (): void {
            Route::middleware('web')
                ->prefix('client')
                ->name('client.')
                ->group(
                    base_path(
                        'routes/client.php'
                    )
                );
        }
    )
    ->withMiddleware(function (
        Middleware $middleware
    ): void {
        $middleware->alias([
            'active' =>
                EnsureUserIsActive::class,

            'client.active' =>
                EnsureClientUserIsActive::class,

            'client.project' =>
                EnsureClientProjectAccess::class,
        ]);
    })
    ->withExceptions(function (
        Exceptions $exceptions
    ): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*')
                || $request->expectsJson(),
        );
    })
    ->create();