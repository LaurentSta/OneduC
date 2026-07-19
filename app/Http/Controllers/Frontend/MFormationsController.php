<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Category;
use Illuminate\Http\Request;

class MFormationsController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $modulesQuery = Module::with(['category', 'formateur'])->publiclyListable();

        if ($request->filled('category_id')) {
            $modulesQuery->where('category_id', $request->category_id);
        }

        // Pagination Laravel intégrée (6 modules par page pour l'exemple)
        $modules = $modulesQuery->paginate(6);

        return view('frontend.contenu.modules', compact('modules', 'categories'));
    }


    public function show(Category $category, Module $module)
    {
        // URL canonique: la catégorie de l'URL doit correspondre au module.
        if ((int) $module->category_id !== (int) $category->id) {
            if (empty($module->category_id)) {
                abort(404);
            }

            return redirect()->route('frontend.modules.show', [
                'category' => $module->category_id,
                'module' => $module->id,
            ], 301);
        }

        return $this->renderModuleDetail($module);
    }

    public function showLegacy(Module $module)
    {
        // Redirection permanente de /modules/{id} vers /categorie/{category}/modules/{id}
        if (!empty($module->category_id)) {
            return redirect()->route('frontend.modules.show', [
                'category' => $module->category_id,
                'module' => $module->id,
            ], 301);
        }

        // Fallback de sécurité si un module historique n'a pas de catégorie.
        return $this->renderModuleDetail($module);
    }

    private function renderModuleDetail(Module $module)
    {
        abort_unless((bool) $module->status && $module->estPubliee(), 404);

        $module->loadMissing([
            'category',
            'subCategory',
            'formateur',
            'sections.lectures.objectives',
        ]);

        $userId = auth()->id();
        $lectures = $module->sections->flatMap->lectures;

        $lessonStatuses = \App\Models\ScormScore::where('user_id', $userId)
            ->whereIn('lecture_id', $lectures->pluck('id'))
            ->pluck('lesson_status', 'lecture_id');

        $total = $lectures->count();
        $completed = $lessonStatuses->filter(fn($status) => $status === 'completed')->count();
        $progression = $total > 0 ? intval(($completed / $total) * 100) : 0;

        $sectionProgress = [];
        foreach ($module->sections as $section) {
            $t = $section->lectures->count();
            $c = $section->lectures->filter(fn($lec) => ($lessonStatuses[$lec->id] ?? null) === 'completed')->count();
            $sectionProgress[$section->id] = ['completed' => $c, 'total' => $t];
        }

        // Objectifs pédagogiques issus des leçons (agrégés et sans doublon)
        $lessonObjectives = $module->sections
            ->flatMap->lectures
            ->flatMap(function ($lecture) {
                return $lecture->objectives->pluck('title');
            })
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->unique()
            ->values();

        $rawModuleObjectives = $module->objectifs;
        $moduleObjectives = match (true) {
            is_array($rawModuleObjectives) => collect($rawModuleObjectives),
            is_string($rawModuleObjectives) => collect(preg_split('/\r\n|\r|\n/', $rawModuleObjectives)),
            default => collect(),
        };

        $moduleObjectives = $moduleObjectives
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->unique()
            ->values();

        $guestView = auth()->guest();
        return view('frontend.contenu.module_detail', compact(
            'module',
            'lessonStatuses',
            'progression',
            'sectionProgress',
            'guestView',
            'lessonObjectives',
            'moduleObjectives'
        ));
    }


}
