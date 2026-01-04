@extends('admin.admin_dashboard')
@section('admin')

{{-- En-tête --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <x-typography variant="titre">Domaines – {{ $referentiel->name }}</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-orangeone">
            Organisation optionnelle des compétences
        </x-typography>

        <div class="prose-oneduc mt-4">
            Les domaines permettent de regrouper des compétences par grands thèmes.
            Certains référentiels peuvent fonctionner sans domaines.
        </div>
    </div>
</div>

{{-- Messages --}}
@if (session('success'))
    <div class="max-w-[1248px] mx-auto px-4 mb-6">
        <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-[20px]">
            {{ session('success') }}
        </div>
    </div>
@endif

{{-- Tableau --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 w-full">

        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900">Liste des domaines</h2>

            <div class="flex gap-3">
                <a href="{{ route('admin.referentiels.edit', $referentiel) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50">
                    Retour au référentiel
                </a>

                <a href="{{ route('admin.referentiels.domains.create', $referentiel) }}"
                   class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-semibold rounded hover:bg-orange-600">
                    Ajouter un domaine
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Ordre</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($domains as $domain)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $domain->name }}</td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $domain->position ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($domain->status)
                                <span class="text-green-700 font-semibold">Actif</span>
                            @else
                                <span class="text-gray-500">Inactif</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.referentiels.domains.edit', [$referentiel, $domain]) }}"
                                   class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                    Éditer
                                </a>

                                <form action="{{ route('admin.referentiels.domains.destroy', [$referentiel, $domain]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Supprimer ce domaine ?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            Aucun domaine défini pour ce référentiel.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
