@extends('stagiaire.master')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Wrapper unique pour en-tête + contenu --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Texte (9) --}}
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Tableau de bord stagiaire</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Suivez votre progression et vos modules de formation.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Accédez à vos modules, vos statistiques de progression, votre formateur référent et plus encore.
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
            <li class="text-gray-400">Tableau de bord</li>
          </ol>
        </nav>
      </div>

      {{-- Image (3) --}}
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/TableauDeBordStagiaire.svg') }}"
             alt="Illustration tableau de bord stagiaire"
             class="max-w-[400px] h-auto">
      </div>

    </div>
  </header>

  {{-- 📋 CONTENU PRINCIPAL --}}
  <main class="space-y-8">

    {{-- Formateur référent --}}
    @if ($formateur)
      <section class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Formateur référent</h2>
        <p class="text-gray-800">
          <span class="font-medium">{{ $formateur->name }}</span>
          <span class="text-sm text-gray-600">({{ $formateur->email }})</span>
        </p>
      </section>
    @endif

    {{-- Indicateurs clés --}}
    <section class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Indicateurs clés</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-700">
        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
          <span><strong>{{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}</strong> passées sur la plateforme</span>
        </div>

        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" x2="14" y1="2" y2="2"/><line x1="12" x2="15" y1="14" y2="11"/><circle cx="12" cy="14" r="8"/></svg>
          <span><strong>{{ $commentairesTotal }}</strong> commentaire{{ $commentairesTotal > 1 ? 's' : '' }}</span>
        </div>

        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
          <span><strong>{{ $answeredCount }}</strong> question{{ $answeredCount > 1 ? 's' : '' }} répondues</span>
        </div>

        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
          <span>Taux de bonnes réponses : <strong>{{ $tauxBonnesReponses }}%</strong></span>
        </div>
      </div>
    </section>

    {{-- Détails d'utilisation --}}
    <section class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
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
    </section>

    {{-- Graphiques --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <section class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 col-span-2">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Comparatif des temps</h2>
        <div class="h-[300px]">
          <canvas id="tempsChart"></canvas>
        </div>
      </section>

      <section class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 flex flex-col items-center justify-center">
        <h2 class="text-lg font-semibold text-gray-700 mb-4 text-center">Taux de bonnes réponses</h2>
        <div class="h-[150px] w-[150px]">
          <canvas id="reussiteChart" width="100" height="100"></canvas>
        </div>
        <p class="mt-4 text-sm text-gray-600">{{ $tauxBonnesReponses }}% de bonnes réponses</p>
      </section>
    </div>

    {{-- Modules du stagiaire --}}
    <section>
      @foreach ($modules as $module)
        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 mb-4">
          <h3 class="text-orangeone font-bold text-xl mb-2">{{ $module->module_title }}</h3>
          <p class="text-sm text-gray-600">{{ $module->description }}</p>
        </article>
      @endforeach
    </section>

  </main>
</div>

{{-- Charts --}}
<script>
  // Donut: taux de bonnes réponses
  const tauxReussite = {{ $tauxBonnesReponses }};
  new Chart(document.getElementById('reussiteChart'), {
    type: 'doughnut',
    data: {
      labels: ['Bonnes réponses', 'Erreurs'],
      datasets: [{ data: [tauxReussite, 100 - tauxReussite], borderWidth: 1 }]
    },
    options: {
      cutout: '70%',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } }
    }
  });

  // Barres: comparatif des temps (minutes)
  const totalSiteSeconds = {{ (int)($totalSiteTime ?? 0) }};
  const totalEvalSeconds = {{ (int)($totalEvaluationTime ?? 0) }};
  new Chart(document.getElementById('tempsChart'), {
    type: 'bar',
    data: {
      labels: ['Temps plateforme', 'Temps évaluations'],
      datasets: [{
        data: [Math.round(totalSiteSeconds/60), Math.round(totalEvalSeconds/60)],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, title: { display: true, text: 'Minutes' } } }
    }
  });
</script>
@endsection
