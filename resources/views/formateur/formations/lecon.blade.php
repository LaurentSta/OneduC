{{-- resources/views/formateur/formations/lecon.blade.php --}}

@extends('formateur.formations.master_lecon')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $lecture   = $selectedLecture ?? ($lecture ?? null);
    $moduleId  = (int) ($module->id ?? 0);
    $lectureId = $lecture ? (int) $lecture->id : null;
    $sectionId = $lecture ? (int) $lecture->section_id : null;

    // Conserver le contexte (mode / group_id / include_hidden) dans la navigation
    $contextQuery = $contextQuery ?? [];
    $qs = '';
    if (!empty($contextQuery) && is_array($contextQuery)) {
        $qs = '?' . http_build_query($contextQuery);
    }

    // --- URL SCORM (robuste) ---
    $scormUrl = null;
    if ($lecture && !empty($lecture->scorm_path)) {
        $p = (string) $lecture->scorm_path;

        if (\Illuminate\Support\Str::startsWith($p, ['http://', 'https://'])) {
            $scormUrl = $p;
        } elseif (\Illuminate\Support\Str::startsWith($p, '/')) {
            $scormUrl = url($p);
        } else {
            $scormUrl = asset($p);
        }
    }

    // --- Navigation (avec contexte) ---
    $finalUrl = $moduleId
        ? route('formateur.formations.detail', ['module' => $moduleId]) . $qs
        : route('formateur.dashboard');

    $nextUrl = '#';
    if (!empty($nextLecture) && $moduleId) {
        $nextSectionId = (int) ($nextLecture['section_id'] ?? 0);
        $nextId        = (int) ($nextLecture['id'] ?? 0);

        if ($nextSectionId && $nextId) {
            if ($sectionId && $nextSectionId === $sectionId) {
                $nextUrl = route('formateur.formations.lecture', [
                    'module'  => $moduleId,
                    'section' => $nextSectionId,
                    'lecture' => $nextId,
                ]) . $qs;
            } else {
                $nextUrl = route('formateur.formations.section', [
                    'module'  => $moduleId,
                    'section' => $nextSectionId,
                ]) . $qs;
            }
        }
    }

    // --- Quiz (optionnel) ---
    $quizStartUrl = null;
    if ($lecture && !empty($lecture->quiz_enabled) && $moduleId && $sectionId && $lectureId) {
        if (\Illuminate\Support\Facades\Route::has('formateur.quiz.start')) {
            $quizStartUrl = \Illuminate\Support\Facades\URL::signedRoute('formateur.quiz.start', [
                'module'  => $moduleId,
                'section' => $sectionId,
                'lecture' => $lectureId, // IMPORTANT : votre route demande {lecture}
            ]) . $qs;
        }
    }
@endphp

@if ($lectureId)
  <script>window.currentLectureId = {{ $lectureId }};</script>
@endif

<div class="flex min-h-[calc(100vh-64px)] bg-white">

  {{-- Sidebar (si votre master_lecon ne l’inclut pas déjà) --}}
  {{-- Décommente si nécessaire :
  @include('formateur.formations.body_formations.sidebar', [
      'module'          => $module,
      'selectedSection' => $selectedSection ?? $section ?? null,
      'selectedLecture' => $lecture,
      'lectureStats'    => $lectureStats ?? [],
      'sectionStatuses' => $sectionStatuses ?? [],
      'contextQuery'    => $contextQuery,
  ])
  --}}

  <main class="flex-1 bg-white">
    @if ($lecture && $scormUrl)
      <iframe
        title="Contenu de la leçon"
        src="{{ $scormUrl }}"
        frameborder="0"
        allowfullscreen
        class="w-full"
        style="height: calc(100vh - 64px); display: block;">
      </iframe>
    @else
      <div class="p-6">
        <p class="text-gray-700">Aucun contenu SCORM défini pour cette leçon.</p>
      </div>
    @endif
  </main>
</div>

<script>
  const finalUrl = @json($finalUrl);

  window.SCORM_CONTEXT = {
    lecture_id: @json($lectureId),
    module_id: @json($moduleId),
    section_id: @json($sectionId),

    // Navigation (déjà avec ?mode=...&group_id=...&include_hidden=...)
    next_url: @json($nextUrl),

    // Quiz (si présent, déjà avec le contexte)
    quiz_start_url: @json($quizStartUrl),

    goToQuiz: function () {
      if (!this.quiz_start_url) return;
      window.location.href = this.quiz_start_url;
    },

    // Règle : quiz prioritaire ; sinon suite ; sinon détail
    goToNextLesson: function () {
      if (this.quiz_start_url) {
        window.location.href = this.quiz_start_url;
        return;
      }
      if (this.next_url && this.next_url !== "#") {
        window.location.href = this.next_url;
        return;
      }
      window.location.href = finalUrl;
    }
  };
</script>
@endsection
