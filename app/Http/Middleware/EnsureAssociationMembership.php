<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAssociationMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'formateur' && !$user->canUsePlatformWithAssociationPolicy()) {
            if ($request->expectsJson()) {
                abort(403, "La periode d'acces decouverte est terminee.");
            }

            return redirect()
                ->route('adhesion')
                ->with('warning', "Votre periode d'acces decouverte est terminee. Merci de finaliser l'adhesion a l'association Oneduc pour continuer a utiliser l'espace formateur.");
        }

        return $next($request);
    }
}
