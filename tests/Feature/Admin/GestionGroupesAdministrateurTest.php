<?php

use App\Http\Controllers\Backend\GroupeController;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

function creerUtilisateurPourGestionGroupesAdministrateur(string $role, string $suffixe): User
{
    return User::factory()->create([
        'prenom' => ucfirst($role),
        'name' => 'Groupes '.$suffixe,
        'username' => $role.'_groupes_admin_'.$suffixe,
        'email' => $role.'-groupes-admin-'.$suffixe.'@example.test',
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function creerGroupePourGestionGroupesAdministrateur(User $formateur, string $suffixe): Group
{
    return Group::query()->create([
        'name' => 'Groupe administration '.$suffixe,
        'description' => 'Groupe préparé pour les tests administrateur',
        'is_active' => true,
        'instructor_id' => $formateur->id,
    ]);
}

function rattacherUtilisateurPourGestionGroupesAdministrateur(Group $groupe, User $utilisateur, string $roleDansGroupe): void
{
    DB::table('group_user')->insert([
        'group_id' => $groupe->id,
        'user_id' => $utilisateur->id,
        'role_in_group' => $roleDansGroupe,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('affiche les écrans de liste création et édition des groupes', function () {
    $admin = creerUtilisateurPourGestionGroupesAdministrateur('admin', 'ecrans');
    $formateur = creerUtilisateurPourGestionGroupesAdministrateur('formateur', 'ecrans');
    $stagiaire = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'ecrans');
    $groupe = creerGroupePourGestionGroupesAdministrateur($formateur, 'ecrans');
    rattacherUtilisateurPourGestionGroupesAdministrateur($groupe, $stagiaire, 'stagiaire');

    $this->actingAs($admin)
        ->get(route('admin.groupes'))
        ->assertOk()
        ->assertViewIs('admin.backend.groupes.groupes')
        ->assertSee($groupe->name);

    $this->actingAs($admin)
        ->get(route('admin.groupes.add'))
        ->assertOk()
        ->assertViewIs('admin.backend.groupes.add_groupe')
        ->assertViewHas('formateurs', fn ($formateurs): bool => $formateurs->contains('id', $formateur->id))
        ->assertViewHas('stagiaires', fn ($stagiaires): bool => $stagiaires->contains('id', $stagiaire->id))
        ->assertSee($stagiaire->email);

    $this->actingAs($admin)
        ->get(route('admin.groupes.edit', $groupe))
        ->assertOk()
        ->assertViewIs('admin.backend.groupes.edit_groupe')
        ->assertViewHas('groupe', fn (Group $groupeAffiche): bool => $groupeAffiche->is($groupe));
});

it('crée un groupe avec son formateur et ses stagiaires', function () {
    $admin = creerUtilisateurPourGestionGroupesAdministrateur('admin', 'creation');
    $formateur = creerUtilisateurPourGestionGroupesAdministrateur('formateur', 'creation');
    $stagiaireA = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'creation-a');
    $stagiaireB = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'creation-b');

    $this->actingAs($admin)
        ->post(route('admin.groupes.store'), [
            'name' => 'Groupe créé par administration',
            'description' => 'Effectif initial administré',
            'formateur_id' => $formateur->id,
            'stagiaires' => [$stagiaireA->id, $stagiaireB->id],
        ])
        ->assertRedirect(route('admin.groupes'))
        ->assertSessionHas('success');

    $groupe = Group::query()->where('name', 'Groupe créé par administration')->firstOrFail();

    expect($groupe->instructor_id)->toBe($formateur->id)
        ->and($groupe->description)->toBe('Effectif initial administré');

    foreach ([$stagiaireA, $stagiaireB] as $stagiaire) {
        $this->assertDatabaseHas('group_user', [
            'group_id' => $groupe->id,
            'user_id' => $stagiaire->id,
            'role_in_group' => 'stagiaire',
        ]);
    }
});

it('rejette un stagiaire comme responsable et un formateur dans la sélection des stagiaires', function () {
    $admin = creerUtilisateurPourGestionGroupesAdministrateur('admin', 'validation');
    $formateur = creerUtilisateurPourGestionGroupesAdministrateur('formateur', 'validation');
    $stagiaire = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'validation');

    $this->actingAs($admin)
        ->post(route('admin.groupes.store'), [
            'name' => 'Groupe responsable invalide',
            'formateur_id' => $stagiaire->id,
            'stagiaires' => [],
        ])
        ->assertSessionHasErrors('formateur_id');

    $this->assertDatabaseMissing('groups', ['name' => 'Groupe responsable invalide']);

    $this->actingAs($admin)
        ->post(route('admin.groupes.store'), [
            'name' => 'Groupe stagiaire invalide',
            'formateur_id' => $formateur->id,
            'stagiaires' => [$formateur->id],
        ])
        ->assertSessionHasErrors('stagiaires.0');

    $this->assertDatabaseMissing('groups', ['name' => 'Groupe stagiaire invalide']);
});

it('met à jour les stagiaires sans supprimer les pivots observateur et co-formateur', function () {
    $admin = creerUtilisateurPourGestionGroupesAdministrateur('admin', 'mise-a-jour');
    $responsable = creerUtilisateurPourGestionGroupesAdministrateur('formateur', 'responsable');
    $coFormateur = creerUtilisateurPourGestionGroupesAdministrateur('formateur', 'co-formateur');
    $observateur = creerUtilisateurPourGestionGroupesAdministrateur('observateur', 'observateur');
    $ancienStagiaire = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'ancien');
    $nouveauStagiaire = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'nouveau');
    $groupe = creerGroupePourGestionGroupesAdministrateur($responsable, 'mise-a-jour');

    rattacherUtilisateurPourGestionGroupesAdministrateur($groupe, $ancienStagiaire, 'stagiaire');
    rattacherUtilisateurPourGestionGroupesAdministrateur($groupe, $coFormateur, 'formateur');
    rattacherUtilisateurPourGestionGroupesAdministrateur($groupe, $observateur, 'observateur');

    $this->actingAs($admin)
        ->put(route('admin.groupes.update', $groupe), [
            'name' => 'Groupe administration mis à jour',
            'description' => 'Nouvelle composition stagiaire',
            'formateur_id' => $responsable->id,
            'stagiaires' => [$nouveauStagiaire->id],
        ])
        ->assertRedirect(route('admin.groupes'))
        ->assertSessionHas('success');

    expect($groupe->refresh()->name)->toBe('Groupe administration mis à jour');

    $this->assertDatabaseMissing('group_user', [
        'group_id' => $groupe->id,
        'user_id' => $ancienStagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
    $this->assertDatabaseHas('group_user', [
        'group_id' => $groupe->id,
        'user_id' => $nouveauStagiaire->id,
        'role_in_group' => 'stagiaire',
    ]);
    $this->assertDatabaseHas('group_user', [
        'group_id' => $groupe->id,
        'user_id' => $coFormateur->id,
        'role_in_group' => 'formateur',
    ]);
    $this->assertDatabaseHas('group_user', [
        'group_id' => $groupe->id,
        'user_id' => $observateur->id,
        'role_in_group' => 'observateur',
    ]);
});

it('relie la route de suppression à destroy et supprime le groupe', function () {
    $admin = creerUtilisateurPourGestionGroupesAdministrateur('admin', 'suppression');
    $formateur = creerUtilisateurPourGestionGroupesAdministrateur('formateur', 'suppression');
    $stagiaire = creerUtilisateurPourGestionGroupesAdministrateur('stagiaire', 'suppression');
    $groupe = creerGroupePourGestionGroupesAdministrateur($formateur, 'suppression');
    rattacherUtilisateurPourGestionGroupesAdministrateur($groupe, $stagiaire, 'stagiaire');
    $routeSuppression = Route::getRoutes()->getByName('admin.groupes.delete');

    expect($routeSuppression)->not->toBeNull()
        ->and($routeSuppression->methods())->toContain('DELETE')
        ->and($routeSuppression->getActionName())->toBe(GroupeController::class.'@destroy');

    $this->actingAs($admin)
        ->delete(route('admin.groupes.delete', $groupe))
        ->assertRedirect(route('admin.groupes'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('groups', ['id' => $groupe->id]);
    $this->assertDatabaseMissing('group_user', ['group_id' => $groupe->id]);
});
