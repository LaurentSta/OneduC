<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function createUserForStagiairePurgeTest(string $role, string $email, array $extra = []): User
{
    return User::query()->create(array_merge([
        'prenom' => 'Test',
        'name' => 'User',
        'username' => str_replace(['@', '.'], '_', $email),
        'email' => $email,
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
    ], $extra));
}

function createLearningContextForStagiairePurgeTest(User $formateur): array
{
    $categoryId = DB::table('categories')->insertGetId([
        'category_name' => 'Categorie test',
        'category_slug' => 'categorie-test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $subCategoryId = DB::table('subcategories')->insertGetId([
        'category_id' => $categoryId,
        'subcategory_name' => 'Sous-categorie test',
        'subcategory_slug' => 'sous-categorie-test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $moduleId = DB::table('modules')->insertGetId([
        'category_id' => $categoryId,
        'subcategory_id' => $subCategoryId,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module test',
        'module_name' => 'Module test',
        'module_name_slug' => 'module-test',
        'status' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sectionId = DB::table('module_sections')->insertGetId([
        'module_id' => $moduleId,
        'section_title' => 'Section test',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $lectureId = DB::table('module_lectures')->insertGetId([
        'module_id' => $moduleId,
        'section_id' => $sectionId,
        'position' => 1,
        'lecture_title' => 'Lecon test',
        'content_type' => 'scorm',
        'slides_status' => 'none',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $evaluationId = DB::table('evaluations')->insertGetId([
        'titre' => 'Evaluation test',
        'scorm_path' => 'eval/test.zip',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('groups')->insertGetId([
        'name' => 'Groupe purge stagiaire',
        'description' => 'Groupe test',
        'instructor_id' => $formateur->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $wordCloudId = DB::table('word_clouds')->insertGetId([
        'module_id' => $moduleId,
        'title' => 'Nuage de mots test',
        'question' => 'Question test ?',
        'access_code' => strtoupper(Str::random(12)),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'module_id' => $moduleId,
        'lecture_id' => $lectureId,
        'evaluation_id' => $evaluationId,
        'group_id' => $groupId,
        'word_cloud_id' => $wordCloudId,
    ];
}

it('purges stagiaire related learning and result data when deleted', function () {
    $admin = createUserForStagiairePurgeTest('admin', 'admin-stagiaire-purge@example.test');
    $formateur = createUserForStagiairePurgeTest('formateur', 'formateur-stagiaire-purge@example.test');
    $stagiaire = createUserForStagiairePurgeTest('stagiaire', 'stagiaire-purge@example.test', [
        'formateur_id' => $formateur->id,
    ]);

    $context = createLearningContextForStagiairePurgeTest($formateur);

    DB::table('group_user')->insert([
        'group_id' => $context['group_id'],
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('progressions')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $context['lecture_id'],
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_results')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $context['lecture_id'],
        'scorm_key' => 'cmi.core.lesson_status',
        'scorm_value' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_scores')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $context['lecture_id'],
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
        'lecture_id' => $context['lecture_id'],
        'interaction_id' => 'q1',
        'interaction_type' => 'choice',
        'result' => 'correct',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $questionId = DB::table('quiz_questions')->insertGetId([
        'lecture_id' => $context['lecture_id'],
        'type' => 'single',
        'question_text' => 'Question test',
        'created_by' => $formateur->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $optionId = DB::table('quiz_options')->insertGetId([
        'question_id' => $questionId,
        'option_text' => 'Reponse test',
        'is_correct' => true,
        'position' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $attemptId = DB::table('quiz_attempts')->insertGetId([
        'user_id' => $stagiaire->id,
        'lecture_id' => $context['lecture_id'],
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
        'attempt_id' => $attemptId,
        'question_id' => $questionId,
        'position' => 1,
        'answer_option_ids' => json_encode([$optionId], JSON_THROW_ON_ERROR),
        'given_answer' => json_encode([$optionId], JSON_THROW_ON_ERROR),
        'is_correct' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_evaluation_results')->insert([
        'user_id' => $stagiaire->id,
        'evaluation_id' => $context['evaluation_id'],
        'scorm_key' => 'cmi.core.score.raw',
        'scorm_value' => '95',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('scorm_evaluation_scores')->insert([
        'user_id' => $stagiaire->id,
        'evaluation_id' => $context['evaluation_id'],
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
        'evaluation_id' => $context['evaluation_id'],
        'interaction_id' => 'e1',
        'interaction_type' => 'choice',
        'result' => 'correct',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('lesson_feedbacks')->insert([
        'user_id' => $stagiaire->id,
        'lesson_id' => $context['lecture_id'],
        'comment' => 'Super lecon',
        'rating' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('video_segment_trackings')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $context['lecture_id'],
        'segment_start' => 0,
        'segment_end' => 30,
        'watch_count' => 2,
        'total_watch_time' => 60,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('learning_objectives')->insert([
        'module_id' => $context['module_id'],
        'user_id' => $stagiaire->id,
        'progress' => 70,
        'started_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('module_completion_notifications')->insert([
        'module_id' => $context['module_id'],
        'stagiaire_id' => $stagiaire->id,
        'recipient_id' => $formateur->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('word_cloud_entries')->insert([
        'word_cloud_id' => $context['word_cloud_id'],
        'user_id' => $stagiaire->id,
        'answer' => 'mot',
        'normalized_answer' => 'mot',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sessions')->insert([
        'id' => Str::random(40),
        'user_id' => $stagiaire->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'payload' => base64_encode('payload'),
        'last_activity' => now()->timestamp,
    ]);

    DB::table('notifications')->insert([
        'id' => (string) Str::uuid(),
        'type' => 'Tests\\Notification',
        'notifiable_type' => User::class,
        'notifiable_id' => $stagiaire->id,
        'data' => json_encode(['msg' => 'test'], JSON_THROW_ON_ERROR),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.stagiaires.destroy', $stagiaire));

    $response->assertStatus(302);
    $this->assertSoftDeleted('users', ['id' => $stagiaire->id]);

    $this->assertDatabaseMissing('group_user', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('progressions', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('scorm_results', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('scorm_scores', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('scorm_interactions', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('quiz_attempts', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('quiz_attempt_questions', ['attempt_id' => $attemptId]);
    $this->assertDatabaseMissing('scorm_evaluation_results', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('scorm_evaluation_scores', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('scorm_evaluation_interactions', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('lesson_feedbacks', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('video_segment_trackings', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('learning_objectives', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('module_completion_notifications', ['stagiaire_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('word_cloud_entries', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('sessions', ['user_id' => $stagiaire->id]);
    $this->assertDatabaseMissing('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $stagiaire->id,
    ]);
});
