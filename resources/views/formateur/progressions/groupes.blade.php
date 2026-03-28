@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Progression par groupe</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Suivez l’avancement des groupes et repérez ceux qui ont besoin d’accompagnement.
        </x-typography>
        <x-typography>
          Cette vue synthétise l’activité par groupe : stagiaires, modules, leçons terminées, temps total passé et taux de réussite.
        </x-typography>

        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
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

      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}"
             alt="Illustration progression par groupe"
             class="max-w-[260px] h-auto">
      </div>

    </div>
  </header>

  <main class="space-y-8">

    {{-- Recherche --}}
    <form method="GET" class="space-y-3">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-full md:flex-1 md:min-w-[280px]">
        <label for="search" class="sr-only">Recherche</label>
        <input type="text"
               id="search"
               name="search"
               value="{{ $search ?? request('search') }}"
               placeholder="Recherche nom du groupe"
               class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
        </div>

        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md border border-orangeone bg-orangeone px-5 text-sm font-varela text-white transition hover:bg-white hover:text-orangeone">
          Filtrer
        </button>

        @if(request()->filled('search'))
          <a href="{{ route('formateur.progressions.groupes') }}"
             class="inline-flex h-10 items-center justify-center rounded-md border border-gray-300 bg-white px-5 text-sm font-varela text-gray-700 transition hover:border-orangeone hover:text-orangeone">
            Réinitialiser
          </a>
        @endif

        <a href="{{ route('formateur.progressions.stagiaires') }}"
           class="inline-flex h-10 items-center justify-center rounded-md border border-orangeone bg-orangeone px-5 text-sm font-varela text-white transition hover:bg-white hover:text-orangeone">
          Suivi par stagiaire
        </a>
        <a href="{{ route('formateur.progressions.modules') }}"
           class="inline-flex h-10 items-center justify-center rounded-md border border-orangeone bg-orangeone px-5 text-sm font-varela text-white transition hover:bg-white hover:text-orangeone">
          Suivi par module
        </a>
      </div>

      @if(request('search'))
        <p class="pt-1 text-sm text-gray-600 font-varela">
          Recherche active :
          <span class="text-orangeone font-semibold">{{ request('search') }}</span>
        </p>
      @endif
    </form>

    {{-- Tableau --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px] border-2 border-bleuone/20">
      <table class="min-w-full bg-white text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-bleuone uppercase text-xs text-white font-varela sticky top-0 z-10">
          <tr>
            <th class="px-6 py-3">#</th>
            <th class="px-6 py-3">Groupe</th>
            <th class="px-6 py-3 text-center">Stagiaires</th>
            <th class="px-6 py-3 text-center">Modules</th>
            <th class="px-6 py-3 text-center">Leçons terminées</th>
            <th class="px-6 py-3 text-center">Temps total site</th>
            <th class="px-6 py-3 text-center">Taux de réussite</th>
            <th class="px-6 py-3">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($groupes as $index => $g)
            @php
              $taux = (int) ($g->taux_reussite ?? 0);
              $seconds = (int) ($g->total_site_time ?? 0);
              $hours = intdiv($seconds, 3600);
              $mins = intdiv($seconds % 3600, 60);
            @endphp

            <tr class="border-t {{ $index % 2 === 0 ? 'bg-white' : 'bg-orangeone/8' }} hover:bg-orangeone/15 transition-colors">
              <td class="px-6 py-4 font-medium">{{ $groupes->firstItem() + $index }}</td>

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
                @if($seconds > 0)
                  {{ $hours }} h {{ str_pad((string) $mins, 2, '0', STR_PAD_LEFT) }} min
                @else
                  —
                @endif
              </td>

              <td class="px-6 py-4 text-center">
                <span class="font-semibold {{ $taux < 50 ? 'text-red-600' : 'text-vertone' }}">
                  {{ $taux }} %
                </span>
              </td>

              <td class="px-6 py-4">
                <a href="{{ route('formateur.progressions.stagiaires', ['group_id' => $g->id]) }}"
                   class="btn-oneduc px-3 py-1 text-xs text-white bg-orangeone border-orangeone hover:bg-white hover:text-orangeone">
                  Voir les stagiaires
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-6 text-center text-gray-500">Aucun groupe trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de groupes :</span>
        <span class="font-bold text-bleuone">{{ $totalGroupes ?? 0 }}</span>
      </div>
    </div>

    {{-- Pagination --}}
    <div>
      {{ $groupes->links('pagination::tailwind') }}
    </div>

  </main>

</div>
@endsection
