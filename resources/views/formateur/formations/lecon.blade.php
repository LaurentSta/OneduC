{{-- resources/views/formateur/formations/lecon.blade.php --}}

@extends('formateur.formations.master_lecon')

@section('hide_app_header', 'true')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $lecture   = $selectedLecture ?? ($lecture ?? null);
    $moduleId  = (int) ($module->id ?? 0);
    $lectureId = $lecture ? (int) $lecture->id : null;
    $sectionId = $lecture ? (int) $lecture->section_id : null;
    $contentType = (string) ($lecture->content_type ?? 'scorm');
    $isSlidesSelected = $contentType === 'slides';
    $isBlocksSelected = $contentType === 'blocks';
    $isScormSelected = !$isSlidesSelected && !$isBlocksSelected;
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

    $moduleResources = collect($moduleResources ?? $lessonResources ?? []);
    $lectureObjectives = collect($lecture?->objectives ?? []);
    $toolGroups = collect($toolGroups ?? []);
    $whiteboardGroups = collect($whiteboardGroups ?? []);
    $currentWhiteboardGroup = $currentWhiteboardGroup ?? null;
    $wordClouds = collect($wordClouds ?? []);
    $moduleLabel = $module->module_title ?? $module->module_name ?? ('Formation #' . $module->id);
    $chapterIndex = collect($module->sections ?? [])
        ->values()
        ->search(fn ($item) => (int) $item->id === (int) ($section->id ?? $sectionId ?? 0));
    $chapterNo = $chapterIndex !== false ? $chapterIndex + 1 : null;
    $lessonIndex = collect($section->lectures ?? [])
        ->values()
        ->search(fn ($item) => (int) $item->id === (int) ($lectureId ?? 0));
    $lessonNo = $lessonIndex !== false ? $lessonIndex + 1 : null;
    $moduleDetailUrl = $appendQuery(route('formateur.formations.detail', ['module' => $moduleId]), $contextQuery);
    $chapterDetailUrl = !empty($section?->id)
        ? $appendQuery(route('formateur.formations.section', ['module' => $moduleId, 'section' => $section->id]), $contextQuery)
        : '#';

    $defaultToolGroup = $toolGroups->firstWhere('id', (int) ($groupId ?? 0)) ?? $toolGroups->first();
    $defaultToolGroupId = (string) data_get($defaultToolGroup, 'id', '');
    $defaultToolModuleId = (string) ($moduleId ?: data_get($defaultToolGroup, 'modules.0.id', ''));
    $defaultToolLectureId = (string) ($lectureId ?: data_get($defaultToolGroup, 'modules.0.lectures.0.id', ''));
@endphp

@if ($lectureId)
  <script>window.currentLectureId = {{ $lectureId }};</script>
@endif

{{-- Wrapper Principal avec Alpine pour gérer l'état Inspecteur --}}
<div x-data="{
    formateurBarOpen: true,
    inspectorOpen: false,
    activeTab: 'objectifs',
    fullscreenSupported: false,
    fullscreenActive: false,
    selectedTool: 'live_quiz',
    toolGroups: @js($toolGroups),
    selectedToolGroupId: @js($defaultToolGroupId),
    selectedToolModuleId: @js($defaultToolModuleId),
    selectedToolLectureId: @js($defaultToolLectureId),
    tabStorageKey: @js('formateur-lesson-tab-' . ($lectureId ?? 'default')),
    selectedToolGroup() {
        return this.toolGroups.find(group => String(group.id) === String(this.selectedToolGroupId)) || null;
    },
    selectedToolModules() {
        return this.selectedToolGroup()?.modules || [];
    },
    selectedToolModule() {
        return this.selectedToolModules().find(module => String(module.id) === String(this.selectedToolModuleId)) || null;
    },
    selectedToolLectures() {
        return this.selectedToolModule()?.lectures || [];
    },
    selectedToolLecture() {
        return this.selectedToolLectures().find(lecture => String(lecture.id) === String(this.selectedToolLectureId)) || null;
    },
    selectedToolSectionId() {
        return this.selectedToolLecture()?.section_id || '';
    },
    selectedToolWhiteboardUrl() {
        return this.selectedToolGroup()?.whiteboard_url || '#';
    },
    selectedToolManageUrl() {
        return this.selectedToolModule()?.manage_url || '#';
    },
    selectedToolWordCloudGroupName() {
        return this.selectedToolGroup()?.name || '';
    },
    openPanel(tab, tool = null) {
        this.activeTab = tab;
        if (tool) {
            this.selectedTool = tool;
        }
        this.inspectorOpen = true;
    },
    async toggleFullscreen() {
        const target = this.$refs.contentViewport;

        if (!target || !this.fullscreenSupported) {
            return;
        }

        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
                return;
            }

            if (typeof target.requestFullscreen === 'function') {
                await target.requestFullscreen();
            }
        } catch (error) {
            console.error('Impossible de basculer en plein ecran.', error);
        }
    },
    syncFullscreenState() {
        this.fullscreenActive = !!document.fullscreenElement;
    },
    syncToolSelections() {
        const modules = this.selectedToolModules();
        if (!modules.length) {
            this.selectedToolModuleId = '';
            this.selectedToolLectureId = '';
            return;
        }

        if (!modules.some(module => String(module.id) === String(this.selectedToolModuleId))) {
            this.selectedToolModuleId = String(modules[0].id);
        }

        const lectures = this.selectedToolLectures();
        if (!lectures.length) {
            this.selectedToolLectureId = '';
            return;
        }

        if (!lectures.some(lecture => String(lecture.id) === String(this.selectedToolLectureId))) {
            this.selectedToolLectureId = String(lectures[0].id);
        }
    },
    init() {
        const savedTab = window.localStorage.getItem(this.tabStorageKey);
        if (savedTab === 'objectifs' || savedTab === 'quiz' || savedTab === 'ressources' || savedTab === 'outils' || savedTab === 'infos') {
            this.activeTab = savedTab === 'infos' ? 'ressources' : savedTab;
        }

        this.fullscreenSupported = !!document.fullscreenEnabled;
        this.syncFullscreenState();
        document.addEventListener('fullscreenchange', () => this.syncFullscreenState());

        this.$watch('activeTab', value => {
            window.localStorage.setItem(this.tabStorageKey, value);
        });
        this.$watch('selectedToolGroupId', () => this.syncToolSelections());
        this.$watch('selectedToolModuleId', () => this.syncToolSelections());
        this.syncToolSelections();
    }
}" class="flex flex-col h-[calc(100vh-var(--app-header-h,86px))] bg-white overflow-hidden">

  {{-- BARRE D'ACTIONS FORMATEUR --}}
  <div class="relative min-h-[2.25rem] bg-bleuone text-white shadow-md z-30 shrink-0 border-b border-bleuone-dark font-varela">
      <div x-show="formateurBarOpen" x-collapse.duration.200ms class="px-5 py-3 pr-12">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div class="min-w-0">
                  <span class="font-semibold text-orangeone uppercase text-[11px] tracking-[0.18em]">Lecture formateur</span>
                  <p class="mt-1 truncate text-base font-semibold leading-tight md:text-lg" title="{{ $lecture->lecture_title }}">
                      {{ $lecture->lecture_title }}
                  </p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                  <button type="button"
                          @click="openPanel('objectifs')"
                          class="inline-flex items-center justify-center rounded-full border border-white/25 px-4 py-2 text-xs font-bold uppercase tracking-wide transition"
                          :class="inspectorOpen && activeTab === 'objectifs' ? 'bg-white text-bleuone' : 'bg-white/10 text-white hover:bg-white hover:text-bleuone'">
                      Reperes
                  </button>

                  @if($quizStartUrl)
                      <a href="{{ $quizStartUrl }}"
                         class="inline-flex items-center justify-center rounded-full border border-orangeone bg-orangeone px-4 py-2 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-orangeone-hover">
                          Tester le quiz
                      </a>
                  @endif

                  <button type="button"
                          @click="openPanel('ressources')"
                          class="inline-flex items-center justify-center gap-2 rounded-full border border-white/25 px-4 py-2 text-xs font-bold uppercase tracking-wide transition"
                          :class="inspectorOpen && activeTab === 'ressources' ? 'bg-white text-bleuone' : 'bg-white/10 text-white hover:bg-white hover:text-bleuone'">
                      Ressources
                      <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px]">{{ $moduleResources->count() }}</span>
                  </button>

                  <button type="button"
                          @click="openPanel('outils', 'live_quiz')"
                          class="inline-flex items-center justify-center rounded-full border border-white/25 px-4 py-2 text-xs font-bold uppercase tracking-wide transition"
                          :class="inspectorOpen && activeTab === 'outils' ? 'bg-white text-bleuone' : 'bg-white/10 text-white hover:bg-white hover:text-bleuone'">
                      Lancer une activite
                  </button>
              </div>
          </div>
      </div>

      <button type="button"
              @click="formateurBarOpen = !formateurBarOpen"
              class="absolute right-3 top-1.5 inline-flex h-7 w-7 items-center justify-center rounded-full text-white/70 transition hover:bg-white/10 hover:text-white"
              :aria-expanded="formateurBarOpen.toString()"
              aria-label="Réduire ou déployer la barre de lecture formateur"
              title="Réduire ou déployer la barre de lecture formateur">
          <svg class="h-4 w-4 transition-transform duration-200" :class="formateurBarOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M6 9l6 6 6-6"></path>
          </svg>
      </button>
  </div>

  {{-- FIL D'ARIANE HIÉRARCHIQUE --}}
  <div class="shrink-0 flex items-center gap-3 border-b border-gray-100 bg-gray-50 px-5 py-2">
      <button type="button"
              @click="$dispatch('toggle-sidebar')"
              aria-controls="module-sidebar-wrapper"
              aria-label="Afficher ou masquer le plan"
              title="Afficher ou masquer le plan"
              class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 transition hover:border-bleuone hover:text-bleuone">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M4 6h16" stroke-linecap="round"/>
              <path d="M4 12h16" stroke-linecap="round"/>
              <path d="M4 18h16" stroke-linecap="round"/>
          </svg>
      </button>

      <div class="min-w-0 flex-1">
          <x-formateur.hierarchy-breadcrumb
              :module="['label' => 'Formation', 'title' => $moduleLabel, 'url' => $moduleDetailUrl]"
              :chapter="['label' => $chapterNo ? 'Ch. '.$chapterNo : 'Chapitre', 'title' => $section->section_title ?? '', 'url' => $chapterDetailUrl]"
              :lesson="['label' => $lessonNo ? 'Leç. '.$lessonNo : 'Leçon', 'title' => $lecture->lecture_title, 'url' => null]"
          />
      </div>

      <a href="{{ route('formateur.dashboard') }}"
         class="shrink-0 text-xs font-semibold text-gray-500 transition hover:text-orangeone hover:underline">
          Tableau de bord
      </a>
  </div>

  {{-- CORPS DE PAGE --}}
  <div class="flex flex-1 overflow-hidden relative">
      
      {{-- ZONE CONTENU (SCORM / SLIDES) --}}
      <main class="relative bg-gray-100 transition-all duration-300 ease-in-out flex flex-col min-w-0"
            :class="inspectorOpen ? 'w-full lg:w-2/3 lg:border-r lg:border-gray-200' : 'w-full'">
          <div x-ref="contentViewport" class="relative flex-1 min-h-0 overflow-hidden bg-gray-100">
              <div class="pointer-events-none absolute left-4 top-4 z-20 flex flex-wrap items-center gap-2">
                  <button type="button"
                          x-show="fullscreenSupported"
                          x-cloak
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0 scale-95"
                          x-transition:enter-end="opacity-100 scale-100"
                          x-transition:leave="transition ease-in duration-150"
                          x-transition:leave-start="opacity-100 scale-100"
                          x-transition:leave-end="opacity-0 scale-95"
                          @click="toggleFullscreen()"
                          class="pointer-events-auto inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/95 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-700 shadow-sm backdrop-blur transition hover:bg-white"
                          :aria-pressed="fullscreenActive.toString()">
                      <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                          <path x-show="!fullscreenActive" x-cloak d="M8 4H4v4" style="display: none;" />
                          <path x-show="!fullscreenActive" x-cloak d="M16 4h4v4" style="display: none;" />
                          <path x-show="!fullscreenActive" x-cloak d="M8 20H4v-4" style="display: none;" />
                          <path x-show="!fullscreenActive" x-cloak d="M16 20h4v-4" style="display: none;" />
                          <path x-show="fullscreenActive" x-cloak d="M9 4H4v5" style="display: none;" />
                          <path x-show="fullscreenActive" x-cloak d="M15 4h5v5" style="display: none;" />
                          <path x-show="fullscreenActive" x-cloak d="M9 20H4v-5" style="display: none;" />
                          <path x-show="fullscreenActive" x-cloak d="M15 20h5v-5" style="display: none;" />
                      </svg>
                      <span x-text="fullscreenActive ? 'Quitter mode plein ecran' : 'Mode plein ecran'"></span>
                  </button>
              </div>

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
              @elseif ($lecture && $isBlocksSelected)
                  <div class="h-full overflow-y-auto bg-white">
                      <div class="max-w-3xl mx-auto px-6 py-10">
                          @include('shared.lecture_blocks', ['blocks' => $lecture->content_blocks ?? [], 'lecture' => $lecture])
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
          </div>
      </main>

      {{-- ZONE INSPECTEUR (Visible seulement en mode formateur) --}}
      <aside x-show="inspectorOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-cloak
             class="w-full max-w-xl bg-slate-50 border-l border-gray-200 flex flex-col shadow-xl z-20 absolute right-0 top-0 bottom-0 lg:relative lg:w-1/3 lg:max-w-none lg:translate-x-0">
          
          <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
              @php
                  $totalInBank = isset($quizData) ? $quizData->count() : 0;
                  $currentCount = $lecture->quiz_questions_per_attempt ?? 0;
                  $percent = $totalInBank > 0 ? ($currentCount / $totalInBank) * 100 : 0;
              @endphp

              <div class="mb-5 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                  <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                          <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500" aria-label="Fil d'ariane de la lecon">
                              <a href="{{ $moduleDetailUrl }}"
                                 class="truncate max-w-[10rem] transition hover:text-bleuone"
                                 title="{{ $moduleLabel }}">
                                  {{ $moduleLabel }}
                              </a>
                              <span class="text-slate-300">></span>
                              <a href="{{ $chapterDetailUrl }}"
                                 class="transition hover:text-bleuone"
                                 title="{{ $section->section_title ?? 'Sans chapitre' }}">
                                  {{ $chapterNo ? 'Ch. ' . $chapterNo : 'Chapitre' }}
                              </a>
                              <span class="text-slate-300">></span>
                              <span title="{{ $lecture->lecture_title }}" class="text-bleuone">{{ $lessonNo ? 'Leç. ' . $lessonNo : 'Leçon' }}</span>
                          </nav>
                      </div>
                      <div class="shrink-0 flex items-center gap-2">
                          <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-bleuone">
                              {{ $isSlidesSelected ? 'Slides' : ($isBlocksSelected ? 'Texte' : 'SCORM') }}
                          </span>
                          <button type="button"
                                  @click="inspectorOpen = false"
                                  class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-bleuone hover:text-bleuone"
                                  aria-label="Fermer le panneau">
                              <span aria-hidden="true">&times;</span>
                          </button>
                      </div>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-2">
                      <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-orangeone">
                          {{ $lectureObjectives->count() }} objectif{{ $lectureObjectives->count() > 1 ? 's' : '' }}
                      </span>
                  </div>
              </div>

              <div class="mb-5 grid grid-cols-2 gap-2 rounded-[20px] border border-slate-200 bg-white p-2 shadow-sm">
                  <button type="button"
                          @click="activeTab = 'objectifs'"
                          :class="activeTab === 'objectifs' ? 'bg-bleuone text-white' : 'text-slate-600 hover:bg-slate-50'"
                          class="rounded-2xl px-3 py-2 text-xs font-bold uppercase tracking-wide transition">
                      Objectifs
                  </button>
                  <button type="button"
                          @click="activeTab = 'quiz'"
                          :class="activeTab === 'quiz' ? 'bg-bleuone text-white' : 'text-slate-600 hover:bg-slate-50'"
                          class="rounded-2xl px-3 py-2 text-xs font-bold uppercase tracking-wide transition">
                      Quiz
                  </button>
                  <button type="button"
                          @click="activeTab = 'ressources'"
                          :class="activeTab === 'ressources' ? 'bg-bleuone text-white' : 'text-slate-600 hover:bg-slate-50'"
                          class="rounded-2xl px-3 py-2 text-xs font-bold uppercase tracking-wide transition">
                      Ressources
                  </button>
                  <button type="button"
                          @click="activeTab = 'outils'"
                          :class="activeTab === 'outils' ? 'bg-bleuone text-white' : 'text-slate-600 hover:bg-slate-50'"
                          class="rounded-2xl px-3 py-2 text-xs font-bold uppercase tracking-wide transition">
                      Outils
                  </button>
              </div>

              <div x-show="activeTab === 'objectifs'"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   style="display: none;">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Objectifs</h3>
                  </div>

                  @if($lectureObjectives->isNotEmpty())
                      <div class="space-y-4">
                          @foreach($lectureObjectives as $objective)
                              <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                                  <div class="flex items-start gap-3">
                                      <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-orangeone"></span>
                                      <div>
                                          <h4 class="text-base font-bold text-bleuone">{{ $objective->title }}</h4>
                                          @if(!empty($objective->description))
                                              <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $objective->description }}</p>
                                          @endif
                                      </div>
                                  </div>
                              </div>
                          @endforeach
                      </div>
                  @else
                      <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                          <p class="text-sm font-medium text-slate-500">Aucun objectif n'est renseigne pour cette lecon.</p>
                      </div>
                  @endif
              </div>

              <div x-show="activeTab === 'quiz'" x-data="{ helpPanel: null, settingsOpen: false, correctionHelpOpen: false }"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Quiz & Corrigés</h3>
                  </div>

                  <div class="mb-6 rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                      <div class="grid gap-4">
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
                                  <div x-show="helpPanel === 'test'" x-cloak
                                       x-transition:enter="transition ease-out duration-200"
                                       x-transition:enter-start="opacity-0 scale-95"
                                       x-transition:enter-end="opacity-100 scale-100"
                                       x-transition:leave="transition ease-in duration-150"
                                       x-transition:leave-start="opacity-100 scale-100"
                                       x-transition:leave-end="opacity-0 scale-95"
                                       class="mt-3 rounded-xl border border-bleuone/15 bg-bleuone/5 px-4 py-3 text-[12px] leading-relaxed text-slate-600" style="display: none;">
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
                          @else
                              <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                  <p class="text-sm font-medium text-slate-500">Aucun quiz test disponible pour cette lecon.</p>
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

                      <div x-show="correctionHelpOpen" x-cloak
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-150"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           class="mt-4 rounded-xl border border-bleuone/15 bg-bleuone/5 px-4 py-3 text-[12px] leading-relaxed text-slate-600" style="display: none;">
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

                      <div x-show="settingsOpen" x-collapse.duration.400ms class="mt-4 space-y-4">
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
                                          <button type="submit" class="btn-oneduc-blue !px-4 !py-2 !text-xs">
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

                                      <div x-show="open" x-collapse.duration.400ms class="border-t border-gray-200 px-4 pb-4 pt-2">
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
                          <p class="text-xs text-gray-400 mt-1">Activez l'option "Quiz" dans l'édition de la formation.</p>
                      </div>
                  @endif
                  </div>
              </div>

              <div x-show="activeTab === 'ressources'"
                   x-data="{ manageResources: @js($errors->has('title') || $errors->has('resource_file')) }"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   style="display: none;">
                  <div class="mb-4 flex items-center gap-2">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Ressources</h3>
                      <x-help-tooltip message="Documents partages sur l'ensemble de la formation." aria-label="Aide sur les ressources" />
                  </div>

                  <div class="space-y-5">
                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <div class="flex items-center justify-between gap-3">
                              <div class="flex items-center gap-2">
                                  <h4 class="text-sm font-bold text-bleuone">Gestion</h4>
                                  <div x-data="{ open: false }" class="relative">
                                      <button type="button"
                                              @mouseenter="open = true"
                                              @mouseleave="open = false"
                                              @focus="open = true"
                                              @blur="open = false"
                                              class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white text-[10px] font-black text-bleuone">
                                          ?
                                      </button>
                                      <div x-show="open" x-cloak class="absolute left-0 top-full z-20 mt-2 w-52 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl" style="display: none;">
                                          PDF, images et documents bureautiques. Taille maximale : 50 Mo.
                                      </div>
                                  </div>
                              </div>
                              <div class="flex items-center gap-2">
                                  <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-orangeone">
                                      {{ $moduleResources->count() }} fichier{{ $moduleResources->count() > 1 ? 's' : '' }}
                                  </span>
                                  <button type="button"
                                          @click="manageResources = !manageResources"
                                          class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-bold uppercase tracking-wide text-bleuone transition hover:border-bleuone">
                                      <span x-text="manageResources ? 'Masquer' : 'Gerer'"></span>
                                  </button>
                              </div>
                          </div>

                          <form action="{{ route('formateur.formations.lesson.resources.store', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                x-show="manageResources"
                                x-collapse.duration.300ms
                                class="mt-4 space-y-4">
                              @csrf

                              @if($errors->has('title') || $errors->has('resource_file'))
                                  <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                      @error('title')<div>{{ $message }}</div>@enderror
                                      @error('resource_file')<div>{{ $message }}</div>@enderror
                                  </div>
                              @endif

                              <input
                                  id="resource_title"
                                  type="text"
                                  name="title"
                                  maxlength="255"
                                  placeholder="Titre de la ressource"
                                  class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                              >

                              <input
                                  id="resource_file"
                                  type="file"
                                  name="resource_file"
                                  accept=".jpg,.jpeg,.png,.gif,.webp,.avif,.pdf,.doc,.docx,.odt,.txt,.rtf,.xls,.xlsx,.ods,.ppt,.pptx,.odp,.csv"
                                  class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-bleuone file:px-3 file:py-2 file:text-xs file:font-bold file:text-white hover:file:opacity-90"
                                  required
                              >

                              <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                  <div class="flex items-center gap-2 text-xs text-slate-700">
                                      <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                          <input type="hidden" name="is_visible_to_stagiaire" value="0">
                                          <input type="checkbox" name="is_visible_to_stagiaire" value="1" class="rounded border-slate-300 text-orangeone focus:ring-orangeone">
                                          Visible stagiaire
                                      </label>
                                      <div x-data="{ open: false }" class="relative">
                                          <button type="button"
                                                  @mouseenter="open = true"
                                                  @mouseleave="open = false"
                                                  @focus="open = true"
                                                  @blur="open = false"
                                                  class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white text-[10px] font-black text-bleuone">
                                              ?
                                          </button>
                                          <div x-show="open" x-cloak class="absolute left-0 top-full z-20 mt-2 w-56 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl" style="display: none;">
                                              Le stagiaire peut consulter cette ressource depuis son espace ressources de la formation.
                                          </div>
                                      </div>
                                  </div>

                                  <button type="submit" class="btn-oneduc-blue !px-4 !py-2.5 !text-xs">
                                      Ajouter
                                  </button>
                              </div>
                          </form>
                      </div>

                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <div class="mb-4 flex items-center justify-between gap-3">
                              <h4 class="text-sm font-bold text-bleuone">Liste des ressources</h4>
                              <div x-data="{ open: false }" class="relative">
                                  <button type="button"
                                          @mouseenter="open = true"
                                          @mouseleave="open = false"
                                          @focus="open = true"
                                          @blur="open = false"
                                          class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white text-[10px] font-black text-bleuone">
                                      ?
                                  </button>
                                  <div x-show="open" x-cloak class="absolute right-0 top-full z-20 mt-2 w-56 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl" style="display: none;">
                                      Seules les actions utiles restent visibles pour limiter la charge cognitive.
                                  </div>
                              </div>
                          </div>

                          <div class="space-y-3">
                              @forelse($moduleResources as $resource)
                                  @php
                                      $resourceUrl = $resource->public_url;
                                      $resourceExt = strtoupper($resource->extension ?: 'FILE');
                                  @endphp
                                  <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                          <div class="min-w-0">
                                              <div class="flex items-center gap-2">
                                                  <span class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold uppercase text-bleuone">{{ $resourceExt }}</span>
                                                  <p class="truncate text-sm font-bold text-slate-800">{{ $resource->title }}</p>
                                              </div>
                                          </div>

                                          <div class="flex flex-wrap items-center gap-2">
                                              <a href="{{ $resourceUrl }}"
                                                 target="_blank"
                                                 title="Ouvrir"
                                                 aria-label="Ouvrir la ressource"
                                                 class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-bleuone/15 bg-bleuone/10 text-bleuone transition hover:bg-bleuone/15">
                                                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                      <path d="M14 4h6v6"></path>
                                                      <path d="M10 14L20 4"></path>
                                                      <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"></path>
                                                  </svg>
                                                  <span class="sr-only">Ouvrir</span>
                                              </a>

                                              <form action="{{ route('formateur.formations.lesson.resources.visibility', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'resource' => $resource->id]) }}"
                                                    method="POST"
                                                    class="inline-flex">
                                                  @csrf
                                                  <input type="hidden" name="is_visible_to_stagiaire" value="{{ $resource->is_visible_to_stagiaire ? 0 : 1 }}">
                                                  <button type="submit"
                                                          title="{{ $resource->is_visible_to_stagiaire ? 'Masquer pour le stagiaire' : 'Rendre visible au stagiaire' }}"
                                                          aria-label="{{ $resource->is_visible_to_stagiaire ? 'Masquer pour le stagiaire' : 'Rendre visible au stagiaire' }}"
                                                          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border transition {{ $resource->is_visible_to_stagiaire ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                                                      @if($resource->is_visible_to_stagiaire)
                                                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                                              <circle cx="12" cy="12" r="3"></circle>
                                                          </svg>
                                                          <span class="sr-only">Masquer</span>
                                                      @else
                                                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                              <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19C5 19 1 12 1 12a21.76 21.76 0 0 1 5.06-5.94"></path>
                                                              <path d="M9.9 4.24A10.83 10.83 0 0 1 12 4c7 0 11 8 11 8a21.18 21.18 0 0 1-3.22 4.82"></path>
                                                              <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"></path>
                                                              <path d="M1 1l22 22"></path>
                                                          </svg>
                                                          <span class="sr-only">Rendre visible</span>
                                                      @endif
                                                  </button>
                                              </form>

                                              <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-resource-{{ $resource->id }}')"
                                                      title="Supprimer"
                                                      aria-label="Supprimer la ressource"
                                                      class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-700 transition hover:bg-red-100">
                                                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                      <path d="M3 6h18"></path>
                                                      <path d="M8 6V4h8v2"></path>
                                                      <path d="M19 6l-1 14H6L5 6"></path>
                                                      <path d="M10 11v6"></path>
                                                      <path d="M14 11v6"></path>
                                                  </svg>
                                                  <span class="sr-only">Supprimer</span>
                                              </button>
                                              <x-confirm-modal
                                                  name="delete-resource-{{ $resource->id }}"
                                                  title="Supprimer cette ressource ?"
                                                  :action="route('formateur.formations.lesson.resources.destroy', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'resource' => $resource->id])"
                                                  method="DELETE"
                                                  confirm-label="Supprimer"
                                              />
                                          </div>
                                      </div>
                                  </div>
                              @empty
                                  <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                      <p class="text-sm font-medium text-slate-500">Aucune ressource sur cette formation.</p>
                                  </div>
                              @endforelse
                          </div>
                      </div>
                  </div>
              </div>

              <div x-show="activeTab === 'outils'"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   style="display: none;">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Outils numeriques</h3>
                  </div>

                  <div class="grid gap-5 lg:grid-cols-[12rem_1fr]">
                      <div class="rounded-[22px] border border-slate-200 bg-white p-3 shadow-sm space-y-2">
                          <div x-data="{ open: false }" class="relative flex items-center gap-2" @click.outside="open = false">
                              <button type="button"
                                      @click="selectedTool = 'module_setup'"
                                      :class="selectedTool === 'module_setup' ? 'bg-orange-50 text-orangeone border-orange-200' : 'bg-white text-slate-700 border-transparent hover:bg-slate-50'"
                                      class="flex flex-1 items-center rounded-2xl border px-3 py-3 text-left text-sm font-semibold transition">
                                  Personnaliser la formation
                              </button>
                              <button type="button"
                                      @click.stop="open = !open"
                                      :aria-expanded="open.toString()"
                                      class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-[11px] font-black text-bleuone transition hover:border-bleuone hover:bg-slate-50"
                                      aria-label="Aide sur personnaliser la formation">
                                  ?
                              </button>
                              <div x-show="open" x-cloak class="absolute left-0 top-full z-20 mt-2 w-64 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl" style="display: none;">
                                  Permet d'ajuster la formation pour un groupe precis, avec ses reglages et sa personnalisation pedagogique.
                              </div>
                          </div>

                          <div x-data="{ open: false }" class="relative flex items-center gap-2" @click.outside="open = false">
                              <button type="button"
                                      @click="selectedTool = 'live_quiz'"
                                      :class="selectedTool === 'live_quiz' ? 'bg-bleuone/10 text-bleuone border-bleuone/15' : 'bg-white text-slate-700 border-transparent hover:bg-slate-50'"
                                      class="flex flex-1 items-center rounded-2xl border px-3 py-3 text-left text-sm font-semibold transition">
                                  Quiz en direct
                              </button>
                              <button type="button"
                                      @click.stop="open = !open"
                                      :aria-expanded="open.toString()"
                                      class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-[11px] font-black text-bleuone transition hover:border-bleuone hover:bg-slate-50"
                                      aria-label="Aide sur quiz en direct">
                                  ?
                              </button>
                              <div x-show="open" x-cloak class="absolute left-0 top-full z-20 mt-2 w-64 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl" style="display: none;">
                                  Lance une session de quiz animee en temps reel pour le groupe et la lecon selectionnes.
                              </div>
                          </div>

                          <div x-data="{ open: false }" class="relative flex items-center gap-2" @click.outside="open = false">
                              <button type="button"
                                      @click="selectedTool = 'whiteboard'"
                                      :class="selectedTool === 'whiteboard' ? 'bg-teal-50 text-teal-800 border-teal-200' : 'bg-white text-slate-700 border-transparent hover:bg-slate-50'"
                                      class="flex flex-1 items-center rounded-2xl border px-3 py-3 text-left text-sm font-semibold transition">
                                  Tableau blanc
                              </button>
                              <button type="button"
                                      @click.stop="open = !open"
                                      :aria-expanded="open.toString()"
                                      class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-[11px] font-black text-bleuone transition hover:border-bleuone hover:bg-slate-50"
                                      aria-label="Aide sur tableau blanc">
                                  ?
                              </button>
                              <div x-show="open" x-cloak class="absolute left-0 top-full z-20 mt-2 w-64 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl" style="display: none;">
                                  Ouvre un espace collaboratif pour ecrire, dessiner ou organiser des idees avec le groupe.
                              </div>
                          </div>
                      </div>

                      <div class="space-y-5">
                          <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                              <div class="grid gap-4 md:grid-cols-2">
                                  <div>
                                      <label for="tool-group" class="mb-1 block text-xs font-semibold text-slate-600">Groupe</label>
                                      <select id="tool-group"
                                              x-model="selectedToolGroupId"
                                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                                          <option value="">Choisir un groupe</option>
                                          <template x-for="group in toolGroups" :key="group.id">
                                              <option :value="String(group.id)" x-text="group.name"></option>
                                          </template>
                                      </select>
                                  </div>

                                  <div>
                                      <label for="tool-module" class="mb-1 block text-xs font-semibold text-slate-600">Formation</label>
                                      <select id="tool-module"
                                              x-model="selectedToolModuleId"
                                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                                              :disabled="selectedToolModules().length === 0">
                                          <option value="">Choisir une formation</option>
                                          <template x-for="moduleItem in selectedToolModules()" :key="moduleItem.id">
                                              <option :value="String(moduleItem.id)" x-text="moduleItem.title"></option>
                                          </template>
                                      </select>
                                  </div>
                              </div>
                          </div>

                          <section x-show="selectedTool === 'whiteboard'" x-cloak
                                   x-transition:enter="transition ease-out duration-200"
                                   x-transition:enter-start="opacity-0 scale-95"
                                   x-transition:enter-end="opacity-100 scale-100"
                                   x-transition:leave="transition ease-in duration-150"
                                   x-transition:leave-start="opacity-100 scale-100"
                                   x-transition:leave-end="opacity-0 scale-95"
                                   class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm" style="display: none;">
                              <div class="flex items-start justify-between gap-3">
                                  <div>
                                      <h4 class="text-lg font-bold text-bleuone">Tableau blanc</h4>
                                      <p class="mt-1 text-xs text-slate-500">Choisissez un groupe puis ouvrez le tableau dans un nouvel onglet.</p>
                                  </div>
                              </div>

                              <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                  <a :href="selectedToolWhiteboardUrl()"
                                     target="_blank"
                                     rel="noopener"
                                     class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-bold uppercase text-white transition hover:bg-teal-700"
                                     :class="selectedToolWhiteboardUrl() === '#' ? 'pointer-events-none opacity-50' : ''">
                                      Ouvrir le tableau blanc
                                  </a>
                              </div>
                          </section>

                          <section x-show="selectedTool === 'live_quiz'" x-cloak
                                   x-transition:enter="transition ease-out duration-200"
                                   x-transition:enter-start="opacity-0 scale-95"
                                   x-transition:enter-end="opacity-100 scale-100"
                                   x-transition:leave="transition ease-in duration-150"
                                   x-transition:leave-start="opacity-100 scale-100"
                                   x-transition:leave-end="opacity-0 scale-95"
                                   class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm" style="display: none;">
                              <div class="flex items-start justify-between gap-3">
                                  <div>
                                      <h4 class="text-lg font-bold text-bleuone">Quiz en direct</h4>
                                      <p class="mt-1 text-xs text-slate-500">Choisissez un groupe, une formation et une lecon avant de lancer la session.</p>
                                  </div>
                              </div>

                              <form method="POST"
                                    action="{{ route('formateur.live-quiz.launch') }}"
                                    target="_blank"
                                    class="mt-4 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                  @csrf
                                  <input type="hidden" name="group_id" :value="selectedToolGroupId">
                                  <input type="hidden" name="module_id" :value="selectedToolModuleId">

                                  <div>
                                      <label for="tool-lecture" class="mb-1 block text-xs font-semibold text-slate-600">Lecon</label>
                                      <select id="tool-lecture"
                                              name="lecture_id"
                                              x-model="selectedToolLectureId"
                                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                                              :disabled="selectedToolLectures().length === 0">
                                          <option value="">Choisir une lecon</option>
                                          <template x-for="lectureItem in selectedToolLectures()" :key="lectureItem.id">
                                              <option :value="String(lectureItem.id)" x-text="lectureItem.label"></option>
                                          </template>
                                      </select>
                                  </div>

                                  <button type="submit"
                                          class="inline-flex items-center justify-center rounded-xl bg-bleuone px-4 py-2.5 text-xs font-bold uppercase text-white transition hover:bg-bleuone/90"
                                          :class="!selectedToolGroupId || !selectedToolModuleId || !selectedToolLectureId ? 'pointer-events-none opacity-50' : ''">
                                      Lancer une session
                                  </button>
                              </form>
                          </section>

                          <section x-show="selectedTool === 'module_setup'" x-cloak
                                   x-transition:enter="transition ease-out duration-200"
                                   x-transition:enter-start="opacity-0 scale-95"
                                   x-transition:enter-end="opacity-100 scale-100"
                                   x-transition:leave="transition ease-in duration-150"
                                   x-transition:leave-start="opacity-100 scale-100"
                                   x-transition:leave-end="opacity-0 scale-95"
                                   class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm" style="display: none;">
                              <div class="flex items-start justify-between gap-3">
                                  <div>
                                      <h4 class="text-lg font-bold text-bleuone">Personnaliser la formation</h4>
                                      <p class="mt-1 text-xs text-slate-500">Accédez au paramétrage pédagogique de la formation sélectionnée pour ce groupe.</p>
                                  </div>
                              </div>

                              <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                  <a :href="selectedToolManageUrl()"
                                     target="_blank"
                                     rel="noopener"
                                     class="inline-flex items-center justify-center rounded-xl bg-orangeone px-4 py-2.5 text-xs font-bold uppercase text-white transition hover:bg-orange-600"
                                     :class="selectedToolManageUrl() === '#' ? 'pointer-events-none opacity-50' : ''">
                                      Ouvrir la personnalisation de la formation
                                  </a>
                              </div>
                          </section>
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
