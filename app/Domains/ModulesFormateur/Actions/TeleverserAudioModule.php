<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TeleverserAudioModule
{
    public function execute(Module $module, UploadedFile $file): Media
    {
        return $module->addMedia($file)->toMediaCollection('lesson-audios');
    }
}
