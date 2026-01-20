<?php
// /var/www/Oneduc_Prod/app/Http/Controllers/Backend/QuizQuestionController.php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ModuleLecture;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class QuizQuestionController extends Controller
{
    /**
     * Liste des questions d’un quiz (par leçon).
     */
    public function index(ModuleLecture $lecture)
    {
        $questions = QuizQuestion::where('lecture_id', $lecture->id)
            ->with(['options' => fn ($q) => $q->orderBy('position')])
            ->orderBy('id')
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

        DB::transaction(function () use ($lecture, $data, $request) {
            $question = QuizQuestion::create([
                'lecture_id'    => $lecture->id,
                'question_text' => $data['question_text'],
                'type'          => $data['type'],
                'is_active'     => $data['is_active'] ?? true,
            ]);

            // Médias (image / audio) : stockage sur disque "public"
            // Médias (image / audio)
            $updates = [];

            // Suppression explicite image
            if ($request->boolean('remove_image')) {
                if (!empty($question->image_path)) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $updates['image_path'] = null;
                $updates['image_alt']  = null;
            } else {
                // Remplacement image
                if ($request->hasFile('image')) {
                    if (!empty($question->image_path)) {
                        Storage::disk('public')->delete($question->image_path);
                    }
                    $updates['image_path'] = $request->file('image')
                        ->store("quiz/questions/lecture_{$lecture->id}", 'public');

                    $updates['image_alt'] = $data['image_alt'] ?? null;
                } else {
                    // Mise à jour de l'alt seul (sans changer l'image)
                    if (array_key_exists('image_alt', $data)) {
                        $updates['image_alt'] = $data['image_alt'];
                    }
                }
            }

            // Remplacement audio
            if ($request->hasFile('audio')) {
                if (!empty($question->audio_path)) {
                    Storage::disk('public')->delete($question->audio_path);
                }
                $updates['audio_path'] = $request->file('audio')
                    ->store("quiz/questions/lecture_{$lecture->id}", 'public');
            }

            // Transcript audio : peut être modifié même sans nouvel audio
            if (array_key_exists('audio_transcript', $data)) {
                $updates['audio_transcript'] = $data['audio_transcript'];
            }

            if (!empty($updates)) {
                $question->update($updates);
            }


            $options = $this->buildOptionsForType($data['type'], $data['options'] ?? null);

            $this->assertOptionsAreValidForType($data['type'], $options);

            $this->replaceOptions($question->id, $options);
        });

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question créée avec succès.');
    }

    /**
     * Formulaire d’édition d’une question.
     */
    public function edit(ModuleLecture $lecture, QuizQuestion $question)
    {
        abort_unless($question->lecture_id === $lecture->id, 404);

        $question->load(['options' => fn ($q) => $q->orderBy('position')]);

        return view('admin.backend.quiz.questions.edit', [
            'lecture'  => $lecture,
            'question' => $question,
        ]);
    }

    /**
     * Mise à jour d’une question + options.
     */
    public function update(Request $request, ModuleLecture $lecture, QuizQuestion $question)
    {
        abort_unless($question->lecture_id === $lecture->id, 404);

        $data = $this->validatePayload($request);

        DB::transaction(function () use ($question, $lecture, $data, $request) {
            $question->update([
                'question_text' => $data['question_text'],
                'type'          => $data['type'],
                'is_active'     => $data['is_active'] ?? $question->is_active,
                
            ]);

            // Médias (image / audio) : remplacement si nouveau fichier
            $updates = [];
            // Suppression explicite image
            if ($request->boolean('remove_image')) {
                if (!empty($question->image_path)) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $updates['image_path'] = null;
                $updates['image_alt']  = null;
            }

            if ($request->hasFile('image')) {
                if (!empty($question->image_path)) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $updates['image_path'] = $request->file('image')
                    ->store("quiz/questions/lecture_{$lecture->id}", 'public');
            }
            if (!$request->boolean('remove_image')) {
                $updates['image_alt'] = $data['image_alt'] ?? $question->image_alt;
            }

            if ($request->hasFile('audio')) {
                if (!empty($question->audio_path)) {
                    Storage::disk('public')->delete($question->audio_path);
                }
                $updates['audio_path'] = $request->file('audio')
                    ->store("quiz/questions/lecture_{$lecture->id}", 'public');
            }
            $updates['audio_transcript'] = $data['audio_transcript'] ?? $question->audio_transcript;

            $question->update($updates);

            $options = $this->buildOptionsForType($data['type'], $data['options'] ?? null);

            $this->assertOptionsAreValidForType($data['type'], $options);

            $this->replaceOptions($question->id, $options);
        });

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question mise à jour.');
    }

    /**
     * Suppression d’une question.
     */
    public function destroy(ModuleLecture $lecture, QuizQuestion $question)
    {
        abort_unless($question->lecture_id === $lecture->id, 404);

        DB::transaction(function () use ($question) {
            // Suppression des fichiers associés
            if (!empty($question->image_path)) {
                Storage::disk('public')->delete($question->image_path);
            }
            if (!empty($question->audio_path)) {
                Storage::disk('public')->delete($question->audio_path);
            }

            QuizOption::where('question_id', $question->id)->delete();
            $question->delete();
        });

        return redirect()
            ->route('admin.quiz.questions.index', $lecture)
            ->with('success', 'Question supprimée.');
    }

    /**
     * Validation commune (create + update).
     */
    function validatePayload(Request $request): array
    {
        // IMPORTANT : tes vues envoient options[*][text] + options[*][is_correct]
        // On accepte aussi option_text pour compatibilité si besoin.
        return $request->validate([
            'question_text'         => ['required', 'string'],
            'type'                  => ['required', 'in:boolean,single,multiple'],
            'is_active'             => ['nullable', 'boolean'],

            'options'               => ['nullable', 'array'],
            'options.*.text'        => ['nullable', 'string'],
            'options.*.option_text' => ['nullable', 'string'],
            'options.*.is_correct'  => ['nullable'],

            // Médias
            'image'            => ['nullable', 'image', 'max:4096'], // 4 Mo
            'image_alt'        => ['nullable', 'string', 'required_with:image'],
            'audio'            => ['nullable', 'file', 'max:10240'], // 10 Mo
            'audio_transcript' => ['nullable', 'string'],
            'remove_image' => ['nullable', 'boolean'],

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
        $rawOptions = $rawOptions ?? [];

        // Boolean : 2 options fixes Vrai/Faux (en base)
        if ($type === 'boolean') {
            return [
                ['text' => 'Vrai',  'is_correct' => 1],
                ['text' => 'Faux',  'is_correct' => 0],
            ];
        }

        // Single / Multiple : normalisation des champs
        $options = [];
        foreach ($rawOptions as $opt) {
            $text = $opt['text'] ?? ($opt['option_text'] ?? null);
            $text = is_string($text) ? trim($text) : null;

            // ignore lignes vides
            if (!$text) {
                continue;
            }

            $isCorrect = $opt['is_correct'] ?? 0;
            $isCorrect = (int) ((string)$isCorrect === '1' || $isCorrect === 1 || $isCorrect === true);

            $options[] = [
                'text'       => $text,
                'is_correct' => $isCorrect,
            ];
        }

        return $options;
    }

    /**
     * Vérifications métier selon le type.
     */
    private function assertOptionsAreValidForType(string $type, array $options): void
    {
        if ($type === 'boolean') {
            return; // fixe
        }

        if (count($options) < 2) {
            throw ValidationException::withMessages([
                'options' => 'Vous devez fournir au moins 2 propositions.',
            ]);
        }

        $correctCount = collect($options)->where('is_correct', 1)->count();

        if ($type === 'single' && $correctCount !== 1) {
            throw ValidationException::withMessages([
                'options' => 'Pour une question à réponse unique, vous devez cocher exactement 1 bonne réponse.',
            ]);
        }

        if ($type === 'multiple' && $correctCount < 1) {
            throw ValidationException::withMessages([
                'options' => 'Pour une question à réponses multiples, vous devez cocher au moins 1 bonne réponse.',
            ]);
        }
    }

    /**
     * Remplace toutes les options d’une question (simple).
     */
    private function replaceOptions(int $questionId, array $options): void
    {
        QuizOption::where('question_id', $questionId)->delete();

        foreach ($options as $i => $opt) {
            QuizOption::create([
                'question_id' => $questionId,
                'option_text' => $opt['text'],
                'is_correct'  => (int) $opt['is_correct'],
                'position'    => $i + 1,
            ]);
        }
    }
}
