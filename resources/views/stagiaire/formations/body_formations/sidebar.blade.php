{{-- resources/views/stagiaire/formations/body_formations/sidebar.blade.php --}}

<aside
  x-cloak
  x-show="sidebarOpen"
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

  <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30">
    <div class="sticky top-0 z-10 border-b border-gray-100 bg-white/95 px-4 py-4 backdrop-blur">
      <a href="{{ route('stagiaire.modules') }}" class="block rounded-2xl border border-orange-100 bg-orange-50 px-4 py-4 transition hover:border-orange-200 hover:bg-orange-100/70">
        <span class="block text-[11px] font-bold uppercase tracking-[0.28em] text-orangeone/70">Stagiaire</span>
        <span class="mt-1 block text-lg font-bold text-bleuone">Formation</span>
        <span class="mt-2 block text-xs text-gray-500">Vous êtes dans l'espace formation.</span>
      </a>
    </div>

    <div class="py-4 px-3">
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

            $openDefault = $isActiveChapter ? 'true' : 'false';

            // Page "chapitre" : on est sur la section sans leçon sélectionnée
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));
          @endphp

          <li x-data="{ open: {{ $openDefault }} }" class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            {{-- EN-TÊTE CHAPITRE --}}
            <div class="grid" style="grid-template-columns: 48px 1fr;">
              <a
                href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
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
                  <h3 class="text-[15px] font-bold leading-tight truncate max-w-[190px]
                    {{ $isSectionPage ? 'text-orangeone' : 'text-bleuone' }}"
                    title="{{ $section->section_title }}"
                  >
                    Chapitre - {{ $section->section_title }}
                  </h3>

                  <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
                    {{ $doneValidated }}/{{ $totalL }} leçons terminées
                  </span>

                  <button
                    type="button"
                    @click.prevent="open = !open"
                    class="absolute right-1 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-bleuone"
                    aria-label="Afficher ou masquer les leçons du chapitre"
                  >
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
                      $st = $lectureStats[$lec->id] ?? [];
                      $status = $st['status'] ?? 'not_started';

                      $isActiveLesson = ($activeLessonId && (int) $activeLessonId === (int) $lec->id);

                      $ico = $statusIcon($status);
                      $q   = $quizLabel($st);
                    @endphp

                    <li>
                      <a
                        href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lec->id]) }}"
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
                                {{ $lec->lecture_title }}
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
  <div class="p-4 bg-white border-t border-gray-100 space-y-3">
    @if(isset($selectedLecture) && ($lessonResources ?? collect())->isNotEmpty())
      <button
        type="button"
        @click="resourcesPanelOpen = !resourcesPanelOpen"
        class="flex w-full items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-left text-gray-600 transition hover:border-gray-300 hover:bg-gray-100"
      >
        <div class="min-w-0">
          <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Ressources</p>
          <p class="mt-1 text-sm font-semibold text-gray-700">Documents ({{ $lessonResources->count() }})</p>
        </div>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500">
          <i class="ti" :class="resourcesPanelOpen ? 'ti-x' : 'ti-paperclip'"></i>
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
