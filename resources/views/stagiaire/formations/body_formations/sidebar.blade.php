{{-- 
    FICHIER : resources/views/stagiaire/formations/body_formations/sidebar.blade.php 
--}}
<aside
  x-cloak
  x-show="sidebarOpen"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="-translate-x-full opacity-0"
  x-transition:enter-end="translate-x-0 opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="translate-x-0 opacity-100"
  x-transition:leave-end="-translate-x-full opacity-0"
  class="w-80 flex-shrink-0 bg-white border-r border-gray-200 shadow-lg md:shadow-none flex flex-col h-[calc(100vh-64px)] fixed md:sticky top-16 z-10 overflow-hidden"
  role="navigation" 
  aria-label="Navigation du cours">

  {{-- 1. PROGRESSION GLOBALE --}}
  <div class="p-4 border-b border-gray-100 bg-gray-50/50">
    @php
        $totalL_global = 0; $doneL_global = 0;
        foreach($module->sections as $sec) {
            foreach($sec->lectures as $l) {
                $totalL_global++;
                $st = $lectureStats[$l->id]['status'] ?? null;
                if(in_array($st, ['acquired','completed','passed'])) $doneL_global++;
            }
        }
        $globalPercent = $totalL_global > 0 ? intval(($doneL_global / $totalL_global) * 100) : 0;
    @endphp

    <div class="flex items-center justify-between mb-2">
        <span class="text-base font-bold text-gray-700 uppercase tracking-wide">Progression</span>
        <span class="text-lg font-bold text-orangeone">{{ $globalPercent }}%</span>
    </div>

    <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-orangeone to-yellow-400 transition-all duration-500 rounded-full" 
             style="width: {{ $globalPercent }}%"></div>
    </div>
  </div>

  {{-- 2. LISTE DES SECTIONS --}}
  <div class="flex-1 overflow-y-auto p-0 space-y-0 custom-scrollbar">
    @foreach ($module->sections as $sIndex => $section)
      @php
        // Est-ce que cette section est active (contient la leçon en cours OU est la page affichée)
        $isActiveSection = (isset($selectedLecture) && optional($selectedLecture)->section_id === $section->id) 
                          || (request()->route('section') == $section->id);
        
        $totalL = $section->lectures->count();
        $doneL  = 0;
        foreach ($section->lectures as $lecP) {
          $stP = $lectureStats[$lecP->id]['status'] ?? null;
          if (in_array($stP, ['acquired','completed','passed'])) $doneL++;
        }

        $isSectionCompleted = ($totalL > 0 && $doneL === $totalL);
      @endphp

      {{-- 
          CONTENEUR SECTION
          Modifications ici pour le fond :
          - Si Actif : bg-orangeone/10 (Orange léger, marqué)
          - Sinon : bg-white
          - Survol (Hover) : hover:bg-orangeone/5 (Orange très pâle au passage de souris)
      --}}
      <div x-data="{ open: {{ $isActiveSection ? 'true' : 'false' }} }"
           class="border-b border-gray-100 transition-colors duration-200
                  {{ $isActiveSection 
                     ? 'bg-orangeone/10' 
                     : 'bg-white hover:bg-orangeone/5' }}">

        {{-- LIGNE DE TITRE --}}
        <div class="flex items-start w-full group">
           
           {{-- Chevron (Interaction Accordéon) --}}
           <button @click="open = !open"
                   type="button"
                   class="p-5 focus:outline-none text-gray-400 group-hover:text-orangeone transition-colors"
                   aria-label="Déplier ou replier la section">
             <svg :class="open ? 'rotate-180' : ''"
                  class="w-7 h-7 transition-transform duration-200"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
             </svg>
           </button>

           {{-- Titre (Lien vers la section) --}}
           <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
              class="flex-1 py-5 pr-4 block focus:outline-none">
             
             {{-- Couleur du texte :
                  - Si actif : Bleu One (votre couleur principale)
                  - Si inactif : Gris foncé, mais devient Bleu One au survol du bloc
             --}}
             <h3 class="text-xl font-bold leading-snug transition-colors
                        {{ $isActiveSection ? 'text-bleuone' : 'text-gray-800 group-hover:text-bleuone' }}
                        {{ $isSectionCompleted ? 'line-through decoration-gray-400 text-gray-400 opacity-80' : '' }}">
               {{ $section->section_title }}
             </h3>
             <p class="text-base text-gray-500 font-medium mt-1">
               {{ $doneL }}/{{ $totalL }} terminé(s)
             </p>
           </a>

        </div>

        {{-- CONTENU LEÇONS --}}
        <div x-show="open" x-collapse>
          <ul class="ml-9 border-l-[3px] border-dotted border-gray-300 pb-4">
            @foreach ($section->lectures as $lec)
              @php
                  $stat = $lectureStats[$lec->id]['status'] ?? 'not_started';
                  $isActiveLesson = optional($selectedLecture)->id === $lec->id;
                  
                  $isDone   = in_array($stat, ['acquired','completed','passed']);
                  $isFailed = in_array($stat, ['failed', 'not_acquired']);
                  $isIncomplete = ($stat === 'incomplete');
              @endphp

              <li class="relative">
                <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lec->id]) }}"
                   class="block pl-6 pr-3 py-3 mt-1 mr-2 rounded-r-xl transition-colors
                          {{ $isActiveLesson 
                             ? 'bg-orangeone/20 text-bleuone' 
                             : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                             
                  {{-- Note : J'ai mis bg-orangeone/20 sur la leçon active pour qu'elle ressorte 
                       sur le fond de section qui est déjà en bg-orangeone/10 --}}
                  
                  <div class="flex items-start gap-2">
                    <div class="flex-1">
                        <span class="block text-lg font-medium leading-snug
                            {{ $isActiveLesson ? 'font-bold' : '' }}
                            {{ $isDone ? 'line-through text-gray-400 decoration-gray-400' : '' }}">
                            {{ $lec->lecture_title }}
                        </span>
                    </div>

                    <div class="shrink-0 pt-1">
                        @if($isDone)
                            <svg class="w-6 h-6 text-vertone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @elseif($isFailed)
                            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @elseif($isIncomplete)
                            <svg class="w-5 h-5 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" /><path d="M12 8v4l2 2" />
                            </svg>
                        @endif
                    </div>
                  </div>
                </a>
              </li>
            @endforeach
          </ul>
        </div>

      </div>
    @endforeach
  </div>

  {{-- 3. PIED DE PAGE --}}
  <div class="p-4 border-t border-gray-200 bg-gray-50">
     <a href="mailto:{{ $formateur->email ?? 'support@oneduc.fr' }}" 
        class="flex items-center gap-3 p-3 bg-white border border-gray-200 shadow-sm hover:border-orangeone hover:shadow-md transition-all group rounded-xl">
        <div class="bg-blue-50 text-bleuone p-2 group-hover:bg-orangeone group-hover:text-white transition-colors rounded-lg">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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