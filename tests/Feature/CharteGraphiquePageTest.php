<?php

it('renders the public charte graphique page', function () {
    $response = $this->get(route('charte-graphique'));

    $response
        ->assertOk()
        ->assertSeeText('Charte graphique')
        ->assertSeeText('Référentiel visuel officiel')
        ->assertSee(route('contact'), false);
});
