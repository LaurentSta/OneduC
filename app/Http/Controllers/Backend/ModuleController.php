<?php
// /home/laurents/Oneduc_Dev/app/Http/Controllers/Backend/ModuleController.php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\ConvertLectureSlides;
use App\Models\Category;
use App\Models\Evaluation;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptQuestion;
use App\Models\ScormInteraction;
use App\Models\ScormPackage;
use App\Models\ScormPackageVersion;
use App\Models\ScormResult;
use App\Models\ScormScore;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\VideoSegmentTracking;
use App\Models\WordCloud;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\LectureObjective;
use App\Models\Competency;
use App\Services\ModuleCompletionNotifier;


class ModuleController extends Controller
{
    /**
     * Résout la base de vue selon le contexte de la route.
     * - formateur.* -> formateur.formations.*
     * - sinon -> stagiaire.formations.*
     */
    private function viewBase(): string
    {
        $name = optional(request()->route())->getName();

        if (is_string($name) && str_starts_with($name, 'formateur.')) {
            return 'formateur.formations';
        }

        if (is_string($name) && str_starts_with($name, 'observateur.')) {
            return 'observateur.formations';
        }

        return 'stagiaire.formations';
    }

    /**
     * 1) Liste des modules (admin)
     */
    public function Modules()
    {
        $modules = Module::query()
            ->with([
                'formateur:id,name',
                'category:id,category_name',
            ])
            ->withCount(['sections', 'lectures'])
            ->withSum('lectures as quiz_questions_planned', 'quiz_questions_per_attempt')
            ->latest('id')
            ->get();

        return view('admin.backend.modules.modules', compact('modules'));
    }

    public function toggleStatus(Module $module)
    {
        $module->update(['status' => $module->status ? 0 : 1]);

        return back()->with('success', $module->status ? 'Module activé' : 'Module désactivé');
    }

    /**
     * 2) Formulaire d'ajout de module (admin)
     */
    public function AddModule()
    {
        $categories    = Category::orderBy('category_name', 'asc')->get();
        $subcategories = SubCategory::orderBy('subcategory_name', 'asc')->get();
        $formateurs    = User::where('role', 'formateur')->orderBy('name')->get();
        $evaluations   = Evaluation::orderBy('titre')->get();

        return view('admin.backend.modules.add_module', compact('categories', 'subcategories', 'formateurs', 'evaluations'));
    }

    /**
     * 3) Enregistre un module (admin)
     */
    public function StoreModule(Request $request)
    {
        $request->validate([
            'module_name'     => 'required|string|max:255',
            'module_title'    => 'required|string|max:255',
            'formateur_id'    => 'required|exists:users,id',
            'category_id'     => 'required|exists:categories,id',
            'subcategory_id'  => 'nullable|exists:subcategories,id',
            'certificat'      => 'required|in:1,0',
            'label'           => 'nullable|string|max:255',
            'duree'           => 'nullable|string|max:100',
            'estimated_question_seconds' => 'nullable|integer|min:1|max:600',
            'resources'       => 'nullable|string|max:255',
            'prerequi'        => 'nullable|string',
            'module_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'header_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'module_video'    => 'nullable|string|max:255',
            'module_video_file'=> 'nullable|file|mimes:mp4,m4v,mov,avi,webm|max:307200',
            'evaluation_id'   => 'nullable|exists:evaluations,id',
        ]);

        $moduleVideo = trim((string) $request->input('module_video', ''));
        $moduleVideo = $moduleVideo === '' ? null : $moduleVideo;

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

        $module = Module::create([
            'category_id'       => $request->category_id,
            'subcategory_id'    => $request->subcategory_id,
            'formateur_id'      => $request->formateur_id,
            'module_name'       => $request->module_name,
            'module_name_slug'  => Str::slug($request->module_name),
            'module_title'      => $request->module_title,
            'description'       => $request->description,
            'module_image'      => $imagePath,
            'header_image'      => $headerImagePath,
            'module_video'      => $moduleVideo,
            'label'             => $request->label,
            'duree'             => $request->duree,
            'estimated_question_seconds' => (int) ($request->input('estimated_question_seconds', 30) ?: 30),
            'resources'         => $request->resources,
            'certificat'        => $request->certificat,
            'prerequi'          => $request->prerequi,
            'bestseller'        => $request->has('bestseller') ? 1 : 0,
            'vedette'           => $request->has('vedette') ? 1 : 0,
            'surevalue'         => $request->has('surevalue') ? 1 : 0,
            'status'            => $request->has('status') ? 1 : 0,
            'evaluation_id'     => $request->evaluation_id,
        ]);

        if ($request->hasFile('module_video_file')) {
            $module->update([
                'module_video' => $this->storeModuleVideo($module, $request->file('module_video_file')),
            ]);
        }

        return redirect()->route('admin.modules')->with('success', 'Module ajouté avec succès !');
    }

    /**
     * 4) Formulaire d’édition (admin)
     */
    public function EditModule($id)
    {
        $module        = Module::findOrFail($id);
        $categories    = Category::orderBy('category_name', 'asc')->get();
        $subcategories = SubCategory::orderBy('subcategory_name', 'asc')->get();
        $formateurs    = User::where('role', 'formateur')->orderBy('name')->get();
        $evaluations   = Evaluation::orderBy('titre')->get();

        return view('admin.backend.modules.edit_module', compact('module', 'categories', 'subcategories', 'formateurs', 'evaluations'));
    }

    public function UpdateModule(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $request->validate([
            'module_name'     => 'required|string|max:255',
            'module_title'    => 'required|string|max:255',
            'category_id'     => 'required|integer|exists:categories,id',
            'subcategory_id'  => 'nullable|integer|exists:subcategories,id',
            'certificat'      => 'required|in:1,0',
            'module_video'    => 'nullable|string|max:255',
            'module_video_file'=> 'nullable|file|mimes:mp4,m4v,mov,avi,webm|max:307200',
            'evaluation_id'   => 'nullable|exists:evaluations,id',
            'formateur_id'    => 'required|exists:users,id',
            'module_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'header_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'estimated_question_seconds' => 'nullable|integer|min:1|max:600',
        ]);

        $moduleVideo = $module->module_video;

        if ($request->has('module_video')) {
            $moduleVideo = trim((string) $request->input('module_video', ''));
            $moduleVideo = $moduleVideo === '' ? null : $moduleVideo;
        }

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

        if ($request->hasFile('module_video_file')) {
            $moduleVideo = $this->storeModuleVideo($module, $request->file('module_video_file'));
        } elseif ($module->module_video !== $moduleVideo) {
            $this->deleteManagedModuleVideo($module->module_video, $module->id);
        }

        $module->update([
            'category_id'       => $request->category_id,
            'subcategory_id'    => $request->subcategory_id,
            'formateur_id'      => $request->formateur_id,
            'module_name'       => $request->module_name,
            'module_name_slug'  => Str::slug($request->module_name),
            'module_title'      => $request->module_title,
            'description'       => $request->description,
            'module_image'      => $imagePath,
            'header_image'      => $headerImagePath,
            'module_video'      => $moduleVideo,
            'label'             => $request->label,
            'duree'             => $request->duree,
            'estimated_question_seconds' => (int) ($request->input('estimated_question_seconds', 30) ?: 30),
            'resources'         => $request->resources,
            'certificat'        => $request->certificat,
            'prerequi'          => $request->prerequi,
            'bestseller'        => $request->has('bestseller') ? 1 : 0,
            'vedette'           => $request->has('vedette') ? 1 : 0,
            'surevalue'         => $request->has('surevalue') ? 1 : 0,
            'status'            => $request->has('status') ? 1 : 0,
            'evaluation_id'     => $request->evaluation_id,
        ]);

        return redirect()->route('admin.modules')->with('success', 'Module mis à jour avec succès !');
    }

    /**
     * 6) Suppression d’un module (admin)
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
        $this->deleteManagedModuleVideo($module->module_video, $module->id);

        $module->delete();

        return redirect()->route('admin.modules')->with('success', 'Module supprimé avec succès !');
    }

    /**
     * 7) Vue d’ajout de lecture (admin)
     */
    public function AddModuleLecture($id)
    {
        $module  = Module::findOrFail($id);
        $section = ModuleSection::where('module_id', $id)->latest()->get();

        return view('admin.backend.modules.section.add_module_lecture', compact('module', 'section'));
    }

    public function MoveLectureUp($id)
    {
        $lecture = ModuleLecture::findOrFail($id);

        $prev = ModuleLecture::where('section_id', $lecture->section_id)
            ->where('position', '<', $lecture->position)
            ->orderByDesc('position')
            ->first();

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
            ->orderBy('position')
            ->first();

        if ($next) {
            [$lecture->position, $next->position] = [$next->position, $lecture->position];
            $lecture->save();
            $next->save();
        }

        return back();
    }

    /**
     * 8) Ajout section (admin)
     */
    public function AddModuleSection(Request $request)
    {
        $cid = $request->module_id;

        ModuleSection::insert([
            'module_id'      => $cid,
            'section_title'  => $request->section_title,
        ]);

        return redirect()->back()->with([
            'message'    => 'Section ajoutée',
            'alert-type' => 'success',
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
            'video_url'     => ['nullable', 'string', 'max:255'],
            'video_file'    => ['nullable', 'file', 'mimes:mp4,m4v,mov,avi,webm', 'max:307200'],
            'stay'          => ['nullable', 'boolean'],
        ]);

        $section = ModuleSection::findOrFail($id);

        // Sécurisation HTML (liste blanche minimale)
        $allowedTags = '<p><br><strong><em><u><ul><ol><li>';

        $sectionHtml = strip_tags((string) $request->input('section_html', ''), $allowedTags);

        $normalize = function (string $html): ?string {
            $html = trim($html);
            if ($html === '') return null;

            $plain = trim(strip_tags($html));
            return $plain === '' ? null : $html;
        };

        $videoUrl = trim((string) $request->input('video_url', ''));
        $videoUrl = $videoUrl === '' ? null : $videoUrl;

        if ($request->hasFile('video_file')) {
            $videoUrl = $this->storeSectionVideo($section, $request->file('video_file'));
        }

        $section->update([
            'section_title' => $request->input('section_title'),
            'section_html'  => $normalize($sectionHtml),
            'video_url'     => $videoUrl,
        ]);

        if ($request->boolean('stay')) {
            return redirect()
                ->route('admin.sections.edit', $section->id)
                ->with('success', 'Section sauvegardée.');
        }

        return redirect()
            ->route('admin.modules.lecture.add', $section->module_id)
            ->with('success', 'Section mise à jour avec succès !');
    }

    private function storeSectionVideo(ModuleSection $section, UploadedFile $video): string
    {
        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $relativeFolder = 'sections/section_' . $section->id;
        $storageFolder = $videosBase . '/' . $relativeFolder;
        $disk = Storage::disk('public');

        if (!$disk->exists($storageFolder)) {
            $disk->makeDirectory($storageFolder);
        }

        $oldVideo = trim((string) $section->video_url);
        $oldCandidates = [];
        if ($oldVideo !== '') {
            $normalizedOld = ltrim($oldVideo, '/');

            if (Str::startsWith($oldVideo, $relativeFolder . '/')) {
                $oldCandidates[] = $videosBase . '/' . $oldVideo;
            }
            if (Str::startsWith($normalizedOld, 'storage/')) {
                $oldCandidates[] = Str::after($normalizedOld, 'storage/');
            }
            if (Str::startsWith($normalizedOld, $videosBase . '/')) {
                $oldCandidates[] = $normalizedOld;
            }
            if (Str::startsWith($normalizedOld, 'media/storage/')) {
                $oldCandidates[] = Str::after($normalizedOld, 'media/storage/');
            }
        }

        foreach (array_unique($oldCandidates) as $oldPath) {
            if ($disk->exists($oldPath)) {
                $disk->delete($oldPath);
            }
        }

        $baseName = Str::slug(pathinfo((string) $video->getClientOriginalName(), PATHINFO_FILENAME));
        if ($baseName === '') {
            $baseName = 'section-video';
        }

        $extension = strtolower((string) $video->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'mp4';
        }

        $fileName = now()->format('Ymd_His') . '_' . Str::random(6) . '_' . $baseName . '.' . $extension;
        $disk->putFileAs($storageFolder, $video, $fileName);

        return route('media.storage', ['path' => $storageFolder . '/' . $fileName], false);
    }

    private function storeModuleVideo(Module $module, UploadedFile $video): string
    {
        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $relativeFolder = 'modules/module_' . $module->id;
        $storageFolder = $videosBase . '/' . $relativeFolder;
        $disk = Storage::disk('public');

        if (!$disk->exists($storageFolder)) {
            $disk->makeDirectory($storageFolder);
        }

        $this->deleteManagedModuleVideo($module->module_video, $module->id);

        $baseName = Str::slug(pathinfo((string) $video->getClientOriginalName(), PATHINFO_FILENAME));
        if ($baseName === '') {
            $baseName = 'module-video';
        }

        $extension = strtolower((string) $video->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'mp4';
        }

        $fileName = now()->format('Ymd_His') . '_' . Str::random(6) . '_' . $baseName . '.' . $extension;
        $disk->putFileAs($storageFolder, $video, $fileName);

        return route('media.storage', ['path' => $storageFolder . '/' . $fileName], false);
    }

    private function deleteManagedModuleVideo(?string $videoPath, int $moduleId): void
    {
        $videoPath = trim((string) $videoPath);
        if ($videoPath === '') {
            return;
        }

        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $relativeFolder = 'modules/module_' . $moduleId;
        $normalized = ltrim($videoPath, '/');
        $disk = Storage::disk('public');
        $candidatePaths = [];

        if (Str::startsWith($videoPath, '/media/storage/')) {
            $candidatePaths[] = Str::after($videoPath, '/media/storage/');
        }

        if (Str::startsWith($videoPath, $relativeFolder . '/')) {
            $candidatePaths[] = $videosBase . '/' . $videoPath;
        }

        if (Str::startsWith($normalized, $videosBase . '/' . $relativeFolder . '/')) {
            $candidatePaths[] = $normalized;
        }

        foreach (array_unique($candidatePaths) as $candidatePath) {
            if ($disk->exists($candidatePath)) {
                $disk->delete($candidatePath);
            }
        }

        $folderPath = $videosBase . '/' . $relativeFolder;
        if ($disk->exists($folderPath) && count($disk->allFiles($folderPath)) === 0) {
            $disk->deleteDirectory($folderPath);
        }
    }

    /**
     * 10) Sauvegarde d’une lecture (admin)
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
            'module_id'                  => $request->module_id,
            'section_id'                 => $request->section_id,
            'lecture_title'              => $request->lecture_title,
            'position'                   => $lastPosition + 1,
            'content_type'               => 'scorm',
            'slides_status'              => 'none',
            'slides_path'                => null,
            'slides_source_path'         => null,
            'slides_error'               => null,
            'slides_converted_at'        => null,
            'slide_count'                => 0,
            'quiz_questions_per_attempt' => 0,
            'scorm_path'                 => null,
        ]);

        return response()->json(['success' => 'Leçon enregistrée avec succès.']);
    }

    /**
     * 11) Formulaire d’édition d’une lecture (admin)
     */
    public function EditLecture($id)
    {
        // 1) Leçon + compteurs + objectifs + compétences des objectifs
        $mlecture = ModuleLecture::query()
            ->withCount([
                'quizQuestions as quiz_questions_count',
            ])
            ->with([
                'objectives' => function ($q) {
                    $q->orderBy('position')->orderBy('id')
                    ->with(['competencies' => function ($qq) {
                        $qq->orderBy('pivot_position')->orderBy('label');
                    }]);
                },
            ])
            ->findOrFail($id);

        // 2) SCORM packages/versions
        $mlecture->load([
            'scormPackage.activeVersion',
            'scormPackage.versions' => fn ($q) => $q->orderByDesc('id'),
            'scormPackageVersion',
        ]);

        // 3) Liste des packages SCORM (sélection)
        $packages = ScormPackage::select('id', 'name', 'slug', 'active_version_id')
            ->orderBy('name')
            ->get();

        // 4) Liste des compétences actives (pour le select multiple par objectif)
        $competencies = \App\Models\Competency::query()
            ->where('is_active', 1)
            ->orderBy('label')
            ->get(['id', 'code', 'label']);

        return view('admin.backend.modules.lecture.edit_module_lecture', [
            'mlecture'           => $mlecture,
            'packages'           => $packages,
            'competencies'       => $competencies,
            'quizQuestionsCount' => $mlecture->quiz_questions_count,
        ]);
    }

    /**
     * Import d'un support Slides (PPT/PPTX/PDF) pour une leçon.
     * La conversion est traitée en arrière-plan.
     */
    public function importSlidesForLecture(Request $request)
    {
        $validated = $request->validate([
            'lecture_id' => ['required', 'exists:module_lectures,id'],
            'slides_file' => ['required', 'file', 'mimes:ppt,pptx,pdf', 'max:51200'],
        ]);

        $lecture = ModuleLecture::findOrFail((int) $validated['lecture_id']);
        $file = $request->file('slides_file');

        if (!empty($lecture->slides_source_path)) {
            Storage::disk('local')->delete($lecture->slides_source_path);
        }

        $storedPath = $file->storeAs(
            'slides/sources/lecture_' . $lecture->id,
            'lecture_' . $lecture->id . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'local'
        );

        $lecture->update([
            'content_type' => 'slides',
            'slides_status' => 'pending',
            'slides_error' => null,
            'slides_source_path' => $storedPath,
        ]);

        ConvertLectureSlides::dispatch($lecture->id, $storedPath)->afterResponse();

        return redirect()
            ->back()
            ->with('success', 'Import Slides lancé. Conversion en cours...');
    }

    /**
     * Relance une conversion Slides à partir du dernier fichier source enregistré.
     */
    public function retrySlidesForLecture(Request $request)
    {
        $validated = $request->validate([
            'lecture_id' => ['required', 'exists:module_lectures,id'],
        ]);

        $lecture = ModuleLecture::findOrFail((int) $validated['lecture_id']);
        $sourcePath = (string) ($lecture->slides_source_path ?? '');

        if ($sourcePath === '' || !Storage::disk('local')->exists($sourcePath)) {
            return redirect()
                ->back()
                ->with('error', 'Aucun fichier source disponible. Reimporte un PPT/PDF avant de relancer.');
        }

        $lecture->update([
            'content_type' => 'slides',
            'slides_status' => 'pending',
            'slides_error' => null,
        ]);

        ConvertLectureSlides::dispatch($lecture->id, $sourcePath)->afterResponse();

        return redirect()
            ->back()
            ->with('success', 'Relance de conversion envoyée. Traitement en cours...');
    }


 /**
     * 12) Mise à jour d’une lecture (admin)
     */
    /**
     * 12) Mise à jour d’une lecture (admin)
     */
    public function UpdateModuleLecture(Request $request)
    {
        $validated = $request->validate([
            'id'                         => 'required|exists:module_lectures,id',
            'lecture_title'              => 'required|string|max:255',
            // AJOUT : Validation de la durée (entier, positif ou nul)
            'duration'                   => 'nullable|integer|min:0',
            'content_type'               => 'required|in:scorm,slides',

            // Autoriser explicitement la persistance du chemin
            'scorm_path'                 => 'nullable|string|max:255',

            'scorm_package_id'           => 'nullable|exists:scorm_packages,id',
            'use_active_scorm_version'   => 'nullable|in:0,1',
            'scorm_package_version_id'   => 'nullable|exists:scorm_package_versions,id',

            'slide_count'                => 'nullable|integer|min:0',
            'quiz_enabled'               => 'nullable|in:0,1',
            'quiz_questions_per_attempt' => 'exclude_unless:quiz_enabled,1|required|integer|min:1',

            // --- OBJECTIFS DE LECON ---
            'objectives'                 => ['nullable', 'array'],
            'objectives.*.id'            => ['nullable', 'integer', 'exists:lecture_objectives,id'],
            'objectives.*.title'         => ['nullable', 'string', 'max:255'],
            'objectives.*.description'   => ['nullable', 'string'],
            'objectives.*.position'      => ['nullable', 'integer', 'min:1'],
            'objectives.*._delete'       => ['nullable', 'boolean'],
        ]);

        $lecture = ModuleLecture::findOrFail($validated['id']);

        // Cast des booléens
        $useActive   = ($request->input('use_active_scorm_version', '1') === '1');
        $quizEnabled = ($request->input('quiz_enabled', '0') === '1');

        // Validation logique SCORM si versioning activé
        if (!$useActive && !$request->filled('scorm_package_version_id')) {
            return back()
                ->withErrors(['scorm_package_version_id' => 'Sélectionne une version SCORM (ou active “version active”).'])
                ->withInput();
        }

        if ($request->filled('scorm_package_id') && $request->filled('scorm_package_version_id')) {
            $ver = ScormPackageVersion::find($request->input('scorm_package_version_id'));

            if (!$ver || (int) $ver->scorm_package_id !== (int) $request->input('scorm_package_id')) {
                return back()
                    ->withErrors(['scorm_package_version_id' => 'La version sélectionnée ne correspond pas au SCORM choisi.'])
                    ->withInput();
            }
        }

        // Transaction: cohérence leçon + objectifs
        DB::transaction(function () use ($lecture, $validated, $request, $useActive, $quizEnabled) {

            // Logique de persistance du chemin
            $finalScormPath = $validated['scorm_path'] ?? $lecture->scorm_path;

            $lecture->update([
                'lecture_title'              => $validated['lecture_title'],
                // AJOUT : Sauvegarde de la durée
                'duration'                   => $request->input('duration'),
                'content_type'               => $validated['content_type'],
                
                'scorm_path'                 => $finalScormPath,

                'scorm_package_id'           => $request->input('scorm_package_id'),
                'use_active_scorm_version'   => $useActive,
                'scorm_package_version_id'   => $useActive ? null : $request->input('scorm_package_version_id'),

                'slide_count'                => $request->filled('slide_count')
                    ? (int) $request->input('slide_count')
                    : (int) ($lecture->slide_count ?? 0),
                'quiz_enabled'               => $quizEnabled,
                'quiz_questions_per_attempt' => $quizEnabled ? (int) $request->input('quiz_questions_per_attempt') : 0,
            ]);

            // Synchronisation des objectifs
            $this->syncLectureObjectives($lecture, $request->input('objectives', []));
        });

        $action = $request->input('save_action', 'back');

        if ($action === 'stay') {
            return redirect()->back()->with('success', 'La lecture a été mise à jour avec succès.');
        }

        return redirect()
            ->route('admin.modules.lecture.add', ['id' => $lecture->module_id])
            ->with('success', 'La lecture a été mise à jour avec succès.');
    }





    /**
     * 13) Suppression d’une lecture (admin)
     */
    public function DeleteLecture($id)
    {
        $lecture = ModuleLecture::find($id);

        if ($lecture) {
            DB::transaction(function () use ($lecture): void {
                $this->deleteLectureAndDependencies($lecture);
            });
        }

        $this->cleanupOrphanQuizQuestions();

        return redirect()->back()->with([
            'message'    => 'Lecture supprimée',
            'alert-type' => 'success',
        ]);
    }

    public function DeleteSection($id)
    {
        $section = ModuleSection::find($id);

        if ($section) {
            DB::transaction(function () use ($section): void {
                $lectureIds = ModuleLecture::query()
                    ->where('section_id', $section->id)
                    ->pluck('id');

                if ($lectureIds->isNotEmpty()) {
                    ModuleLecture::query()
                        ->whereIn('id', $lectureIds)
                        ->get()
                        ->each(function (ModuleLecture $lecture): void {
                            $this->deleteLectureAndDependencies($lecture);
                        });
                }

                $section->delete();
            });
        }

        $this->cleanupOrphanQuizQuestions();

        return redirect()->back()->with([
            'message'    => 'Section supprimée',
            'alert-type' => 'success',
        ]);
    }

    private function deleteLectureAndDependencies(ModuleLecture $lecture): void
    {
        if (!empty($lecture->slides_path)) {
            Storage::disk('public')->deleteDirectory($lecture->slides_path);
        }
        if (!empty($lecture->slides_source_path)) {
            Storage::disk('local')->delete($lecture->slides_source_path);
        }

        // Nettoyage explicite (questions + médias + objectifs) avant suppression de la leçon.
        $this->deleteQuestionsForLecture((int) $lecture->id);
        LectureObjective::query()->where('lecture_id', $lecture->id)->delete();
        $lecture->delete();
    }

    private function deleteQuestionsForLecture(int $lectureId): void
    {
        QuizQuestion::query()
            ->where('lecture_id', $lectureId)
            ->orderBy('id')
            ->chunkById(200, function ($questions): void {
                foreach ($questions as $question) {
                    if (!empty($question->image_path)) {
                        Storage::disk('public')->delete($question->image_path);
                    }
                    if (!empty($question->audio_path)) {
                        Storage::disk('public')->delete($question->audio_path);
                    }
                    $question->delete();
                }
            });
    }

    private function cleanupOrphanQuizQuestions(): int
    {
        $deleted = 0;

        QuizQuestion::query()
            ->whereDoesntHave('lecture')
            ->orderBy('id')
            ->chunkById(200, function ($questions) use (&$deleted): void {
                foreach ($questions as $question) {
                    if (!empty($question->image_path)) {
                        Storage::disk('public')->delete($question->image_path);
                    }
                    if (!empty($question->audio_path)) {
                        Storage::disk('public')->delete($question->audio_path);
                    }
                    $question->delete();
                    $deleted++;
                }
            });

        return $deleted;
    }

    /**
     * 9) Vue Section (stagiaire ou formateur)
     */
    /**
     * 9) Vue Section (stagiaire ou formateur)
     */
    public function section(Request $request, Module $module, ModuleSection $section)
    {
        $user = auth()->user();
        $isFormateurRoute = $request->routeIs('formateur.*');

        abort_unless((int) $section->module_id === (int) $module->id, 404);
        abort_unless($module->isVisibleTo($user), 404);

        $module->load([
            'sections' => function ($q) {
                $q->orderBy('id')
                ->with(['lectures' => function ($qq) {
                    $qq->orderBy('position')
                        ->orderBy('id')
                        ->with(['objectives' => function ($oq) {
                            $oq->orderBy('position')->orderBy('id');
                        }]);
                }]);
            },
        ]);

        $mode          = (string) $request->query('mode', 'groupe'); // 'groupe' | 'officiel'
        $isStaff       = in_array($user->role ?? null, ['formateur', 'admin', 'observateur'], true);
        $includeHidden = $request->boolean('include_hidden') && $isStaff;

        // ✅ Mode anonyme (lecture seule, pas de progression/statuts)
        $anonymous = $request->boolean('anonymous') || ($user->role ?? null) === 'observateur';

        $groupId = $this->resolveGroupIdForContext($request, $user, (int) $module->id);

        if ($mode !== 'officiel') {
            $this->applyGroupLessonOverrides($module, $groupId, !$includeHidden);
        }

        $section = $module->sections->firstWhere('id', (int) $section->id);
        abort_unless($section, 404);

        $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();

        $moduleResources = $module->moduleResources()
            ->when(! $isFormateurRoute, fn ($query) => $query->where('is_visible_to_stagiaire', true))
            ->get();

        $whiteboardGroups = collect();
        $currentWhiteboardGroup = null;
        $toolGroups = collect();
        $wordClouds = collect();

        // ✅ En anonyme : pas de calcul de progression/statuts
        $lectureStats = $anonymous ? [] : $this->buildLectureStats($lectures, (int) $user->id);

        $sectionStatuses = $anonymous
            ? collect()
            : $module->sections->mapWithKeys(function ($sec) use ($lectureStats) {
                $total = $sec->lectures->count();
                if ($total === 0) return [$sec->id => 'not_started'];

                $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                    $st = $lectureStats[$lec->id]['status'] ?? null;
                    return $st === 'completed';
                })->count();

                return [$sec->id => ($ok === $total ? 'completed' : ($ok > 0 ? 'in_progress' : 'not_started'))];
            });

        // ✅ Propagation du contexte + du mode anonyme
        $contextQuery = array_filter([
            'mode'           => $mode,
            'group_id'       => ($mode !== 'officiel' ? ($groupId ?: null) : null),
            'include_hidden' => ($includeHidden ? 1 : null),
            'anonymous'      => ($anonymous ? 1 : null),
        ]);

        if ($isFormateurRoute && ($user->role ?? null) === 'formateur') {
            $whiteboardGroups = $module->groups()
                ->accessibleByTrainer((int) $user->id)
                ->with('whiteboard')
                ->orderBy('groups.name')
                ->get(['groups.id', 'groups.name', 'groups.description'])
                ->map(function ($group) use ($groupId) {
                    return [
                        'id' => (int) $group->id,
                        'name' => (string) $group->name,
                        'description' => (string) ($group->description ?? ''),
                        'is_current' => (int) $group->id === (int) $groupId,
                        'has_whiteboard' => ! is_null($group->whiteboard),
                        'whiteboard_url' => route('formateur.groupes.whiteboard.show', ['group' => $group->id]),
                    ];
                })
                ->values();

            $currentWhiteboardGroup = $whiteboardGroups->firstWhere('is_current', true);

            $toolGroups = Group::query()
                ->accessibleByTrainer((int) $user->id)
                ->with([
                    'modules' => function ($query): void {
                        $query->orderBy('group_module.position')
                            ->with([
                                'sections' => function ($sectionQuery): void {
                                    $sectionQuery->orderBy('id')
                                        ->with([
                                            'lectures' => function ($lectureQuery): void {
                                                $lectureQuery
                                                    ->orderBy('position')
                                                    ->orderBy('id')
                                                    ->select('id', 'module_id', 'section_id', 'lecture_title', 'position');
                                            },
                                        ])
                                        ->select('id', 'module_id', 'section_title');
                                },
                            ])
                            ->select('modules.id', 'modules.module_name', 'modules.module_title');
                    },
                ])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function (Group $group) {
                    $modules = $group->modules->map(function (Module $groupModule) use ($group) {
                        $lectures = $groupModule->sections
                            ->flatMap(function (ModuleSection $moduleSection) {
                                return $moduleSection->lectures->map(function (ModuleLecture $moduleLecture) use ($moduleSection) {
                                    return [
                                        'id' => (int) $moduleLecture->id,
                                        'section_id' => (int) $moduleLecture->section_id,
                                        'title' => (string) $moduleLecture->lecture_title,
                                        'label' => trim((string) $moduleSection->section_title . ' · ' . (string) $moduleLecture->lecture_title),
                                    ];
                                });
                            })
                            ->values();

                        return [
                            'id' => (int) $groupModule->id,
                            'title' => (string) ($groupModule->module_title ?: $groupModule->module_name ?: 'Module'),
                            'manage_url' => route('formateur.groupes.modules.lecons.edit', [
                                'group' => $group->id,
                                'module' => $groupModule->id,
                            ]),
                            'lectures' => $lectures,
                        ];
                    })->values();

                    return [
                        'id' => (int) $group->id,
                        'name' => (string) $group->name,
                        'whiteboard_url' => route('formateur.groupes.whiteboard.show', ['group' => $group->id]),
                        'modules' => $modules,
                    ];
                })
                ->values();

            $toolModuleIds = $toolGroups
                ->pluck('modules')
                ->flatten(1)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $wordClouds = WordCloud::query()
                ->when(
                    $toolModuleIds->isNotEmpty(),
                    fn ($query) => $query->whereIn('module_id', $toolModuleIds->all()),
                    fn ($query) => $query->where('module_id', $module->id)
                )
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->get()
                ->map(function (WordCloud $wordCloud) {
                    return [
                        'id' => (int) $wordCloud->id,
                        'title' => (string) $wordCloud->title,
                        'question' => (string) $wordCloud->question,
                        'module_id' => (int) ($wordCloud->module_id ?? 0),
                        'group_id' => (int) ($wordCloud->group_id ?? 0),
                        'access_code' => (string) $wordCloud->access_code,
                        'is_active' => (bool) $wordCloud->is_active,
                        'live_url' => route('formateur.wordclouds.live', ['wordCloud' => $wordCloud->id]),
                        'join_url' => route('wordcloud.join.code', ['code' => $wordCloud->access_code]),
                        'updated_at_human' => $wordCloud->updated_at?->diffForHumans(),
                    ];
                })
                ->values();
        }

        // ✅ Si formateur + anonymous, on rend une vue "stagiaire" adaptée
        $view = match (true) {
            $anonymous && ($user->role ?? null) === 'formateur' => 'formateur.formations.anonyme.chapitre',
            $anonymous && ($user->role ?? null) === 'observateur' => 'observateur.formations.anonyme.chapitre',
            default => $this->viewBase() . '.chapitre',
        };

        return view($view, [
            'module'          => $module,
            'selectedSection' => $section,
            'section'         => $section,
            'selectedLecture' => null,
            'lectureStats'    => $lectureStats,
            'sectionStatuses' => $sectionStatuses,
            'formateur'       => $module->formateur ?? null,
            'contextQuery'    => $contextQuery,
            'mode'            => $mode,
            'groupId'         => $groupId,
            'includeHidden'   => $includeHidden,
            'anonymous'       => $anonymous,
            'lessonResources' => $moduleResources,
            'moduleResources' => $moduleResources,
            'whiteboardGroups' => $whiteboardGroups,
            'currentWhiteboardGroup' => $currentWhiteboardGroup,
            'toolGroups' => $toolGroups,
            'wordClouds' => $wordClouds,
        ]);

    }


/**
 * 15) Vue Lecture (stagiaire ou formateur)
 * Modifiée pour charger les données d'inspection (Quiz & Réponses) pour le formateur.
 */
public function lire(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture)
{
    $user = auth()->user();
    $isFormateurRoute = $request->routeIs('formateur.*');
    $isObserverRoute = $request->routeIs('observateur.*');

    // Vérifications de sécurité et de contexte
    abort_unless((int) $section->module_id === (int) $module->id, 404);
    abort_unless((int) $lecture->module_id === (int) $module->id, 404);
    abort_unless((int) $lecture->section_id === (int) $section->id, 404);
    abort_unless($module->isVisibleTo($user), 404);

    $lecture->load(['scormPackage.activeVersion', 'scormPackageVersion']);

    $module->load([
        'sections' => function ($q) {
            $q->orderBy('id')
            ->with(['lectures' => function ($qq) {
                $qq->orderBy('position')->orderBy('id');
            }]);
        },
    ]);

    $mode          = (string) $request->query('mode', 'groupe');
    $isStaff       = in_array($user->role ?? null, ['formateur', 'admin', 'observateur'], true);
    $includeHidden = $request->boolean('include_hidden') && $isStaff;
    $anonymous     = $request->boolean('anonymous') || ($user->role ?? null) === 'observateur';

    $groupId = $this->resolveGroupIdForContext($request, $user, (int) $module->id);

    if ($mode !== 'officiel') {
        $this->applyGroupLessonOverrides($module, $groupId, !$includeHidden);
    }

    $section = $module->sections->firstWhere('id', (int) $section->id);
    abort_unless($section, 404);

    $visibleIds = $module->sections->flatMap(fn ($s) => $s->lectures)->pluck('id')->map(fn ($id) => (int) $id)->all();

    if (!in_array((int) $lecture->id, $visibleIds, true)) {
        if ($mode !== 'officiel' && !($isStaff && $includeHidden)) {
            abort(404);
        }
    }

    $lectures = $module->sections->flatMap(fn ($s) => $s->lectures)->values();
    $lectureStats = $anonymous ? [] : $this->buildLectureStats($lectures, (int) $user->id);

    // --- LOGIQUE DE NAVIGATION PÉDAGOGIQUE ---
    $lectureRouteName = $isFormateurRoute
        ? 'formateur.formations.lecture'
        : ($isObserverRoute ? 'observateur.formations.lecture' : 'stagiaire.module.lecture');
    $sectionRouteName = $isFormateurRoute
        ? 'formateur.formations.section'
        : ($isObserverRoute ? 'observateur.formations.section' : 'stagiaire.module.section');
    $finalRouteName = $isFormateurRoute
        ? 'formateur.formations.detail'
        : ($isObserverRoute ? 'observateur.groupes.index' : 'stagiaire.module.fin');

    $nextLecturePayload = null;
    $currentSectionLectures = $section->lectures ?? collect();
    $idx = $currentSectionLectures->search(fn ($l) => (int) $l->id === (int) $lecture->id);

    // 1. Vérifier s'il reste une leçon dans le chapitre actuel
    if ($idx !== false && isset($currentSectionLectures[$idx + 1])) {
        $nextLec = $currentSectionLectures[$idx + 1];
        $nextLecturePayload = [
            'type'       => 'lecture',
            'id'         => (int) $nextLec->id,
            'section_id' => (int) $nextLec->section_id,
            'url'        => route($lectureRouteName, [
                'module'  => $module->id,
                'section' => (int) $nextLec->section_id,
                'lecture' => (int) $nextLec->id,
            ]),
        ];
    } else {
        // 2. Sinon, chercher le chapitre (section) suivant
        $sections = $module->sections->sortBy('id')->values();
        $currentSectionIndex = $sections->search(fn ($s) => (int) $s->id === (int) $section->id);

        $nextSection = null;
        if ($currentSectionIndex !== false) {
            $nextSection = $sections
                ->slice($currentSectionIndex + 1)
                ->first(fn ($candidate) => $candidate->lectures->isNotEmpty());
        }

        if ($nextSection) {
            $nextLecturePayload = [
                'type'       => 'section',
                'id'         => (int) $nextSection->id,
                'url'        => route($sectionRouteName, [
                    'module'  => $module->id,
                    'section' => (int) $nextSection->id,
                ]),
            ];
        } else {
            // 3. Fin du module complet
            $nextLecturePayload = [
                'type' => 'fin',
                'url'  => route($finalRouteName, ['module' => $module->id]),
            ];
        }
    }

    // Calcul des statuts pour la sidebar
    $sectionStatuses = $anonymous
        ? collect()
        : $module->sections->mapWithKeys(function ($sec) use ($lectureStats) {
            $total = $sec->lectures->count();
            if ($total === 0) return [$sec->id => 'not_started'];
            $ok = $sec->lectures->filter(function ($lec) use ($lectureStats) {
                return ($lectureStats[$lec->id]['status'] ?? null) === 'completed';
            })->count();
            return [$sec->id => ($ok === $total ? 'completed' : ($ok > 0 ? 'in_progress' : 'not_started'))];
        });

    $contextQuery = array_filter([
        'mode'           => $mode,
        'group_id'       => ($mode !== 'officiel' ? ($groupId ?: null) : null),
        'include_hidden' => ($includeHidden ? 1 : null),
        'anonymous'      => $anonymous,
    ]);

    // --- 🛠️ NOUVEAU : DONNÉES D'INSPECTION (FORMATEUR) ---
    $quizData = null;
    $moduleResources = collect();
    $whiteboardGroups = collect();
    $currentWhiteboardGroup = null;
    $toolGroups = collect();
    $wordClouds = collect();
    
    // Si staff, on charge les questions et les réponses pour l'affichage "Inspecteur"
    if ($isStaff && $lecture->quiz_enabled) {
        // Note: Assure-toi que la relation 'quizQuestions' et 'answers' existent dans tes modèles
        $quizData = $lecture->quizQuestions()
            ->with('answers') 
            ->orderBy('id')
            ->get();
    }

    $moduleResources = $module->moduleResources()
        ->when(! $isFormateurRoute, fn ($query) => $query->where('is_visible_to_stagiaire', true))
        ->get();

    if ($isFormateurRoute && ($user->role ?? null) === 'formateur') {
        $whiteboardGroups = $module->groups()
            ->accessibleByTrainer((int) $user->id)
            ->with('whiteboard')
            ->orderBy('groups.name')
            ->get(['groups.id', 'groups.name', 'groups.description'])
            ->map(function ($group) use ($groupId, $module, $section, $lecture) {
                return [
                    'id' => (int) $group->id,
                    'name' => (string) $group->name,
                    'description' => (string) ($group->description ?? ''),
                    'is_current' => (int) $group->id === (int) $groupId,
                    'has_whiteboard' => ! is_null($group->whiteboard),
                    'whiteboard_url' => route('formateur.groupes.whiteboard.show', ['group' => $group->id]),
                    'lesson_url' => route('formateur.formations.lecture', [
                        'module' => $module->id,
                        'section' => $section->id,
                        'lecture' => $lecture->id,
                        'mode' => 'groupe',
                        'group_id' => $group->id,
                    ]),
                ];
            })
            ->values();

        $currentWhiteboardGroup = $whiteboardGroups->firstWhere('is_current', true);

        $toolGroups = Group::query()
            ->accessibleByTrainer((int) $user->id)
            ->with([
                'modules' => function ($query): void {
                    $query->orderBy('group_module.position')
                        ->with([
                            'sections' => function ($sectionQuery): void {
                                $sectionQuery->orderBy('id')
                                    ->with([
                                        'lectures' => function ($lectureQuery): void {
                                            $lectureQuery
                                                ->orderBy('position')
                                                ->orderBy('id')
                                                ->select('id', 'module_id', 'section_id', 'lecture_title', 'position');
                                        },
                                    ])
                                    ->select('id', 'module_id', 'section_title');
                            },
                        ])
                        ->select('modules.id', 'modules.module_name', 'modules.module_title');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Group $group) {
                $modules = $group->modules->map(function (Module $groupModule) use ($group) {
                    $lectures = $groupModule->sections
                        ->flatMap(function (ModuleSection $moduleSection) {
                            return $moduleSection->lectures->map(function (ModuleLecture $moduleLecture) use ($moduleSection) {
                                return [
                                    'id' => (int) $moduleLecture->id,
                                    'section_id' => (int) $moduleLecture->section_id,
                                    'title' => (string) $moduleLecture->lecture_title,
                                    'label' => trim((string) $moduleSection->section_title . ' · ' . (string) $moduleLecture->lecture_title),
                                ];
                            });
                        })
                        ->values();

                    return [
                        'id' => (int) $groupModule->id,
                        'title' => (string) ($groupModule->module_title ?: $groupModule->module_name ?: 'Module'),
                        'manage_url' => route('formateur.groupes.modules.lecons.edit', [
                            'group' => $group->id,
                            'module' => $groupModule->id,
                        ]),
                        'lectures' => $lectures,
                    ];
                })->values();

                return [
                    'id' => (int) $group->id,
                    'name' => (string) $group->name,
                    'whiteboard_url' => route('formateur.groupes.whiteboard.show', ['group' => $group->id]),
                    'modules' => $modules,
                ];
            })
            ->values();

        $toolModuleIds = $toolGroups
            ->pluck('modules')
            ->flatten(1)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $wordClouds = WordCloud::query()
            ->when(
                $toolModuleIds->isNotEmpty(),
                fn ($query) => $query->whereIn('module_id', $toolModuleIds->all()),
                fn ($query) => $query->where('module_id', $module->id)
            )
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (WordCloud $wordCloud) {
                return [
                    'id' => (int) $wordCloud->id,
                    'title' => (string) $wordCloud->title,
                    'question' => (string) $wordCloud->question,
                    'module_id' => (int) ($wordCloud->module_id ?? 0),
                    'group_id' => (int) ($wordCloud->group_id ?? 0),
                    'access_code' => (string) $wordCloud->access_code,
                    'is_active' => (bool) $wordCloud->is_active,
                    'live_url' => route('formateur.wordclouds.live', ['wordCloud' => $wordCloud->id]),
                    'join_url' => route('wordcloud.join.code', ['code' => $wordCloud->access_code]),
                    'updated_at_human' => $wordCloud->updated_at?->diffForHumans(),
                ];
            })
            ->values();
    }
    // -----------------------------------------------------

    $view = match (true) {
        $anonymous && ($user->role ?? null) === 'formateur' => 'formateur.formations.anonyme.lecon',
        $anonymous && ($user->role ?? null) === 'observateur' => 'observateur.formations.anonyme.lecon',
        default => $this->viewBase() . '.lecon',
    };
    
    return view($view, [
        'module'          => $module,
        'section'         => $section,
        'selectedSection' => $section,
        'lecture'         => $lecture,
        'selectedLecture' => $lecture,
        'lectureStats'    => $lectureStats,
        'sectionStatuses' => $sectionStatuses,
        'nextLecture'     => $nextLecturePayload,
        'formateur'       => $module->formateur ?? null,
        'contextQuery'    => $contextQuery,
        'mode'            => $mode,
        'groupId'         => $groupId,
        'includeHidden'   => $includeHidden,
        'anonymous'       => $anonymous,
        
        // Nouvelle variable passée à la vue
        'quizData'        => $quizData,
        'lessonResources' => $moduleResources,
        'moduleResources' => $moduleResources,
        'whiteboardGroups' => $whiteboardGroups,
        'currentWhiteboardGroup' => $currentWhiteboardGroup,
        'toolGroups' => $toolGroups,
        'wordClouds' => $wordClouds,
    ]);
}



        /**
         * 14) Détail public d’un module (catalogue)
         */
        public function show($id)
        {
            $module = Module::with('sections.lectures.objectives')->findOrFail($id);

            if (!$module->isVisibleTo(auth()->user())) {
                abort(404);
            }

            $lessonObjectives = $module->sections
                ->flatMap->lectures
                ->flatMap(function ($lecture) {
                    return $lecture->objectives->pluck('title');
                })
                ->map(fn ($title) => trim((string) $title))
                ->filter()
                ->unique()
                ->values();

            return view('frontend.contenu.module_detail', compact('module', 'lessonObjectives'));
        }

        private function buildLectureStats($lectures, int $userId): array
        {
            $lectureIds = $lectures->pluck('id')->all();

            // A) Quiz : dernière tentative par leçon
            $attempts = QuizAttempt::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->orderByDesc('finished_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy('lecture_id')
                ->map(fn ($rows) => $rows->first());

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

            // B) SCORM : démarré / terminé
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
                            'status'            => 'not_started',
                            'quiz'              => true,
                            'questions_total'   => $planned,
                            'questions_answered'=> 0,
                            'questions_correct' => 0,
                            'quiz_score'        => null,
                            'quiz_finished'     => false,
                            'slides'            => (int) ($lec->slide_count ?? 0),
                            'session_time'      => null,
                        ];
                        continue;
                    }

                    $agg      = $attemptAgg->get($attempt->id);
                    $total    = (int) ($agg->total ?? $attempt->total_questions ?? $planned ?? 0);
                    $answered = (int) ($agg->answered ?? 0);
                    $correct  = (int) ($agg->correct ?? 0);
                    $score    = !is_null($attempt->percent)
                        ? (int) $attempt->percent
                        : (($total > 0) ? (int) round(($correct / $total) * 100) : null);
                    $finished = !is_null($attempt->finished_at);

                    if (!$finished) {
                        $status = $answered > 0 ? 'in_progress' : 'not_started';
                    } else {
                        $status = ($score !== null && $score >= 50) ? 'completed' : 'failed';
                    }

                    $stats[$lec->id] = [
                        'status'            => $status,
                        'quiz'              => true,
                        'questions_total'   => $total,
                        'questions_answered'=> $answered,
                        'questions_correct' => $correct,
                        'quiz_score'        => $score,
                        'quiz_finished'     => $finished,
                        'slides'            => (int) ($lec->slide_count ?? 0),
                        'session_time'      => null,
                    ];
                    continue;
                }

                // 2) Sinon : sidebar dépend du SCORM
                $hasStarted  = (int) ($started[$lec->id] ?? 0) > 0;
                $sc          = $scores->get($lec->id);
                $lessonStatus= strtolower((string) ($sc->lesson_status ?? ''));

                $isCompleted = in_array($lessonStatus, ['completed', 'passed'], true) || (bool) ($sc->is_completed ?? false);

                if (!$hasStarted) {
                    $status = 'not_started';
                } elseif ($isCompleted) {
                    $status = 'completed';
                } else {
                    $status = 'in_progress';
                }

                $stats[$lec->id] = [
                    'status'            => $status,
                    'quiz'              => false,
                    'questions_total'   => 0,
                    'questions_answered'=> 0,
                    'questions_correct' => 0,
                    'quiz_score'        => null,
                    'quiz_finished'     => false,
                    'slides'            => (int) ($lec->slide_count ?? 0),
                    'session_time'      => $sc->session_time ?? null,
                ];
            }

            return $stats;
        }

    /**
     * 16) Page de fin de module (stagiaire)
     */
    public function finModule($moduleId)
    {
        $userId = (int) auth()->id();

        $module = Module::with('sections.lectures')->findOrFail($moduleId);
        if (!$module->isVisibleTo(auth()->user())) abort(404);

        $sections = $module->sections;
        $lectures = $sections->flatMap->lectures->values();
        $lectureIds = $lectures->pluck('id')->map(fn ($id) => (int) $id)->all();

        $totalSections = $sections->count();
        $totalLectures = $lectures->count();

        $totalQuestionsPlanned = 0;
        $questionsAnswered = 0;
        $totalCorrectAnswers = 0;

        $latestAttempts = collect();
        $quizAttemptAgg = collect();
        $scormAgg = collect();

        $quizTimeSeconds = 0;
        $scormTimeSeconds = 0;
        $videoTimeSeconds = 0;

        $quizLatencyTotalSeconds = 0;
        $quizLatencySamples = 0;
        $scormLatencyTotalSeconds = 0;
        $scormLatencySamples = 0;

        $scormInteractionsCount = 0;
        $videoSegmentsCount = 0;
        $videoReplayCount = 0;

        if (!empty($lectureIds)) {
            $latestAttempts = QuizAttempt::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->orderByDesc('finished_at')
                ->orderByDesc('id')
                ->get()
                ->groupBy('lecture_id')
                ->map(fn ($rows) => $rows->first());

            $latestAttemptIds = $latestAttempts->pluck('id')->all();

            if (!empty($latestAttemptIds)) {
                $quizAttemptAgg = QuizAttemptQuestion::query()
                    ->select([
                        'attempt_id',
                        DB::raw('SUM(CASE WHEN answered_at IS NOT NULL THEN 1 ELSE 0 END) as answered'),
                        DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct'),
                        DB::raw('SUM(time_seconds) as time_spent'),
                    ])
                    ->whereIn('attempt_id', $latestAttemptIds)
                    ->groupBy('attempt_id')
                    ->get()
                    ->keyBy('attempt_id');

                $quizLatencyTotalSeconds = (int) $quizAttemptAgg->sum(fn ($agg) => (int) ($agg->time_spent ?? 0));
                $quizLatencySamples = (int) $quizAttemptAgg->sum(fn ($agg) => (int) ($agg->answered ?? 0));
            }

            $quizTimeSeconds = (int) $latestAttempts->sum(fn ($attempt) => (int) ($attempt->total_time_seconds ?? 0));

            $scormAgg = ScormInteraction::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->whereIn('result', ['correct', 'wrong'])
                ->select([
                    'lecture_id',
                    DB::raw('COUNT(*) as answered'),
                    DB::raw("SUM(CASE WHEN result = 'correct' THEN 1 ELSE 0 END) as correct"),
                ])
                ->groupBy('lecture_id')
                ->get()
                ->keyBy('lecture_id');

            $scormInteractionsCount = (int) ScormInteraction::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->count();

            $scormTimeSeconds = (int) ScormScore::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->sum('session_time');

            $scormLatencies = ScormInteraction::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->whereIn('result', ['correct', 'wrong'])
                ->whereNotNull('latency')
                ->pluck('latency');

            foreach ($scormLatencies as $latency) {
                try {
                    [$h, $m, $s] = array_pad(explode(':', (string) $latency), 3, 0);
                    $scormLatencyTotalSeconds += ((int) $h * 3600 + (int) $m * 60 + (int) $s);
                    $scormLatencySamples++;
                } catch (\Throwable $e) {
                    // Ignore les latences SCORM mal formatées.
                }
            }

            $videoRows = VideoSegmentTracking::query()
                ->where('user_id', $userId)
                ->whereIn('lecture_id', $lectureIds)
                ->get(['watch_count', 'total_watch_time']);

            $videoSegmentsCount = $videoRows->count();
            $videoTimeSeconds = (int) round((float) $videoRows->sum('total_watch_time'));
            $videoReplayCount = (int) $videoRows->sum(fn ($row) => max(0, ((int) $row->watch_count) - 1));
        }

        foreach ($lectures as $lecture) {
            $plannedScorm = (int) ($lecture->question_count ?? 0);
            $plannedQuiz = (bool) ($lecture->quiz_enabled ?? false)
                ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
                : 0;
            $planned = max($plannedScorm, $plannedQuiz);

            $scormAnswered = (int) ($scormAgg->get($lecture->id)?->answered ?? 0);
            $scormCorrect = (int) ($scormAgg->get($lecture->id)?->correct ?? 0);

            $quizAnswered = 0;
            $quizCorrect = 0;
            $attempt = $latestAttempts->get($lecture->id);

            if ($attempt) {
                $attemptAgg = $quizAttemptAgg->get($attempt->id);
                $quizAnswered = (int) ($attemptAgg?->answered ?? 0);
                $quizCorrect = (int) ($attemptAgg?->correct ?? 0);
            }

            $answered = max($scormAnswered, $quizAnswered);
            $correct = max($scormCorrect, $quizCorrect);

            if ($planned > 0) {
                $answered = min($answered, $planned);
                $correct = min($correct, $answered);
            } elseif ($answered > 0) {
                $planned = $answered;
            }

            $totalQuestionsPlanned += $planned;
            $questionsAnswered += $answered;
            $totalCorrectAnswers += $correct;
        }

        $lectureStats = !empty($lectureIds)
            ? $this->buildLectureStats($lectures, $userId)
            : [];

        $completedLectures = collect($lectureStats)
            ->filter(fn ($stats) => ($stats['status'] ?? null) === 'completed')
            ->count();

        $completedSections = $sections->filter(function ($section) use ($lectureStats) {
            if ($section->lectures->isEmpty()) {
                return false;
            }

            return $section->lectures->every(function ($lecture) use ($lectureStats) {
                return ($lectureStats[$lecture->id]['status'] ?? null) === 'completed';
            });
        })->count();

        $moduleCompletionPercent = $totalLectures > 0
            ? (int) round(($completedLectures / $totalLectures) * 100)
            : 100;

        $sectionCompletionPercent = $totalSections > 0
            ? (int) round(($completedSections / $totalSections) * 100)
            : 100;

        $successRatePercent = $questionsAnswered > 0
            ? (int) round(($totalCorrectAnswers / $questionsAnswered) * 100)
            : null;

        $latencySamples = $quizLatencySamples + $scormLatencySamples;
        $averageLatencySeconds = $latencySamples > 0
            ? (int) round(($quizLatencyTotalSeconds + $scormLatencyTotalSeconds) / $latencySamples)
            : null;

        $totalLearningSeconds = $scormTimeSeconds + $quizTimeSeconds + $videoTimeSeconds;
        $trackedInteractions = $scormInteractionsCount + $quizLatencySamples + $videoSegmentsCount;
        $completionToast = null;

        if ($moduleCompletionPercent >= 100 && auth()->user()?->role === 'stagiaire') {
            $notifyResult = app(ModuleCompletionNotifier::class)->notify($module, auth()->user());

            if (($notifyResult['created_for_stagiaire'] ?? false) === true) {
                $completionToast = 'Vous avez termine le module "' . $module->module_name . '".';
            }
        }

        return view('stagiaire.fin_module', [
            'module'                => $module,
            'totalSections'         => $totalSections,
            'totalLectures'         => $totalLectures,
            'totalQuestionsPlanned' => $totalQuestionsPlanned,
            'questionsAnswered'     => $questionsAnswered,
            'usabilityStats'        => [
                'completed_lectures'         => $completedLectures,
                'completed_sections'         => $completedSections,
                'module_completion_percent'  => $moduleCompletionPercent,
                'section_completion_percent' => $sectionCompletionPercent,
                'success_rate_percent'       => $successRatePercent,
                'total_learning_seconds'     => $totalLearningSeconds,
                'scorm_time_seconds'         => $scormTimeSeconds,
                'quiz_time_seconds'          => $quizTimeSeconds,
                'video_time_seconds'         => $videoTimeSeconds,
                'average_latency_seconds'    => $averageLatencySeconds,
                'tracked_interactions'       => $trackedInteractions,
                'scorm_interactions'         => $scormInteractionsCount,
                'quiz_answers'               => $quizLatencySamples,
                'video_segments'             => $videoSegmentsCount,
                'video_replays'              => $videoReplayCount,
            ],
            'completionToast' => $completionToast,
        ]);
    }

    private function resolveGroupIdForContext(Request $request, $user, int $moduleId): ?int
    {
        $forcedGroupId = (int) $request->query('group_id', 0);
        $isStaff = in_array($user->role ?? null, ['formateur', 'admin', 'observateur'], true);

        if ($forcedGroupId > 0 && $isStaff) {

            $hasModule = DB::table('group_module')
                ->where('group_id', $forcedGroupId)
                ->where('module_id', $moduleId)
                ->exists();

            if (!$hasModule) return null;

            if (($user->role ?? null) === 'formateur') {
                $isAccessible = Group::query()
                    ->accessibleByTrainer((int) $user->id)
                    ->where('id', $forcedGroupId)
                    ->exists();

                if (! $isAccessible) return null;
            }

            if (($user->role ?? null) === 'observateur') {
                $isObserved = DB::table('group_user')
                    ->where('group_id', $forcedGroupId)
                    ->where('user_id', (int) $user->id)
                    ->where('role_in_group', 'observateur')
                    ->exists();

                if (! $isObserved) return null;
            }

            return $forcedGroupId;
        }

        return $this->resolveGroupIdForUserAndModule((int) $user->id, $moduleId);
    }

    private function resolveGroupIdForUserAndModule(int $userId, int $moduleId): ?int
    {
        $formateurGroupId = Group::query()
            ->accessibleByTrainer($userId)
            ->whereHas('modules', fn ($query) => $query->where('modules.id', $moduleId))
            ->value('groups.id');

        if ($formateurGroupId) {
            return (int) $formateurGroupId;
        }

        $gid = DB::table('group_user')
            ->join('group_module', 'group_module.group_id', '=', 'group_user.group_id')
            ->where('group_user.user_id', $userId)
            ->where('group_module.module_id', $moduleId)
            ->value('group_user.group_id');

        return $gid ? (int) $gid : null;
    }

    private function applyGroupLessonOverrides(Module $module, ?int $groupId, bool $filterHidden = true): void
    {
        if (!$groupId) return;

        $over = \App\Models\GroupModuleLecture::query()
            ->where('group_id', $groupId)
            ->where('module_id', $module->id)
            ->get()
            ->keyBy('lecture_id');

        if ($over->isEmpty()) return;

        $module->sections->each(function ($sec) use ($over, $filterHidden) {

            $lectures = $sec->lectures;

            if ($filterHidden) {
                $lectures = $lectures->filter(function ($lec) use ($over) {
                    $row = $over->get($lec->id);
                    return $row ? (bool) $row->is_enabled : true;
                });
            }

            $lectures = $lectures->sortBy(function ($lec) use ($over) {
                $row = $over->get($lec->id);
                return $row ? (int) $row->position : (int) $lec->position;
            })->values();

            $sec->setRelation('lectures', $lectures);
        });
    }
    private function syncLectureObjectives(ModuleLecture $lecture, array $rows = []): void
{
    $rows = collect($rows)
        ->map(function ($r) {
            return [
                'id'          => isset($r['id']) ? (int) $r['id'] : null,
                'title'       => isset($r['title']) ? trim((string) $r['title']) : '',
                'description' => isset($r['description']) ? trim((string) $r['description']) : null,
                'position'    => isset($r['position']) && (int)$r['position'] > 0 ? (int) $r['position'] : null,
                '_delete'     => !empty($r['_delete']),
            ];
        })
        // On garde les lignes utiles : suppression OU titre non vide
        ->filter(fn ($r) => $r['_delete'] || $r['title'] !== '')
        ->values();

    // Sécurité : on ne modifie que les objectifs appartenant à cette leçon
    $existingIds = $lecture->objectives()->pluck('id')->all();

    $autoPos = 1;

    foreach ($rows as $row) {

        // Suppression
        if ($row['_delete'] === true) {
            if ($row['id'] && in_array($row['id'], $existingIds, true)) {
                $lecture->objectives()->whereKey($row['id'])->delete();
            }
            continue;
        }

        // Position automatique si absente
        $pos = $row['position'] ?? $autoPos;

        // Mise à jour
        if ($row['id'] && in_array($row['id'], $existingIds, true)) {
            $lecture->objectives()->whereKey($row['id'])->update([
                'title'       => $row['title'],
                'description' => $row['description'],
                'position'    => $pos,
            ]);
        } else {
            // Création
            $lecture->objectives()->create([
                'title'       => $row['title'],
                'description' => $row['description'],
                'position'    => $pos,
            ]);
        }

        $autoPos++;
    }

    // Normalisation finale des positions : 1..n sans trous
    $ordered = $lecture->objectives()->orderBy('position')->orderBy('id')->get();
    $i = 1;
    foreach ($ordered as $obj) {
        if ((int) $obj->position !== $i) {
            $obj->update(['position' => $i]);
        }
        $i++;
    }
}

}
