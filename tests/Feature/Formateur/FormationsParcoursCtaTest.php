<?php

use App\Models\FormateurParcours;
use App\Models\User;

function createParcoursCtaFormateur(): User
{
    return User::factory()->create([
        'role' => 'formateur',
        'status' => true,
    ]);
}

it('shows only the first parcours CTA on the empty parcours tab', function () {
    $formateur = createParcoursCtaFormateur();

    $response = $this->actingAs($formateur)
        ->get(route('formateur.formations.index', ['tab' => 'parcours']));

    $response->assertOk();
    $response->assertSee('Créer mon premier parcours');
    $response->assertDontSee('Créer un parcours');
});

it('shows the standard parcours CTA after at least one parcours exists', function () {
    $formateur = createParcoursCtaFormateur();

    FormateurParcours::query()->create([
        'formateur_id' => $formateur->id,
        'title' => 'Parcours découverte',
        'description' => null,
    ]);

    $response = $this->actingAs($formateur)
        ->get(route('formateur.formations.index', ['tab' => 'parcours']));

    $response->assertOk();
    $response->assertSee('Créer un parcours');
    $response->assertDontSee('Créer mon premier parcours');
});

it('keeps the same CTA behavior on the dedicated parcours index', function () {
    $formateur = createParcoursCtaFormateur();

    $emptyResponse = $this->actingAs($formateur)
        ->get(route('formateur.mes-formations.index'));

    $emptyResponse->assertOk();
    $emptyResponse->assertSee('Créer mon premier parcours');
    $emptyResponse->assertDontSee('Créer un parcours');

    FormateurParcours::query()->create([
        'formateur_id' => $formateur->id,
        'title' => 'Parcours confirmé',
        'description' => null,
    ]);

    $filledResponse = $this->actingAs($formateur)
        ->get(route('formateur.mes-formations.index'));

    $filledResponse->assertOk();
    $filledResponse->assertSee('Créer un parcours');
    $filledResponse->assertDontSee('Créer mon premier parcours');
});
