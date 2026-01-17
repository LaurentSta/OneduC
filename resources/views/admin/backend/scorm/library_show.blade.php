@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-6xl mx-auto mt-10">
    <div class="bg-white p-6 rounded-2xl shadow">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-bleuone">SCORM : {{ $package->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">
                    Slug : <code class="px-1 py-0.5 bg-gray-100 rounded">{{ $package->slug }}</code>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.scorm.library.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-orangeone hover:underline">
                    <span aria-hidden="true">←</span> Retour à la bibliothèque
                </a>

                <a href="{{ route('admin.scorm.library.test') }}"
                   class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                    Importer une nouvelle version
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @php
            $activeId = $package->active_version_id;
        @endphp

        @if($package->versions->isEmpty())
            <div class="text-sm text-gray-500">
                Aucune version disponible pour ce SCORM.
            </div>
        @else
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Version</th>
                            <th class="px-4 py-3 text-left">Import</th>
                            <th class="px-4 py-3 text-left">Taille</th>
                            <th class="px-4 py-3 text-left">API.js</th>
                            <th class="px-4 py-3 text-left">Utilisée (leçons figées)</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($package->versions as $v)
                            @php
                                $isActive = (int)$v->id === (int)$activeId;
                                $usedCount = (int)($usageCounts[$v->id] ?? 0);
                                $canDelete = !$isActive && $usedCount === 0;
                            @endphp

                            <tr class="{{ $isActive ? 'bg-green-50' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $v->version }}</td>

                                <td class="px-4 py-3 text-gray-600">
                                    {{ optional($v->imported_at)->format('d/m/Y H:i') ?? '—' }}
                                </td>

                                <td class="px-4 py-3 text-gray-600">
                                    @if(!empty($v->size_bytes))
                                        {{ number_format($v->size_bytes / 1024 / 1024, 2, ',', ' ') }} Mo
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-gray-600">
                                    {{ $v->api_injected ? 'Oui' : 'Non / Déjà présent' }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($usedCount > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-orange-50 text-orange-700 text-xs font-semibold rounded">
                                            {{ $usedCount }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">0</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($isActive)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">
                                            Active
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">

                                        {{-- Ouvrir --}}
                                        @if(!empty($v->index_path))
                                            <a href="{{ url($v->index_path) }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 hover:bg-gray-50">
                                                Ouvrir
                                            </a>
                                        @endif

                                        {{-- Activer --}}
                                        @if(!$isActive)
                                            <form method="POST" action="{{ route('admin.scorm.library.versions.activate', $v) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 bg-bleuone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                                                    Activer
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Supprimer --}}
                                        <form method="POST" action="{{ route('admin.scorm.library.versions.destroy', $v) }}"
                                              onsubmit="return confirm('Supprimer définitivement cette version ? Cette action est irréversible.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg border {{ $canDelete ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-gray-200 text-gray-400 cursor-not-allowed' }}"
                                                    {{ $canDelete ? '' : 'disabled' }}>
                                                Supprimer
                                            </button>
                                        </form>

                                    </div>

                                    @if(!$canDelete)
                                        <div class="text-xs text-gray-400 mt-1">
                                            @if($isActive)
                                                Version active non supprimable.
                                            @elseif($usedCount > 0)
                                                Utilisée par {{ $usedCount }} leçon(s).
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-500 mt-4">
                La suppression est désactivée pour la version active et pour toute version utilisée par une leçon (version figée).
            </p>
        @endif

    </div>
</div>
@endsection
