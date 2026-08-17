<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.active' => \App\Http\Middleware\EnsureAdminIsActive::class,
            'admin.permission' => \App\Http\Middleware\EnsureAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception,
            \Illuminate\Http\Request $request
        ) {
            if (
                $exception->getStatusCode() === 403
                && $request->is('admin/*')
                && ! $request->expectsJson()
                && $request->headers->has('referer')
            ) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'admin_permission' => $exception->getMessage()
                            ?: 'You do not have permission to perform this action.',
                    ]);
            }

            return null;
        });
    })->create();
