<?php

use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('auto-injects active single/multiple/boolean questions inline and excludes inactive/cloze ones', function () {
    $formateur = createQuizAuthoringUser('formateur');
    ['module' => $module, 'section' => $section, 'lecture' => $lecture] = createQuizAuthoringLecture($formateur);

    $lecture->update([
        'content_type' => 'blocks',
        'content_blocks' => [['type' => 'text', 'html' => '<p>Intro</p>']],
    ]);

    $active = QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'type' => 'single',
        'question_text' => 'Active Q?',
        'is_active' => true,
        'created_by' => $formateur->id,
    ]);
    QuizOption::query()->create(['question_id' => $active->id, 'option_text' => 'Yes', 'is_correct' => true, 'position' => 1]);
    QuizOption::query()->create(['question_id' => $active->id, 'option_text' => 'No', 'is_correct' => false, 'position' => 2]);

    QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'type' => 'single',
        'question_text' => 'Inactive Q?',
        'is_active' => false,
        'created_by' => $formateur->id,
    ]);

    QuizQuestion::query()->create([
        'lecture_id' => $lecture->id,
        'type' => 'cloze',
        'question_text' => 'Cloze Q?',
        'is_active' => true,
        'created_by' => $formateur->id,
    ]);

    $response = $this->actingAs($formateur)->get(route('formateur.formations.lecture', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
    ]));

    $response->assertOk()
        ->assertSee('Active Q?')
        ->assertDontSee('Inactive Q?')
        ->assertDontSee('Cloze Q?');
});
