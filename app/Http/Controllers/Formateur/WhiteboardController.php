<?php

namespace App\Http\Controllers\Formateur;

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

    public function show(Group $group): View
    {
        $whiteboard = $this->whiteboardForInstructor($group);

        return view('whiteboard.show', $this->viewData($group, $whiteboard));
    }

    public function snapshot(Group $group): JsonResponse
    {
        return $this->whiteboardSnapshotResponse($this->whiteboardForInstructor($group));
    }

    public function upsert(Request $request, Group $group): JsonResponse
    {
        return $this->upsertWhiteboardItem($request, $this->whiteboardForInstructor($group));
    }

    public function destroy(Group $group, GroupWhiteboardItem $item): JsonResponse
    {
        return $this->destroyWhiteboardItem($this->whiteboardForInstructor($group), $item);
    }

    public function clear(Group $group): JsonResponse
    {
        return $this->clearWhiteboard($this->whiteboardForInstructor($group));
    }

    private function whiteboardForInstructor(Group $group): GroupWhiteboard
    {
        abort_unless((int) $group->instructor_id === (int) auth()->id(), 404);

        return GroupWhiteboard::ensureForGroup($group, auth()->user());
    }

    private function viewData(Group $group, GroupWhiteboard $whiteboard): array
    {
        $displayName = trim((string) ((auth()->user()->prenom ?? '') . ' ' . (auth()->user()->name ?? '')));

        return [
            'layoutView' => 'formateur.dashboard',
            'layoutSection' => 'formateur',
            'pageTitle' => 'Tableau blanc collaboratif',
            'pageEyebrow' => 'Espace groupe',
            'pageDescription' => 'Animez un tableau partagé pour vos stagiaires, inspiré de Digiboard.',
            'backUrl' => route('formateur.groupes.edit', $group),
            'backLabel' => 'Retour au groupe',
            'secondaryUrl' => route('formateur.groupes.index'),
            'secondaryLabel' => 'Mes groupes',
            'group' => $group,
            'whiteboardConfig' => [
                'snapshot' => $whiteboard->snapshot(),
                'current_user' => [
                    'id' => (int) auth()->id(),
                    'name' => $displayName !== '' ? $displayName : 'Formateur',
                    'role' => 'formateur',
                ],
                'permissions' => [
                    'can_clear' => true,
                    'can_edit' => true,
                ],
                'routes' => [
                    'snapshot' => route('formateur.groupes.whiteboard.snapshot', ['group' => $group->id]),
                    'upsert' => route('formateur.groupes.whiteboard.items.upsert', ['group' => $group->id]),
                    'destroy_item_pattern' => route('formateur.groupes.whiteboard.items.destroy', [
                        'group' => $group->id,
                        'item' => '__ITEM__',
                    ]),
                    'clear' => route('formateur.groupes.whiteboard.clear', ['group' => $group->id]),
                ],
                'poll_interval_ms' => 2000,
            ],
        ];
    }
}
