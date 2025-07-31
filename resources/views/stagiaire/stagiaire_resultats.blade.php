@extends('stagiaire.master')

@section('content')

{{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Résultats et progression --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">
        <div class="col-span-12">
            <x-typography variant="titre">Mes résultats</x-typography>
            <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                Suivi de ma progression et de mes scores
            </x-typography>
            <x-typography>
                Consultez ici vos scores, le temps passé, vos bonnes réponses et vos résultats aux évaluations. Toutes vos statistiques sont réunies au même endroit.
            </x-typography>

            <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
                <ol class="list-none p-0 inline-flex items-center space-x-1">
                    <li class="flex items-center">
                        <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                            </svg>
                        </a>
                        <span class="mx-2 text-gray-400">/</span>
                    </li>
                    <li class="text-gray-400">Mes résultats</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

{{-- 📊 CONTENU PRINCIPAL DES RÉSULTATS --}}


    {{-- 3 graphiques en haut --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Graphique des questions --}}
        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Graphique des questions</h2>
            <canvas id="globalChart"></canvas>
        </div>

        {{-- Graphique des slides --}}
        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Graphique des slides</h2>
            <canvas id="slideChart"></canvas>
        </div>

        {{-- Donut réponses --}}
        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6 relative">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Répartition des réponses</h2>
            <canvas id="donutChart"></canvas>
            <div id="donutCenterText" class="absolute inset-0 flex items-center justify-center text-xl font-bold text-gray-800 pointer-events-none"></div>
        </div>
    </div>

    {{-- Bloc : Temps et comportement --}}
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Temps et comportement</h2>

    {{-- Temps total --}}
    <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6 mb-6 text-center">
        <p class="text-md text-gray-700 font-semibold">Temps total passé sur la plateforme :</p>
        <p class="text-2xl text-bleuone font-bold">
            {{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}
        </p>
    </div>

    {{-- Temps moyen + vidéo --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Activité SCORM --}}
        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Temps moyen par activité</h2>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>Temps total passé dans les leçons : <strong>{{ gmdate('H\h i\m s\s', $totalScormTime ?? 0) }}</strong></li>
                <li>Temps de réponse aux questions : <strong>{{ gmdate('H\h i\m s\s', $totalLatencyTime ?? 0) }}</strong></li>
                <li>Temps total d’engagement SCORM : <strong>{{ gmdate('H\h i\m s\s', $engagementTotal ?? 0) }}</strong></li>
                <li>Temps moyen par question : <strong>{{ gmdate('i\m s\s', $averageLatencyTime ?? 0) }}</strong></li>
            </ul>
        </div>

        {{-- Vidéo --}}
        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Statistiques vidéo</h2>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>Temps total vidéos : <strong>{{ gmdate('H\h i\m s\s', $videoStats['totalVideoWatchTime'] ?? 0) }}</strong></li>
                <li>Segments visionnés : <strong>{{ $videoStats['totalVideoSegments'] ?? 0 }}</strong></li>
                <li>Nombre de relectures : <strong>{{ $videoStats['totalVideoReplays'] ?? 0 }}</strong></li>
            </ul>
        </div>
    </div>

    {{-- Bloc : Évaluations --}}
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Résultats des évaluations</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Scores des évaluations</h2>
            <canvas id="evaluationChart"></canvas>
        </div>

        <div class="bg-white rounded-[20px] shadow-md px-8 pt-8 pb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Répartition des réponses</h2>
            <canvas id="evaluationDonut"></canvas>
        </div>
    </div>



<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('globalChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        indexAxis: 'y',
        data: {
            labels: ['Questions /n Formation', 'Réponses enregistrées', 'Questions réessayées'],
            datasets: [{
                label: 'Total',
                data: [
                    {{ $resultats->map(fn($r) => $r->lecture->module)->unique('id')->sum(fn($module) => $module->sections->flatMap->lectures->sum('question_count')) }},
                    
                    {{ $resultats->sum('answered_questions') }},
                    {{ $reessayeCount }}
                ],
                backgroundColor: ['#3b82f6', '#22c55e', '#ef4444'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, } }
        }
    });

    const slideCtx = document.getElementById('slideChart').getContext('2d');
    new Chart(slideCtx, {
        type: 'bar',
        data: {
            labels: ['Slides à visionner', 'Slides visionnées'],
            datasets: [{
                label: 'Slides',
                data: [
                    {{ $resultats->sum(fn($r) => $r->lecture->slide_count ?? 0) }},
                    {{ $resultats->filter(fn($r) => $r->lesson_status === 'completed')->sum(fn($r) => $r->lecture->slide_count ?? 0) }}
                ],
                backgroundColor: ['#6366f1', '#94a3b8'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true} }
        }
    });

    const donutCtx = document.getElementById('donutChart').getContext('2d');
    const correct = {{ $resultats->sum('correct_score') / 10 }};
    const total = {{ $resultats->sum('answered_questions') }};
    const incorrect = total - correct;
    const taux = total > 0 ? Math.round((correct / total) * 100) : 0;
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Bonnes réponses', 'Mauvaises réponses'],
            datasets: [{
                data: [correct, incorrect],
                backgroundColor: ['#22c55e', '#ef4444'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.label}: ${ctx.raw} réponses`
                    }
                }
            }
        }
    });
    document.getElementById('donutCenterText').innerText = `${taux}%`;

    

    const evalCtx = document.getElementById('evaluationChart').getContext('2d');
    new Chart(evalCtx, {
        type: 'bar',
        data: {
            labels: ['Score moyen', 'Score max', 'Taux de réussite'],
            datasets: [{
                label: 'Évaluations',
                data: [
                    {{ round($averageEvaluationScore ?? 0, 1) }},
                    {{ round($bestEvaluationScore ?? 0, 1) }},
                    {{ round($tauxReussiteEvaluation ?? 0, 1) }}
                ],
                backgroundColor: ['#3b82f6', '#10b981', '#facc15'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 10 } } }
        }
    });

    const evalDonutCtx = document.getElementById('evaluationDonut').getContext('2d');
    new Chart(evalDonutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Évaluations réussies', 'Échecs ou incomplètes'],
            datasets: [{
                data: [
                    {{ $totalSuccessEvaluations ?? 0 }},
                    {{ ($totalEvaluationsDone ?? 0) - ($totalSuccessEvaluations ?? 0) }}
                ],
                backgroundColor: ['#10b981', '#f87171'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

@endsection
