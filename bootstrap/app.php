<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
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

if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
    $storagePath = '/tmp/greystone-storage';
    $databasePath = '/tmp/greystone.sqlite';

    foreach ([
        'app/private',
        'app/public',
        'framework/cache/data',
        'framework/sessions',
        'framework/views',
        'logs',
    ] as $directory) {
        if (! is_dir($path = $storagePath.'/'.$directory)) {
            mkdir($path, 0755, true);
        }
    }

    if (! is_file($databasePath) && is_file(dirname(__DIR__).'/database/database.sqlite')) {
        copy(dirname(__DIR__).'/database/database.sqlite', $databasePath);
    }

    $app->useStoragePath($storagePath);
}

return $app;
