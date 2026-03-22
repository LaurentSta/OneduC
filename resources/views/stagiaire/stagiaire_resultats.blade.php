@extends('stagiaire.master')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- 1. EN-TÊTE --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="p-3 bg-bleuone/10 rounded-xl text-bleuone">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12h4l3 8l4 -16l3 8h4" /></svg>
        </div>
        <div>
            <h1 class="text-2xl font-raleway font-bold text-bleuone">Mes Résultats & Statistiques</h1>
            <p class="text-sm text-gray-500">Analyse détaillée de votre progression et historique des réponses.</p>
        </div>
    </div>

    {{-- 2. CARTES KPI (Engagement, Vidéo, Latence) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        {{-- Engagement --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-blue-50 text-bleuone rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] font-bold uppercase text-gray-400">Temps Total</span>
            </div>
            <p class="text-xl font-bold text-gray-900">{{ gmdate("H\h i", $engagementTotal ?? 0) }}</p>
        </div>

        {{-- Vidéos --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-orange-50 text-orangeone rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] font-bold uppercase text-gray-400">Vidéos Vues</span>
            </div>
            <p class="text-xl font-bold text-gray-900">{{ gmdate("H\h i", $videoStats->watch_time ?? 0) }}</p>
        </div>

        {{-- Réflexion --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                </div>
                <span class="text-[10px] font-bold uppercase text-gray-400">Réflexion / Quest.</span>
            </div>
            <p class="text-xl font-bold text-gray-900">{{ $averageLatencyTime ?? 0 }} s</p>
        </div>

        {{-- Persévérance --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </div>
                <span class="text-[10px] font-bold uppercase text-gray-400">Réessais</span>
            </div>
            <p class="text-xl font-bold text-gray-900">{{ $reessayeCount ?? 0 }}</p>
        </div>
    </div>

    {{-- 3. ANALYSE GLOBALE (GRAPHIQUE + TABLEAU) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        
        {{-- A. Graphique Circulaire (Donut) --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
            <h3 class="text-sm font-bold text-gray-700 mb-6 w-full text-left">Répartition des Réponses</h3>
            
            @php
                // Calcul pour le graphique CSS
                $rate = $tauxBonnesReponses ?? 0; // 0 à 100
                $wrongRate = 100 - $rate;
            @endphp

            <div class="relative w-48 h-48 rounded-full mb-4"
                 style="background: conic-gradient(#22c55e {{ $rate }}%, #f87171 0);">
                {{-- Cercle intérieur pour faire l'effet Donut --}}
                <div class="absolute inset-0 m-auto w-36 h-36 bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                    <span class="text-3xl font-black text-gray-800">{{ $rate }}%</span>
                    <span class="text-[10px] uppercase font-bold text-gray-400">De réussite</span>
                </div>
            </div>

            <div class="flex gap-6 w-full justify-center mt-2">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-700">Correct</span>
                        <span class="text-[10px] text-gray-400">Bonnes réponses</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-400"></span>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-700">Erreur</span>
                        <span class="text-[10px] text-gray-400">À revoir</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- B. Suivi Détaillé (Droit à l'erreur) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700">Suivi des questions & Tentatives</h3>
                <span class="text-[10px] font-semibold text-gray-500 bg-gray-200 px-2 py-1 rounded">
                    {{ $consolidatedQuestions->count() }} questions traitées
                </span>
            </div>
            
            <div class="overflow-y-auto custom-scrollbar flex-1 max-h-[350px]">
                @if(isset($consolidatedQuestions) && $consolidatedQuestions->count() > 0)
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0 z-10 text-[10px] uppercase text-gray-400 font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Question</th>
                                <th class="px-4 py-3 text-center" title="Nombre de tentatives">Essais</th>
                                <th class="px-4 py-3 text-center">1<sup>er</sup> Résultat</th>
                                <th class="px-4 py-3 text-center">Statut Final</th>
                                <th class="px-4 py-3 text-right">Dernier essai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($consolidatedQuestions as $q)
                                <tr class="hover:bg-gray-50/80 transition-colors group">
                                    {{-- Question --}}
                                    <td class="px-6 py-3">
                                        <p class="text-sm font-medium text-gray-800 line-clamp-2" title="{{ $q->question_text }}">
                                            {{ $q->question_text }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5 group-hover:text-bleuone transition-colors">
                                            {{ $q->module_title }}
                                        </p>
                                    </td>

                                    {{-- Compteur Tentatives --}}
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold 
                                            {{ $q->attempts_count > 1 ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $q->attempts_count }}
                                        </span>
                                    </td>

                                    {{-- 1er Résultat (Mémoire) --}}
                                    <td class="px-4 py-3 text-center">
                                        @if($q->first_result)
                                            <span class="text-green-500 text-xs font-bold" title="Du premier coup !">Correct</span>
                                        @else
                                            <span class="text-red-400 text-xs font-bold" title="Erreur au démarrage">Erreur</span>
                                        @endif
                                    </td>

                                    {{-- Statut Final (Sanctuarisé) --}}
                                    <td class="px-4 py-3 text-center">
                                        @if($q->final_status)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                Acquis
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-red-50 text-red-600 border border-red-100">
                                                À revoir
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-4 py-3 text-right text-xs text-gray-400 tabular-nums">
                                        {{ \Carbon\Carbon::parse($q->last_date)->format('d/m H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                        <p class="text-sm">Aucune donnée disponible.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. DÉTAIL PAR MODULE (ACCORDÉONS) --}}
    <div class="space-y-6">
        <h2 class="text-lg font-raleway font-bold text-gray-800 mb-4 px-1">Détail des scores par module</h2>

        {{-- On groupe par titre de module --}}
        @forelse ($resultats->groupBy(fn($item) => $item->module_title ?? 'Autre') as $moduleTitle => $scores)
            @php
                $totalLessons = $scores->count();
                $completedLessons = $scores->where('status_key', 'completed')->count();
                $modulePercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
                $isModuleSuccess = $modulePercent >= 75;

                $moduleStartAt = $scores->pluck('started_at')
                    ->filter()
                    ->map(function ($dt) {
                        return $dt instanceof \DateTimeInterface
                            ? \Illuminate\Support\Carbon::instance($dt)
                            : \Illuminate\Support\Carbon::parse($dt);
                    })
                    ->sortBy(fn ($dt) => $dt->getTimestamp())
                    ->first();

                $moduleEndAt = $scores->pluck('ended_at')
                    ->filter()
                    ->map(function ($dt) {
                        return $dt instanceof \DateTimeInterface
                            ? \Illuminate\Support\Carbon::instance($dt)
                            : \Illuminate\Support\Carbon::parse($dt);
                    })
                    ->sortByDesc(fn ($dt) => $dt->getTimestamp())
                    ->first();
            @endphp

            <div x-data="{ open: false, datesOpen: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                
                {{-- EN-TÊTE ACCORDÉON --}}
                <button @click="open = !open" class="w-full px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4 group hover:bg-gray-50/50 transition-colors">
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2
                                    {{ $isModuleSuccess ? 'bg-green-50 text-green-600 border-green-200' : 'bg-orange-50 text-orangeone border-orange-200' }}">
                            {{ $modulePercent }}%
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-bleuone group-hover:text-orangeone transition-colors">
                                {{ $moduleTitle }}
                            </h2>
                            <p class="text-xs text-gray-400 font-medium">
                                {{ $completedLessons }} / {{ $totalLessons }} leçon(s) validée(s)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="hidden md:block w-32">
                            <div class="flex justify-between text-[10px] text-gray-400 mb-1 font-bold">
                                <span>Progression</span>
                                <span>{{ $modulePercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full {{ $isModuleSuccess ? 'bg-green-500' : 'bg-orangeone' }}" style="width: {{ $modulePercent }}%"></div>
                            </div>
                        </div>
                        <span
                            role="button"
                            tabindex="0"
                            title="Voir début/fin"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-400 hover:text-bleuone hover:border-bleuone/40 bg-white"
                            @click.stop.prevent="datesOpen = true"
                            @keydown.enter.stop.prevent="datesOpen = true"
                            @keydown.space.stop.prevent="datesOpen = true"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                            </svg>
                        </span>
                        <svg :class="{'rotate-180': open}" class="w-6 h-6 text-gray-300 group-hover:text-bleuone transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </div>
                </button>

                {{-- MODALE: Date de début / date de fin du module --}}
                <template x-teleport="body">
                    <div
                        x-show="datesOpen"
                        x-cloak
                        class="fixed inset-0 flex items-center justify-center p-4"
                        style="z-index: 99999;"
                        aria-modal="true"
                        role="dialog"
                    >
                        <div class="absolute inset-0 bg-black/40" @click="datesOpen = false"></div>

                        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-100 p-6" @click.stop>
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-sm font-bold text-bleuone">Période du module</h3>
                                    <p class="text-xs text-gray-500 mt-1">{{ $moduleTitle }}</p>
                                </div>
                                <button type="button" class="text-gray-400 hover:text-gray-600" @click="datesOpen = false">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Date de début</p>
                                    <p class="text-sm font-bold text-gray-800 mt-1">
                                        {{ $moduleStartAt ? $moduleStartAt->format('d/m/Y H:i') : '-' }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                                    <p class="text-[11px] uppercase tracking-wide text-gray-400 font-semibold">Date de fin</p>
                                    <p class="text-sm font-bold text-gray-800 mt-1">
                                        {{ $moduleEndAt ? $moduleEndAt->format('d/m/Y H:i') : 'En cours' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- CONTENU DÉTAILLÉ --}}
                <div x-show="open" x-collapse>
                    <div class="border-t border-gray-100 bg-gray-50/30">
                        @foreach ($scores as $score)
                            <div class="px-6 py-4 border-b border-gray-100 last:border-0 hover:bg-white transition-colors">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                    {{-- Titre --}}
                                    <div class="md:col-span-5 flex items-start gap-3">
                                        <div class="mt-1">
                                            @if($score->status_key === 'completed')
                                                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @elseif($score->status_key === 'failed')
                                                <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @else
                                                <svg class="w-5 h-5 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M12 6v2m0 8v2m6-6h-2M8 12H6" /></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-gray-800">{{ $score->lecture_title ?? 'Leçon inconnue' }}</h3>
                                            <span class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold">
                                                {{ $score->source_label }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    {{-- Barre de progression --}}
                                    <div class="md:col-span-4">
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="text-xs font-bold w-8 text-right">{{ $score->score_percent }}%</span>
                                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $score->bar_class }}" style="width: {{ $score->score_percent }}%"></div>
                                            </div>
                                        </div>
                                        <div class="flex gap-4 text-[11px] text-gray-500 pl-11">
                                            <span class="flex items-center gap-1" title="Bonnes réponses enregistrées"><span class="w-2 h-2 rounded-full bg-green-500"></span> {{ $score->correct_answers }} correctes</span>
                                            <span class="flex items-center gap-1" title="Erreurs enregistrées"><span class="w-2 h-2 rounded-full bg-red-400"></span> {{ $score->wrong_answers }} erreurs</span>
                                            <span class="text-gray-300">/</span><span>{{ $score->total_questions }} questions posees</span>
                                        </div>
                                    </div>

                                    {{-- Temps et Statut --}}
                                    <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-4">
                                        <div class="text-right">
                                            <div class="flex items-center gap-1 text-gray-500 text-xs font-medium">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                {{ $score->time }}
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide border {{ $score->status_class }}">
                                            {{ $score->status_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Aucun résultat enregistré</h3>
                <a href="{{ route('stagiaire.modules') }}" class="inline-flex mt-6 items-center px-4 py-2 bg-bleuone text-white rounded-lg text-sm font-semibold hover:bg-opacity-90 transition">
                    Aller aux modules
                </a>
            </div>
        @endforelse
    </div>
</div>

<script src="//unpkg.com/alpinejs" defer></script>
@endsection
