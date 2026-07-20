<?php

it('publie la déclaration et les documents de planification de l’accessibilité', function () {
    $declaration = $this->get(route('accessibilite'));

    $declaration
        ->assertOk()
        ->assertSeeText('Déclaration d’accessibilité')
        ->assertSeeText('conformément à l’article 47 de la loi n° 2005-102 du 11 février 2005')
        ->assertSeeText('Accessibilité : non conforme')
        ->assertSeeText('Le site Oneduc est non conforme avec le référentiel général d’amélioration de l’accessibilité')
        ->assertSeeText('Aucun audit de conformité RGAA complet')
        ->assertSee(route('accessibilite.schema'), false)
        ->assertSee(route('accessibilite.plan-2026'), false)
        ->assertSee(route('contact'), false);

    $this->get(route('accessibilite.schema'))
        ->assertOk()
        ->assertSeeText('Schéma pluriannuel 2026-2028')
        ->assertSeeText('Ressources humaines et financières')
        ->assertSeeText('Les futurs devis, contrats et conventions')
        ->assertSeeText('2026 — Établir le socle')
        ->assertSeeText('2028 — Pérenniser');

    $this->get(route('accessibilite.plan-2026'))
        ->assertOk()
        ->assertSeeText('Plan d’action 2026')
        ->assertSeeText('Réaliser l’audit RGAA 4.1.2')
        ->assertSeeText('Publier le bilan 2026');
});

it('rend le statut et l’accès rapide au contenu disponibles dans le site public', function () {
    $response = $this->get(route('accessibilite'));

    $response
        ->assertOk()
        ->assertSee('href="#contenu-principal"', false)
        ->assertSee('<main id="contenu-principal" tabindex="-1">', false)
        ->assertSee('href="'.route('accessibilite').'"', false)
        ->assertSeeText('Accessibilité : non conforme')
        ->assertSee('role="status"', false)
        ->assertSee("\$dispatch('open-modal', 'falc')", false);
});

it('regroupe les liens réglementaires dans une colonne du pied de page', function () {
    $piedDePage = view('frontend.body.footer')->render();

    expect($piedDePage)
        ->not->toContain('Accès rapides')
        ->not->toContain("Code d'accès stagiaire")
        ->not->toContain('Charte graphique')
        ->not->toContain('Nous contacter')
        ->not->toContain('Accessibilité : non conforme')
        ->toContain('Informations légales')
        ->toContain('>Accessibilité</a>')
        ->toContain('Mentions légales')
        ->toContain('Politique de confidentialité')
        ->toContain("Conditions d'utilisation")
        ->toContain('Cookies')
        ->toContain('Gérer mes cookies');

    expect(substr_count($piedDePage, route('accessibilite')))->toBe(1)
        ->and(substr_count($piedDePage, route('mentions-legales')))->toBe(1)
        ->and(substr_count($piedDePage, route('confidentialite')))->toBe(1)
        ->and(substr_count($piedDePage, route('conditions-utilisation')))->toBe(1)
        ->and(substr_count($piedDePage, route('cookies')))->toBe(1);
});

it('réserve le lien accessibilité au pied de page du site public', function () {
    $navigationsApplicatives = [
        'admin/body/sidebar.blade.php',
        'formateur/body_dashboard/sidebar.blade.php',
        'observateur/body_dashboard/sidebar.blade.php',
        'stagiaire/body_dashboard/sidebar.blade.php',
    ];

    foreach ($navigationsApplicatives as $navigation) {
        $this->assertStringNotContainsString(
            "route('accessibilite')",
            file_get_contents(resource_path('views/'.$navigation)),
            "La navigation {$navigation} ne doit pas afficher de lien Accessibilité."
        );
    }

    expect(file_exists(resource_path('views/partials/lien-accessibilite.blade.php')))->toBeFalse();
});
