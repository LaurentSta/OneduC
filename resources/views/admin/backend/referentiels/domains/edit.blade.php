@extends('admin.admin_dashboard')
@section('admin')

{{-- En-tête --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <x-typography variant="titre">Modifier un domaine</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-orangeone">
            Référentiel : {{ $referentiel->name }}
        </x-typography>

        <div class="prose-oneduc mt-4">
            Modifiez le nom, la description, l’ordre d’affichage et le statut du domaine.
        </div>
    </div>
</div>

{{-- Erreurs --}}
@if ($errors->any())
    <div class="max-w-[1248px] mx-auto px-4">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-[20px] shadow-sm p-6 mb-6">
            <p class="font-semibold mb-2">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- Formulaire --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">

        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Édition du domaine</h2>
            <a href="{{ route('admin.referentiels.domains.index', $referentiel) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50">
                Retour à la liste
            </a>
        </div>

        <form action="{{ route('admin.referentiels.domains.update', [$referentiel, $domain]) }}" method="POST" class="px-6 py-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-800 mb-2">
                    Nom du domaine <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $domain->name) }}"
                    required
                    maxlength="150"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-800 mb-2">
                    Description (optionnel)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    maxlength="5000"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                >{{ old('description', $domain->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Position --}}
            <div>
                <label for="position" class="block text-sm font-semibold text-gray-800 mb-2">
                    Ordre d’affichage
                </label>
                <input
                    type="number"
                    id="position"
                    name="position"
                    value="{{ old('position', $domain->position ?? 0) }}"
                    min="0"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                >
                @error('position')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Statut --}}
            <div class="flex items-start gap-3">
                <input
                    type="checkbox"
                    id="status"
                    name="status"
                    value="1"
                    class="mt-1 h-5 w-5 rounded border-gray-300 text-orangeone focus:ring-orangeone"
                    {{ old('status', $domain->status) ? 'checked' : '' }}
                >
                <div>
                    <label for="status" class="text-sm font-semibold text-gray-800">
                        Domaine actif
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-4 flex flex-col sm:flex-row gap-3 sm:items-center">
                <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-orangeone text-white text-sm font-semibold rounded hover:bg-orange-600 transition">
                    Enregistrer
                </button>

                <a href="{{ route('admin.referentiels.domains.index', $referentiel) }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-semibold rounded hover:bg-gray-50 transition">
                    Annuler
                </a>
            </div>

        </form>

        {{-- Suppression --}}
        <div class="mt-10 border-t pt-6 px-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Zone sensible</h3>
            <p class="text-xs text-gray-600 mb-4">
                La suppression est une suppression logique (soft delete).
            </p>

            <button type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'delete-domain')"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700 transition">
                Supprimer le domaine
            </button>
            <x-confirm-modal
                name="delete-domain"
                title="Supprimer ce domaine ?"
                :action="route('admin.referentiels.domains.destroy', [$referentiel, $domain])"
                method="DELETE"
                confirm-label="Supprimer"
            />
        </div>

    </div>
</div>

@endsection
