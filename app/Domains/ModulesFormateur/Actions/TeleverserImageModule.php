<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class TeleverserImageModule
{
    public function execute(Module $module, UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return $file->storeAs(
            "modules_formateur/module_{$module->id}/images",
            now()->format('Ymd_His').'_'.Str::random(8).'.'.$extension,
            'public'
        );
    }
}
