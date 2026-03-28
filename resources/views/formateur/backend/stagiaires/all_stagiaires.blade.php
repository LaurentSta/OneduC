<!-- /home/laurents/Oneduc_Dev/resources/views/formateur/backend/stagiaires/all_stagiaires.blade.php -->
@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE FORMATEUR – Stagiaires --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Bloc texte --}}
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Mes stagiaires</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Gérer l’ensemble de vos apprenants en un coup d’œil.
        </x-typography>
        <x-typography>
          Depuis cette page, vous pouvez modifier, supprimer ou filtrer les stagiaires rattachés à vos groupes.
        </x-typography>

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
            <li class="text-gray-400">Mes stagiaires</li>
          </ol>
        </nav>
      </div>

      {{-- Bloc image --}}
      
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/ListesStagiaires.svg') }}"
             alt="Illustration des modules de formation"
             class="max-w-[400px] h-auto">
      </div>

    </div>
  </header>

  {{-- 📋 CONTENU PRINCIPAL --}}
  <main class="space-y-8">
    @if (session('success'))
      <div class="rounded-[16px] border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
      </div>
    @endif

    {{-- 🔎 Barre de recherche --}}
    <form method="GET" class="space-y-3">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-full md:w-[180px]">
        <label for="per_page" class="sr-only">Nombre de stagiaires à afficher</label>
        <select id="per_page"
                name="per_page"
                class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
          @foreach ($allowedPerPage as $option)
            <option value="{{ $option }}" @selected($perPage === $option)>
              {{ $option }} par page
            </option>
          @endforeach
        </select>
        </div>

        <div class="w-full md:flex-1 md:min-w-[260px]">
          <label for="search" class="sr-only">Recherche prénom</label>
          <input type="text"
                id="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Recherche prénom"
                class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
        </div>

        <div class="w-full md:w-[280px]">
          <label for="group_id" class="sr-only">Recherche de groupe</label>
          <select id="group_id"
                  name="group_id"
                  class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
            <option value="">Recherche de groupe</option>
            @foreach ($groupes as $groupe)
              <option value="{{ $groupe->id }}" @selected((string)request('group_id') === (string)$groupe->id)>
                {{ $groupe->name }}
              </option>
            @endforeach
          </select>
        </div>

        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-md border border-orangeone bg-orangeone px-5 text-sm font-varela text-white transition hover:bg-white hover:text-orangeone">
          Filtrer
        </button>

        @if(request()->filled('search') || request()->filled('group_id') || request('per_page', 10) != 10)
          <a href="{{ route('formateur.stagiaires.index') }}"
            class="inline-flex h-10 items-center justify-center rounded-md border border-gray-300 bg-white px-5 text-sm font-varela text-gray-700 transition hover:border-orangeone hover:text-orangeone">
            Réinitialiser
          </a>
        @endif
      </div>

      @if(request('group_id'))
        <p class="pt-1 text-sm text-gray-600 font-varela">
          Groupe sélectionné :
          <span class="text-orangeone font-semibold">
            {{ optional($groupes->firstWhere('id', (int)request('group_id')))->name }}
          </span>
        </p>
      @endif
    </form>


    {{-- 📊 Tableau des stagiaires --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px] border-2 border-bleuone/20">
      <table class="min-w-full bg-white text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-bleuone uppercase text-xs text-white font-varela sticky top-0 z-10">
          <tr>
            <th class="px-6 py-3">#</th>
            <th class="px-6 py-3">Prénom</th>
            <th class="px-6 py-3">Nom</th>
            <th class="px-6 py-3">Email</th>
            <th class="px-6 py-3">Code d'accès</th>
            <th class="px-6 py-3">Groupe(s)</th>
            <th class="px-6 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($stagiaires as $index => $stagiaire)
            <tr class="border-t {{ $index % 2 === 0 ? 'bg-white' : 'bg-orangeone/8' }} hover:bg-orangeone/15 transition-colors">
              <td class="px-6 py-4 font-medium">{{ $stagiaires->firstItem() + $index }}</td>
              <td class="px-6 py-4">{{ $stagiaire->prenom }}</td>
              <td class="px-6 py-4">{{ $stagiaire->name }}</td>
              <td class="px-6 py-4">{{ $stagiaire->email }}</td>
              <td class="px-6 py-4 font-mono text-sm text-orangeone">
                {{ $stagiaire->code_acces ?? '—' }}
              </td>
              <td class="px-6 py-4">
                @forelse ($stagiaire->groupesStagiaire as $groupe)
                  <span class="inline-block bg-vertone/10 text-vertone text-xs font-varela px-2 py-1 rounded-full mr-1">
                    {{ $groupe->name }}
                  </span>
                @empty
                  <span class="text-gray-400 text-xs italic">Aucun</span>
                @endforelse
              </td>
              <td class="px-6 py-4">
                <div class="flex gap-2">
                  {{-- Modifier --}}
                  <a href="{{ route('formateur.stagiaires.edit', $stagiaire->id) }}"
                     class="btn-oneduc px-3 py-1 text-xs text-white bg-orangeone border-orangeone hover:bg-white hover:text-orangeone">
                    Modifier
                  </a>

                  {{-- Supprimer --}}
                  <form action="{{ route('formateur.stagiaires.destroy', $stagiaire->id) }}"
                        method="POST"
                        onsubmit="return confirm('Supprimer ce stagiaire ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn-oneduc px-3 py-1 text-xs text-white bg-bleuone border-bleuone hover:bg-white hover:text-bleuone">
                      Supprimer
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun stagiaire trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de stagiaires :</span>
        <span class="font-bold text-bleuone">{{ $stagiaires->total() }}</span>
      </div>
      <div class="inline-flex items-center gap-2 rounded-full border border-orangeone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de groupes :</span>
        <span class="font-bold text-orangeone">{{ $groupes->count() }}</span>
      </div>
    </div>

    {{-- 📄 Pagination --}}
    <div>
      {{ $stagiaires->links('pagination::tailwind') }}
    </div>

    <div class="flex justify-end">
      <a href="{{ route('formateur.stagiaires.create', request()->filled('group_id') ? ['group_id' => request('group_id')] : []) }}"
         class="btn-oneduc w-full md:w-auto px-8 py-3 text-lg text-center">
        Ajouter un stagiaire
      </a>
    </div>

  </main>
</div>
@endsection
