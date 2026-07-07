@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="flex items-center justify-between">
      <div>
        <x-typography variant="titre">Modifier la formation</x-typography>
        <p class="text-sm text-gray-500 mt-1">{{ $parcours->title }}</p>
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
              <a href="{{ route('formateur.mes-formations.index') }}" class="text-orangeone hover:underline">Mes formations</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.mes-formations.show', $parcours) }}" class="text-orangeone hover:underline">{{ Str::limit($parcours->title, 30) }}</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Modifier</li>
          </ol>
        </nav>
      </div>
      <div class="flex items-center gap-3">
        <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-formation-{{ $parcours->id }}')"
                class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          Supprimer
        </button>
        <x-confirm-modal
          name="delete-formation-{{ $parcours->id }}"
          title="Supprimer définitivement cette formation ?"
          :action="route('formateur.mes-formations.destroy', $parcours)"
          method="DELETE"
          confirm-label="Supprimer"
        />
        <a href="{{ route('formateur.mes-formations.show', $parcours) }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Retour
        </a>
      </div>
    </div>
  </header>

  @include('formateur.mes-formations._form')

</div>

@endsection
