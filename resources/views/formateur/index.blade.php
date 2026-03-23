@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

<header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
  <div class="grid grid-cols-12 gap-6 items-center">

    <div class="col-span-12 md:col-span-9">
      <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
        Espace formateur
      </p>
      <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
        Vue synthétique de votre activité
      </p>
      <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
        Trois graphiques, puis les accès utiles juste en dessous.
      </p>

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

    <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
      <img src="{{ asset('images/svg/TableauDeBord.svg') }}"
           alt="Illustration du tableau de bord formateur"
           class="max-w-[256px] h-auto">
    </div>

  </div>
</header>

@php
  $activeLearnersCount = max(0, (int) ($learnerCount ?? 0) - (int) ($inactiveLearnersCount ?? 0) - (int) ($notStartedLearnersCount ?? 0));
  $learnerTotal = max(1, (int) ($learnerCount ?? 0));
  $activePercent = round(($activeLearnersCount / $learnerTotal) * 100);
  $inactivePercent = round((((int) ($inactiveLearnersCount ?? 0)) / $learnerTotal) * 100);
  $notStartedPercent = max(0, 100 - $activePercent - $inactivePercent);

  $maxGroupAlerts = max(1, (int) collect($priorityGroups ?? [])->max('alert_count'));
  $maxModuleAttention = max(1, (int) collect($priorityModules ?? [])->max('attention_score'));
@endphp

<main class="space-y-6">

  <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
      <img src="{{ asset('images/svg/Groupe.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
      <div class="leading-tight">
        <p class="text-base font-semibold text-orangeone">Groupes créés</p>
        <p class="text-[17px] font-medium text-bleuone">{{ $groupCount ?? 0 }}</p>
      </div>
    </div>

    <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
      <img src="{{ asset('images/svg/MFormation.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
      <div class="leading-tight">
        <p class="text-base font-semibold text-orangeone">Modules utilisés</p>
        <p class="text-[17px] font-medium text-bleuone">{{ $modulesUsed ?? 0 }}</p>
      </div>
    </div>

    <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
      <img src="{{ asset('images/svg/Stagiaires.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
      <div class="leading-tight">
        <p class="text-base font-semibold text-orangeone">Total stagiaires</p>
        <p class="text-[17px] font-medium text-bleuone">{{ $learnerCount ?? 0 }}</p>
      </div>
    </div>

    <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
      <img src="{{ asset('images/svg/TauxCompletion.svg') }}" alt="" class="w-20 h-20 shrink-0" aria-hidden="true">
      <div class="leading-tight">
        <p class="text-base font-semibold text-orangeone">Taux de complétion moyen</p>
        <p class="text-[17px] font-medium text-vertone">{{ number_format($avgCompletion ?? 0, 0) }}%</p>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <h2 class="text-xl font-semibold text-bleuone">Stagiaires</h2>
          <p class="text-sm text-gray-600">Répartition globale de l'activité.</p>
        </div>
        <p class="text-2xl font-bold text-bleuone">{{ $learnerCount ?? 0 }}</p>
      </div>

      <div class="h-5 w-full overflow-hidden rounded-full bg-gray-100 flex">
        <div class="bg-vertone h-full" style="width: {{ $activePercent }}%"></div>
        <div class="bg-orangeone h-full" style="width: {{ $inactivePercent }}%"></div>
        <div class="bg-red-500 h-full" style="width: {{ $notStartedPercent }}%"></div>
      </div>

      <div class="mt-5 space-y-3 text-sm">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 text-gray-700">
            <span class="h-3 w-3 rounded-full bg-vertone"></span>
            <span>Actifs récemment</span>
          </div>
          <span class="font-semibold text-gray-900">{{ $activeLearnersCount }}</span>
        </div>

        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 text-gray-700">
            <span class="h-3 w-3 rounded-full bg-orangeone"></span>
            <span>À relancer</span>
          </div>
          <span class="font-semibold text-gray-900">{{ $inactiveLearnersCount ?? 0 }}</span>
        </div>

        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 text-gray-700">
            <span class="h-3 w-3 rounded-full bg-red-500"></span>
            <span>Jamais commencés</span>
          </div>
          <span class="font-semibold text-gray-900">{{ $notStartedLearnersCount ?? 0 }}</span>
        </div>
      </div>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <h2 class="text-xl font-semibold text-bleuone">Groupes à suivre</h2>
          <p class="text-sm text-gray-600">Les groupes qui demandent le plus d'attention.</p>
        </div>
        <p class="text-2xl font-bold text-bleuone">{{ $groupsNeedingAttentionCount ?? 0 }}</p>
      </div>

      <div class="space-y-4">
        @forelse($priorityGroups ?? [] as $group)
          @php
            $groupWidth = min(100, round((((int) ($group->alert_count ?? 0)) / $maxGroupAlerts) * 100));
          @endphp
          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <p class="text-sm font-semibold text-gray-800 truncate">{{ $group->name }}</p>
              <p class="text-sm text-gray-600 whitespace-nowrap">{{ (int) ($group->alert_count ?? 0) }} alerte{{ (int) ($group->alert_count ?? 0) > 1 ? 's' : '' }}</p>
            </div>
            <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full rounded-full bg-orangeone" style="width: {{ $groupWidth }}%"></div>
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-500">Aucun groupe à afficher.</p>
        @endforelse
      </div>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <h2 class="text-xl font-semibold text-bleuone">Modules à renforcer</h2>
          <p class="text-sm text-gray-600">Les contenus les plus sensibles.</p>
        </div>
        <p class="text-2xl font-bold text-bleuone">{{ $modulesNeedingAttentionCount ?? 0 }}</p>
      </div>

      <div class="space-y-4">
        @forelse($priorityModules ?? [] as $module)
          @php
            $moduleWidth = min(100, round((((int) ($module->attention_score ?? 0)) / $maxModuleAttention) * 100));
          @endphp
          <div>
            <div class="flex items-center justify-between gap-3 mb-2">
              <p class="text-sm font-semibold text-gray-800 truncate">{{ $module->module_title ?? 'Module' }}</p>
              <p class="text-sm text-gray-600 whitespace-nowrap">{{ $module->attention_label ?? 'Bon suivi' }}</p>
            </div>
            <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
              <div class="h-full rounded-full {{ ($module->attention_variant ?? 'green') === 'red' ? 'bg-red-500' : (($module->attention_variant ?? 'green') === 'amber' ? 'bg-orangeone' : (($module->attention_variant ?? 'green') === 'blue' ? 'bg-bleuone' : 'bg-vertone')) }}" style="width: {{ $moduleWidth }}%"></div>
            </div>
          </div>
        @empty
          <p class="text-sm text-gray-500">Aucun module à afficher.</p>
        @endforelse
      </div>
    </article>

  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6">
    <div class="flex flex-wrap items-center gap-4 md:gap-8 text-sm font-varela">
      <a href="{{ route('formateur.progressions.groupes') }}" class="text-orangeone hover:underline">
        Voir les groupes
      </a>
      <a href="{{ route('formateur.progressions.modules') }}" class="text-orangeone hover:underline">
        Voir les modules
      </a>
      <a href="{{ route('formateur.progressions.stagiaires') }}" class="text-orangeone hover:underline">
        Voir les stagiaires
      </a>
      <a href="{{ route('formateur.groupes.create') }}" class="text-orangeone hover:underline">
        Créer un groupe
      </a>
      <a href="{{ route('formateur.stagiaires.create') }}" class="text-orangeone hover:underline">
        Ajouter un stagiaire
      </a>
      <a href="{{ route('formateur.formations.index') }}" class="text-orangeone hover:underline">
        Consulter les modules
      </a>
    </div>
  </section>

</main>
</div>
@endsection
