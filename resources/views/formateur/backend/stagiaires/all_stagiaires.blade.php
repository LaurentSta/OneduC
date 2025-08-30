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

    {{-- 🔎 Barre de recherche --}}
    <form method="GET" class="flex flex-wrap items-center gap-3">
      <input type="text"
             name="search"
             value="{{ request('search') }}"
             placeholder="Recherche prénom, nom ou email"
             class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible">
      <button type="submit" class="btn-oneduc">
        Rechercher
      </button>
    </form>

    {{-- 📊 Tableau des stagiaires --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
      <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-gray-100 uppercase text-xs text-gray-600 font-varela">
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
            <tr class="border-t hover:bg-gray-50">
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

    {{-- 📄 Pagination --}}
    <div>
      {{ $stagiaires->links('pagination::tailwind') }}
    </div>

  </main>
</div>
@endsection
