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

    // Edition de la leçon reservee a l'auteur de la formation (App\Domains\ModulesFormateur\Support\AccesModule::assertOwner)
    $isModuleAuthor = (bool) ($module->is_trainer_authored ?? false)
        && (int) ($module->formateur_id ?? 0) === (int) auth()->id();
    $editLectureUrl = $lectureId
        ? route('formateur.modules.builder.lectures.edit', ['lecture' => $lectureId])
        : null;
@endphp

@if ($lectureId)
  <script>window.currentLectureId = {{ $lectureId }};</script>
@endif

{{-- Wrapper Principal Alpine --}}
<div x-data="{
    formateurBarOpen: true,
    fullscreenSupported: false,
    fullscreenActive: false,
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
    init() {
        this.fullscreenSupported = !!document.fullscreenEnabled;
        this.syncFullscreenState();
        document.addEventListener('fullscreenchange', () => this.syncFullscreenState());
    }
}" class="flex flex-col h-[calc(100vh-var(--app-header-h,86px))] bg-white overflow-hidden">

  {{-- BARRE D'ACTIONS FORMATEUR --}}
  <div class="relative min-h-[2.25rem] bg-bleuone text-white shadow-md z-30 shrink-0 border-b border-bleuone-dark font-varela">
      <div x-show="formateurBarOpen" x-collapse.duration.200ms class="px-5 py-3 pr-12">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div class="min-w-0 flex items-center gap-3">
                  <button type="button"
                          @click="$dispatch('toggle-sidebar')"
                          aria-controls="module-sidebar-wrapper"
                          aria-label="Afficher ou masquer le plan"
                          title="Afficher ou masquer le plan"
                          class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/25 text-white transition hover:bg-white hover:text-bleuone">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                          <path d="M4 6h16" stroke-linecap="round"/>
                          <path d="M4 12h16" stroke-linecap="round"/>
                          <path d="M4 18h16" stroke-linecap="round"/>
                      </svg>
                  </button>

                  <div class="min-w-0">
                      <span class="font-semibold text-orangeone uppercase text-[11px] tracking-[0.18em]">Lecture formateur</span>
                      <p class="mt-1 truncate text-base font-semibold leading-tight md:text-lg" title="{{ $lecture->lecture_title }}">
                          {{ $lecture->lecture_title }}
                      </p>
                  </div>
              </div>

              <div class="flex flex-wrap items-center gap-3">
                  <a href="{{ route('formateur.dashboard') }}"
                     class="text-xs font-semibold text-white/70 transition hover:text-white hover:underline">
                      Tableau de bord
                  </a>

                  @if($isModuleAuthor && $editLectureUrl)
                      <a href="{{ $editLectureUrl }}"
                         class="inline-flex items-center justify-center rounded-full border border-white/25 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-white hover:text-bleuone">
                          Modifier
                      </a>
                  @else
                      <span class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white/40 cursor-not-allowed"
                            title="Vous n'etes pas l'auteur de ce contenu : seul le formateur qui a cree cette formation peut la modifier.">
                          Modifier
                      </span>
                  @endif
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

  {{-- CORPS DE PAGE --}}
  <div class="flex flex-1 overflow-hidden relative">
      
      {{-- ZONE CONTENU (SCORM / SLIDES) --}}
      <main class="relative bg-gray-100 transition-all duration-300 ease-in-out flex flex-col min-w-0 w-full">
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
                  <div class="h-full flex flex-col bg-white">
                      <div class="flex-1 overflow-y-auto py-10">
                          @include('shared.lecture_blocks', ['blocks' => $lecture->content_blocks ?? [], 'lecture' => $lecture, 'interactif' => true])
                      </div>
                      <div class="border-t border-gray-200 bg-white px-4 py-3 flex items-center justify-end gap-3"
                           x-data="{}" x-show="!$store.lectureProgress || $store.lectureProgress.isComplete" x-cloak>
                          <a href="{{ $nextUrl !== '#' ? $nextUrl : $finalUrl }}"
                             class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white rounded-full text-xs font-bold uppercase hover:bg-orangeone-hover transition">
                              Lecon suivante
                              <i class="ti ti-arrow-right"></i>
                          </a>
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
