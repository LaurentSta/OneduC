{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/edit_module.blade.php --}}
@extends('admin.admin_dashboard')

@push('styles')
<style>
    /* Cartes de section (fond charte en transparence) */
    .form-card {
        background: rgba(0, 68, 97, 0.06);          /* #004461 */
        border: 1px solid rgba(0, 68, 97, 0.14);
        border-radius: 16px;
        padding: 18px;
    }
    .form-card--alt {
        background: rgba(233, 77, 42, 0.06);        /* #E94D2A (orangeone) */
        border: 1px solid rgba(233, 77, 42, 0.18);
    }

    .page-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #004461;
        margin-bottom: 6px;
    }
    .page-subtitle {
        color: rgba(0,0,0,.55);
        font-size: .95rem;
        line-height: 1.35;
    }

    .section-title {
        font-size: 1.05rem;  /* plus gros que text-lg standard */
        font-weight: 800;
        color: #004461;
        margin-bottom: 12px;
    }
    .section-title span {
        color: rgba(0,0,0,.45);
        font-weight: 700;
        margin-right: 8px;
    }

    /* Inputs utilitaires (tu avais déjà @apply en bas ; on garde le même comportement) */
    .input {
        @apply mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white;
    }
    .input-textarea {
        @apply mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white;
    }
    .checkbox {
        @apply rounded text-orangeone focus:ring-orangeone;
    }
    .file {
        @apply file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-orangeone file:text-white hover:file:opacity-90 cursor-pointer;
    }
</style>
@endpush

@section('admin')

@if (session('success'))
    <div class="max-w-6xl mx-auto mb-4 p-3 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="max-w-6xl mx-auto p-4 bg-red-100 border border-red-300 text-red-800 rounded mb-6">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="max-w-6xl mx-auto px-6 py-10 bg-white rounded-2xl shadow-md">
    <div class="mb-8">
        <h2 class="page-title">Modifier un module</h2>
        <p class="page-subtitle">
            Mets à jour les informations du module, ses médias, et ses objectifs.
        </p>
    </div>

    <form method="POST"
          action="{{ route('admin.modules.update', $module->id) }}"
          enctype="multipart/form-data"
          class="space-y-8">
        @csrf
        @method('PUT')

        {{-- 1. Informations générales --}}
        <section class="form-card">
            <h3 class="section-title"><span>1.</span>Informations générales</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-oneduc.input
                    label="Nom technique"
                    name="module_name"
                    :value="old('module_name', $module->module_name)"
                    required
                />

                <x-oneduc.input
                    label="Titre affiché"
                    name="module_title"
                    :value="old('module_title', $module->module_title)"
                    required
                />

                <x-oneduc.input
                    label="Slug"
                    name="module_name_slug"
                    :value="old('module_name_slug', $module->module_name_slug)"
                />

                <x-oneduc.input
                    label="Nom de la vidéo locale (MP4)"
                    name="module_video"
                    :value="old('module_video', $module->module_video)"
                    placeholder="Ex : intro_module1.mp4 ou dossier/intro.mp4"
                />

                <x-oneduc.input
                    label="Label (prix, etc.)"
                    name="label"
                    :value="old('label', $module->label)"
                />

                <x-oneduc.input
                    label="Durée"
                    name="duree"
                    :value="old('duree', $module->duree)"
                    placeholder="Ex : 2h, 3 jours"
                />
            </div>
        </section>

        {{-- 2. Catégorisation --}}
        <section class="form-card form-card--alt">
            <h3 class="section-title"><span>2.</span>Catégorisation</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label for="category_id" class="block text-base font-semibold text-gray-700 mb-1">
                        Catégorie
                    </label>
                    <select name="category_id" id="category_id" required class="input">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $cat->id == $module->category_id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="subcategory_id" class="block text-base font-semibold text-gray-700 mb-1">
                        Sous-catégorie
                    </label>
                    <select name="subcategory_id" id="subcategory_id" class="input">
                        @foreach ($subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ $sub->id == $module->subcategory_id ? 'selected' : '' }}>
                                {{ $sub->subcategory_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="evaluation_id" class="block text-base font-semibold text-gray-700 mb-1">
                        Évaluation finale (optionnelle)
                    </label>
                    <select name="evaluation_id" id="evaluation_id" class="input">
                        <option value="">Aucune évaluation</option>
                        @foreach ($evaluations as $eval)
                            <option value="{{ $eval->id }}" {{ old('evaluation_id', $module->evaluation_id) == $eval->id ? 'selected' : '' }}>
                                {{ $eval->titre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="formateur_id" class="block text-base font-semibold text-gray-700 mb-1">
                        Formateur
                    </label>
                    <select name="formateur_id" id="formateur_id" required class="input">
                        @foreach ($formateurs as $f)
                            <option value="{{ $f->id }}" {{ $f->id == $module->formateur_id ? 'selected' : '' }}>
                                {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="certificat" class="block text-base font-semibold text-gray-700 mb-1">
                        Certificat
                    </label>
                    <select name="certificat" id="certificat" required class="input">
                        <option value="1" {{ $module->certificat == 1 ? 'selected' : '' }}>Oui</option>
                        <option value="0" {{ $module->certificat == 0 ? 'selected' : '' }}>Non</option>
                    </select>
                </div>

            </div>
        </section>

        {{-- 3. Ressources & images --}}
        <section class="form-card">
            <h3 class="section-title"><span>3.</span>Ressources et images</h3>

            <div class="grid grid-cols-1 gap-6">
                <x-oneduc.input
                    label="Ressources (URL ou chemin)"
                    name="resources"
                    :value="old('resources', $module->resources)"
                    class="w-full"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-2">
                        Image d’en-tête
                    </label>
                    <input type="file" name="header_image" id="header_image" class="file" accept="image/*">
                    <img id="showHeaderImage"
                         src="{{ $module->header_image ? asset('storage/' . $module->header_image) : url('upload/category_images/NoImage.png') }}"
                         class="mt-3 w-48 h-48 object-cover rounded-lg shadow bg-white" />
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-2">
                        Image principale
                    </label>
                    <input type="file" name="module_image" id="module_image" class="file" accept="image/*">
                    <img id="showImage"
                         src="{{ $module->module_image ? asset('storage/' . $module->module_image) : url('upload/category_images/NoImage.png') }}"
                         class="mt-3 w-48 h-48 object-cover rounded-lg shadow bg-white" />
                </div>
            </div>
        </section>

        {{-- 4. Contenu pédagogique --}}
        <section class="form-card form-card--alt">
            <h3 class="section-title"><span>4.</span>Contenu pédagogique</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-oneduc.textarea
                    label="Prérequis"
                    name="prerequi"
                    :value="old('prerequi', $module->prerequi)"
                />

                <x-oneduc.textarea
                    label="Description"
                    name="description"
                    :value="old('description', $module->description)"
                />

                {{-- Objectifs pédagogiques --}}
                <div class="md:col-span-2">
                    <h4 class="text-base font-extrabold text-[#004461] mb-3">
                        Objectifs pédagogiques
                    </h4>

                    <div id="objectifs-container" class="space-y-3">
                        @php
                            $objectifs = is_array($module->objectifs)
                                ? $module->objectifs
                                : (json_decode($module->objectifs, true) ?? preg_split("/\r\n|\n|\r/", $module->objectifs));
                        @endphp

                        @foreach ($objectifs as $objectif)
                            <div class="flex items-center gap-3 objectif-item">
                                <input type="text" name="objectifs[]" class="input flex-1" value="{{ $objectif }}" placeholder="Saisir un objectif" />
                                <button type="button" class="remove-objectif text-red-600 hover:text-red-800 font-bold px-2" aria-label="Supprimer l’objectif">
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button"
                            id="add-objectif"
                            class="mt-4 inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-sm font-semibold px-4 py-2 rounded-lg">
                        Ajouter un objectif
                    </button>
                </div>
            </div>
        </section>

        {{-- 5. Options --}}
        <section class="form-card">
            <h3 class="section-title"><span>5.</span>Options</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-oneduc.checkbox label="Bestseller" name="bestseller" :checked="$module->bestseller" />
                <x-oneduc.checkbox label="Vedette" name="vedette" :checked="$module->vedette" />
                <x-oneduc.checkbox label="Valeur ajoutée" name="surevalue" :checked="$module->surevalue" />
                <x-oneduc.checkbox label="Actif" name="status" :checked="$module->status" />
            </div>
        </section>

        {{-- Boutons --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit"
                    class="bg-orangeone hover:opacity-90 text-white font-semibold px-6 py-2 rounded-lg shadow">
                Mettre à jour
            </button>
        </div>
    </form>
</div>

{{-- Scripts image preview --}}
<script>
    document.getElementById('header_image')?.addEventListener('change', function () {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('showHeaderImage').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });

    document.getElementById('module_image')?.addEventListener('change', function () {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('showImage').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>

<script>
document.getElementById('add-objectif')?.addEventListener('click', function () {
    const container = document.getElementById('objectifs-container');
    const item = document.createElement('div');
    item.classList.add('flex', 'items-center', 'gap-3', 'objectif-item');
    item.innerHTML = `
        <input type="text" name="objectifs[]" class="input flex-1" placeholder="Saisir un objectif" />
        <button type="button" class="remove-objectif text-red-600 hover:text-red-800 font-bold px-2" aria-label="Supprimer l’objectif">✕</button>
    `;
    container.appendChild(item);
});

// suppression
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-objectif')) {
        e.target.closest('.objectif-item')?.remove();
    }
});
</script>

@endsection
