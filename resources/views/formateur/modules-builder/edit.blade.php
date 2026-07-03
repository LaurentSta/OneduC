{{-- resources/views/formateur/modules-builder/edit.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8"
     x-data="moduleBuilderOutline(@js([
         'moduleId' => $module->id,
         'basePath' => route('formateur.modules.builder.index'),
         'uploadUrl' => route('formateur.modules.builder.images.store', $module),
         'sections' => $module->sections->map(fn ($s) => [
             'id' => $s->id,
             'section_title' => $s->section_title,
             'position' => $s->position,
             'lectures' => $s->lectures->map(fn ($l) => [
                 'id' => $l->id,
                 'lecture_title' => $l->lecture_title,
                 'content_type' => $l->content_type,
                 'position' => $l->position,
             ])->values(),
         ])->values(),
     ]))"
     x-init="init()"
     x-on:module-builder:lecture-saved.window="onLectureSaved($event.detail)">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <button type="button"
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:inline-flex mt-1 items-center justify-center rounded-[8px] border border-gray-300 bg-white p-2 text-gray-600 hover:bg-gray-50 shrink-0"
                :aria-expanded="(!sidebarCollapsed).toString()"
                aria-controls="formateur-sidebar"
                title="Afficher/masquer le menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <div>
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
        </div>
      </div>
      <a href="{{ route('formateur.formations.preview', $module) }}" target="_blank" rel="noopener"
         class="btn-oneduc-outline !px-4 !py-2 !text-sm">Aperçu</a>
    </div>
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

  <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700" x-show="error" x-text="error" x-cloak></div>

  {{-- Réglages du module --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-1">
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
    </div>

    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Groupes assignés</p>

        @php $assignedGroupIds = $module->groups->pluck('id')->all(); @endphp

        <form method="POST" action="{{ route('formateur.modules.builder.groups.sync', $module) }}" class="space-y-3">
          @csrf
          @method('PUT')

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
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
          </div>

          @if($accessibleGroups->isNotEmpty())
            <button type="submit" class="btn-oneduc-outline !px-4 !py-2 !text-xs mt-2">Mettre à jour les groupes</button>
          @endif
        </form>
      </div>
    </div>
  </div>

  {{-- Plan du module + éditeur de leçon --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Plan du module --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-5 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Plan du module</p>

        <div class="space-y-2">
          <template x-for="(section, sectionIndex) in sections" :key="section.id">
            <div class="rounded-[14px] border border-gray-200 overflow-hidden"
                 draggable="true"
                 x-on:dragstart="dragSectionStart(sectionIndex)"
                 x-on:dragover.prevent="dragSectionOver(sectionIndex)"
                 x-on:drop.prevent="dropSection(sectionIndex)">
              <div class="flex items-center gap-2 bg-gray-50 px-3 py-2">
                <span class="cursor-move text-xs text-gray-400" title="Déplacer le chapitre">⠿</span>
                <input type="text" x-model="section.section_title"
                       x-on:blur="renameSection(section)"
                       x-on:keydown.enter.prevent="$event.target.blur()"
                       class="flex-1 min-w-0 rounded-[8px] border border-transparent bg-transparent px-2 py-1 text-sm font-bold text-gray-900 focus:border-gray-300 focus:bg-white focus:outline-none">
                <button type="button" x-on:click="requestDeleteSection(section)" class="shrink-0 text-xs text-red-400 hover:text-red-600" title="Supprimer le chapitre">✕</button>
              </div>

              <div class="divide-y divide-gray-100">
                <template x-for="(lecture, lectureIndex) in section.lectures" :key="lecture.id">
                  <div class="flex items-center gap-2 px-3 py-2 pl-6 cursor-pointer hover:bg-orange-50"
                       draggable="true"
                       x-on:dragstart.stop="dragLectureStart(sectionIndex, lectureIndex)"
                       x-on:dragover.prevent.stop="void 0"
                       x-on:drop.prevent.stop="dropLecture(sectionIndex, lectureIndex)"
                       x-on:click="selectLecture(lecture.id)"
                       :class="selectedLectureId === lecture.id ? 'bg-orange-50' : ''">
                    <span class="cursor-move text-xs text-gray-300" title="Déplacer la leçon">⠿</span>
                    <span class="flex-1 min-w-0 truncate text-sm" :class="selectedLectureId === lecture.id ? 'font-bold text-orangeone' : 'text-gray-700'" x-text="lecture.lecture_title"></span>
                    <button type="button" x-on:click.stop="requestDeleteLecture(lecture)" class="shrink-0 text-xs text-red-400 hover:text-red-600" title="Supprimer la leçon">✕</button>
                  </div>
                </template>

                <div class="flex gap-2 px-3 py-2 pl-6">
                  <input type="text" x-model="newLectureTitleBySection[section.id]" placeholder="Nouvelle leçon"
                         x-on:keydown.enter.prevent="addLecture(section)"
                         class="flex-1 min-w-0 rounded-[8px] border border-gray-200 px-2 py-1.5 text-xs focus:border-orangeone focus:outline-none">
                  <button type="button" x-on:click="addLecture(section)" class="shrink-0 rounded-[8px] border border-gray-300 bg-white px-2 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50">+</button>
                </div>
              </div>
            </div>
          </template>

          <template x-if="sections.length === 0">
            <p class="text-xs text-gray-400">Aucun chapitre pour le moment. Ajoutez-en un ci-dessous.</p>
          </template>
        </div>

        <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
          <input type="text" x-model="newSectionTitle" placeholder="Titre du chapitre"
                 x-on:keydown.enter.prevent="addSection()"
                 class="flex-1 min-w-0 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
          <button type="button" x-on:click="addSection()" class="shrink-0 btn-oneduc !px-4 !py-2 !text-sm">Ajouter</button>
        </div>
      </div>
    </div>

    {{-- Éditeur de la leçon sélectionnée --}}
    <div class="lg:col-span-2">
      <div id="lecture-editor-panels" class="space-y-4">
        @foreach($module->sections as $section)
          @foreach($section->lectures as $lecture)
            <div class="lecture-editor-panel bg-white rounded-[20px] shadow-md p-6" data-lecture-id="{{ $lecture->id }}" style="display: none;">
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
                <div data-block-editor
                     data-lecture-id="{{ $lecture->id }}"
                     data-update-url="{{ route('formateur.modules.builder.lectures.update', $lecture) }}"
                     data-upload-url="{{ route('formateur.modules.builder.images.store', $module) }}"
                     data-initial-title="{{ $lecture->lecture_title }}"
                     data-initial-blocks="{{ json_encode($lecture->content_blocks ?? []) }}"></div>
              @endif
            </div>
          @endforeach
        @endforeach
      </div>

      <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center"
           x-show="!selectedLectureId" x-cloak>
        <p class="text-sm font-semibold text-gray-700">Aucune leçon sélectionnée</p>
        <p class="text-xs text-gray-400 mt-1">Ajoutez un chapitre puis une leçon dans le plan du module pour commencer à écrire.</p>
      </div>
    </div>

  </div>

  <x-modal name="delete-confirm" maxWidth="sm">
    <div class="p-6">
      <h2 class="text-lg font-raleway font-medium text-bleuone" x-text="pendingDelete.title"></h2>
      <p class="mt-2 text-sm text-gray-600" x-text="pendingDelete.message"></p>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="btn-oneduc-outline !px-4 !py-2 !text-sm" x-on:click="$dispatch('close')">Annuler</button>
        <button type="button" class="btn-oneduc-danger !px-4 !py-2 !text-sm" x-on:click="confirmPendingDelete()">Supprimer</button>
      </div>
    </div>
  </x-modal>
</div>
@endsection
