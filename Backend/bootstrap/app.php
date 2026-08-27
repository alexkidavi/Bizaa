<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);
        $middleware->alias([
            'permission'   => \App\Http\Middleware\PermissionMiddleware::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'superadmin'   => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        // A DB constraint should normally be caught by request validation
        // before it ever reaches the query — this is the fallback for
        // whatever validation didn't anticipate, so users see a plain
        // message instead of a raw SQL error (which APP_DEBUG=true would
        // otherwise dump in full, including table/column/bound values).
        $exceptions->render(function (QueryException $e, Request $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            $message = str_contains($e->getMessage(), 'Duplicate entry')
                ? 'That value is already in use. Please use a different one.'
                : 'Something went wrong while saving. Please try again.';

            return response()->json(['message' => $message], 422);
        });
    })
    ->create();