{{-- 
    FICHIER : resources/views/stagiaire/formations/body_formations/header.blade.php
    Objectif :
    - Supprimer le fil d’Ariane
    - Garder uniquement la progression du module
    - Affichage plus grand et plus lisible
--}}

@php
    // Compteurs module (progression globale)
    $totalLessonsGlobal = 0;
    $validatedLessonsGlobal = 0;

    foreach($module->sections as $sec) {
        foreach($sec->lectures as $l) {
            $totalLessonsGlobal++;
            $st = $lectureStats[$l->id]['status'] ?? 'not_started';
            if(in_array($st, ['acquired','completed','passed'])) {
                $validatedLessonsGlobal++;
            }
        }
    }

    $globalPercent = $totalLessonsGlobal > 0
        ? (int) round(($validatedLessonsGlobal / $totalLessonsGlobal) * 100)
        : 0;
@endphp

<header class="w-full bg-white shadow-sm border-b border-gray-100 px-4 py-2 sticky top-0 z-20">
  <div class="flex items-center justify-between h-16 gap-4">

    {{-- GAUCHE --}}
    <div class="flex items-center gap-3 shrink-0 w-1/4">
      <button
        type="button"
        @click="sidebarOpen = !sidebarOpen"
        class="inline-flex items-center justify-center p-2 rounded-lg text-bleuone border border-gray-200 hover:bg-orangeone/10 hover:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone transition-all shadow-sm"
        aria-label="Ouvrir ou fermer la navigation"
      >
        <svg x-show="!sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-cloak x-show="sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
      </button>

      <a href="{{ route('index') }}" class="hidden lg:block ml-2">
        <img src="/frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg" alt="Logo Onéduc" class="h-9 w-auto">
      </a>
    </div>

    {{-- CENTRE : PROGRESSION MODULE (plus grand) --}}
    <div class="flex-1 flex flex-col items-center justify-center text-center min-w-0">

      <div class="w-full max-w-[620px]">
        <div class="flex items-baseline justify-center gap-3">
          <span class="text-sm md:text-base font-semibold text-gray-700">
            Progression du module
          </span>
          <span class="text-2xl md:text-3xl font-extrabold text-orangeone">
            {{ $globalPercent }}%
          </span>
          <span class="text-sm md:text-base text-gray-500">
            ({{ $validatedLessonsGlobal }}/{{ $totalLessonsGlobal }})
          </span>
        </div>

        <div class="mt-2">
          <div
            class="w-full bg-gray-200 h-3 rounded-full overflow-hidden"
            role="progressbar"
            aria-label="Progression du module"
            aria-valuenow="{{ $globalPercent }}"
            aria-valuemin="0"
            aria-valuemax="100"
          >
            <div
              class="h-full bg-orangeone rounded-full transition-all duration-300"
              style="width: {{ $globalPercent }}%"
            ></div>
          </div>
        </div>
      </div>

    </div>

    {{-- DROITE --}}
    <div class="shrink-0 w-1/4 flex justify-end">
      <a href="{{ route('stagiaire.dashboard') }}"
         class="flex items-center gap-2 px-3 md:px-4 py-2 rounded-full border border-gray-200 text-gray-700 hover:text-orangeone hover:border-orangeone hover:bg-orangeone/5 transition-colors font-medium text-xs md:text-sm">
        <span class="hidden md:inline">Quitter</span><span class="md:hidden">X</span>
        <svg class="hidden md:block w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </a>
    </div>

  </div>
</header>
