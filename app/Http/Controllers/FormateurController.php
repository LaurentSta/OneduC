<?php

namespace App\Http\Controllers;

// /home/laurents/Oneduc_Dev/app/Http/Controllers/FormateurController.php

use App\Mail\FormateurWelcome;
use App\Mail\NewFormateurNotification;
use App\Models\Group;
use App\Models\Module;
use App\Models\ScormScore;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\ModuleLecture;


class FormateurController extends Controller
{
    /* -------------------------------------------------------------------------
     | Tableau de bord Formateur
     |-------------------------------------------------------------------------- */
    public function FormateurDashboard()
    {
        $formateurId = auth()->id();

        $groupCount = Group::query()
            ->where('instructor_id', $formateurId)
            ->count();

        $modulesUsed = Module::query()
            ->whereHas('groups', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->distinct('modules.id')
            ->count('modules.id');

        $learnerCount = User::query()
            ->where('role', 'stagiaire')
            ->whereHas('groupesStagiaire', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->distinct('users.id')
            ->count('users.id');

        // Score moyen (ce n'est pas un taux d'achèvement)
        $avgScore = ScormScore::query()
            ->whereHas('lecture.module.groups', function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId);
            })
            ->whereHas('user', function ($q) {
                $q->where('role', 'stagiaire');
            })
            ->avg('last_score');

        $avgScoreRounded = $avgScore ? (int) round($avgScore) : 0;
        $avgCompletion = $avgScoreRounded;

        // Groupes affichés dans la section "Suivi par groupes" du dashboard
        $groupesDashboard = Group::query()
            ->where('instructor_id', $formateurId)
            ->withCount([
                'students as stagiaires_count' => function ($q) {
                    $q->where('role', 'stagiaire');
                },
                'modules as modules_count',
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'created_at']);

        $groupIds = $groupesDashboard->pluck('id')->all();

        $lastActivityByGroup = collect();
        $scoresByGroup = collect();

        if (!empty($groupIds)) {
            $lastActivityByGroup = DB::table('progressions')
                ->join('group_user', 'group_user.user_id', '=', 'progressions.user_id')
                ->whereIn('group_user.group_id', $groupIds)
                ->selectRaw('group_user.group_id, MAX(progressions.completed_at) as last_completed_at')
                ->groupBy('group_user.group_id')
                ->pluck('last_completed_at', 'group_user.group_id');

            $scoresByGroup = DB::table('progressions')
                ->join('group_user', 'group_user.user_id', '=', 'progressions.user_id')
                ->leftJoin('scorm_scores', function ($join) {
                    $join->on('scorm_scores.user_id', '=', 'progressions.user_id')
                        ->on('scorm_scores.lecture_id', '=', 'progressions.lecture_id');
                })
                ->whereIn('group_user.group_id', $groupIds)
                ->selectRaw('
                    group_user.group_id,
                    COUNT(progressions.id) as total_lessons,
                    SUM(CASE WHEN scorm_scores.last_score >= 50 THEN 1 ELSE 0 END) as success_lessons
                ')
                ->groupBy('group_user.group_id')
                ->get()
                ->keyBy('group_id');
        }

        $groupesDashboard = $groupesDashboard->map(function ($g) use ($lastActivityByGroup, $scoresByGroup) {
            $g->last_completed_at = $lastActivityByGroup[$g->id] ?? null;

            $agg = $scoresByGroup->get($g->id);
            $total = (int) ($agg->total_lessons ?? 0);
            $success = (int) ($agg->success_lessons ?? 0);
            $g->taux_reussite = $total > 0 ? (int) round(($success / $total) * 100) : 0;

            return $g;
        });

        // Modules visibles sur le dashboard (utilisés dans ses groupes OU créés/attribués au formateur)
        $modules = Module::query()
            ->withCount('lectures')
            ->with([
                'groups' => function ($q) use ($formateurId) {
                    $q->where('instructor_id', $formateurId)
                        ->with(['users' => function ($u) {
                            $u->where('role', 'stagiaire');
                        }]);
                },
            ])
            ->where(function ($q) use ($formateurId) {
                $q->whereHas('groups', function ($g) use ($formateurId) {
                    $g->where('instructor_id', $formateurId);
                })
                ->orWhere('formateur_id', $formateurId);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($module) {
                $stagiaires = $module->groups
                    ->flatMap(fn ($g) => $g->users)
                    ->unique('id')
                    ->values();

                $module->stagiaires = $stagiaires;

                return $module;
            });

        return view('formateur.index', compact(
            'groupCount',
            'modulesUsed',
            'learnerCount',
            'avgScoreRounded',
            'avgCompletion',
            'groupesDashboard',
            'modules'
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

        $request->validate([
            'name'     => 'nullable|string|max:255',
            'prenom'   => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:30',
            'photo'    => 'nullable|image|max:2048',
        ]);

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

        return redirect()
            ->route('formateur.parametre')
            ->with('message', 'Profil mis à jour avec succès.');
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
            'newPassword'     => 'required|min:8|confirmed',
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
        $formateurId = auth()->id();

        // Liste des groupes du formateur (pour filtre)
        $groupes = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($q) use ($formateurId) {
                $q->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($gq) use ($formateurId) {
                        $gq->where('instructor_id', $formateurId);
                    });
            });

        // Filtre groupe (sécurisé sur le périmètre du formateur)
        if ($groupId = $request->input('group_id')) {
            $query->whereHas('groupesStagiaire', function ($gq) use ($groupId, $formateurId) {
                $gq->where('groups.id', $groupId)
                    ->where('instructor_id', $formateurId);
            });
        }

        // Recherche texte
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('prenom', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $stagiaires = $query
            ->with(['groupesStagiaire' => function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId)->orderBy('name');
            }])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('formateur.backend.stagiaires.all_stagiaires', compact('stagiaires', 'groupes'));
    }

    public function createStagiaire()
    {
        return view('formateur.backend.stagiaires.add_stagiaire');
    }

    public function storeStagiaire(Request $request)
    {
        $request->validate([
            'prenom'   => ['required', 'string', 'max:255'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
        ]);

        $formateurId = auth()->id();
        $email = strtolower(trim($request->email));
        $prenom = $request->prenom;
        $nom = $request->name;
        $gid = $request->integer('group_id') ?: null;

        // Si un groupe est fourni, il doit appartenir au formateur
        $group = null;
        if ($gid) {
            $group = Group::query()
                ->where('id', $gid)
                ->where('instructor_id', $formateurId)
                ->firstOrFail();
        }

        // Réutilisation possible (y compris supprimé), mais seulement si le compte est bien un stagiaire
        $user = User::withTrashed()->where('email', $email)->first();

        if ($user && $user->role !== 'stagiaire') {
            return back()
                ->withErrors(['email' => 'Adresse déjà utilisée par un autre type de compte.'])
                ->withInput();
        }

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }

            // On rattache au formateur si pas déjà défini
            if (!$user->formateur_id) {
                $user->formateur_id = $formateurId;
            }

            // On complète sans écraser inutilement
            $user->prenom = $user->prenom ?: $prenom;
            $user->name = $user->name ?: $nom;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();
        } else {
            $user = User::create([
                'prenom'       => $prenom,
                'name'         => $nom,
                'email'        => $email,
                'password'     => $request->filled('password')
                    ? Hash::make($request->password)
                    : bcrypt(str()->password(12)),
                'role'         => 'stagiaire',
                'formateur_id' => $formateurId,
                'status'       => 1,
                'code_acces'   => CodeGeneratorService::generateUniqueAccessCode(),
            ]);
        }

        if ($group) {
            $group->users()->syncWithoutDetaching([$user->id]);
        }

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', $user->wasRecentlyCreated
                ? 'Stagiaire créé et rattaché si un groupe a été fourni.'
                : 'Stagiaire existant réutilisé et rattaché si un groupe a été fourni.');
    }

    public function editStagiaire($id)
    {
        $formateurId = auth()->id();

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
                    });
            })
            ->findOrFail($id);

        return view('formateur.backend.stagiaires.edit_stagiaire', compact('stagiaire'));
    }

    public function updateStagiaire(Request $request, $id)
    {
        $formateurId = auth()->id();

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
                    });
            })
            ->findOrFail($id);

        $request->validate([
            'prenom'   => 'required|string|max:255',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $stagiaire->id,
            'password' => 'nullable|string|min:8',
        ]);

        $stagiaire->prenom = $request->prenom;
        $stagiaire->name = $request->name;
        $stagiaire->email = $request->email;

        if ($request->filled('password')) {
            $stagiaire->password = Hash::make($request->password);
        }

        $stagiaire->save();

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', 'Stagiaire modifié avec succès.');
    }

    public function destroyStagiaire($id)
    {
        $formateurId = auth()->id();

        $stagiaire = User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($q) use ($formateurId) {
                        $q->where('instructor_id', $formateurId);
                    });
            })
            ->findOrFail($id);

        $stagiaire->delete();

        return redirect()
            ->route('formateur.stagiaires.index')
            ->with('success', 'Stagiaire supprimé avec succès.');
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
        // Piège antispam
        if ($request->filled('website')) {
            return back()->withErrors(['form' => 'Envoi invalide.'])->withInput();
        }

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

        $formateur = User::create([
            'prenom'   => $validated['prenom'],
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'address'  => $validated['address'] ?? null,
            'societe'  => $validated['societe'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'formateur',
        ]);

        Auth::login($formateur);

        Mail::to($formateur->email)->send(new FormateurWelcome([
            'prenom' => $formateur->prenom,
            'nom'    => $formateur->name,
            'email'  => $formateur->email,
        ]));

        Mail::to('contact@oneduc.fr')->send(new NewFormateurNotification([
            'prenom'  => $formateur->prenom,
            'nom'     => $formateur->name,
            'email'   => $formateur->email,
            'phone'   => $formateur->phone,
            'societe' => $formateur->societe,
        ]));

        return redirect()
            ->route('formateur.dashboard')
            ->with('success', 'Bienvenue sur Oneduc !');
    }

    /* -------------------------------------------------------------------------
     | Mes modules (index)
     |-------------------------------------------------------------------------- */
    public function mesModules(Request $request)
    {
        $formateurId = auth()->id();
        $search = trim((string) $request->query('search', ''));

        $modules = Module::query()
            ->where(function ($q) use ($formateurId) {
                $q->whereHas('groups', function ($g) use ($formateurId) {
                    $g->where('instructor_id', $formateurId);
                })
                ->orWhere('formateur_id', $formateurId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('module_title', 'like', "%{$search}%")
                      ->orWhere('module_name', 'like', "%{$search}%");
                });
            })
            ->with([
                'sections' => function ($q) {
                    $q->select('id', 'module_id')->orderBy('id');
                },
                'groups' => function ($q) use ($formateurId) {
                    $q->where('instructor_id', $formateurId)
                        ->with(['users' => function ($u) {
                            $u->where('role', 'stagiaire');
                        }]);
                },
            ])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('formateur.formations.index', compact('modules', 'search'));
    }

    /* -------------------------------------------------------------------------
     | Détail module (formateur)
     |-------------------------------------------------------------------------- */
    public function moduleDetail(Module $module)
    {
        $formateurId = auth()->id();

        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->where('instructor_id', $formateurId)->exists();

        abort_unless($isAllowed, 403);

        $module->load([
            'formateur',
            'sections.lectures.objectives',
            'groups' => function ($q) use ($formateurId) {
                $q->where('instructor_id', $formateurId)
                    ->with(['users' => function ($u) {
                        $u->where('role', 'stagiaire');
                    }]);
            },
        ]);

        $totalSections = $module->sections->count();
        $totalLectures = $module->sections->flatMap->lectures->count();
        $totalSlides = (int) $module->sections->flatMap->lectures->sum('slide_count');
        $totalQuestions = (int) $module->sections->flatMap->lectures->sum('quiz_questions_per_attempt');

        $groupCount = $module->groups->count();
        $stagiaires = $module->groups->flatMap(fn ($g) => $g->users)->unique('id')->values();
        $stagiaireCount = $stagiaires->count();

        // Objectifs pédagogiques issus des leçons (agrégés et sans doublon)
        $lessonObjectives = $module->sections
            ->flatMap->lectures
            ->flatMap(function ($lecture) {
                return $lecture->objectives->pluck('title');
            })
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->unique()
            ->values();

        return view('formateur.formations.formateur_module_detail', compact(
            'module',
            'totalSections',
            'totalLectures',
            'totalSlides',
            'totalQuestions',
            'groupCount',
            'stagiaires',
            'stagiaireCount',
            'lessonObjectives'
        ));
    }
public function updateQuizCount(Request $request, $lectureId)
{
    $lecture = ModuleLecture::findOrFail($lectureId);

    // Validation : on doit envoyer un nombre, et il ne peut pas dépasser le total dispo
    $totalQuestionsInBank = $lecture->quizQuestions()->count();

    $validated = $request->validate([
        'questions_count' => 'required|integer|min:1|max:' . ($totalQuestionsInBank > 0 ? $totalQuestionsInBank : 1),
    ]);

    // Mise à jour
    $lecture->update([
        'quiz_questions_per_attempt' => $validated['questions_count']
    ]);

    return back()->with('success', 'Le nombre de questions a été mis à jour.');
}
    /* -------------------------------------------------------------------------
     | Prévisualisation (mode test)
     |-------------------------------------------------------------------------- */
    public function preview(Module $module)
    {
        $formateurId = auth()->id();

        // Sécuriser l'accès au module
        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->where('instructor_id', $formateurId)->exists();

        abort_unless($isAllowed, 403);

        $module->load('sections.lectures');

        $firstSection = $module->sections->first();
        $firstLecture = $firstSection?->lectures->first();

        if (!$firstSection || !$firstLecture) {
            return back()->with('error', 'Aucune leçon disponible à tester.');
        }

        // Attention : cette route est côté stagiaire. À conserver seulement si c'est voulu.
        return redirect()->route('formateur.formations.lecture', [
            'module'  => $module->id,
            'section' => $firstSection->id,
            'lecture' => $firstLecture->id,
        ]);
    }
}
