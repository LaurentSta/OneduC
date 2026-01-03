<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Progression;
use App\Models\User;
use App\Models\Group;
use App\Models\Module;

class ProgressionController extends Controller
{
    /**
     * Contrôleur unique piloté par la route (defaults('view', ...))
     * Vues :
     * - groupes
     * - stagiaires
     * - stagiaire
     * - modules
     */
    public function index(Request $request, ?User $user = null)
    {
        $formateurId = auth()->id();

        // view peut venir des defaults() de la route (route param) ou de la query string
        $view = $request->route('view') ?? $request->query('view', 'groupes');

        // Filtres communs
        $groupId = (int) $request->query('group_id', 0);
        $search  = trim((string) $request->query('search', ''));

        // Liste groupes (pour menus / filtres)
        $groupesList = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get(['id', 'name']);

        /*
        |--------------------------------------------------------------------------
        | VUE 1 : GROUPES
        |--------------------------------------------------------------------------
        */
        if ($view === 'groupes') {

            // Agrégats par groupe (pivot group_user + progressions + scorm_scores)
            $groupes = Group::query()
                ->where('instructor_id', $formateurId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function ($g) use ($formateurId) {

                    // Stagiaires du groupe (distinct)
                    $stagiaireIds = DB::table('group_user')
                        ->join('users', 'users.id', '=', 'group_user.user_id')
                        ->where('group_user.group_id', $g->id)
                        ->where('users.role', 'stagiaire')
                        ->pluck('users.id')
                        ->unique()
                        ->values();

                    $g->stagiaires_count = $stagiaireIds->count();

                    // Modules du groupe (si pivot group_module)
                    $g->modules_count = (int) DB::table('group_module')
                        ->where('group_id', $g->id)
                        ->count();

                    // Temps total sur la plateforme (somme simple)
                    $g->total_site_time = (int) User::whereIn('id', $stagiaireIds->all())->sum('total_site_time');

                    // Leçons terminées + dernière activité
                    $g->lecons_terminees_count = 0;
                    $g->last_completed_at = null;
                    $g->taux_reussite = 0;

                    if ($stagiaireIds->isNotEmpty()) {

                        $g->lecons_terminees_count = (int) Progression::whereIn('user_id', $stagiaireIds->all())
                            ->count();

                        $g->last_completed_at = Progression::whereIn('user_id', $stagiaireIds->all())
                            ->max('completed_at');

                        // Réussite : progression join scorm_scores (score >= 50) sur la même leçon
                        $success = (int) DB::table('progressions')
                            ->join('scorm_scores', function ($join) {
                                $join->on('progressions.user_id', '=', 'scorm_scores.user_id')
                                     ->on('progressions.lecture_id', '=', 'scorm_scores.lecture_id');
                            })
                            ->whereIn('progressions.user_id', $stagiaireIds->all())
                            ->where('scorm_scores.last_score', '>=', 50)
                            ->count();

                        $total = (int) $g->lecons_terminees_count;

                        $g->taux_reussite = $total > 0 ? (int) round(($success / $total) * 100) : 0;
                    }

                    return $g;
                });

            return view('formateur.progressions.groupes', [
                'groupes'      => $groupes,
                'groupesList'  => $groupesList,
                'totalGroupes' => $groupes->count(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VUE 2 : STAGIAIRES (liste)
        |--------------------------------------------------------------------------
        */
        if ($view === 'stagiaires') {

            $query = User::query()
                ->where('role', 'stagiaire')
                ->where(function ($q) use ($formateurId) {
                    $q->where('formateur_id', $formateurId)
                      ->orWhereHas('groupesStagiaire', function ($gq) use ($formateurId) {
                          $gq->where('instructor_id', $formateurId);
                      });
                });

            // Filtre groupe
            if ($groupId > 0) {
                $query->whereHas('groupesStagiaire', function ($gq) use ($groupId, $formateurId) {
                    $gq->where('groups.id', $groupId)
                       ->where('instructor_id', $formateurId);
                });
            }

            // Recherche
            if ($search !== '') {
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
                ->withCount(['progressions as lecons_terminees_count'])
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString();

            // Enrichissement : dernière activité + taux de réussite
            $ids = $stagiaires->getCollection()->pluck('id')->all();

            $lastActivity = Progression::query()
                ->selectRaw('user_id, MAX(completed_at) as last_completed_at')
                ->whereIn('user_id', $ids)
                ->groupBy('user_id')
                ->pluck('last_completed_at', 'user_id');

            $completedCount = Progression::query()
                ->selectRaw('user_id, COUNT(*) as total')
                ->whereIn('user_id', $ids)
                ->groupBy('user_id')
                ->pluck('total', 'user_id');

            $successCount = DB::table('progressions')
                ->join('scorm_scores', function ($join) {
                    $join->on('progressions.user_id', '=', 'scorm_scores.user_id')
                         ->on('progressions.lecture_id', '=', 'scorm_scores.lecture_id');
                })
                ->whereIn('progressions.user_id', $ids)
                ->where('scorm_scores.last_score', '>=', 50)
                ->selectRaw('progressions.user_id, COUNT(*) as success')
                ->groupBy('progressions.user_id')
                ->pluck('success', 'progressions.user_id');

            $stagiaires->getCollection()->transform(function ($s) use ($lastActivity, $completedCount, $successCount) {
                $total = (int) ($completedCount[$s->id] ?? 0);
                $ok    = (int) ($successCount[$s->id] ?? 0);

                $s->last_completed_at = $lastActivity[$s->id] ?? null;
                $s->taux_reussite     = $total > 0 ? (int) round(($ok / $total) * 100) : 0;

                return $s;
            });

            return view('formateur.progressions.stagiaires', [
                'stagiaires'      => $stagiaires,
                'groupes'         => $groupesList,
                'groupId'         => $groupId,
                'search'          => $search,
                'totalGroupes'    => $groupesList->count(),
                'totalStagiaires' => $stagiaires->total(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VUE 3 : STAGIAIRE (détail)
        |--------------------------------------------------------------------------
        */
        if ($view === 'stagiaire') {

            // Le param {user} est bindé par Laravel : ProgressionController@index(Request $r, ?User $user)
            if (!$user) {
                abort(404);
            }

            // Sécurité : le stagiaire doit appartenir au formateur
            $allowed = User::query()
                ->where('id', $user->id)
                ->where('role', 'stagiaire')
                ->where(function ($q) use ($formateurId) {
                    $q->where('formateur_id', $formateurId)
                      ->orWhereHas('groupesStagiaire', function ($gq) use ($formateurId) {
                          $gq->where('instructor_id', $formateurId);
                      });
                })
                ->exists();

            if (!$allowed) {
                abort(403);
            }

            $stagiaire = User::query()
                ->with(['groupesStagiaire' => function ($q) use ($formateurId) {
                    $q->where('instructor_id', $formateurId)->orderBy('name');
                }])
                ->findOrFail($user->id);

            $progressions = Progression::query()
                ->with(['lecture.section.module'])
                ->where('user_id', $stagiaire->id)
                ->orderByDesc('completed_at')
                ->paginate(20)
                ->withQueryString();

            // KPI simples
            $totalLeconsTerminees = (int) Progression::where('user_id', $stagiaire->id)->count();
            $lastCompletedAt      = Progression::where('user_id', $stagiaire->id)->max('completed_at');

            $success = (int) DB::table('progressions')
                ->join('scorm_scores', function ($join) {
                    $join->on('progressions.user_id', '=', 'scorm_scores.user_id')
                         ->on('progressions.lecture_id', '=', 'scorm_scores.lecture_id');
                })
                ->where('progressions.user_id', $stagiaire->id)
                ->where('scorm_scores.last_score', '>=', 50)
                ->count();

            $tauxReussite = $totalLeconsTerminees > 0 ? (int) round(($success / $totalLeconsTerminees) * 100) : 0;

            return view('formateur.progressions.stagiaire', [
                'stagiaire'            => $stagiaire,
                'progressions'         => $progressions,
                'totalLeconsTerminees' => $totalLeconsTerminees,
                'lastCompletedAt'      => $lastCompletedAt,
                'tauxReussite'         => $tauxReussite,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VUE 4 : MODULES (utilisés par le formateur)
        |--------------------------------------------------------------------------
        */
        if ($view === 'modules') {

            // Modules utilisés par les groupes du formateur
            $modules = Module::query()
                ->whereHas('groups', function ($q) use ($formateurId) {
                    $q->where('instructor_id', $formateurId);
                })
                ->withCount(['lectures as lectures_count'])
                ->with(['groups' => function ($q) use ($formateurId) {
                    $q->where('instructor_id', $formateurId)->with(['users' => function ($u) {
                        $u->where('role', 'stagiaire');
                    }]);
                }])
                ->orderBy('module_title')
                ->get();

            // Compter stagiaires uniques par module + score moyen via SQL (évite N+1)
            $moduleIds = $modules->pluck('id')->all();

            // Stagiaires uniques par module
            $stagiairesByModule = DB::table('group_module')
                ->join('group_user', 'group_user.group_id', '=', 'group_module.group_id')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->join('users', 'users.id', '=', 'group_user.user_id')
                ->whereIn('group_module.module_id', $moduleIds)
                ->where('groups.instructor_id', $formateurId)
                ->where('users.role', 'stagiaire')
                ->selectRaw('group_module.module_id, COUNT(DISTINCT users.id) as c')
                ->groupBy('group_module.module_id')
                ->pluck('c', 'group_module.module_id');

            // Groupes par module
            $groupesByModule = DB::table('group_module')
                ->join('groups', 'groups.id', '=', 'group_module.group_id')
                ->whereIn('group_module.module_id', $moduleIds)
                ->where('groups.instructor_id', $formateurId)
                ->selectRaw('group_module.module_id, COUNT(DISTINCT group_module.group_id) as c')
                ->groupBy('group_module.module_id')
                ->pluck('c', 'group_module.module_id');

            // Score moyen par module (scorm_scores -> module_lectures -> module_sections -> modules)
            // Hypothèses tables : module_lectures, module_sections
            $avgScoreByModule = DB::table('scorm_scores')
                ->join('module_lectures', 'module_lectures.id', '=', 'scorm_scores.lecture_id')
                ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                ->join('group_user', 'group_user.user_id', '=', 'scorm_scores.user_id')
                ->join('groups', 'groups.id', '=', 'group_user.group_id')
                ->whereIn('module_sections.module_id', $moduleIds)
                ->where('groups.instructor_id', $formateurId)
                ->selectRaw('module_sections.module_id as module_id, AVG(scorm_scores.last_score) as avg_score')
                ->groupBy('module_sections.module_id')
                ->pluck('avg_score', 'module_id');

            $modules = $modules->map(function ($m) use ($stagiairesByModule, $groupesByModule, $avgScoreByModule) {
                $m->stagiaires_count = (int) ($stagiairesByModule[$m->id] ?? 0);
                $m->groupes_count    = (int) ($groupesByModule[$m->id] ?? 0);
                $m->avg_score        = isset($avgScoreByModule[$m->id]) ? (int) round($avgScoreByModule[$m->id]) : 0;
                return $m;
            });

            return view('formateur.progressions.modules', [
                'modules' => $modules,
            ]);
        }

        // Fallback
        return redirect()->route('formateur.progressions.groupes');
    }

    /**
     * Marquer une leçon comme terminée (stagiaire côté SCORM)
     */
    public function markCompleted(Request $request)
    {
        $userId    = auth()->id();
        $lectureId = (int) $request->input('lecture_id');

        if (!$userId || !$lectureId) {
            return response()->json(['error' => 'Données manquantes'], 400);
        }

        Progression::firstOrCreate(
            [
                'user_id'    => $userId,
                'lecture_id' => $lectureId,
            ],
            [
                'completed_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok']);
    }
}
