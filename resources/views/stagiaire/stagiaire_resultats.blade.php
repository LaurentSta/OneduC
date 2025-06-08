@extends('stagiaire.master')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">🎯 Mes résultats par module</h1>




    @forelse ($resultats->groupBy('lecture.module.module_title') as $moduleTitle => $scores)
        <div x-data="{ open: false }" class="mb-4 border rounded shadow">
            <button @click="open = !open"
                    class="w-full text-left px-4 py-3 bg-blue-100 hover:bg-blue-200 font-semibold flex justify-between items-center">
                <span>Formation : {{ $moduleTitle ?? 'Module inconnu' }}</span>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>


            <div x-show="open" x-collapse class="bg-white">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="text-left px-4 py-2">Leçon</th>
                            <th class="text-left px-4 py-2">Score</th>
                            <th class="text-left px-4 py-2">Nbr. Questions</th>
                            <th class="text-left px-4 py-2">Complété ?</th>
                            <th class="text-left px-4 py-2">⏱️ Temps passé</th> {{-- ✅ Nouveau --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scores as $score)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $score->lecture->lecture_title ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $score->correct_score ?? '-' }}/{{ $score->total_score_possible ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $score->answered_questions ?? '-' }} / {{ $score->total_questions ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    @if ($score->lesson_status === 'completed')
                                        <span class="text-green-600 font-semibold">✅ Terminé</span>
                                    @elseif ($score->lesson_status === 'incomplete')
                                        <span class="text-orange-600 font-semibold">⏳ Incomplet</span>
                                    @else
                                        <span class="text-gray-500 italic">– Aucune donnée</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    {{ gmdate('H\h i\m s\s', $score->session_time ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    @empty
        <p class="text-gray-600">Aucun résultat trouvé.</p>
    @endforelse
</div>

{{-- Alpine.js nécessaire pour le fonctionnement de l'accordéon --}}
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
