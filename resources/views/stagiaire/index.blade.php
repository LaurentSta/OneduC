@extends('stagiaire.master')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
  $currentStagiaire = auth()->user();
  $stagiaireDisplayName = trim((string) (($currentStagiaire->prenom ?? '') . ' ' . ($currentStagiaire->name ?? '')));
  if ($stagiaireDisplayName === '') {
      $stagiaireDisplayName = 'Stagiaire';
  }
@endphp

{{-- Wrapper global --}}
<div class="max-w-[1285px] mx-auto px-8 py-8">

  {{-- 1. EN-TÊTE --}}
  <header class="rounded-[20px] border border-gray-100 bg-white shadow-md mb-8">
    <div class="grid gap-6 px-6 py-6 md:px-8 md:py-7 lg:grid-cols-12 lg:items-center">
      <div class="lg:col-span-8">
        <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Tableau de bord']]" />

        <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
          Tableau de bord stagiaire
        </h1>
        <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
          Suivez votre progression et vos formations.
        </p>
        <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
          Accédez à vos formations, vos statistiques de progression, votre formateur référent et plus encore.
        </p>

        <div class="mt-4 flex flex-wrap gap-2 text-xs font-varela">
          <span class="inline-flex items-center gap-1.5 rounded-full border border-bleuone/15 bg-bleuone/5 px-3 py-1 text-bleuone">
            Espace personnel de {{ $stagiaireDisplayName }}
          </span>
        </div>
      </div>

      <div class="lg:col-span-4 flex flex-col items-center lg:items-end">
        <img src="{{ asset('images/svg/TableauDeBordStagiaire.svg') }}"
             alt="Illustration tableau de bord stagiaire"
             class="max-w-[220px] h-auto">
      </div>
    </div>
  </header>

  {{-- CONTENU PRINCIPAL --}}
  <main class="grid grid-cols-1 gap-8">

    {{-- 2. KPIs (Design Original + Nouvelles Données) --}}
    <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

      {{-- KPI 1 : Formateur --}}
      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <img src="{{ asset('images/svg/Formateur.svg') }}" alt="Formateur" class="w-20 h-20 shrink-0">
        @if ($formateur)
          <div class="leading-tight">
            <p class="text-base font-semibold text-orangeone">Formateur</p>
            <p class="text-[17px] font-medium text-bleuone truncate max-w-[150px]" title="{{ $formateur->name }}">
                {{ $formateur->name }}
            </p>
            <a href="mailto:{{ $formateur->email }}" class="text-xs text-gray-500 hover:text-orangeone transition-colors">Contacter</a>
          </div>
        @else
          <div class="leading-tight">
            <p class="text-base font-semibold text-orangeone">Formateur</p>
            <p class="text-gray-500 text-sm">Non assigné</p>
          </div>
        @endif
      </div>

      {{-- KPI 2 : Temps d'apprentissage --}}
      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <img src="{{ asset('images/svg/Horloge.svg') }}" alt="Temps" class="w-20 h-20 shrink-0">
        <div class="leading-tight">
          <p class="text-base font-semibold text-orangeone">Temps Apprentissage</p>
          <p class="text-[17px] font-medium text-bleuone">{{ gmdate("H\h i", $engagementTotal) }}</p>
        </div>
      </div>

      {{-- KPI 3 : Questions Répondues --}}
      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <img src="{{ asset('images/svg/Questions.svg') }}" alt="Questions" class="w-20 h-20 shrink-0">
        <div class="leading-tight">
          <p class="text-base font-semibold text-orangeone">Questions Traitées</p>
          <p class="text-[17px] font-medium text-bleuone">{{ $answeredCount ?? 0 }}</p>
        </div>
      </div>

      {{-- KPI 4 : Taux de Réussite --}}
      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <img src="{{ asset('images/svg/TauxReussite.svg') }}" alt="Réussite" class="w-20 h-20 shrink-0">
        <div class="leading-tight">
          <p class="text-base font-semibold text-orangeone">Taux de Réussite</p>
          <p class="text-[17px] font-medium {{ $tauxBonnesReponses >= 50 ? 'text-vertone' : 'text-red-500' }}">
              {{ number_format($tauxBonnesReponses ?? 0, 0) }}%
          </p>
        </div>
      </div>

    </section>

    {{-- 3. GRAPHIQUE & CARROUSEL --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- A. Carrousel "Formations en cours" (2/3 largeur) --}}
        <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-bleuone">Formations en cours</h2>

            {{-- BOUTONS DE NAVIGATION RESTAURÉS --}}
            <div class="flex gap-2">
              <button type="button" 
                      id="carouselPrev" 
                      class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orangeone transition-colors"
                      aria-label="Précédent">
                ‹
              </button>
              <button type="button" 
                      id="carouselNext" 
                      class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orangeone transition-colors"
                      aria-label="Suivant">
                ›
              </button>
            </div>
          </div>

          {{-- Conteneur Scroll --}}
          <div class="relative">
            <div id="modulesCarousel" class="flex items-center gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 hide-scrollbar">
              @forelse ($modules as $index => $module)
                @if ($module->status)
                  {{-- CARD MODULE CARRÉE --}}
                  <article class="snap-start shrink-0 w-[240px] h-[240px] bg-white rounded-[20px] shadow-sm border border-gray-100 relative group overflow-hidden">
                    
                    {{-- Badge Numéro --}}
                    <div class="absolute top-2 right-2 z-20 bg-bleuone text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm border border-white/20">
                      #{{ $index + 1 }}
                    </div>

                    <a href="{{ url('/stagiaire/modules/'.$module->id) }}" class="flex flex-col h-full focus:outline-none">
                      
                      {{-- Image (Haut - 50%) --}}
                      <div class="h-1/2 w-full overflow-hidden bg-gray-50 relative">
                        @php
                          $imgUrl = $module->module_image ? asset('storage/'.$module->module_image) : null;
                          $progress = isset($module->progress) ? (int)$module->progress : 0;
                        @endphp
                        
                        @if($imgUrl)
                          <img src="{{ $imgUrl }}" alt="" class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                          <div class="w-full h-full flex items-center justify-center text-gray-300">
                             <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                          </div>
                        @endif

                        {{-- Barre de progression overlay --}}
                        <div class="absolute bottom-0 left-0 w-full h-1 bg-gray-200/50">
                          <div class="h-full bg-orangeone transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                      </div>

                      {{-- Contenu (Bas - 50%) --}}
                      <div class="p-3 flex flex-col justify-between flex-1">
                        <div>
                          <h3 class="text-sm font-semibold text-bleuone line-clamp-2 italic leading-tight mb-1 group-hover:text-orangeone transition-colors">
                            {{ $module->module_title }}
                          </h3>
                          <p class="text-[11px] text-gray-500 line-clamp-2 font-lisible leading-snug">
                            {{ $module->description }}
                          </p>
                        </div>

                        <div class="flex items-center justify-between mt-auto pt-2">
                          <span class="text-[10px] font-bold {{ $progress == 100 ? 'text-vertone' : 'text-orangeone' }}">
                              {{ $progress }}% terminé
                          </span>
                          <div class="p-1 bg-orangeone/10 rounded-full group-hover:bg-orangeone group-hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                            </svg>
                          </div>
                        </div>
                      </div>
                    </a>
                  </article>

                  {{-- Flèche de liaison --}}
                  @if (!$loop->last)
                    <div class="shrink-0 flex items-center justify-center px-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orangeone/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 5l7 7-7 7" />
                      </svg>
                    </div>
                  @endif

                @endif
              @empty
                <p class="text-sm text-gray-600 font-lisible italic p-4">Aucune formation en cours.</p>
              @endforelse
            </div>
          </div>
        </div>

        {{-- B. Analyse (Donut Chart) - 1/3 largeur --}}
        <div class="lg:col-span-1 bg-white rounded-[20px] shadow-md p-6 flex flex-col items-center justify-center border border-gray-100">
             <h2 class="text-lg font-semibold text-gray-700 w-full mb-4 text-left">Analyse réussite</h2>
             <div class="relative w-48 h-48">
                 <canvas id="reussiteChart"></canvas>
                 <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                     <span class="text-3xl font-bold text-bleuone">{{ $tauxBonnesReponses }}%</span>
                     <span class="text-[10px] uppercase text-gray-400 font-bold">Correct</span>
                 </div>
             </div>
             
             {{-- Info Latence --}}
             <div class="mt-6 w-full pt-4 border-t border-gray-100">
                 <div class="flex justify-between items-center">
                     <span class="text-xs text-gray-500 font-bold uppercase">Temps de réflexion</span>
                     <span class="text-sm font-bold text-purple-600">{{ $averageLatencyTime }} sec <span class="text-[10px] font-normal text-gray-400">/ quest.</span></span>
                 </div>
             </div>
        </div>

    </section>

  </main>
</div>

{{-- Scripts Chart.js & Carousel --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {
      // Donut Chart
      const ctx = document.getElementById('reussiteChart');
      if (ctx) {
          const taux = {{ $tauxBonnesReponses }};
          new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Bonnes réponses', 'Erreurs'],
              datasets: [{
                data: [taux, 100 - taux],
                backgroundColor: ['#22c55e', '#f87171'],
                borderWidth: 0
              }]
            },
            options: {
              cutout: '75%',
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } }
            }
          });
      }

      // Carousel Logic
      const track = document.getElementById('modulesCarousel');
      const prev = document.getElementById('carouselPrev');
      const next = document.getElementById('carouselNext');

      if(track && prev && next) {
        // Calcul du pas de défilement (largeur card + gap)
        const getStep = () => {
          const card = track.querySelector('article');
          // 240px (card) + 16px (gap)
          return card ? card.getBoundingClientRect().width + 16 : 256;
        };

        prev.addEventListener('click', () => track.scrollBy({ left: -getStep(), behavior: 'smooth' }));
        next.addEventListener('click', () => track.scrollBy({ left: getStep(), behavior: 'smooth' }));
      }
  });
</script>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

@endsection
