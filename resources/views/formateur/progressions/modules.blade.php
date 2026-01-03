@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
          Suivi par module
        </p>

        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Analyse des modules utilisés dans vos groupes
        </p>

        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Cette vue présente uniquement les modules réellement associés à vos groupes.
          Vous pouvez identifier les modules les plus sollicités, ceux à renforcer
          et repérer d’éventuelles difficultés pédagogiques.
        </p>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-3" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}"
                 class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Suivi par module</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}"
             alt="Illustration suivi par module"
             class="max-w-[240px] h-auto">
      </div>

    </div>
  </header>

  {{-- ACTIONS --}}
  <div class="flex flex-wrap justify-end gap-3 mb-6">
    <a href="{{ route('formateur.progressions.groupes') }}" class="btn-oneduc">
      Suivi par groupe
    </a>

    <a href="{{ route('formateur.progressions.stagiaires') }}" class="btn-oneduc">
      Suivi par stagiaire
    </a>
  </div>

  {{-- TABLEAU DES MODULES --}}
  <main class="space-y-6">
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
      <table class="min-w-full text-sm text-left text-gray-800 font-lisible">

        <thead class="bg-gray-100 text-xs text-gray-600 uppercase font-varela">
          <tr>
            <th class="px-6 py-3">Module</th>
            <th class="px-6 py-3 text-center">Groupes</th>
            <th class="px-6 py-3 text-center">Stagiaires</th>
            <th class="px-6 py-3 text-center">Leçons</th>
            <th class="px-6 py-3 text-center">Score moyen</th>
            <th class="px-6 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($modules as $m)
            @php
              $score = (int) ($m->avg_score ?? 0);
            @endphp

            <tr class="border-t hover:bg-gray-50">

              {{-- Module --}}
              <td class="px-6 py-4 font-medium text-gray-900">
                {{ $m->module_title ?? 'Module' }}
              </td>

              {{-- Groupes --}}
              <td class="px-6 py-4 text-center">
                {{ (int) ($m->groupes_count ?? 0) }}
              </td>

              {{-- Stagiaires --}}
              <td class="px-6 py-4 text-center">
                {{ (int) ($m->stagiaires_count ?? 0) }}
              </td>

              {{-- Leçons --}}
              <td class="px-6 py-4 text-center">
                {{ (int) ($m->lectures_count ?? 0) }}
              </td>

              {{-- Score moyen --}}
              <td class="px-6 py-4 text-center">
                <span class="font-semibold {{ $score < 50 ? 'text-red-600' : 'text-vertone' }}">
                  {{ $score }} %
                </span>
              </td>

              {{-- Action --}}
              <td class="px-6 py-4 text-right">
                <a href="{{ route('formateur.formations.detail', $m->id) }}"
                   class="text-orangeone hover:underline text-sm font-semibold">
                  Voir le détail
                </a>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                Aucun module associé à vos groupes.
              </td>
            </tr>
          @endforelse
        </tbody>

      </table>
    </div>
  </main>

</div>
@endsection
