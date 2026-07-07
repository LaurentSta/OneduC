<?php

// /home/laurents/Oneduc_Dev/app/Http/Controllers/Stagiaire/QuizController.php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Concerns\InteractsWithLectureProgressStats;
use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class QuizController extends Controller
{
    use InteractsWithLectureProgressStats;

    public function __construct(
        private readonly QuizService $quizService
    ) {}

    /**
     * Lance le quiz depuis une leçon (URL signée).
     * - Stagiaire : reprend la dernière tentative non terminée (finished_at NULL), sauf si restart=1.
     * - Formateur : rejoue sans limite => force toujours une nouvelle tentative.
     */
    public function start(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture)
    {
        $this->assertLectureContext($module, $section, $lecture);

        abort_unless((bool) $lecture->quiz_enabled, 403);
        abort_unless((int) $lecture->quiz_questions_per_attempt > 0, 403);

        $perAttempt = (int) $lecture->quiz_questions_per_attempt;

        $bankCount = QuizQuestion::query()
            ->where('lecture_id', $lecture->id)
            ->where('is_active', true)
            ->count();

        if ($bankCount < $perAttempt) {
            return back()->withErrors([
                'quiz' => 'Banque insuffisante : ajoutez des questions avant de lancer le quiz.',
            ]);
        }

        // Formateur : toujours une nouvelle tentative (rejouer sans limite)
        $forceNew = $this->isFormateur() ? true : (bool) $request->boolean('restart');

        // Formateur : éviter d’empiler des tentatives en cours
        if ($this->isFormateur()) {
            QuizAttempt::query()
                ->where('user_id', auth()->id())
                ->where('lecture_id', $lecture->id)
                ->whereNull('finished_at')
                ->update(['finished_at' => now()]);
        }

        $attempt = null;

        // Reprise (stagiaire uniquement)
        if (! $forceNew) {
            $attempt = QuizAttempt::query()
                ->where('user_id', auth()->id())
                ->where('lecture_id', $lecture->id)
                ->whereNull('finished_at')
                ->latest('id')
                ->first();
        }

        if (! $attempt) {
            $attempt = DB::transaction(function () use ($lecture, $perAttempt) {
                $attempt = QuizAttempt::create($this->attemptCreatePayload($lecture));

                $picked = QuizQuestion::query()
                    ->where('lecture_id', $lecture->id)
                    ->where('is_active', true)
                    ->inRandomOrder()
                    ->take($perAttempt)
                    ->get();

                foreach ($picked as $i => $q) {
                    QuizAttemptQuestion::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $q->id,
                        'position' => $i + 1,
                        'time_seconds' => 0, // Initialisation
                    ]);
                }

                return $attempt;
            });
        }

        return $this->redirectQuestion($module, $section, $lecture, $attempt);
    }

    /**
     * Affiche la question courante.
     */
    public function showQuestion(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);

        // Charger sections + lectures pour la sidebar
        $module->load([
            'sections' => function ($q) {
                $q->orderBy('id')
                    ->with(['lectures' => function ($qq) {
                        $qq->orderBy('position')->orderBy('id');
                    }]);
            },
        ]);

        $groupId = $this->isFormateur()
            ? null
            : $this->resolveGroupIdForUserAndModule((int) auth()->id(), (int) $module->id);

        $this->applyGroupLessonOverrides($module, $groupId);

        $section = $module->sections->firstWhere('id', (int) $section->id);
        abort_unless($section, 404);

        $visibleIds = $module->sections->flatMap(fn ($s) => $s->lectures)->pluck('id')->all();
        abort_unless(in_array((int) $lecture->id, $visibleIds, true), 404);

        $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();
        $lectureStats = $this->buildLectureStats($lectures, (int) auth()->id());
        $sectionStatuses = $this->computeSectionStatuses($module, $lectureStats);

        if (! is_null($attempt->finished_at)) {
            return $this->redirectResult($module, $section, $lecture, $attempt);
        }

        $aq = $attempt->attemptQuestions()
            ->with(['question.options' => fn ($q) => $q->orderBy('position')])
            ->whereNull('answered_at')
            ->orderBy('position')
            ->first();

        if (! $aq) {
            $this->finalizeAttempt($attempt);

            return $this->redirectResult($module, $section, $lecture, $attempt);
        }

        $question = $aq->question;

        // --- 🟢 AJOUT : CHRONOMÈTRE DÉMARRÉ ---
        // On stocke l'heure précise d'affichage de la question pour calculer la durée ensuite
        session()->put("quiz_timer_{$attempt->id}_{$question->id}", now());
        // -------------------------------------

        $isFormateur = $this->isFormateur();
        $view = $isFormateur ? 'formateur.formations.quiz.question' : 'stagiaire.formations.quiz.question';

        return view($view, [
            'module' => $module,
            'section' => $section,
            'lecture' => $lecture,
            'selectedLecture' => $lecture,
            'attempt' => $attempt,
            'aq' => $aq,
            'question' => $question,
            'lectureStats' => $lectureStats,
            'sectionStatuses' => $sectionStatuses,
            'contextQuery' => $this->buildContextQuery(),
            'isFormateur' => $isFormateur,
        ]);
    }

    /**
     * Enregistre la réponse puis redirige vers la question suivante ou le résultat.
     */
    public function answer(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);

        abort_if(! is_null($attempt->finished_at), 403);

        $current = $attempt->attemptQuestions()
            ->whereNull('answered_at')
            ->orderBy('position')
            ->firstOrFail();

        $question = QuizQuestion::query()
            ->with(['options' => fn ($q) => $q->orderBy('position')])
            ->findOrFail($current->question_id);

        if (in_array($question->type, ['single', 'boolean'], true)) {
            $request->validate([
                'answer' => ['required', 'integer'],
            ], [
                'answer.required' => 'Veuillez répondre en sélectionnant une seule réponse avant de valider.',
            ]);
        } elseif ((string) $question->type === 'multiple') {
            $request->validate([
                'answers' => ['required', 'array', 'min:1'],
                'answers.*' => ['integer'],
            ], [
                'answers.required' => 'Veuillez sélectionner au moins une réponse avant de valider.',
                'answers.min' => 'Veuillez sélectionner au moins une réponse avant de valider.',
            ]);
        } elseif ((string) $question->type === 'cloze') {
            $request->validate([
                'answers' => ['required', 'array', 'min:1'],
                'answers.*' => ['nullable', 'string'],
            ], [
                'answers.required' => 'Veuillez compléter les champs avant de valider.',
                'answers.min' => 'Veuillez compléter les champs avant de valider.',
            ]);
        } else {
            abort(422, 'Type de question non pris en charge.');
        }

        $graded = $this->quizService->gradeAnswer($question, $request->all());

        // --- 🟢 CORRECTION DU CALCUL DU TEMPS (Timer) ---
        $sessionKey = "quiz_timer_{$attempt->id}_{$question->id}";
        $startTime = session()->get($sessionKey);
        $duration = 0;

        if ($startTime) {
            // On utilise abs() pour forcer un nombre positif même si l'horloge dérive
            $duration = (int) abs(now()->diffInSeconds($startTime));
            session()->forget($sessionKey); // Nettoyage
        } else {
            // Fallback (si la session a expiré ou F5)
            $lastActivity = $attempt->updated_at ?? $attempt->started_at;
            // Là aussi, on sécurise avec abs()
            $duration = (int) abs(now()->diffInSeconds($lastActivity));
            // On plafonne à 5min (300s) pour éviter les valeurs folles
            if ($duration > 300) {
                $duration = 300;
            }
        }
        // ------------------------------------------

        $updateAttemptQuestion = [
            'is_correct' => (int) ($graded['is_correct'] ?? false),
            'answered_at' => now(),
            'time_seconds' => $duration, // Sera toujours >= 0 maintenant
        ];

        if ($this->attemptQuestionHasColumn('answer_option_ids')) {
            $updateAttemptQuestion['answer_option_ids'] = is_array($graded['answer_option_ids'] ?? null)
                ? json_encode(array_values($graded['answer_option_ids']))
                : null;
        }

        if ($this->attemptQuestionHasColumn('given_answer')) {
            $updateAttemptQuestion['given_answer'] = $graded['given_answer'] ?? null;
        }

        $current->update($updateAttemptQuestion);

        // On touche aussi le updated_at de la tentative pour le fallback de la question suivante
        $attempt->touch();

        $hasPending = $attempt->attemptQuestions()->whereNull('answered_at')->exists();
        if ($hasPending) {
            return $this->redirectQuestion($module, $section, $lecture, $attempt);
        }

        $this->finalizeAttempt($attempt);

        return $this->redirectResult($module, $section, $lecture, $attempt);
    }

    // ... (Méthode result() inchangée) ...
    public function result(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        // (Copier le contenu de votre méthode result() existante ici, elle ne change pas)
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);
        // ... chargements ...
        // Sidebar
        $module->load([
            'sections' => function ($q) {
                $q->orderBy('id')
                    ->with(['lectures' => function ($qq) {
                        $qq->orderBy('position')->orderBy('id');
                    }]);
            },
        ]);

        $groupId = $this->isFormateur()
            ? null
            : $this->resolveGroupIdForUserAndModule((int) auth()->id(), (int) $module->id);

        $this->applyGroupLessonOverrides($module, $groupId);

        $section = $module->sections->firstWhere('id', (int) $section->id);
        abort_unless($section, 404);

        $visibleIds = $module->sections->flatMap(fn ($s) => $s->lectures)->pluck('id')->all();
        abort_unless(in_array((int) $lecture->id, $visibleIds, true), 404);

        $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();
        $lectureStats = $this->buildLectureStats($lectures, (int) auth()->id());
        $sectionStatuses = $this->computeSectionStatuses($module, $lectureStats);

        // Finaliser si besoin
        if (is_null($attempt->finished_at)) {
            $hasPending = $attempt->attemptQuestions()->whereNull('answered_at')->exists();

            if ($hasPending) {
                return $this->redirectQuestion($module, $section, $lecture, $attempt);
            }

            $this->finalizeAttempt($attempt);
            $attempt->refresh();
        }

        $rows = $attempt->attemptQuestions()
            ->with(['question.options' => fn ($q) => $q->orderBy('position')])
            ->orderBy('position')
            ->get();

        $correctCount = $rows->where('is_correct', true)->count();

        // --- NOUVELLE LOGIQUE DE NAVIGATION PÉDAGOGIQUE ---

        // 1. Détecter la position de la leçon dans la section actuelle
        $sectionLectures = $section->lectures->sortBy('position')->values();
        $currentIndexInSec = $sectionLectures->search(fn ($l) => (int) $l->id === (int) $lecture->id);
        $isLastInSection = ($currentIndexInSec === $sectionLectures->count() - 1);

        $nextUrl = '#';

        $isFormateur = $this->isFormateur();
        $contextQuery = $this->buildContextQuery();

        if ($isFormateur) {
            // Logique formateur (inchangée)
            $allModuleLectures = $lectures;
            $globalIdx = $allModuleLectures->search(fn ($l) => (int) $l->id === (int) $lecture->id);
            $nextLec = ($globalIdx !== false) ? $allModuleLectures->get($globalIdx + 1) : null;

            if ($nextLec) {
                $nextUrl = route('formateur.formations.lecture', [
                    'module' => $module->id,
                    'section' => $nextLec->section_id,
                    'lecture' => $nextLec->id,
                ] + $contextQuery);
            } else {
                $nextUrl = route('formateur.formations.detail', ['module' => $module->id] + $contextQuery);
            }
        } else {
            // Logique Stagiaire : Chapitre par Chapitre
            if (! $isLastInSection) {
                // Il reste des leçons dans le chapitre actuel
                $nextLec = $sectionLectures->get($currentIndexInSec + 1);
                $nextUrl = route('stagiaire.module.lecture', [
                    'module' => $module->id,
                    'section' => $section->id,
                    'lecture' => $nextLec->id,
                ] + $contextQuery);
            } else {
                // C'est la dernière leçon du chapitre -> On cherche la SECTION suivante
                $nextSection = $module->sections
                    ->filter(fn ($candidate) => (int) $candidate->id > (int) $section->id)
                    ->sortBy('id')
                    ->first(fn ($candidate) => $candidate->lectures->isNotEmpty());

                if ($nextSection) {
                    // Redirection vers la page de garde du chapitre suivant (Objectifs)
                    $nextUrl = route('stagiaire.module.section', [
                        'module' => $module->id,
                        'section' => $nextSection->id,
                    ] + $contextQuery);
                } else {
                    // Fin du module complet
                    $nextUrl = route('stagiaire.module.fin', ['module' => $module->id] + $contextQuery);
                }
            }
        }

        $view = $isFormateur ? 'formateur.formations.quiz.result' : 'stagiaire.formations.quiz.result';

        return view($view, [
            'module' => $module,
            'section' => $section,
            'lecture' => $lecture,
            'selectedLecture' => $lecture,
            'attempt' => $attempt,
            'rows' => $rows,
            'correctCount' => $correctCount,
            'nextUrl' => $nextUrl,
            'lectureStats' => $lectureStats,
            'sectionStatuses' => $sectionStatuses,
            'contextQuery' => $contextQuery,
            'isFormateur' => $isFormateur,
        ]);
    }

    // ... (Restart, Assertions inchangés) ...
    public function restart(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);

        if (! $this->isFormateur()) {
            abort_if(is_null($attempt->finished_at), 403);
        }

        return redirect()->to(
            URL::signedRoute($this->routeName('quiz.start'), [
                'module' => $module->id,
                'section' => $section->id,
                'lecture' => $lecture->id,
                'restart' => 1,
            ] + $this->buildContextQuery())
        );
    }

    private function assertLectureContext(Module $module, ModuleSection $section, ModuleLecture $lecture): void
    {
        abort_unless((int) $lecture->module_id === (int) $module->id, 404);
        abort_unless((int) $lecture->section_id === (int) $section->id, 404);
    }

    private function assertAttemptContext(QuizAttempt $attempt, ModuleLecture $lecture): void
    {
        abort_if((int) $attempt->user_id !== (int) auth()->id(), 403);
        abort_unless((int) $attempt->lecture_id === (int) $lecture->id, 404);
    }

    /**
     * Finalise la tentative et calcule le temps TOTAL.
     */
    private function finalizeAttempt(QuizAttempt $attempt): void
    {
        $this->quizService->finalizeAttempt($attempt);
    }

    // ... (Reste des méthodes privées : attemptCreatePayload, attemptHasColumn, etc. inchangées) ...
    private function attemptCreatePayload(ModuleLecture $lecture): array
    {
        return [
            'user_id' => auth()->id(),
            'lecture_id' => $lecture->id,
            'started_at' => now(),
            'finished_at' => null,
            'total_questions' => (int) $lecture->quiz_questions_per_attempt,
            'score' => 0,
            'percent' => 0,
            'passed' => 0,
            'total_time_seconds' => 0,
        ];
    }

    private function attemptHasColumn(string $column): bool
    {
        return Schema::hasColumn((new QuizAttempt)->getTable(), $column);
    }

    private function attemptQuestionHasColumn(string $column): bool
    {
        return Schema::hasColumn((new QuizAttemptQuestion)->getTable(), $column);
    }

    private function redirectQuestion(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        return redirect()->route($this->routeName('lesson.quiz.question'), [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'attempt' => $attempt->id,
        ] + $this->buildContextQuery());
    }

    private function redirectResult(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        return redirect()->route($this->routeName('lesson.quiz.result'), [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'attempt' => $attempt->id,
        ] + $this->buildContextQuery());
    }

    private function routeName(string $suffix): string
    {
        $prefix = $this->isFormateur() ? 'formateur' : 'stagiaire';
        $name = $prefix.'.'.$suffix;

        if (! Route::has($name)) {
            $fallback = 'stagiaire.'.$suffix;
            if (Route::has($fallback)) {
                return $fallback;
            }
        }

        return $name;
    }

    private function isFormateur(): bool
    {
        return Auth::check() && Auth::user()->role === 'formateur';
    }

    private function buildContextQuery(): array
    {
        $mode = (string) request()->query('mode', '');
        if (! in_array($mode, ['groupe', 'officiel'], true)) {
            $mode = null;
        }

        $groupIdRaw = request()->query('group_id');
        $groupId = is_numeric($groupIdRaw) ? (int) $groupIdRaw : null;

        return array_filter([
            'mode' => $mode,
            'group_id' => $groupId ?: null,
            'include_hidden' => request()->boolean('include_hidden') ? 1 : null,
            'anonymous' => request()->boolean('anonymous') ? 1 : null,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    private function resolveGroupIdForUserAndModule(int $userId, int $moduleId): ?int
    {
        $gid = DB::table('group_user')
            ->join('group_module', 'group_module.group_id', '=', 'group_user.group_id')
            ->where('group_user.user_id', $userId)
            ->where('group_module.module_id', $moduleId)
            ->value('group_user.group_id');

        return $gid ? (int) $gid : null;
    }

    private function computeSectionStatuses(Module $module, array $lectureStats): array
    {
        $out = [];
        foreach ($module->sections as $sec) {
            $total = $sec->lectures->count();
            if ($total === 0) {
                $out[$sec->id] = 'not_started';

                continue;
            }
            $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                return in_array($lectureStats[$lec->id]['status'] ?? null, ['completed'], true);
            })->count();
            $out[$sec->id] = ($ok === $total) ? 'completed' : ($ok > 0 ? 'in_progress' : 'not_started');
        }

        return $out;
    }
}
