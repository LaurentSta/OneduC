<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleSection;
use App\Models\ModuleLecture;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function start(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture)
    {
        // Sécurité d’appartenance (cohérence URL)
        abort_unless($lecture->module_id === $module->id, 404);
        abort_unless($lecture->section_id === $section->id, 404);

        // Quiz activé + paramétré
        abort_unless($lecture->quiz_enabled && $lecture->quiz_questions_per_attempt > 0, 403);

        $bankCount = QuizQuestion::where('lecture_id', $lecture->id)->where('is_active', true)->count();
        if ($bankCount < $lecture->quiz_questions_per_attempt) {
            return back()->withErrors(['quiz' => 'Banque insuffisante : ajoutez des questions avant de lancer le quiz.']);
        }

        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'lecture_id' => $lecture->id,
            'started_at' => now(),
            'total_questions' => $lecture->quiz_questions_per_attempt,
        ]);

        $picked = QuizQuestion::where('lecture_id', $lecture->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->take($lecture->quiz_questions_per_attempt)
            ->get();

        foreach ($picked as $i => $q) {
            QuizAttemptQuestion::create([
                'attempt_id' => $attempt->id,
                'question_id' => $q->id,
                'position' => $i + 1,
            ]);
        }

        return redirect()->route('stagiaire.quiz.question', ['attempt' => $attempt->id]);
    }

    public function showQuestion(QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_if($attempt->finished_at, 403);

        $next = $attempt->questions()->whereNull('answered_at')->first();
        if (!$next) {
            return redirect()->route('stagiaire.quiz.result', ['attempt' => $attempt->id]);
        }

        // Démarre le chrono question (non contraignant, mais tracé) :contentReference[oaicite:15]{index=15}
        if (!$next->question_started_at) {
            $next->update(['question_started_at' => now()]);
        }

        $question = $next->question()->with('options')->first();

        return view('stagiaire.quiz.question', compact('attempt','next','question'));
    }

    public function answer(Request $request, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        abort_if($attempt->finished_at, 403);

        $current = $attempt->questions()->whereNull('answered_at')->firstOrFail();
        $question = QuizQuestion::with('options')->findOrFail($current->question_id);

        $payload = $request->validate([
            'selected' => 'nullable|array',
            'selected.*' => 'integer',
        ]);

        $selected = collect($payload['selected'] ?? [])->map(fn($v)=>(int)$v)->sort()->values()->all();
        $correct = collect($question->correctOptionIds())->sort()->values()->all();

        $isCorrect = ($selected === $correct);

        $timeSeconds = 0;
        if ($current->question_started_at) {
            $timeSeconds = now()->diffInSeconds($current->question_started_at);
        }

        $current->update([
            'answered_at' => now(),
            'time_seconds' => $timeSeconds,
            'answer_option_ids' => $selected ? json_encode($selected) : json_encode([]),
            'is_correct' => $isCorrect,
        ]);

        // Enchaîne automatiquement (pas de retour arrière) :contentReference[oaicite:16]{index=16}
        return redirect()->route('stagiaire.quiz.question', ['attempt' => $attempt->id]);
    }

    public function result(QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        if (!$attempt->finished_at) {
            $rows = $attempt->questions()->get();
            $score = $rows->where('is_correct', true)->count();
            $total = max(1, (int)$attempt->total_questions);
            $percent = (int) round(($score / $total) * 100);

            $totalTime = (int) $rows->sum('time_seconds');

            $passed = ($percent === 100); // seuil V1 :contentReference[oaicite:17]{index=17}

            $attempt->update([
                'finished_at' => now(),
                'score' => $score,
                'percent' => $percent,
                'passed' => $passed,
                'total_time_seconds' => $totalTime,
            ]);

            // Validation hiérarchique : réussite quiz => leçon validée :contentReference[oaicite:18]{index=18}
            if ($passed) {
                \App\Models\Progression::updateOrCreate(
                    ['user_id' => $attempt->user_id, 'lecture_id' => $attempt->lecture_id],
                    ['completed_at' => now()]
                );
            }
        }

        $attempt->load(['questions.question.options']);
        return view('stagiaire.quiz.result', compact('attempt'));
    }
}
