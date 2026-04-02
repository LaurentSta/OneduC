@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
          Suivi par module
        </p>

        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Analyse des modules utilisés dans vos groupes
        </p>

        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Cette vue présente uniquement les modules réellement associés à vos groupes.
          Vous pouvez identifier les modules les plus sollicités, ceux à renforcer
          et repérer d’éventuelles difficultés pédagogiques.
        </p>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-3" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}"
                 class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Suivi par module</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}"
             alt="Illustration suivi par module"
             class="max-w-[240px] h-auto">
      </div>

    </div>
  </header>

  {{-- ACTIONS --}}
  <div class="flex flex-wrap justify-end gap-3 mb-6">
    <a href="{{ route('formateur.progressions.groupes') }}"
       class="btn-oneduc h-10 !text-sm">
      <x-icons.eye-iconify class="h-4 w-4" />
      Suivi par groupe
    </a>

    <a href="{{ route('formateur.progressions.stagiaires') }}"
       class="btn-oneduc h-10 !text-sm">
      <x-icons.eye-iconify class="h-4 w-4" />
      Suivi par stagiaire
    </a>
  </div>


  {{-- LISTE DES MODULES (CARTES) --}}
  <main class="space-y-6">
    
    @forelse($modules as $m)
        <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                    
                    {{-- 1. Info Module --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2 py-1 bg-blue-50 text-bleuone text-xs font-bold rounded uppercase tracking-wider">Module</span>
                            <h3 class="text-xl font-bold text-gray-900">{{ $m->module_title }}</h3>
                        </div>
                        <div class="flex items-center gap-6 text-sm text-gray-500 mt-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <span><strong>{{ $m->stagiaires_count }}</strong> stagiaires inscrits</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span><strong>{{ $m->start_rate }}%</strong> ont démarré</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Score Moyen --}}
                    <div class="text-center px-6 border-l border-gray-100">
                        <span class="block text-sm text-gray-400 font-varela mb-1">Score Moyen</span>
                        <span class="text-3xl font-black {{ $m->avg_score < 50 ? 'text-red-500' : 'text-green-500' }}">
                            {{ $m->avg_score }}%
                        </span>
                    </div>

                    {{-- 3. Action --}}
                    <div>
                        <a href="{{ route('formateur.formations.detail', $m->id) }}" class="btn-oneduc">
                            <x-icons.eye-iconify class="h-4 w-4" />
                            Voir le détail
                        </a>
                    </div>
                </div>

                {{-- 4. ZONE D'ALERTE : TOP 3 ERREURS --}}
                @if(count($m->top_failed) > 0)
                    <div class="mt-6 pt-6 border-t border-dashed border-gray-200">
                        <h4 class="text-sm font-bold text-red-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Difficultés rencontrées (Top 3 erreurs)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($m->top_failed as $q)
                                <div class="bg-red-50 rounded-lg p-3 border border-red-100">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-red-800 bg-red-200 px-2 py-0.5 rounded">
                                            {{ $q->fail_rate }}% d'échec
                                        </span>
                                        <span class="text-[10px] text-red-400 font-bold uppercase">{{ $q->failures }} erreurs</span>
                                    </div>
                                    <p class="text-sm text-gray-800 font-medium line-clamp-2" title="{{ $q->question_text }}">
                                        {{ $q->question_text }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-6 pt-6 border-t border-dashed border-gray-200">
                        <p class="text-sm text-gray-400 italic flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Aucune difficulté majeure détectée sur les quiz de ce module.
                        </p>
                    </div>
                @endif

            </div>
        </div>
    @empty
        <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-gray-300">
            <p class="text-gray-500">Aucun module associé à vos groupes pour le moment.</p>
        </div>
    @endforelse

    <div class="flex flex-wrap items-center gap-2">
        <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
            <span>Nombre total de modules :</span>
            <span class="font-bold text-bleuone">{{ $modules->count() }}</span>
        </div>
    </div>

  </main>
</div>
@endsection
