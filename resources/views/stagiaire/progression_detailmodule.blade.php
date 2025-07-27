@extends('stagiaire.master')

@section('content')
<div class="p-6">
    <h1 class="text-titre font-raleway text-bleuone mb-6">📁 Détail par module</h1>

    @forelse ($resultats->groupBy('lecture.module.module_title') as $moduleTitle => $scores)
        <div x-data="{ open: false }" class="mb-6 border rounded-xl shadow overflow-hidden">
            <!-- En-tête Accordéon -->
            <button @click="open = !open" class="w-full px-6 py-4 bg-bleuone text-white flex justify-between items-center">
                <span class="font-semibold text-lg">Module : {{ $moduleTitle ?? 'Inconnu' }}</span>
                <svg :class="{'rotate-180': open}" class="w-5 h-5 transition-transform transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Contenu Accordéon -->
            <div x-show="open" x-collapse class="bg-white px-6 py-4">
                <table class="min-w-full table-auto text-sm text-left">
                    <thead class="bg-gray-100 text-gray-700 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Leçon</th>
                            <th class="px-4 py-3">Questions attendues</th>
                            <th class="px-4 py-3">Réponses enregistrées</th>
                            <th class="px-4 py-3">Bonnes réponses</th>
                            <th class="px-4 py-3">Mauvaises réponses</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scores as $score)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $score->lecture->lecture_title ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $score->total_questions ?? 0 }}</td>
                                <td class="px-4 py-2">{{ $score->answered_questions ?? 0 }}</td>
                                <td class="px-4 py-2">{{ $score->correct_score / 10 ?? 0 }}</td>
                                <td class="px-4 py-2">{{ ($score->answered_questions ?? 0) - ($score->correct_score / 10 ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="text-gray-600">Aucune donnée disponible pour les modules suivis.</p>
    @endforelse
</div>
<script src="https://unpkg.com/alpinejs" defer></script>
@endsection
