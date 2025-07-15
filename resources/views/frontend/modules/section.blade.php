@extends('frontend.modules.master_lecture')
@section('content')
<main class="max-w-full mx-auto px-4 py-8 bg-white">

    <h1 class="text-2xl font-bold text-gray-800 mb-6">📂 {{ $section->section_title }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- 🧠 Objectif / Méthode / Contexte (colonne gauche) --}}
    <div class="space-y-6">
        @if($section->objectif)
            <div class="bg-white border rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">🎯 Objectif pédagogique</h2>
                <p class="text-gray-800">{{ $section->objectif }}</p>
            </div>
        @endif

        @if($section->methode)
            <div class="bg-white border rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">🧠 Méthode pédagogique</h2>
                <p class="text-gray-800">{{ $section->methode }}</p>
            </div>
        @endif

        @if($section->contexte)
            <div class="bg-white border rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">📌 Contexte pédagogique</h2>
                <p class="text-gray-800">{{ $section->contexte }}</p>
            </div>
        @endif
    </div>

    {{-- 🎬 Vidéo + bouton + leçons (colonne droite) --}}
    <div class="space-y-6">
        @php
            $isFullUrl = Str::startsWith($section->video_url, ['http', '/']);
            $videoPath = $isFullUrl ? $section->video_url : '/modules/scorm/02_videos/' . ltrim($section->video_url, '/');
            $videoSrc = asset($videoPath);
        @endphp

        {{-- ▶️ Bouton de démarrage --}}
        @php $firstLecture = $section->lectures->first(); @endphp
        @if($firstLecture)
            <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $firstLecture->id]) }}"
            class="inline-block bg-orangeone text-white font-medium px-6 py-2 rounded shadow hover:bg-orange-600 transition">
                ▶️ Commencer cette section
            </a>
        @endif


        {{-- 🎬 Vidéo pédagogique --}}
<div class="relative w-full" style="padding-top: 56.25%;">
    <video id="formation-video"
           class="video-js absolute top-0 left-0 w-full h-full"
           controls preload="metadata"
           playsinline
           data-setup='{"playbackRates": [0.5, 1, 1.25, 1.5, 2]}'>
        <source src="{{ $videoSrc }}" type="video/mp4">
    </video>
</div>




        {{-- 📚 Liste des leçons --}}
        <div>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">📚 Leçons de cette section</h2>
            <ul class="space-y-2">
                @foreach($section->lectures as $lecture)
                    <li>
                        <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lecture->id]) }}"
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
                startTime = Math.floor(player.currentTime());
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
                        lecture_id: {{ $section->lectures->first()?->id ?? 'null' }},
                        segment_start: startTime,
                        segment_end: endTime,
                        watch_time: duration
                    })
                }).then(() => {
                    console.log('📝 Segment enregistré', startTime, endTime);
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
