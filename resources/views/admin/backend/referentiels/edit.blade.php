{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/referentiels/edit.blade.php --}}
@extends('admin.admin_dashboard')
@section('admin')

{{-- En-tête --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Modifier un référentiel de compétences</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …mettre à jour le cadre d’évaluation
                </x-typography>

                <div class="prose-oneduc">
                    Modifiez le nom, le code, la description et le statut. Les référentiels seront ensuite associés à des groupes pour suivre l’acquisition des compétences.
                </div>
            </div>

            <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Messages --}}
@if (session('success'))
    <div class="max-w-[1248px] mx-auto px-4">
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-[20px] shadow-sm">
            {{ session('success') }}
        </div>
    </div>
@endif

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
            <h2 class="text-xl font-semibold text-gray-800">Édition du référentiel</h2>
            <a href="{{ route('admin.referentiels.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50">
                Retour à la liste
            </a>
        </div>

        <form action="{{ route('admin.referentiels.update', $referentiel->id) }}" method="POST" class="px-6 py-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-800 mb-2">
                    Nom du référentiel <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $referentiel->name) }}"
                    required
                    maxlength="150"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                    placeholder="Ex : PIX, DigComp, Référentiel interne"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Code --}}
            <div>
                <label for="code" class="block text-sm font-semibold text-gray-800 mb-2">
                    Code <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', $referentiel->code) }}"
                    required
                    maxlength="50"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                    placeholder="Ex : PIX, DIGCOMP, REF_INTERNE"
                >
                <p class="mt-2 text-xs text-gray-600">
                    Le code doit être unique. Il sert d’identifiant stable pour le référentiel.
                </p>
                @error('code')
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
                    placeholder="Décrivez brièvement l’objectif et le contexte d’utilisation de ce référentiel."
                >{{ old('description', $referentiel->description) }}</textarea>
                @error('description')
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
                    {{ old('status', $referentiel->status) ? 'checked' : '' }}
                >
                <div>
                    <label for="status" class="text-sm font-semibold text-gray-800">
                        Référentiel actif
                    </label>
                    <p class="text-xs text-gray-600 mt-1">
                        Si désactivé, il ne sera pas proposé lors de la création d’un groupe (quand cette étape sera mise en place).
                    </p>
                </div>
                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="pt-4 flex flex-col sm:flex-row gap-3 sm:items-center">
                <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-orangeone text-white text-sm font-semibold rounded hover:bg-orange-600 transition">
                    Enregistrer
                </button>

                <a href="{{ route('admin.referentiels.index') }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-semibold rounded hover:bg-gray-50 transition">
                    Annuler
                </a>
            </div>

        </form>

        {{-- Zone “danger” --}}
        <div class="mt-10 border-t pt-6 px-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Zone sensible</h3>
            <p class="text-xs text-gray-600 mb-4">
                La suppression est une suppression logique. Le référentiel pourra être restauré si vous mettez en place un écran “Supprimés”.
            </p>

            <button type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'delete-referentiel-{{ $referentiel->id }}')"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700 transition">
                Supprimer le référentiel
            </button>
            <x-confirm-modal
                name="delete-referentiel-{{ $referentiel->id }}"
                title="Supprimer ce référentiel ?"
                :action="route('admin.referentiels.destroy', $referentiel->id)"
                method="DELETE"
                confirm-label="Supprimer"
            />
        </div>

    </div>
</div>

@endsection
