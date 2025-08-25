@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper unique pour en-tête + contenu --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE FORMATEUR – Groupes --}}
<header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
  <div class="grid grid-cols-12 gap-6 items-center">

    {{-- Bloc texte (9 colonnes) --}}
    <div class="col-span-12 md:col-span-9">
      <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
        Mes groupes de formation
      </p>
      <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
        Gérez facilement vos groupes, modules et stagiaires.
      </p>
      <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
        Retrouvez ici tous vos groupes. Vous pouvez les modifier, leur associer des modules ou ajouter des stagiaires.
      </p>

      {{-- 📍 Fil d’Ariane --}}
      <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
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
          <li class="text-gray-400">Mes groupes</li>
        </ol>
      </nav>
    </div>

    {{-- Bloc image (3 colonnes) --}}
    <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
      <img src="{{ asset('images/svg/Groupes.svg') }}"
           alt="Illustration des groupes de formation"
           class="max-w-[256px] h-auto">
    </div>

  </div>
</header>


  {{-- 💼 CONTENU PRINCIPAL --}}
  <main class="space-y-8">

    {{-- Alertes --}}
    @if (session('success'))
      <div class="bg-green-100 text-green-800 px-4 py-3 rounded font-lisible">
        {{ session('success') }}
      </div>
    @endif

    {{-- Grille: carte "Ajouter" + cartes groupes --}}
    <section aria-labelledby="groupes-title">
      <h2 id="groupes-title" class="sr-only">Liste des groupes</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- ➕ Carte "Ajouter un groupe" --}}
        <a href="{{ route('formateur.groupes.create') }}"
           class="flex flex-col items-center justify-center border-4 border-dashed border-orangeone rounded-[20px] p-10 min-h-[180px] text-orangeone hover:bg-orangeone hover:text-white transition font-varela text-lg font-semibold">
          Ajouter un groupe
        </a>

        {{-- 📋 Cartes groupes --}}
        @forelse ($groupes as $groupe)
          <article class="bg-white border border-gray-200 rounded-[20px] shadow p-6 flex flex-col">
            <div class="flex-1">
              <h3 class="text-xl font-bold text-bleuone font-raleway mb-2 truncate">
                {{ $groupe->name }}
              </h3>

              @if ($groupe->description)
                <p class="text-sm text-gray-700 font-lisible mb-4 line-clamp-3">
                  {{ $groupe->description }}
                </p>
              @endif

              <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-600 font-varela">Modules associés :</h4>
                @forelse ($groupe->modules as $module)
                  <a href="{{ route('frontend.modules.show', $module->id) }}"
                     class="inline-block bg-vertone/10 text-vertone text-xs font-varela mr-2 mb-2 px-3 py-1 rounded-full hover:bg-vertone/20 transition">
                    {{ Str::limit($module->module_title, 30) }}
                  </a>
                @empty
                  <p class="text-sm text-gray-400 font-lisible italic">Aucun module</p>
                @endforelse
              </div>

              <div>
                <h4 class="text-sm font-medium text-gray-600 font-varela">Stagiaires :</h4>
                <a href="{{ route('formateur.stagiaires.index') }}"
                   class="text-sm text-orangeone hover:underline font-lisible">
                  {{ $groupe->students->count() }} stagiaire{{ $groupe->students->count() > 1 ? 's' : '' }}
                </a>
              </div>
            </div>

            <div class="flex gap-2 mt-6">
              <a href="{{ route('formateur.groupes.edit', $groupe->id) }}" class="btn-oneduc w-1/2 text-center">
                Modifier
              </a>
              <form action="{{ route('formateur.groupes.destroy', $groupe->id) }}" method="POST"
                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?');"
                    class="w-1/2">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-oneduc bg-bleuone border-bleuone hover:bg-white hover:text-bleuone w-full">
                  Supprimer
                </button>
              </form>
            </div>
          </article>
        @empty
          <p class="text-gray-500 col-span-full font-lisible">Aucun groupe n’a encore été créé.</p>
        @endforelse
      </div>
    </section>

  </main>
</div>
@endsection
