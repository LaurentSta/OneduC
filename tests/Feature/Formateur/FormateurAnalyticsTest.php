<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizAttempt;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createFormateurAnalyticsUser(string $role, array $attributes = []): User
{
    return User::query()->create(array_merge([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role) . ' Analytics',
        'username' => $role . '_analytics_' . uniqid(),
        'email' => $role . '.analytics.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ], $attributes));
}

function createFormateurAnalyticsModule(User $formateur, string $suffix): array
{
    $category = Category::query()->create([
        'category_name' => 'Categorie analytics ' . $suffix,
        'category_slug' => 'categorie-analytics-' . $suffix,
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie analytics ' . $suffix,
        'subcategory_slug' => 'sous-categorie-analytics-' . $suffix,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module analytics ' . $suffix,
        'module_name' => 'Module analytics ' . $suffix,
        'module_name_slug' => 'module-analytics-' . $suffix,
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section analytics ' . $suffix,
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon analytics ' . $suffix,
        'quiz_enabled' => true,
        'quiz_questions_per_attempt' => 1,
        'question_count' => 1,
    ]);

    return compact('module', 'section', 'lecture');
}

function createFormateurAnalyticsContext(): array
{
    $suffix = uniqid();
    $formateur = createFormateurAnalyticsUser('formateur');
    $stagiaire = createFormateurAnalyticsUser('stagiaire', [
        'formateur_id' => $formateur->id,
    ]);

    $learning = createFormateurAnalyticsModule($formateur, $suffix);

    $group = Group::query()->create([
        'name' => 'Groupe analytics ' . $suffix,
        'description' => 'Groupe analytics',
        'instructor_id' => $formateur->id,
    ]);

    $group->modules()->attach($learning['module']->id, ['position' => 1]);
    $group->users()->attach($stagiaire->id, ['role_in_group' => 'stagiaire']);

    return array_merge(compact('formateur', 'stagiaire', 'group'), $learning);
}

function createQuizAttempt(
    User $stagiaire,
    ModuleLecture $lecture,
    int $percent,
    bool $passed,
    string $startedAt,
    ?string $finishedAt = null
): QuizAttempt {
    return QuizAttempt::query()->create([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lecture->id,
        'started_at' => now()->parse($startedAt),
        'finished_at' => $finishedAt ? now()->parse($finishedAt) : null,
        'total_questions' => 1,
        'score' => $percent,
        'percent' => $percent,
        'passed' => $passed,
        'total_time_seconds' => 42,
    ]);
}

it('reports quiz-only success in trainer progression lists', function () {
    $context = createFormateurAnalyticsContext();

    createQuizAttempt(
        $context['stagiaire'],
        $context['lecture'],
        100,
        true,
        '-2 hours',
        '-90 minutes',
    );

    $stagiairesResponse = $this->actingAs($context['formateur'])
        ->get(route('formateur.progressions.stagiaires'));

    $stagiairesResponse->assertOk();

    $stagiaireRow = $stagiairesResponse->viewData('stagiaires')->getCollection()->first();
    expect((int) $stagiaireRow->lecons_terminees_count)->toBe(1);
    expect((int) $stagiaireRow->taux_reussite)->toBe(100);
    expect($stagiaireRow->last_completed_at)->not->toBeNull();

    $groupesResponse = $this->actingAs($context['formateur'])
        ->get(route('formateur.progressions.groupes'));

    $groupesResponse->assertOk();

    $groupRow = $groupesResponse->viewData('groupes')->getCollection()->first();
    expect((int) $groupRow->lecons_terminees_count)->toBe(1);
    expect((int) $groupRow->taux_reussite)->toBe(100);
});

it('scopes trainer stagiaire metrics to the selected group modules', function () {
    $formateur = createFormateurAnalyticsUser('formateur');
    $stagiaire = createFormateurAnalyticsUser('stagiaire', [
        'formateur_id' => $formateur->id,
    ]);

    $first = createFormateurAnalyticsModule($formateur, uniqid('a_'));
    $second = createFormateurAnalyticsModule($formateur, uniqid('b_'));

    $groupA = Group::query()->create([
        'name' => 'Groupe A ' . uniqid(),
        'description' => 'Groupe A',
        'instructor_id' => $formateur->id,
    ]);
    $groupB = Group::query()->create([
        'name' => 'Groupe B ' . uniqid(),
        'description' => 'Groupe B',
        'instructor_id' => $formateur->id,
    ]);

    $groupA->modules()->attach($first['module']->id, ['position' => 1]);
    $groupB->modules()->attach($second['module']->id, ['position' => 1]);
    $groupA->users()->attach($stagiaire->id, ['role_in_group' => 'stagiaire']);
    $groupB->users()->attach($stagiaire->id, ['role_in_group' => 'stagiaire']);

    createQuizAttempt($stagiaire, $first['lecture'], 100, true, '-3 hours', '-2 hours');
    createQuizAttempt($stagiaire, $second['lecture'], 20, false, '-90 minutes', '-80 minutes');

    $response = $this->actingAs($formateur)
        ->get(route('formateur.progressions.stagiaires', ['group_id' => $groupA->id]));

    $response->assertOk();

    $stagiaireRow = $response->viewData('stagiaires')->getCollection()->first();
    expect((int) $stagiaireRow->lecons_terminees_count)->toBe(1);
    expect((int) $stagiaireRow->taux_reussite)->toBe(100);
});

it('includes quiz activity in the trainer dashboard summary and activity payload', function () {
    $context = createFormateurAnalyticsContext();

    createQuizAttempt(
        $context['stagiaire'],
        $context['lecture'],
        100,
        true,
        '-20 minutes',
        '-10 minutes',
    );

    $dashboardResponse = $this->actingAs($context['formateur'])
        ->get(route('formateur.dashboard'));

    $dashboardResponse->assertOk();
    expect((int) $dashboardResponse->viewData('avgSuccessRate'))->toBe(100);
    expect((int) $dashboardResponse->viewData('inactiveLearnersCount'))->toBe(0);
    expect((int) $dashboardResponse->viewData('notStartedLearnersCount'))->toBe(0);

    $activityResponse = $this->actingAs($context['formateur'])
        ->getJson(route('formateur.dashboard.activity', ['range' => 'week']));

    $activityResponse->assertOk()
        ->assertJsonPath('summary.current_average_rate', 100)
        ->assertJsonPath('table_groups.0.latest_rate', 100);
});
