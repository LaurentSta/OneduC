@extends('formateur.formations.evaluations.master_lecon_evaluation')

@section('content')
<main class="max-w-full mx-auto">
  <div class="bg-white rounded-[20px] shadow-md p-8 mb-6">
    <h1 class="text-titre font-raleway text-bleuone">
      {{ $selectedSection->section_title }}
    </h1>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Colonne gauche : contenu + accordéons --}}
    <div x-data="{ openItem: 1 }" class="space-y-3">
      @if(!empty($selectedSection->section_html))
        <div class="border rounded-md p-4 bg-white">
          <h2 class="text-base font-varela text-orangeone mb-2">Questions de départ</h2>
          <div class="prose max-w-none">
            {!! $selectedSection->section_html !!}
          </div>
        </div>
      @endif

      {{-- Objectif pédagogique --}}
      @if($selectedSection->objectif)
      <div class="border rounded-md">
        <button
          @click="openItem = openItem === 1 ? null : 1"
          class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200"
          :aria-expanded="openItem === 1" aria-controls="panel-objectif">
          <span>Objectif pédagogique</span>
          <svg x-show="openItem !== 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/></svg>
          <svg x-show="openItem === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="openItem === 1" x-collapse id="panel-objectif"
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
          class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200"
          :aria-expanded="openItem === 2" aria-controls="panel-methode">
          <span>Méthode pédagogique</span>
          <svg x-show="openItem !== 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/></svg>
          <svg x-show="openItem === 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="openItem === 2" x-collapse id="panel-methode" class="p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
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
          :aria-expanded="openItem === 3" aria-controls="panel-contexte">
          <span>Contexte pédagogique</span>
          <svg x-show="openItem !== 3" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/></svg>
          <svg x-show="openItem === 3" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/></svg>
        </button>
        <div x-show="openItem === 3" x-collapse id="panel-contexte" class="p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed">
          {{ $selectedSection->contexte }}
        </div>
      </div>
      @endif
    </div>

    {{-- Colonne droite : Vidéo + bouton --}}
    <div class="space-y-6">
      @php
        $raw = (string) ($selectedSection->video_url ?? '');
        $isFullUrl = \Illuminate\Support\Str::startsWith($raw, ['http://','https://']);
        $isAbsolutePath = \Illuminate\Support\Str::startsWith($raw, ['/']);
        $videoPath = $raw !== '' ? ($isFullUrl || $isAbsolutePath ? $raw : '/modules/scorm/02_videos/' . ltrim($raw, '/')) : null;
        $videoSrc = $isFullUrl ? $videoPath : ($videoPath ? asset($videoPath) : null);
        $firstLecture = $selectedSection->lectures->first();
      @endphp

      {{-- Vidéo pédagogique ou placeholder --}}
      @if($videoSrc)
        <div class="relative w-full rounded-md shadow" style="padding-top:56.25%;" role="region" aria-label="Vidéo pédagogique">
          <video id="formation-video"
                 class="video-js absolute top-0 left-0 w-full h-full"
                 controls preload="metadata" playsinline
                 aria-describedby="video-desc"
                 data-setup='{"playbackRates":[0.5,1,1.25,1.5,2]}'>
            <source src="{{ $videoSrc }}" type="video/mp4">
          </video>
          <p id="video-desc" class="sr-only">Vidéo de présentation de la section</p>
        </div>
      @else
        <img src="{{ asset('images/svg/Modules.svg') }}" alt="Aperçu indisponible" class="block max-w-[320px] h-auto">
      @endif

      {{-- Bouton tester --}}
      @if($firstLecture)
        <div class="mt-4">
          <a href="{{ route('formateur.formations.lecture', ['module' => $module->id, 'section' => $selectedSection->id, 'lesson' => $firstLecture->id]) }}"
             class="btn-oneduc flex items-center justify-center gap-2">
            Tester cette section
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- Tracking vidéo --}}
  @if($videoSrc)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const player = videojs('formation-video');
      let startTime = 0, trackingInProgress = false;

      player.on('play', () => { if (startTime === 0) startTime = Math.floor(player.currentTime()); });
      player.on('seeked', () => { startTime = Math.floor(player.currentTime()); });
      player.on('pause', () => trackSegment());
      player.on('ended', () => trackSegment(true));

      function trackSegment(force = false) {
        const endTime = Math.floor(player.currentTime());
        const duration = endTime - startTime;
        if ((duration < 5 && !force) || endTime === startTime) return;
        trackingInProgress = true;

        fetch('{{ route('api.video.segment') }}', {
          method: 'POST',
          headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
          body: JSON.stringify({
            lecture_id: {{ $selectedSection->lectures->first()?->id ?? 'null' }},
            segment_start: startTime,
            segment_end: endTime,
            watch_time: duration
          })
        }).then(() => {
          startTime = endTime; trackingInProgress = false;
        }).catch(() => { trackingInProgress = false; });
      }

      setInterval(() => { if (!player.paused() && !trackingInProgress) trackSegment(); }, 10000);

      window.addEventListener('beforeunload', function () {
        const endTime = Math.floor(player.currentTime());
        const duration = endTime - startTime;
        if (duration < 3 || endTime === startTime) return;
        const data = {
          lecture_id: {{ $selectedSection->lectures->first()?->id ?? 'null' }},
          segment_start: startTime, segment_end: endTime, watch_time: duration
        };
        const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
        navigator.sendBeacon('{{ route('api.video.segment') }}', blob);
      });
    });
  </script>
  @endif
</main>
@endsection
