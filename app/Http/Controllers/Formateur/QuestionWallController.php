<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\QuestionWall;
use App\Models\QuestionWallQuestion;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionWallController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $walls = QuestionWall::query()
            ->where('formateur_id', $formateurId)
            ->with(['group'])
            ->withCount('questions')
            ->latest()
            ->limit(15)
            ->get();

        return view('formateur.outils.questions_index', compact('groups', 'walls'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $formateurId = (int) auth()->id();

        $group = Group::query()
            ->accessibleByTrainer($formateurId)
            ->findOrFail((int) $data['group_id']);

        $wall = QuestionWall::query()->create([
            'formateur_id' => $formateurId,
            'group_id' => $group->id,
            'title' => trim((string) ($data['title'] ?? 'Mur de questions')) ?: 'Mur de questions',
            'access_code' => CodeGeneratorService::generateUniqueCode(QuestionWall::class),
            'is_active' => true,
        ]);

        return redirect()->route('formateur.questions.show', $wall);
    }

    public function show(QuestionWall $wall): View
    {
        $this->assertOwnership($wall);

        $wall->load('group');

        $questions = QuestionWallQuestion::query()
            ->where('question_wall_id', $wall->id)
            ->with(['user'])
            ->withCount('votes')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        return view('formateur.outils.questions_show', [
            'wall' => $wall,
            'questions' => $questions,
            'joinUrl' => route('questions.join.code', $wall->access_code),
        ]);
    }

    public function updateStatus(Request $request, QuestionWall $wall, QuestionWallQuestion $question): RedirectResponse
    {
        $this->assertOwnership($wall);
        abort_unless((int) $question->question_wall_id === (int) $wall->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:open,treated,activity,later'],
        ]);

        $status = (string) $data['status'];
        $question->update([
            'status' => $status,
            'acted_at' => $status === QuestionWallQuestion::STATUS_OPEN ? null : now(),
        ]);

        return back()->with('success', 'Statut mis a jour.');
    }

    public function toggle(QuestionWall $wall): RedirectResponse
    {
        $this->assertOwnership($wall);

        $wall->update([
            'is_active' => ! $wall->is_active,
        ]);

        return back()->with('success', $wall->is_active ? 'Le mur est maintenant ouvert.' : 'Le mur est maintenant ferme.');
    }

    public function state(QuestionWall $wall): JsonResponse
    {
        $this->assertOwnership($wall);

        $questions = QuestionWallQuestion::query()
            ->where('question_wall_id', $wall->id)
            ->withCount('votes')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get(['id', 'status', 'body', 'is_anonymous', 'created_at']);

        return response()->json([
            'is_active' => $wall->is_active,
            'questions' => $questions,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function assertOwnership(QuestionWall $wall): void
    {
        abort_unless((int) $wall->formateur_id === (int) auth()->id(), 403);
    }
}
