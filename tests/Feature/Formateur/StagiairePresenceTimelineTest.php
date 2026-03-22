<?php

use App\Models\Category;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\Progression;
use App\Models\ScormInteraction;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPresenceUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role) . ' Presence',
        'username' => $role . '_presence_' . uniqid(),
        'email' => $role . '.presence.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
        'total_site_time' => 0,
    ]);
}

function createPresenceContext(): array
{
    $formateur = createPresenceUser('formateur');
    $stagiaire = createPresenceUser('stagiaire');
    $suffix = uniqid();

    $category = Category::query()->create([
        'category_name' => 'Categorie presence ' . $suffix,
        'category_slug' => 'categorie-presence-' . $suffix,
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie presence ' . $suffix,
        'subcategory_slug' => 'sous-categorie-presence-' . $suffix,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module presence ' . $suffix,
        'module_name' => 'Module presence ' . $suffix,
        'module_name_slug' => 'module-presence-' . $suffix,
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section presence',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon presence',
        'quiz_enabled' => true,
        'quiz_questions_per_attempt' => 3,
    ]);

    $group = Group::query()->create([
        'name' => 'Groupe presence ' . $suffix,
        'description' => 'Groupe test assiduite',
        'instructor_id' => $formateur->id,
    ]);

    $group->modules()->attach($module->id, ['position' => 1]);
    $group->users()->attach($stagiaire->id, [
        'role_in_group' => 'stagiaire',
        'created_at' => now()->subDays(21),
        'updated_at' => now()->subDays(21),
    ]);

    return compact('formateur', 'stagiaire', 'group', 'module', 'section', 'lecture');
}

it('builds a unified activity timeline for the formateur stagiaire detail page', function () {
    [
        'formateur' => $formateur,
        'stagiaire' => $stagiaire,
        'group' => $group,
        'lecture' => $lecture,
    ] = createPresenceContext();

    $stagiaire->forceFill(['total_site_time' => 5400])->save();

    Progression::query()->create([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lecture->id,
        'completed_at' => now()->subDays(2),
    ]);

    DB::table('quiz_attempts')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lecture->id,
        'started_at' => now()->subDays(3)->setTime(9, 0),
        'finished_at' => now()->subDays(3)->setTime(9, 12),
        'total_questions' => 3,
        'score' => 66,
        'percent' => 66,
        'passed' => false,
        'total_time_seconds' => 720,
        'created_at' => now()->subDays(3)->setTime(9, 0),
        'updated_at' => now()->subDays(3)->setTime(9, 12),
    ]);

    DB::table('video_segment_trackings')->insert([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lecture->id,
        'segment_start' => 0,
        'segment_end' => 60,
        'watch_count' => 1,
        'total_watch_time' => 1800,
        'created_at' => now()->subDay()->setTime(14, 0),
        'updated_at' => now()->subDay()->setTime(14, 0),
    ]);

    ScormInteraction::query()->create([
        'user_id' => $stagiaire->id,
        'lecture_id' => $lecture->id,
        'interaction_id' => 'presence-1',
        'result' => 'correct',
        'latency' => '00:00:15',
        'created_at' => now()->subDays(4)->setTime(11, 0),
        'updated_at' => now()->subDays(4)->setTime(11, 0),
    ]);

    $response = $this->actingAs($formateur)->get(route('formateur.progressions.stagiaire', [
        'user' => $stagiaire->id,
        'group_id' => $group->id,
    ]));

    $response->assertOk();
    $response->assertSeeText('Assiduité et présence');
    $response->assertSeeText('Leçons, quiz, vidéos et SCORM');
    $response->assertSee($group->name);

    $presenceSummary = $response->viewData('presenceSummary');
    $activityFeed = $response->viewData('activityFeed');

    expect($presenceSummary['context_name'])->toBe($group->name);
    expect($presenceSummary['active_days_count'])->toBeGreaterThanOrEqual(4);
    expect($presenceSummary['risk']['level'])->toBe('good');
    expect($presenceSummary['last_activity_at']->greaterThanOrEqualTo(now()->subDay()->startOfDay()))->toBeTrue();
    expect($activityFeed->pluck('type')->all())->toContain('lesson', 'quiz', 'video', 'scorm');
});

it('flags a stagiaire without activity as high risk', function () {
    [
        'formateur' => $formateur,
        'stagiaire' => $stagiaire,
        'group' => $group,
    ] = createPresenceContext();

    $response = $this->actingAs($formateur)->get(route('formateur.progressions.stagiaire', [
        'user' => $stagiaire->id,
        'group_id' => $group->id,
    ]));

    $response->assertOk();

    $presenceSummary = $response->viewData('presenceSummary');

    expect($presenceSummary['active_days_count'])->toBe(0);
    expect($presenceSummary['risk']['level'])->toBe('critical');
    expect($presenceSummary['risk']['label'])->toBe('Risque eleve');
});
