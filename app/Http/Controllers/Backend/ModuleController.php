<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Backend/ModuleController.php
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
            'objectifs'   => 'nullable|array',
            'objectifs.*' => 'nullable|string|max:255',
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
            'objectifs' => $request->filled('objectifs')
            ? array_values(array_filter($request->input('objectifs')))
            : [],


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
            'objectifs'   => 'nullable|array',
            'objectifs.*' => 'nullable|string|max:255',
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
            'objectifs' => $request->filled('objectifs')
    ? array_values(array_filter($request->input('objectifs')))
    : [],
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
        'section_title' => ['required', 'string', 'max:255'],
        'section_html'  => ['nullable', 'string', 'max:20000'],
        'objectif'      => ['nullable', 'string', 'max:20000'],
        'methode'       => ['nullable', 'string', 'max:20000'],
        'contexte'      => ['nullable', 'string', 'max:20000'],
        'video_url'     => ['nullable', 'string', 'max:255'],
        // Bouton "Sauvegarder" (reste sur la page)
        'stay'          => ['nullable', 'boolean'],
    ]);

    $section = ModuleSection::findOrFail($id);

    // Sécurisation HTML (Quill) : liste blanche minimale
    // Autorise uniquement texte + mises en forme + listes.
    $allowedTags = '<p><br><strong><em><u><ul><ol><li>';

    $sectionHtml = strip_tags((string) $request->input('section_html', ''), $allowedTags);
    $objectif    = strip_tags((string) $request->input('objectif', ''), $allowedTags);
    $methode     = strip_tags((string) $request->input('methode', ''), $allowedTags);
    $contexte    = strip_tags((string) $request->input('contexte', ''), $allowedTags);

    // Normalisation simple (évite les contenus vides du type "<p><br></p>")
    $normalize = function (string $html): ?string {
        $html = trim($html);
        if ($html === '') {
            return null;
        }

        $plain = trim(strip_tags($html));
        return $plain === '' ? null : $html;
    };

    $section->update([
        'section_title' => $request->input('section_title'),
        'section_html'  => $normalize($sectionHtml),
        'objectif'      => $normalize($objectif),
        'methode'       => $normalize($methode),
        'contexte'      => $normalize($contexte),
        'video_url'     => $request->input('video_url'),
    ]);

    // Si clic sur "Sauvegarder" : rester sur la page d'édition
    if ($request->boolean('stay')) {
        return redirect()
            ->route('admin.sections.edit', $section->id)
            ->with('success', 'Section sauvegardée.');
    }

    // Sinon : comportement actuel
    return redirect()
        ->route('admin.modules.lecture.add', $section->module_id)
        ->with('success', 'Section mise à jour avec succès !');
}



    /**
     * 9. Vue Section (stagiaire ou formateur)
     */
    public function section($id, $section_id)
    {
        $user = auth()->user();

        // 1) Charger module + sections + leçons
        $module = Module::with('sections.lectures')->findOrFail($id);
        if (! $module->isVisibleTo($user)) abort(404);

        // 2) Appliquer la personnalisation "groupe x module x leçon" (si elle existe)
        //    - masque les leçons désactivées
        //    - applique l’ordre personnalisé
        $groupId = null;

        // Récupère le 1er groupe du stagiaire qui a ce module (adaptable si multi-groupes)
        $groupId = \Illuminate\Support\Facades\DB::table('group_user')
            ->join('group_module', 'group_module.group_id', '=', 'group_user.group_id')
            ->where('group_user.user_id', $user->id)
            ->where('group_module.module_id', $module->id)
            ->value('group_user.group_id');

        if ($groupId) {
            $overrides = \App\Models\GroupModuleLecture::query()
                ->where('group_id', (int) $groupId)
                ->where('module_id', (int) $module->id)
                ->get()
                ->keyBy('lecture_id');

            if ($overrides->isNotEmpty()) {
                $module->sections->each(function ($sec) use ($overrides) {
                    $sec->setRelation(
                        'lectures',
                        $sec->lectures
                            ->filter(function ($lec) use ($overrides) {
                                $row = $overrides->get($lec->id);
                                return $row ? (bool) $row->is_enabled : true;
                            })
                            ->sortBy(function ($lec) use ($overrides) {
                                $row = $overrides->get($lec->id);
                                // si la leçon n’a pas d’override, on retombe sur l’ordre “normal”
                                return $row ? (int) $row->position : (int) ($lec->position ?? 999999);
                            })
                            ->values()
                    );
                });
            }
        }

        // 3) Section sélectionnée (après filtrage)
        $section = $module->sections->firstWhere('id', (int) $section_id);
        if (! $section) abort(404, 'Section non trouvée');

        // 4) Calcul stats leçons (robuste aux interaction_id vides)
        $userId     = $user->id;
        $lectures   = $module->sections->flatMap->lectures;
        $lectureIds = $lectures->pluck('id')->all();

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

            // Groupe par interaction_id si présent, sinon par ligne (évite l’écrasement)
            $groups = $rows->groupBy(function ($row) {
                $key = trim((string) $row->interaction_id);
                return $key !== '' ? $key : 'row_' . $row->id;
            });

            $answered = 0;
            $correct  = 0;

            foreach ($groups as $attempts) {
                $latest = $attempts->last(); // dernière réponse sur cette question
                $answered++;

                if ($latest && $latest->result === 'correct') {
                    $correct++;
                }
            }

            $answeredCapped = $totalQuestions > 0 ? min($answered, $totalQuestions) : $answered;
            $correctCapped  = $totalQuestions > 0 ? min($correct,  $totalQuestions) : $correct;

            $score = $totalQuestions > 0
                ? (int) round(($correctCapped / max(1, $totalQuestions)) * 100)
                : ($answeredCapped > 0 ? 100 : null);

            // Statut
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

        // 5) Statuts des sections (sur les leçons visibles après filtrage)
        $sectionStatuses = [];
        foreach ($module->sections as $sec) {
            $total = $sec->lectures->count();
            if ($total === 0) {
                $sectionStatuses[$sec->id] = 'not_started';
                continue;
            }

            $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                return in_array($lectureStats[$lec->id]['status'] ?? null, ['acquired', 'completed'], true);
            })->count();

            $sectionStatuses[$sec->id] = ($ok === $total)
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
    $user = auth()->user();

    // 1) Charger module + sections + leçons
    $module = Module::with('sections.lectures')->findOrFail($module);
    if (! $module->isVisibleTo($user)) abort(404);

    // 2) Appliquer la personnalisation "groupe x module x leçon" (si elle existe)
    $groupId = \Illuminate\Support\Facades\DB::table('group_user')
        ->join('group_module', 'group_module.group_id', '=', 'group_user.group_id')
        ->where('group_user.user_id', $user->id)
        ->where('group_module.module_id', $module->id)
        ->value('group_user.group_id');

    if ($groupId) {
        $overrides = \App\Models\GroupModuleLecture::query()
            ->where('group_id', (int) $groupId)
            ->where('module_id', (int) $module->id)
            ->get()
            ->keyBy('lecture_id');

        if ($overrides->isNotEmpty()) {
            $module->sections->each(function ($sec) use ($overrides) {
                $sec->setRelation(
                    'lectures',
                    $sec->lectures
                        ->filter(function ($lec) use ($overrides) {
                            $row = $overrides->get($lec->id);
                            return $row ? (bool) $row->is_enabled : true;
                        })
                        ->sortBy(function ($lec) use ($overrides) {
                            $row = $overrides->get($lec->id);
                            return $row ? (int) $row->position : (int) ($lec->position ?? 999999);
                        })
                        ->values()
                );
            });
        }
    }

    // 3) Section / leçon sélectionnées (après filtrage)
    $sectionModel = $module->sections->firstWhere('id', (int) $section);
    if (! $sectionModel) abort(404, 'Section non trouvée');

    $selectedLecture = $sectionModel->lectures->firstWhere('id', (int) $lesson);
    if (! $selectedLecture) abort(404, 'Leçon non trouvée');

    // 4) Liste ordonnée des leçons (après filtrage) + Next lecture
    $lectures = $module->sections->flatMap->lectures->values();

    $currentIndex = $lectures->search(fn ($lec) => (int) $lec->id === (int) $selectedLecture->id);
    $nextLecture  = ($currentIndex !== false) ? $lectures->get($currentIndex + 1) : null;

    // 5) Stats leçons (robuste aux interaction_id vides)
    $userId     = $user->id;
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
            return $key !== '' ? $key : 'row_' . $row->id;
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
            ? (int) round(($correctCapped / max(1, $totalQuestions)) * 100)
            : ($answeredCapped > 0 ? 100 : null);

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

    // 6) Statuts des sections (sur les leçons visibles après filtrage)
    $sectionStatuses = [];
    foreach ($module->sections as $sec) {
        $total = $sec->lectures->count();

        if ($total === 0) {
            $sectionStatuses[$sec->id] = 'not_started';
            continue;
        }

        $acq = $sec->lectures->filter(function ($lec) use ($lectureStats) {
            return ($lectureStats[$lec->id]['status'] ?? null) === 'acquired';
        })->count();

        $sectionStatuses[$sec->id] = ($acq === $total)
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
    private function resolveGroupIdForUserAndModule(int $userId, int $moduleId): ?int
    {
        // 1) groupes où l’utilisateur est membre + module rattaché
        $gid = DB::table('group_user')
            ->join('group_module', 'group_module.group_id', '=', 'group_user.group_id')
            ->where('group_user.user_id', $userId)
            ->where('group_module.module_id', $moduleId)
            ->value('group_user.group_id');

        return $gid ? (int) $gid : null;
    }

    private function applyGroupLessonOverrides(Module $module, ?int $groupId): void
    {
        if (!$groupId) return;

        $over = \App\Models\GroupModuleLecture::query()
            ->where('group_id', $groupId)
            ->where('module_id', $module->id)
            ->get()
            ->keyBy('lecture_id');

        if ($over->isEmpty()) return;

        // Filtrer + trier les leçons dans chaque section
        $module->sections->each(function ($sec) use ($over) {
            $sec->setRelation('lectures', $sec->lectures
                ->filter(function ($lec) use ($over) {
                    $row = $over->get($lec->id);
                    return $row ? (bool) $row->is_enabled : true;
                })
                ->sortBy(function ($lec) use ($over) {
                    $row = $over->get($lec->id);
                    return $row ? (int) $row->position : (int) $lec->position;
                })
                ->values()
            );
        });
    }

}
