{{-- resources/views/stagiaire/formations/body_formations/sidebar.blade.php --}}

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
    } elseif (request()->route('section')) {
      $activeSectionId = (int) request()->route('section');
    }

    // Une leçon est active uniquement si on est dans une leçon
    $activeLessonId = isset($selectedLecture) ? (int) $selectedLecture->id : null;

    // Largeur colonne gauche : rond + icônes dessous
    $leftCol = '44px';
  @endphp

  <div class="flex-1 overflow-y-auto p-0 custom-scrollbar">
    <div class="p-0">
      <ol class="space-y-2">
        @foreach ($module->sections as $sIndex => $section)
          @php
            $chapterNo = $sIndex + 1;

            // Chapitre actif si on est sur sa page (section) OU si la leçon courante appartient à ce chapitre
            $isActiveChapter = ($activeSectionId && (int)$activeSectionId === (int)$section->id);

            $totalL = $section->lectures->count();
            $doneValidated = 0;

            foreach ($section->lectures as $lecP) {
              $stP = $lectureStats[$lecP->id]['status'] ?? 'not_started';
              if (in_array($stP, ['acquired','completed','passed'])) $doneValidated++;
            }

            $chapterStateLabel = 'À faire';
            if ($doneValidated > 0 && $doneValidated < $totalL) $chapterStateLabel = 'En cours';
            if ($totalL > 0 && $doneValidated === $totalL) $chapterStateLabel = 'Terminé';

            $openDefault = $isActiveChapter ? 'true' : 'false';

            // Surbrillance "section sélectionnée" : seulement quand on est sur la page section (pas dans une leçon)
            $isSectionPage = ($isActiveChapter && !isset($selectedLecture));
          @endphp

          <li x-data="{ open: {{ $openDefault }} }">
            {{-- CHAPITRE : 2 colonnes (gauche = rond, droite = texte)
                 Surbrillance : le rond + le titre doivent être dans le même bloc clickable --}}
            <div
              class="grid transition-colors {{ $isActiveChapter ? 'bg-orangeone/10' : 'bg-white hover:bg-gray-50' }}"
              style="grid-template-columns: {{ $leftCol }} 1fr;"
            >
              {{-- BLOC CLIQUABLE SECTION : regroupe gauche + droite pour la surbrillance --}}
              <a
                href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
                class="col-span-2 grid items-start transition-colors
                       {{ $isSectionPage ? 'bg-orangeone/15' : '' }}
                       hover:bg-orangeone/5"
                style="grid-template-columns: {{ $leftCol }} 1fr;"
                @if($isSectionPage) aria-current="page" @endif
              >
                {{-- Colonne gauche : rond centré --}}
                <div class="pt-4 flex justify-center">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-lg
                           {{ $isActiveChapter ? 'bg-orangeone text-white' : 'bg-gray-100 text-gray-700' }}"
                    aria-hidden="true"
                  >
                    {{ $chapterNo }}
                  </div>
                </div>

                {{-- Colonne droite : titre + ratio + chevron --}}
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

                    {{-- Bouton accordéon (reste dans le même bloc visuel) --}}
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

              {{-- Séparateur chapitre pleine largeur (pas dans la colonne gauche) --}}
              <div class="col-span-2 border-b border-gray-200"></div>

              {{-- LEÇONS : commencent sur 2 colonnes --}}
              <div x-show="open" x-collapse class="col-span-2 pb-0">
                <ul class="space-y-1">
                  @foreach ($section->lectures as $lec)
                    @php
                      $stat = $lectureStats[$lec->id]['status'] ?? 'not_started';

                      // IMPORTANT : en vue section, aucune leçon n'est active
                      $isActiveLesson = (isset($selectedLecture) && (int)$selectedLecture->id === (int)$lec->id);

                      $isValidated    = in_array($stat, ['acquired','completed','passed']);
                      $isNotValidated = in_array($stat, ['failed','not_acquired']);
                      $isInProgress   = in_array($stat, ['incomplete','in_progress']);

                      $label = 'À faire';
                      if ($isInProgress) $label = 'En cours';
                      if ($isValidated) $label = 'Validée';
                      if ($isNotValidated) $label = 'Non validée';
                    @endphp

                    <li>
                      <a
                        href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lec->id]) }}"
                        class="relative grid items-start py-2 transition-colors
       {{ $isActiveLesson ? 'bg-bleuone text-white' : 'text-gray-700 hover:bg-gray-50' }}"

                        style="grid-template-columns: {{ $leftCol }} 1fr;"
                        @if($isActiveLesson) aria-current="page" @endif
                      >
                        {{-- Colonne gauche : icône centrée (sous le chiffre) --}}
                        <span class="flex justify-center pt-2" aria-hidden="true">
                          @if($isValidated)
                            <svg class="w-5 h-5 text-vertone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                          @elseif($isNotValidated)
                            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          @elseif($isInProgress)
                            <svg class="w-5 h-5 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <circle cx="12" cy="12" r="10" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2" />
                            </svg>
                          @else
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                              <circle cx="12" cy="12" r="10" />
                            </svg>
                          @endif
                        </span>

                        {{-- Colonne droite : texte --}}
                        <span class="min-w-0 pr-3">
                          <span class="block text-sm font-semibold leading-snug break-words {{ $isActiveLesson ? 'font-bold' : '' }}">
                            {{ $lec->lecture_title }}
                          </span>
                          <span class="block text-xs text-gray-500 mt-0.5">
                            {{ $label }}
                          </span>
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

  {{-- PIED DE PAGE --}}
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
