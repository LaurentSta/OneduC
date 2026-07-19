<?php

namespace App\Http\Controllers\Formateur;

use App\Domains\ModulesFormateur\Support\AccesModule;
use App\Http\Controllers\Controller;
use App\Models\LessonResource;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LessonResourceController extends Controller
{
    public function __construct(private readonly AccesModule $accesModule) {}

    public function store(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture): RedirectResponse
    {
        $this->assertCanManage($request, $module, $section, $lecture);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'resource_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,avif,pdf,doc,docx,odt,txt,rtf,xls,xlsx,ods,ppt,pptx,odp,csv',
                'max:51200',
            ],
            'is_visible_to_stagiaire' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('resource_file');
        $baseName = Str::slug(pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $storedPath = $file->storeAs(
            'module-resources/module_' . $module->id,
            now()->format('Ymd_His') . '_' . Str::random(8) . '_' . ($baseName ?: 'resource') . '.' . $extension,
            'public'
        );

        $nextPosition = ((int) $module->moduleResources()->max('position')) + 1;

        $module->moduleResources()->create([
            'lecture_id' => $lecture->id,
            'title' => trim((string) ($validated['title'] ?? '')) ?: pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_path' => $storedPath,
            'original_name' => (string) $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => (int) $file->getSize(),
            'is_visible_to_stagiaire' => $request->boolean('is_visible_to_stagiaire'),
            'position' => $nextPosition,
        ]);

        return back()->with('success', 'Ressource ajoutée au module.');
    }

    public function toggleVisibility(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture, LessonResource $resource): RedirectResponse
    {
        $this->assertCanManage($request, $module, $section, $lecture, $resource);

        $validated = $request->validate([
            'is_visible_to_stagiaire' => ['required', 'boolean'],
        ]);

        $resource->update([
            'is_visible_to_stagiaire' => (bool) $validated['is_visible_to_stagiaire'],
        ]);

        return back()->with('success', 'Visibilité stagiaire mise à jour.');
    }

    public function destroy(Request $request, Module $module, ModuleSection $section, ModuleLecture $lecture, LessonResource $resource): RedirectResponse
    {
        $this->assertCanManage($request, $module, $section, $lecture, $resource);

        if (!empty($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return back()->with('success', 'Ressource supprimée.');
    }

    private function assertCanManage(
        Request $request,
        Module $module,
        ModuleSection $section,
        ModuleLecture $lecture,
        ?LessonResource $resource = null
    ): void {
        $user = $request->user();

        abort_unless((int) $section->module_id === (int) $module->id, 404);
        abort_unless((int) $lecture->module_id === (int) $module->id, 404);
        abort_unless((int) $lecture->section_id === (int) $section->id, 404);
        $this->accesModule->assertOwner($module, (int) $user->id);

        if ($resource) {
            abort_unless((int) $resource->module_id === (int) $module->id, 404);
        }
    }
}
