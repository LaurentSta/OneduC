<?php

use App\Models\Category;
use App\Models\LiveQuizSession;
use App\Models\LiveQuizSessionParticipant;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createLiveQuizUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' Live',
        'username' => $role.'_live_'.uniqid(),
        'email' => $role.'.live.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createLiveQuizContext(User $formateur): array
{
    $suffix = uniqid();

    $category = Category::query()->create([
        'category_name' => 'Categorie live '.$suffix,
        'category_slug' => 'categorie-live-'.$suffix,
    ]);

    $subcategory = SubCategory::query()->create([
        'category_id' => $category->id,
        'subcategory_name' => 'Sous-categorie live '.$suffix,
        'subcategory_slug' => 'sous-categorie-live-'.$suffix,
    ]);

    $module = Module::query()->create([
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'formateur_id' => $formateur->id,
        'module_title' => 'Module live '.$suffix,
        'module_name' => 'Module live '.$suffix,
        'module_name_slug' => 'module-live-'.$suffix,
        'status' => 1,
    ]);

    $section = ModuleSection::query()->create([
        'module_id' => $module->id,
        'section_title' => 'Section live',
    ]);

    $lecture = ModuleLecture::query()->create([
        'module_id' => $module->id,
        'section_id' => $section->id,
        'lecture_title' => 'Lecon live',
        'quiz_enabled' => true,
        'quiz_questions_per_attempt' => 1,
    ]);

    return compact('module', 'section', 'lecture');
}

it('creates a live quiz session from the questions of a lesson', function () {
    $formateur = createLiveQuizUser('formateur');
    $context = createLiveQuizContext($formateur);
    $module = $context['module'];
    $section = $context['section'];
    $lecture = $context['lecture'];
    $token = 'csrf-live-formateur';

    QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Quel est le bon choix ?',
        'type' => 'single',
        'is_active' => true,
    ]);

    QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Deuxieme question',
        'type' => 'boolean',
        'is_active' => true,
    ]);

    $response = $this->actingAs($formateur)
        ->withSession(['_token' => $token])
        ->post(route('formateur.live-quiz.store', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
        ]), ['_token' => $token]);

    $session = LiveQuizSession::query()->latest('id')->first();

    $response->assertRedirect(route('formateur.live-quiz.show', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
        'session' => $session->id,
    ]));

    expect($session)->not->toBeNull();
    expect($session->access_code)->toHaveLength(6);
    expect($session->total_questions)->toBe(2);
    expect($session->sessionQuestions()->count())->toBe(2);
    expect($session->status)->toBe(LiveQuizSession::STATUS_WAITING);
});

it('records stagiaire answers in a live session and finalizes the attempt when the formateur closes it', function () {
    $formateur = createLiveQuizUser('formateur');
    $stagiaire = createLiveQuizUser('stagiaire');
    $context = createLiveQuizContext($formateur);
    $module = $context['module'];
    $section = $context['section'];
    $lecture = $context['lecture'];
    $formateurToken = 'csrf-live-formateur';
    $stagiaireToken = 'csrf-live-stagiaire';

    $question = QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'question_text' => 'Capitale de la France ?',
        'type' => 'single',
        'is_active' => true,
    ]);

    $correctOption = QuizOption::query()->create([
        'question_id' => $question->id,
        'option_text' => 'Paris',
        'is_correct' => true,
        'position' => 1,
    ]);

    QuizOption::query()->create([
        'question_id' => $question->id,
        'option_text' => 'Lyon',
        'is_correct' => false,
        'position' => 2,
    ]);

    $this->actingAs($formateur)
        ->withSession(['_token' => $formateurToken])
        ->post(route('formateur.live-quiz.store', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
        ]), ['_token' => $formateurToken]);

    $session = LiveQuizSession::query()->latest('id')->firstOrFail();

    $this->actingAs($formateur)
        ->withSession(['_token' => $formateurToken])
        ->post(route('formateur.live-quiz.start', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'session' => $session->id,
        ]), ['_token' => $formateurToken]);

    $joinResponse = $this->actingAs($stagiaire)
        ->withSession(['_token' => $stagiaireToken])
        ->post(route('stagiaire.live-quiz.join', [
            'session' => $session->id,
        ]), ['_token' => $stagiaireToken]);

    $participant = LiveQuizSessionParticipant::query()->where('user_id', $stagiaire->id)->first();

    $joinResponse->assertRedirect(route('stagiaire.live-quiz.show', ['session' => $session->id]));
    expect($participant)->not->toBeNull();

    $attempt = QuizAttempt::query()->findOrFail($participant->attempt_id);
    expect($attempt->total_questions)->toBe(1);
    expect($attempt->attemptQuestions()->count())->toBe(1);

    $answerResponse = $this->actingAs($stagiaire)
        ->withSession(['_token' => $stagiaireToken])
        ->post(route('stagiaire.live-quiz.answer', [
            'session' => $session->id,
        ]), [
            '_token' => $stagiaireToken,
            'answer' => $correctOption->id,
            'time_spent' => 12,
        ]);

    $answerResponse->assertRedirect(route('stagiaire.live-quiz.show', ['session' => $session->id]));

    $attempt->refresh();
    $attemptQuestion = $attempt->attemptQuestions()->firstOrFail();

    expect($attemptQuestion->is_correct)->toBeTrue();
    expect($attemptQuestion->time_seconds)->toBe(12);

    $this->actingAs($formateur)
        ->withSession(['_token' => $formateurToken])
        ->post(route('formateur.live-quiz.reveal', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'session' => $session->id,
        ]), ['_token' => $formateurToken]);

    $this->actingAs($formateur)
        ->withSession(['_token' => $formateurToken])
        ->post(route('formateur.live-quiz.next', [
            'module' => $module->id,
            'section' => $section->id,
            'lecture' => $lecture->id,
            'session' => $session->id,
        ]), ['_token' => $formateurToken]);

    $attempt->refresh();
    $session->refresh();

    expect($session->status)->toBe(LiveQuizSession::STATUS_CLOSED);
    expect($attempt->finished_at)->not->toBeNull();
    expect((int) $attempt->percent)->toBe(100);
    expect((int) $attempt->total_time_seconds)->toBe(12);
});

it('does not rewrite last_seen_at on every snapshot poll within the throttle window', function () {
    $formateur = createLiveQuizUser('formateur');
    $stagiaire = createLiveQuizUser('stagiaire');
    $context = createLiveQuizContext($formateur);
    $formateurToken = 'csrf-live-formateur-throttle';
    $stagiaireToken = 'csrf-live-stagiaire-throttle';

    QuizQuestion::query()->create([
        'lecture_id' => $context['lecture']->id,
        'question_text' => 'Question unique',
        'type' => 'boolean',
        'is_active' => true,
    ]);

    $this->actingAs($formateur)
        ->withSession(['_token' => $formateurToken])
        ->post(route('formateur.live-quiz.store', [
            'module' => $context['module']->id,
            'section' => $context['section']->id,
            'lecture' => $context['lecture']->id,
        ]), ['_token' => $formateurToken]);

    $session = LiveQuizSession::query()->latest('id')->firstOrFail();

    $this->actingAs($stagiaire)
        ->withSession(['_token' => $stagiaireToken])
        ->post(route('stagiaire.live-quiz.join', ['session' => $session->id]), ['_token' => $stagiaireToken]);

    $participant = LiveQuizSessionParticipant::query()->where('user_id', $stagiaire->id)->firstOrFail();
    $recentSeenAt = $participant->last_seen_at;

    DB::enableQueryLog();
    $response = $this->actingAs($stagiaire)
        ->getJson(route('stagiaire.live-quiz.snapshot', ['session' => $session->id]));
    $queries = collect(DB::getQueryLog())->pluck('query')->implode(' | ');
    DB::disableQueryLog();

    $response->assertOk();
    expect($participant->fresh()->last_seen_at->eq($recentSeenAt))->toBeTrue();
    expect($queries)->not->toContain('update `live_quiz_session_participants`');

    $participant->update(['last_seen_at' => now()->subSeconds(20)]);

    $this->actingAs($stagiaire)
        ->getJson(route('stagiaire.live-quiz.snapshot', ['session' => $session->id]))
        ->assertOk();

    expect($participant->fresh()->last_seen_at->gt(now()->subSeconds(5)))->toBeTrue();
});
