<?php

namespace App\Domains\ModulesFormateur\Support;

class AssembleurSegmentsLecon
{
    /**
     * Assembles the final tagged list of "segments" fed to shared.lecture_segments:
     * the lesson's own content (already split into progressive-reveal segments by
     * DecoupeurBlocsLecon) followed by one auto-appended "quiz" segment per eligible
     * QuizQuestion — zero manual placement by the formateur, the mere existence of an
     * active, non-cloze question in the bank makes it show up.
     *
     * @param  array<int, array<string, mixed>>  $blocks  the lesson's flat content_blocks array
     * @param  iterable<\App\Models\QuizQuestion>  $quizQuestions  active, eligible questions
     *         (type single|multiple|boolean), 'options' eager-loaded, in display order
     * @return array<int, array{kind: string, blocks?: array, question?: \App\Models\QuizQuestion}>
     */
    public static function assembler(array $blocks, iterable $quizQuestions = []): array
    {
        $segments = array_map(
            static fn (array $segmentBlocks) => ['kind' => 'content', 'blocks' => $segmentBlocks],
            DecoupeurBlocsLecon::decouper($blocks)
        );

        foreach ($quizQuestions as $question) {
            $segments[] = ['kind' => 'quiz', 'question' => $question];
        }

        return $segments;
    }
}
