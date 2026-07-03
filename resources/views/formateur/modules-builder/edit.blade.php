{{-- resources/views/formateur/modules-builder/edit.blade.php --}}
@extends('formateur.dashboard')

@php
  $outlineNodes = [];
  foreach ($module->sections as $section) {
      $outlineNodes[] = [
          'type' => 'chapterHeading',
          'sectionId' => $section->id,
          'title' => $section->section_title,
      ];
      foreach ($section->lectures as $lecture) {
          $outlineNodes[] = [
              'type' => 'lessonItem',
              'lectureId' => $lecture->id,
              'contentType' => $lecture->content_type,
              'title' => $lecture->lecture_title,
          ];
      }
  }
  $assignedGroupIds = $module->groups->pluck('id')->all();
@endphp

@section('formateur')
<div class="w-full px-6 lg:px-8">

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
        <nav class="text-sm font-varela text-gray-500 mt-1">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.modules.builder.index') }}" class="text-orangeone hover:underline">Mes modules</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $module->module_title }}</li>
          </ol>
        </nav>
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

  {{-- Document continu : titre, bandeau auteur, description, plan du module --}}
  <div class="bg-white rounded-[20px] shadow-md px-10 py-10 mb-6"
       x-data="{
         title: @js($module->module_title),
         description: @js($module->description ?? ''),
         status: 'idle',
         savedAt: '',
         timer: null,
         save() {
           this.status = 'saving';
           fetch(@js(route('formateur.modules.builder.update', $module)), {
             method: 'PUT',
             headers: {
               'Content-Type': 'application/json',
               Accept: 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
             },
             body: JSON.stringify({ module_title: this.title || 'Module sans titre', description: this.description }),
           }).then((response) => {
             if (!response.ok) throw new Error('save failed');
             this.status = 'saved';
             this.savedAt = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
           }).catch(() => { this.status = 'error'; });
         },
         scheduleSave() {
           clearTimeout(this.timer);
           this.timer = setTimeout(() => this.save(), 800);
         },
       }"
       x-init="$watch('title', () => scheduleSave()); $watch('description', () => scheduleSave());">

    <input type="text" x-model="title" placeholder="Titre du module" maxlength="255"
           class="w-full border-0 bg-transparent p-0 font-raleway text-4xl font-bold text-bleuone placeholder:text-gray-300 focus:outline-none focus:ring-0">

    <div class="mt-3 flex items-center gap-2">
      <img src="{{ !empty($profileData->photo ?? null) ? asset('upload/formateur_images/'.$profileData->photo) : asset('upload/NoPhoto.png') }}"
           alt="" class="h-8 w-8 rounded-full border border-gray-200 object-cover">
      <span class="text-sm font-semibold text-gray-600">{{ auth()->user()->name ?? auth()->user()->username }}</span>
      <span class="text-gray-300">⌄</span>
    </div>

    <hr class="my-5 w-24 border-t-2 border-orangeone">

    <textarea x-model="description" rows="2" maxlength="5000" placeholder="Description du cours"
              x-init="$nextTick(() => { $el.style.height = $el.scrollHeight + 'px'; })"
              x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
              class="w-full resize-none border-0 bg-transparent p-0 text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-0"></textarea>

    <p class="mt-1 text-xs" :class="status === 'error' ? 'text-red-500' : 'text-gray-400'">
      <span x-show="status === 'saving'">Enregistrement…</span>
      <span x-show="status === 'saved'">Enregistré à <span x-text="savedAt"></span></span>
      <span x-show="status === 'error'">Échec de l'enregistrement</span>
    </p>

    {{-- Plan du module : document continu, un chapitre puis ses leçons --}}
    <div class="mt-6"
         data-outline-editor
         data-module-id="{{ $module->id }}"
         data-base-path="{{ route('formateur.modules.builder.index') }}"
         data-initial-doc="{{ json_encode($outlineNodes) }}"></div>

    <p class="mt-4 text-xs text-gray-400">
      Entrée pour ajouter une leçon · Maj+Entrée pour transformer la ligne en chapitre · Alt+↑/↓ pour réordonner
    </p>
  </div>

  {{-- Groupes assignés --}}
  <div class="bg-white rounded-[20px] shadow-md p-6 mb-8">
    <p class="font-varela text-base font-bold text-bleuone mb-4">Groupes assignés</p>

    <form method="POST" action="{{ route('formateur.modules.builder.groups.sync', $module) }}" class="space-y-3">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
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

  {{-- Confirmation de suppression (chapitre ou leçon), déclenchée depuis l'éditeur outline --}}
  <div x-data="{
        pendingDelete: { type: null, id: null, clientKey: null, title: '', message: '' },
        async confirmDelete() {
          try {
            if (this.pendingDelete.id) {
              const base = @js(route('formateur.modules.builder.index'));
              const url = this.pendingDelete.type === 'section'
                ? base + '/sections/' + this.pendingDelete.id
                : base + '/lectures/' + this.pendingDelete.id;
              await fetch(url, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
              });
            }
            window.dispatchEvent(new CustomEvent('outline:deleted', { detail: { clientKey: this.pendingDelete.clientKey } }));
            this.$dispatch('close');
          } catch (e) {}
        },
      }"
      x-on:outline:request-delete.window="
        pendingDelete = {
          type: $event.detail.type,
          id: $event.detail.id,
          clientKey: $event.detail.clientKey,
          title: $event.detail.type === 'section' ? 'Supprimer ce chapitre ?' : 'Supprimer cette leçon ?',
          message: $event.detail.type === 'section' ? 'Toutes ses leçons seront également supprimées.' : 'Cette action est irréversible.',
        };
        $dispatch('open-modal', 'delete-confirm');
      ">
    <x-modal name="delete-confirm" maxWidth="sm">
      <div class="p-6">
        <h2 class="text-lg font-raleway font-medium text-bleuone" x-text="pendingDelete.title"></h2>
        <p class="mt-2 text-sm text-gray-600" x-text="pendingDelete.message"></p>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="btn-oneduc-outline !px-4 !py-2 !text-sm" x-on:click="$dispatch('close')">Annuler</button>
          <button type="button" class="btn-oneduc-danger !px-4 !py-2 !text-sm" x-on:click="confirmDelete()">Supprimer</button>
        </div>
      </div>
    </x-modal>
  </div>
</div>
@endsection
