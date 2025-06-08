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
    ->withMiddleware(function (Middleware $middleware) {
        // Déclaration des middlewares aliasés
        $middleware->alias([
            'role' => \App\Http\Middleware\Role::class,
            'track.time' => \App\Http\Middleware\TrackSessionTime::class, // ✅ alias proprement défini
        ]);

        // ❌ NE PAS utiliser append() ici pour ce middleware, car il empêche handle() d'être exécuté
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
