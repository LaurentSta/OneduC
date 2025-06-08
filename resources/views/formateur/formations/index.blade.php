@extends('formateur.dashboard')

@section('formateur')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">📚 Tous les modules de formation</h2>

    @if($modules->isEmpty())
        <p class="text-gray-500">Aucun module de formation trouvé.</p>
    @else
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full text-sm text-left text-gray-800">
                <thead class="bg-gray-100 text-xs text-gray-600 uppercase">
                    <tr>
                        <th class="px-4 py-3">Titre</th>
                        <th class="px-4 py-3">Date de création</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Groupes associés</th>
                        <th class="px-4 py-3">Stagiaires total</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $module)
                        @php
                            $groupes = $module->groups;
                            $stagiaires = $groupes->flatMap(fn($g) => $g->users->where('role', 'stagiaire'))->unique('id');
                            $statut = $groupes->count() > 0 ? '✅ Utilisé' : '❌ Non utilisé';
                        @endphp
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $module->module_title }}</td>
                            <td class="px-4 py-3">{{ $module->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $statut }}</td>
                            <td class="px-4 py-3">{{ $groupes->count() }}</td>
                            <td class="px-4 py-3">{{ $stagiaires->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="#" class="text-orange-600 hover:underline text-sm">📄 Voir</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
