@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Texte --}}
      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
          Progression des stagiaires
        </p>

        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Suivi individuel des apprenants
        </p>

        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Cette vue vous permet de repérer rapidement les stagiaires actifs, ceux qui progressent,
          et ceux qui ont besoin d’un accompagnement.
        </p>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-3" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline">
                Accueil
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Progression</li>
          </ol>
        </nav>
      </div>

      {{-- Illustration --}}
      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}"
             alt="Illustration progression des stagiaires"
             class="max-w-[240px] h-auto">
      </div>

    </div>
  </header>

  {{-- CONTENU --}}
  <main class="space-y-6">

    <form method="GET" action="{{ route('formateur.progressions.stagiaires') }}" class="space-y-3">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-full md:flex-1 md:min-w-[240px]">
          <label for="search" class="sr-only">Recherche stagiaire</label>
          <input type="text"
                 id="search"
                 name="search"
                 value="{{ $search ?? request('search') }}"
                 placeholder="Recherche prénom, nom ou email"
                 class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
        </div>

        <div class="w-full md:w-[280px]">
          <label for="group_id" class="sr-only">Filtrer par groupe</label>
          <select id="group_id"
                  name="group_id"
                  class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
            <option value="">Tous les groupes</option>
            @foreach($groupes as $g)
              <option value="{{ $g->id }}" @selected((int)$g->id === (int)($groupId ?? 0))>
                {{ $g->name }}
              </option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="btn-oneduc h-10 !text-sm">
          Filtrer
        </button>

        @if(request()->filled('search') || !empty($groupId))
          <a href="{{ route('formateur.progressions.stagiaires') }}"
             class="btn-oneduc-outline h-10 !text-sm">
            Réinitialiser
          </a>
        @endif

        <a href="{{ route('formateur.progressions.groupes') }}"
           class="btn-oneduc h-10 !text-sm">
          Suivi par groupe
        </a>

        <a href="{{ route('formateur.progressions.stagiaires') }}"
           class="btn-oneduc h-10 !text-sm">
          Tous les stagiaires
        </a>
      </div>

      @if(request('search') || !empty($groupId))
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 pt-1">
          @if(request('search'))
            <p class="text-sm text-gray-600 font-varela">
              Recherche active :
              <span class="text-orangeone font-semibold">{{ request('search') }}</span>
            </p>
          @endif

          @if(!empty($groupId))
            <p class="text-sm text-gray-600 font-varela">
              Groupe sélectionné :
              <span class="text-orangeone font-semibold">
                {{ optional($groupes->firstWhere('id', (int)$groupId))->name }}
              </span>
            </p>
          @endif
        </div>
      @endif
    </form>

    {{-- Tableau stagiaires --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
      <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-gray-100 text-xs text-gray-600 uppercase font-varela">
          <tr>
            <th class="px-6 py-3">Stagiaire</th>
            <th class="px-6 py-3">Email</th>
            <th class="px-6 py-3">Groupe(s)</th>
            <th class="px-6 py-3 text-center">Leçons terminées</th>
            <th class="px-6 py-3 text-center">Taux de réussite</th>
            <th class="px-6 py-3 text-center">Temps sur la plateforme</th>
            <th class="px-6 py-3 text-center">Dernière activité</th>
            <th class="px-6 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($stagiaires as $s)
            <tr class="border-t hover:bg-gray-50">

              {{-- Identité --}}
              <td class="px-6 py-4 font-medium">
                {{ trim(($s->prenom ?? '').' '.($s->name ?? '')) ?: 'Inconnu' }}
              </td>

              {{-- Email --}}
              <td class="px-6 py-4 text-gray-700">
                {{ $s->email ?? '—' }}
              </td>

              {{-- Groupes --}}
              <td class="px-6 py-4">
                @forelse($s->groupesStagiaire as $g)
                  <span class="inline-block bg-vertone/10 text-vertone text-xs font-varela px-2 py-1 rounded-full mr-1 mb-1">
                    {{ $g->name }}
                  </span>
                @empty
                  <span class="text-gray-400 text-xs italic">Aucun</span>
                @endforelse
              </td>

              {{-- Leçons terminées --}}
              <td class="px-6 py-4 text-center">
                {{ $s->lecons_terminees_count ?? 0 }}
              </td>

              {{-- Taux de réussite --}}
              <td class="px-6 py-4 text-center">
                <span class="font-semibold {{ ($s->taux_reussite ?? 0) < 50 ? 'text-red-600' : 'text-vertone' }}">
                  {{ $s->taux_reussite ?? 0 }} %
                </span>
              </td>

              {{-- Temps total --}}
              <td class="px-6 py-4 text-center">
                @if(!empty($s->total_site_time))
                  {{ gmdate('H\hi', (int)$s->total_site_time) }}
                @else
                  —
                @endif
              </td>

              {{-- Dernière activité --}}
              <td class="px-6 py-4 text-center text-gray-600">
                @if(!empty($s->last_completed_at))
                  {{ \Carbon\Carbon::parse($s->last_completed_at)->format('d/m/Y') }}
                @else
                  —
                @endif
              </td>

              {{-- Action --}}
              <td class="px-6 py-4 text-right">
                <a href="{{ route('formateur.progressions.stagiaire', ['user' => $s->id, 'group_id' => $groupId ?: null]) }}"
                   class="text-orangeone hover:underline text-sm font-semibold">
                  Voir le détail
                </a>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                Aucun stagiaire trouvé.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de stagiaires :</span>
        <span class="font-bold text-bleuone">{{ $totalStagiaires ?? $stagiaires->count() }}</span>
      </div>
      <div class="inline-flex items-center gap-2 rounded-full border border-orangeone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de groupes :</span>
        <span class="font-bold text-orangeone">{{ $totalGroupes ?? $groupes->count() }}</span>
      </div>
    </div>

  </main>

</div>
@endsection
