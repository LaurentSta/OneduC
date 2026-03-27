{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/body_formations/sidebar.blade.php --}}
<aside
  x-cloak
  x-show="sidebarOpen"
  class="w-80 flex-shrink-0 bg-white border-r border-gray-100 flex flex-col fixed md:sticky z-20"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  role="navigation"
  aria-label="Plan du module (formateur)"
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
  @endphp

  <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30">
    <div class="py-4 px-3">
      <ol class="space-y-3">
        @foreach ($module->sections as $sIndex => $section)
          @php
            $chapterNo = $sIndex + 1;
            $isActiveChapter = ($activeSectionId && (int) $activeSectionId === (int) $section->id);

            // Page "chapitre" : section active sans leçon sélectionnée
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));

            // Compteurs : en mode anonyme, on affiche uniquement X leçons
            $totalL = $section->lectures->count();

          @endphp

          <li class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            {{-- EN-TÊTE CHAPITRE --}}
            <div class="grid" style="grid-template-columns: 48px 1fr;">
              <a
                href="{{ route('formateur.formations.section', ['module' => $module->id, 'section' => $section->id] + $q) }}"
                class="col-span-2 grid items-center transition-all py-4 border-l-4
                  {{ $isSectionPage ? 'bg-orange-50 border-orangeone' : ($isActiveChapter ? 'bg-blue-50/40 border-bleuone' : 'hover:bg-gray-50 border-transparent') }}"
                style="grid-template-columns: 44px 1fr;"
                aria-current="{{ $isSectionPage ? 'page' : 'false' }}"
              >
                <div class="flex justify-center">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border
                    {{ $isSectionPage ? 'bg-orangeone text-white border-orangeone' : ($isActiveChapter ? 'bg-bleuone text-white border-bleuone' : 'bg-gray-100 text-gray-500 border-gray-200') }}">
                    {{ $chapterNo }}
                  </div>
                </div>

                <div class="min-w-0 pr-10 relative">
                  <h3
                    class="text-[15px] font-bold leading-tight truncate max-w-[190px]
                    {{ $isSectionPage ? 'text-orangeone' : 'text-bleuone' }}"
                    title="{{ $section->section_title }}"
                  >
                    Chapitre - {{ $section->section_title }}
                  </h3>

                  {{-- Formateur : pas de progression. On affiche le nombre de leçons. --}}
                  <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
                    {{ $totalL }} leçon{{ $totalL > 1 ? 's' : '' }}
                  </span>

                </div>
              </a>

              {{-- LISTE DES LEÇONS --}}
              <div class="col-span-2 border-t border-gray-50 bg-white">
                <ul class="py-1">
                  @foreach ($section->lectures as $lec)
                    @php
                      $isActiveLesson = ($activeLessonId && (int) $activeLessonId === (int) $lec->id);
                      $displayedQuestions = (bool) ($lec->quiz_enabled ?? false)
                        ? (int) ($lec->quiz_questions_per_attempt ?? 0)
                        : (int) ($lec->question_count ?? 0);
                    @endphp

                    <li>
                      <a
                        href="{{ route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id] + $q) }}"
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
                                {{ $lec->lecture_title }}
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
