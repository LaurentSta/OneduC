@extends('stagiaire.master')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Wrapper global --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Tableau de bord stagiaire</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Suivez votre progression et vos modules de formation.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose">
          Accédez à vos modules, vos statistiques de progression, votre formateur référent et plus encore.
        </p>

        <nav class="text-sm font-varela text-gray-600 mt-4" aria-label="Fil d'Ariane">
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
            <li class="text-gray-400">Tableau de bord</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/TableauDeBordStagiaire.svg') }}"
             alt="Illustration tableau de bord stagiaire"
             class="max-w-[400px] h-auto">
      </div>
    </div>
  </header>

  {{-- CONTENU PRINCIPAL : espacement vertical géré par gap --}}
  <main class="grid grid-cols-1 gap-6">

    
{{-- ==== KPIs en 4 colonnes ==== --}}
<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

  {{-- 1) Formateur référent --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
    <img src="{{ asset('images/svg/Formateur.svg') }}" alt="Formateur référent" class="w-20 h-20 shrink-0">
    @if ($formateur)
      <div class="leading-tight">
        <p class="text-base font-semibold text-orangeone">Formateur</p>
        <p class="text-[17px] font-medium text-bleuone">{{ $formateur->name }}</p>
        <p class="text-sm text-gray-500">({{ $formateur->email }})</p>
      </div>
    @else
      <div class="leading-tight">
        <p class="text-base font-semibold text-orangeone">Formateur</p>
        <p class="text-gray-500">Aucun formateur défini</p>
      </div>
    @endif
  </div>

  {{-- 2) Temps sur la plateforme --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
    <img src="{{ asset('images/svg/Horloge.svg') }}" alt="Temps sur la plateforme" class="w-20 h-20 shrink-0">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Temps de connexion</p>
      <p class="text-[17px] font-medium text-bleuone">{{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}</p>
    </div>
  </div>

  {{-- 3) Questions répondues --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
    <img src="{{ asset('images/svg/Questions.svg') }}" alt="Questions répondues" class="w-20 h-20 shrink-0">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Questions répondues</p>
      <p class="text-[17px] font-medium text-bleuone">{{ $answeredCount ?? 0 }}</p>
    </div>
  </div>

  {{-- 4) Taux de bonnes réponses --}}
  <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-2">
    <img src="{{ asset('images/svg/TauxReussite.svg') }}" alt="Taux de bonnes réponses" class="w-20 h-20 shrink-0">
    <div class="leading-tight">
      <p class="text-base font-semibold text-orangeone">Taux de bonnes réponses</p>
      <p class="text-[17px] font-medium text-vertone">{{ number_format($tauxBonnesReponses ?? 0, 0) }}%</p>
    </div>
  </div>

</section>

{{-- ==== Carrousel "Formations en cours" ==== --}}
<section class="bg-white rounded-[20px] shadow-md p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold text-bleuone">Formations en cours</h2>

    <div class="flex gap-2">
      <button type="button"
              id="carouselPrev"
              class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orangeone"
              aria-label="Faire défiler vers la gauche">
        ‹
      </button>
      <button type="button"
              id="carouselNext"
              class="px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orangeone"
              aria-label="Faire défiler vers la droite">
        ›
      </button>
    </div>
  </div>
  <div class="relative">
    <div id="modulesCarousel"
         class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2"
         role="region"
         aria-label="Carrousel des modules en cours"
         tabindex="0">
      @forelse ($modules as $module)
        <article class="snap-start shrink-0 w-[300px] md:w-[360px] bg-white rounded-[16px] shadow-md border border-gray-100">
          <a href="{{ url('/stagiaire/modules/'.$module->id) }}" class="block focus:outline-none focus:ring-2 focus:ring-orangeone rounded-[16px]">
            <div class="h-[140px] w-full overflow-hidden rounded-t-[16px] bg-gray-100">
              @php
                $img = $module->module_image ?? null;
              @endphp
              @if($img)
                <img src="{{ asset($img) }}" alt="Image du module {{ $module->module_title }}"
                     class="h-full w-full object-cover">
              @else
                <div class="h-full w-full grid place-items-center text-gray-400 text-sm">Aucune image</div>
              @endif
            </div>
            <div class="p-4">
              <h3 class="text-[17px] font-semibold text-bleuone line-clamp-2">{{ $module->module_title }}</h3>
              <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ $module->description }}</p>

              @php
                // Si disponible côté contrôleur : $module->progress (0..100)
                $progress = isset($module->progress) ? (int)$module->progress : 0;
              @endphp
              <div class="mt-3">
                <div class="flex items-center justify-between text-xs text-gray-600">
                  <span>Progression</span>
                  <span>{{ $progress }}%</span>
                </div>
                <div class="mt-1 h-2 w-full bg-gray-200 rounded-full" aria-hidden="true">
                  <div class="h-2 bg-orangeone rounded-full" style="width: {{ $progress }}%"></div>
                </div>
              </div>
            </div>
          </a>
        </article>
      @empty
        <p class="text-sm text-gray-600">Aucune formation en cours.</p>
      @endforelse
    </div>
  </div>
</section>
{{-- ==== /Carrousel ==== --}}


    <!-- {{-- Détails d'utilisation --}}
    <section class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-lg font-bold text-gray-700 mb-2">Temps et évaluations</h2>
      <ul class="space-y-1 text-sm text-gray-800">
        <li>Questions répondues : <strong>{{ $answeredCount }}</strong></li>
        <li>Taux de bonnes réponses : <strong>{{ $tauxBonnesReponses }}%</strong></li>
        <li>Évaluations finalisées : <strong>{{ $totalEvaluationsDone }}</strong></li>
        <li>Meilleur score évaluation : <strong>{{ $bestEvaluationScore ?? 0 }}/100</strong></li>
        <li>Score moyen évaluation : <strong>{{ number_format($averageEvaluationScore, 1) ?? 0 }}/100</strong></li>
        <li>Taux de réussite aux évaluations : <strong>{{ $tauxReussiteEvaluation }}%</strong></li>
        <li>Temps total évaluations : <strong>{{ gmdate('H\h i\m s\s', $totalEvaluationTime ?? 0) }}</strong></li>
        <li>Questions répondues en évaluations : <strong>{{ $totalEvaluationQuestions ?? 0 }}</strong></li>
      </ul>
    </section> -->

    

    

  </main>
</div>

{{-- Charts --}}
<script>
  const tauxReussite = {{ $tauxBonnesReponses }};
  new Chart(document.getElementById('reussiteChart'), {
    type: 'doughnut',
    data: { labels: ['Bonnes réponses', 'Erreurs'], datasets: [{ data: [tauxReussite, 100 - tauxReussite], borderWidth: 1 }] },
    options: { cutout: '70%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });

  const totalSiteSeconds = {{ (int)($totalSiteTime ?? 0) }};
  const totalEvalSeconds = {{ (int)($totalEvaluationTime ?? 0) }};
  new Chart(document.getElementById('tempsChart'), {
    type: 'bar',
    data: { labels: ['Temps plateforme', 'Temps évaluations'], datasets: [{ data: [Math.round(totalSiteSeconds/60), Math.round(totalEvalSeconds/60)], borderWidth: 1 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'Minutes' } } } }
  });
</script>
<script>
  (function () {
    const track = document.getElementById('modulesCarousel');
    const prev = document.getElementById('carouselPrev');
    const next = document.getElementById('carouselNext');
    if (!track || !prev || !next) return;

    // largeur d’un “card” + gap
    const getStep = () => {
      const card = track.querySelector('article');
      if (!card) return 320;
      const styles = window.getComputedStyle(track);
      const gap = parseInt(styles.columnGap || styles.gap || 16, 10);
      return card.getBoundingClientRect().width + gap;
    };

    const scrollByStep = (dir = 1) => track.scrollBy({ left: dir * getStep(), behavior: 'smooth' });

    prev.addEventListener('click', () => scrollByStep(-1));
    next.addEventListener('click', () => scrollByStep(1));

    // Accessibilité clavier
    track.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') { e.preventDefault(); scrollByStep(1); }
      if (e.key === 'ArrowLeft')  { e.preventDefault(); scrollByStep(-1); }
    });

    // Défilement à la molette horizontale
    track.addEventListener('wheel', (e) => {
      if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) return;
      e.preventDefault();
      track.scrollLeft += e.deltaX;
    }, { passive: false });
  })();
</script>

@endsection
