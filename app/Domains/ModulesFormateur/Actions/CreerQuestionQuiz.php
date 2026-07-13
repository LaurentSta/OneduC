<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\AccesModule;
use App\Models\ModuleLecture;
use App\Models\QuizQuestion;
use App\Services\QuizQuestionBuilder;
use Illuminate\Http\Request;

class CreerQuestionQuiz
{
    public function __construct(
        private readonly AccesModule $access,
        private readonly QuizQuestionBuilder $builder,
    ) {}

    public function execute(ModuleLecture $lecture, array $validated, Request $request): QuizQuestion
    {
        $this->access->assertOwner($lecture->module);

        return $this->builder->createQuestion($lecture, $validated, $request, (int) auth()->id());
    }
}
