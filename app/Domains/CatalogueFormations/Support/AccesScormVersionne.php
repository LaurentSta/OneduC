<?php

namespace App\Domains\CatalogueFormations\Support;

use App\Models\Module;
use App\Models\ScormPackageVersion;
use App\Support\LearningAssetPath;

class AccesScormVersionne
{
    public function autorise(
        Module $module,
        string $cleBloc,
        ScormPackageVersion $version,
    ): bool {
        if ($version->folder === LearningAssetPath::lessonBlockScormFolder($module->id, $cleBloc)) {
            return true;
        }

        $idsAutorises = collect([$module->id]);

        if ($module->catalogue_key) {
            $idsAutorises = $idsAutorises->merge(
                Module::withTrashed()
                    ->where('catalogue_key', $module->catalogue_key)
                    ->pluck('id')
            );
        }

        $source = $module->moduleSource;
        while ($source) {
            $idsAutorises->push($source->id);
            $source = $source->moduleSource;
        }

        return $idsAutorises
            ->unique()
            ->contains(fn ($moduleId) => $version->folder === LearningAssetPath::lessonBlockScormFolder(
                (int) $moduleId,
                $cleBloc,
            ));
    }
}
