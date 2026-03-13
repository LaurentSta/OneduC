<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\LectureObjective;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ObjectiveController extends Controller
{
    public function index(Request $request)
    {
        $formateurId = (int) auth()->id();
        $search = trim((string) $request->query('search', ''));
        $moduleId = (int) $request->query('module_id', 0);
        $keywords = $this->extractKeywords($search);

        $modules = Module::query()
            ->where(function ($q) use ($formateurId) {
                $q->where('formateur_id', $formateurId)
                    ->orWhereHas('groups', function ($g) use ($formateurId) {
                        $g->where('instructor_id', $formateurId);
                    });
            })
            ->orderByRaw('COALESCE(module_title, module_name)')
            ->get(['id', 'module_title', 'module_name']);

        $objectives = LectureObjective::query()
            ->whereHas('lecture.module', function ($moduleQuery) use ($modules, $moduleId) {
                $moduleQuery->whereIn('modules.id', $modules->pluck('id'))
                    ->when($moduleId > 0, function ($q) use ($moduleId) {
                        $q->whereKey($moduleId);
                    });
            })
            ->when($keywords->isNotEmpty(), function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $like = '%' . $keyword . '%';

                    $query->where(function ($q) use ($like) {
                        $q->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhereHas('lecture', function ($lectureQuery) use ($like) {
                                $lectureQuery->where('lecture_title', 'like', $like);
                            })
                            ->orWhereHas('lecture.section', function ($sectionQuery) use ($like) {
                                $sectionQuery->where('section_title', 'like', $like);
                            })
                            ->orWhereHas('lecture.module', function ($moduleQuery) use ($like) {
                                $moduleQuery
                                    ->where('module_title', 'like', $like)
                                    ->orWhere('module_name', 'like', $like);
                            });
                    });
                }
            })
            ->with([
                'lecture:id,module_id,section_id,lecture_title',
                'lecture.section:id,module_id,section_title',
                'lecture.module:id,module_title,module_name',
                'competencies:id,code,label',
            ])
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('formateur.objectifs.index', [
            'objectives' => $objectives,
            'modules' => $modules,
            'search' => $search,
            'moduleId' => $moduleId,
            'keywords' => $keywords,
        ]);
    }

    private function extractKeywords(string $search): Collection
    {
        return collect(preg_split('/[\s,;]+/u', mb_strtolower($search)))
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter();
    }
}
