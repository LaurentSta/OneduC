<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InstructorController extends Controller
{
    public function InstructorDashboard(){
        return view('instructor.index');
    }
    public function InstructorLogout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    public function InstructorProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('instructor.instructor_profile_view', compact('profileData'));
    }
    public function InstructorParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('instructor.instructor_parametre', compact('profileData'));
    }
    public function InstructorProfilStore(Request $request)
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
            $file->move(public_path('/upload/instructor_images'), $filename);
            $user->photo = $filename;
        }

        $user->save();

        return redirect()->route('instructor.parametre')->with('message', 'Profil mis à jour avec succès.');
    }
    public function showInstructorSecurite()
    {
        $user = Auth::user(); // Récupérer les données de l'utilisateur
        return view('instructor.instructor_securite', compact('user')); // Afficher la vue
    }

    public function InstructorSecurite(Request $request)
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
