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
    <a href="{{ route('formateur.modules.builder.edit', $module) }}" class="text-sm font-semibold text-gray-500 hover:text-orangeone">← Retour au plan de la formation</a>
  </header>

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
