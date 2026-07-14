<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

it('rejects an svg upload as a stagiaire profile photo', function () {
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $response = $this->actingAs($stagiaire)
        ->post(route('stagiaire.profil.store'), [
            'name' => $stagiaire->name,
            'prenom' => $stagiaire->prenom,
            'email' => $stagiaire->email,
            'photo' => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'),
        ]);

    $response->assertSessionHasErrors('photo');
});

it('accepts a jpg upload as a stagiaire profile photo', function () {
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'password_changed_at' => now()]);

    $response = $this->actingAs($stagiaire)
        ->post(route('stagiaire.profil.store'), [
            'name' => $stagiaire->name,
            'prenom' => $stagiaire->prenom,
            'email' => $stagiaire->email,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response->assertSessionDoesntHaveErrors('photo');

    $photo = $stagiaire->fresh()->photo;
    expect($photo)->not->toBeNull();
    @unlink(public_path('upload/user_images/'.$photo));
});

it('rejects an svg upload as a formateur profile photo', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $response = $this->actingAs($formateur)
        ->post(route('formateur.profil.store'), [
            'name' => $formateur->name,
            'prenom' => $formateur->prenom,
            'email' => $formateur->email,
            'photo' => UploadedFile::fake()->create('avatar.svg', 10, 'image/svg+xml'),
        ]);

    $response->assertSessionHasErrors('photo');
});

it('accepts a jpg upload as a formateur profile photo', function () {
    $formateur = User::factory()->create(['role' => 'formateur']);

    $response = $this->actingAs($formateur)
        ->post(route('formateur.profil.store'), [
            'name' => $formateur->name,
            'prenom' => $formateur->prenom,
            'email' => $formateur->email,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response->assertSessionDoesntHaveErrors('photo');

    $photo = $formateur->fresh()->photo;
    expect($photo)->not->toBeNull();
    @unlink(public_path('upload/formateur_images/'.$photo));
});
