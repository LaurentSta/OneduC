@extends('admin.admin_dashboard')
@section('admin')

{{-- En-tête --}}
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Modifier une compétence</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Référentiel : {{ $referentiel->name }}
                </x-typography>

                <div class="prose-oneduc">
                    Modifiez le nom, le domaine, le code, la description et le statut de la compétence.
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
            <h2 class="text-xl font-semibold text-gray-800">Édition de la compétence</h2>
            <a href="{{ route('admin.referentiels.skills.index', $referentiel) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50">
                Retour à la liste
            </a>
        </div>

        <form action="{{ route('admin.referentiels.skills.update', [$referentiel, $skill]) }}" method="POST" class="px-6 py-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-800 mb-2">
                    Nom de la compétence <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $skill->name) }}"
                    required
                    maxlength="150"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Domaine --}}
            <div>
                <label for="skill_domain_id" class="block text-sm font-semibold text-gray-800 mb-2">
                    Domaine (optionnel)
                </label>
                <select
                    id="skill_domain_id"
                    name="skill_domain_id"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-orangeone"
                >
                    <option value="">Sans domaine</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" @selected(old('skill_domain_id', $skill->skill_domain_id) == $domain->id)>
                            {{ $domain->name }}
                        </option>
                    @endforeach
                </select>
                @error('skill_domain_id')
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
                    value="{{ old('code', $skill->code) }}"
                    required
                    maxlength="50"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orangeone"
                >
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
                >{{ old('description', $skill->description) }}</textarea>
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
                    value="{{ old('position', $skill->position ?? 0) }}"
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
                    {{ old('status', $skill->status) ? 'checked' : '' }}
                >
                <div>
                    <label for="status" class="text-sm font-semibold text-gray-800">
                        Compétence active
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-4 flex flex-col sm:flex-row gap-3 sm:items-center">
                <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-orangeone text-white text-sm font-semibold rounded hover:bg-orange-600 transition">
                    Enregistrer
                </button>

                <a href="{{ route('admin.referentiels.skills.index', $referentiel) }}"
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

            <form id="delete-skill-form"
                  action="{{ route('admin.referentiels.skills.destroy', [$referentiel, $skill]) }}"
                  method="POST">
                @csrf
                @method('DELETE')

                <button type="button"
                        onclick="if(confirm('Supprimer cette compétence ?')) document.getElementById('delete-skill-form').submit();"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700 transition">
                    Supprimer la compétence
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
