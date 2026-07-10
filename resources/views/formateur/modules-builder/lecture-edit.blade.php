{{-- resources/views/formateur/modules-builder/lecture-edit.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <nav class="text-sm font-varela text-gray-500 mb-2">
      <ol class="inline-flex items-center flex-wrap gap-1">
        <li>
          <a href="{{ route('formateur.modules.builder.index') }}" class="text-orangeone hover:underline">Mes créations</a>
        </li>
        <li><span class="mx-1 text-gray-400">/</span></li>
        <li>
          <a href="{{ route('formateur.modules.builder.edit', $module) }}" class="text-orangeone hover:underline">{{ $module->module_title }}</a>
        </li>
        <li><span class="mx-1 text-gray-400">/</span></li>
        <li class="text-gray-400">{{ $section->section_title }}</li>
        <li><span class="mx-1 text-gray-400">/</span></li>
        <li class="text-gray-400">{{ $lecture->lecture_title }}</li>
      </ol>
    </nav>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <a href="{{ route('formateur.modules.builder.edit', $module) }}" class="text-sm font-semibold text-gray-500 hover:text-orangeone">← Retour au plan de la formation</a>

      <div class="flex items-center gap-2">
        @if($leconPrecedente ?? null)
          <a href="{{ route('formateur.modules.builder.lectures.edit', $leconPrecedente) }}"
             title="{{ $leconPrecedente->lecture_title }}"
             class="inline-flex items-center gap-1 rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-orangeone hover:text-orangeone">
            ← Précédent
          </a>
        @endif

        @if($leconSuivante ?? null)
          <a href="{{ route('formateur.modules.builder.lectures.edit', $leconSuivante) }}"
             title="{{ $leconSuivante->lecture_title }}"
             class="inline-flex items-center gap-1 rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-orangeone hover:text-orangeone">
            Suivant →
          </a>
        @endif
      </div>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-6 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if(!in_array($lecture->content_type, ['scorm', 'slides'], true))
    <div class="bg-white rounded-[20px] shadow-md px-10 py-6 mb-6">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p class="font-varela text-sm font-bold text-bleuone">Audio de la leçon</p>
          <p class="text-xs text-gray-400">Génère une lecture audio du texte de la leçon (voix française).</p>
        </div>
        <form method="POST" action="{{ route('formateur.modules.builder.lectures.generate-audio', $lecture) }}">
          @csrf
          <button type="submit" class="btn-oneduc-outline !px-4 !py-2 !text-xs">
            {{ $lecture->getFirstMedia('lesson-audio') ? "Régénérer l'audio" : "Générer l'audio" }}
          </button>
        </form>
      </div>
      @if($audio = $lecture->getFirstMedia('lesson-audio'))
        <audio controls class="mt-4 w-full" src="{{ $audio->getUrl() }}"></audio>
      @endif
    </div>
  @endif

  <div class="bg-white rounded-[20px] shadow-md px-10 py-10 mb-8">
    @if(in_array($lecture->content_type, ['scorm', 'slides'], true))
      <div class="mb-4 flex items-center gap-2 rounded-[10px] bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700">
        <span class="font-bold uppercase">{{ $lecture->content_type === 'slides' ? 'Slides' : 'SCORM' }}</span>
        <span>Contenu importé depuis le catalogue, non modifiable ici (seul le titre peut être renommé).</span>
      </div>
      <form method="POST" action="{{ route('formateur.modules.builder.lectures.update', $lecture) }}" class="flex gap-2">
        @csrf
        @method('PUT')
        <input type="text" name="lecture_title" value="{{ $lecture->lecture_title }}" required maxlength="255"
               class="flex-1 border-0 bg-transparent p-0 font-raleway text-2xl font-bold text-bleuone focus:outline-none focus:ring-0">
        <button type="submit" class="shrink-0 rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50">Renommer</button>
      </form>
    @else
      <div data-block-editor
           data-lecture-id="{{ $lecture->id }}"
           data-update-url="{{ route('formateur.modules.builder.lectures.update', $lecture) }}"
           data-upload-url="{{ route('formateur.modules.builder.images.store', $module) }}"
           data-video-upload-url="{{ route('formateur.modules.builder.videos.store', $module) }}"
           data-scorm-upload-url="{{ route('formateur.modules.builder.scorm.store', $module) }}"
           data-initial-title="{{ $lecture->lecture_title }}"
           data-initial-blocks="{{ json_encode($initialBlocks ?? []) }}"></div>
    @endif
  </div>
</div>
@endsection
