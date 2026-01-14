<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Backend/QuizQuestionController.php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ModuleLecture;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizQuestionController extends Controller
{
    /**
     * Liste des questions d’une leçon.
     */
    public function index(ModuleLecture $lecture)
    {
        $questions = QuizQuestion::query()
            ->where('lecture_id', $lecture->id)
            ->withCount('options')
            ->orderBy('created_at')
            ->get();

        return view('admin.backend.quiz.questions.index', [
            'lecture'   => $lecture,
            'questions' => $questions,
        ]);
    }

    /**
     * Formulaire de création d’une question.
     */
    public function create(ModuleLecture $lecture)
    {
        return view('admin.backend.quiz.questions.create', [
            'lecture' => $lecture,
        ]);
    }

    /**
     * Enregistrement d’une nouvelle question + ses options.
     */
    public function store(Request $request, ModuleLecture $lecture)
    {
        $data = $this->validatePayload($request);

        DB::transaction(function () use ($lecture, $data) {
            $question = QuizQuestion::create([
                'lecture_id'    => $lecture->id,
                'question_text' => $data['question_text'],
                'type'          => $data['type'],
                'is_active'     => $data['is_active'] ?? true,
            ]);

            $options = $this->buildOptionsForType($data['type'], $data['options'] ?? null);

            $this->assertOptionsAreValidForType($data['type'], $options);

            $this->replaceOptions($question->id, $options);
        });

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question créée avec succès.');
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(ModuleLecture $lecture, QuizQuestion $question)
    {
        abort_unless($question->lecture_id === $lecture->id, 404);

        // utile pour pré-remplir correctement l’édition
        $question->load(['options' => fn ($q) => $q->orderBy('position')]);

        return view('admin.backend.quiz.questions.edit', [
            'lecture'  => $lecture,
            'question' => $question,
        ]);
    }

    /**
     * Mise à jour de la question + ses options.
     */
    public function update(Request $request, ModuleLecture $lecture, QuizQuestion $question)
    {
        abort_unless($question->lecture_id === $lecture->id, 404);

        $data = $this->validatePayload($request);

        DB::transaction(function () use ($question, $data) {
            $question->update([
                'question_text' => $data['question_text'],
                'type'          => $data['type'],
                'is_active'     => $data['is_active'] ?? $question->is_active,
            ]);

            $options = $this->buildOptionsForType($data['type'], $data['options'] ?? null);

            $this->assertOptionsAreValidForType($data['type'], $options);

            $this->replaceOptions($question->id, $options);
        });

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question mise à jour.');
    }

    /**
     * Suppression.
     */
    public function destroy(ModuleLecture $lecture, QuizQuestion $question)
    {
        abort_unless($question->lecture_id === $lecture->id, 404);

        DB::transaction(function () use ($question) {
            QuizOption::where('question_id', $question->id)->delete();
            $question->delete();
        });

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question supprimée.');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function validatePayload(Request $request): array
    {
        // IMPORTANT : tes vues envoient options[*][text] + options[*][is_correct]
        // On accepte aussi option_text pour compatibilité si besoin.
        return $request->validate([
            'question_text'        => ['required', 'string'],
            'type'                 => ['required', 'in:boolean,single,multiple'],
            'is_active'            => ['nullable', 'boolean'],

            'options'              => ['nullable', 'array'],
            'options.*.text'       => ['nullable', 'string'],
            'options.*.option_text'=> ['nullable', 'string'],
            'options.*.is_correct' => ['nullable'],
        ]);
    }

    /**
     * Retourne une liste d’options normalisées :
     * [
     *   ['text' => '...', 'is_correct' => 0|1],
     *   ...
     * ]
     */
    private function buildOptionsForType(string $type, ?array $rawOptions): array
    {
        if ($type === 'boolean') {
            return [
                ['text' => 'Vrai', 'is_correct' => 1],
                ['text' => 'Faux', 'is_correct' => 0],
            ];
        }

        $rawOptions = $rawOptions ?? [];

        // Normaliser (text peut venir de text ou option_text)
        $options = [];
        foreach ($rawOptions as $o) {
            $text = trim((string)($o['text'] ?? $o['option_text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $options[] = [
                'text'       => $text,
                'is_correct' => (int)($o['is_correct'] ?? 0) === 1 ? 1 : 0,
            ];
        }

        return array_values($options);
    }

    private function assertOptionsAreValidForType(string $type, array $options): void
    {
        if ($type === 'boolean') {
            return; // on force Vrai/Faux
        }

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => 'Ajoutez au moins 2 propositions de réponses.',
            ]);
        }

        $correctCount = collect($options)->sum(fn ($o) => (int)$o['is_correct']);
        if ($correctCount < 1) {
            throw ValidationException::withMessages([
                'options' => 'Indiquez au moins une bonne réponse.',
            ]);
        }

        if ($type === 'single' && $correctCount !== 1) {
            throw ValidationException::withMessages([
                'options' => 'En choix unique, il doit y avoir exactement 1 bonne réponse.',
            ]);
        }
    }

    private function replaceOptions(int $questionId, array $options): void
    {
        QuizOption::where('question_id', $questionId)->delete();

        foreach ($options as $i => $opt) {
            QuizOption::create([
                'question_id' => $questionId,
                'option_text' => $opt['text'],
                'is_correct'  => (int)$opt['is_correct'],
                'position'    => $i + 1,
            ]);
        }
    }
}
