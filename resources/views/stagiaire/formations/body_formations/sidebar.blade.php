{{-- resources/views/stagiaire/formations/body_formations/sidebar.blade.php --}}

<aside
  x-cloak
  x-show="sidebarOpen"
  class="w-80 flex-shrink-0 bg-white border-r border-gray-100 flex flex-col h-[calc(100vh-64px)] fixed md:sticky top-16 z-20"
>
  @php
    $activeSectionId = null;
    if (isset($selectedLecture) && optional($selectedLecture)->section_id) {
      $activeSectionId = (int) optional($selectedLecture)->section_id;
    } elseif ($routeSection = request()->route('section')) {
      $activeSectionId = is_object($routeSection) ? (int) ($routeSection->id ?? 0) : (int) $routeSection;
    }
    $activeLessonId = isset($selectedLecture) ? (int) $selectedLecture->id : null;

    $isCompleted = fn($s) => in_array(strtolower((string)$s), ['completed','passed','acquired'], true);
  @endphp

  <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30">
    <div class="py-4 px-3">
      <ol class="space-y-3">
        @foreach ($module->sections as $sIndex => $section)
          @php
            $chapterNo = $sIndex + 1;
            $isActiveChapter = ($activeSectionId && (int)$activeSectionId === (int)$section->id);
            $totalL = $section->lectures->count();
            $doneValidated = 0;
            foreach ($section->lectures as $lecP) {
              if ($isCompleted($lectureStats[$lecP->id]['status'] ?? '')) $doneValidated++;
            }
            $openDefault = $isActiveChapter ? 'true' : 'false';
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));
          @endphp

          <li x-data="{ open: {{ $openDefault }} }" class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            {{-- EN-TETE CHAPITRE (Même couleur que les leçons si actif) --}}
            <div class="grid" style="grid-template-columns: 48px 1fr;">
              
              <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
                 class="col-span-2 grid items-center transition-all py-4 {{ $isSectionPage ? 'bg-orange-50 border-l-4 border-[#E94D2A]' : 'hover:bg-gray-50 border-l-4 border-transparent' }}"
                 style="grid-template-columns: 44px 1fr;">
                
                <div class="flex justify-center">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border {{ $isSectionPage ? 'bg-[#E94D2A] text-white border-[#E94D2A]' : ($isActiveChapter ? 'bg-[#004461] text-white border-[#004461]' : 'bg-gray-100 text-gray-500 border-gray-200') }}">
                    {{ $chapterNo }}
                  </div>
                </div>

                <div class="min-w-0 pr-10 relative">
                  {{-- Titre : Police agrandie et texte tronqué (max-w) --}}
                  <h3 class="text-[15px] font-bold leading-tight truncate max-w-[190px] {{ $isSectionPage ? 'text-[#E94D2A]' : 'text-[#004461]' }}" title="{{ $section->section_title }}">
                    {{ $section->section_title }}
                  </h3>
                  <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
                      {{ $doneValidated }}/{{ $totalL }} leçons terminées
                  </span>

                  {{-- Bouton Accordéon --}}
                  <button type="button" 
                          @click.prevent="open = !open"
                          class="absolute right-1 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-[#004461]">
                    <svg class="w-5 h-5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>
                </div>
              </a>

              {{-- LISTE DES LEÇONS --}}
              <div x-show="open" x-collapse class="col-span-2 border-t border-gray-50 bg-white">
                <ul class="py-1">
                  @foreach ($section->lectures as $lec)
                    @php
                      $stat = $lectureStats[$lec->id]['status'] ?? 'not_started';
                      $isActiveLesson = (isset($selectedLecture) && (int)$selectedLecture->id === (int)$lec->id);
                      $valid = $isCompleted($stat);
                    @endphp

                    <li>
                      <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id]) }}"
                         class="grid items-center py-4 transition-all {{ $isActiveLesson ? 'bg-orange-50 border-l-4 border-[#E94D2A]' : 'hover:bg-gray-50 border-l-4 border-transparent' }}"
                         style="grid-template-columns: 44px 1fr;">
                        
                        <div class="flex justify-center">
                          @if($valid)
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                          @else
                            <div class="w-3 h-3 rounded-full border-2 {{ $isActiveLesson ? 'border-[#E94D2A]' : 'border-gray-200' }}"></div>
                          @endif
                        </div>

                        <div class="pr-4">
                          {{-- Titre Leçon : Police agrandie et texte tronqué --}}
                          <span class="block text-[14px] font-bold leading-snug truncate max-w-[200px] {{ $isActiveLesson ? 'text-[#E94D2A]' : 'text-gray-700' }}" title="{{ $lec->lecture_title }}">
                            {{ $lec->lecture_title }}
                          </span>
                          @if($isActiveLesson)
                            <span class="text-[10px] font-black uppercase tracking-tighter text-[#E94D2A]/70">Lecture en cours</span>
                          @endif
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

  {{-- Pied de page --}}
  <div class="p-4 bg-white border-t border-gray-100">
    <a href="mailto:{{ $formateur->email ?? 'support@oneduc.fr' }}"
       class="flex items-center gap-3 p-4 rounded-xl bg-[#004461] text-white hover:opacity-95 transition-opacity group shadow-md">
      <div class="bg-white/10 p-2 rounded-lg group-hover:bg-[#E94D2A] transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
      </div>
      <div>
        <p class="text-[11px] font-black uppercase tracking-widest leading-none">Aide</p>
        <p class="text-[10px] text-white/70 mt-1">Contacter le formateur</p>
      </div>
    </a>
  </div>
</aside>