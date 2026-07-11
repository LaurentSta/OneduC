@php
    $creneauxLabels = [
        'matin' => 'Matin',
        'apres_midi' => 'Après-midi',
        'journee' => 'Journée complète',
        'soiree' => 'Soirée',
    ];
    $statutsLabels = [
        'planifiee' => ['label' => 'Planifiée', 'class' => 'bg-gray-100 text-gray-700'],
        'ouverte' => ['label' => 'Ouverte', 'class' => 'bg-vertone/10 text-vertone'],
        'cloturee' => ['label' => 'Clôturée', 'class' => 'bg-bleuone/10 text-bleuone'],
    ];
@endphp

<div class="flex items-center gap-2 mb-6">
    <h3 class="text-xl font-bold text-bleuone font-raleway">Feuilles d'émargement — {{ $group->name }}</h3>
    <div class="relative group">
        <button type="button" aria-label="Information sur l'émargement" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
            ?
        </button>
        <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-80 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
            Créez une séance par créneau daté (matin, après-midi...). Chaque stagiaire du groupe signe individuellement, avec une signature graphique, conforme aux exigences d'audit Qualiopi/OPCO.
        </div>
    </div>
</div>

@if (session('success'))
    <div class="mb-6 rounded-lg bg-vertone/10 text-vertone px-4 py-2 text-sm font-semibold">
        {{ session('success') }}
    </div>
@endif

{{-- Création d'une nouvelle séance --}}
<div class="bg-white border border-gray-200 rounded-[12px] p-6 mb-8">
    <h4 class="text-sm font-bold text-bleuone uppercase mb-4">Nouvelle séance</h4>
    <form method="POST" action="{{ route('formateur.groupes.emargement.store', $group->id) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        @csrf
        <div class="sm:col-span-1">
            <label for="seance_date" class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
            <input type="date" name="date" id="seance_date" required
                   value="{{ old('date') }}"
                   class="w-full rounded-[8px] border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
        </div>
        <div class="sm:col-span-1">
            <label for="seance_creneau" class="block text-xs font-bold text-gray-500 uppercase mb-1">Créneau</label>
            <select name="creneau" id="seance_creneau" required
                    class="w-full rounded-[8px] border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
                @foreach ($creneauxLabels as $value => $label)
                    <option value="{{ $value }}" @selected(old('creneau') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-1">
            <label for="seance_titre" class="block text-xs font-bold text-gray-500 uppercase mb-1">Titre (optionnel)</label>
            <input type="text" name="titre" id="seance_titre" maxlength="255"
                   value="{{ old('titre') }}" placeholder="Ex. Atelier Excel"
                   class="w-full rounded-[8px] border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
        </div>
        <div class="sm:col-span-1">
            <button type="submit" class="btn-oneduc w-full !py-2 !text-sm">Créer la séance</button>
        </div>
    </form>
    @error('date')
        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
    @enderror
    @error('creneau')
        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
    @enderror
</div>

{{-- Liste des séances --}}
@if ($seances->isEmpty())
    <p class="text-sm text-gray-500 italic">Aucune séance créée pour ce groupe pour le moment.</p>
@else
    <div class="border border-gray-200 rounded-[12px] overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-bleuone text-xs uppercase text-white font-bold">
                <tr>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Créneau</th>
                    <th class="px-6 py-3">Statut</th>
                    <th class="px-6 py-3">Signatures</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($seances as $seance)
                    @php $pilotageUrl = route('formateur.groupes.emargement.show', [$group->id, $seance->id]); @endphp
                    <tr class="{{ $loop->odd ? 'bg-white' : 'bg-slate-50' }} cursor-pointer hover:bg-slate-100 transition"
                        onclick="window.location.href='{{ $pilotageUrl }}'">
                        <td class="px-6 py-3 font-medium text-gray-900">
                            {{ $seance->date->format('d/m/Y') }}
                            @if ($seance->titre)
                                <span class="block text-xs text-gray-500">{{ $seance->titre }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $creneauxLabels[$seance->creneau] ?? $seance->creneau }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statutsLabels[$seance->statut]['class'] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statutsLabels[$seance->statut]['label'] ?? $seance->statut }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">
                            {{ $seance->presences_signees_count }} / {{ $seance->presences_count }}
                        </td>
                        <td class="px-6 py-3 text-right whitespace-nowrap">
                            <a href="{{ $pilotageUrl }}"
                               class="inline-flex items-center gap-1.5 rounded-[8px] bg-bleuone px-3 py-1.5 text-xs font-bold text-white hover:bg-bleuone/90 transition mr-2">
                                Piloter
                            </a>
                            <a href="{{ route('formateur.groupes.emargement.export-pdf', [$group->id, $seance->id]) }}"
                               onclick="event.stopPropagation()"
                               class="inline-flex items-center gap-1.5 rounded-[8px] border border-bleuone px-3 py-1.5 text-xs font-bold text-bleuone hover:bg-bleuone/5 transition">
                                PDF
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
