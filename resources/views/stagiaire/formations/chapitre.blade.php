<!-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/chapitre.blade.php -->
@extends('stagiaire.formations.master_lecon_evaluation')
@section('content')

<main class="max-w-full mx-auto">
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-6">
        <h1 class="text-2xl md:text-3xl font-raleway font-medium text-bleuone
           leading-tight mt-0 mb-2">
            {{ $selectedSection->section_title }}
        </h1>


    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Colonne gauche : Accordéon pédagogique --}}
        <div x-data="{ openItem: null }" class="space-y-3">
            {{-- Contenu HTML de la section (dans la colonne gauche, avant l’accordéon) --}}
            @php
                use Illuminate\Support\Str;

                $rawQuestions = (string) ($selectedSection->section_html ?? '');
                $rawQuestions = trim($rawQuestions);

                // Détection simple : si ça ressemble à du HTML (Quill), on l'affiche tel quel
                $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);
            @endphp

            @if($rawQuestions !== '')
                <div class="border rounded-md p-4 bg-white">
                    <h2 class="text-base font-varela text-orangeone mb-2">Questions pour commencer</h2>

                    @if($isHtml)
                        {{-- HTML Quill --}}
                        <div class="font-lisible text-[17px] text-gray-800 leading-relaxed
                                    [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                                    [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                                    [&_li::marker]:text-orangeone">
                            {!! $rawQuestions !!}
                        </div>
                    @else
                        {{-- Ancien mode : 1 question par ligne --}}
                        @php
                            $questionsDepart = collect(preg_split("/\r\n|\n|\r/", $rawQuestions))
                                ->map(fn($q) => trim($q))
                                ->filter()
                                ->values();
                        @endphp

                        @if($questionsDepart->isNotEmpty())
                            <ul class="list-disc pl-6 space-y-1 font-lisible text-[17px] text-gray-800 leading-relaxed marker:text-orangeone">
                                @foreach($questionsDepart as $q)
                                    <li>{!! preg_replace('/\s\?$/', '&nbsp;?', e($q)) !!}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                </div>
            @endif


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
                    <div x-show="openItem === 1" x-collapse
                    class="overflow-hidden p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed
                            [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                            [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                            [&_li::marker]:text-orangeone">
                    {!! $selectedSection->objectif !!}
                </div>

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
                    <div x-show="openItem === 2" x-collapse
                        class="p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed
                                [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                                [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                                [&_li::marker]:text-orangeone">
                        {!! $selectedSection->methode !!}
                    </div>

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
                    <div x-show="openItem === 3" x-collapse
                        class="p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed
                                [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                                [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                                [&_li::marker]:text-orangeone">
                        {!! $selectedSection->contexte !!}
                    </div>

                </div>
            </div>
            @endif
        </div>
        {{-- Colonne droite : Vidéo + bouton + leçons --}}
        <div class="space-y-6">
            @php
                $isFullUrl = \Illuminate\Support\Str::startsWith($selectedSection->video_url, ['http', '/']);
                $videoPath = $isFullUrl ? $selectedSection->video_url : '/modules/scorm/02_videos/' . ltrim($selectedSection->video_url, '/');
                $videoSrc = asset($videoPath);
                $firstLecture = $selectedSection->lectures->first();
            @endphp
            <div class="w-full">
</div>
            {{-- Vidéo pédagogique --}}
            <div class="relative w-full rounded-[16px] overflow-hidden shadow-md" style="aspect-ratio:16/9">
                <video id="formation-video"
                        class="block w-full h-auto"   {{-- <- h-auto au lieu de h-full --}}
                        controls preload="metadata" playsinline crossorigin="anonymous"
                        aria-label="Vidéo pédagogique">
                    <source src="{{ $videoSrc }}" type="video/mp4">
                </video>
            </div>


            {{-- Contrôles natifs étendus --}}

<div class="mt-3 w-full flex items-center justify-between text-sm">

  {{-- Bloc vitesse à gauche --}}
  <div class="relative inline-block">
  <select id="rate-select"
          class="w-48 pr-10 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-[#004461] appearance-none
                 focus:outline-none focus:ring-2 focus:ring-[#E94D2A] focus:border-[#E94D2A]"
          style="min-width: 11rem;">
    <option value="0.5">0.5×</option>
    <option value="0.75">0.75×</option>
    <option value="1" selected>1×</option>
    <option value="1.25">1.25×</option>
    <option value="1.5">1.5×</option>
    <option value="1.75">1.75×</option>
    <option value="2">2×</option>
  </select>

  {{-- Chevron custom, ne capte pas les clics --}}
  <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#004461" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 9l6 6 6-6"></path>
    </svg>
  </span>
</div>

  {{-- Boutons regroupés à droite --}}
  <div class="flex items-center gap-2">
    <button id="back-10" type="button"
            class="rounded-md bg-[#004461] px-4 py-2 text-white font-semibold
                   hover:bg-[#00364d] focus:outline-none focus:ring-2 focus:ring-[#E94D2A]">
      −10 s
    </button>

    <button id="fwd-10" type="button"
            class="rounded-md bg-[#E94D2A] px-4 py-2 text-white font-semibold
                   hover:bg-[#cc4120] focus:outline-none focus:ring-2 focus:ring-[#004461]">
      +10 s
    </button>
  </div>
</div>





            {{-- Bouton démarrer --}}
            @if($firstLecture)
            <div class="mt-4">
                <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $selectedSection->id, 'lecture' => $firstLecture->id]) }}"
                class="btn-oneduc flex items-center justify-center gap-2">
                    Commencer cette section
                </a>
            </div>
            @endif
        </div>
    </div>
   <script>
document.addEventListener('DOMContentLoaded', function () {
  // Cible vidéo
  const el = document.getElementById('formation-video');
  if (!el) return;

  // --- Tracking lecture ---
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
        segment_start: startTime,
        segment_end: endTime,
        watch_time: duration
      })
    }).finally(() => { startTime = endTime; trackingInProgress = false; });
  }

  // Sauvegarde périodique
  setInterval(() => {
    if (!el.paused && !trackingInProgress) trackSegment();
  }, 10000);

  // Sauvegarde à la fermeture
  window.addEventListener('beforeunload', function () {
    const endTime  = getCurrent();
    const duration = endTime - startTime;
    if (duration < 3 || endTime === startTime) return;

    const data = {
      lecture_id: {{ $selectedSection->lectures->first()?->id ?? 'null' }},
      segment_start: startTime,
      segment_end: endTime,
      watch_time: duration
    };
    const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
    navigator.sendBeacon('{{ route('api.video.segment') }}', blob);
  });

  // --- Contrôles natifs étendus ---
  const rateSelect = document.getElementById('rate-select');
  const back10 = document.getElementById('back-10');
  const fwd10  = document.getElementById('fwd-10');

  // Restaurer vitesse
  const savedRate = parseFloat(localStorage.getItem('video_rate') || '1');
  if (!isNaN(savedRate)) {
    el.playbackRate = savedRate;
    if (rateSelect) rateSelect.value = String(savedRate);
  }

  // Sélecteur vitesse
  rateSelect?.addEventListener('change', () => {
    const r = parseFloat(rateSelect.value || '1');
    el.playbackRate = isNaN(r) ? 1 : r;
    localStorage.setItem('video_rate', String(el.playbackRate));
  });

  // Sauts temporels
  back10?.addEventListener('click', () => { el.currentTime = Math.max(0, el.currentTime - 10); });
  fwd10?.addEventListener('click',  () => { el.currentTime = el.currentTime + 10; });

  // Raccourcis clavier
  document.addEventListener('keydown', (e) => {
    const tag = (document.activeElement?.tagName || '').toUpperCase();
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

    if (e.key === '[') {
      el.playbackRate = Math.max(0.25, Math.round((el.playbackRate - 0.25)*100)/100);
      if (rateSelect) rateSelect.value = String(el.playbackRate);
      localStorage.setItem('video_rate', String(el.playbackRate));
    } else if (e.key === ']') {
      el.playbackRate = Math.min(4, Math.round((el.playbackRate + 0.25)*100)/100);
      if (rateSelect) rateSelect.value = String(el.playbackRate);
      localStorage.setItem('video_rate', String(el.playbackRate));
    } else if (e.key === 'ArrowLeft') {
      el.currentTime = Math.max(0, el.currentTime - 5);
    } else if (e.key === 'ArrowRight') {
      el.currentTime = el.currentTime + 5;
    }
  });
});
</script>




</main>
@endsection
