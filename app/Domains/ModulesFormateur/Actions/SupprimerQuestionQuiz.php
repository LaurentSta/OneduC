<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\AccesModule;
use App\Models\QuizQuestion;
use App\Services\QuizQuestionBuilder;

class SupprimerQuestionQuiz
{
    public function __construct(
        private readonly AccesModule $access,
        private readonly QuizQuestionBuilder $builder,
    ) {}

    public function execute(QuizQuestion $question): void
    {
        $this->access->assertOwner($question->lecture->module);

        $this->builder->deleteQuestion($question);
    }
}
