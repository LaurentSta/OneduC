@extends('frontend.modules.master_lecture')

@section('content')
<main class="max-w-full mx-auto">
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-6">
        <h1 class="text-titre font-raleway text-bleuone">
            {{ $selectedSection->section_title }}

        </h1>
        <h2 class="text-sous-titre font-varela text-orangeone mt-2">
            Objectifs et déroulé pédagogique.
        </h2>
        <nav class="text-sm font-varela text-gray-600 mt-4" aria-label="Fil d'Ariane">
            <ol class="list-none p-0 inline-flex items-center space-x-1">
                {{-- Accueil --}}
                <li class="flex items-center">
                    <a href="{{ route('index') }}" class="text-orangeone hover:underline flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                        </svg>
                    </a>
                    <span class="mx-2 text-gray-400">/</span>
                </li>
                {{-- Mes modules --}}
                <li class="flex items-center">
                    <a href="{{ route('stagiaire.modules') }}" class="hover:underline text-bleuone">Mes modules</a>
                    <span class="mx-2 text-gray-400">/</span>
                </li>

                {{-- Étape finale stylisée comme "..." --}}
                <li class="text-gray-400">…</li>
            </ol>
        </nav>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Colonne gauche : Accordéon pédagogique --}}
        <div x-data="{ openItem: 1 }" class="space-y-3">
            {{-- Objectif pédagogique --}}
            @if($selectedSection->objectif)
            <div class="border rounded-md">
                <button
                    @click="openItem = openItem === 1 ? null : 1"
                    class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200">
                    <span>Objectif pédagogique</span>
                    <svg x-show="openItem !== 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
                    </svg>
                    <svg x-show="openItem === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="openItem === 1" x-collapse
                    class="overflow-hidden p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
                    {{ $selectedSection->objectif }}     
                </div>
            </div>
            @endif
            {{-- Méthode pédagogique --}}
            @if($selectedSection->methode)
            <div class="border rounded-md">
                <button
                    @click="openItem = openItem === 2 ? null : 2"
                    class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200">
                    <span>Méthode pédagogique</span>
                    <svg x-show="openItem !== 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
                    </svg>
                    <svg x-show="openItem === 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="openItem === 2" x-collapse class="p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
                    {{ $selectedSection->methode }}
                </div>
            </div>
            @endif
            {{-- Contexte pédagogique --}}
            @if($selectedSection->contexte)
            <div class="border rounded-md">
                <button
                    @click="openItem = openItem === 3 ? null : 3"
                    class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200"
                >
                    <span>Contexte pédagogique</span>
                    <svg x-show="openItem !== 3" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
                    </svg>
                    <svg x-show="openItem === 3" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="openItem === 3" x-collapse class="p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
                    {{ $selectedSection->contexte }}
                </div>
            </div>
            @endif
        </div>
        {{-- Colonne droite : Vidéo + bouton + leçons --}}
        <div class="space-y-6">
            @php
                $isFullUrl = Str::startsWith($selectedSection->video_url, ['http', '/']);
                $videoPath = $isFullUrl ? $selectedSection->video_url : '/modules/scorm/02_videos/' . ltrim($selectedSection->video_url, '/');
                $videoSrc = asset($videoPath);
                $firstLecture = $selectedSection->lectures->first();
            @endphp

            <div class="w-full">
    
</div>

            {{-- Vidéo pédagogique --}}
            <div class="relative w-full rounded-md shadow" style="padding-top: 56.25%;">
                <video id="formation-video"
                       class="video-js absolute top-0 left-0 w-full h-full"
                       controls preload="metadata"
                       playsinline
                       data-setup='{"playbackRates": [0.5, 1, 1.25, 1.5, 2]}'>
                    <source src="{{ $videoSrc }}" type="video/mp4">
                </video>
            </div>

            {{-- Bouton démarrer --}}
            @if($firstLecture)
            <div class="mt-4">
                <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $selectedSection->id, 'lesson' => $firstLecture->id]) }}"
                class="btn-oneduc flex items-center justify-center gap-2">
                    Commencer cette section
                </a>

            </div>
            @endif

            {{-- Liste des leçons --}}
            <div>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Leçons de cette section</h2>
                <ul class="space-y-2">
                    @foreach($selectedSection->lectures as $lecture)
                        <li>
                            <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $selectedSection->id, 'lesson' => $lecture->id]) }}"
                               class="text-blue-600 hover:underline">{{ $lecture->lecture_title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Scripts videojs --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const player = videojs('formation-video');

        let startTime = 0;

        player.on('play', () => {
            if (startTime === 0) {
                startTime = Math.floor(player.currentTime());
            }
        });

        player.on('seeked', () => {
            startTime = Math.floor(player.currentTime()); // ← MAJ du start après glissement curseur
        });

        player.on('pause', () => {
            trackSegment();
        });

        player.on('ended', () => {
            trackSegment(true);
        });

        function trackSegment(force = false) {
            const endTime = Math.floor(player.currentTime());
            const duration = endTime - startTime;

            if (duration < 5 && !force) return;

            fetch('{{ route('api.video.segment') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    lecture_id: {{ $selectedSection->lectures->first()?->id ?? 'null' }},
                    segment_start: startTime,
                    segment_end: endTime,
                    watch_time: duration
                })
            }).then(() => {
                console.log('Segment enregistré', startTime, endTime);
                startTime = endTime;
            }).catch(err => {
                console.error('Erreur tracking vidéo :', err);
            });
        }

        setInterval(() => {
            if (!player.paused()) {
                trackSegment();
            }
        }, 60000);
    });
</script>


</main>
@endsection
