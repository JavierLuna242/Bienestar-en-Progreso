<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Detrás del túnel de Cloudflare: respeta X-Forwarded-Proto para generar URLs https.
        // Es seguro porque la app solo escucha en localhost.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions()
    ->create();
