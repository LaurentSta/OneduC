@extends('admin.admin_dashboard')
@section('admin')

{{-- En-tête --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <x-typography variant="titre">
            Compétences – {{ $referentiel->name }}
        </x-typography>

        <x-typography variant="sous-titre" class="font-varela text-orangeone">
            Capacités évaluables du référentiel
        </x-typography>

        <div class="prose-oneduc mt-4">
            Les compétences décrivent ce que le stagiaire est capable de faire.
            Elles peuvent être associées à un domaine ou rester indépendantes.
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
            <h2 class="text-lg font-semibold text-gray-900">
                Liste des compétences
            </h2>

            <a href="{{ route('admin.referentiels.skills.create', $referentiel) }}"
               class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-semibold rounded hover:bg-orange-600">
                Ajouter une compétence
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Domaine</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($skills as $skill)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $skill->name }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $skill->code ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $skill->domain?->name ?? 'Sans domaine' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($skill->status)
                                <span class="text-green-700 font-semibold">Actif</span>
                            @else
                                <span class="text-gray-500">Inactif</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.referentiels.skills.edit', [$referentiel, $skill]) }}"
                                   class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                                    Éditer
                                </a>

                                <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-skill-{{ $skill->id }}')"
                                        class="px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                    Supprimer
                                </button>
                                <x-confirm-modal
                                    name="delete-skill-{{ $skill->id }}"
                                    title="Supprimer cette compétence ?"
                                    :action="route('admin.referentiels.skills.destroy', [$referentiel, $skill])"
                                    method="DELETE"
                                    confirm-label="Supprimer"
                                />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            Aucune compétence définie pour ce référentiel.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="{{ route('admin.referentiels.edit', $referentiel) }}"
               class="text-sm text-gray-600 hover:underline">
                ← Retour au référentiel
            </a>
        </div>

    </div>
</div>

@endsection
