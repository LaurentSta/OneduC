@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper unique pour en-tête + contenu, largeur et padding harmonisés --}}
<div class="max-w-[1285px] mx-auto px-8">

 
  {{-- 🧩 EN-TÊTE DE PAGE FORMATEUR --}}
<header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
  <div class="grid grid-cols-12 gap-6 items-center">

    {{-- Bloc texte (9 colonnes) --}}
    <div class="col-span-12 md:col-span-9">
      <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
        Espace formateur
      </p>
      <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
        Vision d'ensemble sur vos groupes, modules et stagiaires
      </p>
      <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
        Accédez rapidement à vos statistiques, à vos groupes et aux dernières activités des stagiaires.
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
          <li class="text-gray-400">Accueil formateur</li>
        </ol>
      </nav>
    </div>

    {{-- Bloc image (3 colonnes) --}}
    <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
      <img src="{{ asset('images/svg/TableauDeBord.svg') }}"
           alt="Illustration du tableau de bord formateur"
           class="max-w-[256px] h-auto">
    </div>

  </div>
</header>



  {{-- 📊 CONTENU PRINCIPAL --}}
  <main class="space-y-12">

    {{-- Statistiques globales --}}
{{-- ==== KPIs en 4 colonnes ==== --}}
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

  {{-- 1) Groupes créés --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
    <img src="{{ asset('images/svg/Groupe.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Groupes créés</p>
      <p class="text-[17px] font-medium text-bleuone">{{ $groupCount ?? 0 }}</p>
    </div>
  </div>

  {{-- 2) Modules utilisés --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
    <img src="{{ asset('images/svg/MFormation.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Modules utilisés</p>
      <p class="text-[17px] font-medium text-bleuone">{{ $modulesUsed ?? 0 }}</p>
    </div>
  </div>

  {{-- 3) Total stagiaires --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
    <img src="{{ asset('images/svg/Stagiaires.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Total stagiaires</p>
      <p class="text-[17px] font-medium text-bleuone">{{ $learnerCount ?? 0 }}</p>
    </div>
  </div>

  {{-- 4) Taux de complétion moyen --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
    <img src="{{ asset('images/svg/TauxCompletion.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Taux de complétion moyen</p>
      <p class="text-[17px] font-medium text-vertone">{{ number_format($avgCompletion ?? 0, 0) }}%</p>
    </div>
  </div>

</section>

{{-- Suivi par groupe (réel) --}}
<section aria-labelledby="groupes-title">
  <div class="flex items-center justify-between mb-4">
    <h2 id="groupes-title" class="text-xl font-semibold text-bleuone">Suivi par groupes</h2>

    <a href="{{ route('formateur.progressions.groupes') }}"
       class="text-sm font-varela text-orangeone hover:underline">
      Voir la progression
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($groupesDashboard ?? [] as $g)
      <article class="bg-white rounded-[20px] shadow-md border border-gray-100 p-5 h-full flex flex-col">
        <div class="flex items-start justify-between gap-3">
          <p class="text-lg font-semibold text-gray-800 leading-tight">
            {{ $g->name }}
          </p>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-bleuone border border-blue-100 whitespace-nowrap">
            {{ (int) ($g->modules_count ?? 0) }} module{{ (int) ($g->modules_count ?? 0) > 1 ? 's' : '' }}
          </span>
        </div>

        <p class="text-xs text-gray-500 mt-1">
          Créé le {{ optional($g->created_at)->format('d/m/Y') ?? '—' }}
        </p>

        <div class="mt-4 grid grid-cols-2 gap-3">
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500">Stagiaires</p>
            <p class="text-base font-semibold text-gray-900">{{ (int) ($g->stagiaires_count ?? 0) }}</p>
          </div>

          <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
            <p class="text-[11px] uppercase tracking-wide text-gray-500">Dernière activité</p>
            <p class="text-sm font-semibold text-gray-800">
              {{ $g->last_completed_at ? \Carbon\Carbon::parse($g->last_completed_at)->format('d/m/Y') : '—' }}
            </p>
          </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
          <span class="text-sm text-gray-600">Taux de réussite</span>
          <span class="text-sm font-bold {{ ($g->taux_reussite ?? 0) < 50 ? 'text-red-600' : 'text-vertone' }}">
            {{ (int) ($g->taux_reussite ?? 0) }} %
          </span>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
          <a href="{{ route('formateur.progressions.stagiaires', ['group_id' => $g->id]) }}"
             class="text-sm font-varela text-orangeone hover:underline">
            Voir les stagiaires du groupe
          </a>
        </div>
      </article>
    @empty
      <div class="col-span-full bg-white rounded-[20px] shadow-md p-6 text-gray-600 font-lisible">
        Aucun groupe créé pour le moment.
      </div>
    @endforelse
  </div>
</section>


{{-- Suivi par module (réel) --}}
<section aria-labelledby="modules-title">
  <h2 id="modules-title" class="text-xl font-semibold text-bleuone mb-4">
    Suivi par modules
  </h2>

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($modules ?? [] as $module)
      @php
        // $module->stagiaires a été ajouté dans ton FormateurDashboard()
        $nbStagiaires = isset($module->stagiaires) ? $module->stagiaires->count() : 0;
        $nbGroupes = $module->groups?->count() ?? 0;
      @endphp

      <article class="bg-white rounded-[20px] shadow-md border border-gray-100 p-5 h-full flex flex-col">
        <p class="font-semibold text-gray-800">
          {{ $module->module_title ?? 'Module' }}
        </p>

        <div class="mt-4 grid grid-cols-3 gap-2">
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
            <p class="text-[11px] uppercase tracking-wide text-gray-500">Groupes</p>
            <p class="text-sm font-semibold text-gray-900">{{ (int) $nbGroupes }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
            <p class="text-[11px] uppercase tracking-wide text-gray-500">Stagiaires</p>
            <p class="text-sm font-semibold text-gray-900">{{ (int) $nbStagiaires }}</p>
          </div>
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
            <p class="text-[11px] uppercase tracking-wide text-gray-500">Leçons</p>
            <p class="text-sm font-semibold text-gray-900">{{ (int) ($module->lectures_count ?? 0) }}</p>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
          <a href="{{ route('formateur.formations.detail', $module->id) }}"
             class="text-sm font-varela text-orangeone hover:underline">
            Voir le détail du module
          </a>
        </div>
      </article>
    @empty
      <div class="col-span-full bg-white rounded-[20px] shadow-md p-6 text-gray-600 font-lisible">
        Aucun module associé à vos groupes.
      </div>
    @endforelse
  </div>
</section>


{{-- Stagiaires actifs récemment (réel) --}}
<section aria-labelledby="recent-stagiaires-title">
  <div class="flex items-center justify-between mb-4">
    <h2 id="recent-stagiaires-title" class="text-xl font-semibold text-bleuone">
      Stagiaires actifs récemment
    </h2>

    <a href="{{ route('formateur.progressions.stagiaires') }}"
       class="text-sm font-varela text-orangeone hover:underline">
      Voir tous les stagiaires
    </a>
  </div>

  <div class="bg-white rounded-[20px] shadow-md">
    <div class="overflow-x-auto rounded-[20px]">
      <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-gray-100 uppercase text-xs text-gray-600 font-varela">
          <tr>
            <th scope="col" class="px-6 py-3">Stagiaire</th>
            <th scope="col" class="px-6 py-3">Leçon</th>
            <th scope="col" class="px-6 py-3">Module</th>
            <th scope="col" class="px-6 py-3 text-center">Terminé le</th>
            <th scope="col" class="px-6 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($recentProgressions ?? [] as $p)
            <tr class="border-t hover:bg-gray-50">
              <td class="px-6 py-4 font-medium">
                {{ trim(($p->user->prenom ?? '').' '.($p->user->name ?? '')) ?: 'Inconnu' }}
              </td>
              <td class="px-6 py-4">
                {{ $p->lecture->lecture_title ?? 'Leçon supprimée' }}
              </td>
              <td class="px-6 py-4 text-gray-700">
                {{ $p->lecture->section->module->module_title ?? 'Module supprimé' }}
              </td>
              <td class="px-6 py-4 text-center text-gray-600">
                {{ $p->completed_at ? \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y à H:i') : '—' }}
              </td>
              <td class="px-6 py-4 text-right">
                @if(!empty($p->user_id))
                  <a href="{{ route('formateur.progressions.stagiaire', $p->user_id) }}"
                     class="text-sm font-varela text-orangeone hover:underline">
                    Voir
                  </a>
                @else
                  —
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                Aucune activité récente.
              </td>
            </tr>
          @endforelse
        </tbody>

      </table>
    </div>
  </div>
</section>




  </main>
</div>
@endsection
