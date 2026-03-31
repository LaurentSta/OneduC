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
          <article class="flex flex-col rounded-[20px] border border-gray-200 bg-white p-6 shadow">
            <div class="flex-1 space-y-5">
              <div class="border-b border-gray-100 pb-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                  <div class="min-w-0">
                    <h3 class="truncate font-raleway text-xl font-bold text-bleuone">
                      {{ $groupe->name }}
                    </h3>
                    <p class="mt-2 text-xs italic text-gray-400 font-lisible">
                      Créé le {{ optional($groupe->created_at)->format('d/m/Y') ?? '—' }}
                    </p>
                  </div>

                  <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold {{ $groupe->is_active ? 'border-vertone/20 bg-vertone/10 text-vertone' : 'border-gray-200 bg-gray-100 text-gray-600' }}">
                      <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $groupe->is_active ? 'bg-vertone' : 'bg-gray-400' }}"></span>
                      {{ $groupe->is_active ? 'Actif' : 'Inactif' }}
                    </span>

                    @if($groupe->observers->isNotEmpty())
                      <div class="relative inline-flex group">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 cursor-default">
                          Groupe suivi
                        </span>
                        <div class="pointer-events-none absolute right-0 top-full z-20 mt-2 hidden w-max max-w-xs rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs text-blue-900 shadow-lg group-hover:block group-focus-within:block">
                          @foreach($groupe->observers as $observer)
                            <div>{{ trim(($observer->prenom ?? '').' '.($observer->name ?? '')) }}</div>
                          @endforeach
                        </div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>

              @if ($groupe->description)
                <p class="text-sm leading-7 text-gray-700 font-lisible line-clamp-3">
                  {{ $groupe->description }}
                </p>
              @endif

              @if ($groupe->start_date || $groupe->end_date)
                <div class="grid gap-3 sm:grid-cols-2">
                  @if ($groupe->start_date)
                    <div class="flex items-center gap-3 rounded-2xl border border-bleuone/15 bg-slate-50 px-4 py-3">
                      <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-bleuone/10 text-bleuone">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                        </svg>
                      </span>
                      <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Début</p>
                        <p class="text-sm font-bold text-bleuone">{{ $groupe->start_date->format('d/m/Y') }}</p>
                      </div>
                    </div>
                  @endif

                  @if ($groupe->end_date)
                    <div class="flex items-center gap-3 rounded-2xl border border-orangeone/15 bg-orange-50/60 px-4 py-3">
                      <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orangeone/10 text-orangeone">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                        </svg>
                      </span>
                      <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Fin</p>
                        <p class="text-sm font-bold text-orangeone">{{ $groupe->end_date->format('d/m/Y') }}</p>
                      </div>
                    </div>
                  @endif
                </div>
              @endif

              <div class="rounded-2xl bg-gray-50/80 p-4 space-y-4">
                <div>
                  <h4 class="mb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500 font-varela">Modules associés</h4>
                  <div class="flex flex-wrap gap-2">
                    @forelse ($groupe->modules as $module)
                      @php
                        $moduleUrl = !empty($module->category_id)
                          ? route('frontend.modules.show', ['category' => $module->category_id, 'module' => $module->id])
                          : route('frontend.modules.show.legacy', ['module' => $module->id]);
                      @endphp
                      <a href="{{ $moduleUrl }}"
                         class="inline-flex items-center rounded-full bg-vertone/10 px-3 py-1 text-xs font-varela text-vertone transition hover:bg-vertone/20">
                        {{ Str::limit($module->module_title, 30) }}
                      </a>
                    @empty
                      <p class="text-sm italic text-gray-400 font-lisible">Aucun module</p>
                    @endforelse
                  </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-4">
                  <div>
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500 font-varela">Stagiaires</h4>
                    <p class="mt-1 text-sm text-gray-500 font-lisible">Accès rapide à la liste du groupe</p>
                  </div>
                  <a href="{{ route('formateur.stagiaires.index') }}"
                     class="shrink-0 text-sm font-semibold text-orangeone hover:underline font-lisible">
                    {{ $groupe->students->count() }} stagiaire{{ $groupe->students->count() > 1 ? 's' : '' }}
                  </a>
                </div>
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
