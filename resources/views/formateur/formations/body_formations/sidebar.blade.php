{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/body_formations/sidebar.blade.php --}}
<aside
  x-cloak
  x-show="sidebarOpen"
  class="w-80 flex-shrink-0 bg-white border-r border-gray-100 flex flex-col fixed md:sticky z-20"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  role="navigation"
  aria-label="Plan de la formation (formateur)"
>
  @php
    // Mode anonyme (lecture seule) : on masque tout suivi
    $anonymous = (bool)($anonymous ?? request()->boolean('anonymous'));

    // Query à propager dans TOUS les liens
    $q = array_merge(($contextQuery ?? []), $anonymous ? ['anonymous' => 1] : []);

    $activeSectionId = null;

    if (isset($selectedLecture) && optional($selectedLecture)->section_id) {
      $activeSectionId = (int) optional($selectedLecture)->section_id;
    } elseif ($routeSection = request()->route('section')) {
      $activeSectionId = is_object($routeSection) ? (int) ($routeSection->id ?? 0) : (int) $routeSection;
    }

    $activeLessonId = isset($selectedLecture) ? (int) $selectedLecture->id : null;

    // Accordéon : chapitre ouvert par défaut = chapitre actif, sinon le premier chapitre
    $initialOpenSectionId = $activeSectionId ?: optional($module->sections->first())->id;

    // Carte d'en-tête : compteurs uniquement, pas de progression (pas de statut formateur)
    $sidebarChapterCount = $module->sections->count();
    $sidebarLessonCount  = $module->sections->sum(fn ($s) => $s->lectures->count());
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
        <p class="mt-1 text-[10px] font-semibold text-slate-500">
          <span class="whitespace-nowrap">
            {{ $sidebarChapterCount }} chapitre{{ $sidebarChapterCount > 1 ? 's' : '' }}
            ·
            {{ $sidebarLessonCount }} leçon{{ $sidebarLessonCount > 1 ? 's' : '' }}
          </span>
        </p>
      </div>

      <ol class="space-y-3">
        @foreach ($module->sections as $sIndex => $section)
          @php
            $chapterNo = $sIndex + 1;
            $isActiveChapter = ($activeSectionId && (int) $activeSectionId === (int) $section->id);

            // Page "chapitre" : section active sans leçon sélectionnée
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));

            // Compteurs : en mode anonyme, on affiche uniquement X leçons
            $totalL = $section->lectures->count();

            $chapterHeaderBgClass = $isSectionPage ? 'bg-orange-50' : ($isActiveChapter ? 'bg-blue-50/40' : 'hover:bg-gray-50');
            $chapterHeaderBorderClass = $isSectionPage ? 'border-orangeone' : ($isActiveChapter ? 'border-bleuone' : 'border-transparent');
            $chapterNumberClass = $isSectionPage ? 'bg-orangeone text-white border-orangeone' : ($isActiveChapter ? 'bg-bleuone text-white border-bleuone' : 'bg-gray-100 text-gray-500 border-gray-200');
          @endphp

          <li class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            {{-- EN-TÊTE CHAPITRE --}}
            <div class="flex items-stretch">
              <a
                href="{{ route('formateur.formations.section', ['module' => $module->id, 'section' => $section->id] + $q) }}"
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
                  <h3
                    class="text-[15px] font-bold leading-tight truncate max-w-[190px]
                    {{ $isSectionPage ? 'text-orangeone' : 'text-bleuone' }}"
                    title="{{ $section->section_title }}"
                  >
                    Ch. {{ $chapterNo }} - {{ $section->section_title }}
                  </h3>

                  {{-- Formateur : pas de progression. On affiche le nombre de leçons. --}}
                  <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
                    {{ $totalL }} leçon{{ $totalL > 1 ? 's' : '' }}
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
                    $isActiveLesson = ($activeLessonId && (int) $activeLessonId === (int) $lec->id);
                    $displayedQuestions = (bool) ($lec->quiz_enabled ?? false)
                      ? (int) ($lec->quiz_questions_per_attempt ?? 0)
                      : (int) ($lec->question_count ?? 0);
                    $lectureUrl = route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id] + $q);
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
                        {{-- Icône neutre (pas de statut) --}}
                        <div class="w-6 flex justify-center pt-[2px]">
                          <span class="text-[16px] font-black text-gray-400" aria-hidden="true">•</span>
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

                          {{-- Ligne info (formateur/anonyme) --}}
                          <div class="mt-1 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold text-gray-400">
                              Mode {{ $anonymous ? 'anonyme' : 'formateur' }}
                            </span>

                            @if($displayedQuestions > 0)
                              <span class="text-[11px] font-black text-gray-500 tabular-nums">
                                {{ $displayedQuestions }} question{{ $displayedQuestions > 1 ? 's' : '' }}
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

  {{-- Pied de page : aide/support --}}
  <div class="p-4 bg-white border-t border-gray-100">
    <a
      href="mailto:{{ $formateur->email ?? 'support@oneduc.fr' }}"
      class="flex items-center gap-3 p-4 rounded-xl bg-bleuone text-white hover:opacity-95 transition-opacity group shadow-md"
    >
      <div class="bg-white/10 p-2 rounded-lg group-hover:bg-orangeone transition-colors" aria-hidden="true">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
      </div>
      <div>
        <p class="text-[11px] font-black uppercase tracking-widest leading-none">Aide</p>
        <p class="text-[10px] text-white/70 mt-1">Contacter le support</p>
      </div>
    </a>
  </div>
</aside>
