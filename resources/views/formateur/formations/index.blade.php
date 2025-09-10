@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE FORMATEUR – Formations --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Bloc texte (9) --}}
      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
          Mes modules de formation
        </p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Suivez les modules que vous utilisez dans vos groupes.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Retrouvez ici tous les modules de formation utilisés, leur statut, les groupes concernés et les stagiaires associés.
        </p>

        {{-- 📍 Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Mes modules</li>
          </ol>
        </nav>
      </div>

      {{-- Bloc image (3) --}}
      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}"
             alt="Illustration des modules de formation"
             class="max-w-[256px] h-auto">
      </div>

    </div>
  </header>

  {{-- 📋 CONTENU PRINCIPAL --}}
  <main class="space-y-8">

    @if($modules->isEmpty())
      <div class="bg-white rounded-[20px] shadow-md p-6">
        <p class="text-gray-500 font-lisible">Aucun module de formation trouvé.</p>
      </div>
    @else
      <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
        <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
          <thead class="bg-gray-100 text-xs text-gray-600 uppercase font-varela">
            <tr>
              <th class="px-4 py-3">Titre</th>
              <th class="px-4 py-3">Date de création</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3">Groupes associés</th>
              <th class="px-4 py-3">Stagiaires total</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($modules as $module)
              @php
                $groupes = $module->groups;
                $stagiaires = $groupes->flatMap(fn($g) => $g->users->where('role','stagiaire'))->unique('id');

                if ($groupes->count() > 0) {
                    $statut = 'utilise';
                } elseif ($module->status == 1) {
                    $statut = 'non_utilise';
                } else {
                    $statut = 'indisponible';
                }
              @endphp

              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">{{ $module->module_title }}</td>
                <td class="px-4 py-3">{{ $module->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3">
  @if($groupes->count() > 0)
    <span class="inline-flex items-center px-2 py-1 text-green-700 bg-green-100 rounded-full text-xs font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
      Utilisé
    </span>
  @elseif($module->status == 1)
    <span class="inline-flex items-center px-2 py-1 text-blue-700 bg-blue-100 rounded-full text-xs font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Non utilisé
    </span>
  @else
    <span class="inline-flex items-center px-2 py-1 text-red-700 bg-red-100 rounded-full text-xs font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      Indisponible
    </span>
  @endif
</td>

                <td class="px-4 py-3">{{ $groupes->count() }}</td>
                <td class="px-4 py-3">{{ $stagiaires->count() }}</td>
                <td class="px-4 py-3 text-right">
                @if($statut === 'indisponible')
                  <span class="text-gray-400 line-through text-sm cursor-not-allowed select-none">Voir</span>
                @else
                  <a href="{{ route('formateur.formations.detail', $module->id) }}" class="text-orangeone hover:underline text-sm">Voir</a>
                @endif
              </td>



              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

  </main>
</div>
@endsection
