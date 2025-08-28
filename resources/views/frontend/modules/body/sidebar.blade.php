{{-- SIDEBAR --}}
<aside
  id="module-sidebar"
  x-show="sidebarOpen"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="-translate-x-full opacity-0"
  x-transition:enter-end="translate-x-0 opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="translate-x-0 opacity-100"
  x-transition:leave-end="-translate-x-full opacity-0"
- class="w-full md:w-72 xl:w-80 bg-white rounded-[20px] shadow-md p-5 md:sticky md:top-4 h-max transform"
+ class="w-full bg-white rounded-[20px] shadow-none p-5 md:sticky md:top-4 h-max transform"
  role="navigation" aria-label="Plan du module">

  {{-- Titre module --}}
  <h2 class="text-xl font-raleway font-bold text-bleuone mb-4">
    {{ $module->module_title }}
  </h2>
  


  <ul class="space-y-4">
    @foreach ($module->sections as $sIndex => $section)
      @php
      $isActiveSection = optional($selectedLecture)->section_id === $section->id
                        || (request()->route('section') == $section->id);

      // progression section
      $totalL = $section->lectures->count();
      $doneL  = 0;
      foreach ($section->lectures as $lecP) {
        $stP = $lectureStats[$lecP->id]['status'] ?? null;
        if (in_array($stP, ['acquired','completed'])) $doneL++;
      }
      $percent = $totalL > 0 ? intval(($doneL / $totalL) * 100) : 0;

      // ✅ section terminée si toutes les leçons sont acquises/complétées
      $sectionCompleted = ($totalL > 0 && $doneL === $totalL);
    @endphp


<li x-data="{ open: {{ $isActiveSection ? 'true' : 'false' }} }"
    class="rounded-[14px] border transition shadow-sm
           {{ $isActiveSection
              ? 'border-orangeone ring-2 ring-orangeone/30 bg-orangeone/5'
              : 'border-gray-200 hover:border-gray-300' }}">

  {{-- En-tête section cliquable --}}
  <button @click="open = !open"
          class="w-full px-4 py-3 flex items-center justify-between text-left rounded-[14px]">
    <div class="min-w-0">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full
          {{ $isActiveSection ? 'bg-orangeone' : 'bg-gray-300' }}"></span>

        <div class="relative inline-block group">
          <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
            aria-describedby="tt-section-{{ $section->id }}"
            class="truncate font-semibold {{ $isActiveSection ? 'text-bleuone' : 'text-gray-800 hover:text-orangeone' }}">
            {{ $section->section_title }}
          </a>

          <span id="tt-section-{{ $section->id }}" role="tooltip"
              class="tooltip-oneduc tooltip-right
                    group-hover:visible group-hover:opacity-100
                    group-focus-within:visible group-focus-within:opacity-100">
          Chapitre
        </span>

        </div>


      </div>

      {{-- Barre de progression --}}
      <div class="mt-2 w-full bg-gray-100 h-1.5 rounded"
           role="progressbar" aria-valuemin="0" aria-valuemax="100"
           aria-valuenow="{{ $percent }}"
           aria-label="Progression {{ $section->section_title }}">
        <div class="h-1.5 rounded {{ $isActiveSection ? 'bg-orangeone' : 'bg-vertone' }}"
             style="width: {{ $percent }}%"></div>
      </div>
    </div>

    <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-gray-500 transition-transform ml-3 shrink-0"
         fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
  </button>

  {{-- Leçons --}}
  <div x-show="open" x-collapse class="px-3 pb-3">
    <ul class="mt-2 space-y-1">
      @foreach ($section->lectures as $lec)
        @php
          $stat = $lectureStats[$lec->id]['status'] ?? 'not_started';
          $isActiveLesson = optional($selectedLecture)->id === $lec->id;
          $lessonDone = in_array($stat, ['acquired','completed']);
        @endphp
        <li>
  <div class="relative group">
    <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lec->id]) }}"
       aria-describedby="tt-lesson-{{ $lec->id }}"
       class="block px-3 py-2 rounded-lg text-sm font-varela transition
              {{ $isActiveLesson ? 'bg-orangeone/10 border border-orangeone text-bleuone'
                                 : 'hover:bg-gray-100 text-gray-800 border border-transparent' }}"
       @if($isActiveLesson) aria-current="page" @endif>
      <div class="flex items-center justify-between gap-3">
        <span class="truncate">{{ $lec->lecture_title }}</span>
        {{-- icône inchangée --}}
        @if($stat === 'acquired' || $stat === 'completed')
          <svg class="w-4 h-4 text-vertone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        @elseif($stat === 'incomplete')
          <svg class="w-4 h-4 text-orangeone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        @elseif($stat === 'not_acquired')
          <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        @else
          <span class="w-2 h-2 rounded-full bg-gray-300 inline-block" aria-hidden="true"></span>
        @endif
      </div>
    </a>

    <span id="tt-lesson-{{ $lec->id }}" role="tooltip"
      class="tooltip-oneduc tooltip-right
             group-hover:visible group-hover:opacity-100
             group-focus-within:visible group-focus-within:opacity-100">
  Leçon
</span>

  </div>
</li>

      @endforeach
    </ul>
  </div>
</li>

    @endforeach
  </ul>
</aside>
