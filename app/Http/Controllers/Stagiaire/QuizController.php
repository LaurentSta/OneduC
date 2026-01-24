<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Stagiaire/QuizController.php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleSection;
use App\Models\ModuleLecture;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\GroupModuleLecture;
use App\Models\ScormResult;
use App\Models\ScormScore;
use Illuminate\Support\Facades\Auth;


class QuizController extends Controller
{
    /**
     * Lance le quiz depuis une leçon (URL signée).
     * - Reprend la dernière tentative non terminée (finished_at NULL) si elle existe.
     * - Sinon crée une nouvelle tentative + tire les questions.
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

        // Si on vient de "Recommencer", on force une nouvelle tentative
        $forceNew = (bool) $request->boolean('restart');

        $attempt = null;

        // Reprise uniquement si on ne force pas une nouvelle tentative
        if (!$forceNew) {
            $attempt = QuizAttempt::query()
                ->where('user_id', auth()->id())
                ->where('lecture_id', $lecture->id)
                ->whereNull('finished_at')
                ->latest('id')
                ->first();
        }

        if (!$attempt) {
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
                        'attempt_id'  => $attempt->id,
                        'question_id' => $q->id,
                        'position'    => $i + 1,
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

    // Appliquer overrides groupe (ordre + masquage)
    $groupId = $this->resolveGroupIdForUserAndModule((int) auth()->id(), (int) $module->id);
    $this->applyGroupLessonOverrides($module, $groupId);

    // Recaler la section depuis le module filtré
    $section = $module->sections->firstWhere('id', (int) $section->id);
    abort_unless($section, 404);

    // Bloquer l’accès si la leçon est masquée par la personnalisation
    $visibleIds = $module->sections->flatMap(fn ($s) => $s->lectures)->pluck('id')->all();
    abort_unless(in_array((int) $lecture->id, $visibleIds, true), 404);

    // Sidebar : stats + statuts sections
    $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();
    $lectureStats = $this->buildLectureStats($lectures, (int) auth()->id());
    $sectionStatuses = $this->computeSectionStatuses($module, $lectureStats);

    // Si tentative déjà finie -> résultat
    if (!is_null($attempt->finished_at)) {
        return $this->redirectResult($module, $section, $lecture, $attempt);
    }

    $aq = $attempt->attemptQuestions()
        ->with(['question.options' => fn ($q) => $q->orderBy('position')])
        ->whereNull('answered_at')
        ->orderBy('position')
        ->first();

    if (!$aq) {
        $this->finalizeAttempt($attempt);
        return $this->redirectResult($module, $section, $lecture, $attempt);
    }

    $question = $aq->question;

    return view('stagiaire.formations.quiz.question', [
        'module'          => $module,
        'section'         => $section,
        'lecture'         => $lecture,
        'selectedLecture' => $lecture,
        'attempt'         => $attempt,
        'aq'              => $aq,
        'question'        => $question,

        // Sidebar
        'lectureStats'    => $lectureStats,
        'sectionStatuses' => $sectionStatuses,
    ]);
}


    /**
     * Enregistre la réponse puis redirige vers la question suivante ou le résultat.
     * Règles :
     * - single/boolean : 1 radio obligatoire (name="answer")
     * - multiple : au moins 1 case cochée (name="answers[]")
     * Stockage :
     * - answer_option_ids = JSON des ids sélectionnés
     * - given_answer laissé à null
     */
    public function answer(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);

        abort_if(!is_null($attempt->finished_at), 403);

        $current = $attempt->attemptQuestions()
            ->whereNull('answered_at')
            ->orderBy('position')
            ->firstOrFail();

        $question = QuizQuestion::query()
            ->with(['options' => fn ($q) => $q->orderBy('position')])
            ->findOrFail($current->question_id);

        // Normaliser selon le type
        $selectedIds = collect();

        if (in_array($question->type, ['single', 'boolean'], true)) {
            $request->validate([
                'answer' => ['required', 'integer'],
            ]);
            $selectedIds = collect([(int) $request->input('answer')]);
        } elseif ($question->type === 'multiple') {
            $request->validate([
                'answers'   => ['required', 'array', 'min:1'],
                'answers.*' => ['integer'],
            ]);

            $selectedIds = collect($request->input('answers', []))
                ->flatten()
                ->filter()
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values();

            if ($selectedIds->isEmpty()) {
                return back()->withErrors(['answers' => 'Coche au moins une réponse avant de valider.'])->withInput();
            }
        } else {
            abort(422, 'Type de question non pris en charge.');
        }

        // Correction stricte : ensemble exact
        $correctIds = $question->options
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->values();

        $isCorrect =
            $selectedIds->diff($correctIds)->isEmpty()
            && $correctIds->diff($selectedIds)->isEmpty();

        $updateAttemptQuestion = [
            'is_correct'  => (int) $isCorrect,
            'answered_at' => now(),
        ];

        if ($this->attemptQuestionHasColumn('answer_option_ids')) {
            $updateAttemptQuestion['answer_option_ids'] = json_encode($selectedIds->all());
        }

        if ($this->attemptQuestionHasColumn('given_answer')) {
            $updateAttemptQuestion['given_answer'] = null;
        }

        $current->update($updateAttemptQuestion);

        $hasPending = $attempt->attemptQuestions()->whereNull('answered_at')->exists();
        if ($hasPending) {
            return $this->redirectQuestion($module, $section, $lecture, $attempt);
        }

        $this->finalizeAttempt($attempt);

        return $this->redirectResult($module, $section, $lecture, $attempt);
    }

    /**
 * Affiche le résultat final.
 */
    public function result(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
{
    $this->assertLectureContext($module, $section, $lecture);
    $this->assertAttemptContext($attempt, $lecture);

    // Charger sections + lectures pour sidebar + next
    $module->load([
        'sections' => function ($q) {
            $q->orderBy('id')
              ->with(['lectures' => function ($qq) {
                  $qq->orderBy('position')->orderBy('id');
              }]);
        },
    ]);

    // Appliquer overrides groupe
    $groupId = $this->resolveGroupIdForUserAndModule((int) auth()->id(), (int) $module->id);
    $this->applyGroupLessonOverrides($module, $groupId);

    // Recaler la section depuis le module filtré
    $section = $module->sections->firstWhere('id', (int) $section->id);
    abort_unless($section, 404);

    // Bloquer l’accès si le quiz est sur une leçon masquée
    $visibleIds = $module->sections->flatMap(fn ($s) => $s->lectures)->pluck('id')->all();
    abort_unless(in_array((int) $lecture->id, $visibleIds, true), 404);

    // Sidebar
    $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();
    $lectureStats = $this->buildLectureStats($lectures, (int) auth()->id());
    $sectionStatuses = $this->computeSectionStatuses($module, $lectureStats);

    // Si tentative non finalisée mais plus de questions en attente, on renvoie à la question
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

    // Leçon suivante sur l’ordre visible/personnalisé
    $currentIndex = $lectures->search(fn ($l) => (int) $l->id === (int) $lecture->id);
    $nextLecture  = ($currentIndex !== false) ? $lectures->get($currentIndex + 1) : null;

    if ($nextLecture) {
        $nextUrl = url("/stagiaire/modules/{$module->id}/sections/{$nextLecture->section_id}/lessons/{$nextLecture->id}");
    } else {
        $nextUrl = url("/stagiaire/formations/{$module->id}/fin");
    }

    return view('stagiaire.formations.quiz.result', [
        'module'          => $module,
        'section'         => $section,
        'lecture'         => $lecture,
        'selectedLecture' => $lecture,
        'attempt'         => $attempt,
        'rows'            => $rows,
        'correctCount'    => $correctCount,
        'nextUrl'         => $nextUrl,

        // Sidebar
        'lectureStats'    => $lectureStats,
        'sectionStatuses' => $sectionStatuses,
    ]);
}



    public function restart(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);

        abort_if(is_null($attempt->finished_at), 403);

        return redirect()->to(
            \Illuminate\Support\Facades\URL::signedRoute('stagiaire.quiz.start', [
                'module'  => $module->id,
                'section' => $section->id,
                'lecture' => $lecture->id,
                'restart' => 1,
            ])
        );
    }

    /**
     * Sécurité : module/section/lecture cohérents.
     */
    private function assertLectureContext(Module $module, ModuleSection $section, ModuleLecture $lecture): void
    {
        abort_unless((int) $lecture->module_id === (int) $module->id, 404);
        abort_unless((int) $lecture->section_id === (int) $section->id, 404);
    }

    /**
     * Sécurité : tentative appartient à l'utilisateur et à la leçon.
     */
    private function assertAttemptContext(QuizAttempt $attempt, ModuleLecture $lecture): void
    {
        abort_if((int) $attempt->user_id !== (int) auth()->id(), 403);
        abort_unless((int) $attempt->lecture_id === (int) $lecture->id, 404);
    }

    /**
     * Finalise la tentative.
     */
    private function finalizeAttempt(QuizAttempt $attempt): void
    {
        $correct = $attempt->attemptQuestions()->where('is_correct', true)->count();
        $total   = max(1, (int) $attempt->total_questions);

        $percent = (int) round(($correct / $total) * 100);

        $payload = [
            'score'       => $percent,
            'percent'     => $percent,
            'passed'      => $percent >= 50 ? 1 : 0,
            'finished_at' => now(),
        ];

        if ($this->attemptHasColumn('total_time_seconds') && is_null($attempt->total_time_seconds)) {
            $payload['total_time_seconds'] = 0;
        }

        $attempt->forceFill($payload)->save();
    }

    /**
     * Payload de création compatible avec ta table quiz_attempts.
     */
    private function attemptCreatePayload(ModuleLecture $lecture): array
    {
        return [
            'user_id'            => auth()->id(),
            'lecture_id'         => $lecture->id,
            'started_at'         => now(),
            'finished_at'        => null,
            'total_questions'    => (int) $lecture->quiz_questions_per_attempt,
            'score'              => 0,
            'percent'            => 0,
            'passed'             => 0,
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
        return redirect()->route('stagiaire.lesson.quiz.question', [
            'module'  => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'attempt' => $attempt->id,
        ]);
    }

    private function redirectResult(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        return redirect()->route('stagiaire.lesson.quiz.result', [
            'module'  => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'attempt' => $attempt->id,
        ]);
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

private function applyGroupLessonOverrides(Module $module, ?int $groupId): void
{
    if (!$groupId) return;

    $over = GroupModuleLecture::query()
        ->where('group_id', $groupId)
        ->where('module_id', $module->id)
        ->get()
        ->keyBy('lecture_id');

    if ($over->isEmpty()) return;

    $module->sections->each(function ($sec) use ($over) {
        $sec->setRelation('lectures', $sec->lectures
            ->filter(function ($lec) use ($over) {
                $row = $over->get($lec->id);
                return $row ? (bool) $row->is_enabled : true;
            })
            ->sortBy(function ($lec) use ($over) {
                $row = $over->get($lec->id);
                return $row ? (int) $row->position : (int) ($lec->position ?? 999999);
            })
            ->values()
        );
    });
}

/**
 * Reprend la même logique que ModuleController::buildLectureStats()
 * (quiz si activé, sinon statut “diapo” via SCORM).
 */
private function buildLectureStats($lectures, int $userId): array
{
    $lectureIds = $lectures->pluck('id')->all();

    $attempts = QuizAttempt::query()
        ->where('user_id', $userId)
        ->whereIn('lecture_id', $lectureIds)
        ->orderByDesc('finished_at')
        ->orderByDesc('id')
        ->get()
        ->groupBy('lecture_id')
        ->map(fn($rows) => $rows->first());

    $attemptIds = $attempts->filter()->pluck('id')->all();

    $attemptAgg = collect();
    if (!empty($attemptIds)) {
        $attemptAgg = QuizAttemptQuestion::query()
            ->select([
                'attempt_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN answered_at IS NOT NULL THEN 1 ELSE 0 END) as answered'),
                DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'),
            ])
            ->whereIn('attempt_id', $attemptIds)
            ->groupBy('attempt_id')
            ->get()
            ->keyBy('attempt_id');
    }

    $scores = ScormScore::query()
        ->where('user_id', $userId)
        ->whereIn('lecture_id', $lectureIds)
        ->get()
        ->keyBy('lecture_id');

    $started = ScormResult::query()
        ->where('user_id', $userId)
        ->whereIn('lecture_id', $lectureIds)
        ->select('lecture_id', DB::raw('COUNT(*) as c'))
        ->groupBy('lecture_id')
        ->pluck('c', 'lecture_id');

    $stats = [];

    foreach ($lectures as $lec) {

        // Quiz activé => sidebar sur quiz
        if ((bool) ($lec->quiz_enabled ?? false)) {
            $attempt = $attempts->get($lec->id);
            $planned = (int) ($lec->quiz_questions_per_attempt ?? 0);

            if (!$attempt) {
                $stats[$lec->id] = [
                    'status' => 'not_started',
                    'quiz' => true,
                    'questions_total' => $planned,
                    'questions_answered' => 0,
                    'questions_correct' => 0,
                    'quiz_score' => null,
                    'quiz_finished' => false,
                    'slides' => (int)($lec->slide_count ?? 0),
                    'session_time' => null,
                ];
                continue;
            }

            $agg = $attemptAgg->get($attempt->id);
            $total    = (int)($agg->total ?? $attempt->total_questions ?? $planned ?? 0);
            $answered = (int)($agg->answered ?? 0);
            $correct  = (int)($agg->correct ?? 0);
            $score    = ($total > 0) ? (int) round(($correct / $total) * 100) : null;
            $finished = !is_null($attempt->finished_at);

            if (!$finished) {
                $status = $answered > 0 ? 'in_progress' : 'not_started';
            } else {
                $status = ($score !== null && $score >= 50) ? 'completed' : 'failed';
            }

            $stats[$lec->id] = [
                'status' => $status,
                'quiz' => true,
                'questions_total' => $total,
                'questions_answered' => $answered,
                'questions_correct' => $correct,
                'quiz_score' => $score,
                'quiz_finished' => $finished,
                'slides' => (int)($lec->slide_count ?? 0),
                'session_time' => null,
            ];
            continue;
        }

        // Sinon : SCORM
        $hasStarted = (int)($started[$lec->id] ?? 0) > 0;
        $sc = $scores->get($lec->id);

        $lessonStatus = strtolower((string)($sc->lesson_status ?? ''));
        $isCompleted = in_array($lessonStatus, ['completed', 'passed'], true) || (bool)($sc->is_completed ?? false);

        if (!$hasStarted) $status = 'not_started';
        elseif ($isCompleted) $status = 'completed';
        else $status = 'in_progress';

        $stats[$lec->id] = [
            'status' => $status,
            'quiz' => false,
            'questions_total' => 0,
            'questions_answered' => 0,
            'questions_correct' => 0,
            'quiz_score' => null,
            'quiz_finished' => false,
            'slides' => (int)($lec->slide_count ?? 0),
            'session_time' => $sc->session_time ?? null,
        ];
    }

    return $stats;
}

private function hydrateModuleForSidebar(Module $module): Module
{
    // charge sections/leçons + ordre
    $module->load([
        'sections' => function ($q) {
            $q->orderBy('id')
              ->with(['lectures' => function ($qq) {
                  $qq->orderBy('position');
              }]);
        },
    ]);

    // applique overrides groupe
    $groupId = $this->resolveGroupIdForUserAndModule((int) auth()->id(), (int) $module->id);
    $this->applyGroupLessonOverrides($module, $groupId);

    return $module;
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
