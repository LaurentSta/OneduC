<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\Module;
use Illuminate\Support\Facades\Schema;

class AccesFormationCatalogue
{
    public function assertCatalogue(Module $module): void
    {
        abort_if(
            (bool) $module->is_trainer_authored,
            403,
            'Une création personnelle de formateur ne peut pas être modifiée depuis le catalogue.'
        );
    }

    public function assertEditable(Module $module): void
    {
        $this->assertCatalogue($module);

        if (Schema::hasColumn($module->getTable(), 'publication_state')) {
            abort_unless(
                $module->estModifiableParAdministrateur(),
                403,
                'Une formation publiée ou archivée est en lecture seule. Créez une nouvelle version pour la modifier.'
            );

            return;
        }

        // Compatibilité transitoire avant l'ajout du cycle de publication :
        // un module actif est considéré comme déjà publié et reste immuable.
        abort_if(
            (bool) $module->status,
            403,
            'Une formation active est en lecture seule.'
        );
    }
}
