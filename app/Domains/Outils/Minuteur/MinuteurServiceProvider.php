<?php

namespace App\Domains\Outils\Minuteur;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MinuteurServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('outils.minuteur.enabled')) {
            return;
        }

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        });
    }
}
