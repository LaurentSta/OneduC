@extends('formateur.formations.evaluations.master_lecon_evaluation')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@if (isset($selectedLecture))
  <script>window.currentLectureId = {{ $selectedLecture->id }};</script>
@endif

<main class="flex-1 bg-white">
  @if (isset($selectedLecture) && $selectedLecture->scorm_path)
    @php
      $p = (string) ($selectedLecture->scorm_path ?? '');
      $isExternal = \Illuminate\Support\Str::startsWith($p, ['http://','https://','/']);
      $scormUrl = $isExternal
        ? $p
        : asset('modules/scorm/00_Lecons/' . ltrim($p,'/') . '/res/index.html');
    @endphp

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

<script>
  const moduleId        = {{ $module->id }};
  const currentLectureId= {{ $selectedLecture->id ?? 'null' }};
  const currentSectionId= {{ $selectedLecture->section_id ?? 'null' }};
  const nextLecture     = @json($nextLecture);

  const routes = {
    lectureTpl : "{{ route('formateur.formations.lecture', ['module'=>'__m__','section'=>'__s__','lesson'=>'__l__']) }}",
    sectionTpl : "{{ route('formateur.formations.section', ['module'=>'__m__','section'=>'__s__']) }}",
    detail     : "{{ route('formateur.formations.detail', ['module'=>$module->id]) }}",
  };
  function fill(tpl, m, s, l='') {
    return tpl.replace('__m__', m).replace('__s__', s).replace('__l__', l);
  }

  let nextUrl = "#";
  if (nextLecture) {
    if (Number(nextLecture.section_id) === Number(currentSectionId)) {
      nextUrl = fill(routes.lectureTpl, moduleId, nextLecture.section_id, nextLecture.id);
    } else {
      nextUrl = fill(routes.sectionTpl, moduleId, nextLecture.section_id);
    }
  }
  const finalUrl = routes.detail;

  window.SCORM_CONTEXT = {
    lecture_id: currentLectureId,
    next_url: nextUrl,
    goToNextLesson: function () {
      window.location.href = (this.next_url && this.next_url !== "#") ? this.next_url : finalUrl;
    }
  };
</script>
@endsection
