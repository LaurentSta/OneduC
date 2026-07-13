{{-- resources/views/shared/lecture_blocks.blade.php --}}
@php
    $blocksArr = collect($blocks ?? [])->all();
    $lecture = $lecture ?? null;
    $interactif = $interactif ?? false;

    $segments = $interactif
        ? \App\Domains\ModulesFormateur\Support\DecoupeurBlocsLecon::decouper($blocksArr)
        : [$blocksArr];

    $totalGates = max(0, count($segments) - 1);
@endphp

@if($interactif)
    {{-- Shared with the lesson's "Continuer" CTA (rendered by the parent view),
         so that CTA can stay hidden until every "voir la suite" gate has been opened. --}}
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
