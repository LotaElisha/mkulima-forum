<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Sanctum stateful middleware removed for API-only usage
        // All API auth is done via Bearer tokens, not cookies.
        // Never redirect unauthenticated API requests to a (nonexistent)
        // login page — let them surface as 401 JSON instead.
        $middleware->redirectGuestsTo(
            fn ($request) => $request->is('api/*') ? null : '/'
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API-only backend: always render exceptions as JSON for api/*
        // (otherwise unauthenticated requests without an Accept header try
        // to redirect to a nonexistent 'login' route and 500).
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
