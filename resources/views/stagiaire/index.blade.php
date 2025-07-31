
@extends('stagiaire.master')

@section('content')
<div class="min-h-screen flex justify-center pt-[72px]">
    {{-- Main Content --}}
    <div class="flex-1 p-6">
        <h1 class="text-titre font-raleway text-bleuone mb-4">Tableau de bord Stagiaire</h1>
        <!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Formateur référent -->
@if ($formateur)
<div class="bg-white rounded-xl shadow p-5 mt-6">
    <p class="text-lg font-semibold text-gray-800">
        Formateur référent : {{ $formateur->name }} <span class="text-sm text-gray-600">({{ $formateur->email }})</span>
    </p>
</div>

<!-- Bloc d'utilisation stylisé -->
<div class="bg-white rounded-2xl shadow-md p-6 mt-6 space-y-4">
    <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
        En chiffre
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor-icon lucide-monitor"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
            <span><strong>{{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}</strong> sur la plateforme</span>
        </div>
        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-timer-icon lucide-timer"><line x1="10" x2="14" y1="2" y2="2"/><line x1="12" x2="15" y1="14" y2="11"/><circle cx="12" cy="14" r="8"/></svg>
            <span><strong>{{ $commentairesTotal }}</strong> commentaire{{ $commentairesTotal > 1 ? 's' : '' }}</span>
        </div>
        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-check-icon lucide-square-check"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
            <span><strong>{{ $answeredCount }}</strong> question{{ $answeredCount > 1 ? 's' : '' }} répondues</span>
        </div>
        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-percent-icon lucide-percent"><line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
            <span>Taux de bonnes réponses : <strong>{{ $tauxBonnesReponses }}%</strong></span>
        </div>
    </div>
</div>
<!-- Bloc d'utilisation -->
<div class="bg-white rounded-xl shadow p-5 mt-4">
    <h2 class="text-lg font-bold text-gray-700 mb-2">⏱️ Temps d'utilisation</h2>
    <ul class="space-y-1 text-sm text-gray-800">
         <li>🧠 Tu as déjà répondu à <strong>{{ $answeredCount }}</strong> question{{ $answeredCount > 1 ? 's' : '' }}.</li>
        <li>✅ Taux de bonnes réponses : <strong>{{ $tauxBonnesReponses }}%</strong></li>

        <!-- Bloc statistiques évaluations SCORM -->
        <li>📑 Nombre d'évaluations finalisées : <strong>{{ $totalEvaluationsDone }}</strong></li>
        <li>🥇 Meilleur score (évaluation) : <strong>{{ $bestEvaluationScore ?? 0 }}/100</strong></li>
        <li>📈 Score moyen sur les évaluations : <strong>{{ number_format($averageEvaluationScore, 1) ?? 0 }}/100</strong></li>
        <li>🎯 Taux de réussite aux évaluations : <strong>{{ $tauxReussiteEvaluation }}%</strong></li>
        <li>🕒 Temps total passé sur les évaluations : <strong>{{ gmdate('H\h i\m s\s', $totalEvaluationTime ?? 0) }}</strong></li>
        <li>❓ Questions répondues dans les évaluations : <strong>{{ $totalEvaluationQuestions ?? 0 }}</strong></li>
    </ul>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- 📊 Diagramme à barres - Comparatif des temps -->
    <div class="bg-white rounded-xl shadow p-6 col-span-2">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">⏱️ Comparatif des temps</h2>
        <div class="h-[300px]">
            <canvas id="tempsChart"></canvas>
        </div>
    </div>

    <!-- 🎯 Jauge circulaire - Taux de bonnes réponses -->
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center justify-center">
        <h2 class="text-lg font-semibold text-gray-700 mb-4 text-center">✅ Taux de bonnes réponses</h2>
        <div class="h-[150px] w-[150px]">
            <canvas id="reussiteChart" width="100" height="100"></canvas>
        </div>
        <p class="mt-4 text-sm text-gray-600">{{ $tauxBonnesReponses }}% de bonnes réponses</p>
    </div>
</div>


@endif

<!-- Modules du stagiaire -->
<div class="mt-6">
    @foreach ($modules as $module)
    <div class="bg-white rounded-xl shadow-md p-5 mb-4">
        <h3 class="text-orange-600 font-bold text-xl mb-2">{{ $module->module_title }}</h3>
        <p class="text-sm text-gray-600">{{ $module->description }}</p>
    </div>
    @endforeach
</div>
<script>
    // 🎯 Taux de bonnes réponses circulaire
    const tauxReussite = {{ $tauxBonnesReponses }};
    const reussiteChart = new Chart(document.getElementById('reussiteChart'), {
        type: 'doughnut',
        data: {
            labels: ['Bonnes réponses', 'Erreurs'],
            datasets: [{
                data: [tauxReussite, 100 - tauxReussite],
                backgroundColor: ['#22c55e', '#e5e7eb'],
                borderWidth: 1
            }]
        },
        options: {
    cutout: '70%',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: ctx => ctx.label + ': ' + ctx.raw + '%'
            }
        }
    }
}
    });
</script>
@endsection
