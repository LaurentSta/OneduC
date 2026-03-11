{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/anonyme/lecon.blade.php --}}
@extends('formateur.formations.master_lecon')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@php
  use Illuminate\Support\Facades\URL;
  use Illuminate\Support\Facades\Storage;

  $lecture   = $selectedLecture ?? null;
  $moduleId  = $module->id ?? null;
  $lectureId = $lecture?->id;
  $sectionId = $lecture?->section_id;
  $contentType = (string) ($lecture->content_type ?? 'scorm');
  $isSlidesSelected = $contentType === 'slides';
  $isScormSelected = !$isSlidesSelected;

  // Query propagée : conserve mode/group_id/include_hidden et force anonymous
  $q = array_merge(($contextQuery ?? []), ['anonymous' => 1]);
  $appendQuery = static function (string $url, array $query): string {
      if (empty($query)) {
          return $url;
      }
      return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
  };

  // Navigation (routes formateur)
  $nextUrl  = '#';
  $finalUrl = $moduleId
      ? $appendQuery(route('formateur.formations.detail', ['module' => $moduleId]), $q)
      : route('formateur.dashboard');

  if (!empty($nextLecture) && is_array($nextLecture) && !empty($nextLecture['url'])) {
      $nextUrl = $appendQuery((string) $nextLecture['url'], $q);
  }

  // Quiz (routes formateur)
  $quizStartUrl = null;
  if ($lecture && $lecture->quiz_enabled && $moduleId && $sectionId) {
      $quizStartUrl = URL::signedRoute('formateur.quiz.start', [
          'module'  => $moduleId,
          'section' => $sectionId,
          'lecture' => $lecture->id,
      ] + $q);
  }

  // Source SCORM
  $scormSrc = $lecture?->scorm_asset_url;

  $isSlidesMode = $lecture
      && $isSlidesSelected
      && ($lecture->slides_status ?? null) === 'ready'
      && !empty($lecture->slides_path);

  $slideImages = [];
  if ($isSlidesMode) {
      $slideImages = collect(Storage::disk('public')->files($lecture->slides_path))
          ->filter(fn (string $file) => (bool) preg_match('/^slide[-_]\\d+\\.jpg$/i', basename($file)))
          ->sortBy(function (string $file): int {
              if (preg_match('/(\\d+)\\.jpg$/i', basename($file), $matches)) {
                  return (int) $matches[1];
              }
              return PHP_INT_MAX;
          })
          ->values()
          ->map(fn (string $file) => route('media.storage', ['path' => $file], false))
          ->all();
  }

  $slidesStatus = (string) ($lecture->slides_status ?? 'none');
@endphp

@if ($lectureId)
  <script>
    window.currentLectureId = @json($lectureId);
  </script>
@endif

<main class="flex-1 bg-white">
  @if ($lecture)

    <script>
      window.SCORM_CONTEXT = {
        lecture_id: @json($lectureId),
        module_id: @json($moduleId),
        section_id: @json($sectionId),

        next_url: @json($nextUrl),
        final_url: @json($finalUrl),
        is_already_done: false,

        // ✅ lecture seule (formateur anonyme)
        anonymous: true,
        read_only: true,
        force_next_lesson: true,

        goToNextLesson: function () {
          if (this.next_url && this.next_url !== "#") {
            window.location.href = this.next_url;
            return;
          }
          window.location.href = this.final_url;
        },

        // En mode formateur, le quiz est un test volontaire uniquement.
        quiz_start_url: null,
        quiz_tester_url: @json($quizStartUrl),

        goToQuiz: function () {
          if (!this.quiz_tester_url) {
            alert("Quiz non activé pour cette leçon.");
            return;
          }
          window.location.href = this.quiz_tester_url;
        }
      };

      window.goToQuiz = function () {
        if (window.SCORM_CONTEXT && typeof window.SCORM_CONTEXT.goToQuiz === "function") {
          window.SCORM_CONTEXT.goToQuiz();
        }
      };

      window.goToNextLesson = function () {
        if (window.SCORM_CONTEXT && typeof window.SCORM_CONTEXT.goToNextLesson === "function") {
          window.SCORM_CONTEXT.goToNextLesson();
        }
      };

      console.log('[SCORM_CONTEXT] anonymous/read_only =', window.SCORM_CONTEXT.anonymous, window.SCORM_CONTEXT.read_only);
      console.log('[SCORM_CONTEXT] quiz_tester_url =', window.SCORM_CONTEXT.quiz_tester_url);
    </script>

    {{-- Bloc iframe robuste (identique stagiaire) --}}
    <div class="relative w-full bg-gray-100" style="height: calc(100vh - var(--app-header-h, 86px));">
      @if ($isSlidesMode && !empty($slideImages))
        <div
          x-data="{
            current: 1,
            total: {{ count($slideImages) }},
            slides: @js($slideImages),
            get currentSrc() { return this.slides[this.current - 1] ?? null; }
          }"
          class="h-full flex flex-col"
        >
          <div class="relative flex-1 p-4 md:p-6">
            <div class="absolute top-6 right-6 z-10 px-3 py-1 rounded-full bg-black/70 text-white text-xs font-semibold">
              Slide <span x-text="current"></span> / <span x-text="total"></span>
            </div>

            <div class="h-full w-full flex items-center justify-center rounded-xl border border-gray-200 bg-white overflow-hidden">
              <img :src="currentSrc" alt="Slide de cours" class="max-h-full max-w-full object-contain">
            </div>

            <div class="absolute inset-y-0 left-2 flex items-center">
              <button type="button" @click="if(current > 1) current--"
                      class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition" aria-label="Slide precedente">
                <i class="ti ti-chevron-left"></i>
              </button>
            </div>
            <div class="absolute inset-y-0 right-2 flex items-center">
              <button type="button" @click="if(current < total) current++"
                      class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition" aria-label="Slide suivante">
                <i class="ti ti-chevron-right"></i>
              </button>
            </div>
          </div>

          <div class="border-t border-gray-200 bg-white px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <button type="button" @click="current = Math.max(1, current - 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                Precedent
              </button>
              <button type="button" @click="current = Math.min(total, current + 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                Suivant
              </button>
            </div>
            <div class="flex items-center gap-2">
              @if($quizStartUrl)
                <a href="{{ $quizStartUrl }}" class="px-3 py-2 text-xs font-bold uppercase rounded border border-orangeone text-orangeone hover:bg-orangeone hover:text-white transition">
                  Tester le quiz
                </a>
              @endif
              <a href="{{ $nextUrl !== '#' ? $nextUrl : $finalUrl }}"
                 class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white rounded-full text-xs font-bold uppercase hover:bg-orangeone-hover transition">
                Lecon suivante
                <i class="ti ti-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      @elseif ($lecture && $isSlidesSelected && in_array($slidesStatus, ['pending', 'processing'], true))
        <div class="flex items-center justify-center h-full">
          <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
            <svg class="w-12 h-12 text-blue-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15" />
            </svg>
            <h3 class="text-lg font-bold text-bleuone">Conversion en cours</h3>
            <p class="text-gray-500 text-sm">Le support slides est en cours de traitement.</p>
          </div>
        </div>
      @elseif ($lecture && $isSlidesSelected && $slidesStatus === 'failed')
        <div class="flex items-center justify-center h-full">
          <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
            <svg class="w-12 h-12 text-red-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
            </svg>
            <h3 class="text-lg font-bold text-bleuone">Conversion echouee</h3>
            <p class="text-gray-500 text-sm">Le support slides n'a pas pu etre converti.</p>
          </div>
        </div>
      @elseif ($lecture && $isSlidesSelected)
        <div class="flex items-center justify-center h-full">
          <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
            <svg class="w-12 h-12 text-amber-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-bold text-bleuone">Support slides manquant</h3>
            <p class="text-gray-500 text-sm">Le mode Slides est actif, mais aucun support converti n'est disponible.</p>
          </div>
        </div>
      @elseif ($lecture && $isScormSelected && $scormSrc)
        <iframe
          title="{{ $lecture->lecture_title }}"
          src="{{ $scormSrc }}"
          frameborder="0"
          allowfullscreen
          class="w-full h-full block">
        </iframe>
      @elseif ($lecture && $isScormSelected)
        <div class="flex items-center justify-center h-full">
          <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
            <svg class="w-12 h-12 text-orange-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <h3 class="text-lg font-bold text-bleuone">Ressource SCORM manquante</h3>
            <p class="text-gray-500 text-sm">Le mode SCORM est actif, mais aucune ressource SCORM n'est configuree.</p>
          </div>
        </div>
      @else
        <div class="flex items-center justify-center h-full">
          <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-lg font-bold text-bleuone">Contenu non disponible</h3>
            <p class="text-gray-500 text-sm">Aucun contenu pret (SCORM ou Slides) n'est encore configure pour cette lecon.</p>
          </div>
        </div>
      @endif
    </div>

  @else
    <div class="max-w-[900px] mx-auto px-6 py-10">
      <div class="bg-white rounded-[20px] shadow-md p-8">
        <h1 class="text-xl font-raleway text-bleuone font-semibold">Leçon indisponible</h1>
        <p class="text-sm text-gray-600 mt-2">Aucune leçon n’est disponible.</p>
      </div>
    </div>
  @endif
</main>

@endsection
