{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/anonyme/chapitre.blade.php --}}
@extends('formateur.formations.master_lecon')
@section('content')

@php
  use Illuminate\Support\Str;

  // On force anonymous=1 dans tous les liens
  $q = array_merge(($contextQuery ?? []), ['anonymous' => 1]);

  $firstLecture = ($selectedSection->lectures ?? collect())->first();

  // Questions de départ (section_html)
  $rawQuestions = trim((string) ($selectedSection->section_html ?? ''));
  $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);

  // Objectifs issus des leçons
  $lecturesWithObjectives = ($selectedSection->lectures ?? collect())
      ->filter(fn($lec) => ($lec->objectives ?? collect())->isNotEmpty());

  // Vidéo (facultatif)
  $videoSrc = null;
  if (!empty($selectedSection->video_url)) {
      $isFullUrl = Str::startsWith($selectedSection->video_url, ['http', '/']);
      $videoPath = $isFullUrl
          ? $selectedSection->video_url
          : '/modules/scorm/02_videos/' . ltrim($selectedSection->video_url, '/');

      $videoSrc = asset($videoPath);
  }
@endphp

<main class="max-w-full mx-auto">
  <div class="bg-white rounded-[20px] shadow-md p-8 mb-6">
    <h1 class="text-2xl md:text-3xl font-raleway font-medium text-bleuone leading-tight mt-0 mb-2">
      {{ $selectedSection->section_title }}
    </h1>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Colonne gauche : Accordéons --}}
    <div x-data="{ openItem: 1 }" class="space-y-3">

      {{-- Accordéon 1 : Objectifs --}}
      @if($lecturesWithObjectives->isNotEmpty() || !empty($selectedSection->objectif))
        <div class="border rounded-md">
          <button
            type="button"
            @click="openItem = openItem === 1 ? null : 1"
            class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200"
            :aria-expanded="openItem === 1"
            aria-controls="panel-objectifs"
          >
            <span>Objectifs</span>
            <svg x-show="openItem !== 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
            </svg>
            <svg x-show="openItem === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
            </svg>
          </button>

          <div
            id="panel-objectifs"
            x-show="openItem === 1"
            x-collapse
            class="overflow-hidden p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed"
          >
            @if($lecturesWithObjectives->isNotEmpty())
              <div class="space-y-4">
                @foreach($lecturesWithObjectives as $lec)
                  <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="font-semibold text-bleuone">
                      {{ $lec->lecture_title }}
                    </div>

                    <ul class="mt-2 list-disc pl-6 space-y-1 marker:text-orangeone">
                      @foreach($lec->objectives as $obj)
                        <li class="font-medium">{{ $obj->title }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endforeach
              </div>
            @elseif(!empty($selectedSection->objectif))
              <div class="font-lisible text-[17px] text-gray-800 leading-relaxed
                          [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                          [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                          [&_li::marker]:text-orangeone">
                {!! $selectedSection->objectif !!}
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- Accordéon 2 : Questions pour commencer --}}
      <div class="border rounded-md">
        <button
          type="button"
          @click="openItem = openItem === 2 ? null : 2"
          class="w-full flex items-center justify-between px-4 py-3 text-left font-varela text-bleuone bg-gray-100 hover:bg-gray-200"
          :aria-expanded="openItem === 2"
          aria-controls="panel-questions"
        >
          <span>Questions pour commencer</span>
          <svg x-show="openItem !== 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
          </svg>
          <svg x-show="openItem === 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-180 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd" />
          </svg>
        </button>

        <div
          id="panel-questions"
          x-show="openItem === 2"
          x-collapse
          class="overflow-hidden p-4 bg-white font-lisible text-[17px] text-gray-800 leading-relaxed
                 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1
                 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1
                 [&_li::marker]:text-orangeone"
        >
          @if($rawQuestions !== '')
            @if($isHtml)
              {!! $rawQuestions !!}
            @else
              @php
                $questionsDepart = collect(preg_split("/\r\n|\n|\r/", $rawQuestions))
                    ->map(fn($qq) => trim($qq))
                    ->filter()
                    ->values();
              @endphp

              @if($questionsDepart->isNotEmpty())
                <ul class="list-disc pl-6 space-y-1 marker:text-orangeone">
                  @foreach($questionsDepart as $qq)
                    <li>{!! preg_replace('/\s\?$/', '&nbsp;?', e($qq)) !!}</li>
                  @endforeach
                </ul>
              @endif
            @endif
          @else
            <p class="text-sm text-gray-600">
              Aucune question n’est renseignée pour le moment.
            </p>
          @endif
        </div>
      </div>

    </div>

    {{-- Colonne droite : Vidéo + contrôles + bouton démarrer --}}
    <div class="space-y-6">

      {{-- Vidéo pédagogique (optionnelle) --}}
      @if($videoSrc)
        <div class="relative w-full rounded-[16px] overflow-hidden shadow-md" style="aspect-ratio:16/9">
          <video id="formation-video"
                 class="block w-full h-auto"
                 controls preload="metadata" playsinline crossorigin="anonymous"
                 aria-label="Vidéo pédagogique">
            <source src="{{ $videoSrc }}" type="video/mp4">
          </video>
        </div>

        {{-- Contrôles natifs étendus (sans tracking serveur) --}}
        <div class="mt-3 w-full flex items-center justify-between text-sm">
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

            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#004461" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 9l6 6 6-6"></path>
              </svg>
            </span>
          </div>

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
      @else
        <div class="bg-white rounded-[16px] border border-gray-200 p-6 text-sm text-gray-600">
          Aucune vidéo n’est associée à ce chapitre.
        </div>
      @endif

      {{-- Bouton démarrer (mode anonyme) --}}
      @if($firstLecture)
        <div class="mt-4">
          <a href="{{ route('formateur.formations.lecture', [
                'module'  => $module->id,
                'section' => $selectedSection->id,
                'lecture' => $firstLecture->id
              ] + $q) }}"
             class="btn-oneduc flex items-center justify-center gap-2">
            Commencer cette section
          </a>
        </div>
      @endif

    </div>
  </div>

  {{-- JS contrôles vidéo (sans tracking) --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const el = document.getElementById('formation-video');
      if (!el) return;

      const rateSelect = document.getElementById('rate-select');
      const back10 = document.getElementById('back-10');
      const fwd10  = document.getElementById('fwd-10');

      const savedRate = parseFloat(localStorage.getItem('video_rate') || '1');
      if (!isNaN(savedRate)) {
        el.playbackRate = savedRate;
        if (rateSelect) rateSelect.value = String(savedRate);
      }

      rateSelect?.addEventListener('change', () => {
        const r = parseFloat(rateSelect.value || '1');
        el.playbackRate = isNaN(r) ? 1 : r;
        localStorage.setItem('video_rate', String(el.playbackRate));
      });

      back10?.addEventListener('click', () => { el.currentTime = Math.max(0, el.currentTime - 10); });
      fwd10?.addEventListener('click',  () => { el.currentTime = el.currentTime + 10; });

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
