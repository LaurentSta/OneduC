@extends('stagiaire.master')

@section('content')

<h1 class="text-titre font-raleway text-bleuone mb-6">📊 Progression globale</h1>
<h2 class="text-xl font-semibold text-gray-800 mb-6">📌 Résultats et performance</h2>

<div class="p-6">
    <!-- 📊 Graphiques Chart.js côte à côte -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Graphique des questions</h2>
            <canvas id="globalChart"></canvas>
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Graphique des slides</h2>
            <canvas id="slideChart"></canvas>
        </div>

        <div class="bg-white shadow rounded-xl p-6 relative">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Répartition des réponses</h2>
            <canvas id="donutChart"></canvas>
            <div id="donutCenterText" class="absolute inset-0 flex items-center justify-center text-xl font-bold text-gray-800 pointer-events-none"></div>
        </div>
    </div>

    <h2 class="text-xl font-semibold text-gray-800 mb-6">⏱️ Temps et comportement</h2>

    <!-- 🕒 Temps total plateforme -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-xl p-4 text-center">
            <p class="text-md text-gray-700 font-semibold">Temps total passé sur la plateforme :</p>
            <p class="text-2xl text-bleuone font-bold">
                {{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}
            </p>
        </div>
    </div>

    <!-- 📊 Temps moyen + vidéo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Temps moyen par activité -->
        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Temps moyen par activité</h2>
            <canvas id="timeChart"></canvas>
        </div>

        <!-- Statistiques vidéo -->
        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">🎥 Statistiques vidéo (fictives)</h2>
            <ul class="text-gray-700 space-y-2">
                <li><strong>Temps total vidéos :</strong> à venir</li>
                <li><strong>Nombre de segments visionnés :</strong> à venir</li>
                <li><strong>Nombre de relectures :</strong> à venir</li>
            </ul>
        </div>
    </div>

    <h2 class="text-xl font-semibold text-gray-800 mb-6">📋 Résultats des évaluations</h2>

    <!-- Graphiques évaluations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Barres scores -->
        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Scores des évaluations</h2>
            <canvas id="evaluationChart"></canvas>
        </div>

        <!-- Donut des réponses -->
        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Répartition réponses évaluation</h2>
            <canvas id="evaluationDonut"></canvas>
        </div>
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

    const timeChart = new Chart(document.getElementById('timeChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Temps leçons (SCORM)', 'Temps de réponse aux questions', 'Temps moyen par question'],
            datasets: [{
                label: 'Durée (en minutes)',
                data: [
                    {{ round($totalScormTime ?? 0 / 60, 1) }},
                    {{ round($totalLatencyTime ?? 0 / 60, 1) }},
                    {{ round($averageLatencyTime ?? 0 / 60, 2) }}
                ],
                backgroundColor: ['#60a5fa', '#34d399', '#fbbf24'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

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
