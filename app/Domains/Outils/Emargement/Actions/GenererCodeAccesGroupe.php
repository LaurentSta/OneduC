<?php

namespace App\Domains\Outils\Emargement\Actions;

use App\Models\Group;
use App\Services\CodeGeneratorService;
use Illuminate\Support\Facades\DB;

class GenererCodeAccesGroupe
{
    public function execute(Group $group): Group
    {
        return DB::transaction(function () use ($group) {
            $locked = Group::query()->whereKey($group->id)->lockForUpdate()->first();

            if ($locked->emargement_code === null) {
                $locked->update([
                    'emargement_code' => CodeGeneratorService::generateUniqueCode(Group::class, 'emargement_code', 6),
                ]);
            }

            return $locked;
        });
    }
}
