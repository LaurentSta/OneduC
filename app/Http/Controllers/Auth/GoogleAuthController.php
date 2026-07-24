<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\FormateurWelcome;
use App\Mail\NewFormateurNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = $this->registerFormateur($googleUser);
        }

        if (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        if (is_null($user->password_changed_at)) {
            $user->update(['password_changed_at' => now()]);
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended($this->dashboardRoute($user->role))
            ->with('success', 'Connexion réussie');
    }

    private function registerFormateur(\Laravel\Socialite\Two\User $googleUser): User
    {
        $formateur = User::create([
            'prenom' => $googleUser->user['given_name'] ?? $googleUser->getName(),
            'name' => $googleUser->user['family_name'] ?? '',
            'email' => $googleUser->getEmail(),
            'password' => Hash::make(Str::random(40)),
            'google_id' => $googleUser->getId(),
            'role' => 'formateur',
            'adhesion_status' => 'pending',
            'password_changed_at' => now(),
        ]);

        Mail::to($formateur->email)->send(new FormateurWelcome([
            'prenom' => $formateur->prenom,
            'nom' => $formateur->name,
            'email' => $formateur->email,
        ]));

        Mail::to('contact@oneduc.fr')->send(new NewFormateurNotification([
            'prenom' => $formateur->prenom,
            'nom' => $formateur->name,
            'email' => $formateur->email,
            'phone' => null,
            'societe' => null,
        ]));

        return $formateur;
    }

    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'formateur' => route('formateur.dashboard'),
            'observateur' => route('observateur.dashboard'),
            'stagiaire' => route('stagiaire.dashboard'),
            default => route('index'),
        };
    }
}
