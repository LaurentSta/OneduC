<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Concerns\InteractsWithGroupWhiteboards;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupWhiteboard;
use App\Models\GroupWhiteboardItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhiteboardController extends Controller
{
    use InteractsWithGroupWhiteboards;

    public function index(): View
    {
        $groups = auth()->user()
            ->groupesStagiaire()
            ->with(['instructor', 'whiteboard'])
            ->orderBy('groups.name')
            ->get();

        return view('stagiaire.whiteboard.index', compact('groups'));
    }

    public function show(Group $group): View
    {
        $whiteboard = $this->whiteboardForStudent($group);

        return view('whiteboard.show', $this->viewData($group, $whiteboard));
    }

    public function notificationStatus(): JsonResponse
    {
        $group = auth()->user()
            ->groupesStagiaire()
            ->whereHas('whiteboard')
            ->with('whiteboard')
            ->get()
            ->sortByDesc(fn ($candidate) => optional($candidate->whiteboard?->updated_at)->timestamp ?? 0)
            ->first();

        $whiteboard = $group?->whiteboard;

        return response()->json([
            'has_available_whiteboard' => ! is_null($group) && ! is_null($whiteboard),
            'group_name' => $group?->name,
            'updated_at_human' => $whiteboard?->updated_at?->diffForHumans(),
            'join_url' => $group
                ? route('stagiaire.whiteboard.show', ['group' => $group->id])
                : null,
        ]);
    }

    public function snapshot(Group $group): JsonResponse
    {
        return $this->whiteboardSnapshotResponse($this->whiteboardForStudent($group));
    }

    public function upsert(Request $request, Group $group): JsonResponse
    {
        return $this->upsertWhiteboardItem($request, $this->whiteboardForStudent($group));
    }

    public function destroy(Group $group, GroupWhiteboardItem $item): JsonResponse
    {
        return $this->destroyWhiteboardItem($this->whiteboardForStudent($group), $item);
    }

    private function whiteboardForStudent(Group $group): GroupWhiteboard
    {
        $isMember = $group->students()
            ->where('users.id', auth()->id())
            ->exists();

        abort_unless($isMember, 404);

        return GroupWhiteboard::ensureForGroup($group, auth()->user());
    }

    private function viewData(Group $group, GroupWhiteboard $whiteboard): array
    {
        $displayName = trim((string) ((auth()->user()->prenom ?? '') . ' ' . (auth()->user()->name ?? '')));

        return [
            'layoutView' => 'stagiaire.master',
            'layoutSection' => 'content',
            'pageTitle' => 'Tableau blanc collaboratif',
            'pageEyebrow' => 'Travail de groupe',
            'pageDescription' => 'Partagez des idees, des notes et des croquis avec les autres stagiaires.',
            'backUrl' => route('stagiaire.whiteboard.index'),
            'backLabel' => 'Mes tableaux blancs',
            'secondaryUrl' => route('stagiaire.dashboard'),
            'secondaryLabel' => 'Dashboard',
            'group' => $group,
            'whiteboardConfig' => [
                'snapshot' => $whiteboard->snapshot(),
                'current_user' => [
                    'id' => (int) auth()->id(),
                    'name' => $displayName !== '' ? $displayName : 'Stagiaire',
                    'role' => 'stagiaire',
                ],
                'permissions' => [
                    'can_clear' => false,
                    'can_edit' => true,
                ],
                'routes' => [
                    'snapshot' => route('stagiaire.whiteboard.snapshot', ['group' => $group->id]),
                    'upsert' => route('stagiaire.whiteboard.items.upsert', ['group' => $group->id]),
                    'destroy_item_pattern' => route('stagiaire.whiteboard.items.destroy', [
                        'group' => $group->id,
                        'item' => '__ITEM__',
                    ]),
                    'clear' => null,
                ],
                'poll_interval_ms' => 2000,
            ],
        ];
    }
}
