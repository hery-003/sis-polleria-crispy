<?php

use App\Http\Middleware\ApplySettings;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->prefersJsonResponses()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ApplySettings::class,
            HandleInertiaRequests::class,
            SanitizeInput::class,
            SecurityHeaders::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->preventRequestForgery(except: ['api/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
