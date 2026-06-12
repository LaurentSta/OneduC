@extends('admin.admin_dashboard')

@section('admin')
@php
    $formatPercent = fn ($value): string => $value === null ? '-' : number_format((float) $value, 1, ',', ' ') . ' %';
    $formatScore = fn ($value): string => $value === null ? '-' : number_format((float) $value, 2, ',', ' ');
    $formatDate = fn ($value): string => $value ? \Illuminate\Support\Carbon::parse($value)->format('d/m/Y H:i') : '-';
    $badge = function (array $indicator): array {
        if ($indicator['is_low_sample'] ?? false) {
            return ['Effectif insuffisant', 'bg-slate-100 text-slate-700 border-slate-200'];
        }

        if ($indicator['is_alert'] ?? false) {
            return ['Alerte', 'bg-red-100 text-red-700 border-red-200'];
        }

        return ['OK', 'bg-emerald-100 text-emerald-700 border-emerald-200'];
    };

    [$completionBadge, $completionBadgeClass] = $badge($dashboard['completion']);
    [$firstAttemptBadge, $firstAttemptBadgeClass] = $badge($dashboard['first_attempt']);
    [$deepBadge, $deepBadgeClass] = $badge($dashboard['deep']);
    [$transferBadge, $transferBadgeClass] = $badge($dashboard['transfer']);
@endphp

<div class="mx-auto w-full max-w-[1500px] space-y-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold text-bleuone">Pilotage qualité - Parcours formateur</h1>
            <p class="mt-1 text-sm text-gray-600">
                Module 2 : {{ $dashboard['module']['title'] }}.
            </p>
        </div>
        <a href="{{ route('admin.pilotage.index') }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Retour pilotage
        </a>
    </div>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600">Complétion Module 2</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $formatPercent($dashboard['completion']['rate']) }}</p>
                </div>
                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $completionBadgeClass }}">{{ $completionBadge }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-600">
                {{ $dashboard['completion']['completed_count'] }} terminés / {{ $dashboard['completion']['started_count'] }} commencés.
            </p>
            <p class="mt-1 text-xs text-gray-500">Seuil : {{ $dashboard['completion']['threshold'] }}</p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600">Réussite au 1er essai</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $formatPercent($dashboard['first_attempt']['rate']) }}</p>
                </div>
                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $firstAttemptBadgeClass }}">{{ $firstAttemptBadge }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-600">
                {{ $dashboard['first_attempt']['total_first_successes'] }} réussites / {{ $dashboard['first_attempt']['total_first_attempts'] }} premiers essais.
            </p>
            <p class="mt-1 text-xs text-gray-500">Seuil : {{ $dashboard['first_attempt']['threshold'] }}</p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600">Score DEEP</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $formatScore($dashboard['deep']['global_average']) }}/5</p>
                </div>
                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $deepBadgeClass }}">{{ $deepBadge }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-600">{{ $dashboard['deep']['respondent_count'] }} répondant(s).</p>
            <p class="mt-1 text-xs text-gray-500">Seuil : {{ $dashboard['deep']['threshold'] }}</p>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-600">Transfert 30 jours</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $formatPercent($dashboard['transfer']['rate']) }}</p>
                </div>
                <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $transferBadgeClass }}">{{ $transferBadge }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-600">
                {{ $dashboard['transfer']['created_count'] }} groupe(s) réel(s) / {{ $dashboard['transfer']['eligible_count'] }} formateur(s) éligible(s).
            </p>
            <p class="mt-1 text-xs text-gray-500">Seuil : {{ $dashboard['transfer']['threshold'] }}</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_26rem]">
        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Formateurs</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-3 py-3">Formateur</th>
                            <th class="px-3 py-3">Progression</th>
                            <th class="px-3 py-3">Complétion</th>
                            <th class="px-3 py-3">DEEP</th>
                            <th class="px-3 py-3">Groupe réel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($dashboard['trainers'] as $trainer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3">
                                    <p class="font-medium text-gray-900">{{ $trainer['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $trainer['email'] }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="font-semibold text-bleuone">{{ $trainer['completed_steps'] }}/{{ $trainer['required_steps'] }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($trainer['is_completed'])
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $formatDate($trainer['completed_at']) }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Non</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if ($trainer['questionnaire_received'])
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">Oui</span>
                                        <span class="ml-1 text-xs text-gray-500">{{ $formatDate($trainer['questionnaire_submitted_at']) }}</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Non</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if ($trainer['real_group_created'])
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Oui</span>
                                        <p class="mt-1 text-xs text-gray-500">{{ $trainer['real_group_name'] }} - {{ $formatDate($trainer['real_group_created_at']) }}</p>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Non</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500">Aucun formateur trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Dimensions DEEP</h2>
            <div class="mt-4 space-y-3">
                @foreach ($dashboard['deep']['dimensions'] as $dimension)
                    <div class="rounded-lg border border-gray-200 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-gray-800">{{ $dimension['title'] }}</p>
                            <span class="text-sm font-bold text-bleuone">{{ $formatScore($dimension['average']) }}/5</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ $dimension['respondent_count'] }} répondant(s)</p>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Activités Module 2</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-3">Activité</th>
                        <th class="px-3 py-3">Type</th>
                        <th class="px-3 py-3">Tentatives</th>
                        <th class="px-3 py-3">Réussite au 1er essai</th>
                        <th class="px-3 py-3">Fiabilité</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($dashboard['activities'] as $activity)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3">
                                <p class="font-medium text-gray-900">{{ $activity['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['lesson_label'] }}</p>
                            </td>
                            <td class="px-3 py-3 text-gray-700">{{ $activity['activity_type'] }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $activity['attempts'] }}</td>
                            <td class="px-3 py-3">
                                <span class="font-semibold text-bleuone">{{ $formatPercent($activity['first_success_rate']) }}</span>
                                <span class="ml-1 text-xs text-gray-500">({{ $activity['first_successes'] }}/{{ $activity['first_attempts'] }})</span>
                            </td>
                            <td class="px-3 py-3">
                                @if ($activity['is_history_reliable'])
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $activity['reliability_label'] }}</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $activity['reliability_label'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
