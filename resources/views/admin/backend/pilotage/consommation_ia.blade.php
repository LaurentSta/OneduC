@extends('admin.admin_dashboard')

@section('admin')
@php
    $formatNumber = fn ($value): string => number_format((float) $value, 0, ',', ' ');
    $formatDate = fn ($value): string => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y H:i') : '-';
    $totalTokens = $formateurs->sum('total_tokens');
    $totalAppels = $formateurs->sum('appels');
@endphp

<div class="mx-auto w-full max-w-[1500px] space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-bleuone">Consommation IA (Mistral)</h1>
            <p class="mt-1 text-sm text-gray-600">Tokens consommés par formateur lors des générations de leçons et formations par IA.</p>
        </div>
        <a href="{{ route('admin.pilotage.index') }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Retour pilotage
        </a>
    </div>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Total tokens consommés</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $formatNumber($totalTokens) }}</p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-600">Total générations IA</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $formatNumber($totalAppels) }}</p>
        </article>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Détail par formateur</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-3">Formateur</th>
                        <th class="px-3 py-3">Générations</th>
                        <th class="px-3 py-3">Tokens consommés</th>
                        <th class="px-3 py-3">Dernière génération</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($formateurs as $formateur)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3 font-medium text-gray-900">{{ $formateur['nom'] }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $formateur['appels'] }}</td>
                            <td class="px-3 py-3 font-semibold text-bleuone">{{ $formatNumber($formateur['total_tokens']) }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $formatDate($formateur['derniere_generation']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">Aucune génération IA enregistrée pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
