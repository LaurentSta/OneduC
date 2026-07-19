<?php

use App\Support\Parcours\RegistreOutilsParcours;
use Illuminate\Validation\ValidationException;

it('recense tous les outils pédagogiques et collaboratifs du dépôt', function () {
    $registre = app(RegistreOutilsParcours::class);

    expect(array_keys($registre->tous()))->toEqualCanonicalizing([
        'nuage-de-mots',
        'sondage',
        'quiz',
        'tableau-blanc',
        'mur-questions',
        'vrai-faux',
        'buzzer',
        'echelle',
        'composants',
        'roue',
        'minuteur',
        'emargement',
        'page-collaborative',
        'pendu',
        'memoire',
        'carrousel',
        'cartes-retourner',
        'tri-cartes',
    ]);
});

it('valide la configuration par défaut de chaque outil actif', function () {
    $registre = app(RegistreOutilsParcours::class);

    foreach ($registre->actifs() as $cle => $definition) {
        expect($registre->valider($cle, $definition['configuration_defaut']))
            ->toBeArray()
            ->toHaveKey('titre');
    }
});

it('écarte un outil désactivé même si sa configuration est valide', function () {
    config()->set('outils.minuteur.enabled', false);
    $registre = app(RegistreOutilsParcours::class);

    expect($registre->estActif('minuteur'))->toBeFalse()
        ->and(fn () => $registre->valider('minuteur', [
            'titre' => 'Minuteur',
            'consigne' => null,
            'duree_secondes' => 120,
        ]))->toThrow(ValidationException::class);
});
