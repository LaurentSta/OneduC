<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
                ->when($search !== '', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(15, ['id', 'name'])
                ->withQueryString();

            $groupes->getCollection()->transform(function ($g) use ($formateurId) {

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
                'totalGroupes' => $groupes->total(),
                'search'       => $search,
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
            [$groupMemberships, $selectedGroup, $selectedGroupModuleIds, $contextStartAt] = $this->resolveStagiaireContext(
                $stagiaire,
                $formateurId,
                $groupId
            );
            $restrictToSelectedGroup = !is_null($selectedGroup);

            // --- A. CALCULS DE TEMPS & ENGAGEMENT ---
            $scormTimeQuery = DB::table('scorm_scores')
                ->join('module_lectures', 'module_lectures.id', '=', 'scorm_scores.lecture_id')
                ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                ->where('scorm_scores.user_id', $userId);
            $this->applyModuleFilter($scormTimeQuery, $restrictToSelectedGroup, $selectedGroupModuleIds, 'module_sections.module_id');
            $scormTime = (int) $scormTimeQuery->sum('scorm_scores.session_time');

            $quizTimeQuery = DB::table('quiz_attempts')
                ->join('module_lectures', 'module_lectures.id', '=', 'quiz_attempts.lecture_id')
                ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                ->where('quiz_attempts.user_id', $userId);
            $this->applyModuleFilter($quizTimeQuery, $restrictToSelectedGroup, $selectedGroupModuleIds, 'module_sections.module_id');
            $quizTime  = (int) $quizTimeQuery->sum('quiz_attempts.total_time_seconds');

            $videoStatsQuery = DB::table('video_segment_trackings')
                ->join('module_lectures', 'module_lectures.id', '=', 'video_segment_trackings.lecture_id')
                ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                ->where('video_segment_trackings.user_id', $userId)
                ->selectRaw('SUM(total_watch_time) as watch_time, COUNT(*) as segments');
            $this->applyModuleFilter($videoStatsQuery, $restrictToSelectedGroup, $selectedGroupModuleIds, 'module_sections.module_id');
            $videoStatsObj = $videoStatsQuery->first();
            $videoTime = (int) ($videoStatsObj->watch_time ?? 0);

            $engagementTotal = $scormTime + $quizTime + $videoTime;

            // --- B. TEMPS DE RÉFLEXION MOYEN (LATENCE) ---
            $totalLatencySeconds = 0;
            $totalQuestionsCount = 0;

            // SCORM Latency
            $scormInteractionsQuery = DB::table('scorm_interactions')
                ->join('module_lectures', 'module_lectures.id', '=', 'scorm_interactions.lecture_id')
                ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                ->where('scorm_interactions.user_id', $userId)
                ->whereIn('scorm_interactions.result', ['correct', 'wrong'])
                ->select('scorm_interactions.latency');
            $this->applyModuleFilter($scormInteractionsQuery, $restrictToSelectedGroup, $selectedGroupModuleIds, 'module_sections.module_id');
            $scormInteractions = $scormInteractionsQuery->get();
            
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
                ->join('module_lectures', 'module_lectures.id', '=', 'quiz_attempts.lecture_id')
                ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
                ->where('quiz_attempts.user_id', $userId)
                ->whereNotNull('quiz_attempt_questions.answered_at')
                ->select('quiz_attempt_questions.time_seconds');
            $this->applyModuleFilter($nativeQuestions, $restrictToSelectedGroup, $selectedGroupModuleIds, 'module_sections.module_id');
            $nativeQuestions = $nativeQuestions->get();

            foreach ($nativeQuestions as $nq) {
                $totalLatencySeconds += (int) $nq->time_seconds;
                $totalQuestionsCount++;
            }

            $averageLatencyTime = $totalQuestionsCount > 0 ? (int) round($totalLatencySeconds / $totalQuestionsCount) : 0;

            // --- C. ANALYSE DÉTAILLÉE (DROIT À L'ERREUR) ---
            $rawAnswers = \App\Models\QuizAttemptQuestion::with(['question', 'attempt.lecture.module'])
                ->whereHas('attempt', fn($q) => $q->where('user_id', $userId))
                ->when($restrictToSelectedGroup, function ($query) use ($selectedGroupModuleIds) {
                    if ($selectedGroupModuleIds === []) {
                        $query->whereRaw('1 = 0');
                        return;
                    }

                    $query->whereHas('attempt.lecture.section', fn($sectionQuery) => $sectionQuery->whereIn('module_id', $selectedGroupModuleIds));
                })
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
                ->when($restrictToSelectedGroup, function ($query) use ($selectedGroupModuleIds) {
                    if ($selectedGroupModuleIds === []) {
                        $query->whereRaw('1 = 0');
                        return;
                    }

                    $query->whereHas('lecture.section', fn($sectionQuery) => $sectionQuery->whereIn('module_id', $selectedGroupModuleIds));
                })
                ->orderByDesc('completed_at')
                ->paginate(20);

            $dailyActivity = $this->buildDailyActivitySummary(
                $userId,
                $contextStartAt,
                $restrictToSelectedGroup,
                $selectedGroupModuleIds
            );
            $activityFeed = $this->buildUnifiedActivityFeed(
                $userId,
                $contextStartAt,
                $restrictToSelectedGroup,
                $selectedGroupModuleIds
            );
            $presenceSummary = $this->buildPresenceSummary(
                $stagiaire,
                $selectedGroup,
                $contextStartAt,
                $dailyActivity
            );
            $activityTimeline = $this->buildTimelineWindow($dailyActivity, $contextStartAt);
            $timelineMaxScore = max(1, (int) $activityTimeline->max('activity_score'));

            return view('formateur.progressions.stagiaire', compact(
                'stagiaire',
                'progressions',
                'engagementTotal',
                'averageLatencyTime',
                'videoTime',
                'tauxReussiteGlobal',
                'consolidatedQuestions',
                'uniqueQuestions',
                'validatedQuestions',
                'groupMemberships',
                'selectedGroup',
                'presenceSummary',
                'activityTimeline',
                'activityFeed',
                'timelineMaxScore'
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

    private function resolveStagiaireContext(User $stagiaire, int $formateurId, int $preferredGroupId = 0): array
    {
        $groupMemberships = DB::table('group_user')
            ->join('groups', 'groups.id', '=', 'group_user.group_id')
            ->where('group_user.user_id', $stagiaire->id)
            ->where('group_user.role_in_group', 'stagiaire')
            ->where('groups.instructor_id', $formateurId)
            ->orderByDesc('group_user.created_at')
            ->get([
                'groups.id',
                'groups.name',
                'group_user.created_at as joined_at',
            ])
            ->map(function ($membership) {
                $membership->joined_at = $membership->joined_at
                    ? Carbon::parse($membership->joined_at)
                    : null;

                return $membership;
            })
            ->values();

        $selectedGroup = $groupMemberships->firstWhere('id', $preferredGroupId) ?? $groupMemberships->first();

        $selectedGroupModuleIds = [];
        if ($selectedGroup) {
            $selectedGroupModuleIds = DB::table('group_module')
                ->where('group_id', $selectedGroup->id)
                ->pluck('module_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $contextStartAt = $selectedGroup?->joined_at
            ? $selectedGroup->joined_at->copy()->startOfDay()
            : $stagiaire->created_at->copy()->startOfDay();

        return [$groupMemberships, $selectedGroup, $selectedGroupModuleIds, $contextStartAt];
    }

    private function applyModuleFilter($query, bool $restrictToSelectedGroup, array $moduleIds, string $column): void
    {
        if (! $restrictToSelectedGroup) {
            return;
        }

        if ($moduleIds === []) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn($column, $moduleIds);
    }

    private function buildDailyActivitySummary(
        int $userId,
        Carbon $contextStartAt,
        bool $restrictToSelectedGroup,
        array $moduleIds
    ): Collection {
        $activityByDate = collect();

        $mergeRows = function (Collection $rows, callable $resolver) use (&$activityByDate): void {
            foreach ($rows as $row) {
                $payload = $resolver($row);
                $date = $payload['activity_date'];

                if (! $date) {
                    continue;
                }

                $existing = $activityByDate->get($date, [
                    'activity_date' => $date,
                    'lesson_completions' => 0,
                    'quiz_attempts' => 0,
                    'video_sessions' => 0,
                    'scorm_events' => 0,
                    'engagement_seconds' => 0,
                ]);

                $existing['lesson_completions'] += (int) ($payload['lesson_completions'] ?? 0);
                $existing['quiz_attempts'] += (int) ($payload['quiz_attempts'] ?? 0);
                $existing['video_sessions'] += (int) ($payload['video_sessions'] ?? 0);
                $existing['scorm_events'] += (int) ($payload['scorm_events'] ?? 0);
                $existing['engagement_seconds'] += (int) ($payload['engagement_seconds'] ?? 0);

                $activityByDate->put($date, $existing);
            }
        };

        $progressionRows = DB::table('progressions')
            ->join('module_lectures', 'module_lectures.id', '=', 'progressions.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->where('progressions.user_id', $userId)
            ->whereNotNull('progressions.completed_at')
            ->where('progressions.completed_at', '>=', $contextStartAt)
            ->selectRaw('DATE(progressions.completed_at) as activity_date, COUNT(*) as lesson_completions')
            ->groupBy(DB::raw('DATE(progressions.completed_at)'));
        $this->applyModuleFilter($progressionRows, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $mergeRows($progressionRows->get(), fn ($row) => [
            'activity_date' => $row->activity_date,
            'lesson_completions' => $row->lesson_completions,
        ]);

        $quizRows = DB::table('quiz_attempts')
            ->join('module_lectures', 'module_lectures.id', '=', 'quiz_attempts.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->where('quiz_attempts.user_id', $userId)
            ->whereRaw('COALESCE(quiz_attempts.finished_at, quiz_attempts.started_at, quiz_attempts.updated_at) >= ?', [$contextStartAt->toDateTimeString()])
            ->selectRaw('DATE(COALESCE(quiz_attempts.finished_at, quiz_attempts.started_at, quiz_attempts.updated_at)) as activity_date, COUNT(*) as quiz_attempts, SUM(quiz_attempts.total_time_seconds) as engagement_seconds')
            ->groupBy(DB::raw('DATE(COALESCE(quiz_attempts.finished_at, quiz_attempts.started_at, quiz_attempts.updated_at))'));
        $this->applyModuleFilter($quizRows, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $mergeRows($quizRows->get(), fn ($row) => [
            'activity_date' => $row->activity_date,
            'quiz_attempts' => $row->quiz_attempts,
            'engagement_seconds' => $row->engagement_seconds,
        ]);

        $videoRows = DB::table('video_segment_trackings')
            ->join('module_lectures', 'module_lectures.id', '=', 'video_segment_trackings.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->where('video_segment_trackings.user_id', $userId)
            ->where('video_segment_trackings.updated_at', '>=', $contextStartAt)
            ->selectRaw('DATE(video_segment_trackings.updated_at) as activity_date, COUNT(DISTINCT video_segment_trackings.lecture_id) as video_sessions, SUM(video_segment_trackings.total_watch_time) as engagement_seconds')
            ->groupBy(DB::raw('DATE(video_segment_trackings.updated_at)'));
        $this->applyModuleFilter($videoRows, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $mergeRows($videoRows->get(), fn ($row) => [
            'activity_date' => $row->activity_date,
            'video_sessions' => $row->video_sessions,
            'engagement_seconds' => $row->engagement_seconds,
        ]);

        $scormRows = DB::table('scorm_interactions')
            ->join('module_lectures', 'module_lectures.id', '=', 'scorm_interactions.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->where('scorm_interactions.user_id', $userId)
            ->where('scorm_interactions.updated_at', '>=', $contextStartAt)
            ->selectRaw('DATE(scorm_interactions.updated_at) as activity_date, COUNT(*) as scorm_events')
            ->groupBy(DB::raw('DATE(scorm_interactions.updated_at)'));
        $this->applyModuleFilter($scormRows, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $mergeRows($scormRows->get(), fn ($row) => [
            'activity_date' => $row->activity_date,
            'scorm_events' => $row->scorm_events,
        ]);

        return $activityByDate
            ->sortKeys()
            ->map(function (array $row) {
                $score = ((int) $row['lesson_completions'] * 4)
                    + ((int) $row['quiz_attempts'] * 3)
                    + ((int) $row['video_sessions'] * 2)
                    + min(5, (int) $row['scorm_events']);

                $row['activity_score'] = $score;
                $row['has_activity'] = $score > 0;

                return $row;
            });
    }

    private function buildUnifiedActivityFeed(
        int $userId,
        Carbon $contextStartAt,
        bool $restrictToSelectedGroup,
        array $moduleIds
    ): Collection {
        $feed = collect();

        $progressions = DB::table('progressions')
            ->join('module_lectures', 'module_lectures.id', '=', 'progressions.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->join('modules', 'modules.id', '=', 'module_sections.module_id')
            ->where('progressions.user_id', $userId)
            ->whereNotNull('progressions.completed_at')
            ->where('progressions.completed_at', '>=', $contextStartAt)
            ->select(
                'progressions.completed_at as activity_at',
                'module_lectures.lecture_title',
                'modules.module_title'
            )
            ->orderByDesc('progressions.completed_at')
            ->limit(12);
        $this->applyModuleFilter($progressions, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $feed = $feed->concat($progressions->get()->map(fn ($row) => (object) [
            'activity_at' => Carbon::parse($row->activity_at),
            'type' => 'lesson',
            'label' => 'Lecon terminee',
            'title' => $row->lecture_title ?: 'Lecon supprimee',
            'module_title' => $row->module_title ?: 'Module inconnu',
            'detail' => 'Lecon validee et marquee comme terminee.',
            'metric' => null,
        ]));

        $quizAttempts = DB::table('quiz_attempts')
            ->join('module_lectures', 'module_lectures.id', '=', 'quiz_attempts.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->join('modules', 'modules.id', '=', 'module_sections.module_id')
            ->where('quiz_attempts.user_id', $userId)
            ->whereRaw('COALESCE(quiz_attempts.finished_at, quiz_attempts.started_at, quiz_attempts.updated_at) >= ?', [$contextStartAt->toDateTimeString()])
            ->selectRaw('COALESCE(quiz_attempts.finished_at, quiz_attempts.started_at, quiz_attempts.updated_at) as activity_at, module_lectures.lecture_title, modules.module_title, quiz_attempts.percent, quiz_attempts.passed, quiz_attempts.total_questions')
            ->orderByDesc('activity_at')
            ->limit(12);
        $this->applyModuleFilter($quizAttempts, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $feed = $feed->concat($quizAttempts->get()->map(fn ($row) => (object) [
            'activity_at' => Carbon::parse($row->activity_at),
            'type' => 'quiz',
            'label' => 'Quiz',
            'title' => $row->lecture_title ?: 'Quiz',
            'module_title' => $row->module_title ?: 'Module inconnu',
            'detail' => $row->passed
                ? 'Quiz valide avec succes.'
                : 'Tentative de quiz en cours ou a reprendre.',
            'metric' => is_null($row->percent) ? null : ((int) $row->percent) . '%',
        ]));

        $videos = DB::table('video_segment_trackings')
            ->join('module_lectures', 'module_lectures.id', '=', 'video_segment_trackings.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->join('modules', 'modules.id', '=', 'module_sections.module_id')
            ->where('video_segment_trackings.user_id', $userId)
            ->where('video_segment_trackings.updated_at', '>=', $contextStartAt)
            ->selectRaw('MAX(video_segment_trackings.updated_at) as activity_at, module_lectures.lecture_title, modules.module_title, SUM(video_segment_trackings.total_watch_time) as watch_time')
            ->groupBy('video_segment_trackings.lecture_id', 'module_lectures.lecture_title', 'modules.module_title', DB::raw('DATE(video_segment_trackings.updated_at)'))
            ->orderByDesc('activity_at')
            ->limit(12);
        $this->applyModuleFilter($videos, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $feed = $feed->concat($videos->get()->map(fn ($row) => (object) [
            'activity_at' => Carbon::parse($row->activity_at),
            'type' => 'video',
            'label' => 'Video',
            'title' => $row->lecture_title ?: 'Video',
            'module_title' => $row->module_title ?: 'Module inconnu',
            'detail' => 'Visionnage trace sur cette lecon.',
            'metric' => gmdate('H\hi', (int) round($row->watch_time)),
        ]));

        $scorm = DB::table('scorm_interactions')
            ->join('module_lectures', 'module_lectures.id', '=', 'scorm_interactions.lecture_id')
            ->join('module_sections', 'module_sections.id', '=', 'module_lectures.section_id')
            ->join('modules', 'modules.id', '=', 'module_sections.module_id')
            ->where('scorm_interactions.user_id', $userId)
            ->where('scorm_interactions.updated_at', '>=', $contextStartAt)
            ->selectRaw('MAX(scorm_interactions.updated_at) as activity_at, module_lectures.lecture_title, modules.module_title, COUNT(*) as interactions_count')
            ->groupBy('scorm_interactions.lecture_id', 'module_lectures.lecture_title', 'modules.module_title', DB::raw('DATE(scorm_interactions.updated_at)'))
            ->orderByDesc('activity_at')
            ->limit(12);
        $this->applyModuleFilter($scorm, $restrictToSelectedGroup, $moduleIds, 'module_sections.module_id');
        $feed = $feed->concat($scorm->get()->map(fn ($row) => (object) [
            'activity_at' => Carbon::parse($row->activity_at),
            'type' => 'scorm',
            'label' => 'SCORM',
            'title' => $row->lecture_title ?: 'Activite SCORM',
            'module_title' => $row->module_title ?: 'Module inconnu',
            'detail' => 'Interactions detectees dans le contenu.',
            'metric' => (int) $row->interactions_count . ' actions',
        ]));

        return $feed
            ->sortByDesc('activity_at')
            ->take(25)
            ->values();
    }

    private function buildPresenceSummary(
        User $stagiaire,
        ?object $selectedGroup,
        Carbon $contextStartAt,
        Collection $dailyActivity
    ): array {
        $today = now()->startOfDay();
        $daysSinceStart = max(1, $contextStartAt->diffInDays($today) + 1);
        $activeDays = $dailyActivity
            ->filter(fn (array $day) => ! empty($day['has_activity']))
            ->keys()
            ->values();
        $activeDaysCount = $activeDays->count();

        $lastActivityAt = $this->resolveLastActivityAt($stagiaire->id, $activeDays);
        $inactivityDays = $lastActivityAt ? $lastActivityAt->copy()->startOfDay()->diffInDays($today) : null;

        $last14DaysActive = $activeDays->filter(fn ($date) => Carbon::parse($date)->greaterThanOrEqualTo($today->copy()->subDays(13)))->count();
        $last28DaysActive = $activeDays->filter(fn ($date) => Carbon::parse($date)->greaterThanOrEqualTo($today->copy()->subDays(27)))->count();
        ['current' => $currentStreakDays, 'longest' => $longestStreakDays] = $this->calculateStreaks($activeDays);

        $activityRate = (int) round(($activeDaysCount / $daysSinceStart) * 100);
        $risk = $this->assessDropoutRisk(
            $daysSinceStart,
            $activeDaysCount,
            $inactivityDays,
            $last14DaysActive,
            $last28DaysActive
        );

        return [
            'context_type' => $selectedGroup ? 'group' : 'account',
            'context_name' => $selectedGroup?->name,
            'started_at' => $contextStartAt,
            'days_since_start' => $daysSinceStart,
            'active_days_count' => $activeDaysCount,
            'activity_rate' => $activityRate,
            'current_streak_days' => $currentStreakDays,
            'longest_streak_days' => $longestStreakDays,
            'last_14_days_active' => $last14DaysActive,
            'last_28_days_active' => $last28DaysActive,
            'last_activity_at' => $lastActivityAt,
            'inactivity_days' => $inactivityDays,
            'site_time_total' => (int) ($stagiaire->total_site_time ?? 0),
            'risk' => $risk,
        ];
    }

    private function resolveLastActivityAt(int $userId, Collection $activeDays): ?Carbon
    {
        $lastDetectedDate = $activeDays->isNotEmpty()
            ? Carbon::parse((string) $activeDays->last())->endOfDay()
            : null;

        $sessionTimestamp = DB::table('sessions')
            ->where('user_id', $userId)
            ->max('last_activity');

        $sessionLastActivityAt = $sessionTimestamp
            ? Carbon::createFromTimestamp((int) $sessionTimestamp)
            : null;

        return collect([$lastDetectedDate, $sessionLastActivityAt])
            ->filter()
            ->sortByDesc(fn (Carbon $date) => $date->timestamp)
            ->first();
    }

    private function calculateStreaks(Collection $activeDays): array
    {
        if ($activeDays->isEmpty()) {
            return ['current' => 0, 'longest' => 0];
        }

        $dates = $activeDays
            ->map(fn ($date) => Carbon::parse((string) $date)->startOfDay())
            ->sort()
            ->values();

        $running = 0;
        $longest = 0;
        $previous = null;

        foreach ($dates as $date) {
            if ($previous && $date->diffInDays($previous) === 1) {
                $running++;
            } else {
                $running = 1;
            }

            $longest = max($longest, $running);
            $previous = $date;
        }

        return [
            'current' => $running,
            'longest' => $longest,
        ];
    }

    private function assessDropoutRisk(
        int $daysSinceStart,
        int $activeDaysCount,
        ?int $inactivityDays,
        int $last14DaysActive,
        int $last28DaysActive
    ): array {
        if ($activeDaysCount === 0) {
            return [
                'level' => 'critical',
                'label' => 'Risque eleve',
                'reason' => "Aucune activite detectee depuis l'entree dans le contexte suivi.",
            ];
        }

        if (! is_null($inactivityDays) && $inactivityDays >= 14) {
            return [
                'level' => 'critical',
                'label' => 'Risque eleve',
                'reason' => "Aucune activite recente depuis {$inactivityDays} jours.",
            ];
        }

        if ($daysSinceStart >= 14 && $activeDaysCount <= 1) {
            return [
                'level' => 'critical',
                'label' => 'Risque eleve',
                'reason' => 'Le stagiaire a tres peu demarre depuis son arrivee.',
            ];
        }

        if (! is_null($inactivityDays) && $inactivityDays >= 7) {
            return [
                'level' => 'warning',
                'label' => 'Vigilance',
                'reason' => "Le rythme a baisse : aucune activite depuis {$inactivityDays} jours.",
            ];
        }

        if ($daysSinceStart >= 21 && $last14DaysActive <= 2 && $last28DaysActive <= 4) {
            return [
                'level' => 'warning',
                'label' => 'A surveiller',
                'reason' => 'Presence irreguliere sur les deux a quatre dernieres semaines.',
            ];
        }

        return [
            'level' => 'good',
            'label' => 'Rythme stable',
            'reason' => 'Activite recente et presence suffisamment reguliere.',
        ];
    }

    private function buildTimelineWindow(Collection $dailyActivity, Carbon $contextStartAt): Collection
    {
        $windowStart = $contextStartAt->copy()->max(now()->subDays(55)->startOfDay());
        $windowEnd = now()->startOfDay();
        $timeline = collect();

        for ($cursor = $windowStart->copy(); $cursor->lte($windowEnd); $cursor->addDay()) {
            $key = $cursor->toDateString();
            $timeline->push(array_merge([
                'activity_date' => $key,
                'lesson_completions' => 0,
                'quiz_attempts' => 0,
                'video_sessions' => 0,
                'scorm_events' => 0,
                'engagement_seconds' => 0,
                'activity_score' => 0,
                'has_activity' => false,
            ], $dailyActivity->get($key, [])));
        }

        return $timeline;
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
