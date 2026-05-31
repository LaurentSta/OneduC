<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createSidebarProgressFormateur(): User
{
    return User::query()->create([
        'prenom' => 'Lina',
        'name' => 'Formatrice',
        'username' => 'lina.formatrice',
        'email' => 'lina.formatrice@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);
}

function recordSidebarProgress(User $formateur, string $lesson, string $activity): void
{
    recordModuleTwoProgress(
        $formateur,
        'mettre-en-place-un-parcours-coherent',
        $lesson,
        $activity
    );
}

function recordModuleTwoProgress(
    User $formateur,
    string $chapter,
    string $lesson,
    string $activity,
    string $activityType = 'guided_group_creation'
): void
{
    $now = now();

    DB::table('trainer_path_activity_attempts')->insert([
        'user_id' => $formateur->id,
        'module_key' => 'organiser-ses-parcours',
        'chapter_key' => $chapter,
        'lesson_key' => $lesson,
        'activity_key' => $activity,
        'activity_type' => $activityType,
        'total_items' => 1,
        'correct_items' => 1,
        'is_success' => true,
        'submitted_answer' => json_encode([]),
        'expected_answer' => json_encode([]),
        'wrong_items' => json_encode([]),
        'submitted_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

function sidebarLessonPartRoute(string $lesson, string $part): string
{
    return route('formateur.parcours.lessons.part', [
        'module' => 'organiser-ses-parcours',
        'chapter' => 'mettre-en-place-un-parcours-coherent',
        'lesson' => $lesson,
        'part' => $part,
    ]);
}

it('keeps the final chapter in progress until cases and bilan are completed', function () {
    $formateur = createSidebarProgressFormateur();

    recordSidebarProgress($formateur, 'associer-le-bon-parcours-au-bon-contexte', 'ajustement-groupe-finalise');

    $marcValidation = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('traiter-les-cas-particuliers', 'validation'));

    $marcValidation->assertOk();
    $marcValidation->assertSee('1/3 étapes validées');

    $casesFinalisation = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('traiter-les-cas-particuliers', 'modifier-contenu-finalisation'));

    $casesFinalisation->assertOk();
    $casesFinalisation->assertSee('2/3 étapes validées');

    $bilanFinal = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'resultat-final'));

    $bilanFinal->assertOk();
    $bilanFinal->assertSee('3/3 étapes validées');
    $bilanFinal->assertSee('Chapitre validé');
});

it('adds a student inside the group adjustment simulator without writing to the database', function () {
    $formateur = createSidebarProgressFormateur();
    $finalisationUrl = sidebarLessonPartRoute('associer-le-bon-parcours-au-bon-contexte', 'ajustement-groupe-finalisation');

    $form = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('associer-le-bon-parcours-au-bon-contexte', 'ajouter-stagiaire'));

    $form->assertOk();
    $form->assertSee('action="' . $finalisationUrl . '" method="GET"', false);
    $form->assertDontSee(route('formateur.stagiaires.store'), false);

    $finalisation = $this
        ->actingAs($formateur)
        ->get($finalisationUrl . '?' . http_build_query([
            'prenom' => 'Sophie',
            'name' => 'Durand',
            'email' => 'sophie.durand@example.test',
            'group_id' => 1,
        ]));

    $finalisation->assertOk();
    $finalisation->assertSee('Sophie');
    $finalisation->assertSee('Durand');
    $finalisation->assertSee('sophie.durand@example.test');
    $finalisation->assertSee('Hygiène alimentaire 2026 - promo 1');
    $this->assertDatabaseMissing('users', ['email' => 'sophie.durand@example.test']);
});

it('shows a green overview border once every module step is completed', function () {
    $formateur = createSidebarProgressFormateur();

    recordModuleTwoProgress($formateur, 'preparer-les-contenus', 'retrouver-les-espaces-de-preparation', 'classer-les-elements', 'sorting');
    recordModuleTwoProgress($formateur, 'preparer-les-contenus', 'distinguer-contenu-ressource-et-structure', 'preparer-informations-utiles', 'essential_sorting');
    recordModuleTwoProgress($formateur, 'structurer-la-progression', 'creation-groupe-de-formation', 'creation-groupe-finalisee');
    recordModuleTwoProgress($formateur, 'structurer-la-progression', 'creation-parcours', 'creation-parcours-finalisee');
    recordSidebarProgress($formateur, 'associer-le-bon-parcours-au-bon-contexte', 'ajustement-groupe-finalise');
    recordSidebarProgress($formateur, 'traiter-les-cas-particuliers', 'cas-particuliers-finalises');

    $beforeBilan = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'bilan'));

    $beforeBilan->assertOk();
    $beforeBilan->assertDontSee('border-t-0 border-vertone bg-white', false);

    $completedModule = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'resultat-final'));

    $completedModule->assertOk();
    $completedModule->assertSee('border-vertone bg-teal-50/70', false);
    $completedModule->assertSee('border-t-0 border-vertone bg-white', false);
    $completedModule->assertSee('Validé');
});
