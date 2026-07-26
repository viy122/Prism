<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
        ]);

        // The dev tunnel (Cloudflare quick tunnel) the mobile app talks to forwards
        // requests to this local server as plain HTTP with X-Forwarded-* headers
        // describing the real public scheme/host — trust those so URL generation
        // (see AppServiceProvider::boot()) reflects the tunnel's actual https host
        // instead of always assuming plain local HTTP.
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A CSRF/session mismatch (session lifetime elapsed while a page sat
        // open, or a stale tab's token after logging out elsewhere) otherwise
        // surfaces as a raw framework error page. The only sensible recovery is
        // "log in again" anyway, so just send the user there instead of a 419
        // crash screen — non-JSON requests only; API/AJAX callers still get the
        // normal 419 JSON response so their own error handling is unaffected.
        //
        // Laravel's Handler::prepareException() rewraps TokenMismatchException
        // into a plain Symfony HttpException(419) BEFORE render callbacks run,
        // so a callback typed on TokenMismatchException itself never matches —
        // has to be caught here as a generic HttpException and checked by status.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 && !$request->expectsJson()) {
                return redirect()->route('login')->withErrors([
                    'login' => 'Your session has expired. Please log in again.',
                ]);
            }
        });
    })->create();
