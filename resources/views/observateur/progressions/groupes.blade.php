@extends('observateur.dashboard')

@section('observateur')
<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Progression par groupe</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Consultez l’avancement des groupes qui vous sont confiés.
        </x-typography>
        <x-typography>
          Cette vue agrège les indicateurs essentiels des groupes observés, sans action de modification.
        </x-typography>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}" alt="Illustration progression par groupe" class="max-w-[260px] h-auto">
      </div>
    </div>
  </header>

  <main class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4 px-6 pt-4 pb-0">
      <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de groupes :</span>
        <span class="font-bold text-bleuone">{{ $totalGroupes ?? 0 }}</span>
      </div>
    </div>

    <div class="flex flex-wrap justify-end gap-3">
      <a href="{{ route('observateur.progressions.stagiaires') }}" class="btn-oneduc">
        <x-icons.eye-iconify class="h-4 w-4" />
        Suivi par stagiaire
      </a>
      <a href="{{ route('observateur.groupes.index') }}" class="btn-oneduc-blue">
        Groupes observés
      </a>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-3 -mt-1">
      <div class="w-full md:w-1/2">
        <label for="search" class="sr-only">Recherche</label>
        <input type="text" id="search" name="search" value="{{ $search ?? request('search') }}" placeholder="Recherche nom du groupe" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible">
      </div>
      <button type="submit" class="btn-oneduc">
        <x-icons.filter-iconify class="h-4 w-4" />
        Filtrer
      </button>
      @if(request()->filled('search'))
        <a href="{{ route('observateur.progressions.groupes') }}" class="btn-oneduc-outline">
          Réinitialiser
        </a>
      @endif
    </form>

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
              <td class="px-6 py-4 font-medium text-gray-900">{{ $g->name }}</td>
              <td class="px-6 py-4 text-center">{{ (int) ($g->stagiaires_count ?? 0) }}</td>
              <td class="px-6 py-4 text-center">{{ (int) ($g->modules_count ?? 0) }}</td>
              <td class="px-6 py-4 text-center">{{ (int) ($g->lecons_terminees_count ?? 0) }}</td>
              <td class="px-6 py-4 text-center text-gray-700">
                @if($seconds > 0)
                  {{ $hours }} h {{ str_pad((string) $mins, 2, '0', STR_PAD_LEFT) }} min
                @else
                  —
                @endif
              </td>
              <td class="px-6 py-4 text-center">
                <span class="font-semibold {{ $taux < 50 ? 'text-red-600' : 'text-vertone' }}">{{ $taux }} %</span>
              </td>
              <td class="px-6 py-4">
                <a href="{{ route('observateur.progressions.stagiaires', ['group_id' => $g->id]) }}" class="btn-oneduc !px-3 !py-1 !text-sm">
                  <x-icons.eye-iconify class="h-4 w-4" />
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

    <div>
      {{ $groupes->links('pagination::tailwind') }}
    </div>
  </main>
</div>
@endsection
