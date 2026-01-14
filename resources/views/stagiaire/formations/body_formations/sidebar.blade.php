{{-- resources/views/stagiaire/formations/body_formations/sidebar.blade.php --}}
{{-- Sidebar améliorée : prend en compte lectureStats (quiz + scorm slides) --}}
{{-- Attend : $module (sections->lectures), $lectureStats, $formateur (optionnel), $selectedLecture (optionnel) --}}

<aside
  x-cloak
  x-show="sidebarOpen"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="-translate-x-full opacity-0"
  x-transition:enter-end="translate-x-0 opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="translate-x-0 opacity-100"
  x-transition:leave-end="-translate-x-full opacity-0"
  class="w-80 flex-shrink-0 bg-white border border-gray-200 rounded-2xl shadow-lg md:shadow-none flex flex-col h-[calc(100vh-64px)] fixed md:sticky top-16 z-10 overflow-visible"
  role="navigation"
  aria-label="Navigation du cours"
>
  @php
    $activeSectionId = null;

    if (isset($selectedLecture) && optional($selectedLecture)->section_id) {
      $activeSectionId = (int) optional($selectedLecture)->section_id;
    } elseif ($routeSection = request()->route('section')) {
      $activeSectionId = is_object($routeSection)
        ? (int) ($routeSection->id ?? 0)
        : (int) $routeSection;
    }

    $activeLessonId = isset($selectedLecture) ? (int) $selectedLecture->id : null;

    // Largeur colonne gauche : rond chapitre / icônes leçon
    $leftCol = '44px';

    // Helpers de statut
    $isCompleted = function (?string $s) {
      $s = strtolower((string)$s);
      return in_array($s, ['completed','passed','acquired'], true);
    };
    $isFailed = function (?string $s) {
      $s = strtolower((string)$s);
      return in_array($s, ['failed','not_acquired'], true);
    };
    $isInProgress = function (?string $s) {
      $s = strtolower((string)$s);
      return in_array($s, ['in_progress','incomplete'], true);
    };
  @endphp

  <div class="flex-1 overflow-y-auto p-0 custom-scrollbar">
    <div class="p-0">
      <ol class="space-y-2">
        @foreach ($module->sections as $sIndex => $section)
          @php
            $chapterNo = $sIndex + 1;

            $isActiveChapter = ($activeSectionId && (int)$activeSectionId === (int)$section->id);

            $totalL = $section->lectures->count();
            $doneValidated = 0;

            foreach ($section->lectures as $lecP) {
              $stP = $lectureStats[$lecP->id]['status'] ?? 'not_started';
              if ($isCompleted($stP)) $doneValidated++;
            }

            $chapterStateLabel = 'À faire';
            if ($doneValidated > 0 && $doneValidated < $totalL) $chapterStateLabel = 'En cours';
            if ($totalL > 0 && $doneValidated === $totalL) $chapterStateLabel = 'Terminé';

            $openDefault = $isActiveChapter ? 'true' : 'false';

            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));
          @endphp

          <li x-data="{ open: {{ $openDefault }} }">
            <div
              class="grid transition-colors {{ $isActiveChapter ? 'bg-orangeone/10' : 'bg-white hover:bg-gray-50' }}"
              style="grid-template-columns: {{ $leftCol }} 1fr;"
            >
              {{-- Lien chapitre --}}
              <a
                href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
                class="col-span-2 grid items-start transition-colors
                       {{ $isSectionPage ? 'bg-orangeone/15' : '' }}
                       hover:bg-orangeone/5"
                style="grid-template-columns: {{ $leftCol }} 1fr;"
                @if($isSectionPage) aria-current="page" @endif
              >
                <div class="pt-4 flex justify-center">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-lg
                           {{ $isActiveChapter ? 'bg-orangeone text-white' : 'bg-gray-100 text-gray-700' }}"
                    aria-hidden="true"
                  >
                    {{ $chapterNo }}
                  </div>
                </div>

                <div class="min-w-0 py-4 pr-3 pl-2">
                  <div class="flex items-start justify-between gap-2">
                    <div class="block min-w-0">
                      <h3 class="text-base font-bold leading-snug break-words
                                 {{ $isSectionPage ? 'text-bleuone' : ($isActiveChapter ? 'text-bleuone' : 'text-gray-800') }}">
                        {{ $section->section_title }}
                      </h3>

                      <p class="mt-1 text-sm text-gray-500">
                        <span class="font-medium text-gray-700">{{ $doneValidated }}/{{ $totalL }}</span>
                        <span class="mx-2 text-gray-300">·</span>
                        <span class="text-gray-600">{{ $chapterStateLabel }}</span>
                      </p>
                    </div>

                    {{-- Accordéon --}}
                    <button
                      type="button"
                      class="shrink-0 mt-0.5 p-2 text-gray-500 hover:text-orangeone hover:bg-white/70 focus:outline-none"
                      @click.prevent="open = !open"
                      :aria-expanded="open.toString()"
                      aria-label="Ouvrir ou fermer le chapitre {{ $chapterNo }}"
                    >
                      <svg class="w-6 h-6 transition-transform duration-200"
                           :class="open ? 'rotate-180' : ''"
                           fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </a>

              <div class="col-span-2 border-b border-gray-200"></div>

              {{-- Leçons --}}
              <div x-show="open" x-collapse class="col-span-2 pb-0">
                <ul class="space-y-1">
                  @foreach ($section->lectures as $lec)
                    @php
                      $st = $lectureStats[$lec->id] ?? null;
                      $stat = $st['status'] ?? 'not_started';

                      $isActiveLesson = (isset($selectedLecture) && (int)$selectedLecture->id === (int)$lec->id);

                      $validated = $isCompleted($stat);
                      $failed = $isFailed($stat);
                      $inProgress = $isInProgress($stat);

                      // Libellé principal
                      $label = 'À faire';
                      if ($inProgress) $label = 'En cours';
                      if ($validated) $label = 'Validée';
                      if ($failed) $label = 'Non validée';

                      // Détails quiz / diapositives
                      $isQuiz = (bool) ($st['quiz'] ?? false);

                      $qAnswered = (int) ($st['questions_answered'] ?? 0);
                      $qTotal    = (int) ($st['questions_total'] ?? 0);
                      $qCorrect  = (int) ($st['questions_correct'] ?? 0);
                      $qScore    = $st['quiz_score'] ?? null;

                      $slides    = (int) ($st['slides'] ?? ($lec->slide_count ?? 0));
                      $time      = $st['session_time'] ?? null;
                    @endphp

                    <li>
                      <a
                        href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id]) }}"
                        class="relative grid items-start py-2 transition-colors
                          {{ $isActiveLesson ? 'bg-bleuone text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                        style="grid-template-columns: {{ $leftCol }} 1fr;"
                        @if($isActiveLesson) aria-current="page" @endif
                      >
                        {{-- Colonne gauche : icône --}}
                        <span class="flex justify-center pt-2" aria-hidden="true">
                          @if($validated)
                            <svg class="w-5 h-5 {{ $isActiveLesson ? 'text-white' : 'text-vertone' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                          @elseif($failed)
                            <svg class="w-5 h-5 {{ $isActiveLesson ? 'text-white' : 'text-red-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          @elseif($inProgress)
                            <svg class="w-5 h-5 {{ $isActiveLesson ? 'text-white' : 'text-orangeone' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <circle cx="12" cy="12" r="10" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                            </svg>
                          @else
                            <svg class="w-5 h-5 {{ $isActiveLesson ? 'text-white' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <circle cx="12" cy="12" r="10" />
                            </svg>
                          @endif
                        </span>

                        {{-- Colonne droite : titre + infos --}}
                        <span class="min-w-0 pr-3">
                          <span class="block text-sm font-semibold leading-snug break-words {{ $isActiveLesson ? 'font-bold' : '' }}">
                            {{ $lec->lecture_title }}
                          </span>

                          {{-- Ligne statut --}}
                          <span class="block text-xs mt-0.5 {{ $isActiveLesson ? 'text-white/90' : 'text-gray-500' }}">
                            {{ $label }}
                            @if($isQuiz)
                              <span class="mx-2 {{ $isActiveLesson ? 'text-white/60' : 'text-gray-300' }}">·</span>
                              <span class="{{ $isActiveLesson ? 'text-white/90' : 'text-gray-600' }}">
                                Quiz : {{ $qAnswered }}/{{ $qTotal }}
                                @if(!is_null($qScore)) – {{ $qScore }}% @endif
                              </span>
                            @else
                              <span class="mx-2 {{ $isActiveLesson ? 'text-white/60' : 'text-gray-300' }}">·</span>
                              <span class="{{ $isActiveLesson ? 'text-white/90' : 'text-gray-600' }}">
                                Diapositives : {{ $slides }}
                                @if(!empty($time)) – Temps : {{ $time }} @endif
                              </span>
                            @endif
                          </span>

                          {{-- Détail optionnel : bonnes réponses --}}
                          @if($isQuiz && $qTotal > 0)
                            <span class="block text-[11px] mt-0.5 {{ $isActiveLesson ? 'text-white/80' : 'text-gray-400' }}">
                              Bonnes réponses : {{ $qCorrect }}/{{ $qTotal }}
                            </span>
                          @endif
                        </span>
                      </a>
                    </li>
                  @endforeach
                </ul>
              </div>

            </div>
          </li>
        @endforeach
      </ol>
    </div>
  </div>

  {{-- Pied de page --}}
  <div class="p-4 border-t border-gray-200 bg-gray-50">
    <a
      href="mailto:{{ $formateur->email ?? 'support@oneduc.fr' }}"
      class="flex items-center gap-3 p-3 bg-white border border-gray-200 shadow-sm hover:border-orangeone hover:shadow-md transition-all group rounded-xl"
    >
      <div class="bg-blue-50 text-bleuone p-2 group-hover:bg-orangeone group-hover:text-white transition-colors rounded-lg">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
      </div>
      <div>
        <p class="text-base font-bold text-gray-800 leading-none mb-1">Une question ?</p>
        <p class="text-sm text-gray-500 group-hover:text-orangeone">Contacter le formateur</p>
      </div>
    </a>
  </div>
</aside>
