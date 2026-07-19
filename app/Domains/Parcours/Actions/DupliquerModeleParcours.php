<?php

namespace App\Domains\Parcours\Actions;

use App\Models\FormateurParcours;
use App\Models\FormateurParcoursItem;
use App\Models\ModeleParcours;
use App\Models\ModeleParcoursItem;
use App\Models\Module;
use App\Models\User;
use App\Support\Parcours\RegistreOutilsParcours;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DupliquerModeleParcours
{
    public function __construct(private readonly RegistreOutilsParcours $registre) {}

    /**
     * Crée uniquement la structure appartenant au formateur.
     * Aucune session d'outil, réponse, participation, donnée de résultat ou code
     * d'accès n'est créé par cette action.
     */
    public function executer(ModeleParcours $modele, User $formateur): FormateurParcours
    {
        if ($formateur->role !== 'formateur') {
            throw ValidationException::withMessages([
                'formateur' => 'Seul un formateur peut dupliquer un modèle de parcours.',
            ]);
        }

        if (! $modele->estPublie()) {
            throw ValidationException::withMessages([
                'modele' => 'Seul un modèle publié peut être dupliqué.',
            ]);
        }

        $modele->loadMissing('items');

        if ($modele->items->isEmpty()) {
            throw ValidationException::withMessages([
                'modele' => 'Ce modèle publié ne contient aucune étape.',
            ]);
        }

        return DB::transaction(function () use ($modele, $formateur): FormateurParcours {
            $parcours = FormateurParcours::create([
                'formateur_id' => $formateur->id,
                'title' => $modele->titre,
                'description' => $modele->description,
            ]);

            // Affectation explicite : la colonne peut ne pas encore faire partie du
            // fillable de l'ancien modèle durant l'intégration progressive.
            $parcours->modele_parcours_id = $modele->id;
            $parcours->save();

            $maintenant = now();
            $lignes = $modele->items
                ->sortBy('position')
                ->values()
                ->map(function (ModeleParcoursItem $item, int $index) use ($parcours, $maintenant): array {
                    $ligne = [
                        'formateur_parcours_id' => $parcours->id,
                        'position' => $index + 1,
                        'type' => $item->type,
                        'module_id' => null,
                        'outil' => null,
                        'configuration' => null,
                        'wc_title' => null,
                        'wc_questions' => null,
                        'wc_duration' => null,
                        'poll_questions' => null,
                        'poll_duration' => null,
                        'created_at' => $maintenant,
                        'updated_at' => $maintenant,
                    ];

                    if ($item->estModule()) {
                        $moduleValide = Module::query()
                            ->whereKey($item->module_id)
                            ->where('is_trainer_authored', false)
                            ->assignable()
                            ->exists();

                        if (! $moduleValide) {
                            throw ValidationException::withMessages([
                                'modele' => "L’étape {$item->position} référence une formation officielle indisponible.",
                            ]);
                        }

                        $ligne['module_id'] = $item->module_id;

                        return $ligne;
                    }

                    if (! $item->estOutil() || blank($item->outil)) {
                        throw ValidationException::withMessages([
                            'modele' => "Le type de l’étape {$item->position} n’est pas pris en charge.",
                        ]);
                    }

                    $configuration = $this->registre->valider(
                        $item->outil,
                        $item->configuration ?? [],
                        'modele.items.'.$item->position.'.configuration'
                    );

                    $ligne['outil'] = $item->outil;
                    $ligne['configuration'] = json_encode($configuration, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

                    return $ligne;
                })
                ->all();

            FormateurParcoursItem::insert($lignes);

            return $parcours->load('items');
        });
    }
}
