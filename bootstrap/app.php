<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

    // 1) Errores claros de conexión / DB caída
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

    // 2) Fallback: si el mensaje "huele" a DB caída
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

    // 3) (Opcional pero recomendado en tu caso) Catch-all SOLO para /admin
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