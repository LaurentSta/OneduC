<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupeModuleLessonController extends Controller
{
    public function editModuleLessons($groupId, $moduleId)
    {
        $formateurId = auth()->id();

        $group = Group::query()
            ->accessibleByTrainer((int) $formateurId)
            ->where('id', $groupId)
            ->firstOrFail();

        $module = Module::query()->findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

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
                    ->sortBy(fn ($lecture) => (int) ($rows[$lecture->id]->position ?? 999999))
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

        if (! $groupFirstSectionId || ! $groupFirstLectureId) {
            $fallbackSection = $sections->first();
            $fallbackLecture = $fallbackSection?->lectures?->first();
            if ($fallbackSection && $fallbackLecture) {
                $groupFirstSectionId = (int) $fallbackSection->id;
                $groupFirstLectureId = (int) $fallbackLecture->id;
            }
        }

        $officialPreviewUrl = null;
        if ($officialFirstLecture) {
            $officialPreviewUrl = route('formateur.formations.section', [
                'module' => $module->id,
                'section' => (int) $officialFirstLecture->section_id,
                'mode' => 'officiel',
            ]);
        }

        $groupPreviewUrl = null;
        if ($groupFirstSectionId && $groupFirstLectureId) {
            $groupPreviewUrl = route('formateur.formations.section', [
                'module' => $module->id,
                'section' => $groupFirstSectionId,
                'mode' => 'groupe',
                'group_id' => $group->id,
            ]);
        }

        return view('formateur.groupes.module_lecons', compact(
            'group',
            'module',
            'sections',
            'rows',
            'officialPreviewUrl',
            'groupPreviewUrl',
        ));
    }

    public function toggleModuleLesson($groupId, $moduleId, $lectureId)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $groupId)
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

        $row = GroupModuleLecture::where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->where('lecture_id', $lectureId)
            ->first();

        if (! $row) {
            $lectureExistsInModule = ModuleLecture::query()
                ->where('id', $lectureId)
                ->where('module_id', $module->id)
                ->exists();

            abort_unless($lectureExistsInModule, 404);

            $nextPosition = ((int) GroupModuleLecture::query()
                ->where('group_id', $group->id)
                ->where('module_id', $module->id)
                ->max('position')) + 1;

            $row = GroupModuleLecture::query()->create([
                'group_id' => $group->id,
                'module_id' => $module->id,
                'lecture_id' => (int) $lectureId,
                'position' => max(1, $nextPosition),
                'is_enabled' => true,
            ]);
        }

        $row->update(['is_enabled' => ! (bool) $row->is_enabled]);

        return back()->with('success', 'Leçon mise à jour.');
    }

    public function moveModuleLessonUp($groupId, $moduleId, $lectureId)
    {
        return $this->moveModuleLesson($groupId, $moduleId, $lectureId, -1);
    }

    public function moveModuleLessonDown($groupId, $moduleId, $lectureId)
    {
        return $this->moveModuleLesson($groupId, $moduleId, $lectureId, 1);
    }

    public function resetModuleLessons($groupId, $moduleId)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $groupId)
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        GroupModuleLecture::where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->delete();

        return back()->with('success', 'Personnalisation réinitialisée.');
    }

    private function ensureCustomization(Group $group, Module $module): void
    {
        $moduleLectureIds = ModuleLecture::query()
            ->where('module_id', $module->id)
            ->orderBy('section_id')
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($moduleLectureIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($group, $module, $moduleLectureIds): void {
            $existingRows = GroupModuleLecture::query()
                ->where('group_id', $group->id)
                ->where('module_id', $module->id)
                ->lockForUpdate()
                ->get(['lecture_id', 'position']);

            if ($existingRows->isEmpty()) {
                $pos = 1;
                $payload = $moduleLectureIds->map(function ($lectureId) use ($group, $module, &$pos) {
                    return [
                        'group_id' => $group->id,
                        'module_id' => $module->id,
                        'lecture_id' => (int) $lectureId,
                        'position' => $pos++,
                        'is_enabled' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                GroupModuleLecture::query()->insert($payload);
                return;
            }

            $existingLectureIds = $existingRows
                ->pluck('lecture_id')
                ->map(fn ($id) => (int) $id);

            $missingLectureIds = $moduleLectureIds->diff($existingLectureIds)->values();
            if ($missingLectureIds->isEmpty()) {
                return;
            }

            $pos = ((int) $existingRows->max('position')) + 1;
            $payload = $missingLectureIds->map(function ($lectureId) use ($group, $module, &$pos) {
                return [
                    'group_id' => $group->id,
                    'module_id' => $module->id,
                    'lecture_id' => (int) $lectureId,
                    'position' => $pos++,
                    'is_enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            GroupModuleLecture::query()->insert($payload);
        });
    }

    private function moveModuleLesson($groupId, $moduleId, $lectureId, int $delta)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $groupId)
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

        $lecture = ModuleLecture::where('id', $lectureId)
            ->where('module_id', $module->id)
            ->firstOrFail();

        $sectionId = (int) $lecture->section_id;

        DB::transaction(function () use ($group, $module, $lectureId, $delta, $sectionId): void {
            $list = GroupModuleLecture::query()
                ->join('module_lectures', 'module_lectures.id', '=', 'group_module_lectures.lecture_id')
                ->where('group_module_lectures.group_id', $group->id)
                ->where('group_module_lectures.module_id', $module->id)
                ->where('module_lectures.section_id', $sectionId)
                ->orderBy('group_module_lectures.position')
                ->lockForUpdate()
                ->get(['group_module_lectures.*'])
                ->values();

            $idx = $list->search(fn ($row) => (int) $row->lecture_id === (int) $lectureId);
            if ($idx === false) {
                return;
            }

            $swapIdx = $idx + $delta;
            if ($swapIdx < 0 || $swapIdx >= $list->count()) {
                return;
            }

            $a = $list[$idx];
            $b = $list[$swapIdx];

            $tmp = $a->position;
            $a->position = $b->position;
            $b->position = $tmp;

            $a->save();
            $b->save();
        });

        return back()->with('success', 'Ordre mis à jour.');
    }
}
