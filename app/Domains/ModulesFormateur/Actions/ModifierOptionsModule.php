<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModifierOptionsModule
{
    public function execute(Module $module, array $data, ?UploadedFile $moduleImage, ?UploadedFile $headerImage, ?UploadedFile $videoFile): Module
    {
        $imagePath = $module->module_image;
        if ($moduleImage) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->storeImage($moduleImage, 'uploads/modules/images');
        }

        $headerImagePath = $module->header_image;
        if ($headerImage) {
            if ($headerImagePath) {
                Storage::disk('public')->delete($headerImagePath);
            }
            $headerImagePath = $this->storeImage($headerImage, 'uploads/modules/headers');
        }

        $moduleVideo = $module->module_video;
        if ($videoFile) {
            $moduleVideo = $this->storeVideo($module, $videoFile);
        }

        $module->update([
            'module_image' => $imagePath,
            'header_image' => $headerImagePath,
            'module_video' => $moduleVideo,
            'category_id' => $data['category_id'],
            'label' => $data['label'] ?? null,
            'duree' => $data['duree'] ?? null,
            'estimated_question_seconds' => $data['estimated_question_seconds'] ?? null,
            'resources' => $data['resources'] ?? null,
            'prerequi' => $data['prerequi'] ?? null,
            'objectifs' => $this->normaliserObjectifs($data['objectifs'] ?? null),
            'certificat' => $data['certificat'] ?? false,
            'status' => $data['status'] ?? false,
        ]);

        return $module;
    }

    /**
     * @return array<int, string>|null
     */
    private function normaliserObjectifs(?string $rawObjectifs): ?array
    {
        if ($rawObjectifs === null || trim($rawObjectifs) === '') {
            return null;
        }

        $objectifs = collect(preg_split('/\r\n|\r|\n/', $rawObjectifs))
            ->map(fn ($ligne) => trim($ligne))
            ->filter()
            ->values()
            ->all();

        return $objectifs !== [] ? $objectifs : null;
    }

    private function storeImage(UploadedFile $file, string $folder): string
    {
        $name = now()->format('Ymd_His').'_'.Str::random(8).'.'.strtolower((string) $file->getClientOriginalExtension());
        $file->storeAs($folder, $name, 'public');

        return $folder.'/'.$name;
    }

    private function storeVideo(Module $module, UploadedFile $video): string
    {
        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $ownFolder = 'modules/module_'.$module->id;
        $storageFolder = $videosBase.'/'.$ownFolder;
        $disk = Storage::disk('public');

        if (! $disk->exists($storageFolder)) {
            $disk->makeDirectory($storageFolder);
        }

        if ($module->module_video) {
            $this->deleteOwnVideo($module, $module->module_video);
        }

        $baseName = Str::slug(pathinfo((string) $video->getClientOriginalName(), PATHINFO_FILENAME));
        if ($baseName === '') {
            $baseName = 'module-video';
        }

        $extension = strtolower((string) $video->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'mp4';
        }

        $fileName = now()->format('Ymd_His').'_'.Str::random(6).'_'.$baseName.'.'.$extension;
        $disk->putFileAs($storageFolder, $video, $fileName);

        return route('media.storage', ['path' => $storageFolder.'/'.$fileName], false);
    }

    /**
     * Only ever deletes a video that lives in this module's own folder — a duplicated
     * catalog module can point to another module's video file, which must not be touched.
     */
    private function deleteOwnVideo(Module $module, string $videoPath): void
    {
        $videosBase = trim((string) config('learning_assets.videos_base', 'modules/videos'), '/');
        $ownFolder = $videosBase.'/modules/module_'.$module->id;
        $relative = Str::after($videoPath, '/media/storage/');

        if ($relative === $videoPath || ! Str::startsWith($relative, $ownFolder.'/')) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($relative)) {
            $disk->delete($relative);
        }
        if ($disk->exists($ownFolder) && count($disk->allFiles($ownFolder)) === 0) {
            $disk->deleteDirectory($ownFolder);
        }
    }
}
