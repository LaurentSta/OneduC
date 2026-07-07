<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Forcer https très tôt
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        RateLimiter::for('contact', function (Request $request) {
            return [
                // 3 envois / minute par IP
                Limit::perMinute(3)->by($request->ip())
                    ->response(function () {
                        // Message lisible + statut 429
                        return back()
                            ->withErrors(['global' => 'Trop de tentatives. Réessayez dans une minute.'])
                            ->withInput()
                            ->setStatusCode(429);
                    }),
                // 20 envois / heure par IP
                Limit::perHour(20)->by($request->ip()),
            ];
        });
        RateLimiter::for('connexion-code', function (Request $request) {
            return [
                // 10 tentatives / minute par IP
                Limit::perMinute(10)->by($request->ip())
                    ->response(function () {
                        return back()
                            ->withErrors(['code_acces' => 'Trop de tentatives. Réessayez dans une minute.'])
                            ->withInput()
                            ->setStatusCode(429);
                    }),
            ];
        });
        // Partage de données vues
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $profileData = User::find(Auth::id());
                $view->with('profileData', $profileData);
            }
        });
    }
}
