<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\Module;
use App\Models\ModuleSection;
use Illuminate\Support\Facades\DB;

class ReordonnerChapitres
{
    public function execute(Module $module, array $sectionIds): void
    {
        $existingIds = $module->sections()->pluck('id');
        $requestedIds = collect($sectionIds);

        abort_unless(
            $requestedIds->count() === $existingIds->count()
                && $requestedIds->unique()->count() === $requestedIds->count()
                && $requestedIds->diff($existingIds)->isEmpty(),
            422
        );

        DB::transaction(function () use ($requestedIds) {
            foreach ($requestedIds->values() as $position => $sectionId) {
                ModuleSection::where('id', $sectionId)->update(['position' => $position]);
            }
        });
    }
}
