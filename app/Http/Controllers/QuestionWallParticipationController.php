<?php

namespace App\Http\Controllers;

use App\Models\QuestionWall;
use App\Models\QuestionWallQuestion;
use App\Models\QuestionWallVote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionWallParticipationController extends Controller
{
    public function joinByCode(string $code): View
    {
        $wall = QuestionWall::query()
            ->where('access_code', strtoupper(trim($code)))
            ->with(['group'])
            ->firstOrFail();

        $questions = QuestionWallQuestion::query()
            ->where('question_wall_id', $wall->id)
            ->with(['user'])
            ->withCount('votes')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        $myVotes = collect();
        if (auth()->check()) {
            $myVotes = QuestionWallVote::query()
                ->where('user_id', (int) auth()->id())
                ->whereIn('question_wall_question_id', $questions->pluck('id'))
                ->pluck('question_wall_question_id');
        }

        $questions->each(function (QuestionWallQuestion $question) use ($myVotes): void {
            $question->setAttribute('voted_by_me', $myVotes->contains((int) $question->id));
        });

        return view('questions.show', compact('wall', 'questions'));
    }

    public function submitQuestion(Request $request, string $code): RedirectResponse
    {
        $wall = QuestionWall::query()
            ->where('access_code', strtoupper(trim($code)))
            ->firstOrFail();

        $this->ensureCanParticipate($wall, $request->user());

        if (! $wall->is_active) {
            return back()->withErrors(['question' => 'Ce mur est ferme pour le moment.']);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        QuestionWallQuestion::query()->create([
            'question_wall_id' => $wall->id,
            'user_id' => (int) auth()->id(),
            'body' => trim((string) $data['body']),
            'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            'status' => QuestionWallQuestion::STATUS_OPEN,
        ]);

        return back()->with('success', 'Question envoyee.');
    }

    public function toggleVote(Request $request, string $code, QuestionWallQuestion $question): RedirectResponse
    {
        $wall = QuestionWall::query()
            ->where('access_code', strtoupper(trim($code)))
            ->firstOrFail();

        abort_unless((int) $question->question_wall_id === (int) $wall->id, 404);
        $this->ensureCanParticipate($wall, $request->user());

        if (! $wall->is_active) {
            return back()->withErrors(['vote' => 'Ce mur est ferme pour le moment.']);
        }

        $existing = QuestionWallVote::query()
            ->where('question_wall_question_id', $question->id)
            ->where('user_id', (int) auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            QuestionWallVote::query()->create([
                'question_wall_question_id' => $question->id,
                'user_id' => (int) auth()->id(),
            ]);
        }

        return back();
    }

    private function ensureCanParticipate(QuestionWall $wall, ?User $user): void
    {
        abort_unless($user instanceof User, 403);

        $group = $wall->group;
        abort_unless($group, 404);

        $isMember = $group->users()->where('users.id', (int) $user->id)->exists();
        $isOwner = (int) $group->instructor_id === (int) $user->id;

        abort_unless($isOwner || $isMember, 403);
    }
}
