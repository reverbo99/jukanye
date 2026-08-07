<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Mobile clients often omit Accept; always return JSON from /api/*
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Stable mobile error JSON (no HTML, no debug stack noise on /api/*).
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], $e->status);
            }

            $status = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
            } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404;
            }

            $message = match ($status) {
                404 => 'Not found',
                403 => 'Forbidden',
                401 => 'Unauthenticated',
                429 => 'Too many requests',
                default => $status >= 500
                    ? 'Server error'
                    : ($e->getMessage() !== '' ? $e->getMessage() : 'Request failed'),
            };

            return response()->json([
                'message' => $message,
                'errors' => (object) [],
            ], $status);
        });
    })->create();
