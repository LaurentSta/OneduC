@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-6xl mx-auto px-6 py-10 bg-white rounded-2xl shadow-md">
    <h2 class="text-3xl font-bold text-gray-800 mb-10">Ajouter un nouveau module</h2>

    <form method="POST" action="{{ route('admin.modules.store') }}" enctype="multipart/form-data" class="space-y-12">
        @csrf

        {{-- 1. Informations générales --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">1. Informations générales</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-oneduc.input label="Nom technique" name="module_name" required />
                <x-oneduc.input label="Titre affiché" name="module_title" required />
                <x-oneduc.input label="Slug" name="module_name_slug" />
                <x-oneduc.input label="Vidéo locale (MP4)" name="module_video" placeholder="ex: intro_module1.mp4 ou dossier/nom.mp4" />

                <x-oneduc.input label="Label (prix, etc.)" name="label" />
                <x-oneduc.input label="Durée" name="duree" placeholder="Ex : 2h, 3 jours" />
            </div>
        </div>

        {{-- 2. Catégorisation --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">2. Catégorisation</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select name="category_id" id="category_id" required class="input">
                        <option value="">-- Choisir une catégorie --</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-1">Sous-catégorie</label>
                    <select name="subcategory_id" id="subcategory_id" class="input">
                        <option value="">-- Choisir une sous-catégorie --</option>
                        @foreach ($subcategories as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->subcategory_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="evaluation_id" class="block text-sm font-medium text-gray-700 mb-1">Évaluation finale (optionnelle)</label>
                    <select name="evaluation_id" id="evaluation_id" class="input">
                        <option value="">-- Aucune évaluation --</option>
                        @foreach ($evaluations as $eval)
                            <option value="{{ $eval->id }}" {{ old('evaluation_id') == $eval->id ? 'selected' : '' }}>{{ $eval->titre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="formateur_id" class="block text-sm font-medium text-gray-700 mb-1">Formateur</label>
                    <select name="formateur_id" id="formateur_id" required class="input">
                        <option value="">-- Choisir un formateur --</option>
                        @foreach ($formateurs as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="certificat" class="block text-sm font-medium text-gray-700 mb-1">Certificat</label>
                    <select name="certificat" id="certificat" required class="input">
                        <option value="">-- Avec certificat ? --</option>
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 3. Ressources & images --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">3. Ressources & images</h3>
            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                <x-oneduc.input label="Ressources (URL ou chemin)" name="resources" class="w-full" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <x-oneduc.file label="Image d’en-tête" name="header_image" preview="showHeaderImage" />
                <x-oneduc.file label="Image principale" name="module_image" preview="showImage" />
            </div>
        </div>

        {{-- 4. Contenu pédagogique --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">4. Contenu pédagogique</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-oneduc.textarea label="Prérequis" name="prerequi" />
                <x-oneduc.textarea label="Description" name="description" />
            </div>
        </div>
        <div>
            <h4 class="text-md font-semibold text-gray-700 mb-2">Objectifs pédagogiques</h4>
            <div id="objectifs-container" class="space-y-3">
                <div class="flex items-center gap-3 objectif-item">
                    <input type="text" name="objectifs[]" class="input flex-1" placeholder="Saisir un objectif" />
                    <button type="button" class="remove-objectif text-red-600 hover:text-red-800">✕</button>
                </div>
            </div>
            <button type="button" id="add-objectif" class="mt-3 bg-gray-200 hover:bg-gray-300 text-sm px-4 py-1 rounded">
                + Ajouter un objectif
            </button>
        </div>

        {{-- 5. Options --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">5. Options</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-oneduc.checkbox label="Bestseller" name="bestseller" />
                <x-oneduc.checkbox label="Vedette" name="vedette" />
                <x-oneduc.checkbox label="Valeur ajoutée" name="surevalue" />
                <x-oneduc.checkbox label="Actif" name="status" />
            </div>
        </div>

        {{-- Bouton --}}
        <div class="text-right pt-6">
            <button type="submit" class="bg-orangeone hover:bg-orange-600 text-white font-medium px-6 py-2 rounded shadow">
                <i class="ti ti-check mr-2"></i> Enregistrer
            </button>
        </div>
    </form>
</div>

<script>
    const previews = [
        { input: 'header_image', img: 'showHeaderImage' },
        { input: 'module_image', img: 'showImage' },
    ];
    previews.forEach(({ input, img }) => {
        const inputElem = document.getElementById(input);
        const imgElem = document.getElementById(img);
        if (inputElem && imgElem) {
            inputElem.addEventListener('change', function (e) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imgElem.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            });
        }
    });

    
</script>
document.addEventListener('click', function (e) {
    if (e.target.id === 'add-objectif') {
        const container = document.getElementById('objectifs-container');
        const item = document.createElement('div');
        item.classList.add('flex','items-center','gap-3','objectif-item');
        item.innerHTML = `
            <input type="text" name="objectifs[]" class="input flex-1" placeholder="Saisir un objectif" />
            <button type="button" class="remove-objectif text-red-600 hover:text-red-800">✕</button>
        `;
        container.appendChild(item);
    }
    if (e.target.classList.contains('remove-objectif')) {
        e.target.closest('.objectif-item').remove();
    }
});

<style>
    .input {
        @apply mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm;
    }
    .input-textarea {
        @apply mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm;
    }
    .checkbox {
        @apply rounded text-orangeone focus:ring-orangeone;
    }
    .file {
        @apply file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-orangeone file:text-white hover:file:bg-orange-600 cursor-pointer;
    }
</style>

@endsection
