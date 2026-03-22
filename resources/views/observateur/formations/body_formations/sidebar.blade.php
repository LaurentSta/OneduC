<aside
  x-cloak
  x-show="sidebarOpen"
  class="w-80 flex-shrink-0 bg-white border-r border-gray-100 flex flex-col fixed md:sticky z-20"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  role="navigation"
  aria-label="Plan du module observateur"
>
  @php
    $q = array_merge(($contextQuery ?? []), ['anonymous' => 1]);
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
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));
            $totalL = $section->lectures->count();
            $openDefault = $isActiveChapter ? 'true' : 'false';
          @endphp

          <li x-data="{ open: {{ $openDefault }} }" class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <div class="grid" style="grid-template-columns: 48px 1fr;">
              <a
                href="{{ route('observateur.formations.section', ['module' => $module->id, 'section' => $section->id] + $q) }}"
                class="col-span-2 grid items-center transition-all py-4 border-l-4 {{ $isSectionPage ? 'bg-orange-50 border-orangeone' : ($isActiveChapter ? 'bg-blue-50/40 border-bleuone' : 'hover:bg-gray-50 border-transparent') }}"
                style="grid-template-columns: 44px 1fr;"
              >
                <div class="flex justify-center">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border {{ $isSectionPage ? 'bg-orangeone text-white border-orangeone' : ($isActiveChapter ? 'bg-bleuone text-white border-bleuone' : 'bg-gray-100 text-gray-500 border-gray-200') }}">
                    {{ $chapterNo }}
                  </div>
                </div>

                <div class="min-w-0 pr-10 relative">
                  <h3 class="text-[15px] font-bold leading-tight truncate max-w-[190px] {{ $isSectionPage ? 'text-orangeone' : 'text-bleuone' }}" title="{{ $section->section_title }}">
                    Chapitre - {{ $section->section_title }}
                  </h3>
                  <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">{{ $totalL }} leçon{{ $totalL > 1 ? 's' : '' }}</span>
                  <button type="button" @click.prevent="open = !open" class="absolute right-1 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-bleuone" aria-label="Afficher ou masquer les leçons du chapitre">
                    <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                      <path d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                </div>
              </a>

              <div x-show="open" x-collapse class="col-span-2 border-t border-gray-50 bg-white">
                <ul class="py-1">
                  @foreach ($section->lectures as $lec)
                    @php
                      $isActiveLesson = ($activeLessonId && (int) $activeLessonId === (int) $lec->id);
                    @endphp
                    <li>
                      <a
                        href="{{ route('observateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id] + $q) }}"
                        class="block py-3 px-3 transition-all border-l-4 {{ $isActiveLesson ? 'bg-orange-50 border-orangeone' : 'hover:bg-gray-50 border-transparent' }}"
                      >
                        <div class="flex items-start gap-2">
                          <div class="w-6 flex justify-center pt-[2px]">
                            <span class="text-[16px] font-black text-gray-400" aria-hidden="true">•</span>
                          </div>
                          <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                              <span class="block text-[14px] font-bold leading-snug truncate {{ $isActiveLesson ? 'text-orangeone' : 'text-gray-700' }}" title="{{ $lec->lecture_title }}">
                                {{ $lec->lecture_title }}
                              </span>
                            </div>
                            <div class="mt-1 flex items-center justify-between gap-2">
                              <span class="text-[11px] font-bold text-gray-400">Mode observateur</span>
                              @if(!empty($lec->question_count))
                                <span class="text-[11px] font-black text-gray-500 tabular-nums">{{ (int)$lec->question_count }} question{{ (int)$lec->question_count > 1 ? 's' : '' }}</span>
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
</aside>
