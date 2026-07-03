<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use Illuminate\Support\Facades\DB;

class ReordonnerLecons
{
    public function execute(ModuleSection $section, array $lectureIds): void
    {
        $existingIds = $section->lectures()->pluck('id');
        $requestedIds = collect($lectureIds);

        abort_unless(
            $requestedIds->count() === $existingIds->count()
                && $requestedIds->unique()->count() === $requestedIds->count()
                && $requestedIds->diff($existingIds)->isEmpty(),
            422
        );

        DB::transaction(function () use ($requestedIds) {
            foreach ($requestedIds->values() as $position => $lectureId) {
                ModuleLecture::where('id', $lectureId)->update(['position' => $position]);
            }
        });
    }
}
