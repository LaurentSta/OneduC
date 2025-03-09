<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Partage $profileData avec toutes les vues si l'utilisateur est connecté
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $profileData = User::find(Auth::id());
                $view->with('profileData', $profileData);
            }
        });
    }
}
