<?php

use App\Models\ActivityJournalEntry;
use App\Models\User;

function creerComptePourAccesGestionUtilisateursAdministrateur(string $role, string $suffixe): User
{
    return User::factory()->create([
        'prenom' => 'Acces',
        'name' => ucfirst($role),
        'username' => 'acces_'.$role.'_'.$suffixe,
        'email' => 'acces-'.$role.'-'.$suffixe.'@example.test',
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

dataset('roles_non_administrateurs_gestion_utilisateurs', [
    'formateur',
    'stagiaire',
    'observateur',
]);

it('redirige un visiteur non authentifié hors de la gestion des utilisateurs', function () {
    $this->get(route('admin.utilisateurs.index'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('refuse la gestion des utilisateurs aux rôles non administrateurs', function (string $role) {
    $utilisateur = creerComptePourAccesGestionUtilisateursAdministrateur($role, 'lecture');

    $this->actingAs($utilisateur)
        ->get(route('admin.utilisateurs.index'))
        ->assertRedirect('/connexion');

    $this->assertGuest();
})->with('roles_non_administrateurs_gestion_utilisateurs');

it('empêche un rôle non administrateur de créer un compte et ne journalise aucune mutation', function (string $role) {
    $utilisateur = creerComptePourAccesGestionUtilisateursAdministrateur($role, 'ecriture');
    $emailCible = 'creation-interdite-'.$role.'@example.test';

    $this->actingAs($utilisateur)
        ->post(route('admin.utilisateurs.store'), [
            'prenom' => 'Creation',
            'name' => 'Interdite',
            'username' => 'creation_interdite_'.$role,
            'email' => $emailCible,
            'password' => 'MotDePasse!123',
            'password_confirmation' => 'MotDePasse!123',
            'role' => 'stagiaire',
            'status' => '1',
        ])
        ->assertRedirect('/connexion');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => $emailCible]);
    expect(ActivityJournalEntry::query()->count())->toBe(0);
})->with('roles_non_administrateurs_gestion_utilisateurs');

it('refuse la connexion par email et mot de passe à un compte inactif', function () {
    $utilisateur = User::factory()->create([
        'email' => 'compte-inactif-connexion@example.test',
        'role' => 'formateur',
        'status' => false,
    ]);

    $this->from('/connexion')
        ->post(route('login.process'), [
            'email' => $utilisateur->email,
            'password' => 'password',
        ])
        ->assertRedirect('/connexion')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
