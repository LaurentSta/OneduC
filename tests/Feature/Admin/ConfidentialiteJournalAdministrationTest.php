<?php

use App\Models\ActivityJournalEntry;
use App\Models\User;

function creerAdministrateurPourConfidentialiteJournalUtilisateurs(): User
{
    return User::factory()->create([
        'prenom' => 'Admin',
        'name' => 'Confidentialite',
        'username' => 'admin_confidentialite_journal',
        'email' => 'admin-confidentialite-journal@example.test',
        'role' => 'admin',
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function donneesPersonnellesPourConfidentialiteJournalUtilisateurs(string $suffixe): array
{
    return [
        'prenom' => 'PrenomSecret'.$suffixe,
        'name' => 'NomSecret'.$suffixe,
        'username' => 'identifiant_secret_'.strtolower($suffixe),
        'email' => 'email-secret-'.strtolower($suffixe).'@example.test',
        'password' => 'MotDePasse!123',
        'password_confirmation' => 'MotDePasse!123',
        'phone' => '0601020304',
        'address' => '99 adresse confidentielle '.$suffixe,
        'societe' => 'SocieteConfidentielle'.$suffixe,
        'code_acces' => 'ZX'.str_pad((string) (strlen($suffixe) * 7), 4, '0', STR_PAD_LEFT),
        'role' => 'stagiaire',
        'status' => '1',
    ];
}

it('journalise la gestion des utilisateurs sans données personnelles ni code d’accès', function () {
    $admin = creerAdministrateurPourConfidentialiteJournalUtilisateurs();
    $creation = donneesPersonnellesPourConfidentialiteJournalUtilisateurs('Creation');

    $this->actingAs($admin)
        ->post(route('admin.utilisateurs.store'), $creation)
        ->assertRedirect();

    $stagiaire = User::query()->where('email', $creation['email'])->firstOrFail();
    $modification = donneesPersonnellesPourConfidentialiteJournalUtilisateurs('Modification');
    $modification['email'] = $creation['email'];
    $modification['code_acces'] = $creation['code_acces'];

    $this->actingAs($admin)
        ->put(route('admin.utilisateurs.update', $stagiaire), $modification)
        ->assertRedirect(route('admin.utilisateurs.edit', $stagiaire));

    $entrees = ActivityJournalEntry::query()
        ->whereIn('route_name', [
            'admin.utilisateurs.store',
            'admin.utilisateurs.update',
        ])
        ->orderBy('id')
        ->get();

    expect($entrees)->toHaveCount(2);

    $champsInterdits = [
        'prenom',
        'name',
        'username',
        'email',
        'phone',
        'address',
        'societe',
        'code_acces',
        'password',
        'password_confirmation',
    ];
    $valeursInterdites = collect([$creation, $modification])
        ->flatMap(fn (array $donnees) => collect($champsInterdits)
            ->map(fn (string $champ) => $donnees[$champ] ?? null))
        ->filter()
        ->unique()
        ->values();

    foreach ($entrees as $entree) {
        $contexte = $entree->context ?? [];
        $contexteEncode = json_encode($contexte, JSON_THROW_ON_ERROR);

        expect($contexte)->toBeArray()
            ->and(array_intersect($champsInterdits, array_keys($contexte)))->toBe([])
            ->and($contexte)->toHaveKey('role', 'stagiaire');

        foreach ($valeursInterdites as $valeurInterdite) {
            expect($contexteEncode)->not->toContain((string) $valeurInterdite);
        }
    }
});
