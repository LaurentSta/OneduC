<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Stagiaire/QuizController.php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use App\Models\ScormResult;
use App\Models\ScormScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class QuizController extends Controller
{
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
                        'time_seconds'=> 0, // Initialisation
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

        // --- 🟢 AJOUT : CHRONOMÈTRE DÉMARRÉ ---
        // On stocke l'heure précise d'affichage de la question pour calculer la durée ensuite
        session()->put("quiz_timer_{$attempt->id}_{$question->id}", now());
        // -------------------------------------

        $isFormateur = $this->isFormateur();
        $view = $isFormateur ? 'formateur.formations.quiz.question' : 'stagiaire.formations.quiz.question';

        return view($view, [
            'module'          => $module,
            'section'         => $section,
            'lecture'         => $lecture,
            'selectedLecture' => $lecture,
            'attempt'         => $attempt,
            'aq'              => $aq,
            'question'        => $question,
            'lectureStats'    => $lectureStats,
            'sectionStatuses' => $sectionStatuses,
            'contextQuery'    => $this->buildContextQuery(),
            'isFormateur'     => $isFormateur,
        ]);
    }

    /**
     * Enregistre la réponse puis redirige vers la question suivante ou le résultat.
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

        $selectedIds = collect();
        $isCorrect = false;
        $givenAnswer = null;

        if (in_array($question->type, ['single', 'boolean'], true)) {
            $request->validate([
                'answer' => ['required', 'integer'],
            ]);
            $selectedIds = collect([(int) $request->input('answer')]);

            $correctIds = $question->options
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->values();

            $isCorrect =
                $selectedIds->diff($correctIds)->isEmpty()
                && $correctIds->diff($selectedIds)->isEmpty();
        } elseif ((string) $question->type === 'multiple') {
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

            $correctIds = $question->options
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->values();

            $isCorrect =
                $selectedIds->diff($correctIds)->isEmpty()
                && $correctIds->diff($selectedIds)->isEmpty();
        } elseif ((string) $question->type === 'cloze') {
            $request->validate([
                'answers'   => ['required', 'array', 'min:1'],
                'answers.*' => ['nullable', 'string'],
            ]);

            $evaluation = $this->gradeClozeAnswer($question, (array) $request->input('answers', []));
            $isCorrect = (bool) $evaluation['is_correct'];
            $givenAnswer = [
                'type'      => 'cloze',
                'answers'   => $evaluation['answers'],
                'blanks'    => $evaluation['blanks'],
                'score'     => $evaluation['score'],
                'max_score' => $evaluation['max_score'],
                'percent'   => $evaluation['percent'],
            ];
        } else {
            abort(422, 'Type de question non pris en charge.');
        }

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
            if ($duration > 300) $duration = 300; 
        }
        // ------------------------------------------

        $updateAttemptQuestion = [
            'is_correct'   => (int) $isCorrect,
            'answered_at'  => now(),
            'time_seconds' => $duration, // Sera toujours >= 0 maintenant
        ];

        if ($this->attemptQuestionHasColumn('answer_option_ids')) {
            $updateAttemptQuestion['answer_option_ids'] = (string) $question->type === 'cloze'
                ? null
                : json_encode($selectedIds->all());
        }

        if ($this->attemptQuestionHasColumn('given_answer')) {
            $updateAttemptQuestion['given_answer'] = (string) $question->type === 'cloze'
                ? $givenAnswer
                : null;
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
        $currentIndexInSec = $sectionLectures->search(fn($l) => (int)$l->id === (int)$lecture->id);
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
                    'module'  => $module->id,
                    'section' => $nextLec->section_id,
                    'lecture' => $nextLec->id,
                ] + $contextQuery);
            } else {
                $nextUrl = route('formateur.formations.detail', ['module' => $module->id] + $contextQuery);
            }
        } else {
            // Logique Stagiaire : Chapitre par Chapitre
            if (!$isLastInSection) {
                // Il reste des leçons dans le chapitre actuel
                $nextLec = $sectionLectures->get($currentIndexInSec + 1);
                $nextUrl = route('stagiaire.module.lecture', [
                    'module'  => $module->id,
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
                        'module'  => $module->id,
                        'section' => $nextSection->id
                    ] + $contextQuery);
                } else {
                    // Fin du module complet
                    $nextUrl = route('stagiaire.module.fin', ['module' => $module->id] + $contextQuery);
                }
            }
        }

        $view = $isFormateur ? 'formateur.formations.quiz.result' : 'stagiaire.formations.quiz.result';

        return view($view, [
            'module'          => $module,
            'section'         => $section,
            'lecture'         => $lecture,
            'selectedLecture' => $lecture,
            'attempt'         => $attempt,
            'rows'            => $rows,
            'correctCount'    => $correctCount,
            'nextUrl'         => $nextUrl,
            'lectureStats'    => $lectureStats,
            'sectionStatuses' => $sectionStatuses,
            'contextQuery'    => $contextQuery,
            'isFormateur'     => $isFormateur,
        ]);
    }

    // ... (Restart, Assertions inchangés) ...
    public function restart(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        $this->assertLectureContext($module, $section, $lecture);
        $this->assertAttemptContext($attempt, $lecture);

        if (!$this->isFormateur()) {
            abort_if(is_null($attempt->finished_at), 403);
        }

        return redirect()->to(
            URL::signedRoute($this->routeName('quiz.start'), [
                'module'  => $module->id,
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
        $rows = $attempt->attemptQuestions()
            ->with('question')
            ->get();

        $earnedPoints = 0.0;
        $maxPoints = 0.0;

        foreach ($rows as $row) {
            $isCorrect = (bool) $row->is_correct;
            $type = (string) ($row->question->type ?? '');

            // Cloze: score en points partiels stocké dans given_answer.
            if ($type === 'cloze') {
                $given = is_array($row->given_answer ?? null) ? $row->given_answer : [];

                $rowScore = is_numeric($given['score'] ?? null)
                    ? (float) $given['score']
                    : ($isCorrect ? 1.0 : 0.0);

                $rowMax = is_numeric($given['max_score'] ?? null)
                    ? (float) $given['max_score']
                    : 1.0;

                if ($rowMax <= 0) {
                    $rowMax = 1.0;
                    $rowScore = $isCorrect ? 1.0 : 0.0;
                } else {
                    $rowScore = max(0.0, min($rowScore, $rowMax));
                }

                $earnedPoints += $rowScore;
                $maxPoints += $rowMax;
                continue;
            }

            // Types historiques: 1 point par question correcte.
            $earnedPoints += $isCorrect ? 1.0 : 0.0;
            $maxPoints += 1.0;
        }

        if ($maxPoints <= 0) {
            $maxPoints = 1.0;
        }

        $percent = (int) round(($earnedPoints / $maxPoints) * 100);

        // --- 🟢 AJOUT : SOMME DES TEMPS ---
        // On additionne le temps de toutes les questions pour avoir le temps total de la tentative
        $totalTime = $attempt->attemptQuestions()->sum('time_seconds');
        // ----------------------------------

        $payload = [
            'score'              => $percent,
            'percent'            => $percent,
            'passed'             => $percent >= 50 ? 1 : 0,
            'finished_at'        => now(),
            'total_time_seconds' => (int) $totalTime, // Sauvegarde du total
        ];

        $attempt->forceFill($payload)->save();
    }

    /**
     * Corrige une réponse "texte à trous" et retourne score + détail.
     */
    private function gradeClozeAnswer(QuizQuestion $question, array $submittedAnswers): array
    {
        $payload = is_array($question->payload ?? null) ? $question->payload : [];
        $blanks = is_array($payload['blanks'] ?? null) ? $payload['blanks'] : [];

        if (empty($blanks)) {
            abort(422, 'Cette question à trous est mal configurée: aucun blank défini.');
        }

        $score = 0;
        $maxScore = 0;
        $details = [];
        $cleanAnswers = [];

        foreach ($blanks as $blankKey => $config) {
            $key = trim((string) $blankKey);
            if ($key === '') {
                continue;
            }

            $acceptedRaw = $config['accepted_answers'] ?? [];
            if (!is_array($acceptedRaw)) {
                $acceptedRaw = [$acceptedRaw];
            }

            $accepted = collect($acceptedRaw)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();

            $normalizedAccepted = collect($accepted)
                ->map(fn ($value) => $this->normalizeClozeText($value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $points = is_numeric($config['points'] ?? null) ? (int) $config['points'] : 1;
            $points = max(0, $points);
            $maxScore += $points;

            $answerRaw = $submittedAnswers[$key] ?? '';
            $answerText = is_scalar($answerRaw) ? trim((string) $answerRaw) : '';
            $answerNormalized = $this->normalizeClozeText($answerText);

            $cleanAnswers[$key] = $answerText;

            $isBlankCorrect = $answerNormalized !== '' && in_array($answerNormalized, $normalizedAccepted, true);
            $earned = $isBlankCorrect ? $points : 0;
            $score += $earned;

            $details[$key] = [
                'answer'            => $answerText,
                'normalized_answer' => $answerNormalized,
                'accepted_answers'  => $accepted,
                'is_correct'        => $isBlankCorrect,
                'points'            => $points,
                'earned_points'     => $earned,
            ];
        }

        if ($maxScore <= 0) {
            $maxScore = 1;
        }

        $allCorrect = !empty($details)
            && collect($details)->every(fn ($blank) => (bool) ($blank['is_correct'] ?? false));

        return [
            'answers'    => $cleanAnswers,
            'blanks'     => $details,
            'score'      => $score,
            'max_score'  => $maxScore,
            'percent'    => (int) round(($score / $maxScore) * 100),
            'is_correct' => $allCorrect,
        ];
    }

    /**
     * Normalisation tolérante pour comparer les réponses texte:
     * - trim
     * - minuscules
     * - accents retirés
     * - espaces multiples compressés
     */
    private function normalizeClozeText(?string $value): string
    {
        $out = trim((string) $value);
        if ($out === '') {
            return '';
        }

        $out = preg_replace('/\s+/u', ' ', $out) ?? $out;
        $out = Str::lower($out);

        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($out, \Normalizer::FORM_D);
            if (is_string($normalized) && $normalized !== '') {
                $out = preg_replace('/\p{Mn}+/u', '', $normalized) ?? $normalized;
            }
        } else {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $out);
            if (is_string($ascii) && $ascii !== '') {
                $out = Str::lower($ascii);
            }
        }

        return trim($out);
    }

    // ... (Reste des méthodes privées : attemptCreatePayload, attemptHasColumn, etc. inchangées) ...
    private function attemptCreatePayload(ModuleLecture $lecture): array
    {
        return [
            'user_id'              => auth()->id(),
            'lecture_id'           => $lecture->id,
            'started_at'           => now(),
            'finished_at'          => null,
            'total_questions'      => (int) $lecture->quiz_questions_per_attempt,
            'score'                => 0,
            'percent'              => 0,
            'passed'               => 0,
            'total_time_seconds'   => 0,
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
            'module'  => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'attempt' => $attempt->id,
        ] + $this->buildContextQuery());
    }

    private function redirectResult(Module $module, ModuleSection $section, ModuleLecture $lecture, QuizAttempt $attempt)
    {
        return redirect()->route($this->routeName('lesson.quiz.result'), [
            'module'  => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'attempt' => $attempt->id,
        ] + $this->buildContextQuery());
    }

    private function routeName(string $suffix): string
    {
        $prefix = $this->isFormateur() ? 'formateur' : 'stagiaire';
        $name = $prefix . '.' . $suffix;

        if (!Route::has($name)) {
            $fallback = 'stagiaire.' . $suffix;
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
        if (!in_array($mode, ['groupe', 'officiel'], true)) {
            $mode = null;
        }

        $groupIdRaw = request()->query('group_id');
        $groupId = is_numeric($groupIdRaw) ? (int) $groupIdRaw : null;

        return array_filter([
            'mode'           => $mode,
            'group_id'       => $groupId ?: null,
            'include_hidden' => request()->boolean('include_hidden') ? 1 : null,
            'anonymous'      => request()->boolean('anonymous') ? 1 : null,
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

    private function buildLectureStats($lectures, int $userId): array
    {
        // (Copiez votre méthode buildLectureStats existante ici, elle ne change pas pour le timer)
        // ... Code existant ...
        $lectureIds = $lectures->pluck('id')->all();

        $attempts = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('lecture_id')
            ->map(fn ($rows) => $rows->first());

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
            if ((bool) ($lec->quiz_enabled ?? false)) {
                $attempt = $attempts->get($lec->id);
                $planned = (int) ($lec->quiz_questions_per_attempt ?? 0);

                if (!$attempt) {
                    $stats[$lec->id] = [
                        'status'             => 'not_started',
                        'quiz'               => true,
                        'questions_total'    => $planned,
                        'questions_answered' => 0,
                        'questions_correct'  => 0,
                        'quiz_score'         => null,
                        'quiz_finished'      => false,
                        'slides'             => (int) ($lec->slide_count ?? 0),
                        'session_time'       => null,
                    ];
                    continue;
                }

                $agg      = $attemptAgg->get($attempt->id);
                $total    = (int) ($agg->total ?? $attempt->total_questions ?? $planned ?? 0);
                $answered = (int) ($agg->answered ?? 0);
                $correct  = (int) ($agg->correct ?? 0);
                $score    = !is_null($attempt->percent)
                    ? (int) $attempt->percent
                    : (($total > 0) ? (int) round(($correct / $total) * 100) : null);
                $finished = !is_null($attempt->finished_at);

                if (!$finished) {
                    $status = $answered > 0 ? 'in_progress' : 'not_started';
                } else {
                    $status = ($score !== null && $score >= 50) ? 'completed' : 'failed';
                }

                $stats[$lec->id] = [
                    'status'             => $status,
                    'quiz'               => true,
                    'questions_total'    => $total,
                    'questions_answered' => $answered,
                    'questions_correct'  => $correct,
                    'quiz_score'         => $score,
                    'quiz_finished'      => $finished,
                    'slides'             => (int) ($lec->slide_count ?? 0),
                    'session_time'       => null,
                ];
                continue;
            }

            // Sinon : SCORM
            $hasStarted   = (int) ($started[$lec->id] ?? 0) > 0;
            $sc           = $scores->get($lec->id);
            $lessonStatus = strtolower((string) ($sc->lesson_status ?? ''));
            $isCompleted  = in_array($lessonStatus, ['completed', 'passed'], true) || (bool) ($sc->is_completed ?? false);

            if (!$hasStarted) $status = 'not_started';
            elseif ($isCompleted) $status = 'completed';
            else $status = 'in_progress';

            $stats[$lec->id] = [
                'status'             => $status,
                'quiz'               => false,
                'questions_total'    => 0,
                'questions_answered' => 0,
                'questions_correct'  => 0,
                'quiz_score'         => null,
                'quiz_finished'      => false,
                'slides'             => (int) ($lec->slide_count ?? 0),
                'session_time'       => $sc->session_time ?? null,
            ];
        }

        return $stats;
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
