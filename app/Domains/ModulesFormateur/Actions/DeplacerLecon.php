<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use Illuminate\Support\Facades\DB;

class DeplacerLecon
{
    public function execute(ModuleLecture $lecture, int $targetSectionId, int $position): ModuleLecture
    {
        $targetSection = ModuleSection::find($targetSectionId);

        abort_unless(
            $targetSection && (int) $targetSection->module_id === (int) $lecture->module_id,
            422
        );

        DB::transaction(function () use ($lecture, $targetSection, $position) {
            $originSection = (int) $lecture->section_id === (int) $targetSection->id
                ? null
                : ModuleSection::find($lecture->section_id);

            $lecture->update(['section_id' => $targetSection->id]);

            $siblingIds = $targetSection->lectures()->orderBy('position')->pluck('id')
                ->reject(fn ($id) => (int) $id === (int) $lecture->id)
                ->values();

            $safePosition = max(0, min($position, $siblingIds->count()));
            $siblingIds->splice($safePosition, 0, [$lecture->id]);

            foreach ($siblingIds->values() as $index => $id) {
                ModuleLecture::where('id', $id)->update(['position' => $index]);
            }

            if ($originSection) {
                foreach ($originSection->lectures()->orderBy('position')->pluck('id')->values() as $index => $id) {
                    ModuleLecture::where('id', $id)->update(['position' => $index]);
                }
            }
        });

        return $lecture->fresh();
    }
}
