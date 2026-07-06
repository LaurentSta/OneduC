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
        <nav class="text-sm font-varela text-gray-500 mt-1">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.modules.builder.index') }}" class="text-orangeone hover:underline">Mes créations</a>
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

  @if(session('error'))
    <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  {{-- Document continu : titre, bandeau auteur, description, plan du module --}}
  <div class="relative bg-white rounded-[20px] shadow-md px-10 py-10 mb-6"
       x-data="{
         title: @js($module->module_title),
         description: @js($module->description ?? ''),
         status: 'idle',
         savedAt: '',
         timer: null,
         hideTimer: null,
         save() {
           clearTimeout(this.hideTimer);
           this.status = 'saving';
           fetch(@js(route('formateur.modules.builder.update', $module)), {
             method: 'PUT',
             headers: {
               'Content-Type': 'application/json',
               Accept: 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
             },
             body: JSON.stringify({ module_title: this.title || 'Formation sans titre', description: this.description }),
           }).then((response) => {
             if (response.status === 419) throw new Error('expired');
             if (!response.ok) throw new Error('failed');
             this.markSaved();
           }).catch((error) => { this.status = error.message === 'expired' ? 'expired' : 'error'; });
         },
         scheduleSave() {
           clearTimeout(this.timer);
           this.timer = setTimeout(() => this.save(), 800);
         },
         markSaved() {
           this.status = 'saved';
           this.savedAt = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
           clearTimeout(this.hideTimer);
           this.hideTimer = setTimeout(() => { this.status = 'idle'; }, 2500);
         },
       }"
       x-init="$watch('title', () => scheduleSave()); $watch('description', () => scheduleSave());"
       x-on:outline:sync-status.window="$event.detail.status === 'saved' ? markSaved() : (clearTimeout(hideTimer), status = $event.detail.status)">

    <div class="flex items-center gap-2">
      <img src="{{ !empty($profileData->photo ?? null) ? asset('upload/formateur_images/'.$profileData->photo) : asset('upload/NoPhoto.png') }}"
           alt="" class="h-8 w-8 rounded-full border border-gray-200 object-cover">
      <span class="text-sm font-semibold text-gray-600">{{ auth()->user()->name ?? auth()->user()->username }}</span>
      <span class="text-gray-300">⌄</span>
    </div>

    <div class="group relative mt-3">
      <input type="text" x-model="title" placeholder="Titre de la formation" maxlength="255"
             class="w-full border-0 bg-transparent p-0 pr-8 font-raleway text-4xl font-bold text-bleuone placeholder:text-gray-300 focus:outline-none focus:ring-0">
      <x-icons.edit-iconify class="pointer-events-none absolute right-0 top-[60%] h-7 w-7 -translate-y-1/2 text-gray-300 opacity-0 transition-opacity group-hover:opacity-100" />
    </div>

    <hr class="my-5 w-24 border-t border-orangeone">

    <div class="group relative">
      <textarea x-model="description" rows="2" maxlength="5000" placeholder="Description du cours"
                x-init="$nextTick(() => { $el.style.height = $el.scrollHeight + 'px'; })"
                x-on:input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                class="w-full resize-none border-0 bg-transparent p-0 pr-8 text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-0"></textarea>
      <x-icons.edit-iconify class="pointer-events-none absolute right-0 top-0 h-4 w-4 text-gray-300 opacity-0 transition-opacity group-hover:opacity-100" />
    </div>

    <p class="absolute top-6 right-8 text-xs" :class="(status === 'error' || status === 'expired') ? 'text-red-500' : 'text-gray-400'">
      <span x-show="status === 'saving'">Enregistrement…</span>
      <span x-show="status === 'saved'"><span class="grayscale opacity-50">💾</span> Enregistré à <span x-text="savedAt"></span></span>
      <span x-show="status === 'error'">Échec de l'enregistrement</span>
      <span x-show="status === 'expired'">Session expirée — <a href="" class="underline">rechargez la page</a> pour continuer</span>
    </p>

    {{-- Plan du module : document continu, un chapitre puis ses leçons --}}
    <div class="mt-2"
         data-outline-editor
         data-module-id="{{ $module->id }}"
         data-base-path="{{ route('formateur.modules.builder.index') }}"
         data-initial-doc="{{ json_encode($outlineNodes) }}"></div>

    <div class="mt-4 flex gap-2">
      <button type="button"
              class="inline-flex shrink-0 items-center rounded-full border-2 border-orangeone bg-transparent px-3 py-1 text-xs font-semibold text-orangeone transition-colors hover:bg-orangeone hover:text-white"
              x-on:click="window.dispatchEvent(new CustomEvent('outline:request-add-lesson'))">
        + Ajouter une leçon
      </button>
      <button type="button"
              class="inline-flex shrink-0 items-center rounded-full border-2 border-orangeone bg-transparent px-3 py-1 text-xs font-semibold text-orangeone transition-colors hover:bg-orangeone hover:text-white"
              x-on:click="window.dispatchEvent(new CustomEvent('outline:request-add-chapter'))">
        + Ajouter un chapitre
      </button>
      @if($module->sections->isNotEmpty())
        <div x-data="{ sectionUrls: @js($module->sections->mapWithKeys(fn ($s) => [$s->id => route('formateur.modules.builder.lectures.generate-ia', $s)])), selectedSectionId: {{ $module->sections->first()->id }} }">
          <button type="button"
                  class="inline-flex shrink-0 items-center rounded-full border-2 border-bleuone bg-transparent px-3 py-1 text-xs font-semibold text-bleuone transition-colors hover:bg-bleuone hover:text-white"
                  x-on:click="$dispatch('open-modal', 'generate-lecture-ia')">
            + Générer une leçon (IA)
          </button>

          <x-modal name="generate-lecture-ia" maxWidth="md">
            <div class="p-6">
              <h2 class="text-lg font-raleway font-medium text-bleuone">Générer une leçon avec l'IA</h2>
              <p class="mt-2 text-sm text-gray-600">
                Importez un document (PDF, Word .docx ou texte brut) : l'IA (Mistral) en extrait le contenu et pré-remplit une nouvelle leçon.
                Relisez et ajustez le contenu avant de le proposer aux stagiaires.
              </p>

              <form method="POST" :action="sectionUrls[selectedSectionId]" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div>
                  <label class="block text-sm text-gray-600 mb-1">Chapitre de destination</label>
                  <select x-model="selectedSectionId"
                          class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">
                    @foreach($module->sections as $section)
                      <option value="{{ $section->id }}">{{ $section->section_title }}</option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="block text-sm text-gray-600 mb-1">Titre de la leçon (optionnel — sinon suggéré par l'IA)</label>
                  <input type="text" name="lecture_title" maxlength="255"
                         class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">
                </div>

                <div>
                  <label class="block text-sm text-gray-600 mb-1">Document source</label>
                  <input type="file" name="document" accept=".pdf,.docx,.txt" required
                         class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-full file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
                  <p class="mt-1 text-xs text-gray-400">PDF, Word (.docx) ou texte brut, 20 Mo max.</p>
                </div>

                <div class="flex justify-end gap-3">
                  <button type="button" class="btn-oneduc-outline !px-4 !py-2 !text-sm" x-on:click="$dispatch('close')">Annuler</button>
                  <button type="submit" class="btn-oneduc !px-4 !py-2 !text-sm">Générer la leçon</button>
                </div>
              </form>
            </div>
          </x-modal>
        </div>
      @endif
    </div>
  </div>

  {{-- Options du module & groupes assignés --}}
  <div class="bg-white rounded-[20px] shadow-md p-6 mb-8" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-left">
      <p class="font-varela text-base font-bold text-bleuone">Options de la formation</p>
      <span class="text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">⌄</span>
    </button>

    <div x-show="open" x-cloak class="mt-4 space-y-6">
    <form method="POST" action="{{ route('formateur.modules.builder.options.update', $module) }}"
          enctype="multipart/form-data" class="space-y-6">
      @csrf
      @method('PUT')

      <div>
        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-gray-400">Média</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Vidéo</label>
            <input type="file" name="module_video_file"
                   accept="video/mp4,video/x-m4v,video/quicktime,video/x-msvideo,video/webm"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-full file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
            @if($module->module_video)
              <p class="mt-1 text-xs text-gray-400">Une vidéo est déjà associée à cette formation.</p>
            @endif
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Image d'en-tête</label>
            <input type="file" name="header_image" accept="image/jpeg,image/png"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-full file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
            @if($module->header_image)
              <img src="{{ asset('storage/'.$module->header_image) }}" alt="" class="mt-2 h-16 w-16 rounded-lg border border-gray-200 object-cover">
            @endif
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Image principale</label>
            <input type="file" name="module_image" accept="image/jpeg,image/png"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-full file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
            @if($module->module_image)
              <img src="{{ asset('storage/'.$module->module_image) }}" alt="" class="mt-2 h-16 w-16 rounded-lg border border-gray-200 object-cover">
            @endif
          </div>
        </div>
      </div>

      <div>
        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-gray-400">Paramètres</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Label</label>
            <input type="text" name="label" value="{{ old('label', $module->label) }}" placeholder="Ex : Gratuit / Premium"
                   class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Durée</label>
            <input type="text" name="duree" value="{{ old('duree', $module->duree) }}" placeholder="Ex : 2h, 3 jours"
                   class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Temps estimé / question (s)</label>
            <input type="number" min="1" max="600" name="estimated_question_seconds"
                   value="{{ old('estimated_question_seconds', $module->getRawOriginal('estimated_question_seconds') ?? 30) }}"
                   placeholder="30"
                   class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">
          </div>

          <div class="flex flex-col justify-center gap-2">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" name="certificat" value="1" {{ old('certificat', $module->certificat) ? 'checked' : '' }}
                     class="rounded border-gray-300 text-orangeone focus:ring-orangeone">
              Certificat
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" name="status" value="1" {{ old('status', $module->status) ? 'checked' : '' }}
                     class="rounded border-gray-300 text-orangeone focus:ring-orangeone">
              Actif
            </label>
          </div>
        </div>
      </div>

      <div>
        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-gray-400">Contenu</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Ressources (URL ou chemin)</label>
            <input type="text" name="resources" value="{{ old('resources', $module->resources) }}" placeholder="Ex : https://... ou storage/..."
                   class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Prérequis</label>
            <textarea name="prerequi" rows="2" placeholder="Ex : savoir utiliser la souris..."
                      class="w-full rounded-lg border border-gray-200 px-3 py-1.5 text-sm focus:border-orangeone focus:outline-none focus:ring-1 focus:ring-orangeone">{{ old('prerequi', $module->prerequi) }}</textarea>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-oneduc-outline !px-4 !py-2 !text-xs">Enregistrer les options</button>
    </form>

    <hr class="border-t border-orangeone">

    <div>
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
    </div>
  </div>

  {{-- Confirmation de suppression (chapitre ou leçon), déclenchée depuis l'éditeur outline --}}
  <div x-data="{
        pendingDelete: { type: null, id: null, clientKey: null, title: '', message: '' },
        deleteError: '',
        async confirmDelete() {
          this.deleteError = '';
          try {
            if (this.pendingDelete.id) {
              const base = @js(route('formateur.modules.builder.index'));
              const url = this.pendingDelete.type === 'section'
                ? base + '/sections/' + this.pendingDelete.id
                : base + '/lectures/' + this.pendingDelete.id;
              const response = await fetch(url, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
              });
              if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                this.deleteError = data.message || 'Suppression impossible.';
                return;
              }
            }
            window.dispatchEvent(new CustomEvent('outline:deleted', { detail: { clientKey: this.pendingDelete.clientKey } }));
            this.$dispatch('close');
          } catch (e) {
            this.deleteError = 'Suppression impossible.';
          }
        },
      }"
      x-on:outline:request-delete.window="
        deleteError = '';
        pendingDelete = {
          type: $event.detail.type,
          id: $event.detail.id,
          clientKey: $event.detail.clientKey,
          title: $event.detail.type === 'section' ? 'Supprimer ce chapitre ?' : 'Supprimer cette leçon ?',
          message: 'Cette action est irréversible.',
        };
        $dispatch('open-modal', 'delete-confirm');
      ">
    <x-modal name="delete-confirm" maxWidth="sm">
      <div class="p-6">
        <h2 class="text-lg font-raleway font-medium text-bleuone" x-text="pendingDelete.title"></h2>
        <p class="mt-2 text-sm text-gray-600" x-text="pendingDelete.message"></p>
        <p x-show="deleteError" x-text="deleteError" class="mt-2 text-sm font-semibold text-red-600"></p>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="btn-oneduc-outline !px-4 !py-2 !text-sm" x-on:click="$dispatch('close')">Annuler</button>
          <button type="button" class="btn-oneduc-danger !px-4 !py-2 !text-sm" x-on:click="confirmDelete()">Supprimer</button>
        </div>
      </div>
    </x-modal>
  </div>
</div>
@endsection
