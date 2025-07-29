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
     * 1. Affiche tous les modules (admin ou formateur)
     */
    public function Modules()
    {
        $id = Auth::user()->id;

        $modules = Module::orderBy('id', 'desc')->get();


        return view('admin.backend.modules.modules', compact('modules'));
    }

    /**
     * 2. Affiche le formulaire d'ajout de module
     */

     public function AddModule()
     {
         $categories = Category::orderBy('category_name', 'asc')->get();
         $subcategories = SubCategory::orderBy('subcategory_name', 'asc')->get();
         $formateurs = User::where('role', 'formateur')->orderBy('name')->get();
          $evaluations = Evaluation::orderBy('titre')->get();
        return view('admin.backend.modules.add_module', compact('categories', 'subcategories', 'formateurs', 'evaluations'));
     }

    /**
     * 🔹 3. Enregistre un module en base de données
     */
    public function StoreModule(Request $request)
{
    $request->validate([
        'module_name' => 'required|string|max:255',
        'module_title' => 'required|string|max:255',
        'formateur_id' => 'required|exists:users,id',
        'category_id' => 'required|integer',
        'subcategory_id' => 'nullable|integer',
        'certificat' => 'required|in:1,0',
        'label' => 'nullable|string|max:255',
        'duree' => 'nullable|string|max:100',
        'resources' => 'nullable|string|max:255',
        'prerequi' => 'nullable|string',
        'module_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'header_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'video' => ['nullable', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/i'],
        'evaluation_id' => 'nullable|exists:evaluations,id',



    ]);

    // ✅ Gère l'upload image
    $imagePath = null;
    if ($request->hasFile('module_image')) {
        $image = $request->file('module_image');
        $imageName = time() . '_' . Str::slug($request->module_name) . '.' . $image->getClientOriginalExtension();
        $image->storeAs('uploads/modules/images', $imageName, 'public');
        $imagePath = 'uploads/modules/images/' . $imageName;
    }

    $headerImagePath = null;
    if ($request->hasFile('header_image')) {
        $headerImage = $request->file('header_image');
        $headerImageName = time() . '_header_' . Str::slug($request->module_name) . '.' . $headerImage->getClientOriginalExtension();
        $headerImage->storeAs('uploads/modules/headers', $headerImageName, 'public');
        $headerImagePath = 'uploads/modules/headers/' . $headerImageName;
    }

    // ✅ Crée le module avec l’URL directe pour la vidéo
    Module::create([
        'category_id' => $request->category_id,
        'subcategory_id' => $request->subcategory_id,
        'formateur_id' => $request->formateur_id,
        'module_name' => $request->module_name,
        'module_name_slug' => Str::slug($request->module_name),
        'module_title' => $request->module_title,
        'description' => $request->description,
        'module_image' => $imagePath,
        'header_image' => $headerImagePath,
        'video' => $request->video,
        'label' => $request->label,
        'duree' => $request->duree,
        'resources' => $request->resources,
        'certificat' => $request->certificat,
        'prerequi' => $request->prerequi,
        'bestseller' => $request->has('bestseller') ? 1 : 0,
        'vedette' => $request->has('vedette') ? 1 : 0,
        'surevalue' => $request->has('surevalue') ? 1 : 0,
        'status' => $request->has('status') ? 1 : 0,
        'evaluation_id' => $request->evaluation_id,
    ]);

    return redirect()->route('admin.modules')->with('success', 'Module ajouté avec succès !');
}


    // 🔹 4. Affiche le formulaire de modification d’un module

    public function EditModule($id)
    {
        $module = Module::findOrFail($id);

        $categories = Category::orderBy('category_name', 'asc')->get();
        $subcategories = SubCategory::orderBy('subcategory_name', 'asc')->get();
        $formateurs = User::where('role', 'formateur')->orderBy('name')->get();
        $evaluations = Evaluation::orderBy('titre')->get();

return view('admin.backend.modules.edit_module', compact('module', 'categories', 'subcategories', 'formateurs', 'evaluations'));
    }

    public function UpdateModule(Request $request, $id)
{
    $module = Module::findOrFail($id);

    $request->validate([
        'module_name' => 'required|string|max:255',
        'module_title' => 'required|string|max:255',
        'category_id' => 'required|integer|exists:categories,id',
        'subcategory_id' => 'nullable|integer|exists:subcategories,id',
        'certificat' => 'required|in:1,0',
        'video' => ['nullable', 'url', 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/i'],
        'evaluation_id' => 'nullable|exists:evaluations,id',
        'formateur_id' => 'required|exists:users,id',


    ]);

    $imagePath = $module->module_image;
    if ($request->hasFile('module_image')) {
        if ($module->module_image) {
            Storage::disk('public')->delete($module->module_image);
        }

        $image = $request->file('module_image');
        $imageName = time() . '_' . Str::slug($request->module_name) . '.' . $image->getClientOriginalExtension();
        $image->storeAs('uploads/modules/images', $imageName, 'public');
        $imagePath = 'uploads/modules/images/' . $imageName;
    }

    $headerImagePath = $module->header_image;
    if ($request->hasFile('header_image')) {
        if ($module->header_image) {
            Storage::disk('public')->delete($module->header_image);
        }

        $headerImage = $request->file('header_image');
        $headerImageName = time() . '_header_' . Str::slug($request->module_name) . '.' . $headerImage->getClientOriginalExtension();
        $headerImage->storeAs('uploads/modules/headers', $headerImageName, 'public');
        $headerImagePath = 'uploads/modules/headers/' . $headerImageName;
    }


    // ✅ Vidéo = URL directe
    $videoPath = $request->video;

    $module->update([
        'category_id' => $request->category_id,
        'subcategory_id' => $request->subcategory_id,
        'formateur_id' => $request->formateur_id, // ← AJOUT ESSENTIEL

        'module_name' => $request->module_name,
        'module_name_slug' => Str::slug($request->module_name),
        'module_title' => $request->module_title,
        'description' => $request->description,

        'module_image' => $imagePath,
        'header_image' => $headerImagePath,
        'video' => $videoPath,
        'label' => $request->label,
        'duree' => $request->duree,
        'resources' => $request->resources,
        'certificat' => $request->certificat,
        'prerequi' => $request->prerequi,
        'bestseller' => $request->has('bestseller') ? 1 : 0,
        'vedette' => $request->has('vedette') ? 1 : 0,
        'surevalue' => $request->has('surevalue') ? 1 : 0,
        'status' => $request->has('status') ? 1 : 0,
        'evaluation_id' => $request->evaluation_id,

    ]);
    

    
    return redirect()->route('admin.modules')->with('success', 'Module mis à jour avec succès !');
}


     // 🔹 6. Supprime un module et ses fichiers liés
    public function DeleteModule($id)
    {
        $module = Module::findOrFail($id);

        if ($module->module_image) {
            Storage::disk('public')->delete($module->module_image);
        }
        if ($module->video) {
            Storage::disk('public')->delete($module->video);
        }

        $module->delete();

        return redirect()->route('admin.modules')->with('success', 'Module supprimé avec succès !');
    }

    /**
     * 🔹 7. Affiche la vue pour ajouter des lectures à un module
     */
    public function AddModuleLecture($id){

        $module = Module::find($id);

        $section = ModuleSection::where('module_id',$id)->latest()->get();

         return view('admin.backend.modules.section.add_module_lecture',compact('module','section'));

    }// End Method

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



    // 🔹 8. Ajoute une section à un module (titre de section uniquement)

    public function AddModuleSection(Request $request)
    {

        $cid = $request->module_id;

        ModuleSection::insert([
            'module_id' => $cid,
            'section_title' => $request->section_title,
        ]);

        $notification = array(
            'message' => 'Module Section Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }// End Method

    public function EditModuleSection($id){
        $section = ModuleSection::findOrFail($id);
        return view('admin.backend.modules.section.edit_module_section', compact('section'));
    }

    public function UpdateModuleSection(Request $request, $id)
{
    $request->validate([
        'section_title' => 'required|string|max:255',
        'section_html' => 'nullable|string',
        'objectif' => 'nullable|string',
        'methode' => 'nullable|string',
        'contexte' => 'nullable|string',
        'video_url' => 'nullable|string|max:255',
    ]);

    $section = ModuleSection::findOrFail($id);

    // ✅ Traitement du champ vidéo
    $videoName = $request->input('video_url');
    $videoPath = $videoName;

    // ✅ Mise à jour des données
    $section->update([
        'section_title' => $request->section_title,
        'section_html' => $request->section_html,
        'objectif' => $request->objectif,
        'methode' => $request->methode,
        'contexte' => $request->contexte,
        'video_url' => $videoPath,
    ]);

    return redirect()->route('admin.modules.lecture.add', $section->module_id)
        ->with('success', 'Section mise à jour avec succès !');
}


    public function section($id, $section_id)
        {
            $module = Module::with('sections.lectures')->findOrFail($id);
            $section = $module->sections->where('id', $section_id)->first();

            if (!$section) {
                abort(404, 'Section non trouvée');
            }

            // 🔁 Calcul lectureStats
            $lectureStats = [];
            $userId = auth()->id();

            foreach ($module->sections->flatMap->lectures as $lecture) {
                $totalQuestions = $lecture->question_count ?? 0;

                $grouped = ScormInteraction::where('user_id', $userId)
                    ->where('lecture_id', $lecture->id)
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy('interaction_id');

                $answered = 0;
                $correct = 0;

                foreach ($grouped as $attempts) {
                    $latestAttempt = $attempts->last();
                    if (!empty($latestAttempt)) {
                        $answered++;
                        if ($latestAttempt->result === 'correct') {
                            $correct++;
                        }
                    }
                }

                $score = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100) : null;

                $status = 'not_started';
                if ($answered === 0) {
                    $status = 'not_started';
                } elseif ($answered < $totalQuestions) {
                    $status = 'incomplete';
                } elseif ($score >= 50) {
                    $status = 'acquired';
                } else {
                    $status = 'not_acquired';
                }

                $lectureStats[$lecture->id] = [
                    'lecture_id' => $lecture->id,
                    'status' => $status,
                    'score' => $score,
                    'answered' => $answered,
                    'correct' => $correct,
                    'slides' => $lecture->slide_count ?? 0,
                    'questions' => $totalQuestions,
                ];
            }

            // 🔁 Calcul sectionStatuses
            $sectionStatuses = [];
            foreach ($module->sections as $sec) {
                $total = $sec->lectures->count();
                $acquired = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                    return ($lectureStats[$lec->id]['status'] ?? null) === 'acquired';
                })->count();

                $sectionStatuses[$sec->id] = $acquired === $total
                    ? 'completed'
                    : ($acquired > 0 ? 'in_progress' : 'not_started');
            }

            return view('frontend.modules.section', [
                'module' => $module,
                'selectedSection' => $section,
                'lectureStats' => $lectureStats,
                'sectionStatuses' => $sectionStatuses,
                'selectedLecture' => null, // pour éviter les erreurs dans la sidebar
            ]);
        }




    // 🔹 9. Sauvegarde une nouvelle lecture (cours) dans une section
    public function SaveLecture(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'section_id' => 'required|exists:module_sections,id',
            'lecture_title' => 'required|string|max:255',
        ]);

       $lastPosition = ModuleLecture::where('section_id', $request->section_id)->max('position') ?? 0;

        ModuleLecture::create([
            'module_id' => $request->module_id,
            'section_id' => $request->section_id,
            'lecture_title' => $request->lecture_title,
            'position' => $lastPosition + 1,
            'slide_count' => 0,
            'question_count' => 0,
            'scorm_path' => null,
        ]);

        return response()->json(['success' => 'Leçon enregistrée avec succès.']);
    }


    // 🔹 10. Affiche le formulaire d’édition d’une lecture
    public function EditLecture($id){

        $mlecture = ModuleLecture::find($id);
        return view('admin.backend.modules.lecture.edit_module_lecture',compact('mlecture'));

    }// End Method

    // 🔹 11. Met à jour les données d’une lecture
    public function UpdateModuleLecture(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:module_lectures,id',
            'lecture_title' => 'required|string|max:255',
            'scorm_path' => 'nullable|string|max:255',
            'slide_count' => 'nullable|integer|min:0',
            'question_count' => 'nullable|integer|min:0',
        ]);

        $lecture = ModuleLecture::findOrFail($request->id);

        $lecture->update([
            'lecture_title' => $request->lecture_title,
            'scorm_path' => $request->scorm_path,
            'slide_count' => $request->slide_count ?? 0,
            'question_count' => $request->question_count ?? 0,
        ]);

        return redirect()->route('admin.modules.lecture.add', ['id' => $lecture->module_id])
                        ->with('success', 'La lecture a été mise à jour avec succès.');
    }


    // 🔹 12. Supprime une lecture d’un

    public function DeleteLecture($id){

        ModuleLecture::find($id)->delete();

        $notification = array(
            'message' => 'Module Lecture Delete Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }// End Method

    public function DeleteSection($id){

        $section = ModuleSection::find($id);

        /// Delete reated lectures
        $section->lectures()->delete();
        // Delete the section
        $section->delete();

        $notification = array(
            'message' => 'Module Section Delete Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }// End Method

    //  Affiche le détail d'un module de formation

    public function show($id)
    {
        // Chargement du module avec ses sections et les lectures de chaque section
        $module = Module::with('sections.lectures')->findOrFail($id);

        return view('frontend.contenu.module_detail', compact('module'));
    }
    public function lire($module, $section, $lesson)
    {
        $module = Module::with('sections.lectures')->findOrFail($module);
        $sectionModel = $module->sections->firstWhere('id', $section);

        if (!$sectionModel) {
            abort(404, 'Section non trouvée');
        }

        $selectedLecture = $sectionModel->lectures->firstWhere('id', $lesson);

        if (!$selectedLecture) {
            abort(404, 'Leçon non trouvée');
        }

        // 🔁 Détermination de la prochaine leçon
        $nextLecture = null;
        $lectures = $module->sections->flatMap->lectures;
        $currentIndex = $lectures->search(fn($lec) => $lec->id === $selectedLecture->id);
        $nextLecture = $lectures->get($currentIndex + 1);

        // 🔁 Progression enrichie (slides, questions, score, statut)
        $userId = auth()->id();
        $lectureStats = [];

        foreach ($lectures as $lecture) {
            $totalQuestions = $lecture->question_count ?? 0;

            $grouped = ScormInteraction::where('user_id', $userId)
                ->where('lecture_id', $lecture->id)
                ->orderBy('created_at')
                ->get()
                ->groupBy('interaction_id');

            $answered = 0;
            $correct = 0;

            foreach ($grouped as $attempts) {
                $latestAttempt = $attempts->last();
                if (!empty($latestAttempt)) {
                    $answered++;
                    if ($latestAttempt->result === 'correct') {
                        $correct++;
                    }
                }
            }

            $score = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100) : null;

            $status = 'not_started';
            if ($answered === 0) {
                $status = 'not_started';
            } elseif ($answered < $totalQuestions) {
                $status = 'incomplete';
            } elseif ($score >= 50) {
                $status = 'acquired';
            } else {
                $status = 'not_acquired';
            }

            $lectureStats[$lecture->id] = [
                'lecture_id' => $lecture->id,
                'status' => $status,
                'score' => $score,
                'answered' => $answered,
                'correct' => $correct,
                'slides' => $lecture->slide_count ?? 0,
                'questions' => $totalQuestions,
            ];
        }

        // 🔁 Statuts par section
        $sectionStatuses = [];
        foreach ($module->sections as $section) {
            $lectures = $section->lectures;
            $total = $lectures->count();
            $acquired = $lectures->filter(function ($lec) use ($lectureStats) {
                return ($lectureStats[$lec->id]['status'] ?? null) === 'acquired';
            })->count();

            $sectionStatuses[$section->id] = $acquired === $total
                ? 'completed'
                : ($acquired > 0 ? 'in_progress' : 'not_started');
        }

        return view('frontend.modules.lecture', compact(
            'module',
            'selectedLecture',
            'nextLecture',
            'lectureStats',
            'sectionStatuses'
        ));
    }




}
