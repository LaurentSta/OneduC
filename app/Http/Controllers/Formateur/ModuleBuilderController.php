<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleBuilderController extends Controller
{
    public function index()
    {
        $formateurId = auth()->id();

        $modules = Module::query()
            ->authoredByTrainer($formateurId)
            ->withCount(['sections', 'groups'])
            ->orderByDesc('updated_at')
            ->get();

        return view('formateur.modules-builder.index', [
            'modules' => $modules,
        ]);
    }

    public function create()
    {
        return redirect()->route('formateur.modules.builder.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $category = $this->resolveTrainerCategory();
        $subcategory = $this->resolveTrainerSubcategory($category);

        $module = Module::create([
            'module_title' => $validated['module_title'],
            'module_name' => $validated['module_title'],
            'module_name_slug' => Str::slug($validated['module_title']),
            'description' => $validated['description'] ?? null,
            'formateur_id' => auth()->id(),
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'status' => 1,
            'is_trainer_authored' => true,
        ]);

        return redirect()
            ->route('formateur.modules.builder.edit', $module)
            ->with('success', 'Module créé. Ajoutez maintenant des chapitres et des leçons.');
    }

    public function duplicate(Module $catalogModule)
    {
        abort_unless($catalogModule->isVisibleTo(auth()->user()), 404);

        $newModule = DB::transaction(function () use ($catalogModule) {
            $newTitle = trim((string) ($catalogModule->module_title ?? $catalogModule->module_name)).' (copie)';

            $newModule = Module::create([
                'category_id' => $catalogModule->category_id,
                'subcategory_id' => $catalogModule->subcategory_id,
                'formateur_id' => auth()->id(),
                'module_title' => $newTitle,
                'module_name' => $newTitle,
                'module_name_slug' => Str::slug($newTitle).'-'.Str::random(6),
                'description' => $catalogModule->description,
                'objectifs' => $catalogModule->objectifs,
                'module_video' => $catalogModule->module_video,
                'label' => $catalogModule->label,
                'duree' => $catalogModule->duree,
                'resources' => $catalogModule->resources,
                'certificat' => $catalogModule->certificat,
                'prerequi' => $catalogModule->prerequi,
                'status' => 1,
                'is_trainer_authored' => true,
            ]);

            foreach ($catalogModule->sections()->orderBy('position')->get() as $index => $section) {
                $newSection = $newModule->sections()->create([
                    'section_title' => $section->section_title,
                    'section_html' => $section->section_html,
                    'objectif' => $section->objectif,
                    'methode' => $section->methode,
                    'contexte' => $section->contexte,
                    'scorm_video_path' => $section->scorm_video_path,
                    'video_url' => $section->video_url,
                    'position' => $index,
                ]);

                foreach ($section->lectures()->orderBy('position')->get() as $lecture) {
                    $newSection->lectures()->create([
                        'module_id' => $newModule->id,
                        'lecture_title' => $lecture->lecture_title,
                        'url' => $lecture->url,
                        'position' => $lecture->position,
                        'content_type' => $lecture->content_type,
                        'content_blocks' => $lecture->content_blocks,
                        'scorm_path' => $lecture->scorm_path,
                        'scorm_package_id' => $lecture->scorm_package_id,
                        'scorm_package_version_id' => $lecture->scorm_package_version_id,
                        'use_active_scorm_version' => $lecture->use_active_scorm_version,
                        'duration' => $lecture->duration,
                        'slides_status' => $lecture->slides_status,
                        'slides_path' => $lecture->slides_path,
                        'slides_source_path' => $lecture->slides_source_path,
                        'slide_count' => $lecture->slide_count,
                        // Quiz banks are keyed to the original lecture id, so the copy
                        // starts with quiz disabled rather than pointing at no questions.
                        'quiz_enabled' => false,
                    ]);
                }
            }

            $groupIds = $catalogModule->groups()->accessibleByTrainer(auth()->id())->pluck('groups.id');
            $newModule->groups()->sync($groupIds);

            return $newModule;
        });

        return redirect()
            ->route('formateur.modules.builder.edit', $newModule)
            ->with('success', 'Module dupliqué. Vous pouvez maintenant le personnaliser librement.');
    }

    public function edit(Module $module)
    {
        $this->assertOwnership($module);

        $module->load(['sections.lectures' => fn ($query) => $query->orderBy('position'), 'groups']);

        $accessibleGroups = Group::query()
            ->accessibleByTrainer(auth()->id())
            ->orderBy('name')
            ->get();

        return view('formateur.modules-builder.edit', [
            'module' => $module,
            'accessibleGroups' => $accessibleGroups,
        ]);
    }

    public function update(Request $request, Module $module)
    {
        $this->assertOwnership($module);

        $validated = $request->validate([
            'module_title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $module->update([
            'module_title' => $validated['module_title'],
            'module_name' => $validated['module_title'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Module mis à jour.');
    }

    public function destroy(Module $module)
    {
        $this->assertOwnership($module);

        $module->delete();

        return redirect()->route('formateur.modules.builder.index')->with('success', 'Module supprimé.');
    }

    public function storeSection(Request $request, Module $module)
    {
        $this->assertOwnership($module);

        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
        ]);

        $position = (int) $module->sections()->max('position') + 1;

        $section = $module->sections()->create([
            'section_title' => $validated['section_title'],
            'position' => $position,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'section' => $this->sectionPayload($section),
            ], 201);
        }

        return back()->with('success', 'Chapitre ajouté.');
    }

    public function updateSection(Request $request, ModuleSection $section)
    {
        $this->assertOwnership($section->module);

        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
        ]);

        $section->update(['section_title' => $validated['section_title']]);

        if ($request->wantsJson()) {
            return response()->json([
                'section' => $this->sectionPayload($section),
            ]);
        }

        return back()->with('success', 'Chapitre mis à jour.');
    }

    public function destroySection(Request $request, ModuleSection $section)
    {
        $this->assertOwnership($section->module);

        $section->lectures()->delete();
        $section->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Chapitre supprimé.');
    }

    public function reorderSections(Request $request, Module $module)
    {
        $this->assertOwnership($module);

        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'integer',
        ]);

        $existingIds = $module->sections()->pluck('id');
        $requestedIds = collect($validated['section_ids']);

        abort_unless(
            $requestedIds->count() === $existingIds->count() && $requestedIds->diff($existingIds)->isEmpty(),
            422
        );

        DB::transaction(function () use ($requestedIds) {
            foreach ($requestedIds->values() as $position => $sectionId) {
                ModuleSection::where('id', $sectionId)->update(['position' => $position]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function storeLecture(Request $request, ModuleSection $section)
    {
        $this->assertOwnership($section->module);

        $validated = $request->validate([
            'lecture_title' => 'required|string|max:255',
            'content_blocks' => 'nullable|string',
        ]);

        $position = (int) $section->lectures()->max('position') + 1;

        $lecture = $section->lectures()->create([
            'module_id' => $section->module_id,
            'lecture_title' => $validated['lecture_title'],
            'content_type' => 'blocks',
            'content_blocks' => $this->sanitizeBlocks($validated['content_blocks'] ?? null, (int) $section->module_id),
            'position' => $position,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'lecture' => $this->lecturePayload($lecture),
            ], 201);
        }

        return back()->with('success', 'Leçon ajoutée.');
    }

    public function updateLecture(Request $request, ModuleLecture $lecture)
    {
        $this->assertOwnership($lecture->module);

        $validated = $request->validate([
            'lecture_title' => 'required|string|max:255',
            'content_blocks' => 'nullable|string',
        ]);

        $updates = ['lecture_title' => $validated['lecture_title']];

        // Title-only renames (e.g. from the outline editor) must not touch existing
        // content — only overwrite content_blocks when the caller actually sent it.
        if ($request->has('content_blocks')) {
            $updates['content_blocks'] = $this->sanitizeBlocks($validated['content_blocks'], (int) $lecture->module_id);
        }

        $lecture->update($updates);

        if ($request->wantsJson()) {
            return response()->json([
                'lecture' => $this->lecturePayload($lecture),
                'saved_at' => now()->toIso8601String(),
            ]);
        }

        return back()->with('success', 'Leçon mise à jour.');
    }

    public function destroyLecture(Request $request, ModuleLecture $lecture)
    {
        $this->assertOwnership($lecture->module);

        $lecture->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Leçon supprimée.');
    }

    public function reorderLectures(Request $request, ModuleSection $section)
    {
        $this->assertOwnership($section->module);

        $validated = $request->validate([
            'lecture_ids' => 'required|array',
            'lecture_ids.*' => 'integer',
        ]);

        $existingIds = $section->lectures()->pluck('id');
        $requestedIds = collect($validated['lecture_ids']);

        abort_unless(
            $requestedIds->count() === $existingIds->count() && $requestedIds->diff($existingIds)->isEmpty(),
            422
        );

        DB::transaction(function () use ($requestedIds) {
            foreach ($requestedIds->values() as $position => $lectureId) {
                ModuleLecture::where('id', $lectureId)->update(['position' => $position]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function editLecture(ModuleLecture $lecture)
    {
        $this->assertOwnership($lecture->module);

        $lecture->load('section');

        return view('formateur.modules-builder.lecture-edit', [
            'module' => $lecture->module,
            'section' => $lecture->section,
            'lecture' => $lecture,
        ]);
    }

    public function moveLecture(Request $request, ModuleLecture $lecture)
    {
        $this->assertOwnership($lecture->module);

        $validated = $request->validate([
            'section_id' => 'required|integer',
            'position' => 'required|integer|min:0',
        ]);

        $targetSection = ModuleSection::find($validated['section_id']);

        abort_unless(
            $targetSection && (int) $targetSection->module_id === (int) $lecture->module_id,
            422
        );

        DB::transaction(function () use ($lecture, $targetSection, $validated) {
            $originSection = (int) $lecture->section_id === (int) $targetSection->id
                ? null
                : ModuleSection::find($lecture->section_id);

            $lecture->update(['section_id' => $targetSection->id]);

            $siblingIds = $targetSection->lectures()->pluck('id')
                ->reject(fn ($id) => (int) $id === (int) $lecture->id)
                ->values();

            $position = min((int) $validated['position'], $siblingIds->count());
            $siblingIds->splice($position, 0, [$lecture->id]);

            foreach ($siblingIds->values() as $index => $id) {
                ModuleLecture::where('id', $id)->update(['position' => $index]);
            }

            if ($originSection) {
                foreach ($originSection->lectures()->pluck('id')->values() as $index => $id) {
                    ModuleLecture::where('id', $id)->update(['position' => $index]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'lecture' => $this->lecturePayload($lecture->fresh()),
            ]);
        }

        return back()->with('success', 'Leçon déplacée.');
    }

    public function promoteLectureToSection(Request $request, ModuleLecture $lecture)
    {
        $this->assertOwnership($lecture->module);

        abort_if(! empty($lecture->content_blocks), 422, 'Cette leçon contient déjà du contenu, elle ne peut pas être transformée en chapitre.');

        $module = $lecture->module;

        $section = DB::transaction(function () use ($lecture, $module) {
            $position = (int) $module->sections()->max('position') + 1;

            $section = $module->sections()->create([
                'section_title' => $lecture->lecture_title,
                'position' => $position,
            ]);

            $lecture->delete();

            return $section;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'section' => $this->sectionPayload($section),
            ], 201);
        }

        return back()->with('success', 'Leçon transformée en chapitre.');
    }

    public function uploadImage(Request $request, Module $module)
    {
        $this->assertOwnership($module);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $file = $request->file('image');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $path = $file->storeAs(
            "modules_formateur/module_{$module->id}/images",
            now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension,
            'public'
        );

        return response()->json([
            'path' => $path,
            'url' => route('media.storage', ['path' => $path]),
        ]);
    }

    public function assignGroups(Request $request, Module $module)
    {
        $this->assertOwnership($module);

        $validated = $request->validate([
            'group_ids' => 'array',
            'group_ids.*' => 'integer',
        ]);

        $accessibleGroupIds = Group::query()
            ->accessibleByTrainer(auth()->id())
            ->pluck('groups.id')
            ->map(fn ($id) => (int) $id);

        $requestedIds = collect($validated['group_ids'] ?? [])->map(fn ($id) => (int) $id);

        $module->groups()->sync($requestedIds->intersect($accessibleGroupIds)->values());

        return back()->with('success', 'Groupes mis à jour.');
    }

    private function assertOwnership(Module $module): void
    {
        abort_unless(
            (int) $module->formateur_id === (int) auth()->id() && $module->is_trainer_authored,
            403
        );
    }

    private function sectionPayload(ModuleSection $section): array
    {
        return [
            'id' => $section->id,
            'section_title' => $section->section_title,
            'position' => $section->position,
        ];
    }

    private function lecturePayload(ModuleLecture $lecture): array
    {
        return [
            'id' => $lecture->id,
            'section_id' => $lecture->section_id,
            'module_id' => $lecture->module_id,
            'lecture_title' => $lecture->lecture_title,
            'content_type' => $lecture->content_type,
            'content_blocks' => $lecture->content_blocks ?? [],
            'position' => $lecture->position,
        ];
    }

    private function resolveTrainerCategory(): Category
    {
        return Category::firstOrCreate(
            ['category_slug' => 'modules-formateurs'],
            ['category_name' => 'Modules formateurs']
        );
    }

    private function resolveTrainerSubcategory(Category $category): SubCategory
    {
        return SubCategory::firstOrCreate(
            ['subcategory_slug' => 'contenu-personnalise'],
            ['category_id' => $category->id, 'subcategory_name' => 'Contenu personnalisé']
        );
    }

    private function sanitizeBlocks(?string $rawBlocksJson, int $moduleId): array
    {
        if (! $rawBlocksJson) {
            return [];
        }

        $decoded = json_decode($rawBlocksJson, true);
        if (! is_array($decoded)) {
            return [];
        }

        $allowedImagePrefix = "modules_formateur/module_{$moduleId}/images/";
        $sanitized = [];

        foreach (array_slice($decoded, 0, 100) as $block) {
            if (! is_array($block) || ! isset($block['type'])) {
                continue;
            }

            switch ($block['type']) {
                case 'text':
                    $html = $this->sanitizeHtml($block['html'] ?? null);
                    if ($html !== null) {
                        $sanitized[] = ['type' => 'text', 'html' => $html];
                    }
                    break;

                case 'image':
                    $path = (string) ($block['path'] ?? '');
                    if ($path === '' || ! str_starts_with($path, $allowedImagePrefix) || ! Storage::disk('public')->exists($path)) {
                        break;
                    }
                    $sanitized[] = [
                        'type' => 'image',
                        'path' => $path,
                        'caption' => Str::limit(strip_tags((string) ($block['caption'] ?? '')), 255, ''),
                    ];
                    break;

                case 'list':
                    $style = ($block['style'] ?? 'bullet') === 'numbered' ? 'numbered' : 'bullet';
                    $items = collect($block['items'] ?? [])
                        ->take(30)
                        ->map(fn ($item) => Str::limit(strip_tags((string) $item), 500, ''))
                        ->filter(fn ($item) => $item !== '')
                        ->values()
                        ->all();

                    if (! empty($items)) {
                        $sanitized[] = ['type' => 'list', 'style' => $style, 'items' => $items];
                    }
                    break;

                case 'quote':
                    $text = trim(strip_tags((string) ($block['text'] ?? '')));
                    if ($text === '') {
                        break;
                    }
                    $sanitized[] = [
                        'type' => 'quote',
                        'text' => Str::limit($text, 1000, ''),
                        'source' => Str::limit(strip_tags((string) ($block['source'] ?? '')), 255, ''),
                    ];
                    break;

                case 'divider':
                    $sanitized[] = ['type' => 'divider'];
                    break;
            }
        }

        return $sanitized;
    }

    private function sanitizeHtml(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><h1><h2><h3><h4><blockquote><a><code><pre>';
        $clean = strip_tags($html, $allowedTags);

        // Strip inline event handlers (onclick=..., onerror=...) left on allowed tags.
        $clean = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);

        // Neutralize javascript: URLs on <a href="...">.
        $clean = preg_replace_callback(
            '/<a\s+([^>]*)>/i',
            function (array $matches) {
                $attrs = preg_replace('/href\s*=\s*("|\')\s*javascript:[^"\']*\1/i', 'href=$1#$1', $matches[1]);

                return '<a '.$attrs.'>';
            },
            $clean
        );

        return $clean;
    }
}
