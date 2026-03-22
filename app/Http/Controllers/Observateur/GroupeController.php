<?php

namespace App\Http\Controllers\Observateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use Illuminate\View\View;

class GroupeController extends Controller
{
    public function index(): View
    {
        $groupes = auth()->user()
            ->groupesObserve()
            ->with([
                'instructor:id,prenom,name',
                'modules',
                'students',
                'observers:id,prenom,name',
            ])
            ->orderBy('groups.name')
            ->get();

        return view('observateur.groupes.index', [
            'profileData' => auth()->user(),
            'groupes' => $groupes,
        ]);
    }

    public function showModuleLessons(Group $group, Module $module): View
    {
        abort_unless(
            auth()->user()->groupesObserve()->where('groups.id', $group->id)->exists(),
            404
        );

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 404);

        $rows = GroupModuleLecture::query()
            ->where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->orderBy('position')
            ->get(['lecture_id', 'position', 'is_enabled'])
            ->keyBy('lecture_id');

        $sections = ModuleSection::query()
            ->where('module_id', $module->id)
            ->orderBy('id')
            ->with([
                'lectures' => function ($query) use ($module): void {
                    $query->where('module_id', $module->id)
                        ->orderBy('position')
                        ->orderBy('id');
                },
            ])
            ->get();

        $sections->each(function ($section) use ($rows): void {
            $section->setRelation(
                'lectures',
                $section->lectures
                    ->sortBy(fn ($lecture) => (int) ($rows[$lecture->id]->position ?? $lecture->position ?? 999999))
                    ->values()
            );
        });

        $officialFirstLecture = ModuleLecture::query()
            ->where('module_id', $module->id)
            ->orderBy('position')
            ->orderBy('id')
            ->first(['id', 'section_id']);

        $groupFirstSectionId = 0;
        $groupFirstLectureId = 0;

        foreach ($sections as $section) {
            foreach (($section->lectures ?? collect()) as $lecture) {
                $row = $rows[$lecture->id] ?? null;
                $enabled = $row ? (bool) $row->is_enabled : true;

                if (! $enabled) {
                    continue;
                }

                $groupFirstSectionId = (int) $section->id;
                $groupFirstLectureId = (int) $lecture->id;
                break 2;
            }
        }

        $officialPreviewUrl = null;
        if ($officialFirstLecture) {
            $officialPreviewUrl = route('observateur.formations.section', [
                'module' => $module->id,
                'section' => (int) $officialFirstLecture->section_id,
                'mode' => 'officiel',
                'group_id' => $group->id,
                'anonymous' => 1,
            ]);
        }

        $groupPreviewUrl = null;
        if ($groupFirstSectionId && $groupFirstLectureId) {
            $groupPreviewUrl = route('observateur.formations.section', [
                'module' => $module->id,
                'section' => $groupFirstSectionId,
                'mode' => 'groupe',
                'group_id' => $group->id,
                'anonymous' => 1,
            ]);
        }

        return view('observateur.groupes.module_lecons', [
            'profileData' => auth()->user(),
            'group' => $group,
            'module' => $module,
            'sections' => $sections,
            'rows' => $rows,
            'officialPreviewUrl' => $officialPreviewUrl,
            'groupPreviewUrl' => $groupPreviewUrl,
        ]);
    }
}
