{{-- resources/views/formateur/modules-builder/index.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Mes modules</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Mes modules</p>
        <p class="text-sm text-gray-500 mt-1">Créez vos propres modules de formation (chapitres + leçons en texte riche) et assignez-les à vos groupes.</p>
        </div>
      </div>
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

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Formulaire création --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau module</p>

        <form method="POST" action="{{ route('formateur.modules.builder.store') }}" class="space-y-4">
          @csrf

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Titre du module</label>
            <input type="text" name="module_title" value="{{ old('module_title') }}" required maxlength="255"
                   placeholder="Ex : Les bases du numérique"
                   class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
            <textarea name="description" rows="3" maxlength="5000"
                      placeholder="Décrivez brièvement le contenu du module"
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('description') }}</textarea>
          </div>

          <button type="submit" class="btn-oneduc w-full !py-2.5 !text-sm">
            Créer le module
          </button>
        </form>
      </div>
    </div>

    {{-- Liste des modules --}}
    <div class="lg:col-span-2 space-y-4">
      @forelse($modules as $module)
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orangeone">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.832.477 6 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-bold text-gray-900 truncate">{{ $module->module_title }}</p>
              <p class="text-xs text-gray-500 truncate">{{ $module->description ?: 'Aucune description' }}</p>
              <p class="text-[10px] text-gray-400 mt-1">
                {{ $module->sections_count }} chapitre(s) · {{ $module->groups_count }} groupe(s) assigné(s)
              </p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route('formateur.modules.builder.edit', $module) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-orangeone px-3 py-1.5 text-xs font-bold text-white hover:opacity-90 transition">
              Modifier
            </a>
            <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-module-{{ $module->id }}')"
                    class="inline-flex items-center gap-1.5 rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50 transition">
              Supprimer
            </button>
            <x-confirm-modal
              name="delete-module-{{ $module->id }}"
              title="Supprimer ce module ?"
              message="Cette action est irréversible."
              :action="route('formateur.modules.builder.destroy', $module)"
              method="DELETE"
              confirm-label="Supprimer"
            />
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orangeone">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.832.477 6 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-700">Aucun module créé</p>
          <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour créer votre premier module.</p>
        </div>
      @endforelse
    </div>

  </div>
</div>
@endsection
