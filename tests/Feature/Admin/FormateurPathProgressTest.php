<?php

use App\Data\ParcoursFormateur;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function createUserForAdminPathProgress(string $role, string $email): User
{
    return User::query()->create([
        'prenom' => 'Test',
        'name' => ucfirst($role),
        'username' => str_replace(['@', '.'], '_', $email),
        'email' => $email,
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
    ]);
}

function recordAdminPathProgress(User $formateur, array $requirements): void
{
    $now = now();

    foreach ($requirements as $statusKey => $activityType) {
        [$chapterKey, $lessonKey, $activityKey] = explode('.', $statusKey, 3);

        DB::table('trainer_path_activity_attempts')->insert([
            'user_id' => $formateur->id,
            'module_key' => 'organiser-ses-parcours',
            'chapter_key' => $chapterKey,
            'lesson_key' => $lessonKey,
            'activity_key' => $activityKey,
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
}

it('shows trainer path completion and questionnaire receipt on the admin trainer list', function () {
    $admin = createUserForAdminPathProgress('admin', 'admin-path-progress@example.test');
    $completedTrainer = createUserForAdminPathProgress('formateur', 'completed-path@example.test');
    $inProgressTrainer = createUserForAdminPathProgress('formateur', 'in-progress-path@example.test');
    $moduleTwoRequirements = ParcoursFormateur::moduleCompletionRequirements('organiser-ses-parcours');

    recordAdminPathProgress($completedTrainer, $moduleTwoRequirements);
    recordAdminPathProgress($inProgressTrainer, array_slice($moduleTwoRequirements, 0, 1, true));

    DB::table('trainer_module_questionnaire_submissions')->insert([
        'submission_uuid' => (string) Str::uuid(),
        'user_id' => $completedTrainer->id,
        'module_number' => 2,
        'module_key' => 'organiser-ses-parcours',
        'questionnaire_key' => 'utilisabilite-percue',
        'questionnaire_version' => 1,
        'responses' => json_encode([]),
        'submitted_at' => now(),
        'emailed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.formateurs'));

    $response->assertOk();
    $response->assertSee('Parcours formateur');
    $response->assertSee(
        'data-trainer-path-module-status="'.$completedTrainer->id.':2:completed"',
        false
    );
    $response->assertSee(
        'data-trainer-path-module-status="'.$inProgressTrainer->id.':2:in_progress"',
        false
    );
    $response->assertSee(
        'data-trainer-path-module-status="'.$completedTrainer->id.':1:coming_soon"',
        false
    );
    $response->assertSee('Questionnaire M2 reçu');
});
