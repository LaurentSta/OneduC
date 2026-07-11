<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class FirstLoginController extends Controller
{
    /**
     * Affiche le formulaire de changement de mot de passe.
     */
    public function show()
    {
        // Sécurité : Si l'utilisateur a déjà changé son mot de passe, on le renvoie au dashboard
        if (Auth::user()->password_changed_at !== null) {
            return redirect()->route('stagiaire.dashboard');
        }

        return view('auth.first-login');
    }

    /**
     * Traite le changement de mot de passe.
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(), // C'est ici qu'on débloque l'utilisateur
        ]);

        return redirect()->intended(route('stagiaire.dashboard'))
            ->with('success', 'Votre mot de passe a été mis à jour avec succès. Bienvenue !');
    }
}