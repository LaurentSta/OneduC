<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createEmargementThrottleUser(): User
{
    return User::query()->create([
        'prenom' => 'Stagiaire',
        'name' => 'Stagiaire EmargementThrottle',
        'username' => 'stagiaire_emargement_throttle_'.uniqid(),
        'email' => 'stagiaire.emargement.throttle.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => 'stagiaire',
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

it('throttles repeated join-by-code lookups from the same ip', function () {
    $stagiaire = createEmargementThrottleUser();

    $response = null;
    for ($i = 0; $i < 21; $i++) {
        $response = $this->actingAs($stagiaire)->get(route('emargement.join.code', ['code' => 'ZZZZZZ']));
    }

    $response->assertStatus(429);
});
