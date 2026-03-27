{{-- resources/views/stagiaire/formations/body_formations/header.blade.php --}}

@php
    // Calcul de la progression
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

<header id="app-header" class="w-full bg-white/80 backdrop-blur-md sticky top-0 z-30 border-b border-gray-100 px-6 py-3">
  <div class="max-w-screen-2xl mx-auto flex items-center justify-between gap-8">

    {{-- GAUCHE : Logo & Menu --}}
    <div class="flex items-center gap-4 shrink-0 lg:w-1/4">
      <button
        type="button"
        @click="sidebarOpen = !sidebarOpen"
        class="p-2.5 rounded-xl text-bleuone bg-gray-50 hover:bg-orangeone/10 hover:text-orangeone transition-all border border-transparent hover:border-orangeone/20"
        aria-label="Toggle Navigation"
      >
        <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-cloak x-show="sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
      </button>

      <a href="{{ route('index') }}" class="hidden sm:block">
        <img src="/frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg" alt="Onéduc" class="h-7 w-auto opacity-90 hover:opacity-100 transition-opacity">
      </a>
    </div>

    {{-- CENTRE : PROGRESSION ÉPURÉE --}}
    <div class="flex-1 max-w-md hidden md:block">
      <div class="flex items-center justify-between mb-1 px-1">
        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Progression de la formation</span>
        <span class="text-sm font-black text-bleuone">{{ $globalPercent }}%</span>
      </div>
      
      <div class="relative h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
        <div
          class="absolute top-0 left-0 h-full rounded-full bg-gradient-to-r from-orangeone to-[#ff7a59] transition-all duration-700 ease-out"
          style="width: {{ $globalPercent }}%"
        >
            <div
              class="absolute inset-0 opacity-30"
              style="background-image: repeating-linear-gradient(135deg, rgba(255,255,255,0.55) 0, rgba(255,255,255,0.55) 6px, transparent 6px, transparent 12px);"
            ></div>
            <div class="absolute inset-y-0 left-0 h-full w-full bg-white/10"></div>
        </div>
      </div>
      
      <div class="mt-1 text-center">
         <span class="text-[10px] font-semibold text-bleuone">
            {{ $validatedLessonsGlobal }} leçons validées sur {{ $totalLessonsGlobal }}
         </span>
      </div>
    </div>

    {{-- DROITE : Actions --}}
    <div class="shrink-0 lg:w-1/4 flex justify-end items-center gap-3">
      @include('components.user-notification-bell')
      <a href="{{ route('stagiaire.dashboard') }}"
         class="group flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-50 text-gray-600 hover:bg-red-50 hover:text-red-600 transition-all border border-gray-100 hover:border-red-100 font-bold text-sm">
        <span>Quitter</span>
        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
        </svg>
      </a>
    </div>

  </div>
</header>
