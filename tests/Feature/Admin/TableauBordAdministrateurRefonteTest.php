<?php

use App\Models\Group;
use App\Models\User;

function creerComptePourTableauBordAdministrateurRefondu(string $role, string $suffixe, array $attributs = []): User
{
    return User::factory()->create(array_merge([
        'prenom' => ucfirst($role),
        'name' => 'Tableau '.$suffixe,
        'username' => $role.'_tableau_'.$suffixe,
        'email' => $role.'-tableau-'.$suffixe.'@example.test',
        'role' => $role,
        'status' => true,
    ], $attributs));
}

it('affiche les compteurs opérationnels et les alertes du tableau de bord administrateur', function () {
    $admin = creerComptePourTableauBordAdministrateurRefondu('admin', 'principal');
    creerComptePourTableauBordAdministrateurRefondu('observateur', 'exclu');

    $formateurActif = creerComptePourTableauBordAdministrateurRefondu('formateur', 'actif', [
        'status' => true,
        'adhesion_status' => 'active',
        'adhesion_valid_until' => now()->addMonth()->toDateString(),
    ]);
    creerComptePourTableauBordAdministrateurRefondu('formateur', 'attente', [
        'status' => false,
        'adhesion_status' => 'pending',
        'adhesion_valid_until' => null,
    ]);
    $stagiaireAvecGroupe = creerComptePourTableauBordAdministrateurRefondu('stagiaire', 'rattache', [
        'status' => true,
    ]);
    creerComptePourTableauBordAdministrateurRefondu('stagiaire', 'sans-groupe', [
        'status' => false,
    ]);

    $groupeActif = Group::query()->create([
        'name' => 'Groupe actif tableau admin',
        'description' => 'Groupe avec stagiaire',
        'is_active' => true,
        'instructor_id' => $formateurActif->id,
    ]);
    Group::query()->create([
        'name' => 'Groupe vide tableau admin',
        'description' => 'Groupe sans stagiaire',
        'is_active' => false,
        'instructor_id' => $formateurActif->id,
    ]);
    $groupeActif->students()->attach($stagiaireAvecGroupe->id, ['role_in_group' => 'stagiaire']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $attendus = [
        'formateurCount' => 2,
        'stagiaireCount' => 2,
        'utilisateurCount' => 4,
        'utilisateurActifCount' => 2,
        'utilisateurInactifCount' => 2,
        'groupCount' => 2,
        'groupesActifsCount' => 1,
        'formateursEnAttenteCount' => 1,
        'adhesionsARegulariserCount' => 1,
        'stagiairesSansGroupeCount' => 1,
        'groupesSansStagiaireCount' => 1,
        'comptesCreesCeMoisCount' => 4,
    ];

    $response->assertOk()->assertViewIs('admin.index');

    foreach ($attendus as $cle => $valeur) {
        $response->assertViewHas($cle, $valeur);
    }

    $response
        ->assertViewHas('utilisateursRecents', function ($utilisateurs): bool {
            return $utilisateurs->count() === 4
                && $utilisateurs->every(fn (User $utilisateur): bool => in_array($utilisateur->role, ['formateur', 'stagiaire'], true));
        })
        ->assertSeeText('Comptes gérés')
        ->assertSeeText('2 actifs · 2 inactifs')
        ->assertSeeText('1 compte à activer')
        ->assertSeeText('Stagiaires sans groupe')
        ->assertSeeText('Groupes sans stagiaire')
        ->assertSeeText('Points d’attention');
});
