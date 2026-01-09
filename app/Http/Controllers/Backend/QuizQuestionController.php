<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ModuleLecture;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizQuestionController extends Controller
{
    public function index(ModuleLecture $lecture)
    {
        $questions = $lecture->quizQuestions()
            ->withCount('options')
            ->latest()
            ->get();

        return view('admin.backend.quiz.questions.index', compact('lecture', 'questions'));
    }

    public function create(ModuleLecture $lecture)
    {
        return view('admin.backend.quiz.questions.create', compact('lecture'));
    }

    public function store(Request $request, ModuleLecture $lecture)
    {
        $data = $request->validate([
            'type'                    => 'required|in:single,multiple,boolean',
            'question_text'           => 'required|string',
            'image'                   => 'nullable|image|max:2048',
            'image_alt'               => 'nullable|string|max:255',
            'audio'                   => 'nullable|mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/ogg|max:10240',
            'audio_transcript'        => 'nullable|string',
            'options'                 => 'required|array|min:2',
            'options.*.text'          => 'required|string',
            'options.*.is_correct'    => 'nullable|boolean',
            'is_active'               => 'nullable|boolean',
        ]);

        // Accessibilité : alt obligatoire si image
        if ($request->hasFile('image') && empty($data['image_alt'])) {
            return back()
                ->withErrors(['image_alt' => 'Le texte alternatif est obligatoire si une image est fournie.'])
                ->withInput();
        }

        // Règles de bonnes réponses selon type
        $correctCount = collect($data['options'])
            ->filter(fn ($o) => !empty($o['is_correct']))
            ->count();

        // single + boolean : exactement 1 bonne réponse
        if (in_array($data['type'], ['single', 'boolean'], true) && $correctCount !== 1) {
            return back()
                ->withErrors(['options' => 'Pour ce type de question, il faut exactement 1 bonne réponse.'])
                ->withInput();
        }

        // multiple : au moins 1 bonne réponse
        if ($data['type'] === 'multiple' && $correctCount < 1) {
            return back()
                ->withErrors(['options' => 'En choix multiple, il faut au moins 1 bonne réponse.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $lecture, $data) {
            $question = new QuizQuestion();
            $question->lecture_id = $lecture->id;
            $question->type = $data['type'];
            $question->question_text = $data['question_text'];
            $question->image_alt = $data['image_alt'] ?? null;
            $question->audio_transcript = $data['audio_transcript'] ?? null;
            $question->created_by = auth()->id();
            $question->is_active = (bool) $request->input('is_active', 1);

            if ($request->hasFile('image')) {
                $question->image_path = $request->file('image')->store('quiz/images', 'public');
            }

            if ($request->hasFile('audio')) {
                $question->audio_path = $request->file('audio')->store('quiz/audios', 'public');
            }

            $question->save();

            foreach ($data['options'] as $i => $opt) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'is_correct'  => !empty($opt['is_correct']),
                    'position'    => $i + 1,
                ]);
            }
        });

        return redirect()
            ->route('admin.quiz.questions.index', ['lecture' => $lecture->id])
            ->with('success', 'Question ajoutée avec succès.');
    }

    public function edit(ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->assertQuestionBelongsToLecture($lecture, $question);

        $question->load(['options' => fn ($q) => $q->orderBy('position')]);

        return view('admin.backend.quiz.questions.edit', compact('lecture', 'question'));
    }

    public function update(Request $request, ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->assertQuestionBelongsToLecture($lecture, $question);

        $data = $request->validate([
            'type'                    => 'required|in:single,multiple,boolean',
            'question_text'           => 'required|string',
            'image'                   => 'nullable|image|max:2048',
            'image_alt'               => 'nullable|string|max:255',
            'remove_image'            => 'nullable|boolean',
            'audio'                   => 'nullable|mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/ogg|max:10240',
            'audio_transcript'        => 'nullable|string',
            'remove_audio'            => 'nullable|boolean',
            'options'                 => 'required|array|min:2',
            'options.*.text'          => 'required|string',
            'options.*.is_correct'    => 'nullable|boolean',
            'is_active'               => 'nullable|boolean',
        ]);

        // Accessibilité : alt obligatoire si image (nouvelle image OU image conservée)
        $hasNewImage = $request->hasFile('image');
        $keepsOldImage = !$hasNewImage && !((bool) $request->input('remove_image', 0)) && !empty($question->image_path);

        if (($hasNewImage || $keepsOldImage) && empty($data['image_alt'])) {
            return back()
                ->withErrors(['image_alt' => 'Le texte alternatif est obligatoire si une image est fournie.'])
                ->withInput();
        }

        // Règles de bonnes réponses selon type
        $correctCount = collect($data['options'])
            ->filter(fn ($o) => !empty($o['is_correct']))
            ->count();

        if (in_array($data['type'], ['single', 'boolean'], true) && $correctCount !== 1) {
            return back()
                ->withErrors(['options' => 'Pour ce type de question, il faut exactement 1 bonne réponse.'])
                ->withInput();
        }

        if ($data['type'] === 'multiple' && $correctCount < 1) {
            return back()
                ->withErrors(['options' => 'En choix multiple, il faut au moins 1 bonne réponse.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $data, $question) {
            $question->type = $data['type'];
            $question->question_text = $data['question_text'];
            $question->image_alt = $data['image_alt'] ?? null;
            $question->audio_transcript = $data['audio_transcript'] ?? null;
            $question->is_active = (bool) $request->input('is_active', 1);

            // Image : suppression demandée
            if ((bool) $request->input('remove_image', 0) && $question->image_path) {
                Storage::disk('public')->delete($question->image_path);
                $question->image_path = null;
                $question->image_alt = null;
            }

            // Image : remplacement
            if ($request->hasFile('image')) {
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $question->image_path = $request->file('image')->store('quiz/images', 'public');
            }

            // Audio : suppression demandée
            if ((bool) $request->input('remove_audio', 0) && $question->audio_path) {
                Storage::disk('public')->delete($question->audio_path);
                $question->audio_path = null;
            }

            // Audio : remplacement
            if ($request->hasFile('audio')) {
                if ($question->audio_path) {
                    Storage::disk('public')->delete($question->audio_path);
                }
                $question->audio_path = $request->file('audio')->store('quiz/audios', 'public');
            }

            $question->save();

            // Options : stratégie simple et fiable V1 = on supprime puis on recrée
            $question->options()->delete();

            foreach ($data['options'] as $i => $opt) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'is_correct'  => !empty($opt['is_correct']),
                    'position'    => $i + 1,
                ]);
            }
        });

        return redirect()
            ->route('admin.quiz.questions.index', ['lecture' => $lecture->id])
            ->with('success', 'Question mise à jour avec succès.');
    }

    public function destroy(ModuleLecture $lecture, QuizQuestion $question)
    {
        $this->assertQuestionBelongsToLecture($lecture, $question);

        DB::transaction(function () use ($question) {
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }
            if ($question->audio_path) {
                Storage::disk('public')->delete($question->audio_path);
            }
            $question->options()->delete();
            $question->delete();
        });

        return redirect()
            ->route('admin.quiz.questions.index', ['lecture' => $lecture->id])
            ->with('success', 'Question supprimée.');
    }

    private function assertQuestionBelongsToLecture(ModuleLecture $lecture, QuizQuestion $question): void
    {
        if ((int) $question->lecture_id !== (int) $lecture->id) {
            abort(404);
        }
    }
}
