@extends('formateur.dashboard')

@section('formateur')

{{-- 🧩 EN-TÊTE DE PAGE FORMATEUR – Formations --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Mes modules de formation</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Suivez les modules que vous utilisez dans vos groupes.
      </x-typography>
      <x-typography>
        Retrouvez ici tous les modules de formation utilisés, leur statut, les groupes concernés et les stagiaires associés.
      </x-typography>

      {{-- 📍 Fil d’Ariane --}}
      <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
        <ol class="list-none p-0 inline-flex items-center space-x-1">
          <li class="flex items-center">
            <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
              </svg>
            </a>
            <span class="mx-2 text-gray-400">/</span>
          </li>
          <li class="text-gray-400">Mes modules</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

{{-- 📋 CONTENU PRINCIPAL --}}

  @if($modules->isEmpty())
      <p class="text-gray-500 font-lisible">Aucun module de formation trouvé.</p>
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
                          $stagiaires = $groupes->flatMap(fn($g) => $g->users->where('role', 'stagiaire'))->unique('id');
                          $statut = $groupes->count() > 0 ? 'Utilisé' : 'Non utilisé';
                      @endphp
                      <tr class="border-t hover:bg-gray-50">
                          <td class="px-4 py-3 font-medium">{{ $module->module_title }}</td>
                          <td class="px-4 py-3">{{ $module->created_at->format('d/m/Y') }}</td>
                          <td class="px-4 py-3">{{ $statut }}</td>
                          <td class="px-4 py-3">{{ $groupes->count() }}</td>
                          <td class="px-4 py-3">{{ $stagiaires->count() }}</td>
                          <td class="px-4 py-3 text-right">
                              <a href="#" class="text-orangeone hover:underline text-sm">Voir</a>
                          </td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
      </div>
  @endif


@endsection
