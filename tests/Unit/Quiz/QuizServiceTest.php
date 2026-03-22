<?php

use App\Models\QuizQuestion;
use App\Services\QuizService;
use Illuminate\Support\Collection;

it('grades cloze answers with tolerant text normalization and partial points', function () {
    $service = app(QuizService::class);

    $question = new QuizQuestion([
        'type' => 'cloze',
        'payload' => [
            'blanks' => [
                'city' => [
                    'accepted_answers' => ['Paris', 'paris '],
                    'points' => 2,
                ],
                'country' => [
                    'accepted_answers' => ['France', 'Republique francaise'],
                    'points' => 1,
                ],
            ],
        ],
    ]);

    $graded = $service->gradeAnswer($question, [
        'answers' => [
            'city' => '  paris ',
            'country' => 'République française',
        ],
    ]);

    expect($graded['is_correct'])->toBeTrue()
        ->and($graded['answer_option_ids'])->toBeNull()
        ->and(data_get($graded, 'given_answer.score'))->toBe(3)
        ->and(data_get($graded, 'given_answer.max_score'))->toBe(3)
        ->and(data_get($graded, 'given_answer.percent'))->toBe(100)
        ->and(data_get($graded, 'given_answer.blanks.country.is_correct'))->toBeTrue();
});

it('grades multiple choice answers without going through an HTTP controller', function () {
    $service = app(QuizService::class);

    $question = new QuizQuestion([
        'type' => 'multiple',
    ]);
    $question->setRelation('options', new Collection([
        (object) ['id' => 10, 'is_correct' => true],
        (object) ['id' => 11, 'is_correct' => false],
        (object) ['id' => 12, 'is_correct' => true],
    ]));

    $graded = $service->gradeAnswer($question, [
        'answers' => [10, 12, 12],
    ]);

    expect($graded)->toMatchArray([
        'is_correct' => true,
        'answer_option_ids' => [10, 12],
        'given_answer' => null,
    ]);
});
