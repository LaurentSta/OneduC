{{-- resources/views/formateur/modules-builder/edit.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8" x-data="{ openSections: {{ $module->sections->isNotEmpty() ? '['.$module->sections->first()->id.']' : '[]' }} }">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <nav class="text-sm font-varela text-gray-500 mb-2">
      <ol class="inline-flex items-center space-x-1">
        <li>
          <a href="{{ route('formateur.modules.builder.index') }}" class="text-orangeone hover:underline">Mes modules</a>
        </li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li class="text-gray-400">{{ $module->module_title }}</li>
      </ol>
    </nav>
    <p class="font-raleway text-2xl text-bleuone">{{ $module->module_title }}</p>
  </header>

  @if(session('success'))
    <div class="mb-6 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    <div class="lg:col-span-1 space-y-6">
      {{-- Informations générales --}}
      <div class="bg-white rounded-[20px] shadow-md p-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Informations générales</p>
        <form method="POST" action="{{ route('formateur.modules.builder.update', $module) }}" class="space-y-4">
          @csrf
          @method('PUT')

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Titre du module</label>
            <input type="text" name="module_title" value="{{ old('module_title', $module->module_title) }}" required maxlength="255"
                   class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
            <textarea name="description" rows="3" maxlength="5000"
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('description', $module->description) }}</textarea>
          </div>

          <button type="submit" class="btn-oneduc w-full !py-2.5 !text-sm">Enregistrer</button>
        </form>
      </div>

      {{-- Groupes assignés --}}
      <div class="bg-white rounded-[20px] shadow-md p-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Groupes assignés</p>

        @php $assignedGroupIds = $module->groups->pluck('id')->all(); @endphp

        <form method="POST" action="{{ route('formateur.modules.builder.groups.sync', $module) }}" class="space-y-3">
          @csrf
          @method('PUT')

          @forelse($accessibleGroups as $group)
            <label class="flex items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" name="group_ids[]" value="{{ $group->id }}"
                     {{ in_array($group->id, $assignedGroupIds) ? 'checked' : '' }}
                     class="rounded border-gray-300 text-orangeone focus:ring-orangeone">
              {{ $group->name }}
            </label>
          @empty
            <p class="text-xs text-gray-400">Vous n'avez aucun groupe pour le moment.</p>
          @endforelse

          @if($accessibleGroups->isNotEmpty())
            <button type="submit" class="btn-oneduc-outline w-full !py-2 !text-xs mt-2">Mettre à jour les groupes</button>
          @endif
        </form>
      </div>
    </div>

    <div class="lg:col-span-2 space-y-4">

      {{-- Chapitres --}}
      @foreach($module->sections as $section)
        <div class="bg-white rounded-[20px] shadow-md overflow-hidden">
          <button type="button"
                  @click="openSections.includes({{ $section->id }}) ? openSections = openSections.filter(id => id !== {{ $section->id }}) : openSections.push({{ $section->id }})"
                  class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left transition-colors duration-300 hover:bg-gray-50">
            <span class="text-sm font-bold text-gray-900">{{ $section->section_title }}</span>
            <span class="flex items-center gap-3 text-xs text-gray-400">
              {{ $section->lectures->count() }} leçon(s)
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform duration-300 ease-out"
                   :class="openSections.includes({{ $section->id }}) ? 'rotate-180' : ''"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </span>
          </button>

          <div x-show="openSections.includes({{ $section->id }})" x-collapse.duration.400ms class="border-t border-gray-100 px-5 py-4 space-y-4">

            <form method="POST" action="{{ route('formateur.modules.builder.sections.update', $section) }}" class="flex gap-2">
              @csrf
              @method('PUT')
              <input type="text" name="section_title" value="{{ $section->section_title }}" required maxlength="255"
                     class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              <button type="submit" class="rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50">Renommer</button>
            </form>

            {{-- Leçons existantes --}}
            @foreach($section->lectures as $lecture)
              <div class="rounded-[14px] border border-gray-200 p-4">
                @if(in_array($lecture->content_type, ['scorm', 'slides'], true))
                  <div class="mb-3 flex items-center gap-2 rounded-[10px] bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-700">
                    <span class="font-bold uppercase">{{ $lecture->content_type === 'slides' ? 'Slides' : 'SCORM' }}</span>
                    <span>Contenu importé depuis le catalogue, non modifiable ici (seul le titre peut être renommé).</span>
                  </div>
                  <form method="POST" action="{{ route('formateur.modules.builder.lectures.update', $lecture) }}" class="flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="lecture_title" value="{{ $lecture->lecture_title }}" required maxlength="255"
                           class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm font-semibold focus:border-orangeone focus:outline-none">
                    <button type="submit" class="rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50">Renommer</button>
                  </form>
                @else
                  <form method="POST" action="{{ route('formateur.modules.builder.lectures.update', $lecture) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <input type="text" name="lecture_title" value="{{ $lecture->lecture_title }}" required maxlength="255"
                           class="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm font-semibold focus:border-orangeone focus:outline-none">

                    <div data-block-editor
                         data-input-name="content_blocks"
                         data-initial-blocks="{{ json_encode($lecture->content_blocks ?? []) }}"
                         data-upload-url="{{ route('formateur.modules.builder.images.store', $module) }}"></div>

                    <div class="flex justify-end gap-2">
                      <button type="submit" class="btn-oneduc !px-4 !py-1.5 !text-xs">Enregistrer la leçon</button>
                    </div>
                  </form>
                @endif
                <div class="mt-2">
                  <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-lecture-{{ $lecture->id }}')"
                          class="text-xs font-semibold text-red-500 hover:underline">Supprimer la leçon</button>
                  <x-confirm-modal
                    name="delete-lecture-{{ $lecture->id }}"
                    title="Supprimer cette leçon ?"
                    :action="route('formateur.modules.builder.lectures.destroy', $lecture)"
                    method="DELETE"
                    confirm-label="Supprimer"
                  />
                </div>
              </div>
            @endforeach

            {{-- Nouvelle leçon --}}
            <div class="rounded-[14px] border border-dashed border-gray-300 p-4">
              <p class="text-xs font-semibold text-gray-500 mb-2">Ajouter une leçon</p>
              <form method="POST" action="{{ route('formateur.modules.builder.lectures.store', $section) }}" class="space-y-3">
                @csrf
                <input type="text" name="lecture_title" placeholder="Titre de la leçon" required maxlength="255"
                       class="w-full rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">

                <div data-block-editor
                     data-input-name="content_blocks"
                     data-initial-blocks="[]"
                     data-upload-url="{{ route('formateur.modules.builder.images.store', $module) }}"></div>

                <div class="flex justify-end">
                  <button type="submit" class="btn-oneduc !px-4 !py-1.5 !text-xs">Ajouter la leçon</button>
                </div>
              </form>
            </div>

            <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-section-{{ $section->id }}')"
                    class="text-xs font-semibold text-red-500 hover:underline">Supprimer le chapitre</button>
            <x-confirm-modal
              name="delete-section-{{ $section->id }}"
              title="Supprimer ce chapitre ?"
              message="Toutes ses leçons seront également supprimées."
              :action="route('formateur.modules.builder.sections.destroy', $section)"
              method="DELETE"
              confirm-label="Supprimer"
            />
          </div>
        </div>
      @endforeach

      {{-- Nouveau chapitre --}}
      <div class="bg-white rounded-[20px] shadow-md p-5">
        <p class="text-sm font-bold text-gray-900 mb-3">Ajouter un chapitre</p>
        <form method="POST" action="{{ route('formateur.modules.builder.sections.store', $module) }}" class="flex gap-2">
          @csrf
          <input type="text" name="section_title" placeholder="Titre du chapitre" required maxlength="255"
                 class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
          <button type="submit" class="btn-oneduc !px-4 !py-2 !text-sm">Ajouter</button>
        </form>
      </div>

    </div>
  </div>
</div>
@endsection
