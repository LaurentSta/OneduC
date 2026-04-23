<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\LiveQuizSession;
use App\Models\Module;
use App\Models\ModuleSection;
use Illuminate\View\View;

class OutilsLiveQuizController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $toolGroups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->with([
                'modules' => function ($query) {
                    $query->orderBy('group_module.position')
                        ->with([
                            'sections' => function ($sectionQuery) {
                                $sectionQuery->orderBy('id')
                                    ->select('id', 'module_id', 'section_title')
                                    ->with([
                                        'lectures' => function ($lectureQuery) {
                                            $lectureQuery
                                                ->whereHas('quizQuestions', fn ($q) => $q->where('is_active', true))
                                                ->orderBy('position')
                                                ->orderBy('id')
                                                ->select('id', 'module_id', 'section_id', 'lecture_title', 'position');
                                        },
                                    ]);
                            },
                        ])
                        ->select('modules.id', 'modules.module_name', 'modules.module_title');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Group $group) {
                $modules = $group->modules
                    ->map(function (Module $m) {
                        $lectures = $m->sections->flatMap(function (ModuleSection $section) {
                            return $section->lectures->map(fn ($l) => [
                                'id'         => (int) $l->id,
                                'section_id' => (int) $l->section_id,
                                'label'      => trim($section->section_title . ' · ' . $l->lecture_title),
                            ]);
                        })->values();

                        if ($lectures->isEmpty()) {
                            return null;
                        }

                        return [
                            'id'       => (int) $m->id,
                            'title'    => (string) ($m->module_title ?: $m->module_name ?: 'Module'),
                            'lectures' => $lectures,
                        ];
                    })
                    ->filter()
                    ->values();

                if ($modules->isEmpty()) {
                    return null;
                }

                return [
                    'id'      => (int) $group->id,
                    'name'    => (string) $group->name,
                    'modules' => $modules,
                ];
            })
            ->filter()
            ->values();

        $sessions = LiveQuizSession::where('formateur_id', $formateurId)
            ->with(['group', 'lecture'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('formateur.outils.quiz_index', compact('toolGroups', 'sessions'));
    }
}
