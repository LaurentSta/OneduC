<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Vérifie si l'utilisateur est connecté et a l'un des rôles autorisés
        if (!$user || !in_array($user->role, $roles)) {
            return redirect('/')->with('error', 'Accès refusé.');
        }

        return $next($request);
    }
}
