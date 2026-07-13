{{-- resources/views/formateur/modules-builder/lecture-edit.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <div class="flex items-center gap-4 border-b border-gray-200 py-3">
    <nav aria-label="Fil d'ariane" class="min-w-0 flex-1 truncate text-sm font-varela text-gray-500">
      <a href="{{ route('formateur.modules.builder.index') }}" class="text-orangeone hover:underline">Mes créations</a>
      <span class="mx-1 text-gray-400">/</span>
      <a href="{{ route('formateur.modules.builder.edit', $module) }}" class="text-orangeone hover:underline">{{ $module->module_title }}</a>
      <span class="mx-1 text-gray-400">/</span>
      <span class="text-gray-400">{{ $section->section_title }}</span>
      <span class="mx-1 text-gray-400">/</span>
      <span class="font-semibold text-bleuone">{{ $lecture->lecture_title }}</span>
    </nav>

    <div class="flex shrink-0 items-center gap-3">
      <a href="{{ route('formateur.modules.builder.edit', $module) }}" class="text-sm font-semibold text-gray-500 hover:text-orangeone">← Retour au plan de la formation</a>
      <a href="{{ route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]) }}"
         target="_blank" rel="noopener"
         class="btn-oneduc-outline !px-4 !py-1.5 !text-xs">Aperçu</a>
    </div>
  </div>

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
           data-audio-upload-url="{{ route('formateur.modules.builder.audios.store', $module) }}"
           data-audio-generate-url="{{ route('formateur.modules.builder.lectures.generate-audio', $lecture) }}"
           data-scorm-upload-url="{{ route('formateur.modules.builder.scorm.store', $module) }}"
           data-initial-title="{{ $lecture->lecture_title }}"
           data-initial-blocks="{{ json_encode($initialBlocks ?? []) }}"></div>
    @endif
  </div>
</div>
@endsection
