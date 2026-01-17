@extends('stagiaire.formations.master_lecon_evaluation')
<!-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/lecon.blade.php -->

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    use Illuminate\Support\Facades\URL;

    $lecture = $selectedLecture ?? null;

    $moduleId  = $module->id ?? null;
    $lectureId = $lecture?->id;
    $sectionId = $lecture?->section_id;

    $nextUrl  = '#';
    $finalUrl = $moduleId ? "/stagiaire/modules/{$moduleId}/fin" : '/stagiaire';

    if (!empty($nextLecture) && $moduleId) {
        if ((int) $nextLecture['section_id'] === (int) $sectionId) {
            $nextUrl = "/stagiaire/modules/{$moduleId}/sections/{$nextLecture['section_id']}/lessons/{$nextLecture['id']}";
        } else {
            $nextUrl = "/stagiaire/modules/{$moduleId}/sections/{$nextLecture['section_id']}";
        }
    }

    $quizStartUrl = null;
    if ($lecture && $lecture->quiz_enabled && $moduleId && $sectionId) {
        $quizStartUrl = URL::signedRoute('stagiaire.quiz.start', [
            'module'  => $moduleId,
            'section' => $sectionId,
            'lecture' => $lecture->id,
        ]);
    }


    $scormSrc = null;

    if ($lecture) {

        // 1) Nouveau système : bibliothèque SCORM (index_path calculé via accessor)
        // Nécessite : ModuleLecture::getScormIndexPathAttribute()
        $indexPath = $lecture->scorm_index_path ?? null;

        if (!empty($indexPath)) {
            $scormSrc = asset(ltrim($indexPath, '/'));
        }

        // 2) Fallback : ancien système (scorm_path)
        if (!$scormSrc && !empty($lecture->scorm_path)) {
            $path = trim((string) $lecture->scorm_path);
            if ($path !== '') {
                $scormSrc = asset("modules/scorm/00_Lecons/{$path}/res/index.html");
            }
        }
    }


@endphp

@if ($lectureId)
<script>
  window.currentLectureId = @json($lectureId);
</script>
@endif

<main class="flex-1 bg-white">
  @if ($lecture && $scormSrc)

    <script>
      window.SCORM_CONTEXT = {
        lecture_id: @json($lectureId),
        module_id: @json($moduleId),
        section_id: @json($sectionId),

        next_url: @json($nextUrl),
        final_url: @json($finalUrl),

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

      console.log('[SCORM_CONTEXT] quiz_start_url =', window.SCORM_CONTEXT.quiz_start_url);
    </script>

    

    <iframe
      title="Contenu de la leçon"
      src="{{ $scormSrc }}"
      frameborder="0"
      allowfullscreen
      class="w-full"
      style="height: calc(100vh - 64px); display: block;">
    </iframe>

  @else
    <div class="max-w-[900px] mx-auto px-6 py-10">
      <div class="bg-white rounded-[20px] shadow-md p-8">
        <h1 class="text-xl font-raleway text-bleuone font-semibold">Leçon indisponible</h1>
        <p class="text-sm text-gray-600 mt-2">Aucun contenu SCORM n’est associé à cette leçon.</p>
      </div>
    </div>
  @endif
</main>

@endsection
