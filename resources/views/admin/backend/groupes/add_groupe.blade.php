@extends('admin.admin_dashboard')

@section('title', 'Créer un groupe')

@section('admin')
    @php
        $stagiairesSelectionnes = collect(old('stagiaires', []))
            ->map(fn ($id) => (int) $id)
            ->all();
    @endphp

    <div class="space-y-4">
        <header class="border-b border-slate-200 pb-4">
            <nav aria-label="Fil d’Ariane" class="text-xs font-medium text-slate-500">
                <a href="{{ route('admin.groupes') }}" class="transition hover:text-bleuone">Groupes</a>
                <span class="mx-1.5" aria-hidden="true">/</span>
                <span aria-current="page">Création</span>
            </nav>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-bleuone">Créer un groupe</h1>
            <p class="mt-1 text-sm text-slate-600">Définissez le responsable pédagogique et constituez l’effectif initial.</p>
        </header>

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                <p class="font-semibold">Le formulaire contient des erreurs.</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.groupes.store') }}" method="POST" class="grid gap-4 lg:grid-cols-12">
            @csrf

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-5" aria-labelledby="titre-identite-groupe">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 id="titre-identite-groupe" class="text-sm font-semibold text-slate-950">Informations du groupe</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Les champs marqués d’un astérisque sont obligatoires.</p>
                </div>

                <div class="space-y-4 p-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700">Nom du groupe <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            maxlength="255"
                            autocomplete="off"
                            class="mt-1.5 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-orangeone focus:ring-orangeone"
                            @error('name') aria-invalid="true" aria-describedby="erreur-name" @enderror
                        >
                        @error('name')
                            <p id="erreur-name" class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            maxlength="5000"
                            class="mt-1.5 w-full resize-y rounded-lg border-slate-300 text-sm shadow-sm focus:border-orangeone focus:ring-orangeone"
                            @error('description') aria-invalid="true" aria-describedby="erreur-description" @enderror
                        >{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Contexte, objectifs ou informations utiles à l’équipe pédagogique.</p>
                        @error('description')
                            <p id="erreur-description" class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="formateur_id" class="block text-sm font-semibold text-slate-700">Formateur responsable <span class="text-red-600" aria-hidden="true">*</span></label>
                        <select
                            id="formateur_id"
                            name="formateur_id"
                            required
                            class="mt-1.5 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-orangeone focus:ring-orangeone"
                            @error('formateur_id') aria-invalid="true" aria-describedby="erreur-formateur" @enderror
                        >
                            <option value="">Sélectionner un formateur</option>
                            @foreach ($formateurs as $formateur)
                                @php
                                    $nomFormateur = trim(($formateur->prenom ?? '').' '.($formateur->name ?? ''));
                                @endphp
                                <option value="{{ $formateur->id }}" @selected((int) old('formateur_id') === (int) $formateur->id)>
                                    {{ $nomFormateur !== '' ? $nomFormateur : $formateur->email }}{{ $formateur->status ? '' : ' — inactif' }}
                                </option>
                            @endforeach
                        </select>
                        @if ($formateurs->isEmpty())
                            <p class="mt-1 text-xs font-medium text-amber-700">Aucun compte formateur n’est disponible.</p>
                        @endif
                        @error('formateur_id')
                            <p id="erreur-formateur" class="mt-1 text-xs font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <section
                x-data="{ rechercheStagiaire: '' }"
                class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-7"
                aria-labelledby="titre-affectations-stagiaires"
            >
                <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 id="titre-affectations-stagiaires" class="text-sm font-semibold text-slate-950">Stagiaires affectés</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $stagiaires->count() }} compte(s) disponible(s). La sélection peut rester vide.</p>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <label for="recherche-stagiaire" class="sr-only">Rechercher un stagiaire</label>
                        <i class="ti ti-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                        <input
                            id="recherche-stagiaire"
                            x-model="rechercheStagiaire"
                            type="search"
                            placeholder="Nom ou email"
                            class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-orangeone focus:ring-orangeone"
                        >
                    </div>
                </div>

                <div class="max-h-[28rem] overflow-y-auto p-3">
                    <div class="grid gap-2 sm:grid-cols-2">
                        @forelse ($stagiaires as $stagiaire)
                            @php
                                $nomStagiaire = trim(($stagiaire->prenom ?? '').' '.($stagiaire->name ?? ''));
                                $texteRecherche = Str::lower($nomStagiaire.' '.$stagiaire->email);
                            @endphp
                            <label
                                data-recherche="{{ $texteRecherche }}"
                                x-show="$el.dataset.recherche.includes(rechercheStagiaire.toLocaleLowerCase('fr'))"
                                class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 px-3 py-2.5 transition hover:border-sky-300 hover:bg-sky-50/50"
                            >
                                <input
                                    type="checkbox"
                                    name="stagiaires[]"
                                    value="{{ $stagiaire->id }}"
                                    class="mt-0.5 rounded border-slate-300 text-bleuone focus:ring-bleuone"
                                    @checked(in_array((int) $stagiaire->id, $stagiairesSelectionnes, true))
                                >
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $nomStagiaire !== '' ? $nomStagiaire : 'Stagiaire sans nom' }}</span>
                                    <span class="block truncate text-xs text-slate-500">{{ $stagiaire->email }}</span>
                                    @unless ($stagiaire->status)
                                        <span class="mt-1 inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Compte inactif</span>
                                    @endunless
                                </span>
                            </label>
                        @empty
                            <p class="col-span-full px-3 py-8 text-center text-sm text-slate-500">Aucun stagiaire disponible.</p>
                        @endforelse
                    </div>
                </div>

                @error('stagiaires')
                    <p class="border-t border-red-100 bg-red-50 px-4 py-2 text-xs font-medium text-red-700">{{ $message }}</p>
                @enderror
                @error('stagiaires.*')
                    <p class="border-t border-red-100 bg-red-50 px-4 py-2 text-xs font-medium text-red-700">{{ $message }}</p>
                @enderror
            </section>

            <footer class="flex flex-col-reverse gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-end lg:col-span-12">
                <a href="{{ route('admin.groupes') }}" class="btn-oneduc-outline inline-flex items-center justify-center !px-4 !py-2.5 !text-sm">
                    Annuler
                </a>
                <button type="submit" class="btn-oneduc inline-flex items-center justify-center gap-2 !px-4 !py-2.5 !text-sm" @disabled($formateurs->isEmpty())>
                    <i class="ti ti-check" aria-hidden="true"></i>
                    Créer le groupe
                </button>
            </footer>
        </form>
    </div>
@endsection
