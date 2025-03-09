<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function Index(){
        return view('frontend.index');
    }
    public function Register()
    {
        return view('frontend.dashboard.register'); // Vérifie que ce fichier existe
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
    public function UserLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    public function UserProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('frontend.dashboard.user_profile_view', compact('profileData'));
    }
    public function UserParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('frontend.dashboard.user_parametre', compact('profileData'));
    }
    public function UserProfilStore(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::find($id);

        $user->name = $request->lastName;
        $user->username = $request->firstName;
        $user->email = $request->email;
        $user->phone = $request->phoneNumber;

        // Gestion de l'upload de l'image
        if ($request->file('photo')) {
            $file = $request->file('photo');
            $filename = date('YmdHi') . $file->getClientOriginalName();
            $file->move(public_path('/upload/user_images'), $filename);
            $user->photo = $filename;
        }

        $user->save();

        return redirect()->route('user.parametre')->with('message', 'Profil mis à jour avec succès.');
    }
    public function showUserSecurite()
    {
        $user = Auth::user(); // Récupérer les données de l'utilisateur
        return view('frontend.dashboard.user_securite', compact('user')); // Afficher la vue
    }

    public function UserSecurite(Request $request)
    {
        $id = Auth::user()->id;
        $user = User::find($id); // Récupère l'utilisateur depuis la base de données

        // Validation des champs
        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        // Vérifier si le mot de passe actuel est correct
        if (!Hash::check($request->currentPassword, $user->password)) {
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->newPassword);
        $user->save(); // Sauvegarde dans la base de données

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }
}
