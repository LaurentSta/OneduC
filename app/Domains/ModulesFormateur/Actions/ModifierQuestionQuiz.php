<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\AccesModule;
use App\Models\QuizQuestion;
use App\Services\QuizQuestionBuilder;
use Illuminate\Http\Request;

class ModifierQuestionQuiz
{
    public function __construct(
        private readonly AccesModule $access,
        private readonly QuizQuestionBuilder $builder,
    ) {}

    public function execute(QuizQuestion $question, array $validated, Request $request): QuizQuestion
    {
        $this->access->assertOwner($question->lecture->module);

        return $this->builder->updateQuestion($question, $validated, $request);
    }
}
