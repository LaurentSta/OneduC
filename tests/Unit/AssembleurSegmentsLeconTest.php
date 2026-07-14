<?php

use App\Domains\ModulesFormateur\Support\AssembleurSegmentsLecon;
use App\Models\QuizQuestion;

function stubQuizQuestion(int $id, string $type = 'single'): QuizQuestion
{
    $question = new QuizQuestion(['type' => $type, 'question_text' => "Q{$id}"]);
    $question->id = $id;
    $question->setRelation('options', collect());

    return $question;
}

it('wraps content-only blocks as a single content segment when there are no quiz questions', function () {
    $blocks = [['type' => 'text', 'html' => 'hello']];

    expect(AssembleurSegmentsLecon::assembler($blocks, []))
        ->toBe([['kind' => 'content', 'blocks' => $blocks]]);
});

it('appends one quiz segment per question, after all content segments', function () {
    $blocks = [
        ['type' => 'text', 'html' => 'a'],
        ['type' => 'divider', 'mode' => 'reveal'],
        ['type' => 'text', 'html' => 'b'],
    ];
    $q1 = stubQuizQuestion(1);
    $q2 = stubQuizQuestion(2, 'boolean');

    $segments = AssembleurSegmentsLecon::assembler($blocks, [$q1, $q2]);

    expect($segments)->toHaveCount(4)
        ->and($segments[0])->toBe(['kind' => 'content', 'blocks' => [['type' => 'text', 'html' => 'a']]])
        ->and($segments[1])->toBe(['kind' => 'content', 'blocks' => [['type' => 'text', 'html' => 'b']]])
        ->and($segments[2])->toBe(['kind' => 'quiz', 'question' => $q1])
        ->and($segments[3])->toBe(['kind' => 'quiz', 'question' => $q2]);
});

it('still produces exactly one (possibly empty) content segment when there is no content but quiz questions exist', function () {
    $q1 = stubQuizQuestion(1);

    $segments = AssembleurSegmentsLecon::assembler([], [$q1]);

    expect($segments)->toHaveCount(2)
        ->and($segments[0])->toBe(['kind' => 'content', 'blocks' => []])
        ->and($segments[1])->toBe(['kind' => 'quiz', 'question' => $q1]);
});
