<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{


    /**
     * Affiche la page de connexion.
     */
    public function create(): View|RedirectResponse
{
    if (Auth::check()) {

        $role = Auth::user()->role;

        $url = match ($role) {
            'admin'     => route('admin.dashboard'),
            'formateur' => route('formateur.dashboard'),
            'observateur' => route('observateur.dashboard'),
            'stagiaire' => route('stagiaire.dashboard'),
            default     => route('index') // la route standard de Laravel
        };

        return redirect($url);
    }

    return view('frontend.contenu.login');
}


    /**
     * Traite la tentative de connexion.
     */
    public function store(LoginRequest $request): RedirectResponse
    {


        $request->authenticate();


        $request->session()->regenerate();

        $notification = [
            'message' => 'Connexion réussie',
            'alert-type' => 'success'
        ];

        // Redirection selon le rôle
        $role = $request->user()->role;

        $url = match ($role) {
            'admin'     => route('admin.dashboard'),
            'formateur' => route('formateur.dashboard'),
            'observateur' => route('observateur.dashboard'),
            'stagiaire' => route('stagiaire.dashboard'),
            default     => route('index'),
        };


        return redirect()->intended($url)->with($notification);
    }
    public function destroy(Request $request): RedirectResponse
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/')->with('message', 'Déconnexion réussie.');
}


}
