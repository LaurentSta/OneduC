<?php

namespace App\Domains\CatalogueFormations\Actions;

use App\Models\FormateurParcours;
use App\Models\Group;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BasculerGroupesVersionFormation
{
    /**
     * @param  array<int, int|string>  $groupeIds
     */
    public function execute(Module $nouvelleVersion, array $groupeIds): int
    {
        if ($nouvelleVersion->is_trainer_authored
            || $nouvelleVersion->publication_state !== Module::PUBLICATION_PUBLISHED) {
            throw ValidationException::withMessages([
                'group_ids' => 'La version cible doit être une formation publiée du catalogue.',
            ]);
        }

        $versionIds = Module::withTrashed()
            ->where('catalogue_key', $nouvelleVersion->catalogue_key)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $ids = collect($groupeIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $basculees = 0;

        DB::transaction(function () use ($ids, $versionIds, $nouvelleVersion, &$basculees): void {
            foreach ($ids as $groupeId) {
                $groupe = Group::query()->lockForUpdate()->find($groupeId);
                if (! $groupe) {
                    continue;
                }

                $pivot = DB::table('group_module')
                    ->where('group_id', $groupe->id)
                    ->whereIn('module_id', $versionIds)
                    ->orderBy('position')
                    ->first();

                if (! $pivot) {
                    continue;
                }

                $this->adapterParcoursDuGroupe($groupe, $versionIds, $nouvelleVersion->id);

                DB::table('group_module')
                    ->where('group_id', $groupe->id)
                    ->whereIn('module_id', $versionIds)
                    ->delete();

                DB::table('group_module')->insert([
                    'group_id' => $groupe->id,
                    'module_id' => $nouvelleVersion->id,
                    'position' => (int) $pivot->position,
                ]);

                $basculees++;
            }
        });

        return $basculees;
    }

    private function adapterParcoursDuGroupe(Group $groupe, array $versionIds, int $nouveauModuleId): void
    {
        $parcours = $groupe->formateurParcours;
        if (! $parcours || ! $parcours->items()->whereIn('module_id', $versionIds)->exists()) {
            return;
        }

        if ($parcours->groups()->whereKeyNot($groupe->id)->exists()) {
            $parcours = $this->dupliquerParcoursPourGroupe($parcours, $groupe, $versionIds, $nouveauModuleId);
        } else {
            $parcours->items()->whereIn('module_id', $versionIds)->update([
                'module_id' => $nouveauModuleId,
            ]);
        }
    }

    private function dupliquerParcoursPourGroupe(
        FormateurParcours $source,
        Group $groupe,
        array $versionIds,
        int $nouveauModuleId,
    ): FormateurParcours {
        $copie = $source->replicate();
        $copie->title = $source->title.' — '.$groupe->name;
        $copie->save();

        foreach ($source->items()->orderBy('position')->get() as $itemSource) {
            $item = $itemSource->replicate();
            $item->formateur_parcours_id = $copie->id;
            if ($item->module_id && in_array((int) $item->module_id, $versionIds, true)) {
                $item->module_id = $nouveauModuleId;
            }
            $item->save();
        }

        $groupe->update(['formateur_parcours_id' => $copie->id]);

        return $copie;
    }
}
