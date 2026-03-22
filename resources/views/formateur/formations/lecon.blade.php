{{-- resources/views/formateur/formations/lecon.blade.php --}}

@extends('formateur.formations.master_lecon')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $lecture   = $selectedLecture ?? ($lecture ?? null);
    $moduleId  = (int) ($module->id ?? 0);
    $lectureId = $lecture ? (int) $lecture->id : null;
    $sectionId = $lecture ? (int) $lecture->section_id : null;
    $contentType = (string) ($lecture->content_type ?? 'scorm');
    $isSlidesSelected = $contentType === 'slides';
    $isScormSelected = !$isSlidesSelected;
    $st = $lectureId ? ($lectureStats[$lectureId] ?? []) : [];
    $currentStatus = strtolower((string) ($st['status'] ?? 'not_started'));
    $isAlreadyDone = in_array($currentStatus, ['completed', 'passed'], true);

    // Conserver le contexte (mode / group_id / include_hidden) dans la navigation
    $contextQuery = is_array($contextQuery ?? null) ? $contextQuery : [];
    $appendQuery = static function (string $url, array $query): string {
        if (empty($query)) {
            return $url;
        }
        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    };

    // URL SCORM versionnee (anti-cache) resolue par le modele.
    $scormUrl = $lecture?->scorm_asset_url;

    $isSlidesMode = $lecture
        && $isSlidesSelected
        && ($lecture->slides_status ?? null) === 'ready'
        && !empty($lecture->slides_path);

    $slideImages = [];
    if ($isSlidesMode) {
        $slideImages = collect(\Illuminate\Support\Facades\Storage::disk('public')->files($lecture->slides_path))
            ->filter(fn (string $file) => (bool) preg_match('/^slide[-_]\\d+\\.jpg$/i', basename($file)))
            ->sortBy(function (string $file): int {
                if (preg_match('/(\\d+)\\.jpg$/i', basename($file), $matches)) {
                    return (int) $matches[1];
                }
                return PHP_INT_MAX;
            })
            ->values()
            ->map(fn (string $file) => route('media.storage', ['path' => $file], false))
            ->all();
    }

    $slidesStatus = (string) ($lecture->slides_status ?? 'none');

    // --- Navigation ---
    $finalUrl = $moduleId
        ? $appendQuery(route('formateur.formations.detail', ['module' => $moduleId]), $contextQuery)
        : route('formateur.dashboard');
    
    $nextUrl = '#';
    if (!empty($nextLecture) && isset($nextLecture['url'])) {
        $nextUrl = $appendQuery((string) $nextLecture['url'], $contextQuery);
    }

    // --- Quiz Start URL ---
    $quizStartUrl = null;
    if ($lecture && !empty($lecture->quiz_enabled) && $moduleId && $sectionId && $lectureId) {
        if (\Illuminate\Support\Facades\Route::has('formateur.quiz.start')) {
            $quizStartUrl = \Illuminate\Support\Facades\URL::signedRoute('formateur.quiz.start', array_merge([
                'module'  => $moduleId,
                'section' => $sectionId,
                'lecture' => $lectureId,
            ], $contextQuery));
        }
    }

    $liveQuizStoreUrl = null;
    if ($lecture && !empty($lecture->quiz_enabled) && $moduleId && $sectionId && $lectureId) {
        if (\Illuminate\Support\Facades\Route::has('formateur.live-quiz.store')) {
            $liveQuizStoreUrl = route('formateur.live-quiz.store', [
                'module' => $moduleId,
                'section' => $sectionId,
                'lecture' => $lectureId,
            ]);
        }
    }

    $formatBytes = static function (?int $bytes): string {
        $bytes = max(0, (int) $bytes);
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
        }

        return $bytes . ' o';
    };

    $whiteboardGroups = collect($whiteboardGroups ?? []);
    $currentWhiteboardGroup = $currentWhiteboardGroup ?? null;
    $wordClouds = collect($wordClouds ?? []);
@endphp

@if ($lectureId)
  <script>window.currentLectureId = {{ $lectureId }};</script>
@endif

{{-- Wrapper Principal avec Alpine pour gérer l'état Inspecteur --}}
<div x-data="{
    mode: 'formateur',
    activeTab: 'quiz',
    tabStorageKey: @js('formateur-lesson-tab-' . ($lectureId ?? 'default')),
    init() {
        const savedTab = window.localStorage.getItem(this.tabStorageKey);
        if (savedTab === 'quiz' || savedTab === 'infos' || savedTab === 'outils') {
            this.activeTab = savedTab;
        }

        this.$watch('activeTab', value => {
            window.localStorage.setItem(this.tabStorageKey, value);
        });
    }
}" class="flex flex-col h-[calc(100vh-var(--app-header-h,86px))] bg-white overflow-hidden">

  {{-- BARRE D'OUTILS FORMATEUR (Cockpit) --}}
  <div class="bg-gray-800 text-white px-4 py-3 flex items-center justify-between shadow-md z-30 shrink-0 border-b border-gray-700">
      <div class="flex items-center gap-6">
          <div class="flex items-center gap-2">
            <span class="font-bold text-orangeone uppercase text-[10px] tracking-widest">Mode</span>
            
            {{-- Toggle Switch --}}
            <button @click="mode = (mode === 'formateur' ? 'stagiaire' : 'formateur')"
                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone focus:ring-offset-gray-900"
                    :class="mode === 'stagiaire' ? 'bg-green-500' : 'bg-gray-600'">
                <span class="sr-only">Changer le mode de vue</span>
                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform duration-200 ease-in-out"
                      :class="mode === 'stagiaire' ? 'translate-x-6' : 'translate-x-1'"></span>
            </button>
            <span class="text-xs font-semibold ml-1" x-text="mode === 'formateur' ? 'Inspecteur (Vue Formateur)' : 'Réel (Vue Stagiaire)'"></span>
          </div>

          {{-- Onglets Inspecteur (visibles seulement en mode formateur) --}}
          <div x-show="mode === 'formateur'" x-cloak>
              <div class="flex flex-wrap items-stretch">
                  <button @click="activeTab = 'quiz'" 
                          :class="activeTab === 'quiz' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-300 hover:bg-white hover:text-gray-900'"
                          class="h-10 rounded-none px-3.5 text-xs font-bold transition-all whitespace-nowrap">
                      Quiz & Corrigés
                  </button>
                  <span class="mx-1 self-stretch w-px bg-white/80"></span>
                  <button @click="activeTab = 'infos'" 
                          :class="activeTab === 'infos' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-300 hover:bg-white hover:text-gray-900'"
                          class="h-10 rounded-none px-3.5 text-xs font-bold transition-all whitespace-nowrap">
                      Fichiers & Infos
                  </button>
                  <span class="mx-1 self-stretch w-px bg-white/80"></span>
                  <button @click="activeTab = 'outils'" 
                          :class="activeTab === 'outils' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-300 hover:bg-white hover:text-gray-900'"
                          class="h-10 rounded-none px-3.5 text-xs font-bold transition-all whitespace-nowrap">
                      Outils numeriques
                  </button>
              </div>
          </div>
      </div>

      <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-gray-300">
        Inspecteur de lecon
      </div>
  </div>

  {{-- CORPS DE PAGE --}}
  <div class="flex flex-1 overflow-hidden relative">
      
      {{-- ZONE CONTENU (SCORM / SLIDES) --}}
      <main class="relative bg-gray-100 transition-all duration-300 ease-in-out flex flex-col"
            :class="mode === 'formateur' ? 'w-2/3 border-r border-gray-200' : 'w-full'">
          
          @if ($isSlidesMode && !empty($slideImages))
              <div
                x-data="{
                    current: 1,
                    total: {{ count($slideImages) }},
                    slides: @js($slideImages),
                    get currentSrc() { return this.slides[this.current - 1] ?? null; }
                }"
                class="h-full flex flex-col"
              >
                <div class="relative flex-1 p-4 md:p-6">
                    <div class="absolute top-6 right-6 z-10 px-3 py-1 rounded-full bg-black/70 text-white text-xs font-semibold">
                        Slide <span x-text="current"></span> / <span x-text="total"></span>
                    </div>

                    <div class="h-full w-full flex items-center justify-center rounded-xl border border-gray-200 bg-white overflow-hidden">
                        <img :src="currentSrc" alt="Slide de cours" class="max-h-full max-w-full object-contain">
                    </div>

                    <div class="absolute inset-y-0 left-2 flex items-center">
                        <button type="button" @click="if(current > 1) current--"
                                class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition" aria-label="Slide precedente">
                            <i class="ti ti-chevron-left"></i>
                        </button>
                    </div>
                    <div class="absolute inset-y-0 right-2 flex items-center">
                        <button type="button" @click="if(current < total) current++"
                                class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition" aria-label="Slide suivante">
                            <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-white px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="current = Math.max(1, current - 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                            Precedent
                        </button>
                        <button type="button" @click="current = Math.min(total, current + 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                            Suivant
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ $nextUrl !== '#' ? $nextUrl : $finalUrl }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white rounded-full text-xs font-bold uppercase hover:bg-orangeone-hover transition">
                            Lecon suivante
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                </div>
              </div>
          @elseif ($lecture && $isSlidesSelected && in_array($slidesStatus, ['pending', 'processing'], true))
              <div class="flex items-center justify-center h-full text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m14.836 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-14.837-2m14.837 2H15" />
                    </svg>
                    <p class="mt-2">Conversion des slides en cours.</p>
                  </div>
              </div>
          @elseif ($lecture && $isSlidesSelected && $slidesStatus === 'failed')
              <div class="flex items-center justify-center h-full text-red-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                    </svg>
                    <p class="mt-2">La conversion des slides a echoue.</p>
                  </div>
              </div>
          @elseif ($lecture && $isSlidesSelected)
              <div class="flex items-center justify-center h-full text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2">Mode Slides actif, mais aucun support converti n'est disponible.</p>
                  </div>
              </div>
          @elseif ($lecture && $isScormSelected && $scormUrl)
              <iframe
                title="Contenu de la leçon"
                src="{{ $scormUrl }}"
                frameborder="0"
                allowfullscreen
                class="w-full h-full block bg-white">
              </iframe>
          @elseif ($lecture && $isScormSelected)
              <div class="flex items-center justify-center h-full text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <p class="mt-2">Mode SCORM actif, mais la ressource SCORM est manquante.</p>
                  </div>
              </div>
          @else
              <div class="flex items-center justify-center h-full text-gray-500">
                  <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2">Aucun contenu pret (SCORM ou Slides) defini pour cette lecon.</p>
                  </div>
              </div>
          @endif
      </main>

      {{-- ZONE INSPECTEUR (Visible seulement en mode formateur) --}}
      <aside x-show="mode === 'formateur'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-cloak
             class="w-1/3 bg-white border-l border-gray-200 flex flex-col shadow-xl z-20 absolute right-0 top-0 bottom-0 lg:relative lg:translate-x-0">
          
          <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
              @php
                  $totalInBank = isset($quizData) ? $quizData->count() : 0;
                  $currentCount = $lecture->quiz_questions_per_attempt ?? 0;
                  $percent = $totalInBank > 0 ? ($currentCount / $totalInBank) * 100 : 0;
              @endphp

              <div x-show="activeTab === 'quiz'" x-data="{ helpPanel: null, settingsOpen: false, correctionHelpOpen: false }">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Quiz & Corrigés</h3>
                  </div>

                  <div class="mb-6 rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                      <div class="grid gap-4 md:grid-cols-2">
                          @if($liveQuizStoreUrl)
                              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                  <div class="flex items-start gap-3">
                                      <form method="POST" action="{{ $liveQuizStoreUrl }}" class="flex-1">
                                          @csrf
                                          <button type="submit"
                                                  class="flex w-full flex-col items-center justify-center rounded-[20px] bg-bleuone px-4 py-5 text-center text-white transition hover:bg-bleuone/90">
                                              <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-white/12">
                                                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m-9 5h8a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                  </svg>
                                              </span>
                                              <span class="mt-4 text-[11px] font-bold uppercase tracking-[0.16em]">Lancer une session presentielle</span>
                                          </button>
                                      </form>
                                      <button type="button"
                                              @click="helpPanel = helpPanel === 'live' ? null : 'live'"
                                              :aria-expanded="(helpPanel === 'live').toString()"
                                              class="inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-sm font-black text-bleuone transition hover:border-bleuone hover:bg-white"
                                              aria-label="Afficher l'aide pour la session presentielle">
                                          ?
                                      </button>
                                  </div>
                                  <div x-show="helpPanel === 'live'" x-cloak class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-[12px] leading-relaxed text-slate-600" style="display: none;">
                                      <p>
                                          La session presentielle sert a animer un quiz en direct avec tout le groupe pendant un cours, un atelier ou une classe virtuelle.
                                      </p>
                                      <p class="mt-2">
                                          Quand vous la lancez, Oneduc cree une session pour cette lecon, genere un code d'acces et les stagiaires peuvent rejoindre depuis leur cloche de notification.
                                      </p>
                                      <p class="mt-2">
                                          Vous pilotez ensuite la seance question par question, affichez la correction au bon moment et suivez les reponses de la salle en temps reel.
                                      </p>
                                  </div>
                              </div>
                          @endif

                          @if($quizStartUrl)
                              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                  <div class="flex items-start gap-3">
                                      <a href="{{ $quizStartUrl }}"
                                         class="flex flex-1 flex-col items-center justify-center rounded-[20px] border border-bleuone bg-white px-4 py-5 text-center text-bleuone transition hover:bg-bleuone/5">
                                          <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-bleuone/8">
                                              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18l-1.813-2.096a5.5 5.5 0 1110.626 0L16 18l-.813-2.096M12 8v4m0 4h.01" />
                                              </svg>
                                          </span>
                                          <span class="mt-4 text-[11px] font-bold uppercase tracking-[0.16em]">Lancer le quiz test</span>
                                      </a>
                                      <button type="button"
                                              @click="helpPanel = helpPanel === 'test' ? null : 'test'"
                                              :aria-expanded="(helpPanel === 'test').toString()"
                                              class="inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-sm font-black text-bleuone transition hover:border-bleuone hover:bg-white"
                                              aria-label="Afficher l'aide pour le quiz test">
                                          ?
                                      </button>
                                  </div>
                                  <div x-show="helpPanel === 'test'" x-cloak class="mt-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-[12px] leading-relaxed text-slate-600" style="display: none;">
                                      <p>
                                          Le quiz test ouvre le quiz dans une vue de verification, avant de le proposer aux stagiaires.
                                      </p>
                                      <p class="mt-2">
                                          Il permet de controler les enonces, les reponses attendues, le nombre de questions tirees et le deroulement global du parcours.
                                      </p>
                                      <p class="mt-2">
                                          Utilisez-le comme un dernier controle qualite avant une diffusion reelle en autonomie ou en session presentielle.
                                      </p>
                                  </div>
                              </div>
                          @endif
                      </div>

                  </div>

                  <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                      <div class="flex items-start justify-between gap-3">
                          <div>
                              <h3 class="font-raleway text-bleuone font-bold text-lg flex items-center gap-2">
                                  <svg class="w-5 h-5 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                  Questions de la lecon
                              </h3>
                          </div>
                          <div class="flex items-center gap-2">
                              <button type="button"
                                      @click="correctionHelpOpen = !correctionHelpOpen"
                                      :aria-expanded="correctionHelpOpen.toString()"
                                      class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 bg-white text-sm font-black text-bleuone transition hover:border-bleuone hover:bg-slate-50"
                                      aria-label="Afficher l'aide sur les questions de la lecon">
                                  ?
                              </button>
                              <button type="button"
                                      @click="settingsOpen = !settingsOpen"
                                      :aria-expanded="settingsOpen.toString()"
                                      class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-bleuone transition hover:border-bleuone hover:bg-slate-50">
                                  Configuration avancee
                              </button>
                          </div>
                      </div>

                      <div x-show="correctionHelpOpen" x-cloak class="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-[12px] leading-relaxed text-slate-600" style="display: none;">
                          <p>
                              Cette section vous permet de relire toutes les questions configurees dans cette lecon sans lancer une tentative complete.
                          </p>
                          <p class="mt-2">
                              Les questions sont affichees sous forme d'accordeon pour alleger la lecture : vous ouvrez seulement celle que vous souhaitez consulter.
                          </p>
                          <p class="mt-2">
                              Cette vue sert surtout a la relecture de contenu et des reponses configurees. Si vous voulez tester l'experience reelle du quiz, utilisez plutot <strong>Lancer le quiz test</strong>.
                          </p>
                      </div>

                      <div x-show="settingsOpen" x-collapse class="mt-4 space-y-4">
                          <div class="rounded-xl border border-slate-200 bg-white p-4">
                              <div class="flex items-center justify-between gap-3 mb-3">
                                  <h4 class="font-bold text-bleuone text-xs uppercase">Parametres du tirage</h4>
                                  <span class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold text-bleuone">
                                      {{ $currentCount }} posees / {{ $totalInBank }} disponibles
                                  </span>
                              </div>

                              <form action="{{ route('formateur.lecture.update_quiz_count', $lecture->id) }}" method="POST" class="space-y-3">
                                  @csrf
                                  <div>
                                      <label for="q_count" class="block text-xs text-slate-500 mb-1">
                                          Nombre de questions posees au stagiaire :
                                      </label>
                                      <div class="flex gap-2">
                                          <input type="number"
                                                 id="q_count"
                                                 name="questions_count"
                                                 value="{{ $currentCount }}"
                                                 min="1"
                                                 max="{{ $totalInBank }}"
                                                 class="w-full rounded-xl border border-slate-300 px-3 py-2 text-center text-sm font-bold focus:border-bleuone focus:ring-bleuone">
                                          <button type="submit" class="rounded-xl bg-bleuone px-4 py-2 text-xs font-bold text-white transition hover:bg-bleuone/90">
                                              OK
                                          </button>
                                      </div>
                                  </div>

                                  <div class="w-full overflow-hidden rounded-full bg-slate-200 h-1.5">
                                      <div class="h-1.5 rounded-full bg-orangeone transition-all duration-500" style="width: {{ $percent }}%"></div>
                                  </div>
                                  <p class="text-[11px] italic text-slate-400">
                                      Le systeme tirera {{ $currentCount }} questions reellement posees au stagiaire parmi les {{ $totalInBank }} disponibles dans la banque.
                                  </p>
                              </form>
                          </div>

                      </div>
                      @if(isset($quizData) && $quizData->count() > 0)
                          <div class="mt-6 space-y-4">
                              @foreach($quizData as $index => $q)
                                  <div x-data="{ open: false }" class="rounded-xl border border-gray-200 bg-gray-50 transition-colors hover:border-orangeone/30">
                                      <button type="button"
                                              @click="open = !open"
                                              :aria-expanded="open.toString()"
                                              class="flex w-full items-center justify-between gap-4 px-4 py-4 text-left">
                                          <div class="min-w-0">
                                              <p class="font-bold text-gray-800 text-sm">
                                                  <span class="text-orangeone mr-1">Q{{ $index + 1 }}.</span> {{ $q->question_text }}
                                              </p>
                                          </div>
                                          <span class="shrink-0 rounded-full border border-gray-200 bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-bleuone">
                                              <span x-text="open ? 'Masquer' : 'Voir les reponses'"></span>
                                          </span>
                                      </button>

                                      <div x-show="open" x-collapse class="border-t border-gray-200 px-4 pb-4 pt-2">
                                          <ul class="space-y-2 pl-1">
                                              @foreach($q->answers as $ans)
                                                  <li class="text-xs flex items-start gap-2 p-2 rounded-lg {{ $ans->is_correct ? 'bg-green-50 border border-green-100 text-green-800' : 'text-gray-500' }}">
                                                      <span class="mt-0.5 shrink-0">
                                                          @if($ans->is_correct)
                                                              <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                          @else
                                                              <span class="inline-block w-4 h-4 border border-gray-300 rounded-full"></span>
                                                          @endif
                                                      </span>
                                                      <div class="flex-1">
                                                          <span class="{{ $ans->is_correct ? 'font-bold' : '' }}">{{ $ans->option_text }}</span>
                                                          @if($ans->feedback)
                                                              <div class="mt-1 text-[11px] text-gray-500 italic border-l-2 border-gray-300 pl-2">
                                                                  💡 {{ $ans->feedback }}
                                                              </div>
                                                          @endif
                                                      </div>
                                                  </li>
                                              @endforeach
                                          </ul>
                                      </div>
                                  </div>
                              @endforeach
                          </div>
                      @else
                      <div class="mt-6 text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                          <p class="text-gray-500 text-sm font-medium">Aucun quiz configuré pour cette leçon.</p>
                          <p class="text-xs text-gray-400 mt-1">Activez l'option "Quiz" dans l'édition du module.</p>
                      </div>
                  @endif
                  </div>
              </div>

              <div x-show="activeTab === 'infos'" x-data="{ techInfoOpen: false }" style="display: none;">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Fichiers & Infos</h3>
                  </div>

                  <div class="space-y-5">
                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <div class="flex items-start justify-between gap-3">
                              <div>
                                  <h3 class="font-raleway text-bleuone font-bold text-lg">Ressources stagiaires</h3>
                                  <p class="mt-1 text-xs text-slate-500">
                                      Ajoutez ici les fichiers utiles à la leçon, puis choisissez s’ils doivent être visibles côté stagiaire.
                                  </p>
                              </div>
                              <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-orangeone">
                                  {{ $lessonResources->count() }} fichier{{ $lessonResources->count() > 1 ? 's' : '' }}
                              </span>
                          </div>

                          <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                              <div class="mb-4">
                                  <h4 class="text-xs font-bold uppercase tracking-wide text-bleuone">Ajouter une ressource</h4>
                                  <p class="mt-1 text-xs text-slate-500">
                                      Formats acceptés : image, PDF, Word, Excel, PowerPoint, texte ou CSV. Taille maximale : 50 Mo.
                                  </p>
                              </div>

                              <form action="{{ route('formateur.formations.lesson.resources.store', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]) }}"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    class="space-y-4">
                                  @csrf
                                  @if($errors->has('title') || $errors->has('resource_file'))
                                      <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                          @error('title')<div>{{ $message }}</div>@enderror
                                          @error('resource_file')<div>{{ $message }}</div>@enderror
                                      </div>
                                  @endif

                                  <div class="grid gap-4">
                                      <div>
                                          <label for="resource_title" class="mb-1 block text-xs font-semibold text-slate-600">Titre</label>
                                          <input
                                              id="resource_title"
                                              type="text"
                                              name="title"
                                              maxlength="255"
                                              placeholder="Ex: Fiche pratique de la leçon"
                                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                                          >
                                      </div>
                                      <div>
                                          <label for="resource_file" class="mb-1 block text-xs font-semibold text-slate-600">Fichier</label>
                                          <input
                                              id="resource_file"
                                              type="file"
                                              name="resource_file"
                                              accept=".jpg,.jpeg,.png,.gif,.webp,.avif,.pdf,.doc,.docx,.odt,.txt,.rtf,.xls,.xlsx,.ods,.ppt,.pptx,.odp,.csv"
                                              class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-bleuone file:px-3 file:py-2 file:text-xs file:font-bold file:text-white hover:file:opacity-90"
                                              required
                                          >
                                      </div>
                                  </div>

                                  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                      <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-700">
                                          <input type="hidden" name="is_visible_to_stagiaire" value="0">
                                          <input type="checkbox" name="is_visible_to_stagiaire" value="1" class="rounded border-slate-300 text-orangeone focus:ring-orangeone">
                                          Afficher immédiatement cette ressource aux stagiaires
                                      </label>
                                      <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-bleuone px-4 py-2.5 text-xs font-bold uppercase text-white transition hover:opacity-90">
                                          Ajouter la ressource
                                      </button>
                                  </div>
                              </form>
                          </div>

                          <div class="mt-5 border-t border-slate-200 pt-5">
                              <div class="mb-3">
                                  <h4 class="text-xs font-bold uppercase tracking-wide text-bleuone">Ressources attachées</h4>
                                  <p class="mt-1 text-xs text-slate-500">
                                      Chaque fichier peut être ouvert, téléchargé, affiché ou masqué côté stagiaire.
                                  </p>
                              </div>

                              <div class="space-y-3">
                                  @forelse($lessonResources as $resource)
                                      @php
                                          $resourceUrl = $resource->public_url;
                                          $resourceExt = strtoupper($resource->extension ?: 'FILE');
                                      @endphp
                                      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                          <div class="flex items-start gap-3">
                                              @if($resource->is_image)
                                                  <a href="{{ $resourceUrl }}" target="_blank" class="shrink-0">
                                                      <img src="{{ $resourceUrl }}" alt="{{ $resource->title }}" class="h-14 w-14 rounded-xl border border-slate-200 object-cover bg-white">
                                                  </a>
                                              @else
                                                  <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs font-black text-bleuone">
                                                      {{ $resourceExt }}
                                                  </div>
                                              @endif

                                              <div class="min-w-0 flex-1">
                                                  <div class="flex flex-wrap items-center gap-2">
                                                      <p class="truncate text-sm font-bold text-slate-800">{{ $resource->title }}</p>
                                                      <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $resource->is_visible_to_stagiaire ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-600' }}">
                                                          {{ $resource->is_visible_to_stagiaire ? 'Visible stagiaire' : 'Masquée stagiaire' }}
                                                      </span>
                                                  </div>
                                                  <p class="mt-1 truncate text-xs text-slate-500">{{ $resource->original_name }}</p>
                                                  <div class="mt-1 flex items-center gap-1 text-[11px] text-slate-400">
                                                      <span class="shrink-0">{{ $formatBytes($resource->file_size) }}</span>
                                                      @if($resource->mime_type)
                                                          <span class="shrink-0">•</span>
                                                          <span class="truncate max-w-[180px]" title="{{ $resource->mime_type }}">{{ $resource->mime_type }}</span>
                                                      @endif
                                                  </div>

                                                  <div class="mt-3 flex flex-wrap items-center gap-2">
                                                      <a href="{{ $resourceUrl }}" target="_blank" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-bold text-blue-700 transition hover:bg-blue-100">
                                                          Ouvrir
                                                      </a>
                                                      <a href="{{ $resourceUrl }}" download="{{ $resource->original_name }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-bold text-slate-700 transition hover:bg-slate-100">
                                                          Télécharger
                                                      </a>

                                                      <form action="{{ route('formateur.formations.lesson.resources.visibility', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'resource' => $resource->id]) }}"
                                                            method="POST"
                                                            class="inline-flex">
                                                          @csrf
                                                          <input type="hidden" name="is_visible_to_stagiaire" value="{{ $resource->is_visible_to_stagiaire ? 0 : 1 }}">
                                                          <button type="submit" class="inline-flex items-center rounded-lg border px-3 py-1.5 text-[11px] font-bold transition {{ $resource->is_visible_to_stagiaire ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                              {{ $resource->is_visible_to_stagiaire ? 'Masquer côté stagiaire' : 'Afficher côté stagiaire' }}
                                                          </button>
                                                      </form>

                                                      <form action="{{ route('formateur.formations.lesson.resources.destroy', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'resource' => $resource->id]) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Supprimer cette ressource ?');"
                                                            class="inline-flex">
                                                          @csrf
                                                          @method('DELETE')
                                                          <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-[11px] font-bold text-red-700 transition hover:bg-red-100">
                                                              Supprimer
                                                          </button>
                                                      </form>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  @empty
                                      <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                          <p class="text-sm font-medium text-slate-500">Aucune ressource attachée à cette leçon.</p>
                                          <p class="mt-1 text-xs text-slate-400">Ajoutez un fichier ci-dessus, puis choisissez s’il doit être visible pour les stagiaires.</p>
                                      </div>
                                  @endforelse
                              </div>
                          </div>
                      </div>

                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <div class="flex items-start justify-between gap-3">
                              <div>
                                  <h3 class="font-raleway text-bleuone font-bold text-lg">Informations de la leçon</h3>
                                  <p class="mt-1 text-xs text-slate-500">
                                      Retrouvez ici le support principal et, si besoin, les détails techniques associés à cette leçon.
                                  </p>
                              </div>
                              <button type="button"
                                      @click="techInfoOpen = !techInfoOpen"
                                      :aria-expanded="techInfoOpen.toString()"
                                      class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-bleuone transition hover:border-bleuone hover:bg-slate-50">
                                  Détails techniques
                              </button>
                          </div>

                          <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                              <h4 class="text-xs font-bold uppercase tracking-wide text-bleuone">Support principal</h4>

                              <div class="mt-3 space-y-3 text-sm">
                                  @if(!empty($lecture->slides_source_path))
                                      <div class="flex items-center gap-2 text-slate-700">
                                          <span class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold uppercase text-bleuone">SRC</span>
                                          Source slides importée
                                      </div>
                                  @endif
                                  @if($lecture->scorm_asset_url)
                                      <a href="{{ $lecture->scorm_asset_url }}" target="_blank" class="flex items-center gap-2 text-slate-700 transition hover:text-orangeone hover:underline">
                                          <span class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold uppercase text-bleuone">SCORM</span>
                                          Ouvrir la ressource principale
                                      </a>
                                  @endif
                                  @if(empty($lecture->slides_source_path) && !$lecture->scorm_asset_url)
                                      <p class="text-xs italic text-slate-400">Aucun fichier source direct disponible.</p>
                                  @endif
                              </div>
                          </div>

                          <div x-show="techInfoOpen" x-collapse class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4" style="display: none;">
                              <h4 class="text-xs font-bold uppercase tracking-wide text-slate-500">Détails techniques</h4>
                              <dl class="mt-3 space-y-2 text-xs">
                                  <div class="flex justify-between gap-4">
                                      <dt class="text-slate-500">ID Leçon</dt>
                                      <dd class="font-mono text-slate-700">{{ $lecture->id }}</dd>
                                  </div>
                                  <div class="flex justify-between gap-4">
                                      <dt class="text-slate-500">Slides déclarées</dt>
                                      <dd class="font-medium text-slate-700">{{ $lecture->slide_count ?? 0 }}</dd>
                                  </div>
                                  <div class="flex justify-between gap-4">
                                      <dt class="text-slate-500">Questions posees / tentative</dt>
                                      <dd class="font-medium text-slate-700">{{ $lecture->quiz_questions_per_attempt ?? 0 }}</dd>
                                  </div>
                                  <div class="flex justify-between gap-4">
                                      <dt class="text-slate-500">Ressources attachées</dt>
                                      <dd class="font-medium text-slate-700">{{ $lessonResources->count() }}</dd>
                                  </div>
                              </dl>
                          </div>
                      </div>
                  </div>
              </div>

              <div x-show="activeTab === 'outils'" style="display: none;">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Outils numeriques</h3>
                  </div>

                  <div class="mb-5 rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                      <p class="text-xs text-slate-500">
                          Centralisez ici les outils collaboratifs de la lecon. Le tableau blanc se pilote par groupe pour rester coherent avec vos promotions.
                      </p>
                  </div>

                  <div class="space-y-5">
                      @if($currentWhiteboardGroup)
                          <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                              <div class="flex items-start justify-between gap-4">
                                  <div>
                                      <p class="text-xs font-bold uppercase tracking-wide text-teal-700">Tableau blanc du module</p>
                                      <h4 class="mt-1 text-base font-bold text-bleuone">{{ $currentWhiteboardGroup['name'] }}</h4>
                                      <p class="mt-2 text-xs text-slate-500">
                                          Cette lecon est ouverte dans le contexte du groupe selectionne. Depuis ici vous pouvez ouvrir ou creer le tableau blanc collaboratif rattache a ce module pour ce groupe.
                                      </p>
                                  </div>
                                  <a href="{{ $currentWhiteboardGroup['whiteboard_url'] }}"
                                     class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-bold uppercase text-white transition hover:bg-teal-700">
                                      {{ !empty($currentWhiteboardGroup['has_whiteboard']) ? 'Ouvrir le tableau' : 'Creer le tableau' }}
                                  </a>
                              </div>
                          </div>
                      @endif

                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <div class="flex items-center justify-between gap-3 mb-3">
                              <div>
                                  <h4 class="text-xs font-bold uppercase text-bleuone">Tableaux disponibles par groupe</h4>
                                  <p class="mt-1 text-xs text-slate-500">
                                      Choisissez le groupe concerne si vous souhaitez ouvrir ou creer le tableau blanc de cette lecon dans un autre contexte.
                                  </p>
                              </div>
                              <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                  {{ $whiteboardGroups->count() }} groupe{{ $whiteboardGroups->count() > 1 ? 's' : '' }}
                              </span>
                          </div>

                          <div class="space-y-3">
                              @forelse($whiteboardGroups as $boardGroup)
                                  <div class="rounded-2xl border {{ !empty($boardGroup['is_current']) ? 'border-teal-300 bg-teal-50/70' : 'border-slate-200 bg-slate-50' }} p-4">
                                      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                          <div>
                                              <div class="flex flex-wrap items-center gap-2">
                                                  <p class="text-sm font-bold text-slate-800">{{ $boardGroup['name'] }}</p>
                                                  @if(!empty($boardGroup['is_current']))
                                                      <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-teal-700 border border-teal-200">
                                                          Contexte actuel
                                                      </span>
                                                  @endif
                                                  <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ !empty($boardGroup['has_whiteboard']) ? 'bg-white text-teal-700 border border-teal-200' : 'bg-white text-slate-500 border border-slate-200' }}">
                                                      {{ !empty($boardGroup['has_whiteboard']) ? 'Cree' : 'A creer' }}
                                                  </span>
                                              </div>
                                              <p class="mt-1 text-xs text-slate-500">
                                                  {{ $boardGroup['description'] !== '' ? $boardGroup['description'] : 'Groupe sans description.' }}
                                              </p>
                                              <p class="mt-2 text-[11px] text-slate-500">
                                                  Le tableau blanc est reutilisable dans ce module pour ce groupe.
                                              </p>
                                          </div>

                                          <div class="flex flex-wrap items-center gap-2">
                                              @if(empty($boardGroup['is_current']))
                                                  <a href="{{ $boardGroup['lesson_url'] }}"
                                                     class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] font-bold uppercase text-slate-700 transition hover:bg-slate-100">
                                                      Basculer sur ce groupe
                                                  </a>
                                              @endif
                                              <a href="{{ $boardGroup['whiteboard_url'] }}"
                                                 class="inline-flex items-center justify-center rounded-lg bg-bleuone px-3 py-2 text-[11px] font-bold uppercase text-white transition hover:opacity-90">
                                                  {{ !empty($boardGroup['has_whiteboard']) ? 'Ouvrir le tableau' : 'Creer le tableau' }}
                                              </a>
                                          </div>
                                      </div>
                                  </div>
                              @empty
                                  <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                      <p class="text-sm font-medium text-slate-500">Aucun groupe rattache a ce module pour l'instant.</p>
                                      <p class="mt-1 text-xs text-slate-400">
                                          Associez d'abord ce module a un groupe pour ouvrir un tableau blanc collaboratif.
                                      </p>
                                  </div>
                              @endforelse
                          </div>
                      </div>

                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <div class="flex items-center justify-between gap-3 mb-3">
                              <div>
                                  <h4 class="text-xs font-bold uppercase text-bleuone">Nuages de mots du module</h4>
                                  <p class="mt-1 text-xs text-slate-500">
                                      Les nuages crees par l'administration et rattaches a ce module sont reutilisables ici.
                                  </p>
                              </div>
                              <span class="rounded-full bg-violet-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-violet-700">
                                  {{ $wordClouds->count() }} nuage{{ $wordClouds->count() > 1 ? 's' : '' }}
                              </span>
                          </div>

                          <div class="space-y-3">
                              @forelse($wordClouds as $wordCloud)
                                  <div class="rounded-2xl border {{ $wordCloud['is_active'] ? 'border-violet-200 bg-violet-50/60' : 'border-slate-200 bg-slate-50' }} p-4">
                                      <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                          <div>
                                              <div class="flex flex-wrap items-center gap-2">
                                                  <p class="text-sm font-bold text-slate-800">{{ $wordCloud['title'] }}</p>
                                                  <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $wordCloud['is_active'] ? 'bg-white text-violet-700 border border-violet-200' : 'bg-white text-slate-500 border border-slate-200' }}">
                                                      {{ $wordCloud['is_active'] ? 'Actif' : 'Ferme' }}
                                                  </span>
                                              </div>
                                              <p class="mt-2 text-xs text-slate-600">{{ $wordCloud['question'] }}</p>
                                              <p class="mt-2 text-[11px] text-slate-500">
                                                  Code <span class="font-mono font-bold text-slate-700">{{ $wordCloud['access_code'] }}</span>
                                                  @if($wordCloud['updated_at_human'])
                                                      · Mis a jour {{ $wordCloud['updated_at_human'] }}
                                                  @endif
                                              </p>
                                          </div>

                                          <div class="flex flex-wrap items-center gap-2">
                                              <a href="{{ $wordCloud['join_url'] }}"
                                                 target="_blank"
                                                 rel="noopener"
                                                 class="inline-flex items-center justify-center rounded-lg border border-violet-200 bg-white px-3 py-2 text-[11px] font-bold uppercase text-violet-700 transition hover:bg-violet-100">
                                                  Ouvrir le nuage
                                              </a>
                                          </div>
                                      </div>
                                  </div>
                              @empty
                                  <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                      <p class="text-sm font-medium text-slate-500">Aucun nuage de mots n'est rattache a ce module.</p>
                                      <p class="mt-1 text-xs text-slate-400">
                                          Creez-le cote administration puis associez-le au module pour le retrouver ici.
                                      </p>
                                  </div>
                              @endforelse
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </aside>
  </div>
</div>

{{-- Scripts SCORM / Navigation inchangés --}}
<script>
  const finalUrl = @json($finalUrl);

  window.SCORM_CONTEXT = {
    lecture_id: @json($lectureId),
    module_id: @json($moduleId),
    section_id: @json($sectionId),
    next_url: @json($nextUrl),
    is_already_done: @json($isAlreadyDone),
    // En mode formateur, le flux "Leçon suivante" ne doit jamais basculer sur le quiz.
    quiz_start_url: null,
    quiz_tester_url: @json($quizStartUrl),
    force_next_lesson: true,

    goToQuiz: function () {
      if (!this.quiz_tester_url) return;
      window.location.href = this.quiz_tester_url;
    },

    goToNextLesson: function () {
      if (this.next_url && this.next_url !== "#") {
        window.location.href = this.next_url;
        return;
      }
      window.location.href = finalUrl;
    }
  };

  window.goToQuiz = function () {
    if (window.SCORM_CONTEXT && typeof window.SCORM_CONTEXT.goToQuiz === "function") {
      window.SCORM_CONTEXT.goToQuiz();
    }
  };

  window.goToNextLesson = function () {
    if (window.SCORM_CONTEXT && typeof window.SCORM_CONTEXT.goToNextLesson === "function") {
      window.SCORM_CONTEXT.goToNextLesson();
    }
  };
</script>
@endsection
