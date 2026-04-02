{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/index.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Mes modules de formation</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Suivez les modules que vous utilisez dans vos groupes.
        </x-typography>
        <x-typography>
          Retrouvez ici tous les modules de formation utilisés, leur statut, les groupes concernés et les stagiaires associés.
        </x-typography>

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

      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}"
             alt="Illustration des modules de formation"
             class="max-w-[300px] h-auto"
             loading="lazy">
      </div>

    </div>
  </header>

  <main class="space-y-8">

    {{-- Barre de recherche --}}
    <form method="GET" class="space-y-3">
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-full md:w-1/2">
        <label for="search" class="sr-only">Recherche</label>
        <input type="text"
               id="search"
               name="search"
               value="{{ $search ?? request('search') }}"
               placeholder="Recherche titre du module"
               class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
        </div>

        <button type="submit" class="btn-oneduc h-10 !text-sm">
          Filtrer
        </button>

        @if(request()->filled('search'))
          <a href="{{ route('formateur.formations.index') }}"
             class="btn-oneduc-outline h-10 !text-sm">
            Réinitialiser
          </a>
        @endif
      </div>

      @if(request('search'))
        <p class="pt-1 text-sm text-gray-600 font-varela">
          Recherche active :
          <span class="text-orangeone font-semibold">{{ request('search') }}</span>
        </p>
      @endif
    </form>

    {{-- Tableau des modules --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px] border-2 border-bleuone/20">
      <table class="min-w-full bg-white text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-bleuone uppercase text-xs text-white font-varela sticky top-0 z-10">
          <tr>
            <th class="px-6 py-3">#</th>
            <th class="px-6 py-3">Titre</th>
            <th class="px-6 py-3">Date de création</th>
            <th class="px-6 py-3">Statut</th>
            <th class="px-6 py-3 text-center">Groupes associés</th>
            <th class="px-6 py-3 text-center">Stagiaires total</th>
            <th class="px-6 py-3">Actions</th>
          </tr>
        </thead>

        <tbody>
          @forelse ($modules as $index => $module)
            @php
              $groupes = $module->groups ?? collect();
              $stagiaires = $groupes
                ->flatMap(fn ($g) => ($g->users ?? collect())->where('role', 'stagiaire'))
                ->unique('id');

              if ($groupes->count() > 0) {
                $statut = 'utilise';
              } elseif ((int)($module->status ?? 0) === 1) {
                $statut = 'non_utilise';
              } else {
                $statut = 'indisponible';
              }

              $titre = $module->module_title ?? $module->module_name ?? $module->name ?? 'Sans titre';
              $firstSection = ($module->sections ?? collect())->sortBy('id')->first();
              $officialUrl = $firstSection
                ? route('formateur.formations.detail', ['module' => $module->id])
                : null;
            @endphp

            <tr class="border-t {{ $index % 2 === 0 ? 'bg-white' : 'bg-orangeone/8' }} hover:bg-orangeone/15 transition-colors">
              <td class="px-6 py-4 font-medium">{{ $modules->firstItem() + $index }}</td>

              <td class="px-6 py-4 font-semibold text-gray-900">
                {{ $titre }}
              </td>

              <td class="px-6 py-4 text-gray-700">
                {{ optional($module->created_at)->format('d/m/Y') ?? '—' }}
              </td>

              <td class="px-6 py-4">
                @if($statut === 'utilise')
                  <span class="inline-flex items-center px-2.5 py-1 text-green-800 bg-green-100 rounded-full text-xs font-semibold border border-green-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-600 mr-2" aria-hidden="true"></span>
                    Utilisé
                  </span>
                @elseif($statut === 'non_utilise')
                  <span class="inline-flex items-center px-2.5 py-1 text-blue-800 bg-blue-100 rounded-full text-xs font-semibold border border-blue-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 mr-2" aria-hidden="true"></span>
                    Non utilisé
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-1 text-red-800 bg-red-100 rounded-full text-xs font-semibold border border-red-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-600 mr-2" aria-hidden="true"></span>
                    Indisponible
                  </span>
                @endif
              </td>

              <td class="px-6 py-4 text-center font-semibold text-gray-900">
                {{ $groupes->count() }}
              </td>

              <td class="px-6 py-4 text-center font-semibold text-gray-900">
                {{ $stagiaires->count() }}
              </td>

              <td class="px-6 py-4">
                @if($statut === 'indisponible')
                  <span class="text-gray-400 text-sm">Accès indisponible</span>
                @elseif($officialUrl)
                  <a href="{{ $officialUrl }}"
                     class="btn-oneduc !px-3 !py-1 !text-sm">
                    <x-icons.eye-iconify class="h-4 w-4" />
                    Voir le module
                  </a>
                @else
                  <span class="text-gray-400 text-sm">Accès indisponible</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun module trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Nombre total de modules :</span>
        <span class="font-bold text-bleuone">{{ $modules->total() }}</span>
      </div>
    </div>

    {{-- Pagination --}}
    <div>
      {{ $modules->links('pagination::tailwind') }}
    </div>

  </main>

</div>
@endsection
