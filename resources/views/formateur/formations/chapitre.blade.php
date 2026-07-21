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

  $rawQuestions = trim((string) ($selectedSection->section_html ?? ''));
  $isHtml = Str::contains($rawQuestions, ['<ul', '<ol', '<li', '<p', '<br', '</']);

  $firstLecture = $lectures->first();
  $videoSrc = !empty($selectedSection->video_url)
    ? \App\Support\LearningAssetPath::resolveSectionVideoUrl((string) $selectedSection->video_url)
    : null;
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
}" class="flex flex-col h-[calc(100vh-var(--app-header-h,86px))] bg-white overflow-hidden">

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
                      <span class="font-semibold text-orangeone uppercase text-[11px] tracking-[0.18em]">Chapitre formateur</span>
                      <p class="mt-1 truncate text-base font-semibold leading-tight md:text-lg" title="{{ $selectedSection->section_title }}">
                          {{ $selectedSection->section_title }}
                      </p>
                  </div>
              </div>

              <div class="flex flex-wrap items-center gap-3">
                  <a href="{{ route('formateur.dashboard') }}"
                     class="text-xs font-semibold text-white/70 transition hover:text-white hover:underline">
                      Tableau de bord
                  </a>

                  @if($firstLectureUrl)
                      <a href="{{ $firstLectureUrl }}"
                         class="inline-flex items-center justify-center rounded-full border border-orangeone bg-orangeone px-4 py-2 text-xs font-bold uppercase tracking-wide text-white transition hover:bg-orangeone-hover">
                          Commencer
                      </a>
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

  <div class="flex flex-1 overflow-hidden relative">

      <main class="relative bg-gray-50 transition-all duration-300 ease-in-out flex flex-col min-w-0 w-full">

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
                                        ? "Quiz de validation : {$plannedCount} questions"
                                        : ($quizCount > 0 ? "Questions préparées : {$quizCount}" : "Quiz de validation non paramétré"))
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
