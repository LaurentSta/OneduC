{{-- 
    FICHIER : resources/views/stagiaire/formations/body_formations/header.blade.php 
--}}
<header class="w-full bg-white shadow-sm border-b border-gray-100 px-4 py-2 sticky top-0 z-20 h-16">
  <div class="flex items-center justify-between h-full gap-4">
    
    {{-- GAUCHE --}}
    <div class="flex items-center gap-3 shrink-0 w-1/4">
      <button type="button" @click="sidebarOpen = !sidebarOpen" class="inline-flex items-center justify-center p-2 rounded-lg text-bleuone border border-gray-200 hover:bg-orangeone/10 hover:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone transition-all shadow-sm">
        <svg x-show="!sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
        <svg x-cloak x-show="sidebarOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" /></svg>
      </button>
      <a href="{{ route('index') }}" class="hidden lg:block ml-2">
        <img src="/frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg" alt="Logo Onéduc" class="h-9 w-auto">
      </a>
    </div>

    {{-- CENTRE : FIL D'ARIANE --}}
    <div class="flex-1 flex flex-col items-center justify-center text-center min-w-0">
        
        {{-- MODULE (Racine) --}}
        <div class="text-xs md:text-sm font-bold text-gray-500 uppercase tracking-widest truncate max-w-full leading-tight">
            <span class="truncate">{{ $module->module_title }}</span>
        </div>

        {{-- CHEMIN ACTIF --}}
        <div class="flex items-center justify-center gap-2 text-sm md:text-base mt-0.5 max-w-full truncate">
            
            {{-- CAS 1 : Dans une LEÇON --}}
            @if(isset($selectedLecture))
                
                {{-- Lien vers Section --}}
                @if($selectedLecture->section)
                    <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $selectedLecture->section->id]) }}"
                       class="font-medium text-gray-500 truncate hidden sm:inline-block max-w-[150px] md:max-w-xs hover:text-orangeone hover:underline transition-colors">
                        {{ $selectedLecture->section->section_title }}
                    </a>
                    <svg class="w-4 h-4 text-gray-300 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                @endif

                {{-- Leçon actuelle --}}
                <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $selectedLecture->section->id, 'lesson' => $selectedLecture->id]) }}"
                   class="flex items-center gap-1.5 font-bold text-bleuone truncate hover:text-orangeone hover:underline transition-colors">
                    <svg class="w-4 h-4 text-orangeone shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    <span class="truncate">{{ $selectedLecture->lecture_title }}</span>
                </a>

            {{-- CAS 2 : Dans une SECTION (sans leçon active) --}}
            @elseif(isset($section))
                
                <div class="flex items-center gap-1.5 font-bold text-bleuone truncate">
                    {{-- Icône Dossier Ouvert --}}
                    <svg class="w-4 h-4 text-orangeone shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                    </svg>
                    <span class="truncate">{{ $section->section_title }}</span>
                </div>

            {{-- CAS 3 : Racine --}}
            @else
                <span class="font-bold text-gray-600">Vue d'ensemble</span>
            @endif

        </div>
    </div>

    {{-- DROITE --}}
    <div class="shrink-0 w-1/4 flex justify-end">
        <a href="{{ route('stagiaire.dashboard') }}" class="flex items-center gap-2 px-3 md:px-4 py-2 rounded-full border border-gray-200 text-gray-700 hover:text-orangeone hover:border-orangeone hover:bg-orangeone/5 transition-colors font-medium text-xs md:text-sm">
            <span class="hidden md:inline">Quitter</span><span class="md:hidden">X</span>
            <svg class="hidden md:block w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
        </a>
    </div>
  </div>
</header>