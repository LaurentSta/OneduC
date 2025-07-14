<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ScormInteraction;
use App\Models\LessonFeedback;
use App\Models\VideoSegmentTracking;
use App\Models\ScormEvaluationScore;

class UserController extends Controller
{
    /* -------------------------------------------------------------------------
     | Pages publiques
     |-------------------------------------------------------------------------- */
    public function Index()
    {
        return view('frontend.index');
    }

    public function Projet()
    {
        return view('frontend.contenu.projet');
    }

    public function Association()
    {
        return view('frontend.contenu.association');
    }

    public function Adhesion()
    {
        return view('frontend.contenu.adhesion');
    }



    /* -------------------------------------------------------------------------
     | Auth & Session
     |-------------------------------------------------------------------------- */
    public function UserLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /* -------------------------------------------------------------------------
     | Stagiaire Dashboard & Profil
     |-------------------------------------------------------------------------- */

    
    // Vue du profil stagiaire
    public function UserProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('stagiaire.stagiaire_profile_view', compact('profileData'));
    }

    // Paramètres du compte stagiaire
    public function UserParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('stagiaire.stagiaire_parametre', compact('profileData'));
    }


    public function UserProfilStore(Request $request)
{
    $id = Auth::id();
    $user = User::findOrFail($id);

    // ✅ Validation sécurisée
    $request->validate([
        'name' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phoneNumber' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'photo' => 'nullable|image|max:2048',
    ]);

    // ✅ Mise à jour des champs
    if ($request->has('name')) {
    $user->name = $request->name;
    }
    if ($request->has('prenom')) {
        $user->prenom = $request->prenom;
    }
    if ($request->has('email')) {
        $user->email = $request->email;
    }
    if ($request->has('phoneNumber')) {
        $user->phone = $request->phoneNumber;
    }
    if ($request->has('address')) {
        $user->address = $request->address;
    }


    // ✅ Upload image si présente
    if ($request->file('photo')) {
        $file = $request->file('photo');
        $filename = date('YmdHi') . '_' . $file->getClientOriginalName();
        $file->move(public_path('/upload/user_images'), $filename);
        $user->photo = $filename;
    }

    $user->save();

    return redirect()->back()->with('message', 'Profil mis à jour avec succès.');
}


    /* -------------------------------------------------------------------------
     | Sécurité / Mot de passe
     |-------------------------------------------------------------------------- */

    public function showUserSecurite()
    {
        $user = Auth::user();

        return view('stagiaire.stagiaire_securite', compact('user'));
    }

    public function UserSecurite(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::findOrFail($id);

        // Validation des champs
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        // Vérification du mot de passe actuel
        if (!Hash::check($request->currentPassword, $user->password)) {
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        // Mise à jour du mot de passe
        $user->password = Hash::make($request->newPassword);
        $user->save();

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }

    public function showCodeLoginForm()
    {
        return view('frontend.contenu.code_login');
    }
    public function loginByCode(Request $request)
    {
        $request->validate([
            'code_acces' => 'required|string|size:6',
        ]);

        $user = User::where('code_acces', strtoupper($request->code_acces))->first();

        if ($user && $user->role === 'stagiaire') {
            Auth::login($user);
            return redirect()->route('stagiaire.dashboard')->with('success', 'Bienvenue !');
        }

        return back()->withErrors([
            'code_acces' => 'Code d’accès invalide ou utilisateur non autorisé.',
        ]);
    }





}
