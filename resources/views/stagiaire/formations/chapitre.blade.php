@extends('stagiaire.formations.master_lecon_evaluation')

@section('content')

@php
    $chapterNo = collect($module->sections ?? [])
        ->values()
        ->search(fn ($section) => (int) $section->id === (int) ($selectedSection->id ?? 0));

    $chapterNo = $chapterNo !== false ? $chapterNo + 1 : null;
@endphp

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- EN-TÊTE DE CHAPITRE --}}
    <div class="mb-8 border-b border-gray-100 pb-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-bleuone leading-tight">
            {{ $chapterNo ? 'Ch. ' . $chapterNo . ' - ' : '' }}{{ $selectedSection->section_title }}
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- COLONNE GAUCHE : CONTENU PÉDAGOGIQUE (7/12) --}}
        {{-- x-data configuré pour être ouvert par défaut (true) --}}
        <div class="lg:col-span-7 space-y-6" x-data="{ openObjectives: true, openQuestions: false }">
            
            @php
                use Illuminate\Support\Str;
                // Analyse du contenu HTML des questions
                $rawQuestions = trim((string) ($selectedSection->section_html ?? ''));
                $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);

                // Récupération des objectifs
                $lecturesWithObjectives = ($selectedSection->lectures ?? collect())
                    ->filter(fn($lec) => ($lec->objectives ?? collect())->isNotEmpty());
                
                $hasObjectives = $lecturesWithObjectives->isNotEmpty();
                $hasQuestions = $rawQuestions !== '';
            @endphp

            {{-- BLOC 1 : OBJECTIFS (Cible) --}}
            @if($hasObjectives)
                <div class="bg-white rounded-2xl border transition-all duration-300 overflow-hidden"
                     :class="openObjectives ? 'border-orangeone shadow-lg shadow-orange-100' : 'border-gray-100 shadow-sm'">
                    
                    <button type="button" @click="openObjectives = !openObjectives"
                            class="w-full flex items-center justify-between p-5 text-left group">
                        <div class="flex items-center gap-4">
                            {{-- Icône Cible (Target) au lieu de l'éclair --}}
                            <div class="size-10 rounded-full flex items-center justify-center transition-colors"
                                 :class="openObjectives ? 'bg-orangeone text-white' : 'bg-orange-50 text-orangeone group-hover:bg-orangeone group-hover:text-white'">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /> 
                                    {{-- Alternative icône cible pure : d="M12 2a10 10 0 100 20 10 10 0 000-20zM12 6a6 6 0 100 12 6 6 0 000-12zM12 10a2 2 0 100 4 2 2 0 000-4z" --}}
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-bleuone">Objectifs du chapitre</span>
                        </div>
                        <div class="size-8 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 transition-transform duration-300"
                             :class="openObjectives ? 'rotate-180 bg-gray-50 text-bleuone' : 'rotate-0'">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </button>

                    <div x-show="openObjectives" x-collapse>
                        <div class="p-5 pt-0 text-gray-600 leading-relaxed">
                            @if($lecturesWithObjectives->isNotEmpty())
                                <div class="space-y-6">
                                    @foreach($lecturesWithObjectives as $lec)
                                        <div class="pl-4 border-l-2 border-orange-100">
                                            <h4 class="font-bold text-bleuone text-sm uppercase tracking-wide mb-2">
                                                {{ $lec->lecture_title }}
                                            </h4>
                                            <ul class="space-y-2">
                                                @foreach($lec->objectives as $obj)
                                                    <li class="flex items-start gap-3 text-sm md:text-base">
                                                        <svg class="size-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                        <span>{{ $obj->title }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- BLOC 2 : QUESTIONS DE RÉFLEXION --}}
            <div class="bg-white rounded-2xl border transition-all duration-300 overflow-hidden"
                 :class="openQuestions ? 'border-orangeone shadow-lg shadow-orange-100' : 'border-gray-100 shadow-sm'">
                
                <button type="button" @click="openQuestions = !openQuestions"
                        class="w-full flex items-center justify-between p-5 text-left group">
                    <div class="flex items-center gap-4">
                        <div class="size-10 rounded-full flex items-center justify-center transition-colors"
                             :class="openQuestions ? 'bg-orangeone text-white' : 'bg-blue-50 text-bleuone group-hover:bg-bleuone group-hover:text-white'">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        {{-- Nouveau titre Pédagogique --}}
                        <span class="text-lg font-bold text-bleuone">Les questions que l'on se pose souvent</span>
                    </div>
                    <div class="size-8 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 transition-transform duration-300"
                         :class="openQuestions ? 'rotate-180 bg-gray-50 text-bleuone' : 'rotate-0'">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </button>

                <div x-show="openQuestions" x-collapse>
                    <div class="p-5 pt-0 text-gray-600 leading-relaxed">
                        @if($hasQuestions)
                            @if($isHtml)
                                <div class="prose prose-blue prose-sm max-w-none">
                                    {!! $rawQuestions !!}
                                </div>
                            @else
                                @php
                                    $questionsDepart = collect(preg_split("/\r\n|\n|\r/", $rawQuestions))->map(fn($q) => trim($q))->filter();
                                @endphp
                                <ul class="space-y-3">
                                    @foreach($questionsDepart as $q)
                                        <li class="flex items-start gap-3 text-sm md:text-base bg-gray-50 p-3 rounded-lg border border-gray-100">
                                            <span class="font-bold text-orangeone">?</span>
                                            <span>{!! preg_replace('/\s\?$/', '&nbsp;?', e($q)) !!}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @else
                            <p class="text-sm italic text-gray-400">Aucune question fréquente pour ce chapitre.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- COLONNE DROITE : VIDÉO & ACTIONS (5/12) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-8 space-y-6">
            
            <div class="bg-white rounded-[24px] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden p-2">
                
                {{-- Conteneur Vidéo --}}
                <div class="relative w-full rounded-2xl overflow-hidden bg-black shadow-inner group aspect-video">
                    @php
                        $videoSrc = \App\Support\LearningAssetPath::resolveSectionVideoUrl($selectedSection->video_url);
                    @endphp
                    
                    <video id="formation-video"
                           class="w-full h-full object-cover"
                           controls preload="metadata" playsinline crossorigin="anonymous">
                        <source src="{{ $videoSrc }}" type="video/mp4">
                        Votre navigateur ne supporte pas la vidéo.
                    </video>
                </div>

                {{-- Bouton Principal (Sans les contrôles de vitesse) --}}
                @php $firstLecture = $selectedSection->lectures->first(); @endphp
                @if($firstLecture)
                    <div class="p-4">
                        <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $selectedSection->id, 'lecture' => $firstLecture->id]) }}"
                           class="flex items-center justify-center w-full py-4 rounded-xl bg-orangeone text-white font-black text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100 hover:-translate-y-0.5 group">
                            <span>Commencer le chapitre</span>
                            <svg class="ml-2 size-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Info contextuelle (Raccourcis Clavier) --}}
            <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100/50 flex items-start gap-3">
                <svg class="size-6 text-bleuone mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div class="text-sm text-bleuone/80">
                    <span class="font-bold block text-bleuone">Le saviez-vous ?</span>
                    Vous pouvez contrôler la vidéo avec votre clavier : <kbd class="font-mono bg-white px-1 rounded border">Espace</kbd> pour pause, <kbd class="font-mono bg-white px-1 rounded border">←</kbd> / <kbd class="font-mono bg-white px-1 rounded border">→</kbd> pour naviguer.
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS JS (Tracking & Player Logic simplifié) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('formation-video');
            if (!el) return;

            // --- Tracking lecture (Conservé pour les stats) ---
            let startTime = 0, trackingInProgress = false;

            const getCurrent = () => Math.floor(el.currentTime || 0);
            const onPlay   = () => { if (startTime === 0) startTime = getCurrent(); };
            const onSeeked = () => { startTime = getCurrent(); };
            const onPause  = () => trackSegment();
            const onEnded  = () => trackSegment(true);

            el.addEventListener('play',   onPlay);
            el.addEventListener('seeked', onSeeked);
            el.addEventListener('pause',  onPause);
            el.addEventListener('ended',  onEnded);

            function trackSegment(force = false) {
                const endTime  = getCurrent();
                const duration = endTime - startTime;
                if ((duration < 5 && !force) || endTime === startTime) return;

                trackingInProgress = true;
                fetch('{{ route('api.video.segment') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        lecture_id: {{ $selectedSection->lectures->first()?->id ?? 'null' }},
                        section_id: {{ $selectedSection->id }},
                        segment_start: startTime,
                        segment_end: endTime,
                        watch_time: duration
                    })
                }).finally(() => { startTime = endTime; trackingInProgress = false; });
            }

            // Envoi périodique
            setInterval(() => {
                if (!el.paused && !trackingInProgress) trackSegment();
            }, 10000);

            // Envoi si on quitte la page
            window.addEventListener('beforeunload', function () {
                const endTime  = getCurrent();
                const duration = endTime - startTime;
                if (duration < 3 || endTime === startTime) return;

                const data = {
                    lecture_id: {{ $selectedSection->lectures->first()?->id ?? 'null' }},
                    section_id: {{ $selectedSection->id }},
                    segment_start: startTime,
                    segment_end: endTime,
                    watch_time: duration
                };
                const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
                navigator.sendBeacon('{{ route('api.video.segment') }}', blob);
            });

            // Raccourcis clavier Natifs (Toujours utile)
            document.addEventListener('keydown', (e) => {
                const tag = (document.activeElement?.tagName || '').toUpperCase();
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return;

                if (e.key === 'ArrowLeft') {
                    el.currentTime = Math.max(0, el.currentTime - 5);
                    // e.preventDefault(); // Optionnel : on laisse le comportement natif si focus
                } else if (e.key === 'ArrowRight') {
                    el.currentTime = el.currentTime + 5;
                } else if (e.key === ' ') {
                    if(el.paused) el.play(); else el.pause();
                    e.preventDefault();
                }
            });
        });
    </script>
</main>
@endsection
