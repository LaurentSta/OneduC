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

        // Si tentative non finalisée mais plus de questions en attente, on finalise ici
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

        /**
         * Déterminer la "leçon suivante" avec la même logique que ton ModuleController@lire :
         * - d'abord l'ordre des sections
         * - puis l'ordre des leçons dans chaque section
         */
        $moduleOrdered = Module::with(['sections.lectures'])->findOrFail($module->id);

        $lectures = $moduleOrdered->sections
            ->flatMap(fn ($s) => $s->lectures)
            ->values();

        $currentIndex = $lectures->search(fn ($l) => (int) $l->id === (int) $lecture->id);
        $nextLecture  = ($currentIndex !== false) ? $lectures->get($currentIndex + 1) : null;

        // URL suivante (sans dépendre d'un nom de route)
        if ($nextLecture) {
            $nextUrl = url("/stagiaire/modules/{$module->id}/sections/{$nextLecture->section_id}/lessons/{$nextLecture->id}");
        } else {
            // Fin de module (ta route existante : /stagiaire/formations/{module}/fin)
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
}
