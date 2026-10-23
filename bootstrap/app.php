<?php

use App\Http\Middleware\AdminTokenCookie;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [SecurityHeaders::class]);
        $middleware->api(prepend: [AdminTokenCookie::class]);
        $middleware->alias(['verify.webhook' => VerifyWebhookSignature::class]);
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

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/v1') && ! $request->is('api/v1/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return null;
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Haipatikani.',
                    'errors' => new stdClass,
                ], 404);
            }

            $status = $e instanceof AuthenticationException
                ? 401
                : ($e instanceof HttpExceptionInterface ? $e->getStatusCode() : null);

            if ($status === null) {
                return null;
            }

            $message = $e->getMessage() !== ''
                ? $e->getMessage()
                : ($status === 401 ? 'Unauthenticated.' : 'Request failed.');

            return response()->json([
                'message' => $message,
                'errors' => new stdClass,
            ], $status);
        });
    })->create();
