<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\FormateurMessage;
use App\Models\Group;
use App\Models\WordCloud;
use App\Notifications\FormateurMessageNotification;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WordCloudController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $wordClouds = WordCloud::query()
            ->whereHas('group', fn ($q) => $q->where('instructor_id', $formateurId))
            ->with('group')
            ->latest()
            ->get();

        $groups = Group::query()
            ->where('instructor_id', $formateurId)
            ->orderBy('name')
            ->get();

        return view('formateur.outils.wordcloud_index', compact('wordClouds', 'groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'questions' => ['required', 'array', 'min:1', 'max:10'],
            'questions.*' => ['required', 'string', 'max:500'],
        ]);

        $questions = collect($data['questions'])
            ->map(fn ($question) => trim((string) $question))
            ->filter()
            ->values()
            ->all();

        if ($questions === []) {
            return back()
                ->withErrors(['questions' => 'Ajoutez au moins une question utile.'])
                ->withInput();
        }

        $group = Group::query()
            ->where('id', (int) $data['group_id'])
            ->where('instructor_id', $formateurId)
            ->firstOrFail();

        $wordCloud = WordCloud::query()->create([
            'group_id' => $group->id,
            'module_id' => null,
            'title' => (string) $data['title'],
            'questions' => $questions,
            'question' => $questions[0], // rétro-compatibilité
            'current_question_index' => 0,
            'access_code' => CodeGeneratorService::generateUniqueCode(WordCloud::class),
            'is_active' => true,
            'opened_at' => now(),
            'closed_at' => null,
        ]);

        $this->notifyStudents($wordCloud);

        return redirect()
            ->route('formateur.nuages.live', $wordCloud)
            ->with('success', 'Nuage de mots lancé.');
    }

    public function live(WordCloud $wordCloud): View
    {
        $wordCloud->load(['group', 'module']);
        $this->assertOwnership($wordCloud);

        return view('formateur.wordcloud.live', [
            'wordCloud' => $wordCloud,
            'joinUrl' => route('wordcloud.join.code', ['code' => $wordCloud->access_code]),
        ]);
    }

    public function setQuestion(WordCloud $wordCloud, Request $request): RedirectResponse
    {
        $wordCloud->load('group');
        $this->assertOwnership($wordCloud);

        $data = $request->validate([
            'question_index' => ['required', 'integer', 'min:0'],
        ]);

        $maxIndex = max(0, count($wordCloud->questions_array) - 1);
        $questionIndex = min((int) $data['question_index'], $maxIndex);

        $wordCloud->forceFill([
            'current_question_index' => $questionIndex,
        ])->save();

        return redirect()
            ->route('formateur.nuages.live', $wordCloud)
            ->with('success', 'Question active mise à jour.');
    }

    public function close(WordCloud $wordCloud): RedirectResponse
    {
        $wordCloud->load('group');
        $this->assertOwnership($wordCloud);

        $wordCloud->forceFill([
            'is_active' => false,
            'closed_at' => now(),
        ])->save();

        return redirect()
            ->route('formateur.nuages.live', $wordCloud)
            ->with('success', 'Nuage de mots terminé.');
    }

    public function destroy(WordCloud $wordCloud): RedirectResponse
    {
        $wordCloud->load('group');
        $this->assertOwnership($wordCloud);

        DB::transaction(function () use ($wordCloud): void {
            $wordCloud->entries()->delete();
            $wordCloud->delete();
        });

        return redirect()
            ->route('formateur.nuages.index')
            ->with('success', 'Nuage de mots supprimé.');
    }

    public function liveData(WordCloud $wordCloud, Request $request): JsonResponse
    {
        $this->assertOwnership($wordCloud);

        $qi = (int) $request->query('q', 0);
        $questionIndex = min(max($qi, 0), max(0, count($wordCloud->questions_array) - 1));

        $words = $wordCloud->entries()
            ->where('question_index', $questionIndex)
            ->select('normalized_answer as word', DB::raw('count(*) as score'))
            ->groupBy('normalized_answer')
            ->orderByDesc('score')
            ->orderBy('normalized_answer')
            ->limit(100)
            ->get();

        $responses = $wordCloud->entries()
            ->where('question_index', $questionIndex)
            ->count();

        return response()->json([
            'active' => $wordCloud->is_active,
            'current_question_index' => $wordCloud->active_question_index,
            'question_index' => $questionIndex,
            'question_count' => count($wordCloud->questions_array),
            'total_entries' => $words->sum('score'),
            'respondents' => $responses,
            'words' => $words,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function notifyStudents(WordCloud $wordCloud): void
    {
        $wordCloud->loadMissing('group.students');
        $group = $wordCloud->group;

        if (! $group) {
            return;
        }

        $joinUrl = route('wordcloud.join.code', ['code' => $wordCloud->access_code]);
        $subject = 'Nuage de mots disponible';
        $body = implode("\n", [
            'Un nuage de mots est disponible pour votre groupe.',
            '',
            'Groupe : '.$group->name,
            'Titre : '.$wordCloud->title,
            'Code : '.$wordCloud->access_code,
            'Lien de participation : '.$joinUrl,
        ]);

        foreach ($group->students as $stagiaire) {
            $message = FormateurMessage::query()->create([
                'formateur_id' => (int) auth()->id(),
                'stagiaire_id' => $stagiaire->id,
                'subject' => $subject,
                'body' => $body,
                'sent_as_notification' => true,
                'sent_as_email' => false,
            ]);

            $stagiaire->notify(new FormateurMessageNotification($message, ['database']));
        }
    }

    private function assertOwnership(WordCloud $wordCloud): void
    {
        abort_unless(
            (int) ($wordCloud->group?->instructor_id ?? 0) === (int) auth()->id(),
            403
        );
    }
}
