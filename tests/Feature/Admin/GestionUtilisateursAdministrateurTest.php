<?php

use App\Models\ActivityJournalEntry;
use App\Models\Group;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

function creerAdministrateurPourGestionUtilisateursRefondue(string $suffixe): User
{
    return User::factory()->create([
        'prenom' => 'Admin',
        'name' => 'Gestion',
        'username' => 'admin_gestion_'.$suffixe,
        'email' => 'admin-gestion-'.$suffixe.'@example.test',
        'role' => 'admin',
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function donneesFormateurPourGestionUtilisateursRefondue(string $suffixe): array
{
    return [
        'prenom' => 'Alice',
        'name' => 'Durand',
        'username' => 'alice_formateur_'.$suffixe,
        'email' => 'alice-formateur-'.$suffixe.'@example.test',
        'password' => 'MotDePasse!123',
        'password_confirmation' => 'MotDePasse!123',
        'phone' => '0102030405',
        'address' => '12 rue des Tests',
        'societe' => 'Formation Exemple',
        'role' => 'formateur',
        'status' => '1',
        'adhesion_status' => 'active',
        'adhesion_valid_until' => now()->addYear()->toDateString(),
    ];
}

function creerGroupePourGestionUtilisateursRefondue(User $formateur, string $suffixe): Group
{
    return Group::query()->create([
        'name' => 'Groupe gestion '.$suffixe,
        'description' => 'Groupe utilisé par les tests de gestion administrateur',
        'is_active' => true,
        'instructor_id' => $formateur->id,
    ]);
}

dataset('mots_de_passe_invalides_creation_utilisateur_admin', [
    'moins de douze caractères' => [str_repeat('a', 11), str_repeat('a', 11)],
    'confirmation différente' => ['MotDePasse!123', 'MotDePasse!124'],
]);

dataset('roles_non_geres_creation_utilisateur_admin', [
    'admin',
    'observateur',
]);

it('affiche les formulaires de création et de modification des rôles gérés', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('formulaires');
    $formateur = User::factory()->create([
        'role' => 'formateur',
        'status' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.utilisateurs.create', ['role' => 'stagiaire']))
        ->assertOk()
        ->assertViewIs('admin.backend.utilisateurs.creer')
        ->assertViewHas('roleSelectionne', 'stagiaire');

    $this->actingAs($admin)
        ->get(route('admin.utilisateurs.edit', $formateur))
        ->assertOk()
        ->assertViewIs('admin.backend.utilisateurs.modifier')
        ->assertViewHas('utilisateur', fn (User $utilisateur): bool => $utilisateur->is($formateur));
});

it('filtre le répertoire par rôle statut rattachement et recherche sans exposer les autres rôles', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('filtres');
    $cible = User::factory()->create([
        'prenom' => 'Cible',
        'name' => 'SansGroupe',
        'username' => 'cible_filtres_admin',
        'email' => 'cible-filtres@example.test',
        'role' => 'stagiaire',
        'status' => false,
    ]);
    $formateurMauvaisRole = User::factory()->create([
        'prenom' => 'Cible',
        'name' => 'MauvaisRole',
        'email' => 'cible-formateur-filtres@example.test',
        'role' => 'formateur',
        'status' => false,
    ]);
    $stagiaireMauvaisStatut = User::factory()->create([
        'prenom' => 'Cible',
        'name' => 'MauvaisStatut',
        'email' => 'cible-active-filtres@example.test',
        'role' => 'stagiaire',
        'status' => true,
    ]);
    $formateurDuGroupe = User::factory()->create([
        'prenom' => 'Responsable',
        'name' => 'LeurreRattachement',
        'email' => 'responsable-leurre-filtres@example.test',
        'role' => 'formateur',
        'status' => true,
    ]);
    $groupe = creerGroupePourGestionUtilisateursRefondue($formateurDuGroupe, 'filtre-rattachement');
    $stagiaireMauvaisRattachement = User::factory()->create([
        'prenom' => 'Cible',
        'name' => 'AvecGroupe',
        'email' => 'cible-avec-groupe-filtres@example.test',
        'role' => 'stagiaire',
        'status' => false,
    ]);
    $stagiaireMauvaiseRecherche = User::factory()->create([
        'prenom' => 'Hors',
        'name' => 'Recherche',
        'username' => 'hors_recherche_filtres',
        'email' => 'hors-recherche-filtres@example.test',
        'role' => 'stagiaire',
        'status' => false,
    ]);
    $stagiaireMauvaisRattachement->groupesStagiaire()->attach($groupe->id, ['role_in_group' => 'stagiaire']);
    $observateur = User::factory()->observateur()->create([
        'name' => 'Cible observateur',
        'email' => 'cible-observateur-filtres@example.test',
        'status' => false,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.utilisateurs.index', [
        'role' => 'stagiaire',
        'statut' => 'inactif',
        'rattachement' => 'sans_groupe',
        'recherche' => 'Cible',
        'tri' => 'nom',
        'par_page' => 50,
    ]));

    $response
        ->assertOk()
        ->assertViewHas('utilisateurs', function (LengthAwarePaginator $utilisateurs) use ($cible): bool {
            return $utilisateurs->total() === 1
                && $utilisateurs->perPage() === 50
                && $utilisateurs->getCollection()->pluck('id')->all() === [$cible->id];
        })
        ->assertSee($cible->email)
        ->assertDontSee($formateurMauvaisRole->email)
        ->assertDontSee($stagiaireMauvaisStatut->email)
        ->assertDontSee($stagiaireMauvaisRattachement->email)
        ->assertDontSee($stagiaireMauvaiseRecherche->email)
        ->assertDontSee($observateur->email);
});

it('considère un co-formateur comme rattaché à un groupe dans les filtres individuels', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('filtre-co-formateur');
    $formateurResponsable = User::factory()->create([
        'role' => 'formateur',
        'status' => true,
    ]);
    $coFormateur = User::factory()->create([
        'name' => 'CoformateurRattache',
        'email' => 'co-formateur-rattache@example.test',
        'role' => 'formateur',
        'status' => true,
    ]);
    $formateurSansGroupe = User::factory()->create([
        'name' => 'FormateurSansGroupe',
        'email' => 'formateur-sans-groupe@example.test',
        'role' => 'formateur',
        'status' => true,
    ]);
    $groupe = creerGroupePourGestionUtilisateursRefondue($formateurResponsable, 'co-formateur');
    $groupe->coFormateurs()->attach($coFormateur->id, ['role_in_group' => 'formateur']);

    $avecGroupe = $this->actingAs($admin)->get(route('admin.utilisateurs.index', [
        'role' => 'formateur',
        'rattachement' => 'avec_groupe',
        'par_page' => 50,
    ]));

    $avecGroupe
        ->assertOk()
        ->assertViewHas('utilisateurs', function (LengthAwarePaginator $utilisateurs) use (
            $coFormateur,
            $formateurSansGroupe,
        ): bool {
            $utilisateur = $utilisateurs->getCollection()->firstWhere('id', $coFormateur->id);

            return $utilisateur instanceof User
                && $utilisateur->groupes_formateur_count === 1
                && ! $utilisateurs->getCollection()->contains('id', $formateurSansGroupe->id);
        })
        ->assertSee($coFormateur->email)
        ->assertDontSee($formateurSansGroupe->email);

    $this->actingAs($admin)
        ->get(route('admin.utilisateurs.index', [
            'role' => 'formateur',
            'rattachement' => 'sans_groupe',
            'par_page' => 50,
        ]))
        ->assertOk()
        ->assertViewHas('utilisateurs', function (LengthAwarePaginator $utilisateurs) use (
            $coFormateur,
            $formateurSansGroupe,
        ): bool {
            return $utilisateurs->getCollection()->contains('id', $formateurSansGroupe->id)
                && ! $utilisateurs->getCollection()->contains('id', $coFormateur->id);
        })
        ->assertSee($formateurSansGroupe->email)
        ->assertDontSee($coFormateur->email);
});

it('pagine le répertoire des utilisateurs côté serveur', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('pagination');

    User::factory()->count(21)->create([
        'role' => 'stagiaire',
        'status' => true,
    ]);

    $premierePage = $this->actingAs($admin)->get(route('admin.utilisateurs.index', [
        'role' => 'stagiaire',
        'par_page' => 20,
    ]));

    $premierePage->assertOk()->assertViewHas(
        'utilisateurs',
        fn (LengthAwarePaginator $utilisateurs): bool => $utilisateurs->total() === 21
            && $utilisateurs->count() === 20
            && $utilisateurs->lastPage() === 2
    );

    $this->actingAs($admin)
        ->get(route('admin.utilisateurs.index', [
            'role' => 'stagiaire',
            'par_page' => 20,
            'page' => 2,
        ]))
        ->assertOk()
        ->assertViewHas(
            'utilisateurs',
            fn (LengthAwarePaginator $utilisateurs): bool => $utilisateurs->currentPage() === 2
                && $utilisateurs->count() === 1
        );
});

it('crée un formateur avec un mot de passe robuste et une adhésion administrée', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('creation-formateur');
    $donnees = donneesFormateurPourGestionUtilisateursRefondue('creation');

    $response = $this->actingAs($admin)
        ->post(route('admin.utilisateurs.store'), $donnees);

    $formateur = User::query()->where('email', $donnees['email'])->firstOrFail();

    $response
        ->assertRedirect(route('admin.utilisateurs.edit', $formateur))
        ->assertSessionHas('success');

    expect($formateur->role)->toBe('formateur')
        ->and($formateur->status)->toBeTrue()
        ->and($formateur->societe)->toBe('Formation Exemple')
        ->and($formateur->adhesion_status)->toBe('active')
        ->and($formateur->adhesion_valid_until?->toDateString())->toBe($donnees['adhesion_valid_until'])
        ->and($formateur->adhesion_verified_by)->toBe($admin->id)
        ->and(Hash::check($donnees['password'], $formateur->password))->toBeTrue();
});

it('exige un mot de passe confirmé d’au moins douze caractères à la création', function (string $password, string $confirmation) {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue(md5($password.$confirmation));
    $donnees = donneesFormateurPourGestionUtilisateursRefondue(md5($confirmation.$password));
    $donnees['password'] = $password;
    $donnees['password_confirmation'] = $confirmation;

    $this->actingAs($admin)
        ->post(route('admin.utilisateurs.store'), $donnees)
        ->assertSessionHasErrors('password');

    $this->assertDatabaseMissing('users', ['email' => $donnees['email']]);
})->with('mots_de_passe_invalides_creation_utilisateur_admin');

it('refuse la création de tout rôle autre que formateur ou stagiaire', function (string $role) {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('role-'.$role);
    $donnees = donneesFormateurPourGestionUtilisateursRefondue('role-'.$role);
    $donnees['role'] = $role;
    unset($donnees['adhesion_status'], $donnees['adhesion_valid_until']);

    $this->actingAs($admin)
        ->post(route('admin.utilisateurs.store'), $donnees)
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['email' => $donnees['email']]);
})->with('roles_non_geres_creation_utilisateur_admin');

it('crée un stagiaire avec son formateur ses groupes et un code d’accès généré', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('creation-stagiaire');
    $formateur = User::factory()->create([
        'role' => 'formateur',
        'status' => true,
    ]);
    $groupeA = creerGroupePourGestionUtilisateursRefondue($formateur, 'A');
    $groupeB = creerGroupePourGestionUtilisateursRefondue($formateur, 'B');

    $response = $this->actingAs($admin)->post(route('admin.utilisateurs.store'), [
        'prenom' => 'Camille',
        'name' => 'Martin',
        'username' => 'camille_stagiaire_admin',
        'email' => 'camille-stagiaire-admin@example.test',
        'password' => 'MotDePasse!123',
        'password_confirmation' => 'MotDePasse!123',
        'role' => 'stagiaire',
        'status' => '1',
        'formateur_id' => $formateur->id,
        'group_ids' => [$groupeA->id, $groupeB->id],
        'code_acces' => '',
    ]);

    $stagiaire = User::query()->where('email', 'camille-stagiaire-admin@example.test')->firstOrFail();

    $response->assertRedirect(route('admin.utilisateurs.edit', $stagiaire));

    expect($stagiaire->role)->toBe('stagiaire')
        ->and($stagiaire->formateur_id)->toBe($formateur->id)
        ->and($stagiaire->code_acces)->toMatch('/^[A-Z0-9]{6}$/')
        ->and($stagiaire->password_changed_at)->toBeNull();

    foreach ([$groupeA, $groupeB] as $groupe) {
        $this->assertDatabaseHas('group_user', [
            'group_id' => $groupe->id,
            'user_id' => $stagiaire->id,
            'role_in_group' => 'stagiaire',
        ]);
    }
});

it('modifie les rattachements d’un stagiaire sans permettre de changer son rôle', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('modification');
    $formateurInitial = User::factory()->create(['role' => 'formateur', 'status' => true]);
    $nouveauFormateur = User::factory()->create(['role' => 'formateur', 'status' => true]);
    $ancienGroupe = creerGroupePourGestionUtilisateursRefondue($formateurInitial, 'ancien');
    $nouveauGroupe = creerGroupePourGestionUtilisateursRefondue($nouveauFormateur, 'nouveau');
    $stagiaire = User::factory()->create([
        'prenom' => 'Avant',
        'name' => 'Modification',
        'email' => 'stagiaire-modification-admin@example.test',
        'role' => 'stagiaire',
        'status' => true,
        'formateur_id' => $formateurInitial->id,
        'code_acces' => 'ABC123',
    ]);
    $stagiaire->groupesStagiaire()->attach($ancienGroupe->id, ['role_in_group' => 'stagiaire']);

    $this->actingAs($admin)
        ->put(route('admin.utilisateurs.update', $stagiaire), [
            'prenom' => 'Après',
            'name' => 'Modification',
            'username' => 'stagiaire_modifie_admin',
            'email' => 'stagiaire-modification-admin@example.test',
            'phone' => '0600000000',
            'address' => 'Nouvelle adresse',
            'role' => 'formateur',
            'status' => '1',
            'adhesion_status' => 'active',
            'societe' => 'Tentative non autorisée',
            'formateur_id' => $nouveauFormateur->id,
            'group_ids' => [$nouveauGroupe->id],
            'code_acces' => 'ABC123',
        ])
        ->assertRedirect(route('admin.utilisateurs.edit', $stagiaire))
        ->assertSessionHas('success');

    $stagiaire->refresh();

    expect($stagiaire->prenom)->toBe('Après')
        ->and($stagiaire->role)->toBe('stagiaire')
        ->and($stagiaire->formateur_id)->toBe($nouveauFormateur->id)
        ->and($stagiaire->societe)->toBeNull()
        ->and($stagiaire->code_acces)->toBe('ABC123');

    $this->assertDatabaseMissing('group_user', [
        'group_id' => $ancienGroupe->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
    $this->assertDatabaseHas('group_user', [
        'group_id' => $nouveauGroupe->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
});

it('conserve le rôle formateur malgré une tentative de changement vers stagiaire', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('role-formateur');
    $autreFormateur = User::factory()->create(['role' => 'formateur', 'status' => true]);
    $groupe = creerGroupePourGestionUtilisateursRefondue($autreFormateur, 'role-formateur');
    $formateur = User::factory()->create([
        'prenom' => 'Avant',
        'name' => 'Formateur',
        'email' => 'formateur-role-immuable@example.test',
        'role' => 'formateur',
        'status' => true,
        'societe' => 'Société initiale',
        'adhesion_status' => 'active',
        'adhesion_valid_until' => now()->addMonth()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->put(route('admin.utilisateurs.update', $formateur), [
            'prenom' => 'Après',
            'name' => 'Formateur',
            'username' => 'formateur_role_immuable',
            'email' => 'formateur-role-immuable@example.test',
            'role' => 'stagiaire',
            'status' => '1',
            'societe' => 'Société mise à jour',
            'adhesion_status' => 'active',
            'adhesion_valid_until' => now()->addYear()->toDateString(),
            'formateur_id' => $autreFormateur->id,
            'group_ids' => [$groupe->id],
            'code_acces' => 'XYZ789',
        ])
        ->assertRedirect(route('admin.utilisateurs.edit', $formateur))
        ->assertSessionHas('success');

    $formateur->refresh();

    expect($formateur->prenom)->toBe('Après')
        ->and($formateur->role)->toBe('formateur')
        ->and($formateur->societe)->toBe('Société mise à jour')
        ->and($formateur->formateur_id)->toBeNull()
        ->and($formateur->code_acces)->toBeNull();

    $this->assertDatabaseMissing('group_user', [
        'group_id' => $groupe->id,
        'user_id' => $formateur->id,
    ]);
});

it('met à jour le statut des formateurs et stagiaires', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('statuts');
    $formateur = User::factory()->create(['role' => 'formateur', 'status' => true]);
    $stagiaire = User::factory()->create(['role' => 'stagiaire', 'status' => false]);

    $this->actingAs($admin)
        ->patch(route('admin.utilisateurs.statut.update', $formateur), ['status' => '0'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($formateur->refresh()->status)->toBeFalse();

    $this->actingAs($admin)
        ->patchJson(route('admin.utilisateurs.statut.update', $stagiaire), ['status' => true])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'status' => true,
        ]);

    expect($stagiaire->refresh()->status)->toBeTrue();
});

it('répond 404 quand la mise à jour de statut cible un administrateur', function () {
    $admin = creerAdministrateurPourGestionUtilisateursRefondue('statut-admin');

    $this->actingAs($admin)
        ->patch(route('admin.utilisateurs.statut.update', $admin), ['status' => '0'])
        ->assertNotFound();

    expect($admin->refresh()->status)->toBeTrue();
    expect(ActivityJournalEntry::query()
        ->where('route_name', 'admin.utilisateurs.statut.update')
        ->count())->toBe(0);
});
