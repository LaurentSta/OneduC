<?php

use App\Models\User;

test('stagiaire can login with a valid access code', function () {
    $user = User::factory()->create([
        'role' => 'stagiaire',
        'code_acces' => 'ABC123',
    ]);

    $response = $this->post('/stagiaire/connexion-code', [
        'code_acces' => 'ABC123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('stagiaire.dashboard'));
});

test('inactive stagiaire cannot login with an access code', function () {
    User::factory()->create([
        'role' => 'stagiaire',
        'status' => false,
        'code_acces' => 'OFF123',
    ]);

    $response = $this->post('/stagiaire/connexion-code', [
        'code_acces' => 'OFF123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('code_acces');
});

test('connexion-code route is blocked with 429 after too many attempts from the same IP', function () {
    for ($i = 0; $i < 10; $i++) {
        $response = $this->post('/stagiaire/connexion-code', [
            'code_acces' => 'ZZZZZZ',
        ]);
        $response->assertStatus(302);
    }

    $response = $this->post('/stagiaire/connexion-code', [
        'code_acces' => 'ZZZZZZ',
    ]);

    $response->assertStatus(429);
});
