<?php

namespace App\Http\Controllers\Backend;

use App\Domains\ModulesFormateur\Support\AccesFormationCatalogue;
use App\Domains\ModulesFormateur\Support\NettoyeurBlocsModule;
use App\Http\Controllers\Controller;
use App\Models\LectureObjective;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleSectionController extends Controller
{
    public function __construct(private readonly AccesFormationCatalogue $accesCatalogue) {}

    public function AddModuleSection(Request $request)
    {
        $module = Module::findOrFail((int) $request->input('module_id'));
        $this->accesCatalogue->assertEditable($module);

        ModuleSection::insert([
            'module_id' => $module->id,
            'section_title' => $request->section_title,
        ]);

        return redirect()->back()->with([
            'message' => 'Section ajoutée',
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
            'section_html' => ['nullable', 'string', 'max:20000'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,m4v,mov,avi,webm', 'max:307200'],
            'stay' => ['nullable', 'boolean'],
        ]);

        $section = ModuleSection::findOrFail($id);
        $this->assertSectionEditable($section);

        $sectionHtml = app(NettoyeurBlocsModule::class)
            ->sanitizeHtmlFragment((string) $request->input('section_html', ''));

        $videoUrl = trim((string) $request->input('video_url', ''));
        $videoUrl = $videoUrl === '' ? null : $videoUrl;

        if ($request->hasFile('video_file')) {
            $videoUrl = $this->storeSectionVideo($section, $request->file('video_file'));
        }

        $section->update([
            'section_title' => $request->input('section_title'),
            'section_html' => $sectionHtml,
            'video_url' => $videoUrl,
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

    public function DeleteSection($id)
    {
        $section = ModuleSection::find($id);

        if ($section) {
            $this->assertSectionEditable($section);

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
            'message' => 'Section supprimée',
            'alert-type' => 'success',
        ]);
    }

    private function storeSectionVideo(ModuleSection $section, UploadedFile $video): string
    {
        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $relativeFolder = 'sections/section_'.$section->id;
        $storageFolder = $videosBase.'/'.$relativeFolder;
        $disk = Storage::disk('public');

        if (! $disk->exists($storageFolder)) {
            $disk->makeDirectory($storageFolder);
        }

        $oldVideo = trim((string) $section->video_url);
        $oldCandidates = [];
        if ($oldVideo !== '') {
            $normalizedOld = ltrim($oldVideo, '/');

            if (Str::startsWith($oldVideo, $relativeFolder.'/')) {
                $oldCandidates[] = $videosBase.'/'.$oldVideo;
            }
            if (Str::startsWith($normalizedOld, 'storage/')) {
                $oldCandidates[] = Str::after($normalizedOld, 'storage/');
            }
            if (Str::startsWith($normalizedOld, $videosBase.'/')) {
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

        $fileName = now()->format('Ymd_His').'_'.Str::random(6).'_'.$baseName.'.'.$extension;
        $disk->putFileAs($storageFolder, $video, $fileName);

        return route('media.storage', ['path' => $storageFolder.'/'.$fileName], false);
    }

    private function assertSectionEditable(ModuleSection $section): void
    {
        $this->accesCatalogue->assertEditable($section->module()->firstOrFail());
    }

    private function deleteLectureAndDependencies(ModuleLecture $lecture): void
    {
        if (! empty($lecture->slides_path)) {
            Storage::disk('public')->deleteDirectory($lecture->slides_path);
        }
        if (! empty($lecture->slides_source_path)) {
            Storage::disk('local')->delete($lecture->slides_source_path);
        }

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
                    if (! empty($question->image_path)) {
                        Storage::disk('public')->delete($question->image_path);
                    }
                    if (! empty($question->audio_path)) {
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
                    if (! empty($question->image_path)) {
                        Storage::disk('public')->delete($question->image_path);
                    }
                    if (! empty($question->audio_path)) {
                        Storage::disk('public')->delete($question->audio_path);
                    }
                    $question->delete();
                    $deleted++;
                }
            });

        return $deleted;
    }
}
