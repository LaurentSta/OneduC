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
        Une synthèse rapide, puis un suivi d activité de vos groupes sans ralentir l affichage de la page.
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
        <p class="text-base font-semibold text-orangeone">Taux de réussite moyen</p>
        <p class="text-[17px] font-medium text-vertone">{{ number_format($avgSuccessRate ?? $avgCompletion ?? 0, 0) }}%</p>
      </div>
    </div>
  </section>

  <section
    x-data="formateurDashboardActivity('{{ route('formateur.dashboard.activity') }}')"
    class="bg-white rounded-[20px] shadow-md p-6"
  >
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
      <div class="max-w-2xl">
        <h2 class="text-xl font-semibold text-bleuone">Activité des groupes</h2>
        <p class="mt-1 text-sm text-gray-600">
          Ce bloc se charge séparément du reste du tableau de bord pour préserver la fluidité de l accueil.
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <template x-for="option in ranges" :key="option.value">
          <button
            type="button"
            @click="changeRange(option.value)"
            class="rounded-full border px-4 py-2 text-sm font-semibold transition"
            :class="range === option.value ? 'border-orangeone bg-orangeone text-white shadow-sm' : 'border-gray-200 bg-white text-gray-700 hover:border-orangeone hover:text-orangeone'"
            x-text="option.label"
          ></button>
        </template>
      </div>
    </div>

    <div x-show="loading" x-cloak class="mt-6 animate-pulse space-y-6">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="h-20 rounded-2xl bg-gray-100"></div>
        <div class="h-20 rounded-2xl bg-gray-100"></div>
        <div class="h-20 rounded-2xl bg-gray-100"></div>
      </div>
      <div class="h-72 rounded-2xl bg-gray-100"></div>
      <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="h-16 rounded-2xl bg-gray-100"></div>
        <div class="h-16 rounded-2xl bg-gray-100"></div>
        <div class="h-16 rounded-2xl bg-gray-100"></div>
        <div class="h-16 rounded-2xl bg-gray-100"></div>
      </div>
    </div>

    <div x-show="error && !loading" x-cloak class="mt-6 rounded-[18px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <p x-text="error"></p>
        <button
          type="button"
          @click="load()"
          class="inline-flex items-center justify-center rounded-full border border-red-200 bg-white px-4 py-2 font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100"
        >
          Réessayer
        </button>
      </div>
    </div>

    <div x-show="!loading && !error && data" x-cloak class="mt-6 space-y-6">
      <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded-[18px] border border-bleuone/10 bg-bleuone/5 px-5 py-4">
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-bleuone/70">Périmètre</p>
          <p class="mt-2 text-2xl font-bold text-bleuone" x-text="data.summary.groups_with_learners_count"></p>
          <p class="mt-1 text-sm text-gray-600">groupes avec stagiaires suivis</p>
        </div>
        <div class="rounded-[18px] border border-orangeone/10 bg-orangeone/5 px-5 py-4">
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-orangeone/70">Taux actuel moyen</p>
          <p class="mt-2 text-2xl font-bold text-orangeone" x-text="formatPercent(data.summary.current_average_rate)"></p>
          <p class="mt-1 text-sm text-gray-600">stagiaires actifs sur le dernier créneau</p>
        </div>
        <div class="rounded-[18px] border border-vertone/10 bg-vertone/5 px-5 py-4">
          <p class="text-xs font-semibold uppercase tracking-[0.22em] text-vertone/70">Pic d activité</p>
          <p class="mt-2 text-2xl font-bold text-vertone" x-text="formatPercent(data.summary.peak_average_rate)"></p>
          <p class="mt-1 text-sm text-gray-600" x-text="data.summary.peak_label ? `observé le ${data.summary.peak_label}` : 'Aucun pic détecté pour le moment'"></p>
        </div>
      </div>

      <template x-if="data.meta.empty_message">
        <div class="rounded-[18px] border border-gray-200 bg-gray-50 px-5 py-4 text-sm text-gray-600" x-text="data.meta.empty_message"></div>
      </template>

      <div class="rounded-[20px] border border-gray-100 bg-gradient-to-b from-white to-slate-50 p-4 md:p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
          <div>
            <p class="text-lg font-semibold text-bleuone" x-text="data.title"></p>
            <p class="mt-1 text-sm text-gray-600" x-text="data.subtitle"></p>
          </div>
          <template x-if="data.meta.chart_truncated">
            <p class="text-sm text-gray-500">Le graphique affiche les 6 groupes les plus visibles sur la période. Le tableau ci dessous liste tous les groupes.</p>
          </template>
        </div>

        <div class="mt-5 overflow-x-auto">
          <div class="min-w-[760px]">
            <svg viewBox="0 0 960 320" class="h-[320px] w-full">
              <template x-for="tick in yTicks" :key="`tick-${tick}`">
                <g>
                  <line
                    x1="56"
                    :x2="chartWidth - 16"
                    :y1="y(tick)"
                    :y2="y(tick)"
                    stroke="#E5E7EB"
                    stroke-dasharray="4 6"
                  ></line>
                  <text
                    x="0"
                    :y="y(tick) + 4"
                    class="fill-gray-400 text-[11px] font-semibold"
                    x-text="`${tick}%`"
                  ></text>
                </g>
              </template>

              <polyline
                :points="buildPolyline(data.average_points)"
                fill="none"
                stroke="#94A3B8"
                stroke-width="3"
                stroke-dasharray="8 8"
                stroke-linecap="round"
                stroke-linejoin="round"
              ></polyline>

              <template x-for="group in data.chart_groups" :key="`line-${group.id}`">
                <g>
                  <polyline
                    :points="buildPolyline(group.points)"
                    fill="none"
                    :stroke="group.color"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  ></polyline>
                  <circle
                    :cx="x(group.points.length - 1, group.points.length)"
                    :cy="y(group.points[group.points.length - 1] ?? 0)"
                    r="4.5"
                    :fill="group.color"
                  ></circle>
                </g>
              </template>

              <template x-for="(label, index) in data.labels" :key="`label-${index}`">
                <g x-show="visibleLabel(index)">
                  <text
                    :x="x(index, data.labels.length)"
                    y="304"
                    text-anchor="middle"
                    class="fill-gray-400 text-[11px] font-semibold"
                    x-text="label"
                  ></text>
                </g>
              </template>
            </svg>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3 text-sm">
          <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">
            <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
            <span>Moyenne globale</span>
          </div>
          <template x-for="group in data.chart_groups" :key="`legend-${group.id}`">
            <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-gray-700 ring-1 ring-gray-200">
              <span class="h-2.5 w-2.5 rounded-full" :style="`background:${group.color}`"></span>
              <span x-text="group.name"></span>
            </div>
          </template>
        </div>
      </div>

      <div class="rounded-[20px] border border-gray-100 bg-white p-4 md:p-5">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold text-bleuone">Lecture par groupe</h3>
            <p class="text-sm text-gray-600">Le tableau résume le taux actuel, la moyenne de période et la tendance observée.</p>
          </div>
          <p class="text-sm text-gray-500" x-text="`${data.summary.learners_count} stagiaire(s) suivis`"></p>
        </div>

        <div class="mt-4 overflow-hidden rounded-[18px] border border-gray-100">
          <div class="max-h-[360px] overflow-auto">
            <table class="min-w-full text-left text-sm">
              <thead class="sticky top-0 bg-slate-50 text-xs uppercase tracking-[0.18em] text-gray-500">
                <tr>
                  <th class="px-4 py-3 font-semibold">Groupe</th>
                  <th class="px-4 py-3 font-semibold">Stagiaires</th>
                  <th class="px-4 py-3 font-semibold">Actuel</th>
                  <th class="px-4 py-3 font-semibold">Moyenne</th>
                  <th class="px-4 py-3 font-semibold">Tendance</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <template x-for="group in data.table_groups" :key="`row-${group.id}`">
                  <tr class="bg-white transition hover:bg-slate-50">
                    <td class="px-4 py-3 font-semibold text-gray-800" x-text="group.name"></td>
                    <td class="px-4 py-3 text-gray-600" x-text="group.learners_count"></td>
                    <td class="px-4 py-3">
                      <span class="inline-flex rounded-full bg-orangeone/10 px-3 py-1 font-semibold text-orangeone" x-text="formatPercent(group.latest_rate)"></span>
                    </td>
                    <td class="px-4 py-3 text-gray-700" x-text="formatPercent(group.average_rate)"></td>
                    <td class="px-4 py-3">
                      <span
                        class="inline-flex rounded-full px-3 py-1 font-semibold"
                        :class="group.trend > 0 ? 'bg-vertone/10 text-vertone' : (group.trend < 0 ? 'bg-red-50 text-red-500' : 'bg-slate-100 text-slate-500')"
                        x-text="formatTrend(group.trend)"
                      ></span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
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
      <a href="{{ route('formateur.progressions.groupes') }}" class="inline-flex items-center gap-2 text-orangeone hover:underline">
        <x-icons.eye-iconify class="h-4 w-4" />
        Voir les groupes
      </a>
      <a href="{{ route('formateur.progressions.modules') }}" class="inline-flex items-center gap-2 text-orangeone hover:underline">
        <x-icons.eye-iconify class="h-4 w-4" />
        Voir les modules
      </a>
      <a href="{{ route('formateur.progressions.stagiaires') }}" class="inline-flex items-center gap-2 text-orangeone hover:underline">
        <x-icons.eye-iconify class="h-4 w-4" />
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

<script>
  window.formateurDashboardActivity = function (endpoint) {
    return {
      endpoint,
      range: 'week',
      loading: true,
      error: null,
      data: null,
      chartWidth: 960,
      chartHeight: 320,
      paddingTop: 18,
      paddingRight: 16,
      paddingBottom: 34,
      paddingLeft: 56,
      ranges: [
        { value: 'day', label: 'Jour' },
        { value: 'week', label: 'Semaine' },
        { value: 'month', label: 'Mois' },
        { value: 'year', label: 'Année' },
      ],

      async init() {
        await this.load();
      },

      async changeRange(range) {
        if (this.range === range && this.data) {
          return;
        }

        this.range = range;
        await this.load();
      },

      async load() {
        this.loading = true;
        this.error = null;

        try {
          const url = new URL(this.endpoint, window.location.origin);
          url.searchParams.set('range', this.range);

          const response = await fetch(url.toString(), {
            headers: {
              Accept: 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          });

          if (!response.ok) {
            throw new Error('Le bloc activité n a pas pu être chargé.');
          }

          this.data = await response.json();
        } catch (error) {
          this.error = error.message || 'Le bloc activité n a pas pu être chargé.';
        } finally {
          this.loading = false;
        }
      },

      get plotWidth() {
        return this.chartWidth - this.paddingLeft - this.paddingRight;
      },

      get plotHeight() {
        return this.chartHeight - this.paddingTop - this.paddingBottom;
      },

      get maxValue() {
        const values = [10];

        if (this.data?.average_points) {
          values.push(...this.data.average_points);
        }

        if (this.data?.chart_groups) {
          this.data.chart_groups.forEach(group => {
            values.push(...(group.points || []));
          });
        }

        return Math.max(10, Math.ceil(Math.max(...values) / 10) * 10);
      },

      get yTicks() {
        const max = this.maxValue;
        const steps = 4;
        const ticks = [];

        for (let index = 0; index <= steps; index += 1) {
          ticks.push(Math.round((max / steps) * (steps - index)));
        }

        return ticks;
      },

      x(index, total) {
        if (total <= 1) {
          return this.paddingLeft + this.plotWidth / 2;
        }

        return this.paddingLeft + (index * this.plotWidth) / (total - 1);
      },

      y(value) {
        const max = this.maxValue || 1;
        return this.paddingTop + ((max - value) / max) * this.plotHeight;
      },

      buildPolyline(points) {
        if (!points || points.length === 0) {
          return '';
        }

        return points
          .map((point, index) => `${this.x(index, points.length)},${this.y(point)}`)
          .join(' ');
      },

      visibleLabel(index) {
        return (this.data?.visible_label_indexes || []).includes(index);
      },

      formatPercent(value) {
        return `${Math.round(value || 0)}%`;
      },

      formatTrend(value) {
        const rounded = Math.round(value || 0);

        if (rounded > 0) {
          return `+${rounded} pts`;
        }

        if (rounded < 0) {
          return `${rounded} pts`;
        }

        return 'Stable';
      },
    };
  };
</script>
@endsection
