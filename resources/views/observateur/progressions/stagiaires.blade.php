@extends('observateur.dashboard')

@section('observateur')
<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Progression des stagiaires</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">Suivi individuel des apprenants observés</p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Cette vue vous donne accès aux indicateurs de progression des stagiaires appartenant aux groupes observés.
        </p>
      </div>
      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}" alt="Illustration progression des stagiaires" class="max-w-[240px] h-auto">
      </div>
    </div>
  </header>

  <main class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
      <div class="flex flex-wrap gap-3">
        <a href="{{ route('observateur.progressions.groupes') }}" class="btn-oneduc">Voir les groupes</a>
        <a href="{{ route('observateur.progressions.stagiaires') }}" class="btn-oneduc">Tous les stagiaires</a>
      </div>

      <form method="GET" action="{{ route('observateur.progressions.stagiaires') }}" class="flex flex-wrap items-center gap-3">
        <label for="group_id" class="text-sm font-varela text-gray-700">Filtrer par groupe</label>
        <select id="group_id" name="group_id" class="w-full md:w-[320px] px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible">
          <option value="">Tous les groupes</option>
          @foreach($groupes as $g)
            <option value="{{ $g->id }}" @selected((int)$g->id === (int)($groupId ?? 0))>{{ $g->name }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn-oneduc">Appliquer</button>
      </form>
    </div>

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
              <td class="px-6 py-4 font-medium">{{ trim(($s->prenom ?? '').' '.($s->name ?? '')) ?: 'Inconnu' }}</td>
              <td class="px-6 py-4 text-gray-700">{{ $s->email ?? '—' }}</td>
              <td class="px-6 py-4">
                @forelse($s->groupesStagiaire as $g)
                  <span class="inline-block bg-vertone/10 text-vertone text-xs font-varela px-2 py-1 rounded-full mr-1 mb-1">{{ $g->name }}</span>
                @empty
                  <span class="text-gray-400 text-xs italic">Aucun</span>
                @endforelse
              </td>
              <td class="px-6 py-4 text-center">{{ $s->lecons_terminees_count ?? 0 }}</td>
              <td class="px-6 py-4 text-center">
                <span class="font-semibold {{ ($s->taux_reussite ?? 0) < 50 ? 'text-red-600' : 'text-vertone' }}">{{ $s->taux_reussite ?? 0 }} %</span>
              </td>
              <td class="px-6 py-4 text-center">
                @if(!empty($s->total_site_time))
                  {{ gmdate('H\hi', (int)$s->total_site_time) }}
                @else
                  —
                @endif
              </td>
              <td class="px-6 py-4 text-center text-gray-600">
                @if(!empty($s->last_completed_at))
                  {{ \Carbon\Carbon::parse($s->last_completed_at)->format('d/m/Y') }}
                @else
                  —
                @endif
              </td>
              <td class="px-6 py-4 text-right">
                <a href="{{ route('observateur.progressions.stagiaire', $s->id) }}" class="text-orangeone hover:underline text-sm font-semibold">
                  Voir le détail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-6 text-center text-gray-500">Aucun stagiaire trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div>
      {{ $stagiaires->links('pagination::tailwind') }}
    </div>
  </main>
</div>
@endsection
