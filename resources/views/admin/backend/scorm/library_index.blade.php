@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-6xl mx-auto mt-10">
    <div class="bg-white p-6 rounded-2xl shadow">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-bleuone">Bibliothèque SCORM</h2>

            <a href="{{ route('admin.scorm.library.test') }}"
               class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                + Importer un SCORM
            </a>
        </div>

        @if($packages->isEmpty())
            <div class="text-sm text-gray-500">
                Aucun SCORM importé pour le moment.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Nom</th>
                            <th class="px-4 py-3 text-left">Slug</th>
                            <th class="px-4 py-3 text-left">Version active</th>
                            <th class="px-4 py-3 text-center">Versions</th>
                            <th class="px-4 py-3 text-left">Dernier import</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($packages as $package)
                            <tr>
                                <td class="px-4 py-3 font-medium">
                                    <a href="{{ route('admin.scorm.library.show', $package) }}"
                                    class="text-bleuone font-semibold hover:underline">
                                        {{ $package->name }}
                                    </a>

                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $package->versions_count }} version(s)
                                        @if($package->activeVersion)
                                            • active : {{ $package->activeVersion->version }}
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-gray-600">
                                    {{ $package->slug }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($package->activeVersion)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 text-xs font-semibold rounded">
                                            {{ $package->activeVersion->version }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $package->versions_count }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ optional($package->activeVersion?->imported_at)->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ url($package->activeVersion?->index_path) }}"
                                       target="_blank"
                                       class="text-sm font-semibold text-orangeone hover:underline">
                                        Ouvrir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endsection
