<?php

test('/inscription redirects permanently to the working formateur registration form', function () {
    $response = $this->get('/inscription');

    $response->assertRedirect('/inscription-formateur');
    $response->assertStatus(301);
});
