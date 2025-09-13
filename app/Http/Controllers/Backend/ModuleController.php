<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ModuleSection;
use App\Models\ModuleLecture;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\ScormResult;
use App\Models\ScormScore;
use App\Models\Evaluation;
use App\Models\ScormInteraction;

class ModuleController extends Controller
{
    /**
     * Resolve la base de vue selon le contexte de la route.
     * - formateur.* -> formateur.formations.*
     * - sinon -> stagiaire.formations.*
     */
    private function viewBase(): string
    {
        $name = optional(request()->route())->getName();
        return (is_string($name) && str_starts_with($name, 'formateur.'))
            ? 'formateur.formations'
            : 'stagiaire.formations';
    }

    /**
     * 1. Liste des modules (admin)
     */
    public function Modules()
    {
        $modules = Module::query()
            ->with([
                'formateur:id,name',
                'category:id,category_name',
            ])
            ->withCount(['sections','lectures'])
            ->withSum('lectures as questions_count', 'question_count')
            ->latest('id')
            ->get();

        return view('admin.backend.modules.modules', compact('modules'));
    }

    public function toggleStatus(\App\Models\Module $module)
    {
        $module->update(['status' => $module->status ? 0 : 1]);
        return back()->with('success', $module->status ? 'Module désactivé' : 'Module activé');
    }

    /**
     * 2. Formulaire d'ajout de module (admin)
     */
    public function AddModule()
    {
        $categories   = Category::orderBy('category_name', 'asc')->get();
        $subcategories= SubCategory::orderBy('subcategory_name', 'asc')->get();
        $formateurs   = User::where('role', 'formateur')->orderBy('name')->get();
        $evaluations  = Evaluation::orderBy('titre')->get();

        return view('admin.backend.modules.add_module', compact('categories', 'subcategories', 'formateurs', 'evaluations'));
    }

    /**
     * 3. Enregistre un module (admin)
     */
    public function StoreModule(Request $request)
    {
        $request->validate([
            'module_name'   => 'required|string|max:255',
            'module_title'  => 'required|string|max:255',
            'formateur_id'  => 'required|exists:users,id',
            'category_id'   => 'required|integer',
            'subcategory_id'=> 'nullable|integer',
            'certificat'    => 'required|in:1,0',
            'label'         => 'nullable|string|max:255',
            'duree'         => 'nullable|string|max:100',
            'resources'     => 'nullable|string|max:255',
            'prerequi'      => 'nullable|string',
            'module_image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'header_image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'module_video'  => 'nullable|string|max:255',
            'evaluation_id' => 'nullable|exists:evaluations,id',
        ]);

        $imagePath = null;
        if ($request->hasFile('module_image')) {
            $image = $request->file('module_image');
            $imageName = time().'_'.Str::slug($request->module_name).'.'.$image->getClientOriginalExtension();
            $image->storeAs('uploads/modules/images', $imageName, 'public');
            $imagePath = 'uploads/modules/images/'.$imageName;
        }

        $headerImagePath = null;
        if ($request->hasFile('header_image')) {
            $headerImage = $request->file('header_image');
            $headerImageName = time().'_header_'.Str::slug($request->module_name).'.'.$headerImage->getClientOriginalExtension();
            $headerImage->storeAs('uploads/modules/headers', $headerImageName, 'public');
            $headerImagePath = 'uploads/modules/headers/'.$headerImageName;
        }

        Module::create([
            'category_id'     => $request->category_id,
            'subcategory_id'  => $request->subcategory_id,
            'formateur_id'    => $request->formateur_id,
            'module_name'     => $request->module_name,
            'module_name_slug'=> Str::slug($request->module_name),
            'module_title'    => $request->module_title,
            'description'     => $request->description,
            'module_image'    => $imagePath,
            'header_image'    => $headerImagePath,
            'module_video'    => $request->module_video, // URL directe
            'label'           => $request->label,
            'duree'           => $request->duree,
            'resources'       => $request->resources,
            'certificat'      => $request->certificat,
            'prerequi'        => $request->prerequi,
            'bestseller'      => $request->has('bestseller') ? 1 : 0,
            'vedette'         => $request->has('vedette') ? 1 : 0,
            'surevalue'       => $request->has('surevalue') ? 1 : 0,
            'status'          => $request->has('status') ? 1 : 0,
            'evaluation_id'   => $request->evaluation_id,
        ]);

        return redirect()->route('admin.modules')->with('success', 'Module ajouté avec succès !');
    }

    /**
     * 4. Formulaire d’édition (admin)
     */
    public function EditModule($id)
    {
        $module       = Module::findOrFail($id);
        $categories   = Category::orderBy('category_name', 'asc')->get();
        $subcategories= SubCategory::orderBy('subcategory_name', 'asc')->get();
        $formateurs   = User::where('role', 'formateur')->orderBy('name')->get();
        $evaluations  = Evaluation::orderBy('titre')->get();

        return view('admin.backend.modules.edit_module', compact('module', 'categories', 'subcategories', 'formateurs', 'evaluations'));
    }

    public function UpdateModule(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $request->validate([
            'module_name'   => 'required|string|max:255',
            'module_title'  => 'required|string|max:255',
            'category_id'   => 'required|integer|exists:categories,id',
            'subcategory_id'=> 'nullable|integer|exists:subcategories,id',
            'certificat'    => 'required|in:1,0',
            'module_video'  => 'nullable|string|max:255',
            'evaluation_id' => 'nullable|exists:evaluations,id',
            'formateur_id'  => 'required|exists:users,id',
        ]);

        $imagePath = $module->module_image;
        if ($request->hasFile('module_image')) {
            if ($module->module_image) {
                Storage::disk('public')->delete($module->module_image);
            }
            $image = $request->file('module_image');
            $imageName = time().'_'.Str::slug($request->module_name).'.'.$image->getClientOriginalExtension();
            $image->storeAs('uploads/modules/images', $imageName, 'public');
            $imagePath = 'uploads/modules/images/'.$imageName;
        }

        $headerImagePath = $module->header_image;
        if ($request->hasFile('header_image')) {
            if ($module->header_image) {
                Storage::disk('public')->delete($module->header_image);
            }
            $headerImage = $request->file('header_image');
            $headerImageName = time().'_header_'.Str::slug($request->module_name).'.'.$headerImage->getClientOriginalExtension();
            $headerImage->storeAs('uploads/modules/headers', $headerImageName, 'public');
            $headerImagePath = 'uploads/modules/headers/'.$headerImageName;
        }

        $module->update([
            'category_id'     => $request->category_id,
            'subcategory_id'  => $request->subcategory_id,
            'formateur_id'    => $request->formateur_id,
            'module_name'     => $request->module_name,
            'module_name_slug'=> Str::slug($request->module_name),
            'module_title'    => $request->module_title,
            'description'     => $request->description,
            'module_image'    => $imagePath,
            'header_image'    => $headerImagePath,
            'module_video'    => $request->module_video, // URL directe
            'label'           => $request->label,
            'duree'           => $request->duree,
            'resources'       => $request->resources,
            'certificat'      => $request->certificat,
            'prerequi'        => $request->prerequi,
            'bestseller'      => $request->has('bestseller') ? 1 : 0,
            'vedette'         => $request->has('vedette') ? 1 : 0,
            'surevalue'       => $request->has('surevalue') ? 1 : 0,
            'status'          => $request->has('status') ? 1 : 0,
            'evaluation_id'   => $request->evaluation_id,
        ]);

        return redirect()->route('admin.modules')->with('success', 'Module mis à jour avec succès !');
    }

    /**
     * 6. Suppression d’un module (admin)
     */
    public function DeleteModule($id)
    {
        $module = Module::findOrFail($id);

        if ($module->module_image) {
            Storage::disk('public')->delete($module->module_image);
        }
        if ($module->header_image) {
            Storage::disk('public')->delete($module->header_image);
        }
        // module_video est une URL ou un chemin externe → pas de suppression disque ici.

        $module->delete();

        return redirect()->route('admin.modules')->with('success', 'Module supprimé avec succès !');
    }

    /**
     * 7. Vue d’ajout de lecture (admin)
     */
    public function AddModuleLecture($id)
    {
        $module  = Module::find($id);
        $section = ModuleSection::where('module_id',$id)->latest()->get();

        return view('admin.backend.modules.section.add_module_lecture', compact('module','section'));
    }

    public function MoveLectureUp($id)
    {
        $lecture = ModuleLecture::findOrFail($id);
        $prev = ModuleLecture::where('section_id', $lecture->section_id)
            ->where('position', '<', $lecture->position)
            ->orderByDesc('position')->first();

        if ($prev) {
            [$lecture->position, $prev->position] = [$prev->position, $lecture->position];
            $lecture->save();
            $prev->save();
        }

        return back();
    }

    public function MoveLectureDown($id)
    {
        $lecture = ModuleLecture::findOrFail($id);
        $next = ModuleLecture::where('section_id', $lecture->section_id)
            ->where('position', '>', $lecture->position)
            ->orderBy('position')->first();

        if ($next) {
            [$lecture->position, $next->position] = [$next->position, $lecture->position];
            $lecture->save();
            $next->save();
        }

        return back();
    }

    /**
     * 8. Ajout section (admin)
     */
    public function AddModuleSection(Request $request)
    {
        $cid = $request->module_id;

        ModuleSection::insert([
            'module_id' => $cid,
            'section_title' => $request->section_title,
        ]);

        return redirect()->back()->with([
            'message' => 'Section ajoutée',
            'alert-type' => 'success'
        ]);
    }

    public function EditModuleSection($id)
    {
        $section = ModuleSection::findOrFail($id);
        return view('admin.backend.modules.section.edit_module_section', compact('section'));
    }

    public function UpdateModuleSection(Request $request, $id)
    {
        $request->validate([
            'section_title' => 'required|string|max:255',
            'section_html'  => 'nullable|string',
            'objectif'      => 'nullable|string',
            'methode'       => 'nullable|string',
            'contexte'      => 'nullable|string',
            'video_url'     => 'nullable|string|max:255',
        ]);

        $section = ModuleSection::findOrFail($id);

        $videoPath = $request->input('video_url');

        $section->update([
            'section_title' => $request->section_title,
            'section_html'  => $request->section_html,
            'objectif'      => $request->objectif,
            'methode'       => $request->methode,
            'contexte'      => $request->contexte,
            'video_url'     => $videoPath,
        ]);

        return redirect()->route('admin.modules.lecture.add', $section->module_id)
            ->with('success', 'Section mise à jour avec succès !');
    }

    /**
     * 9. Vue Section (stagiaire ou formateur)
     */
    public function section($id, $section_id)
    {
        $module  = Module::with('sections.lectures')->findOrFail($id);
        if (! $module->isVisibleTo(\Illuminate\Support\Facades\Auth::user())) abort(404);
        $section = $module->sections->firstWhere('id', $section_id);

        if (!$section) {
            abort(404, 'Section non trouvée');
        }

        // Stats lectures (robuste aux interaction_id vides)
        $userId    = auth()->id();
        $lectures  = $module->sections->flatMap->lectures;
        $lectureIds= $lectures->pluck('id')->all();

        $all = ScormInteraction::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->orderBy('created_at')
            ->get();

        $byLecture = $all->groupBy('lecture_id');

        $lectureStats = [];
        foreach ($lectures as $lecture) {
            $totalQuestions = (int) ($lecture->question_count ?? 0);
            $rows = $byLecture->get($lecture->id, collect());

            $groups = $rows->groupBy(function ($row) {
                $key = trim((string) $row->interaction_id);
                return $key !== '' ? $key : 'row_'.$row->id;
            });

            $answered = 0;
            $correct  = 0;

            foreach ($groups as $attempts) {
                $latest = $attempts->last();
                $answered++;
                if ($latest && $latest->result === 'correct') {
                    $correct++;
                }
            }

            $answeredCapped = $totalQuestions > 0 ? min($answered, $totalQuestions) : $answered;
            $correctCapped  = $totalQuestions > 0 ? min($correct,  $totalQuestions) : $correct;

            $score = $totalQuestions > 0
                ? (int) round(($correctCapped / $totalQuestions) * 100)
                : ($answeredCapped > 0 ? 100 : null);

            $status = 'not_started';
            if ($answeredCapped === 0) {
                $status = 'not_started';
            } elseif ($totalQuestions > 0 && $answeredCapped < $totalQuestions) {
                $status = 'incomplete';
            } elseif ($score !== null && $score >= 50) {
                $status = 'acquired';
            } else {
                $status = 'not_acquired';
            }

            $lectureStats[$lecture->id] = [
                'lecture_id' => $lecture->id,
                'status'     => $status,
                'score'      => $score,
                'answered'   => $answeredCapped,
                'correct'    => $correctCapped,
                'slides'     => (int) ($lecture->slide_count ?? 0),
                'questions'  => $totalQuestions,
            ];
        }

        // Statuts des sections
        $sectionStatuses = [];
        foreach ($module->sections as $sec) {
            $total = $sec->lectures->count();
            $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                return in_array($lectureStats[$lec->id]['status'] ?? null, ['acquired','completed'], true);
            })->count();

            $sectionStatuses[$sec->id] = $ok === $total
                ? 'completed'
                : ($ok > 0 ? 'in_progress' : 'not_started');
        }

        $base = $this->viewBase();
        return view("$base.chapitre", [
            'module'          => $module,
            'selectedSection' => $section,
            'lectureStats'    => $lectureStats,
            'sectionStatuses' => $sectionStatuses,
            'selectedLecture' => null,
        ]);
    }

    /**
     * 10. Sauvegarde d’une lecture (admin)
     */
    public function SaveLecture(Request $request)
    {
        $request->validate([
            'module_id'     => 'required|exists:modules,id',
            'section_id'    => 'required|exists:module_sections,id',
            'lecture_title' => 'required|string|max:255',
        ]);

        $lastPosition = ModuleLecture::where('section_id', $request->section_id)->max('position') ?? 0;

        ModuleLecture::create([
            'module_id'     => $request->module_id,
            'section_id'    => $request->section_id,
            'lecture_title' => $request->lecture_title,
            'position'      => $lastPosition + 1,
            'slide_count'   => 0,
            'question_count'=> 0,
            'scorm_path'    => null,
        ]);

        return response()->json(['success' => 'Leçon enregistrée avec succès.']);
    }

    /**
     * 11. Formulaire d’édition d’une lecture (admin)
     */
    public function EditLecture($id)
    {
        $mlecture = ModuleLecture::find($id);
        return view('admin.backend.modules.lecture.edit_module_lecture', compact('mlecture'));
    }

    /**
     * 12. Mise à jour d’une lecture (admin)
     */
    public function UpdateModuleLecture(Request $request)
    {
        $request->validate([
            'id'            => 'required|exists:module_lectures,id',
            'lecture_title' => 'required|string|max:255',
            'scorm_path'    => 'nullable|string|max:255',
            'slide_count'   => 'nullable|integer|min:0',
            'question_count'=> 'nullable|integer|min:0',
        ]);

        $lecture = ModuleLecture::findOrFail($request->id);

        $lecture->update([
            'lecture_title' => $request->lecture_title,
            'scorm_path'    => $request->scorm_path,
            'slide_count'   => $request->slide_count ?? 0,
            'question_count'=> $request->question_count ?? 0,
        ]);

        return redirect()->route('admin.modules.lecture.add', ['id' => $lecture->module_id])
            ->with('success', 'La lecture a été mise à jour avec succès.');
    }

    /**
     * 13. Suppression d’une lecture (admin)
     */
    public function DeleteLecture($id)
    {
        ModuleLecture::find($id)?->delete();

        return redirect()->back()->with([
            'message' => 'Lecture supprimée',
            'alert-type' => 'success'
        ]);
    }

    public function DeleteSection($id)
    {
        $section = ModuleSection::find($id);
        if ($section) {
            $section->lectures()->delete();
            $section->delete();
        }

        return redirect()->back()->with([
            'message' => 'Section supprimée',
            'alert-type' => 'success'
        ]);
    }

    /**
     * 14. Détail public d’un module (catalogue)
     */
    public function show($id)
    {
        $module = Module::with('sections.lectures')->findOrFail($id);
        if (! $module->isVisibleTo(\Illuminate\Support\Facades\Auth::user())) abort(404);
        return view('frontend.contenu.module_detail', compact('module'));
    }


    /**
     * 15. Vue Lecture (stagiaire ou formateur)
     */
    public function lire($module, $section, $lesson)
    {
        $module = Module::with('sections.lectures')->findOrFail($module);
        if (! $module->isVisibleTo(\Illuminate\Support\Facades\Auth::user())) abort(404);
        $sectionModel = $module->sections->firstWhere('id', $section);
        if (!$sectionModel) abort(404, 'Section non trouvée');

        $selectedLecture = $sectionModel->lectures->firstWhere('id', $lesson);
        if (!$selectedLecture) abort(404, 'Leçon non trouvée');

        // Next lecture
        $lectures = $module->sections->flatMap->lectures;
        $currentIndex = $lectures->search(fn($lec) => $lec->id === $selectedLecture->id);
        $nextLecture  = $lectures->get($currentIndex + 1);

        // Stats lectures
        $userId     = auth()->id();
        $lectureIds = $lectures->pluck('id')->all();

        $all = ScormInteraction::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->orderBy('created_at')
            ->get();

        $byLecture = $all->groupBy('lecture_id');

        $lectureStats = [];
        foreach ($lectures as $lec) {
            $totalQuestions = (int) ($lec->question_count ?? 0);
            $rows = $byLecture->get($lec->id, collect());

            $groups = $rows->groupBy(function ($row) {
                $key = trim((string) $row->interaction_id);
                return $key !== '' ? $key : 'row_'.$row->id;
            });

            $answered = 0;
            $correct  = 0;

            foreach ($groups as $attempts) {
                $latest = $attempts->last();
                $answered++;
                if ($latest && $latest->result === 'correct') {
                    $correct++;
                }
            }

            $answeredCapped = $totalQuestions > 0 ? min($answered, $totalQuestions) : $answered;
            $correctCapped  = $totalQuestions > 0 ? min($correct,  $totalQuestions) : $correct;

            $score = $totalQuestions > 0
                ? (int) round(($correctCapped / $totalQuestions) * 100)
                : ($answeredCapped > 0 ? 100 : null);

            $status = 'not_started';
            if ($answeredCapped === 0) {
                $status = 'not_started';
            } elseif ($totalQuestions > 0 && $answeredCapped < $totalQuestions) {
                $status = 'incomplete';
            } elseif ($score !== null && $score >= 50) {
                $status = 'acquired';
            } else {
                $status = 'not_acquired';
            }

            $lectureStats[$lec->id] = [
                'lecture_id' => $lec->id,
                'status'     => $status,
                'score'      => $score,
                'answered'   => $answeredCapped,
                'correct'    => $correctCapped,
                'slides'     => (int) ($lec->slide_count ?? 0),
                'questions'  => $totalQuestions,
            ];
        }

        // Statuts des sections
        $sectionStatuses = [];
        foreach ($module->sections as $sec) {
            $total = $sec->lectures->count();
            $acq = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                return ($lectureStats[$lec->id]['status'] ?? null) === 'acquired';
            })->count();

            $sectionStatuses[$sec->id] = $acq === $total
                ? 'completed'
                : ($acq > 0 ? 'in_progress' : 'not_started');
        }

        $base = $this->viewBase();
        return view("$base.lecon", compact(
            'module',
            'selectedLecture',
            'nextLecture',
            'lectureStats',
            'sectionStatuses'
        ));
    }

    /**
     * 16. Page de fin de module (stagiaire)
     */
    public function finModule($moduleId)
    {
        $userId = auth()->id();
        $module = Module::with('sections.lectures')->findOrFail($moduleId);
        if (! $module->isVisibleTo(\Illuminate\Support\Facades\Auth::user())) abort(404);


        $totalSections = $module->sections->count();
        $totalLectures = $module->sections->flatMap->lectures->count();

        $totalQuestionsPlanned = $module->sections
            ->flatMap->lectures
            ->sum(fn($lec) => (int)($lec->question_count ?? 0));

        $questionsAnswered = 0;
        foreach ($module->sections->flatMap->lectures as $lecture) {
            $planned = (int)($lecture->question_count ?? 0);
            if ($planned === 0) continue;

            $distinctAnswered = ScormInteraction::where('user_id', $userId)
                ->where('lecture_id', $lecture->id)
                ->distinct('interaction_id')
                ->count('interaction_id');

            $questionsAnswered += min($distinctAnswered, $planned);
        }

        return view('stagiaire.fin_module', [
            'module'                 => $module,
            'totalSections'          => $totalSections,
            'totalLectures'          => $totalLectures,
            'totalQuestionsPlanned'  => $totalQuestionsPlanned,
            'questionsAnswered'      => $questionsAnswered,
        ]);
    }
}
