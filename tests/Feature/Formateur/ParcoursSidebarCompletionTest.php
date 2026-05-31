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
    $now = now();

    DB::table('trainer_path_activity_attempts')->insert([
        'user_id' => $formateur->id,
        'module_key' => 'organiser-ses-parcours',
        'chapter_key' => 'mettre-en-place-un-parcours-coherent',
        'lesson_key' => $lesson,
        'activity_key' => $activity,
        'activity_type' => 'guided_group_creation',
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
