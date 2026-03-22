<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Module;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ScormResult;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;

class AdminController extends Controller
{
    /**
     * Affiche la page de connexion, sauf si l'utilisateur est déjà connecté.
     */
    public function Login()
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectUserByRole());
        }

        return view('admin.admin_dashboard');
    }
    public function processLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            if (!Auth::user()->status) {
                Auth::logout();
                return redirect()->route('connexion')->withErrors(['status' => 'Votre compte est désactivé.']);
            }

            $request->session()->regenerate();

            // C'est ici que tu rediriges après connexion réussie
            return redirect()->route($this->redirectUserByRole());
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ]);
    }
    public function AdminParametre()
{
    $profileData = Auth::user();
    return view('admin.admin_parametre', compact('profileData'));
}

public function AdminProfilStore(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'firstName'    => ['nullable','string','max:255'],
        'lastName'     => ['nullable','string','max:255'],
        'email'        => ['required','email','max:255'],
        'phoneNumber'  => ['nullable','string','max:50'],
        'photo'        => ['nullable','image','mimes:jpg,jpeg,png','max:800'],
    ]);

    // Attention : chez toi "firstName" mappe sur username (historique) -> à clarifier.
    // Ici je conserve ton mapping actuel :
    $user->username = $validated['firstName'] ?? $user->username;
    $user->name     = $validated['lastName']  ?? $user->name;
    $user->email    = $validated['email'];
    $user->phone    = $validated['phoneNumber'] ?? null;

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = time().'_'.$file->getClientOriginalName();
        $file->move(public_path('upload/admin_images'), $filename);
        $user->photo = $filename;
    }

    $user->save();

    return back()->with('message', 'Modifications enregistrées.');
}

public function AdminSecurite()
{
    return view('admin.admin_securite');
}

public function AdminSecuriteUpdate(Request $request)
{
    $request->validate([
        'currentPassword' => ['required', 'current_password'], // règle Laravel
        'newPassword' => ['required', 'string', 'min:12', 'confirmed'],
    ]);

    $user = Auth::user();
    $user->password = Hash::make($request->newPassword);
    $user->save();

    return back()->with('message', 'Mot de passe mis à jour.');
}
    private function redirectUserByRole()
    {
        return match (Auth::user()->role) {
            'admin'     => 'admin.dashboard',
            'formateur' => 'formateur.dashboard',
            'observateur' => 'observateur.dashboard',
            'stagiaire' => 'stagiaire.dashboard',
            default     => 'dashboard',
        };
    }


    /* -------------------------------------------------------------------------
     | ADMIN Dashboard, Logout & Profil
     |-------------------------------------------------------------------------- */

     public function AdminDashboard()
    {
        $moduleCount = Module::count();
        $categoryCount = Category::count();
        $subCategoryCount = SubCategory::count();
        $groupCount = \App\Models\Group::count(); // ✅ Nouveau compteur
        $formateurCount = User::where('role', 'formateur')->count(); // ✅
        $stagiaireCount = User::where('role', 'stagiaire')->count(); // ✅
        $groupCount = \App\Models\Group::count(); // ✅ Nouveau compteur
        $sectionCount = \App\Models\ModuleSection::count();
        $lectureCount = \App\Models\ModuleLecture::count();
        // Synthèse SCORM par utilisateur et module
        $scormSummaries = ScormResult::with(['user', 'lecture'])
            ->selectRaw('user_id, lecture_id, MAX(updated_at) as last_update')
            ->groupBy('user_id', 'lecture_id')
            ->get()
            ->map(function ($row) {
                $score = ScormResult::where('user_id', $row->user_id)
                    ->where('lecture_id', $row->lecture_id)
                    ->where('scorm_key', 'cmi.core.score.raw')
                    ->orderByDesc('updated_at')
                    ->first();

                $status = ScormResult::where('user_id', $row->user_id)
                    ->where('lecture_id', $row->lecture_id)
                    ->where('scorm_key', 'cmi.core.lesson_status')
                    ->orderByDesc('updated_at')
                    ->first();

                return [
                    'user' => $row->user?->username ?? 'N/A',
                    'module' => $row->lecture?->lecture_title ?? 'N/A',
                    'score' => $score?->scorm_value ?? '-',
                    'status' => $status?->scorm_value ?? '-',
                    'date' => $row->last_update,
                ];
            });

            return view('admin.index', compact(
                'moduleCount',
                'categoryCount',
                'subCategoryCount',
                'formateurCount',
                'stagiaireCount',
                'groupCount',
                'sectionCount',
                'lectureCount',
                'scormSummaries'
            ));

    }
    public function AdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/connexion');
    }

    public function AdminProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('admin.admin_profile_view', compact('profileData'));
    }




/* -------------------------------------------------------------------------
     | Liste et statut des formateurs
     |-------------------------------------------------------------------------- */

     public function AllFormateur()
    {
        $allFormateur = User::where('role', 'formateur')
            ->withCount('stagiaires')
            ->with('stagiaires') // 🔥 On charge la liste aussi
            ->latest()
            ->get();

        return view('admin.backend.formateur.all_formateur', compact('allFormateur'));
    }


    public function UpdateUserStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_checked' => 'required|boolean',
        ]);

        $user = User::findOrFail($request->user_id);

        // ✅ Autoriser uniquement les rôles modifiables : formateur OU stagiaire
        if (!in_array($user->role, ['formateur', 'stagiaire', 'observateur'])) {
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée.'
            ], 403);
        }

        $user->status = $request->is_checked;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Le statut de l’utilisateur a été mis à jour avec succès.',
            'new_status' => $user->status ? 'Actif' : 'Inactif'
        ]);
    }
    public function DestroyFormateur(\App\Models\User $user)
    {
        if ($user->id === auth()->id())          return $this->deny('Vous ne pouvez pas vous supprimer.');
        if ($user->role !== 'formateur')         return $this->deny('Action non autorisée.');
        if (!\Schema::hasColumn('users','deleted_at')) return $this->deny('Soft delete indisponible.');

        $user->delete(); // soft delete
        return $this->ok('Formateur supprimé.', $user->id);
    }

    public function DestroyStagiaire(\App\Models\User $user)
    {
        if ($user->id === auth()->id())          return $this->deny('Vous ne pouvez pas vous supprimer.');
        if ($user->role !== 'stagiaire')         return $this->deny('Action non autorisée.');
        if (!\Schema::hasColumn('users','deleted_at')) return $this->deny('Soft delete indisponible.');

        $user->delete(); // soft delete
        return $this->ok('Stagiaire supprimé.', $user->id);
    }

    public function DestroyObservateur(\App\Models\User $user)
    {
        if ($user->id === auth()->id())          return $this->deny('Vous ne pouvez pas vous supprimer.');
        if ($user->role !== 'observateur')       return $this->deny('Action non autorisée.');
        if (!\Schema::hasColumn('users','deleted_at')) return $this->deny('Soft delete indisponible.');

        \Illuminate\Support\Facades\DB::table('group_user')
            ->where('user_id', $user->id)
            ->where('role_in_group', 'observateur')
            ->delete();

        $user->delete();

        return $this->ok('Observateur supprimé.', $user->id);
    }

    private function ok($msg, $id){
        if (request()->ajax()) return response()->json(['success'=>true,'message'=>$msg,'id'=>$id]);
        return back()->with('success',$msg);
    }
    private function deny($msg){
        if (request()->ajax()) return response()->json(['success'=>false,'message'=>$msg], 422);
        return back()->with('error',$msg);
    }

    /* -------------------------------------------------------------------------
     | FORMATEUR : Demande et validation
     |-------------------------------------------------------------------------- */

    // Formulaire inscription formateur
    public function BecomeFormateur()
    {
        return view('frontend.formateur.reg_formateur');
    }

    // Traite l'inscription et enregistre un nouveau formateur
    public function FormateurRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:255',
            'societe' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'societe' => $request->societe,
            'address' => $request->address,
            'role' => 'formateur',
            'status' => false, // En attente de validation par l'admin
        ]);


        return redirect()->route('devenir.formateur')->with('success', 'Inscription réussie ! En attente de validation par un administrateur.');
    }
    public function AllStagiaires()
    {
        $allStagiaires = \App\Models\User::where('role', 'stagiaire')
            ->with('formateur') // utile si tu veux afficher le formateur assigné
            ->latest()
            ->get();

        return view('admin.backend.stagiaire.all_stagiaire', compact('allStagiaires'));
    }




}
