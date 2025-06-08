<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  mixed  ...$roles  Les rôles autorisés
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        $errorMessage = 'Accès refusé à cette section.';

        // 🔐 Vérifie auth, rôle ET statut actif
        if (!$user || !in_array($user->role, $roles) || !$user->status) {

            if ($user) {
                // L'utilisateur est connecté mais désactivé ou non autorisé
                Auth::logout();
                return redirect('/connexion')->with('error', 'Votre compte a été désactivé ou vous n’avez pas les droits d’accès.');
            }

            return redirect('/connexion')->with('error', 'Vous devez être connecté.');
        }

        return $next($request);
    }


    /**
     * Redirection en fonction du rôle de l'utilisateur.
     *
     * @param string $role
     * @param string $errorMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectByRole(string $role, string $errorMessage)
    {
        return match ($role) {
            'admin'     => redirect()->route('admin.dashboard')->with('error', $errorMessage),
            'formateur' => redirect()->route('formateur.dashboard')->with('error', $errorMessage),
            'stagiaire' => redirect()->route('stagiaire.dashboard')->with('error', $errorMessage),
            default     => redirect('/')->with('error', $errorMessage),
        };
    }
}
