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
        $middleware->trustProxies(
        at: '*',
        headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                 \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                 \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                 \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'wp.api.key' => \App\Http\Middleware\EnsureApiKeyIsValid::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

    // Errores claros de conexión / DB caída
    $exceptions->render(function (\Illuminate\Database\ConnectionException $e, $request) {
        if (! config('app.debug')) {
            return response()->view('errors.db-down', [], 503);
        }

        return null;
    });

    $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
        if (! config('app.debug')) {
            return response()->view('errors.db-down', [], 503);
        }

        return null;
    });

    $exceptions->render(function (\PDOException $e, $request) {
        if (! config('app.debug')) {
            return response()->view('errors.db-down', [], 503);
        }

        return null;
    });

    $exceptions->render(function (\Throwable $e, $request) {
        if (config('app.debug')) {
            return null;
        }

        $msg = strtolower($e->getMessage());

        if (
            str_contains($msg, 'connection refused') ||
            str_contains($msg, 'could not connect') ||
            str_contains($msg, 'sqlstate') ||
            str_contains($msg, 'timeout') ||
            str_contains($msg, 'tenant or user not found') ||
            str_contains($msg, 'server closed the connection') ||
            str_contains($msg, 'no route to host') ||
            str_contains($msg, 'getaddrinfo') ||
            str_contains($msg, 'could not translate host name')
        ) {
            return response()->view('errors.db-down', [], 503);
        }

        return null;
    });

    $exceptions->render(function (\Throwable $e, $request) {
        if (config('app.debug')) {
            return null;
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return response()->view('errors.db-down', [], 503);
        }

        return null;
    });

})
    ->create();