@extends('stagiaire.master')

@section('content')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Mes formations --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Texte (9) --}}
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Mes formations</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Accédez à vos contenus et suivez votre progression.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Chaque module regroupe plusieurs sections. Vous pouvez reprendre une leçon à tout moment.
        </p>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Mes modules</li>
          </ol>
        </nav>
      </div>

      {{-- Image (3) --}}
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/FormationStagiaire.svg') }}"
             alt="Illustration des modules de formation"
             class="max-w-[400px] h-auto">
      </div>

    </div>
  </header>
  {{-- 📋 CONTENU PRINCIPAL --}}
  <main class="space-y-8">

    <section aria-labelledby="liste-modules" class="relative pb-12">
  <h2 id="liste-modules" class="sr-only">Votre parcours de formation</h2>

  <div class="flex flex-col items-center space-y-0"> {{-- On gère l'espace via les flèches --}}
    @forelse($modules as $index => $module)
      
      {{-- Card du Module --}}
      <article class="w-full max-w-2xl bg-white shadow-lg rounded-[24px] overflow-hidden border-2 {{ $module->progression_status === 'completed' ? 'border-vertone' : 'border-transparent' }} transition-all hover:scale-[1.01]">
        <div class="flex flex-col md:flex-row">
          
          {{-- Image miniature --}}
          <div class="md:w-48 h-32 md:h-auto shrink-0 relative">
            @if($module->module_image)
              <img src="{{ asset('storage/' . $module->module_image) }}" 
                   class="w-full h-full object-cover" 
                   alt="">
            @endif
            {{-- Badge de statut sur l'image --}}
            <div class="absolute top-2 left-2">
                @php
                    $status = $module->progression_status ?? 'not_started';
                    $badgeColor = [
                        'completed'   => 'bg-vertone',
                        'in_progress' => 'bg-orangeone',
                        'not_started' => 'bg-gray-400',
                    ][$status] ?? 'bg-gray-400';
                @endphp
                <span class="flex h-3 w-3 rounded-full {{ $badgeColor }} ring-2 ring-white"></span>
            </div>
          </div>

          <div class="p-6 flex-1">
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-xl font-varela text-bleuone">{{ $module->module_title }}</h3>
              <span class="text-sm font-bold text-bleuone bg-gray-100 px-2 py-1 rounded">Étape {{ $index + 1 }}</span>
            </div>
            
            <p class="text-sm text-gray-600 font-lisible mb-4 line-clamp-2">
              {{ $module->description }}
            </p>

            {{-- Barre de progression --}}
            <div class="flex items-center gap-4">
              <div class="flex-1 bg-gray-200 h-2 rounded-full overflow-hidden">
                <div class="h-full bg-vertone transition-all duration-500" style="width: {{ $module->progression_percent ?? 0 }}%"></div>
              </div>
              <span class="text-xs font-bold text-vertone">{{ (int)$module->progression_percent ?? 0 }}%</span>
            </div>

            <div class="mt-4 flex justify-end">
                <a href="{{ route('stagiaire.module.detail', $module->id) }}" class="text-orangeone font-varela font-bold flex items-center hover:translate-x-1 transition-transform">
                    Continuer le module
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
          </div>
        </div>
      </article>

      {{-- Flèche de liaison (affichée sauf après le dernier module) --}}
      @if (!$loop->last)
        <div class="py-4 flex flex-col items-center">
          {{-- Ligne verticale en pointillés --}}
          <div class="w-1 h-8 border-l-4 border-dotted {{ $module->progression_status === 'completed' ? 'border-vertone' : 'border-gray-300' }}"></div>
          {{-- Icône de flèche --}}
          <svg xmlns="http://www.w3.org/2000/svg" 
               class="h-8 w-8 {{ $module->progression_status === 'completed' ? 'text-vertone' : 'text-gray-300' }}" 
               fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
          <div class="w-1 h-8 border-l-4 border-dotted {{ ($modules[$index+1]->progression_status ?? '') === 'in_progress' ? 'border-orangeone' : 'border-gray-300' }}"></div>
        </div>
      @endif

    @empty
      <div class="text-center py-20 bg-white rounded-[20px] w-full shadow-inner">
        <p class="text-gray-500 font-lisible">Aucun module ne vous a encore été attribué.</p>
      </div>
    @endforelse
  </div>
</section>

  </main>
</div>
@endsection
