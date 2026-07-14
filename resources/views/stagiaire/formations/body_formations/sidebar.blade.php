{{-- resources/views/stagiaire/formations/body_formations/sidebar.blade.php --}}

<aside
  x-cloak
  x-show="sidebarOpen"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0 scale-95"
  x-transition:enter-end="opacity-100 scale-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100 scale-100"
  x-transition:leave-end="opacity-0 scale-95"
  class="w-80 flex-shrink-0 bg-white border-r border-gray-100 flex flex-col fixed md:sticky z-20"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
>
  @php
    $activeSectionId = null;

    if (isset($selectedLecture) && optional($selectedLecture)->section_id) {
      $activeSectionId = (int) optional($selectedLecture)->section_id;
    } elseif ($routeSection = request()->route('section')) {
      $activeSectionId = is_object($routeSection) ? (int) ($routeSection->id ?? 0) : (int) $routeSection;
    }

    $activeLessonId = isset($selectedLecture) ? (int) $selectedLecture->id : null;

    // Accordéon : chapitre ouvert par défaut = chapitre actif, sinon le premier chapitre
    $initialOpenSectionId = $activeSectionId ?: optional($module->sections->first())->id;

    // Carte d'en-tête : progression réelle, agrégée sur tout le module
    $sidebarChapterCount = $module->sections->count();
    $sidebarTotalLessons = 0;
    $sidebarCompletedLessons = 0;
    foreach ($module->sections as $sidebarSection) {
      foreach ($sidebarSection->lectures as $sidebarLecture) {
        $sidebarTotalLessons++;
        if (($lectureStats[$sidebarLecture->id]['status'] ?? null) === 'completed') {
          $sidebarCompletedLessons++;
        }
      }
    }
    $sidebarProgressPercent = $sidebarTotalLessons > 0
      ? (int) round(($sidebarCompletedLessons / $sidebarTotalLessons) * 100)
      : 0;

    $statusIcon = function (?string $status): array {
      $s = strtolower((string) $status);

      return match ($s) {
        'completed'    => ['icon' => '✓', 'class' => 'text-vertone', 'label' => 'Acquise'],
        'in_progress'  => ['icon' => '⏳', 'class' => 'text-orangeone', 'label' => 'En cours'],
        'failed'       => ['icon' => '✗', 'class' => 'text-orangeone', 'label' => 'Non acquise'],
        default        => ['icon' => '✗', 'class' => 'text-gray-400', 'label' => 'Non commencé'],
      };
    };

    $quizLabel = function (array $st): array {
      // SCORM / pas de quiz
      if (empty($st['quiz'])) {
        return ['text' => 'Quiz : non requis', 'count' => null, 'class' => 'text-gray-400'];
      }

      $total    = (int) ($st['questions_total'] ?? 0);
      $answered = (int) ($st['questions_answered'] ?? 0);

      // Sécurité si le total n’est pas connu
      if ($total <= 0) {
        return ['text' => 'Quiz : non requis', 'count' => null, 'class' => 'text-gray-400'];
      }

      if ($answered <= 0) {
        return ['text' => 'Quiz : non commencé', 'count' => "0/{$total}", 'class' => 'text-gray-500'];
      }

      if ($answered < $total) {
        return ['text' => 'Quiz : à faire', 'count' => "{$answered}/{$total}", 'class' => 'text-orangeone'];
      }

      return ['text' => 'Quiz : terminé', 'count' => "{$total}/{$total}", 'class' => 'text-vertone'];
    };
  @endphp

  <div x-data="{ openSidebarSection: @js($initialOpenSectionId) }" class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30">
    <div class="py-4 px-3">
      <div class="mb-3 rounded-xl border border-orange-100 bg-white px-3 py-2.5 shadow-sm">
        <p class="text-[9px] font-black uppercase tracking-[0.18em] text-orangeone">
          Plan de la formation
        </p>
        <h2 class="mt-1 text-[15px] font-black leading-tight text-bleuone" title="{{ $module->module_title }}">
          {{ $module->module_title }}
        </h2>

        @if($sidebarTotalLessons > 0)
          <div class="mt-2.5">
            <div class="mb-1 flex items-end justify-between gap-2">
              <span class="font-varela text-[9px] font-black uppercase tracking-[0.16em] text-slate-400">
                Votre avancée
              </span>
              <span class="font-varela text-[13px] font-black text-orangeone">
                {{ $sidebarProgressPercent }}%
              </span>
            </div>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
              <div
                class="h-full rounded-full bg-orangeone transition-all duration-1000 ease-out"
                style="width: {{ $sidebarProgressPercent }}%"
              ></div>
            </div>
          </div>
        @endif

        <p class="mt-1 flex items-center justify-between gap-2 text-[10px] font-semibold text-slate-500">
          @if($sidebarTotalLessons > 0)
            <span class="whitespace-nowrap text-slate-400">
              {{ $sidebarCompletedLessons }} / {{ $sidebarTotalLessons }} leçon{{ $sidebarTotalLessons > 1 ? 's' : '' }} terminée{{ $sidebarCompletedLessons > 1 ? 's' : '' }}
            </span>
          @endif

          <span class="whitespace-nowrap">
            {{ $sidebarChapterCount }} chapitre{{ $sidebarChapterCount > 1 ? 's' : '' }}
            ·
            {{ $sidebarTotalLessons }} leçon{{ $sidebarTotalLessons > 1 ? 's' : '' }}
          </span>
        </p>
      </div>

      <ol class="space-y-3">
        @foreach ($module->sections as $sIndex => $section)
          @php
            $chapterNo = $sIndex + 1;
            $isActiveChapter = ($activeSectionId && (int) $activeSectionId === (int) $section->id);

            $totalL = $section->lectures->count();
            $doneValidated = 0;
            foreach ($section->lectures as $lecP) {
              if (($lectureStats[$lecP->id]['status'] ?? null) === 'completed') $doneValidated++;
            }

            // Page "chapitre" : on est sur la section sans leçon sélectionnée
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));

            $isChapterCompleted = $totalL > 0 && $doneValidated >= $totalL;

            $chapterHeaderBgClass = $isChapterCompleted
              ? 'bg-vertone/5'
              : ($isSectionPage
                ? 'bg-orange-50'
                : ($isActiveChapter ? 'bg-blue-50/40' : 'hover:bg-gray-50'));
            $chapterHeaderBorderClass = $isChapterCompleted
              ? 'border-vertone'
              : ($isSectionPage
                ? 'border-orangeone'
                : ($isActiveChapter ? 'border-bleuone' : 'border-transparent'));
            $chapterNumberClass = $isChapterCompleted
              ? 'bg-vertone text-white border-vertone'
              : ($isSectionPage
                ? 'bg-orangeone text-white border-orangeone'
                : ($isActiveChapter ? 'bg-bleuone text-white border-bleuone' : 'bg-gray-100 text-gray-500 border-gray-200'));
            $chapterTitleClass = $isChapterCompleted
              ? 'text-bleuone'
              : ($isSectionPage ? 'text-orangeone' : 'text-bleuone');
          @endphp

          <li class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            {{-- EN-TÊTE CHAPITRE --}}
            <div class="flex items-stretch">
              <a
                href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
                class="flex-1 min-w-0 grid items-center transition-all py-4 border-l-4 {{ $chapterHeaderBgClass }} {{ $chapterHeaderBorderClass }}"
                style="grid-template-columns: 44px 1fr;"
                aria-current="{{ $isSectionPage ? 'page' : 'false' }}"
              >
                <div class="flex justify-center">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border {{ $chapterNumberClass }}">
                    {{ $chapterNo }}
                  </div>
                </div>

                <div class="min-w-0 pr-2">
                  <h3 class="text-[15px] font-bold leading-tight truncate max-w-[190px] {{ $chapterTitleClass }}"
                    title="{{ $section->section_title }}"
                  >
                    Ch. {{ $chapterNo }} - {{ $section->section_title }}
                  </h3>

                  <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
                    {{ $doneValidated }}/{{ $totalL }} leçons terminées
                  </span>
                </div>
              </a>

              <button
                type="button"
                @click="openSidebarSection = (openSidebarSection === {{ $section->id }} ? null : {{ $section->id }})"
                class="flex w-11 flex-shrink-0 items-center justify-center border-l-4 border-transparent transition {{ $chapterHeaderBgClass }}"
                :aria-expanded="(openSidebarSection === {{ $section->id }}).toString()"
                aria-controls="sidebar-section-{{ $section->id }}"
                aria-label="Afficher ou masquer les leçons du chapitre {{ $chapterNo }}"
              >
                <span
                  class="flex h-8 w-8 items-center justify-center rounded-full border border-orange-100 bg-orange-50 text-orangeone transition"
                  :class="openSidebarSection === {{ $section->id }} ? 'bg-orangeone text-white border-orangeone' : ''"
                >
                  <svg
                    :class="openSidebarSection === {{ $section->id }} ? 'rotate-180' : ''"
                    class="h-5 w-5 transition-transform duration-200"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
              </button>
            </div>

            {{-- LISTE DES LEÇONS --}}
            <div
              id="sidebar-section-{{ $section->id }}"
              x-show="openSidebarSection === {{ $section->id }}"
              x-collapse.duration.400ms
              x-cloak
              class="border-t border-gray-50 bg-white"
            >
              <ul class="py-1">
                @foreach ($section->lectures as $lec)
                  @php
                    $lessonNo = $loop->iteration;
                    $st = $lectureStats[$lec->id] ?? [];
                    $status = $st['status'] ?? 'not_started';

                    $isActiveLesson = ($activeLessonId && (int) $activeLessonId === (int) $lec->id);

                    $ico = $statusIcon($status);
                    $q   = $quizLabel($st);
                    $lectureUrl = route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id]);
                    $activityCount = $lec->quizQuestions()->eligibleForInlineActivity()->count();
                  @endphp

                  <li>
                    <a
                      href="{{ $lectureUrl }}"
                      class="block py-3 px-3 transition-all border-l-4
                        {{ $isActiveLesson ? 'bg-orange-50 border-orangeone' : 'hover:bg-gray-50 border-transparent' }}"
                      aria-current="{{ $isActiveLesson ? 'page' : 'false' }}"
                    >
                      <div class="flex items-start gap-2">
                        <div class="w-6 flex justify-center pt-[2px]">
                          <span class="text-[16px] font-black {{ $ico['class'] }}" aria-label="{{ $ico['label'] }}">
                            {{ $ico['icon'] }}
                          </span>
                        </div>

                        <div class="min-w-0 flex-1">
                          <div class="flex items-start justify-between gap-2">
                            <span
                              class="block text-[14px] font-bold leading-snug truncate
                                {{ $isActiveLesson ? 'text-orangeone' : 'text-gray-700' }}"
                              title="{{ $lec->lecture_title }}"
                            >
                              Leç. {{ $lessonNo }} - {{ $lec->lecture_title }}
                            </span>

                            @if($isActiveLesson)
                              <span class="text-[10px] font-black uppercase tracking-tighter text-orangeone/70 whitespace-nowrap">
                                Lecture en cours
                              </span>
                            @endif
                          </div>

                          <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold {{ $q['class'] }}">
                              {{ $q['text'] }}
                            </span>

                            @if(!empty($q['count']))
                              <span class="text-[11px] font-black text-gray-500 tabular-nums" aria-label="Avancement du quiz">
                                {{ $q['count'] }}
                              </span>
                            @endif
                          </div>
                        </div>
                      </div>
                    </a>

                    @if($activityCount > 0)
                      <a
                        href="{{ $lectureUrl }}"
                        class="ml-6 block py-2 px-3 border-l-4 border-transparent hover:bg-gray-50 transition-all"
                      >
                        <div class="flex items-center gap-2">
                          <div class="w-6 flex justify-center">
                            <span class="text-[16px] font-black text-gray-400" aria-hidden="true">✎</span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <span class="block text-[13px] font-bold leading-snug text-gray-600">Activité</span>
                            <span class="block text-[11px] font-semibold text-gray-400">
                              {{ $activityCount }} question{{ $activityCount > 1 ? 's' : '' }}
                            </span>
                          </div>
                        </div>
                      </a>
                    @endif
                  </li>
                @endforeach
              </ul>
            </div>
          </li>
        @endforeach
      </ol>
    </div>
  </div>

  {{-- Pied de page --}}
  <div class="p-4 bg-white border-t border-gray-100 space-y-3">
    @if(($moduleResources ?? $lessonResources ?? collect())->isNotEmpty())
      <div
        x-show="resourcesPanelOpen"
        x-transition.opacity
        x-cloak
        class="rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl"
      >
        <div class="mb-3 flex items-start justify-between gap-3">
          <div class="flex items-start gap-2 min-w-0">
            <h2 class="text-sm font-bold text-bleuone">Ressources de la formation</h2>
            <div x-data="{ open: false }" class="relative">
              <button
                type="button"
                @mouseenter="open = true"
                @mouseleave="open = false"
                @focus="open = true"
                @blur="open = false"
                class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-200 bg-white text-[10px] font-black text-bleuone transition hover:border-bleuone"
                aria-label="Aide sur les ressources de la formation"
              >
                ?
              </button>
              <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute left-0 top-full z-20 mt-2 w-52 rounded-xl border border-slate-200 bg-slate-900 px-3 py-2 text-[11px] leading-relaxed text-white shadow-xl"
                style="display: none;"
              >
                Documents et supports utilises dans la formation.
              </div>
            </div>
          </div>
          <button
            type="button"
            @click="resourcesPanelOpen = false"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:text-bleuone"
            aria-label="Fermer les fichiers ressources"
          >
            <i class="ti ti-x text-base"></i>
          </button>
        </div>

        <div class="max-h-[45vh] space-y-3 overflow-y-auto pr-1">
          @foreach(($moduleResources ?? $lessonResources ?? collect()) as $resource)
            @php
              $resourceUrl = $resource->public_url;
              $resourceExt = strtoupper($resource->extension ?: 'FILE');
              $resourceDate = optional($resource->created_at)->format('d/m/Y');
            @endphp
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
              <div class="flex items-start gap-3">
                @if($resource->is_image)
                  <a href="{{ $resourceUrl }}" target="_blank" class="shrink-0">
                    <img src="{{ $resourceUrl }}" alt="{{ $resource->title }}" class="h-12 w-12 rounded-lg border border-gray-200 object-cover bg-white">
                  </a>
                @else
                  <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-[11px] font-black text-bleuone">
                    {{ $resourceExt }}
                  </div>
                @endif

                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-bold text-gray-800">{{ $resource->title }}</p>
                  <div class="mt-1 flex items-center gap-1 text-[11px] text-gray-500">
                    <span class="truncate max-w-[170px]" title="{{ $resource->original_name }}">{{ $resource->original_name }}</span>
                    @if($resourceDate)
                      <span class="shrink-0">•</span>
                      <span class="shrink-0">{{ $resourceDate }}</span>
                    @endif
                  </div>
                  <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ $resourceUrl }}" target="_blank" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-bold text-blue-700 hover:bg-blue-100">
                      Ouvrir
                    </a>
                    <a href="{{ $resourceUrl }}" download="{{ $resource->original_name }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-bold text-gray-700 hover:bg-gray-100">
                      Télécharger
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <button
        type="button"
        @click="resourcesPanelOpen = !resourcesPanelOpen"
        class="flex w-full items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-left text-gray-600 transition hover:border-gray-300 hover:bg-gray-100"
      >
        <div class="min-w-0 flex items-center gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-bleuone">
            <i class="ti ti-folder text-lg"></i>
          </span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-700">Fichiers ressources</p>
          </div>
        </div>
        <span class="inline-flex shrink-0 items-center rounded-full border border-gray-200 bg-white px-3 py-1 text-[11px] font-bold text-bleuone">
          {{ ($moduleResources ?? $lessonResources ?? collect())->count() }}
        </span>
      </button>
    @endif

    <a
      href="mailto:{{ $formateur->email ?? 'support@oneduc.fr' }}"
      class="flex items-center gap-3 p-4 rounded-xl bg-bleuone text-white hover:opacity-95 transition-opacity group shadow-md"
    >
      <div class="bg-white/10 p-2 rounded-lg group-hover:bg-orangeone transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
      </div>
      <div>
        <p class="text-[11px] font-black uppercase tracking-widest leading-none">Aide</p>
        <p class="text-[10px] text-white/70 mt-1">Contacter le formateur</p>
      </div>
    </a>
  </div>
</aside>
