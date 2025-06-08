@extends('formateur.dashboard')
@section('formateur')

<div class="max-w-7xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Suivi des progressions des stagiaires</h1>

    <table class="min-w-full bg-white border">
        <thead class="bg-blue-100 text-blue-900">
            <tr>
                <th class="py-2 px-4 text-left">👤 Stagiaire</th>
                <th class="py-2 px-4 text-left">📘 Leçon</th>
                <th class="py-2 px-4 text-left">📚 Module</th>
                <th class="py-2 px-4 text-left">⏱️ Terminé le</th>
            </tr>
        </thead>
        <tbody>
            @forelse($progressions as $p)
                <tr class="border-b hover:bg-blue-50">
                    <td class="py-2 px-4">{{ $p->user->name ?? 'Inconnu' }}</td>
                    <td class="py-2 px-4">{{ $p->lecture->lecture_title ?? 'Lecture supprimée' }}</td>
                    <td class="py-2 px-4">{{ $p->lecture->section->module->module_title ?? 'Module supprimé' }}</td>
                    <td class="py-2 px-4">{{ \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y à H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-4 text-gray-500">Aucune progression enregistrée pour le moment.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
