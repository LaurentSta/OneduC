<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si l'utilisateur est connecté ET qu'il n'a pas de date de changement de mot de passe
        if ($user && is_null($user->password_changed_at)) {
            
            // On définit les routes autorisées pour éviter une boucle infinie
            // (La page pour changer le mot de passe, et la déconnexion)
            $allowedRoutes = [
                'stagiaire.password.init',       // La page du formulaire (GET)
                'stagiaire.password.init.store', // L'action de sauvegarde (POST)
                'logout',                        // Toujours permettre de sortir !
            ];

            // Si la route actuelle n'est pas dans la liste blanche, on redirige
            if (!in_array(Route::currentRouteName(), $allowedRoutes)) {
                return redirect()->route('stagiaire.password.init');
            }
        }

        return $next($request);
    }
}