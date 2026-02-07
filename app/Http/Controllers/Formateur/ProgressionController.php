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
        | VUE 1 : GROUPES (Avec indicateurs de décrochage)
        |--------------------------------------------------------------------------
        */
        if ($view === 'groupes') {

            $groupes = Group::query()
                ->where('instructor_id', $formateurId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function ($g) use ($formateurId) {

                    // Récupérer les stagiaires
                    $stagiaires = User::query()
                        ->join('group_user', 'users.id', '=', 'group_user.user_id')
                        ->where('group_user.group_id', $g->id)
                        ->where('users.role', 'stagiaire')
                        ->select('users.id')
                        ->get();

                    $stagiaireIds = $stagiaires->pluck('id');
                    $g->stagiaires_count = $stagiaireIds->count();

                    // Modules du groupe
                    $g->modules_count = (int) DB::table('group_module')->where('group_id', $g->id)->count();

                    // --- 🚨 NOUVEAUX INDICATEURS DE DÉCROCHAGE ---
                    
                    // 1. Jamais commencé : Aucune progression enregistrée
                    $activeUserIds = Progression::whereIn('user_id', $stagiaireIds)->distinct('user_id')->pluck('user_id')->toArray();
                    $g->not_started_count = $stagiaireIds->count() - count($activeUserIds);

                    // 2. Inactifs : N'a rien terminé depuis 15 jours (parmi ceux qui ont commencé)
                    $fifteenDaysAgo = now()->subDays(15);
                    
                    $recentActivityIds = Progression::whereIn('user_id', $stagiaireIds)
                        ->where('completed_at', '>=', $fifteenDaysAgo)
                        ->distinct('user_id')
                        ->pluck('user_id')
                        ->toArray();

                    // Ceux qui ont commencé MAIS n'ont rien fait récemment
                    $g->inactive_count = count(array_diff($activeUserIds, $recentActivityIds));

                    // --- FIN INDICATEURS ---

                    // Calculs existants (Moyennes, Temps...)
                    $g->total_site_time = (int) User::whereIn('id', $stagiaireIds)->sum('total_site_time');
                    $g->lecons_terminees_count = 0;
                    $g->taux_reussite = 0;

                    if ($stagiaireIds->isNotEmpty()) {
                        $g->lecons_terminees_count = (int) Progression::whereIn('user_id', $stagiaireIds)->count();
                        
                        $success = (int) DB::table('progressions')
                            ->join('scorm_scores', function ($join) {
                                $join->on('progressions.user_id', '=', 'scorm_scores.user_id')
                                     ->on('progressions.lecture_id', '=', 'scorm_scores.lecture_id');
                            })
                            ->whereIn('progressions.user_id', $stagiaireIds)
                            ->where('scorm_scores.last_score', '>=', 50)
                            ->count();

                        $total = $g->lecons_terminees_count;
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
        | VUE 3 : STAGIAIRE (Détail complet & Statistiques)
        |--------------------------------------------------------------------------
        */
        if ($view === 'stagiaire') {

            if (!$user) abort(404);

            // 1. Sécurité : le stagiaire doit être lié au formateur
            $allowed = User::query()
                ->where('id', $user->id)
                ->where('role', 'stagiaire')
                ->where(function ($q) use ($formateurId) {
                    $q->where('formateur_id', $formateurId)
                      ->orWhereHas('groupesStagiaire', fn($g) => $g->where('instructor_id', $formateurId));
                })
                ->exists();

            if (!$allowed) abort(403, 'Ce stagiaire ne fait pas partie de vos groupes.');

            $stagiaire = User::findOrFail($user->id);
            $userId = $stagiaire->id;

            // --- A. CALCULS DE TEMPS & ENGAGEMENT ---
            $scormTime = (int) \App\Models\ScormScore::where('user_id', $userId)->sum('session_time');
            $quizTime  = (int) DB::table('quiz_attempts')->where('user_id', $userId)->sum('total_time_seconds');
            
            $videoStatsObj = \App\Models\VideoSegmentTracking::where('user_id', $userId)
                ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments')
                ->first();
            $videoTime = (int) ($videoStatsObj->watch_time ?? 0);

            $engagementTotal = $scormTime + $quizTime + $videoTime;

            // --- B. TEMPS DE RÉFLEXION MOYEN (LATENCE) ---
            $totalLatencySeconds = 0;
            $totalQuestionsCount = 0;

            // SCORM Latency
            $scormInteractions = \App\Models\ScormInteraction::where('user_id', $userId)
                ->whereIn('result', ['correct', 'wrong'])->get();
            
            foreach ($scormInteractions as $interaction) {
                if ($interaction->latency) {
                    try {
                        [$h, $m, $s] = array_pad(explode(':', $interaction->latency), 3, 0);
                        $totalLatencySeconds += ((int)$h * 3600 + (int)$m * 60 + (int)$s);
                        $totalQuestionsCount++;
                    } catch (\Exception $e) {}
                }
            }

            // Quiz Latency (Natif)
            $nativeQuestions = DB::table('quiz_attempt_questions')
                ->join('quiz_attempts', 'quiz_attempt_questions.attempt_id', '=', 'quiz_attempts.id')
                ->where('quiz_attempts.user_id', $userId)
                ->whereNotNull('quiz_attempt_questions.answered_at')
                ->select('quiz_attempt_questions.time_seconds')->get();

            foreach ($nativeQuestions as $nq) {
                $totalLatencySeconds += (int) $nq->time_seconds;
                $totalQuestionsCount++;
            }

            $averageLatencyTime = $totalQuestionsCount > 0 ? (int) round($totalLatencySeconds / $totalQuestionsCount) : 0;

            // --- C. ANALYSE DÉTAILLÉE (DROIT À L'ERREUR) ---
            $rawAnswers = \App\Models\QuizAttemptQuestion::with(['question', 'attempt.lecture.module'])
                ->whereHas('attempt', fn($q) => $q->where('user_id', $userId))
                ->whereNotNull('answered_at')
                ->orderBy('answered_at', 'asc')
                ->get();

            $consolidatedQuestions = $rawAnswers->groupBy('question_id')->map(function ($answers) {
                $firstTry = $answers->first();
                $lastTry  = $answers->last();
                return (object) [
                    'question_text'  => $firstTry->question->question_text ?? 'Question supprimée',
                    'module_title'   => $firstTry->attempt->lecture->module->module_title ?? 'Module inconnu',
                    'attempts_count' => $answers->count(),
                    'first_result'   => (bool) $firstTry->is_correct,
                    'final_status'   => $answers->contains('is_correct', 1), // Validé si au moins une bonne réponse
                    'last_date'      => $lastTry->answered_at,
                ];
            })->sortByDesc('last_date');

            // --- D. TAUX DE RÉUSSITE GLOBAL ---
            $uniqueQuestions = $consolidatedQuestions->count();
            $validatedQuestions = $consolidatedQuestions->where('final_status', true)->count();
            $tauxReussiteGlobal = $uniqueQuestions > 0 ? (int) round(($validatedQuestions / $uniqueQuestions) * 100) : 0;

            // --- E. PROGRESSION CLASSIQUE (HISTORIQUE) ---
            $progressions = Progression::query()
                ->with(['lecture.section.module'])
                ->where('user_id', $userId)
                ->orderByDesc('completed_at')
                ->paginate(20);

            return view('formateur.progressions.stagiaire', compact(
                'stagiaire',
                'progressions',
                'engagementTotal',
                'averageLatencyTime',
                'videoTime',
                'tauxReussiteGlobal',
                'consolidatedQuestions',
                'uniqueQuestions',
                'validatedQuestions'
            ));
        }

        /*
        |--------------------------------------------------------------------------
        | VUE 4 : MODULES (Avec Top 3 Erreurs & Taux d'abandon)
        |--------------------------------------------------------------------------
        */
        if ($view === 'modules') {

            $modules = Module::query()
                ->whereHas('groups', fn($q) => $q->where('instructor_id', $formateurId))
                ->withCount(['lectures as lectures_count'])
                ->orderBy('module_title')
                ->get();

            $modules = $modules->map(function ($m) use ($formateurId) {
                
                // 1. Stagiaires assignés via les groupes du formateur
                $stagiaireIds = DB::table('group_module')
                    ->join('group_user', 'group_user.group_id', '=', 'group_module.group_id')
                    ->join('groups', 'groups.id', '=', 'group_module.group_id')
                    ->join('users', 'users.id', '=', 'group_user.user_id')
                    ->where('group_module.module_id', $m->id)
                    ->where('groups.instructor_id', $formateurId)
                    ->where('users.role', 'stagiaire')
                    ->pluck('users.id')
                    ->unique();

                $m->stagiaires_count = $stagiaireIds->count();
                $m->groupes_count = DB::table('group_module')
                    ->join('groups', 'groups.id', '=', 'group_module.group_id')
                    ->where('module_id', $m->id)
                    ->where('groups.instructor_id', $formateurId)
                    ->count();

                // 2. Score Moyen (Calcul existant)
                $avgScore = DB::table('scorm_scores')
                    ->join('module_lectures', 'module_lectures.id', '=', 'scorm_scores.lecture_id')
                    ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                    ->where('module_sections.module_id', $m->id)
                    ->whereIn('scorm_scores.user_id', $stagiaireIds)
                    ->avg('scorm_scores.last_score');
                
                $m->avg_score = (int) round($avgScore ?? 0);

                // --- 🚨 NOUVEAU : TAUX D'ABANDON ---
                // Définition : Ont commencé le module (au moins 1 leçon faite) MAIS ne l'ont pas fini.
                // Note : On considère "Fini" si toutes les leçons sont completed. C'est lourd à calculer parfaitement.
                // Approche simplifiée : On regarde ceux qui ont démarré mais dont la dernière activité date de > 15j
                
                $startedUsers = Progression::whereIn('user_id', $stagiaireIds)
                    ->join('module_lectures', 'module_lectures.id', '=', 'progressions.lecture_id')
                    ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                    ->where('module_sections.module_id', $m->id)
                    ->distinct('progressions.user_id')
                    ->count();

                // Ce n'est pas un vrai "Abandon" (churn) mais plutôt un "Démarrage"
                $m->started_count = $startedUsers;
                $m->start_rate = $m->stagiaires_count > 0 ? round(($startedUsers / $m->stagiaires_count) * 100) : 0;


                // --- ❌ NOUVEAU : TOP 3 QUESTIONS ÉCHOUÉES ---
                // On regarde les quiz_attempt_questions pour ce module
                $topFailed = DB::table('quiz_attempt_questions as qaq')
                    ->join('quiz_attempts as qa', 'qa.id', '=', 'qaq.attempt_id')
                    ->join('module_lectures as ml', 'ml.id', '=', 'qa.lecture_id')
                    ->join('module_sections as ms', 'ms.id', '=', 'ml.section_id')
                    ->join('quiz_questions as qq', 'qq.id', '=', 'qaq.question_id')
                    ->where('ms.module_id', $m->id)
                    ->whereIn('qa.user_id', $stagiaireIds) // Seulement mes stagiaires
                    ->select(
                        'qq.question_text',
                        DB::raw('count(*) as total_attempts'),
                        DB::raw('sum(case when qaq.is_correct = 0 then 1 else 0 end) as failures')
                    )
                    ->groupBy('qq.id', 'qq.question_text')
                    ->having('failures', '>', 0) // Au moins une erreur
                    ->orderByDesc('failures') // Les plus ratées en premier
                    ->take(3)
                    ->get();

                // On calcule le % d'échec pour l'affichage
                $topFailed->transform(function($q) {
                    $q->fail_rate = $q->total_attempts > 0 ? round(($q->failures / $q->total_attempts) * 100) : 0;
                    return $q;
                });

                $m->top_failed = $topFailed;

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
