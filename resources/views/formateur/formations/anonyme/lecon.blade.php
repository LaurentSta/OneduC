{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/anonyme/lecon.blade.php --}}
@extends('formateur.formations.master_lecon')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@php
  use Illuminate\Support\Facades\URL;

  $lecture   = $selectedLecture ?? null;
  $moduleId  = $module->id ?? null;
  $lectureId = $lecture?->id;
  $sectionId = $lecture?->section_id;

  // Query propagée : conserve mode/group_id/include_hidden et force anonymous
  $q = array_merge(($contextQuery ?? []), ['anonymous' => 1]);

  // Navigation (routes formateur)
  $nextUrl  = '#';
  $finalUrl = $moduleId ? route('formateur.formations.detail', ['module' => $moduleId]) : route('formateur.dashboard');

  if (!empty($nextLecture) && $moduleId) {
      if ((int) $nextLecture['section_id'] === (int) $sectionId) {
          $nextUrl = route('formateur.formations.lecture', [
              'module'  => $moduleId,
              'section' => (int) $nextLecture['section_id'],
              'lecture' => (int) $nextLecture['id'],
          ] + $q);
      } else {
          $nextUrl = route('formateur.formations.section', [
              'module'  => $moduleId,
              'section' => (int) $nextLecture['section_id'],
          ] + $q);
      }
  }

  // Quiz (routes formateur)
  $quizStartUrl = null;
  if ($lecture && $lecture->quiz_enabled && $moduleId && $sectionId) {
      $quizStartUrl = URL::signedRoute('formateur.quiz.start', [
          'module'  => $moduleId,
          'section' => $sectionId,
          'lecture' => $lecture->id,
      ]);
  }

  // Source SCORM
  $scormSrc = null;
  if ($lecture && !empty($lecture->scorm_path)) {
      $scormSrc = asset($lecture->scorm_path);
  }
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

        // ✅ lecture seule (formateur anonyme)
        anonymous: true,
        read_only: true,

        goToNextLesson: function () {
          if (this.next_url && this.next_url !== "#") {
            window.location.href = this.next_url;
            return;
          }
          window.location.href = this.final_url;
        },

        quiz_start_url: @json($quizStartUrl),

        goToQuiz: function () {
          if (!this.quiz_start_url) {
            alert("Quiz non activé pour cette leçon.");
            return;
          }
          window.location.href = this.quiz_start_url;
        }
      };

      console.log('[SCORM_CONTEXT] anonymous/read_only =', window.SCORM_CONTEXT.anonymous, window.SCORM_CONTEXT.read_only);
      console.log('[SCORM_CONTEXT] quiz_start_url =', window.SCORM_CONTEXT.quiz_start_url);
    </script>

    {{-- Bloc iframe robuste (identique stagiaire) --}}
    <div class="relative w-full bg-gray-100" style="height: calc(100vh - 64px);">
      @if ($scormSrc)
        <iframe
          title="{{ $lecture->lecture_title }}"
          src="{{ $scormSrc }}"
          frameborder="0"
          allowfullscreen
          class="w-full h-full block">
        </iframe>
      @else
        <div class="flex items-center justify-center h-full">
          <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-gray-200">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-lg font-bold text-bleuone">Contenu non disponible</h3>
            <p class="text-gray-500 text-sm">Le module interactif n'est pas encore configuré pour cette leçon.</p>
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
