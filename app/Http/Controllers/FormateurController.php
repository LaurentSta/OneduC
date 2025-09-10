<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Module;
use App\Models\ScormResult;
use Illuminate\Support\Facades\DB;
use App\Services\CodeGeneratorService;


class FormateurController extends Controller
{
    /* -------------------------------------------------------------------------
     | Tableau de bord Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurDashboard()
    {
        $formateurId = auth()->id();

        // 1) Groupes créés par ce formateur
        $groupCount = \App\Models\Group::where('instructor_id', $formateurId)->count();

        // 2) Modules utilisés par ses groupes (distincts)
        $modulesUsed = \App\Models\Module::whereHas('groups', function ($q) use ($formateurId) {
            $q->where('instructor_id', $formateurId);
        })->distinct('modules.id')->count('modules.id');

        // 3) Total stagiaires rattachés à ses groupes (distincts)
        $learnerCount = \App\Models\User::where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })->distinct('users.id')->count('users.id');

        // 4) Taux de complétion moyen
        // Approche simple et performante: moyenne des derniers scores sur les leçons
        // vues par les stagiaires de ses groupes (fallback 0 si aucun score).
        $avgScore = \App\Models\ScormScore::whereHas('lecture.module.groups', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->whereHas('user', function ($q) use ($formateurId) {
                $q->where('role', 'stagiaire')
                ->whereHas('groupesStagiaire', function ($g) use ($formateurId) {
                    $g->where('instructor_id', $formateurId);
                });
            })
            ->avg('last_score');

        $avgCompletion = $avgScore ? round($avgScore) : 0;

        // Modules enrichis (si la vue en a besoin)
        $modules = Module::withCount('lectures')
            ->with(['groups.users' => function ($q) {
                $q->where('role', 'stagiaire');
            }])
            ->whereHas('groups', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->orWhere('formateur_id', $formateurId)
            ->get()
            ->map(function ($module) {
                $stagiaires = $module->groups->flatMap->users->where('role', 'stagiaire')->unique('id')->values();
                $module->stagiaires = $stagiaires;
                return $module;
            });

        return view('formateur.index', compact(
            'groupCount', 'modulesUsed', 'learnerCount', 'avgCompletion', 'modules'
        ));
    }


    /* -------------------------------------------------------------------------
     | Auth / Déconnexion Formateur
     |-------------------------------------------------------------------------- */
     public function FormateurLogout(Request $request)
     {
        Auth::guard('web')->logout();


         $request->session()->invalidate();
         $request->session()->regenerateToken();

         return redirect('/'); // Tu peux mettre '/login-formateur' si besoin
     }


    /* -------------------------------------------------------------------------
     | Profil Formateur
     |-------------------------------------------------------------------------- */

    // Afficher le profil formateur
    public function FormateurProfile()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('formateur.profile_view', compact('profileData'));
    }

    // Afficher les paramètres de profil formateur
    public function FormateurParametre()
    {
        $id = Auth::user()->id;
        $profileData = User::findOrFail($id);

        return view('formateur.parametre', compact('profileData'));
    }

    // Sauvegarder les modifications du profil formateur
    public function FormateurProfilStore(Request $request)
{
    $id = Auth::user()->id;
    $user = User::findOrFail($id);

    // 🛠️ Corriger ici en mettant les bons champs
    $user->name = $request->name;
    $user->prenom = $request->prenom;
    $user->username = $request->username;
    $user->email = $request->email;
    $user->phone = $request->phone;

    if ($request->file('photo')) {
        $file = $request->file('photo');
        $filename = date('YmdHi') . '_' . $file->getClientOriginalName();
        $file->move(public_path('/upload/formateur_images'), $filename);
        $user->photo = $filename;
    }

    $user->save();

    return redirect()->route('formateur.parametre')->with('message', 'Profil mis à jour avec succès.');
}


    /* -------------------------------------------------------------------------
     | Sécurité Formateur / Changement de mot de passe
     |-------------------------------------------------------------------------- */

    public function showFormateurSecurite()
    {
        $user = Auth::user();

        return view('formateur.securite', compact('user'));
    }

    public function FormateurSecurite(Request $request)
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
    // Afficher la liste de SES stagiaires
    public function indexStagiaires(Request $request)
    {
        $query = User::where('role', 'stagiaire');

        // Filtrer les stagiaires liés au formateur
        $query->where(function ($q) {
            $q->where('formateur_id', auth()->id())
            ->orWhereHas('groupesStagiaire', function ($gq) {
                $gq->where('instructor_id', auth()->id());
            });
        });

        // Recherche texte : prénom, nom, email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        // ⚠️ Précharger les groupes (évite problème N+1)
        $stagiaires = $query
            ->with('groupesStagiaire') // charge les groupes associés
            ->orderBy('name')
            ->paginate(10);

        return view('formateur.backend.stagiaires.all_stagiaires', compact('stagiaires'));
    }



    // Afficher le formulaire de création de stagiaire
    public function createStagiaire()
    {
        return view('formateur.backend.stagiaires.add_stagiaire');
    }

    // Stocker le stagiaire créé
    public function storeStagiaire(Request $request)
{
    $request->validate([
        'prenom' => ['required', 'string', 'max:255'],
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:6', 'confirmed'],
    ]);

    User::create([
        'prenom' => $request->prenom,
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'stagiaire',
        'formateur_id' => auth()->id(),
        'code_acces' => CodeGeneratorService::generateUniqueAccessCode(),
    ]);

    return redirect()->route('formateur.stagiaires.index')->with('success', 'Stagiaire créé avec succès ✅');
}



    public function editStagiaire($id)
        {
            $stagiaire = User::where('role', 'stagiaire')
    ->where(function ($query) {
        $query->where('formateur_id', auth()->id())
              ->orWhereHas('groupesStagiaire', function ($q) {
                  $q->where('instructor_id', auth()->id());
              });
    })
    ->findOrFail($id);

            return view('formateur.backend.stagiaires.edit_stagiaire', compact('stagiaire'));
        }
    
        public function updateStagiaire(Request $request, $id)
        {
            $stagiaire = User::where('role', 'stagiaire')
                ->where(function ($query) {
                    $query->where('formateur_id', auth()->id())
                        ->orWhereHas('groupesStagiaire', function ($q) {
                            $q->where('instructor_id', auth()->id());
                        });
                })
                ->findOrFail($id);

            $request->validate([
                'prenom' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $stagiaire->id,
                'password' => 'nullable|string|min:8',
            ]);

            $stagiaire->prenom = $request->prenom;
            $stagiaire->name = $request->name;
            $stagiaire->email = $request->email;

            if ($request->filled('password')) {
                $stagiaire->password = Hash::make($request->password);
            }

            $stagiaire->save();

            return redirect()->route('formateur.stagiaires.index')->with('success', 'Stagiaire modifié avec succès ✅');
        }

        // Supprimer un stagiaire





    public function destroyStagiaire($id)
        {
            $stagiaire = User::where('role', 'stagiaire')
    ->where(function ($query) {
        $query->where('formateur_id', auth()->id())
              ->orWhereHas('groupesStagiaire', function ($q) {
                  $q->where('instructor_id', auth()->id());
              });
    })
    ->findOrFail($id);

            $stagiaire->delete();

            return redirect()->route('formateur.stagiaires.index')->with('success', 'Stagiaire supprimé avec succès 🗑️');
        }

        // Afficher le formulaire d’inscription formateur
        public function showRegistrationForm()
        {
            return view('formateur.auth.register');
        }

        // Traiter l’inscription formateur
        public function register(Request $request)
        {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $formateur = User::create([
                'name' => $request->name,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address ?? null,
                'societe' => $request->societe ?? null,
                'password' => Hash::make($request->password),
                'role' => 'formateur',
            ]);

            Auth::login($formateur);

            return redirect()->route('formateur.dashboard')->with('success', 'Bienvenue sur Oneduc !');
        }

        public function mesModules()
            {
                $formateurId = auth()->id();

                $modules = \App\Models\Module::with(['groups' => function ($query) use ($formateurId) {
                    $query->where('instructor_id', $formateurId)
                        ->with(['users' => function ($q) {
                            $q->where('role', 'stagiaire');
                        }]);
                }, 'lectures'])->get();

                return view('formateur.formations.index', compact('modules'));
            }

        public function moduleDetail(\App\Models\Module $module)
            {
                $formateurId = auth()->id();

                $module->load([
                    'formateur',                       // si relation existe
                    'sections.lectures',               // structure pédagogique
                    'groups' => function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId)
                        ->with(['users' => fn($u) => $u->where('role', 'stagiaire')]);
                    },
                ]);

                $totalSections   = $module->sections->count();
                $totalLectures   = $module->sections->flatMap->lectures->count();
                $totalSlides     = $module->sections->flatMap->lectures->sum('slide_count');
                $totalQuestions  = $module->sections->flatMap->lectures->sum('question_count');
                $groupCount      = $module->groups->count();
                $stagiaires      = $module->groups->flatMap(fn($g) => $g->users)->unique('id')->values();
                $stagiaireCount  = $stagiaires->count();

                return view('formateur.formations.formateur_module_detail', compact(
                    'module','totalSections','totalLectures','totalSlides','totalQuestions','groupCount','stagiaires','stagiaireCount'
                ));
            }
        public function preview(Module $module)
            {
                $module->load('sections.lectures');
                $firstSection = $module->sections->first();
                $firstLecture = $firstSection?->lectures->first();

                if (!$firstSection || !$firstLecture) {
                    return back()->with('error', 'Aucune leçon disponible à tester.');
                }

                // Ici tu peux réutiliser la même vue que pour le stagiaire
                return redirect()->route('stagiaire.module.lecture', [
                    'module' => $module->id,
                    'section' => $firstSection->id,
                    'lesson' => $firstLecture->id
                ]);
            }



}
