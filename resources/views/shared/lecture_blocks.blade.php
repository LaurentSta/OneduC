{{-- resources/views/shared/lecture_blocks.blade.php --}}
@php
    $blocksArr = collect($blocks ?? [])->all();
    $lecture = $lecture ?? null;
    $interactif = $interactif ?? false;

    // Auto-append every active, non-cloze bank question for this lecture as an
    // inline "quiz" segment — zero manual placement by the formateur.
    $quizQuestions = collect();
    if ($interactif && $lecture) {
        $quizQuestions = $lecture->quizQuestions()
            ->eligibleForInlineActivity()
            ->orderBy('id')
            ->with('options')
            ->get();
    }

    $segments = $interactif
        ? \App\Domains\ModulesFormateur\Support\AssembleurSegmentsLecon::assembler($blocksArr, $quizQuestions)
        : [['kind' => 'content', 'blocks' => $blocksArr]];

    $totalGates = max(0, count($segments) - 1);
@endphp

@if($interactif)
    {{-- Shared with the lesson's "Continuer" CTA (rendered by the parent view),
         so that CTA can stay hidden until every "voir la suite"/quiz gate has been passed. --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('lectureProgress', {
                totalGates: {{ $totalGates }},
                revealedCount: 0,
                get isComplete() {
                    return this.revealedCount >= this.totalGates;
                },
            });
        });
    </script>
@endif

@include('shared.lecture_segments', ['segments' => $segments, 'lecture' => $lecture])
