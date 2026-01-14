@extends('stagiaire.master')

@section('content')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Résultats et progression --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Texte (9) --}}
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Mes résultats</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Suivi de ma progression et de mes scores
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Consultez vos scores, le temps passé, vos bonnes réponses et vos résultats aux évaluations.
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
            <li class="text-gray-400">Mes résultats</li>
          </ol>
        </nav>
      </div>

      {{-- Image (3) --}}
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Resultats.svg') }}"
             alt="Illustration des résultats et de la progression"
             class="max-w-[400px] h-auto">
      </div>

    </div>
  </header>

  {{-- 📊 CONTENU PRINCIPAL --}}
  <main class="space-y-8">

    {{-- 3 graphiques en haut --}}
    <section aria-labelledby="charts-top">
      <h2 id="charts-top" class="sr-only">Tableaux de bord</h2>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Questions</h3>
          <div class="h-[260px]"><canvas id="globalChart"></canvas></div>
        </article>

        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Slides</h3>
          <div class="h-[260px]"><canvas id="slideChart"></canvas></div>
        </article>

        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 relative">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Répartition des réponses</h3>
          <div class="h-[260px]"><canvas id="donutChart"></canvas></div>
          <div id="donutCenterText" class="absolute inset-0 flex items-center justify-center text-xl font-bold text-gray-800 pointer-events-none"></div>
        </article>
      </div>
    </section>

    {{-- Temps et comportement --}}
    <section aria-labelledby="temps-comportement">
      <h2 id="temps-comportement" class="text-xl font-semibold text-gray-800 mb-4">Temps et comportement</h2>

      <div class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 mb-6 text-center">
        <p class="text-md text-gray-700 font-semibold">Temps total passé sur la plateforme</p>
        <p class="text-2xl text-bleuone font-bold">
          {{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Temps moyen par activité</h3>
          <ul class="space-y-2 text-sm text-gray-700">
            <li>Temps total leçons : <strong>{{ gmdate('H\h i\m s\s', $totalScormTime ?? 0) }}</strong></li>
            <li>Temps de réponse questions : <strong>{{ gmdate('H\h i\m s\s', $totalLatencyTime ?? 0) }}</strong></li>
            <li>Engagement SCORM total : <strong>{{ gmdate('H\h i\m s\s', $engagementTotal ?? 0) }}</strong></li>
            <li>Temps moyen par question : <strong>{{ gmdate('i\m s\s', $averageLatencyTime ?? 0) }}</strong></li>
          </ul>
        </article>

        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques vidéo</h3>
          <ul class="space-y-2 text-sm text-gray-700">
            <li>Temps total vidéos : <strong>{{ gmdate('H\h i\m s\s', $videoStats['totalVideoWatchTime'] ?? 0) }}</strong></li>
            <li>Segments visionnés : <strong>{{ $videoStats['totalVideoSegments'] ?? 0 }}</strong></li>
            <li>Relectures : <strong>{{ $videoStats['totalVideoReplays'] ?? 0 }}</strong></li>
          </ul>
        </article>
      </div>
    </section>

    {{-- Évaluations --}}
    <section aria-labelledby="evaluations">
      <h2 id="evaluations" class="text-xl font-semibold text-gray-800 mb-4">Résultats des évaluations</h2>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Scores des évaluations</h3>
          <div class="h-[260px]"><canvas id="evaluationChart"></canvas></div>
        </article>

        <article class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Répartition des réponses</h3>
          <div class="h-[260px]"><canvas id="evaluationDonut"></canvas></div>
        </article>
      </div>
    </section>

  </main>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  // Graphique questions
  new Chart(document.getElementById('globalChart'), {
    type: 'bar',
    data: {
      labels: ['Questions par formation', 'Réponses enregistrées', 'Questions réessayées'],
      datasets: [{
        data: [
          {{ $resultats->map(fn($r) => $r->lecture->module)->unique('id')->sum(fn($m) => $m->sections->flatMap->lectures->sum('quiz_questions_per_attempt')) }},
          {{ $resultats->sum('answered_questions') }},
          {{ $reessayeCount }}
        ],
        borderWidth: 1,
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Graphique slides
  new Chart(document.getElementById('slideChart'), {
    type: 'bar',
    data: {
      labels: ['Slides à visionner', 'Slides visionnées'],
      datasets: [{
        data: [
          {{ $resultats->sum(fn($r) => $r->lecture->slide_count ?? 0) }},
          {{ $resultats->filter(fn($r) => $r->lesson_status === 'completed')->sum(fn($r) => $r->lecture->slide_count ?? 0) }}
        ],
        borderWidth: 1,
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Donut réponses globales
  const correct = {{ (int) ($resultats->sum('correct_score') / 10) }};
  const total = {{ (int) $resultats->sum('answered_questions') }};
  const incorrect = Math.max(total - correct, 0);
  const taux = total > 0 ? Math.round((correct / total) * 100) : 0;

  new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
      labels: ['Bonnes réponses', 'Mauvaises réponses'],
      datasets: [{ data: [correct, incorrect], borderWidth: 1 }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: { position: 'bottom' },
        tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw} réponses` } }
      }
    }
  });
  document.getElementById('donutCenterText').textContent = `${taux}%`;

  // Scores évaluations
  new Chart(document.getElementById('evaluationChart'), {
    type: 'bar',
    data: {
      labels: ['Score moyen', 'Score max', 'Taux de réussite'],
      datasets: [{
        data: [
          {{ round($averageEvaluationScore ?? 0, 1) }},
          {{ round($bestEvaluationScore ?? 0, 1) }},
          {{ round($tauxReussiteEvaluation ?? 0, 1) }}
        ],
        borderWidth: 1,
        borderRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { stepSize: 10 } } }
    }
  });

  // Donut évaluations
  new Chart(document.getElementById('evaluationDonut'), {
    type: 'doughnut',
    data: {
      labels: ['Évaluations réussies', 'Échecs ou incomplètes'],
      datasets: [{
        data: [
          {{ (int)($totalSuccessEvaluations ?? 0) }},
          {{ (int)(($totalEvaluationsDone ?? 0) - ($totalSuccessEvaluations ?? 0)) }}
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>
@endsection
