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
        $middleware->alias([
            'role' => \App\Http\Middleware\Role::class,
            'track.time' => \App\Http\Middleware\TrackSessionTime::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'admin.activity' => \App\Http\Middleware\RecordAdminActivity::class,
            'association.member' => \App\Http\Middleware\EnsureAssociationMembership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
