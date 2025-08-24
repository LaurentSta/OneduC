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

        $modulesQuery = Module::with(['category', 'formateur']);

        if ($request->filled('category_id')) {
            $modulesQuery->where('category_id', $request->category_id);
        }

        // Pagination Laravel intégrée (6 modules par page pour l'exemple)
        $modules = $modulesQuery->paginate(6);

        return view('frontend.contenu.modules', compact('modules', 'categories'));
    }


    public function show($id)
    {
        $module = Module::with('sections.lectures')->findOrFail($id);

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

        $guestView = auth()->guest(); // ← ajoute ça
        return view('frontend.contenu.module_detail', compact('module','lessonStatuses','progression','sectionProgress','guestView'));
    }


}
