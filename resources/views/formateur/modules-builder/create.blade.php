{{-- resources/views/formateur/modules-builder/create.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="flex items-center justify-between">
      <div>
        <x-typography variant="titre">Créer une formation</x-typography>
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.formations.index', ['tab' => 'creations']) }}" class="text-orangeone hover:underline">Créations</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Créer</li>
          </ol>
        </nav>
      </div>
      <a href="{{ route('formateur.formations.index', ['tab' => 'creations']) }}"
         class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour
      </a>
    </div>
  </header>

  <div class="bg-white rounded-[20px] shadow-md p-6 max-w-xl">
    <form method="POST" action="{{ route('formateur.modules.builder.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Titre de la formation</label>
        <input type="text" name="module_title" value="{{ old('module_title') }}" required maxlength="255"
               placeholder="Ex : Les bases du numérique"
               class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
        @error('module_title')
          <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
        <textarea name="description" rows="4" maxlength="5000"
                  placeholder="Décrivez brièvement le contenu de la formation"
                  class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('description') }}</textarea>
      </div>

      <p class="text-xs text-gray-400">Vous pourrez ajouter les chapitres et les leçons juste après la création.</p>

      <button type="submit" class="btn-oneduc !py-2.5 !text-sm">
        Créer la formation
      </button>
    </form>
  </div>

</div>

@endsection
