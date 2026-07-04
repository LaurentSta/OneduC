<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\FormateurParcours;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FormateurModuleController extends Controller
{
    private function accessibleTrainerGroupIds(int $formateurId): Collection
    {
        return Group::query()
            ->accessibleByTrainer($formateurId)
            ->pluck('groups.id')
            ->map(fn ($groupId) => (int) $groupId)
            ->values();
    }

    public function mesModules(Request $request)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $search = trim((string) $request->query('search', ''));

        $modules = Module::query()
            ->where(function ($q) use ($accessibleGroupIds, $formateurId) {
                $q->whereHas('groups', function ($g) use ($accessibleGroupIds) {
                    $g->whereIn('groups.id', $accessibleGroupIds->all());
                })
                ->orWhere('formateur_id', $formateurId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('module_title', 'like', "%{$search}%")
                      ->orWhere('module_name', 'like', "%{$search}%");
                });
            })
            ->with([
                'sections' => function ($q) {
                    $q->select('id', 'module_id')->orderBy('id');
                },
                'groups' => function ($q) use ($accessibleGroupIds) {
                    $q->whereIn('groups.id', $accessibleGroupIds->all())
                        ->with(['users' => function ($u) {
                            $u->where('role', 'stagiaire');
                        }]);
                },
            ])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $mesParcours = FormateurParcours::query()
            ->where('formateur_id', $formateurId)
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get();

        return view('formateur.formations.index', compact('modules', 'search', 'mesParcours'));
    }

    public function moduleDetail(Request $request, Module $module)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->whereIn('groups.id', $accessibleGroupIds->all())->exists();

        abort_unless($isAllowed, 403);

        $module->load([
            'formateur',
            'sections' => function ($query) {
                $query->orderBy('id')
                    ->with(['lectures' => function ($lectureQuery) {
                        $lectureQuery->orderBy('position')
                            ->orderBy('id')
                            ->with(['objectives' => function ($objectiveQuery) {
                                $objectiveQuery->orderBy('position')->orderBy('id');
                            }]);
                    }]);
            },
            'groups' => function ($q) use ($accessibleGroupIds) {
                $q->whereIn('groups.id', $accessibleGroupIds->all())
                    ->with(['users' => function ($u) {
                        $u->where('role', 'stagiaire');
                    }]);
            },
        ]);

        $mode    = (string) $request->query('mode', 'officiel');
        $groupId = $this->resolveTrainerModuleDetailGroupId($request, $module, $formateurId);

        if ($mode !== 'officiel') {
            $this->applyTrainerGroupLessonOverrides($module, $groupId);
        }

        $contextQuery = array_filter([
            'mode'     => $mode !== 'officiel' ? $mode : null,
            'group_id' => $mode !== 'officiel' ? ($groupId ?: null) : null,
        ]);

        $totalSections  = $module->sections->count();
        $totalLectures  = $module->sections->flatMap->lectures->count();
        $totalSlides    = (int) $module->sections->flatMap->lectures->sum('slide_count');
        $totalQuestions = (int) $module->sections->flatMap->lectures->sum('quiz_questions_per_attempt');

        $groupCount     = $module->groups->count();
        $stagiaires     = $module->groups->flatMap(fn ($g) => $g->users)->unique('id')->values();
        $stagiaireCount = $stagiaires->count();

        $lessonObjectives = $module->sections
            ->flatMap->lectures
            ->flatMap(function ($lecture) {
                return $lecture->objectives->pluck('title');
            })
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->unique()
            ->values();

        return view('formateur.formations.formateur_module_detail', compact(
            'module',
            'totalSections',
            'totalLectures',
            'totalSlides',
            'totalQuestions',
            'groupCount',
            'stagiaires',
            'stagiaireCount',
            'lessonObjectives',
            'contextQuery'
        ));
    }

    public function updateQuizCount(Request $request, $lectureId)
    {
        $lecture = ModuleLecture::findOrFail($lectureId);
        $totalQuestionsInBank = $lecture->quizQuestions()->count();

        $validated = $request->validate([
            'questions_count' => 'required|integer|min:1|max:' . ($totalQuestionsInBank > 0 ? $totalQuestionsInBank : 1),
        ]);

        $lecture->update([
            'quiz_questions_per_attempt' => $validated['questions_count'],
        ]);

        return back()->with('success', 'Le nombre de questions a été mis à jour.');
    }

    public function preview(Module $module)
    {
        $formateurId = auth()->id();
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);

        $isAllowed = ($module->formateur_id === $formateurId)
            || $module->groups()->whereIn('groups.id', $accessibleGroupIds->all())->exists();

        abort_unless($isAllowed, 403);

        $module->load('sections.lectures');

        $firstSection = $module->sections->first();
        $firstLecture = $firstSection?->lectures->first();

        if (!$firstSection || !$firstLecture) {
            return back()->with('error', 'Ajoutez au moins un chapitre et une leçon avant de pouvoir accéder à l\'aperçu.');
        }

        return redirect()->route('formateur.formations.lecture', [
            'module'  => $module->id,
            'section' => $firstSection->id,
            'lecture' => $firstLecture->id,
        ]);
    }

    private function resolveTrainerModuleDetailGroupId(Request $request, Module $module, int $formateurId): ?int
    {
        $accessibleGroupIds = $this->accessibleTrainerGroupIds($formateurId);
        $forcedGroupId = (int) $request->query('group_id', 0);

        if ($forcedGroupId > 0) {
            $isAccessibleGroup = Group::query()
                ->where('id', $forcedGroupId)
                ->whereIn('id', $accessibleGroupIds->all())
                ->exists();

            if (!$isAccessibleGroup) return null;

            $hasModule = DB::table('group_module')
                ->where('group_id', $forcedGroupId)
                ->where('module_id', $module->id)
                ->exists();

            return $hasModule ? $forcedGroupId : null;
        }

        return Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->whereHas('modules', fn ($query) => $query->where('modules.id', $module->id))
            ->value('id');
    }

    private function applyTrainerGroupLessonOverrides(Module $module, ?int $groupId): void
    {
        if (!$groupId || !$module->relationLoaded('sections')) {
            return;
        }

        $overrides = \App\Models\GroupModuleLecture::query()
            ->where('group_id', $groupId)
            ->where('module_id', $module->id)
            ->get()
            ->keyBy('lecture_id');

        if ($overrides->isEmpty()) return;

        $module->sections->each(function ($section) use ($overrides): void {
            $lectures = collect($section->lectures)
                ->filter(function ($lecture) use ($overrides) {
                    $row = $overrides->get($lecture->id);
                    return $row ? (bool) $row->is_enabled : true;
                })
                ->sortBy(function ($lecture) use ($overrides) {
                    $row = $overrides->get($lecture->id);
                    return $row ? (int) $row->position : (int) $lecture->position;
                })
                ->values();

            $section->setRelation('lectures', $lectures);
        });
    }
}
