<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Mail\FormateurWelcome;
use App\Mail\NewFormateurNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormateurProfileController extends Controller
{
    public function FormateurLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function FormateurProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('formateur.profile_view', compact('profileData'));
    }

    public function FormateurParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('formateur.parametre', compact('profileData'));
    }

    public function FormateurProfilStore(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $request->name;
        $user->prenom = $request->prenom;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = Str::uuid().'.'.$file->extension();
            $file->move(public_path('/upload/formateur_images'), $filename);
            $user->photo = $filename;
        }

        $user->save();

        return redirect()
            ->route('formateur.parametre')
            ->with('message', 'Profil mis à jour avec succès.');
    }

    public function showFormateurSecurite()
    {
        $user = Auth::user();

        return view('formateur.securite', compact('user'));
    }

    public function FormateurSecurite(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::findOrFail($id);

        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($request->currentPassword, $user->password)) {
            return back()->withErrors([
                'currentPassword' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }

    public function destroyOwnAccount(Request $request)
    {
        $user = Auth::user();

        abort_unless($user && $user->role === 'formateur', 403);

        $validator = Validator::make(
            $request->all(),
            ['password' => ['required', 'string']],
            ['password.required' => 'Le mot de passe actuel est requis pour confirmer la suppression.']
        );

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'accountDeletion')
                ->with('openDeleteAccountModal', true);
        }

        if (! Hash::check((string) $request->input('password'), (string) $user->password)) {
            return back()
                ->withErrors(['password' => 'Le mot de passe actuel est incorrect.'], 'accountDeletion')
                ->with('openDeleteAccountModal', true);
        }

        try {
            $user->delete();
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors([
                    'password' => 'La suppression du compte a échoué. Merci de réessayer ou de contacter le support.',
                ], 'accountDeletion')
                ->with('openDeleteAccountModal', true);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('index')
            ->with('success', 'Votre compte formateur a été supprimé définitivement.');
    }

    public function showRegistrationForm()
    {
        return view('formateur.auth.register');
    }

    public function register(Request $request)
    {
        if ($request->filled('website')) {
            return back()->withErrors(['form' => 'Envoi invalide.'])->withInput();
        }

        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30',
            'societe' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:255',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        $formateur = User::create([
            'prenom' => $validated['prenom'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'societe' => $validated['societe'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'formateur',
            'adhesion_status' => 'pending',
        ]);

        Auth::login($formateur);

        Mail::to($formateur->email)->send(new FormateurWelcome([
            'prenom' => $formateur->prenom,
            'nom' => $formateur->name,
            'email' => $formateur->email,
        ]));

        Mail::to('contact@oneduc.fr')->send(new NewFormateurNotification([
            'prenom' => $formateur->prenom,
            'nom' => $formateur->name,
            'email' => $formateur->email,
            'phone' => $formateur->phone,
            'societe' => $formateur->societe,
        ]));

        return redirect()
            ->route('formateur.dashboard')
            ->with('success', "Bienvenue sur Oneduc ! Votre espace formateur est ouvert. L'adhésion à l'association vous sera proposée pour soutenir le projet.");
    }
}
