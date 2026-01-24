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
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use Illuminate\Support\Facades\DB;
use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;


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
            ->withSum('lectures as quiz_questions_planned', 'quiz_questions_per_attempt')
            ->latest('id')
            ->get();

        return view('admin.backend.modules.modules', compact('modules'));
    }

    public function toggleStatus(\App\Models\Module $module)
    {
       $module->update(['status' => $module->status ? 0 : 1]);
        return back()->with('success', $module->status ? 'Module activé' : 'Module désactivé');
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
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
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
            'module_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
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
            'quiz_questions_per_attempt'=> 0,
            'scorm_path'    => null,
        ]);

        return response()->json(['success' => 'Leçon enregistrée avec succès.']);
    }

    /**
     * 11. Formulaire d’édition d’une lecture (admin)
     */


public function EditLecture($id)
{
    // Leçon + compteur de questions du quiz
    $mlecture = ModuleLecture::withCount([
        // relation quizQuestions => quiz_questions.lecture_id
        'quizQuestions as quiz_questions_count'
    ])->findOrFail($id);

    // Charger le SCORM déjà lié à la leçon + ses versions
    $mlecture->load([
        'scormPackage.activeVersion',
        'scormPackage.versions' => fn ($q) => $q->orderByDesc('id'),
        'scormPackageVersion',
    ]);

    // Liste des SCORM sélectionnables
    $packages = ScormPackage::select('id', 'name', 'slug', 'active_version_id')
        ->orderBy('name')
        ->get();

    return view(
        'admin.backend.modules.lecture.edit_module_lecture',
        [
            'mlecture'            => $mlecture,
            'packages'            => $packages,
            // optionnel si tu veux l’utiliser explicitement dans la vue
            'quizQuestionsCount'  => $mlecture->quiz_questions_count,
        ]
    );
}





    /**
     * 12. Mise à jour d’une lecture (admin)
     */
    public function UpdateModuleLecture(Request $request)
        {
            $validated = $request->validate([
                'id' => 'required|exists:module_lectures,id',
                'lecture_title' => 'required|string|max:255',

                // Compat (ancien champ)
                'scorm_path' => 'nullable|string|max:255',

                // Bibliothèque SCORM
                'scorm_package_id' => 'nullable|exists:scorm_packages,id',
                'use_active_scorm_version' => 'nullable|in:0,1',
                'scorm_package_version_id' => 'nullable|exists:scorm_package_versions,id',

                // Indicateurs / quiz
                'slide_count' => 'nullable|integer|min:0',
                'quiz_enabled' => 'nullable|in:0,1',
                'quiz_questions_per_attempt' => 'exclude_unless:quiz_enabled,1|required|integer|min:1',
            ]);

            $lecture = ModuleLecture::findOrFail($validated['id']);

            // IMPORTANT : éviter le piège (bool)"0" == true
            $useActive = ($request->input('use_active_scorm_version', '1') === '1');

            // Quiz : cohérence des valeurs
            $quizEnabled = ($request->input('quiz_enabled', '0') === '1');

            // Si on ne suit pas la version active, une version doit être choisie
            if (!$useActive && !$request->filled('scorm_package_version_id')) {
                return back()
                    ->withErrors([
                        'scorm_package_version_id' => 'Sélectionne une version SCORM (ou active “version active”).',
                    ])
                    ->withInput();
            }

            // Vérifier que la version appartient bien au package
            if ($request->filled('scorm_package_id') && $request->filled('scorm_package_version_id')) {
                $ver = ScormPackageVersion::find($request->input('scorm_package_version_id'));

                if (!$ver || (int) $ver->scorm_package_id !== (int) $request->input('scorm_package_id')) {
                    return back()
                        ->withErrors([
                            'scorm_package_version_id' => 'La version sélectionnée ne correspond pas au SCORM choisi.',
                        ])
                        ->withInput();
                }
            }

            $lecture->update([
                'lecture_title' => $validated['lecture_title'],

                // Compat (ancien champ)
                'scorm_path' => $validated['scorm_path'] ?? null,

                // Bibliothèque
                'scorm_package_id' => $request->input('scorm_package_id'),
                'use_active_scorm_version' => $useActive,
                'scorm_package_version_id' => $useActive ? null : $request->input('scorm_package_version_id'),

                // Indicateurs / quiz
                'slide_count' => (int) $request->input('slide_count', 0),
                'quiz_enabled' => $quizEnabled,
                'quiz_questions_per_attempt' => $quizEnabled
                    ? (int) $request->input('quiz_questions_per_attempt')
                    : 0,
            ]);

            $action = $request->input('save_action', 'back');

            if ($action === 'stay') {
                return redirect()
                    ->back()
                    ->with('success', 'La lecture a été mise à jour avec succès.');
            }

            return redirect()
                ->route('admin.modules.lecture.add', ['id' => $lecture->module_id])
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

    // ModuleController.php

public function section(Request $request, Module $module, ModuleSection $section)
{
    $user = auth()->user();

    // Sécurité : cohérence URL + visibilité
    abort_unless((int) $section->module_id === (int) $module->id, 404);
    abort_unless($module->isVisibleTo($user), 404);

    // Charger sections + lectures (ordre admin)
    $module->load([
        'sections' => function ($q) {
            $q->orderBy('id')
              ->with(['lectures' => function ($qq) {
                  $qq->orderBy('position')->orderBy('id');
              }]);
        },
    ]);

    // Appliquer personnalisation formateur (groupe x module x leçon) si elle existe
    $groupId = $this->resolveGroupIdForUserAndModule((int) $user->id, (int) $module->id);
    $this->applyGroupLessonOverrides($module, $groupId);

    // Re-récupérer la section après filtrage (au cas où elle existe mais sans leçons)
    $section = $module->sections->firstWhere('id', (int) $section->id);
    abort_unless($section, 404);

    // Toutes les leçons visibles du module (pour buildLectureStats)
    $lectures = $module->sections->flatMap(fn ($s) => $s->lectures);

    // Stats sidebar (quiz si activé, sinon SCORM)
    $lectureStats = $this->buildLectureStats($lectures, (int) $user->id);

    // Statut par section (sur le périmètre visible après filtrage)
    $sectionStatuses = $module->sections->mapWithKeys(function ($sec) use ($lectureStats) {
        $total = $sec->lectures->count();

        if ($total === 0) {
            return [$sec->id => 'not_started'];
        }

        $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
            $st = $lectureStats[$lec->id]['status'] ?? null;
            return in_array($st, ['completed'], true);
        })->count();

        return [$sec->id => ($ok === $total ? 'completed' : ($ok > 0 ? 'in_progress' : 'not_started'))];
    });

    return view($this->viewBase() . '.chapitre', [
        'module'          => $module,
        'selectedSection' => $section,
        'section'         => $section, // compat éventuelle
        'selectedLecture' => null,
        'lectureStats'    => $lectureStats,
        'sectionStatuses' => $sectionStatuses,
        'formateur'       => $module->formateur ?? null,
    ]);
}

public function lire(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture)
{
    $user = auth()->user();

    // Sécurité : cohérence URL + visibilité
    abort_unless((int) $section->module_id === (int) $module->id, 404);
    abort_unless((int) $lecture->module_id === (int) $module->id, 404);
    abort_unless((int) $lecture->section_id === (int) $section->id, 404);
    abort_unless($module->isVisibleTo($user), 404);

    // Charger infos SCORM (si présent)
    $lecture->load(['scormPackage.activeVersion', 'scormPackageVersion']);

    // Charger sections + lectures (sidebar + navigation)
    $module->load([
        'sections' => function ($q) {
            $q->orderBy('id')
              ->with(['lectures' => function ($qq) {
                  $qq->orderBy('position')->orderBy('id');
              }]);
        },
    ]);

    // Appliquer personnalisation formateur (groupe x module x leçon) si elle existe
    $groupId = $this->resolveGroupIdForUserAndModule((int) $user->id, (int) $module->id);
    $this->applyGroupLessonOverrides($module, $groupId);

    // Re-récupérer la section après filtrage
    $section = $module->sections->firstWhere('id', (int) $section->id);
    abort_unless($section, 404);

    // Bloquer l’accès si la leçon est masquée par la personnalisation
    $visibleIds = $module->sections->flatMap(fn ($s) => $s->lectures)->pluck('id')->all();
    abort_unless(in_array((int) $lecture->id, $visibleIds, true), 404);

    // Toutes les leçons visibles du module (pour buildLectureStats)
    $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();

    // Stats sidebar (quiz si activé, sinon SCORM)
    $lectureStats = $this->buildLectureStats($lectures, (int) $user->id);

    // Déterminer la prochaine leçon (navigation) sur la liste visible et ordonnée
    $nextLecture = null;

    // 1) prochaine dans la section courante
    $currentSectionLectures = $section->lectures ?? collect();
    $idx = $currentSectionLectures->search(fn ($l) => (int) $l->id === (int) $lecture->id);

    if ($idx !== false) {
        $nextLecture = $currentSectionLectures->get($idx + 1);
    }

    // 2) sinon : première leçon de la section suivante (ordre sections par id)
    if (!$nextLecture) {
        $sections = $module->sections->sortBy('id')->values();
        $currentSectionIndex = $sections->search(fn ($s) => (int) $s->id === (int) $section->id);

        if ($currentSectionIndex !== false) {
            $nextSection = $sections->get($currentSectionIndex + 1);
            if ($nextSection) {
                $nextLecture = $nextSection->lectures->first();
            }
        }
    }

    // Format attendu par le blade : ['id' => .., 'section_id' => ..]
    $nextLecturePayload = $nextLecture ? [
        'id'         => (int) $nextLecture->id,
        'section_id' => (int) $nextLecture->section_id,
    ] : null;

    // Statut par section (optionnel, sur périmètre visible)
    $sectionStatuses = $module->sections->mapWithKeys(function ($sec) use ($lectureStats) {
        $total = $sec->lectures->count();

        if ($total === 0) {
            return [$sec->id => 'not_started'];
        }

        $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
            $st = $lectureStats[$lec->id]['status'] ?? null;
            return in_array($st, ['completed'], true);
        })->count();

        return [$sec->id => ($ok === $total ? 'completed' : ($ok > 0 ? 'in_progress' : 'not_started'))];
    });

    return view($this->viewBase() . '.lecon', [
        'module'          => $module,
        'section'         => $section,
        'selectedSection' => $section,
        'lecture'         => $lecture,
        'selectedLecture' => $lecture,
        'lectureStats'    => $lectureStats,
        'sectionStatuses' => $sectionStatuses,
        'nextLecture'     => $nextLecturePayload,
        'formateur'       => $module->formateur ?? null,
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

    private function buildLectureStats($lectures, int $userId): array
    {
        $lectureIds = $lectures->pluck('id')->all();

        // A) Quiz : dernière tentative par leçon
        $attempts = QuizAttempt::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->orderByDesc('finished_at') // terminées d’abord (non null)
            ->orderByDesc('id')
            ->get()
            ->groupBy('lecture_id')
            ->map(fn($rows) => $rows->first());

        $attemptIds = $attempts->filter()->pluck('id')->all();

        $attemptAgg = collect();
        if (!empty($attemptIds)) {
            $attemptAgg = QuizAttemptQuestion::query()
                ->select([
                    'attempt_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN answered_at IS NOT NULL THEN 1 ELSE 0 END) as answered'),
                    DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                ])
                ->whereIn('attempt_id', $attemptIds)
                ->groupBy('attempt_id')
                ->get()
                ->keyBy('attempt_id');
        }

        // B) SCORM “diapositives” : démarré / terminé
        $scores = ScormScore::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->get()
            ->keyBy('lecture_id');

        $started = ScormResult::query()
            ->where('user_id', $userId)
            ->whereIn('lecture_id', $lectureIds)
            ->select('lecture_id', DB::raw('COUNT(*) as c'))
            ->groupBy('lecture_id')
            ->pluck('c', 'lecture_id');

        $stats = [];

        foreach ($lectures as $lec) {

            // 1) Si quiz activé : la sidebar dépend du quiz
            if ((bool) ($lec->quiz_enabled ?? false)) {
                $attempt = $attempts->get($lec->id);
                $planned = (int) ($lec->quiz_questions_per_attempt ?? 0);

                if (!$attempt) {
                    $stats[$lec->id] = [
                        'status' => 'not_started',
                        'quiz' => true,
                        'questions_total' => $planned,
                        'questions_answered' => 0,
                        'questions_correct' => 0,
                        'quiz_score' => null,
                        'quiz_finished' => false,
                        'slides' => (int)($lec->slide_count ?? 0),
                        'session_time' => null,
                    ];
                    continue;
                }

                $agg = $attemptAgg->get($attempt->id);
                $total    = (int)($agg->total ?? $attempt->total_questions ?? $planned ?? 0);
                $answered = (int)($agg->answered ?? 0);
                $correct  = (int)($agg->correct ?? 0);
                $score    = ($total > 0) ? (int) round(($correct / $total) * 100) : null;
                $finished = !is_null($attempt->finished_at);

                // Statut sidebar (à adapter si tu as une autre règle que 50%)
                if (!$finished) {
                    $status = $answered > 0 ? 'in_progress' : 'not_started';
                } else {
                    $status = ($score !== null && $score >= 50) ? 'completed' : 'failed';
                }

                $stats[$lec->id] = [
                    'status' => $status,
                    'quiz' => true,
                    'questions_total' => $total,
                    'questions_answered' => $answered,
                    'questions_correct' => $correct,
                    'quiz_score' => $score,
                    'quiz_finished' => $finished,
                    'slides' => (int)($lec->slide_count ?? 0),
                    'session_time' => null,
                ];
                continue;
            }

            // 2) Sinon : sidebar dépend du SCORM diapositives
            $hasStarted = (int)($started[$lec->id] ?? 0) > 0;
            $sc = $scores->get($lec->id);

            $lessonStatus = strtolower((string)($sc->lesson_status ?? ''));
            $isCompleted = in_array($lessonStatus, ['completed', 'passed'], true) || (bool)($sc->is_completed ?? false);

            if (!$hasStarted) $status = 'not_started';
            elseif ($isCompleted) $status = 'completed';
            else $status = 'in_progress';

            $stats[$lec->id] = [
                'status' => $status,
                'quiz' => false,
                'questions_total' => 0,
                'questions_answered' => 0,
                'questions_correct' => 0,
                'quiz_score' => null,
                'quiz_finished' => false,
                'slides' => (int)($lec->slide_count ?? 0),
                'session_time' => $sc->session_time ?? null,
            ];
        }

        return $stats;
    }




    /**
     * 15. Vue Lecture (stagiaire ou formateur)
     */


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
