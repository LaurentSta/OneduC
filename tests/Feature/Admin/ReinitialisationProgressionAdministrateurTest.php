<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

function creerUtilisateurPourReinitialisationProgressionAdmin(string $role, string $suffixe, array $attributs = []): User
{
    return User::factory()->create(array_merge([
        'prenom' => ucfirst($role),
        'name' => 'Reset '.$suffixe,
        'username' => $role.'_reset_admin_'.$suffixe,
        'email' => $role.'-reset-admin-'.$suffixe.'@example.test',
        'role' => $role,
        'status' => true,
    ], $attributs));
}

function creerContextePourReinitialisationProgressionAdmin(User $formateur): array
{
    $categorieId = DB::table('categories')->insertGetId([
        'category_name' => 'Categorie reset admin',
        'category_slug' => 'categorie-reset-admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $sousCategorieId = DB::table('subcategories')->insertGetId([
        'category_id' => $categorieId,
        'subcategory_name' => 'Sous categorie reset admin',
        'subcategory_slug' => 'sous-categorie-reset-admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $moduleId = DB::table('modules')->insertGetId([
        'category_id' => $categorieId,
        'subcategory_id' => $sousCategorieId,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module reset admin',
        'module_name' => 'Module reset admin',
        'module_name_slug' => 'module-reset-admin',
        'status' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $sectionId = DB::table('module_sections')->insertGetId([
        'module_id' => $moduleId,
        'section_title' => 'Section reset admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $lectureId = DB::table('module_lectures')->insertGetId([
        'module_id' => $moduleId,
        'section_id' => $sectionId,
        'position' => 1,
        'lecture_title' => 'Lecon reset admin',
        'content_type' => 'scorm',
        'slides_status' => 'none',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $evaluationId = DB::table('evaluations')->insertGetId([
        'titre' => 'Evaluation reset admin',
        'scorm_path' => 'eval/reset-admin.zip',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $questionId = DB::table('quiz_questions')->insertGetId([
        'lecture_id' => $lectureId,
        'type' => 'single',
        'question_text' => 'Question reset admin',
        'created_by' => $formateur->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $optionId = DB::table('quiz_options')->insertGetId([
        'question_id' => $questionId,
        'option_text' => 'Réponse reset admin',
        'is_correct' => true,
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'module_id' => $moduleId,
        'lecture_id' => $lectureId,
        'evaluation_id' => $evaluationId,
        'question_id' => $questionId,
        'option_id' => $optionId,
    ];
}

function insererProgressionBlocScormPourResetAdmin(User $stagiaire, int $lectureId, string $cle): void
{
    DB::table('content_block_scorm_results')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lectureId,
        'content_block_key' => $cle,
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('content_block_scorm_scores')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lectureId,
        'content_block_key' => $cle,
        'lesson_status' => 'completed',
        'first_score' => 80,
        'best_score' => 90,
        'last_score' => 90,
        'attempts_count' => 2,
        'is_completed' => true,
        'session_time' => 120,
        'last_session_time' => 60,
        'last_attempt_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function insererProgressionCompletePourResetAdmin(
    User $stagiaire,
    User $formateur,
    array $contexte,
    string $suffixe,
): int {
    DB::table('progressions')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $contexte['lecture_id'],
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_results')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $contexte['lecture_id'],
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_scores')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $contexte['lecture_id'],
        'first_score' => 70,
        'best_score' => 90,
        'last_score' => 90,
        'attempts_count' => 2,
        'is_completed' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_interactions')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $contexte['lecture_id'],
        'interaction_id' => 'interaction-'.$suffixe,
        'interaction_type' => 'choice',
        'result' => 'correct',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tentativeQuizId = DB::table('quiz_attempts')->insertGetId([
        'user_id' => $stagiaire->id,
        'lecture_id' => $contexte['lecture_id'],
        'started_at' => now(),
        'finished_at' => now(),
        'total_questions' => 1,
        'score' => 1,
        'percent' => 100,
        'passed' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('quiz_attempt_questions')->insert([
        'attempt_id' => $tentativeQuizId,
        'question_id' => $contexte['question_id'],
        'position' => 1,
        'answer_option_ids' => json_encode([$contexte['option_id']], JSON_THROW_ON_ERROR),
        'given_answer' => json_encode([$contexte['option_id']], JSON_THROW_ON_ERROR),
        'is_correct' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_evaluation_results')->insert([
        'user_id' => $stagiaire->id,
        'evaluation_id' => $contexte['evaluation_id'],
        'scorm_key' => 'cmi.core.score.raw',
        'scorm_value' => '95',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_evaluation_scores')->insert([
        'user_id' => $stagiaire->id,
        'evaluation_id' => $contexte['evaluation_id'],
        'first_score' => 95,
        'last_score' => 95,
        'best_score' => 95,
        'attempts_count' => 1,
        'questions_answered' => 10,
        'session_time' => 60,
        'is_completed' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_evaluation_interactions')->insert([
        'user_id' => $stagiaire->id,
        'evaluation_id' => $contexte['evaluation_id'],
        'interaction_id' => 'evaluation-'.$suffixe,
        'interaction_type' => 'choice',
        'result' => 'correct',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    insererProgressionBlocScormPourResetAdmin(
        $stagiaire,
        $contexte['lecture_id'],
        'bloc-'.$suffixe,
    );

    DB::table('video_segment_trackings')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $contexte['lecture_id'],
        'segment_start' => 0,
        'segment_end' => 30,
        'watch_count' => 2,
        'total_watch_time' => 60,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('module_completion_notifications')->insert([
        'module_id' => $contexte['module_id'],
        'stagiaire_id' => $stagiaire->id,
        'recipient_id' => $formateur->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $tentativeQuizId;
}

it('refuse de réinitialiser la progression d’un compte non stagiaire', function () {
    $admin = creerUtilisateurPourReinitialisationProgressionAdmin('admin', 'refus');
    $formateur = creerUtilisateurPourReinitialisationProgressionAdmin('formateur', 'cible', [
        'total_site_time' => 42,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.stagiaires.reset', $formateur))
        ->assertRedirect()
        ->assertSessionHas('error', 'Seul un compte stagiaire peut être réinitialisé.');

    expect($formateur->refresh()->total_site_time)->toBe(42);
});

it('purge toutes les familles de progression du seul stagiaire ciblé', function () {
    $admin = creerUtilisateurPourReinitialisationProgressionAdmin('admin', 'purge');
    $formateur = creerUtilisateurPourReinitialisationProgressionAdmin('formateur', 'module');
    $stagiaire = creerUtilisateurPourReinitialisationProgressionAdmin('stagiaire', 'cible', [
        'total_site_time' => 180,
    ]);
    $autreStagiaire = creerUtilisateurPourReinitialisationProgressionAdmin('stagiaire', 'conserve', [
        'total_site_time' => 90,
    ]);
    $contexte = creerContextePourReinitialisationProgressionAdmin($formateur);
    $tentativeCibleId = insererProgressionCompletePourResetAdmin(
        $stagiaire,
        $formateur,
        $contexte,
        'cible',
    );
    $tentativeConserveeId = insererProgressionCompletePourResetAdmin(
        $autreStagiaire,
        $formateur,
        $contexte,
        'conserve',
    );

    $this->actingAs($admin)
        ->post(route('admin.stagiaires.reset', $stagiaire))
        ->assertRedirect()
        ->assertSessionHas('success');

    $tablesParUtilisateur = [
        'progressions',
        'scorm_results',
        'scorm_scores',
        'scorm_interactions',
        'quiz_attempts',
        'scorm_evaluation_results',
        'scorm_evaluation_scores',
        'scorm_evaluation_interactions',
        'content_block_scorm_results',
        'content_block_scorm_scores',
        'video_segment_trackings',
    ];

    foreach ($tablesParUtilisateur as $table) {
        $this->assertDatabaseMissing($table, ['user_id' => $stagiaire->id]);
        $this->assertDatabaseHas($table, ['user_id' => $autreStagiaire->id]);
    }

    $this->assertDatabaseMissing('quiz_attempt_questions', ['attempt_id' => $tentativeCibleId]);
    $this->assertDatabaseHas('quiz_attempt_questions', ['attempt_id' => $tentativeConserveeId]);
    $this->assertDatabaseMissing('module_completion_notifications', ['stagiaire_id' => $stagiaire->id]);
    $this->assertDatabaseHas('module_completion_notifications', ['stagiaire_id' => $autreStagiaire->id]);

    expect($stagiaire->refresh()->total_site_time)->toBe(0)
        ->and($autreStagiaire->refresh()->total_site_time)->toBe(90);
});
