@extends('formateur.formations.master_lecon')

@section('hide_app_header', 'true')

@section('content')
@php
  use Illuminate\Support\Str;

  $contextQuery = is_array($contextQuery ?? null) ? $contextQuery : [];
  $appendQuery = static function (string $url, array $query): string {
    if (empty($query)) {
      return $url;
    }

    return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
  };

  $lectures = $selectedSection->lectures ?? collect();
  $lecturesWithObjectives = $lectures
    ->filter(fn ($lec) => ($lec->objectives ?? collect())->isNotEmpty());
  $chapterIndex = collect($module->sections ?? [])
    ->values()
    ->search(fn ($item) => (int) $item->id === (int) ($selectedSection->id ?? 0));
  $chapterNo = $chapterIndex !== false ? $chapterIndex + 1 : null;

  $rawQuestions = trim((string) ($selectedSection->section_html ?? ''));
  $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);

  $firstLecture = $lectures->first();
  $videoSrc = !empty($selectedSection->video_url)
    ? \App\Support\LearningAssetPath::resolveSectionVideoUrl((string) $selectedSection->video_url)
    : null;
  $moduleResources = collect($moduleResources ?? $lessonResources ?? []);
  $toolGroups = collect($toolGroups ?? []);
  $whiteboardGroups = collect($whiteboardGroups ?? []);
  $currentWhiteboardGroup = $currentWhiteboardGroup ?? null;
  $wordClouds = collect($wordClouds ?? []);
  $moduleLabel = $module->module_title ?? $module->module_name ?? ('Formation #' . $module->id);
  $moduleDetailUrl = $appendQuery(route('formateur.formations.detail', ['module' => $module->id]), $contextQuery);

  $defaultToolGroup = $toolGroups->firstWhere('id', (int) ($groupId ?? 0)) ?? $toolGroups->first();
  $defaultToolGroupId = (string) data_get($defaultToolGroup, 'id', '');
  $defaultToolModuleId = (string) ($module->id ?: data_get($defaultToolGroup, 'modules.0.id', ''));
  $defaultToolLectureId = (string) (($firstLecture?->id) ?: data_get($defaultToolGroup, 'modules.0.lectures.0.id', ''));
  $firstLectureUrl = $firstLecture
    ? $appendQuery(route('formateur.formations.lecture', [
        'module' => $module->id,
        'section' => $selectedSection->id,
        'lecture' => $firstLecture->id,
      ]), $contextQuery)
    : null;
@endphp

<div x-data="{
    mode: 'formateur',
    formateurBarOpen: true,
    inspectorOpen: false,
    activeTab: 'objectifs',
    selectedTool: 'live_quiz',
    toolGroups: @js($toolGroups),
    selectedToolGroupId: @js($defaultToolGroupId),
    selectedToolModuleId: @js($defaultToolModuleId),
    selectedToolLectureId: @js($defaultToolLectureId),
    tabStorageKey: @js('formateur-chapter-tab-' . ($selectedSection->id ?? 'default')),
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
    selectedToolWhiteboardUrl() {
        return this.selectedToolGroup()?.whiteboard_url || '#';
    },
    selectedToolManageUrl() {
        return this.selectedToolModule()?.manage_url || '#';
    },
    openPanel(tab, tool = null) {
        this.activeTab = tab;
        if (tool) {
            this.selectedTool = tool;
        }
        this.inspectorOpen = true;
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
        if (savedTab === 'objectifs' || savedTab === 'quiz' || savedTab === 'ressources' || savedTab === 'outils') {
            this.activeTab = savedTab;
        }

        this.$watch('activeTab', value => {
            window.localStorage.setItem(this.tabStorageKey, value);
        });
        this.$watch('selectedToolGroupId', () => this.syncToolSelections());
        this.$watch('selectedToolModuleId', () => this.syncToolSelections());
        this.syncToolSelections();
    }
}" class="flex flex-col h-[calc(100vh-var(--app-header-h,86px))] bg-white overflow-hidden">

  <div class="relative min-h-[2.25rem] bg-bleuone text-white shadow-md z-30 shrink-0 border-b border-bleuone-dark font-varela">
      <div x-show="formateurBarOpen" x-collapse.duration.200ms class="px-5 py-3 pr-12">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div class="min-w-0">
                  <span class="font-semibold text-orangeone uppercase text-[11px] tracking-[0.18em]">Chapitre formateur</span>
                  <p class="mt-1 truncate text-base font-semibold leading-tight md:text-lg" title="{{ $selectedSection->section_title }}">
                      {{ $selectedSection->section_title }}
                  </p>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                  @if($firstLectureUrl)
                      <a href="{{ $firstLectureUrl }}"
                         class="inline-flex items-center justify-center rounded-full border border-orangeone bg-orangeone px-4 py-2 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-orangeone-hover">
                          Commencer
                      </a>
                  @endif

                  <button type="button"
                          @click="openPanel('objectifs')"
                          class="inline-flex items-center justify-center rounded-full border border-white/25 px-4 py-2 text-xs font-bold uppercase tracking-wide transition"
                          :class="inspectorOpen && activeTab === 'objectifs' ? 'bg-white text-bleuone' : 'bg-white/10 text-white hover:bg-white hover:text-bleuone'">
                      Reperes
                  </button>

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
              :chapter="['label' => $chapterNo ? 'Ch. '.$chapterNo : 'Chapitre', 'title' => $selectedSection->section_title, 'url' => null]"
          />
      </div>

      <a href="{{ route('formateur.dashboard') }}"
         class="shrink-0 text-xs font-semibold text-gray-500 transition hover:text-orangeone hover:underline">
          Tableau de bord
      </a>
  </div>

  <div class="flex flex-1 overflow-hidden relative">

      <main class="relative bg-gray-50 transition-all duration-300 ease-in-out flex flex-col min-w-0"
            :class="inspectorOpen ? 'w-full lg:w-2/3 lg:border-r lg:border-gray-200' : 'w-full'">

          <div x-show="mode === 'formateur'" x-cloak
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="h-full overflow-y-auto custom-scrollbar p-6" style="display: none;">
              <div class="max-w-4xl mx-auto space-y-6">
                  <header class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-8">
                      <div class="flex flex-wrap items-center justify-between gap-4">
                          <div>
                              <span class="inline-flex rounded-full bg-bleuone/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-bleuone">
                                  Chapitre
                              </span>
                              <h1 class="mt-4 text-3xl font-raleway font-medium text-bleuone leading-tight">
                                  {{ $selectedSection->section_title }}
                              </h1>
                          </div>
                          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-right">
                              <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Lecons</p>
                              <p class="mt-1 text-2xl font-bold text-bleuone">{{ $lectures->count() }}</p>
                          </div>
                      </div>
                  </header>

                  <section class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-6">
                      <div class="flex items-start justify-between gap-4">
                          <div>
                              <h2 class="text-xl font-bold text-bleuone font-varela">Support de la section</h2>
                              <p class="mt-1 text-sm text-gray-600 font-lisible">
                                  Retrouvez ici la video et le point d'entree vers la premiere lecon.
                              </p>
                          </div>
                      </div>

                      @if($videoSrc)
                          <div class="mt-5" data-chapter-video-card>
                              <div class="relative w-full rounded-[16px] overflow-hidden border border-gray-200 bg-black" style="padding-top:56.25%;" role="region" aria-label="Video pedagogique">
                                  <video
                                      data-chapter-video
                                      class="absolute top-0 left-0 h-full w-full"
                                      controls
                                      preload="metadata"
                                      playsinline>
                                      <source src="{{ $videoSrc }}" type="video/mp4">
                                  </video>
                              </div>

                              <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                                  <div class="relative inline-block">
                                      <select
                                          data-rate-select
                                          class="w-48 pr-10 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-bleuone appearance-none focus:outline-none focus:ring-2 focus:ring-orangeone focus:border-orangeone"
                                          style="min-width: 11rem;">
                                          <option value="0.5">0.5×</option>
                                          <option value="0.75">0.75×</option>
                                          <option value="1" selected>1×</option>
                                          <option value="1.25">1.25×</option>
                                          <option value="1.5">1.5×</option>
                                          <option value="1.75">1.75×</option>
                                          <option value="2">2×</option>
                                      </select>
                                      <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#004461" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                              <path d="M6 9l6 6 6-6"></path>
                                          </svg>
                                      </span>
                                  </div>

                                  <div class="flex items-center gap-2">
                                      <button
                                          type="button"
                                          data-back-10
                                          class="rounded-md bg-bleuone px-4 py-2 text-white font-semibold hover:bg-bleuone-dark focus:outline-none focus:ring-2 focus:ring-orangeone">
                                          -10 s
                                      </button>
                                      <button
                                          type="button"
                                          data-fwd-10
                                          class="rounded-md bg-orangeone px-4 py-2 text-white font-semibold hover:bg-orangeone-hover focus:outline-none focus:ring-2 focus:ring-bleuone">
                                          +10 s
                                      </button>
                                  </div>
                              </div>
                          </div>
                      @else
                          <div class="mt-5 flex items-center justify-center rounded-[16px] border border-gray-200 bg-gray-50 p-6">
                              <img src="{{ asset('images/svg/Modules.svg') }}" alt="Apercu indisponible" class="block max-w-[320px] h-auto">
                          </div>
                      @endif
                  </section>

                  @if($firstLectureUrl)
                      <section class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-6">
                          <a href="{{ $firstLectureUrl }}"
                             class="w-full inline-flex items-center justify-center rounded-[16px] px-5 py-3 bg-orangeone text-white font-varela font-semibold hover:bg-orangeone-hover transition focus:outline-none focus:ring-4 focus:ring-orange-200">
                              Tester cette section
                          </a>

                          <p class="mt-3 text-sm text-gray-600 font-lisible">
                              Lance la premiere lecon du chapitre dans le parcours formateur.
                          </p>
                      </section>
                  @endif
              </div>
          </div>

          <div x-show="mode === 'stagiaire'" x-cloak
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="h-full overflow-y-auto custom-scrollbar p-6" style="display: none;">
              <main class="max-w-full mx-auto">
                  <header class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-8 mb-6">
                    <h1 class="text-titre font-raleway text-bleuone">
                      {{ $selectedSection->section_title }}
                    </h1>
                  </header>

                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <section x-data="{ openItem: 1 }" class="space-y-4">
                      <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 overflow-hidden">
                        <button
                          type="button"
                          @click="openItem = openItem === 1 ? null : 1"
                          class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 transition font-varela text-bleuone"
                          :aria-expanded="openItem === 1"
                          aria-controls="panel-objectifs">
                          <div class="min-w-0">
                            <div class="text-lg font-semibold">Objectifs</div>
                            <div class="text-sm text-gray-600 font-lisible mt-1">
                              Ce que l'apprenant doit viser dans les lecons de cette section
                            </div>
                          </div>

                          <svg x-show="openItem !== 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
                          </svg>
                          <svg x-show="openItem === 1" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 transform rotate-180" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
                          </svg>
                        </button>

                        <div
                          id="panel-objectifs"
                          x-show="openItem === 1"
                          x-collapse.duration.400ms
                          class="px-6 pb-6 pt-4 font-lisible text-[17px] text-gray-800 leading-relaxed">
                          @if($lecturesWithObjectives->isNotEmpty())
                            <div class="space-y-4">
                              @foreach($lecturesWithObjectives as $lec)
                                <div class="rounded-[16px] border border-gray-200 bg-gray-50 p-4">
                                  <div class="font-varela text-bleuone font-semibold">
                                    {{ $lec->lecture_title }}
                                  </div>

                                  <ul class="mt-2 list-disc pl-6 space-y-1 marker:text-orangeone">
                                    @foreach($lec->objectives as $obj)
                                      <li>
                                        <span class="font-medium">{{ $obj->title }}</span>
                                        @if(!empty($obj->description))
                                          <div class="text-sm text-gray-600 mt-1">{{ $obj->description }}</div>
                                        @endif
                                      </li>
                                    @endforeach
                                  </ul>
                                </div>
                              @endforeach
                            </div>
                          @else
                            <div class="text-sm text-gray-600">
                              Aucun objectif n'est renseigne pour le moment.
                            </div>
                          @endif
                        </div>
                      </div>

                      <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 overflow-hidden">
                        <button
                          type="button"
                          @click="openItem = openItem === 2 ? null : 2"
                          class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 transition font-varela text-bleuone"
                          :aria-expanded="openItem === 2"
                          aria-controls="panel-questions">
                          <div class="min-w-0">
                            <div class="text-lg font-semibold">Questions pour commencer</div>
                            <div class="text-sm text-gray-600 font-lisible mt-1">
                              Accroche et reperes avant de lancer la section
                            </div>
                          </div>

                          <svg x-show="openItem !== 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
                          </svg>
                          <svg x-show="openItem === 2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 transform rotate-180" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 8l4 4 4-4" clip-rule="evenodd"/>
                          </svg>
                        </button>

                        <div
                          id="panel-questions"
                          x-show="openItem === 2"
                          x-collapse.duration.400ms
                          class="px-6 pb-6 pt-4 font-lisible text-[17px] text-gray-800 leading-relaxed">
                          @if($rawQuestions !== '')
                            <div class="mb-5">
                              <div class="text-sm font-varela text-orangeone mb-2">
                                Questions de depart
                              </div>

                              @if($isHtml)
                                <div class="[&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1 [&_li::marker]:text-orangeone [&_p]:my-2">
                                  {!! $rawQuestions !!}
                                </div>
                              @else
                                @php
                                  $lines = collect(preg_split("/\r\n|\n|\r/", $rawQuestions))
                                    ->map(fn ($q) => trim($q))
                                    ->filter()
                                    ->values();
                                @endphp

                                @if($lines->isNotEmpty())
                                  <ul class="list-disc pl-6 space-y-1 marker:text-orangeone">
                                    @foreach($lines as $q)
                                      <li>{{ $q }}</li>
                                    @endforeach
                                  </ul>
                                @endif
                              @endif
                            </div>
                          @endif

                          @if($lectures->isNotEmpty())
                            <div class="mt-2">
                              <div class="text-sm font-varela text-orangeone mb-2">
                                Reperes par lecon
                              </div>

                              <div class="space-y-3">
                                @foreach($lectures as $lec)
                                  @php
                                    $quizCount = (int) ($lec->quiz_questions_count ?? 0);
                                    $plannedCount = (int) ($lec->quiz_questions_per_attempt ?? 0);
                                    $declaredCount = (int) ($lec->question_count ?? 0);
                                    $usesNativeQuiz = (bool) ($lec->quiz_enabled ?? false);

                                    $total = $usesNativeQuiz
                                      ? ($plannedCount > 0 ? $plannedCount : $quizCount)
                                      : $declaredCount;

                                    $badgeText = $usesNativeQuiz
                                      ? ($plannedCount > 0
                                        ? "Questions posees : {$plannedCount}"
                                        : ($quizCount > 0 ? "Banque quiz : {$quizCount}" : "Quiz non parametre"))
                                      : ($declaredCount > 0 ? "SCORM : {$declaredCount}" : "Non defini");
                                  @endphp

                                  <div class="rounded-[16px] border border-gray-200 bg-gray-50 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                      <div class="min-w-0">
                                        <div class="font-varela text-bleuone font-semibold truncate">
                                          {{ $lec->lecture_title }}
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                          {{ $total }} question{{ $total > 1 ? 's' : '' }}
                                        </div>
                                      </div>

                                      <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-sm bg-white border border-gray-200 text-gray-700">
                                        {{ $badgeText }}
                                      </span>
                                    </div>
                                  </div>
                                @endforeach
                              </div>
                            </div>
                          @endif

                          @if($rawQuestions === '' && $lectures->isEmpty())
                            <div class="text-sm text-gray-600">
                              Aucune question n'est renseignee pour le moment.
                            </div>
                          @endif
                        </div>
                      </div>
                    </section>

                    <aside class="space-y-6">
                      <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-6">
                        <div class="font-varela text-bleuone font-semibold mb-3">
                          Support de la section
                        </div>

                        @if($videoSrc)
                          <div data-chapter-video-card>
                            <div class="relative w-full rounded-[16px] overflow-hidden border border-gray-200" style="padding-top:56.25%;" role="region" aria-label="Video pedagogique">
                              <video
                                  data-chapter-video
                                  class="absolute top-0 left-0 w-full h-full"
                                  controls
                                  preload="metadata"
                                  playsinline>
                                <source src="{{ $videoSrc }}" type="video/mp4">
                              </video>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                              <div class="relative inline-block">
                                <select
                                    data-rate-select
                                    class="w-48 pr-10 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-bleuone appearance-none focus:outline-none focus:ring-2 focus:ring-orangeone focus:border-orangeone"
                                    style="min-width: 11rem;">
                                  <option value="0.5">0.5×</option>
                                  <option value="0.75">0.75×</option>
                                  <option value="1" selected>1×</option>
                                  <option value="1.25">1.25×</option>
                                  <option value="1.5">1.5×</option>
                                  <option value="1.75">1.75×</option>
                                  <option value="2">2×</option>
                                </select>

                                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#004461" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 9l6 6 6-6"></path>
                                  </svg>
                                </span>
                              </div>

                              <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    data-back-10
                                    class="rounded-md bg-bleuone px-4 py-2 text-white font-semibold hover:bg-bleuone-dark focus:outline-none focus:ring-2 focus:ring-orangeone">
                                  -10 s
                                </button>

                                <button
                                    type="button"
                                    data-fwd-10
                                    class="rounded-md bg-orangeone px-4 py-2 text-white font-semibold hover:bg-orangeone-hover focus:outline-none focus:ring-2 focus:ring-bleuone">
                                  +10 s
                                </button>
                              </div>
                            </div>
                          </div>
                        @else
                          <div class="flex items-center justify-center rounded-[16px] border border-gray-200 bg-gray-50 p-6">
                            <img src="{{ asset('images/svg/Modules.svg') }}" alt="Apercu indisponible" class="block max-w-[320px] h-auto">
                          </div>
                        @endif
                      </div>

                      @if($firstLectureUrl)
                        <div class="bg-white rounded-[20px] shadow-soft border border-gray-100 p-6">
                          <a href="{{ $firstLectureUrl }}"
                          class="w-full inline-flex items-center justify-center rounded-[16px] px-5 py-3 bg-orangeone text-white font-varela font-semibold hover:bg-orangeone-hover transition focus:outline-none focus:ring-4 focus:ring-orange-200">
                            Tester cette section
                          </a>

                          <p class="mt-3 text-sm text-gray-600 font-lisible">
                            Lance la premiere lecon de la section.
                          </p>
                        </div>
                      @endif
                    </aside>
                  </div>
              </main>
          </div>
      </main>

      <aside x-show="inspectorOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-cloak
             class="w-full max-w-xl bg-slate-50 border-l border-gray-200 flex flex-col shadow-xl z-20 absolute right-0 top-0 bottom-0 lg:relative lg:w-1/3 lg:max-w-none lg:translate-x-0">
          <div class="flex-1 overflow-y-auto custom-scrollbar p-6">

              <div class="mb-5 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                  <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                          <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500" aria-label="Fil d'ariane du chapitre">
                              <a href="{{ $moduleDetailUrl }}"
                                 class="truncate max-w-[14rem] transition hover:text-bleuone"
                                 title="{{ $moduleLabel }}">
                                  {{ $moduleLabel }}
                              </a>
                              <span class="text-slate-300">></span>
                              <span title="{{ $selectedSection->section_title }}" class="text-bleuone">{{ $chapterNo ? 'Ch. ' . $chapterNo : 'Chapitre' }}</span>
                          </nav>
                      </div>
                      <button type="button"
                              @click="inspectorOpen = false"
                              class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:border-bleuone hover:text-bleuone"
                              aria-label="Fermer le panneau">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>

                  <div class="mt-4 flex flex-wrap gap-2">
                      <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-orangeone">
                          {{ $lecturesWithObjectives->count() }} lecon{{ $lecturesWithObjectives->count() > 1 ? 's' : '' }} avec objectifs
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

              <div x-show="activeTab === 'objectifs'" x-cloak
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

                  @if($lecturesWithObjectives->isNotEmpty())
                      <div class="space-y-4">
                          @foreach($lecturesWithObjectives as $lec)
                              <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                                  <h4 class="text-base font-bold text-bleuone">{{ $lec->lecture_title }}</h4>
                                  <ul class="mt-3 space-y-3">
                                      @foreach($lec->objectives as $obj)
                                          <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                              <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-orangeone"></span>
                                              <span>
                                                  <span class="font-semibold text-slate-900">{{ $obj->title }}</span>
                                                  @if(!empty($obj->description))
                                                      <span class="mt-1 block text-xs text-slate-500">{{ $obj->description }}</span>
                                                  @endif
                                              </span>
                                          </li>
                                      @endforeach
                                  </ul>
                              </div>
                          @endforeach
                      </div>
                  @else
                      <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                          <p class="text-sm font-medium text-slate-500">Aucun objectif n'est renseigne pour ce chapitre.</p>
                      </div>
                  @endif
              </div>

              <div x-show="activeTab === 'quiz'" x-cloak
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   style="display: none;">
                  <div class="mb-4">
                      <h3 class="font-raleway text-lg font-bold text-orangeone">Quiz & Corrigés</h3>
                  </div>

                  <div class="space-y-5">
                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <h4 class="text-base font-bold text-bleuone">Questions pour commencer</h4>

                          @if($rawQuestions !== '')
                              @if($isHtml)
                                  <div class="mt-4 text-sm text-slate-700 leading-relaxed [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-1 [&_li::marker]:text-orangeone [&_p]:my-2">
                                      {!! $rawQuestions !!}
                                  </div>
                              @else
                                  @php
                                      $questions = collect(preg_split("/\r\n|\n|\r/", $rawQuestions))
                                          ->map(fn ($q) => trim($q))
                                          ->filter()
                                          ->values();
                                  @endphp

                                  @if($questions->isNotEmpty())
                                      <ul class="mt-4 space-y-3">
                                          @foreach($questions as $q)
                                              <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                                  {{ $q }}
                                              </li>
                                          @endforeach
                                      </ul>
                                  @endif
                              @endif
                          @else
                              <p class="mt-3 text-sm text-slate-500">Aucune question de depart n'est renseignee.</p>
                          @endif
                      </div>

                      <div class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                          <h4 class="text-base font-bold text-bleuone">Reperes par lecon</h4>

                          @if($lectures->isNotEmpty())
                              <div class="mt-4 space-y-3">
                                  @foreach($lectures as $lec)
                                      @php
                                          $quizCount = (int) ($lec->quiz_questions_count ?? 0);
                                          $plannedCount = (int) ($lec->quiz_questions_per_attempt ?? 0);
                                          $declaredCount = (int) ($lec->question_count ?? 0);
                                          $usesNativeQuiz = (bool) ($lec->quiz_enabled ?? false);

                                          $total = $usesNativeQuiz
                                              ? ($plannedCount > 0 ? $plannedCount : $quizCount)
                                              : $declaredCount;

                                          $badgeText = $usesNativeQuiz
                                              ? ($plannedCount > 0
                                                  ? "Questions posees : {$plannedCount}"
                                                  : ($quizCount > 0 ? "Banque quiz : {$quizCount}" : "Quiz non parametre"))
                                              : ($declaredCount > 0 ? "SCORM : {$declaredCount}" : "Non defini");
                                      @endphp

                                      <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                          <div class="flex items-start justify-between gap-3">
                                              <div class="min-w-0">
                                                  <p class="truncate text-sm font-bold text-slate-800">{{ $lec->lecture_title }}</p>
                                                  <p class="mt-1 text-xs text-slate-500">{{ $total }} question{{ $total > 1 ? 's' : '' }}</p>
                                              </div>
                                              <span class="shrink-0 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-bleuone">
                                                  {{ $badgeText }}
                                              </span>
                                          </div>
                                      </div>
                                  @endforeach
                              </div>
                          @else
                              <p class="mt-3 text-sm text-slate-500">Aucune lecon rattachee a ce chapitre.</p>
                          @endif
                      </div>
                  </div>
              </div>

              <div x-show="activeTab === 'ressources'" x-cloak
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

                          @if($firstLecture)
                              <form action="{{ route('formateur.formations.lesson.resources.store', ['module' => $module->id, 'section' => $section->id, 'lecture' => $firstLecture->id]) }}"
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
                                      id="chapter_resource_title"
                                      type="text"
                                      name="title"
                                      maxlength="255"
                                      placeholder="Titre de la ressource"
                                      class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                                  >

                                  <input
                                      id="chapter_resource_file"
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
                          @else
                              <div x-show="manageResources"
                                   x-collapse.duration.300ms
                                   class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-500">
                                  Ajout indisponible tant qu'aucune lecon n'est rattachee a ce chapitre.
                              </div>
                          @endif
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

                                              @if($firstLecture)
                                                  <form action="{{ route('formateur.formations.lesson.resources.visibility', ['module' => $module->id, 'section' => $section->id, 'lecture' => $firstLecture->id, 'resource' => $resource->id]) }}"
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
                                                      :action="route('formateur.formations.lesson.resources.destroy', ['module' => $module->id, 'section' => $section->id, 'lecture' => $firstLecture->id, 'resource' => $resource->id])"
                                                      method="DELETE"
                                                      confirm-label="Supprimer"
                                                  />
                                              @endif
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
                                      <label for="tool-group-chapter" class="mb-1 block text-xs font-semibold text-slate-600">Groupe</label>
                                      <select id="tool-group-chapter"
                                              x-model="selectedToolGroupId"
                                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                                          <option value="">Choisir un groupe</option>
                                          <template x-for="group in toolGroups" :key="group.id">
                                              <option :value="String(group.id)" x-text="group.name"></option>
                                          </template>
                                      </select>
                                  </div>

                                  <div>
                                      <label for="tool-module-chapter" class="mb-1 block text-xs font-semibold text-slate-600">Formation</label>
                                      <select id="tool-module-chapter"
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
                                      <label for="tool-lecture-chapter" class="mb-1 block text-xs font-semibold text-slate-600">Lecon</label>
                                      <select id="tool-lecture-chapter"
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
                                      <p class="mt-1 text-xs text-slate-500">Accedez au parametrage pedagogique de la formation selectionnee pour ce groupe.</p>
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-chapter-video-card]').forEach(card => {
      const video = card.querySelector('[data-chapter-video]');
      const rateSelect = card.querySelector('[data-rate-select]');
      const backButton = card.querySelector('[data-back-10]');
      const forwardButton = card.querySelector('[data-fwd-10]');

      if (!video) {
        return;
      }

      if (rateSelect) {
        rateSelect.addEventListener('change', () => {
          video.playbackRate = parseFloat(rateSelect.value || '1');
        });
      }

      if (backButton) {
        backButton.addEventListener('click', () => {
          video.currentTime = Math.max(0, video.currentTime - 10);
        });
      }

      if (forwardButton) {
        forwardButton.addEventListener('click', () => {
          video.currentTime = video.currentTime + 10;
        });
      }
    });
  });
</script>
@endsection
