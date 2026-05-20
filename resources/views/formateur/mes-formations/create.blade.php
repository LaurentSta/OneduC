@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="flex items-center justify-between">
      <div>
        <x-typography variant="titre">Créer un parcours de formation</x-typography>
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
            <li class="text-gray-400">Créer</li>
          </ol>
        </nav>
      </div>
      <a href="{{ route('formateur.mes-formations.index') }}"
         class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour
      </a>
    </div>
  </header>

  @include('formateur.mes-formations._form')

</div>

@endsection
