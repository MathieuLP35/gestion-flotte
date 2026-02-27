<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'block.login' => \App\Http\Middleware\BlockLoginAttempts::class,
            'admin' => \App\Http\Middleware\EnsureUserHasAdminAccess::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if (! app()->environment(['local', 'testing']) || in_array($response->getStatusCode(), [403, 404, 500, 503])) {
                // Si on a un code d'erreur qu'on a customisé ou en prod
                if (in_array($response->getStatusCode(), [403, 404, 500, 503])) {
                    return \Inertia\Inertia::render('Error', ['status' => $response->getStatusCode()])
                        ->toResponse($request)
                        ->setStatusCode($response->getStatusCode());
                } elseif ($response->getStatusCode() === 419) {
                    return back()->with([
                        'error' => 'La page a expiré, veuillez réessayer.',
                    ]);
                }
            }

            return $response;
        });
    })->create();
