<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Module;
use App\Models\ScormResult;
use App\Services\CodeGeneratorService;
use App\Mail\FormateurWelcome;
use App\Mail\NewFormateurNotification;

class FormateurController extends Controller
{
    /* -------------------------------------------------------------------------
     | Tableau de bord Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurDashboard()
    {
        $formateurId = auth()->id();

        $groupCount = \App\Models\Group::where('instructor_id', $formateurId)->count();

        $modulesUsed = \App\Models\Module::whereHas('groups', function ($q) use ($formateurId) {
            $q->where('instructor_id', $formateurId);
        })->distinct('modules.id')->count('modules.id');

        $learnerCount = \App\Models\User::where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })->distinct('users.id')->count('users.id');

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
        return redirect('/');
    }

    /* -------------------------------------------------------------------------
     | Profil Formateur
     |-------------------------------------------------------------------------- */
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
     | Sécurité Formateur
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

        $request->validate([
            'currentPassword' => 'required',
            'newPassword' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->currentPassword, $user->password)) {
            return back()->with('error', 'Le mot de passe actuel est incorrect.');
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return back()->with('message', 'Votre mot de passe a été modifié avec succès.');
    }

    /* -------------------------------------------------------------------------
     | Stagiaires
     |-------------------------------------------------------------------------- */
    public function indexStagiaires(Request $request)
    {
        $query = User::where('role', 'stagiaire');

        $query->where(function ($q) {
            $q->where('formateur_id', auth()->id())
              ->orWhereHas('groupesStagiaire', function ($gq) {
                  $gq->where('instructor_id', auth()->id());
              });
        });

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%$search%")
                  ->orWhere('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $stagiaires = $query
            ->with('groupesStagiaire')
            ->orderBy('name')
            ->paginate(10);

        return view('formateur.backend.stagiaires.all_stagiaires', compact('stagiaires'));
    }

    public function createStagiaire()
    {
        return view('formateur.backend.stagiaires.add_stagiaire');
    }

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

    /* -------------------------------------------------------------------------
     | Inscription formateur
     |-------------------------------------------------------------------------- */
    public function showRegistrationForm()
    {
        return view('formateur.auth.register');
    }

    public function register(Request $request)
    {
        // Honeypot
        if ($request->filled('website')) {
            return back()->withErrors(['form' => 'Envoi invalide.'])->withInput();
        }

        // Validation + captcha
        $validated = $request->validate([
            'prenom'   => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone'    => 'nullable|string|max:30',
            'societe'  => 'nullable|string|max:150',
            'address'  => 'nullable|string|max:255',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // Création du formateur
        $formateur = User::create([
            'prenom'  => $validated['prenom'],
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'phone'   => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'societe' => $validated['societe'] ?? null,
            'password'=> Hash::make($validated['password']),
            'role'    => 'formateur',
        ]);

        // Connexion
        Auth::login($formateur);

        // Mails
        Mail::to($formateur->email)->send(new FormateurWelcome([
            'prenom' => $formateur->prenom,
            'nom'    => $formateur->name,
            'email'  => $formateur->email,
        ]));

        Mail::to('contact@oneduc.fr')->send(new NewFormateurNotification([
            'prenom' => $formateur->prenom,
            'nom'    => $formateur->name,
            'email'  => $formateur->email,
            'phone'  => $formateur->phone,
            'societe'=> $formateur->societe,
        ]));

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
            'formateur',
            'sections.lectures',
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

        return redirect()->route('stagiaire.module.lecture', [
            'module' => $module->id,
            'section' => $firstSection->id,
            'lesson' => $firstLecture->id
        ]);
    }
}
