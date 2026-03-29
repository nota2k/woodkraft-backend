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
        // Activer les sessions pour les routes API d'authentification
        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        // Exclure les routes auth du CSRF (SPA Vue)
        $middleware->validateCsrfTokens(except: [
            'api/v1/auth/login',
            'api/v1/auth/logout',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
