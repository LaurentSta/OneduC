@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
          Progression par groupe
        </p>

        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Suivez l’avancement des groupes et repérez ceux qui ont besoin d’accompagnement.
        </p>

        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Cette vue synthétise l’activité par groupe : nombre de stagiaires, modules associés, leçons terminées,
          temps total passé sur la plateforme et taux de réussite estimé.
        </p>

        {{-- Indicateurs --}}
        <div class="flex flex-wrap gap-4 mt-2">
          <p class="text-sm text-gray-600 font-varela">
            Nombre total de groupes :
            <span class="text-gray-900 font-semibold">{{ $totalGroupes ?? 0 }}</span>
          </p>
        </div>

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
            <li class="text-gray-400">Progression par groupe</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}"
             alt="Illustration progression par groupe"
             class="max-w-[240px] h-auto">
      </div>

    </div>
  </header>

  {{-- ACTIONS --}}
  <div class="flex flex-wrap justify-end gap-3 mb-6">
    <a href="{{ route('formateur.progressions.stagiaires') }}" class="btn-oneduc">
      Suivi par stagiaire
    </a>

    <a href="{{ route('formateur.progressions.modules') }}" class="btn-oneduc">
      Suivi par module
    </a>
  </div>

  {{-- TABLEAU --}}
  <main class="space-y-6">
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
      <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-gray-100 text-xs text-gray-600 uppercase font-varela">
          <tr>
            <th class="px-6 py-3">Groupe</th>
            <th class="px-6 py-3 text-center">Stagiaires</th>
            <th class="px-6 py-3 text-center">Modules</th>
            <th class="px-6 py-3 text-center">Leçons terminées</th>
            <th class="px-6 py-3 text-center">Temps total site</th>
            <th class="px-6 py-3 text-center">Taux de réussite</th>
            <th class="px-6 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($groupes as $g)
            @php
              $taux = (int) ($g->taux_reussite ?? 0);
            @endphp

            <tr class="border-t hover:bg-gray-50">
              <td class="px-6 py-4 font-medium text-gray-900">
                {{ $g->name }}
              </td>

              <td class="px-6 py-4 text-center">
                {{ (int) ($g->stagiaires_count ?? 0) }}
              </td>

              <td class="px-6 py-4 text-center">
                {{ (int) ($g->modules_count ?? 0) }}
              </td>

              <td class="px-6 py-4 text-center">
                {{ (int) ($g->lecons_terminees_count ?? 0) }}
              </td>

              <td class="px-6 py-4 text-center text-gray-700">
                @php
                  // total_site_time : selon ton modèle, c’est souvent en secondes
                  // Ici on affiche en heures/minutes si c’est bien des secondes.
                  $seconds = (int) ($g->total_site_time ?? 0);
                  $hours = intdiv($seconds, 3600);
                  $mins  = intdiv($seconds % 3600, 60);
                @endphp
                @if($seconds > 0)
                  {{ $hours }} h {{ str_pad((string)$mins, 2, '0', STR_PAD_LEFT) }} min
                @else
                  —
                @endif
              </td>

              <td class="px-6 py-4 text-center">
                <span class="font-semibold {{ $taux < 50 ? 'text-red-600' : 'text-vertone' }}">
                  {{ $taux }} %
                </span>
              </td>

              <td class="px-6 py-4 text-right">
                <a href="{{ route('formateur.progressions.stagiaires', ['group_id' => $g->id]) }}"
                   class="text-orangeone hover:underline text-sm font-semibold">
                  Voir les stagiaires
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                Aucun groupe trouvé.
              </td>
            </tr>
          @endforelse
        </tbody>

      </table>
    </div>
  </main>

</div>
@endsection
