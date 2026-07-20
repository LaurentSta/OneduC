@extends('stagiaire.master')

@section('content')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Mes formations --}}
  <header class="rounded-[20px] border border-gray-100 bg-white shadow-md mb-6">
    <div class="grid gap-6 px-6 py-6 md:px-8 md:py-7 lg:grid-cols-12 lg:items-center">

      {{-- Texte (9) --}}
      <div class="lg:col-span-8">
        <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Mes formations']]" />

        <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
          Mes formations
        </h1>
        <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
          Accédez à vos contenus et suivez votre progression.
        </p>
        <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
          Chaque formation regroupe plusieurs sections. Vous pouvez reprendre une leçon à tout moment.
        </p>
      </div>

      {{-- Image (3) --}}
      <div class="lg:col-span-4 flex justify-center lg:justify-end">
        <img src="{{ asset('images/svg/FormationStagiaire.svg') }}"
             alt="Illustration des formations"
             class="max-w-[220px] h-auto">
      </div>

    </div>
  </header>
  {{-- 📋 CONTENU PRINCIPAL --}}
  <main class="space-y-8">
    @php
      $stagiaireFlowModules = $modules->values()->map(function ($module, $index) {
          return [
              'id' => (int) $module->id,
              'title' => (string) ($module->module_title ?? ''),
              'order' => $index + 1,
              'status' => (string) ($module->progression_status ?? 'not_started'),
              'progress' => (int) ($module->progression_percent ?? 0),
              'estimated_duration_label' => $module->getFormattedDurationForUser(auth()->id()),
              'detail_url' => route('stagiaire.module.detail', $module->id),
          ];
      });
    @endphp

    <div
      data-stagiaire-module-flow
      data-modules='@json($stagiaireFlowModules)'
    ></div>

    @if($modules->isEmpty() && !$parcours)
      <div class="text-center py-20 bg-white rounded-[20px] w-full shadow-inner">
        <p class="text-gray-500 font-lisible">Aucune formation ne vous a encore été attribuée.</p>
      </div>
    @endif

    {{-- ── PARCOURS DE FORMATION ─────────────────────────────────────────── --}}
    @if($parcours && $parcoursItems->isNotEmpty())
      <section class="bg-white rounded-[20px] shadow-md overflow-hidden">

        {{-- Titre du parcours --}}
        <div class="bg-[#004461] px-6 py-4 flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/60">Parcours de formation</p>
            <p class="text-base font-bold text-white leading-snug">{{ $parcours->title }}</p>
          </div>
          <div class="ml-auto flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
              {{ $parcoursItems->count() }} étape{{ $parcoursItems->count() > 1 ? 's' : '' }}
            </span>
          </div>
        </div>

        {{-- Étapes --}}
        <div class="divide-y divide-gray-100">
          @foreach($parcoursItems as $index => $item)
            @php
              $stepNum = $index + 1;
            @endphp

            @if($item->type === 'module')
              @php
                $mod = $modulesById->get($item->module_id);
                $progress = $mod ? (int)($mod->progression_percent ?? 0) : 0;
                $status   = $mod ? ($mod->progression_status ?? 'not_started') : 'not_started';
                $url      = $mod ? route('stagiaire.module.detail', $mod->id) : null;
              @endphp
              <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition {{ $url ? '' : 'opacity-60' }}">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#004461]/10 text-[#004461] text-sm font-bold">
                  {{ $stepNum }}
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#004461]/10 text-[#004461]">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $mod ? ($mod->module_title ?? 'Formation') : ($item->module_id ? "Formation #{$item->module_id}" : 'Formation') }}
                  </p>
                  @if($mod)
                    <div class="mt-1 flex items-center gap-2">
                      <div class="h-1.5 w-32 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full bg-[#E94D2A] transition-all" style="width: {{ $progress }}%"></div>
                      </div>
                      <span class="text-xs text-gray-500">{{ $progress }}%</span>
                    </div>
                  @endif
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold
                  {{ $status === 'completed' ? 'bg-green-100 text-green-700' : ($status === 'in_progress' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500') }}">
                  {{ $status === 'completed' ? 'Terminé' : ($status === 'in_progress' ? 'En cours' : 'À faire') }}
                </span>
                @if($url)
                  <a href="{{ $url }}" class="shrink-0 inline-flex items-center gap-1 rounded-[8px] bg-[#E94D2A] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#cf4121] transition">
                    Accéder
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                @endif
              </div>

            @elseif($item->type === 'wordcloud')
              <div class="flex items-center gap-4 px-6 py-4 bg-amber-50/50 hover:bg-amber-50 transition">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-sm font-bold">
                  {{ $stepNum }}
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-amber-800 truncate">
                    {{ $item->wc_title ?: 'Nuage de mots' }}
                  </p>
                  <p class="text-xs text-amber-600 mt-0.5">
                    {{ count($item->wc_questions ?? []) }} question{{ count($item->wc_questions ?? []) > 1 ? 's' : '' }}
                  </p>
                </div>
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-[10px] font-semibold text-amber-700">
                  Nuage de mots
                </span>
                <a href="{{ route('stagiaire.wordcloud.parcours.show', $item) }}"
                   class="shrink-0 inline-flex items-center gap-1 rounded-[8px] bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600 transition">
                  Accéder
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </a>
              </div>

            @elseif($item->type === 'poll')
              <div class="flex items-center gap-4 px-6 py-4 bg-teal-50/50">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700 text-sm font-bold">
                  {{ $stepNum }}
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <div class="flex-1">
                  @php
                    $firstQ = collect($item->poll_questions ?? [])->first();
                  @endphp
                  <p class="text-sm font-semibold text-teal-800">
                    {{ $firstQ ? Str::limit($firstQ['question'] ?? 'Sondage', 60) : 'Sondage' }}
                  </p>
                  <p class="text-xs text-teal-600 mt-0.5">Activité interactive — lancée par le formateur en session</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-[10px] font-semibold text-teal-700">
                  Sondage
                </span>
              </div>
            @elseif($item->type === 'outil')
              @php
                $configurationOutil = collect($item->configuration ?? []);
                $libelleOutil = trim((string) ($configurationOutil->get('titre') ?: Str::headline(str_replace(['-', '_'], ' ', (string) ($item->outil ?? 'activité')))));
                $outilExecutable = $item->outil === 'vrai-faux'
                    && app(\App\Support\Parcours\RegistreOutilsParcours::class)->executionDisponible('vrai-faux');
              @endphp
              <div class="flex items-center gap-4 bg-slate-50/70 px-6 py-4">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-700">
                  {{ $stepNum }}
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-slate-700">
                  <i class="ti ti-settings text-base" aria-hidden="true"></i>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-semibold text-slate-800">{{ $libelleOutil ?: 'Activité' }}</p>
                  @if(!$outilExecutable)
                    <p class="mt-0.5 text-xs text-slate-500">Cette étape sera ouverte par le formateur pendant la séance.</p>
                  @endif
                </div>
                @if($outilExecutable)
                  <a href="{{ route('stagiaire.outil.parcours.show', $item) }}"
                     class="shrink-0 inline-flex items-center gap-1 rounded-[8px] bg-slate-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-700 transition">
                    Accéder
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                @else
                  <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-0.5 text-[10px] font-semibold text-slate-700">
                    Configuration — session non lancée
                  </span>
                @endif
              </div>
            @endif
          @endforeach
        </div>

      </section>
    @endif

    {{-- ── OUTILS NUMÉRIQUES ACTIFS ─────────────────────────────────────────── --}}
    @if($activeWordClouds->isNotEmpty())
      <section class="bg-white rounded-[20px] shadow-md overflow-hidden">
        <div class="bg-amber-500 px-6 py-4 flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
          </div>
          <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/60">Outils numériques</p>
            <p class="text-base font-bold text-white leading-snug">Activités de mon groupe</p>
          </div>
        </div>

        <div class="divide-y divide-gray-100">
          @foreach($activeWordClouds as $wc)
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-amber-50/30 transition">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $wc->title }}</p>
                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $wc->question }}</p>
              </div>
              <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-[10px] font-semibold text-green-700">
                Nuage de mots
              </span>
              <a href="{{ route('wordcloud.join.code', $wc->access_code) }}"
                 class="shrink-0 inline-flex items-center gap-1 rounded-[8px] bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600 transition">
                Participer
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </a>
            </div>
          @endforeach
        </div>
      </section>
    @endif

  </main>
</div>
@endsection
