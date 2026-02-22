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
        
        // 👇 AJOUTE CETTE PARTIE 👇
        $middleware->alias([
            'role' => \App\Http\Middleware\Role::class, // (Tu dois sûrement déjà avoir celui-ci)
            'track.time' => \App\Http\Middleware\TrackSessionTime::class, // (Et celui-ci vu tes logs)
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class, // ✅ C'est celui-ci qui manque !
            'admin.activity' => \App\Http\Middleware\RecordAdminActivity::class,
        ]);
        // 👆 FIN DE L'AJOUT 👆

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
