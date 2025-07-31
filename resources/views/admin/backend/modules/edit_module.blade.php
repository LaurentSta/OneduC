@extends('admin.admin_dashboard')
@section('admin')

@if (session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="max-w-5xl mx-auto p-4 bg-red-100 border border-red-300 text-red-800 rounded mb-6">
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="max-w-6xl mx-auto px-6 py-10 bg-white rounded-2xl shadow-md">
    <h2 class="text-3xl font-bold text-gray-800 mb-10">Modifier un module</h2>

    <form method="POST" action="{{ route('admin.modules.update', $module->id) }}" enctype="multipart/form-data" class="space-y-12">
        @csrf
        @method('PUT')

        {{-- 1. Informations générales --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">1. Informations générales</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-oneduc.input label="Nom technique" name="module_name" :value="old('module_name', $module->module_name)" required />
                <x-oneduc.input label="Titre affiché" name="module_title" :value="old('module_title', $module->module_title)" required />
                <x-oneduc.input label="Slug" name="module_name_slug" :value="old('module_name_slug', $module->module_name_slug)" />
                <x-oneduc.input
                    label="Nom de la vidéo locale (MP4)"
                    name="module_video"
                    :value="old('module_video', $module->module_video)"
                    placeholder="ex: intro_module1.mp4 ou dossier/intro.mp4"
                />

                <x-oneduc.input label="Label (prix, etc.)" name="label" :value="old('label', $module->label)" />
                <x-oneduc.input label="Durée" name="duree" :value="old('duree', $module->duree)" placeholder="Ex : 2h, 3 jours" />
            </div>
        </div>

        {{-- 2. Catégorisation --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">2. Catégorisation</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select name="category_id" id="category_id" required class="input">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $cat->id == $module->category_id ? 'selected' : '' }}>{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-1">Sous-catégorie</label>
                    <select name="subcategory_id" id="subcategory_id" class="input">
                        @foreach ($subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ $sub->id == $module->subcategory_id ? 'selected' : '' }}>{{ $sub->subcategory_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="evaluation_id" class="block text-sm font-medium text-gray-700 mb-1">Évaluation finale (optionnelle)</label>
                    <select name="evaluation_id" id="evaluation_id" class="input">
                        <option value="">-- Aucune évaluation --</option>
                        @foreach ($evaluations as $eval)
                            <option value="{{ $eval->id }}" {{ old('evaluation_id', $module->evaluation_id) == $eval->id ? 'selected' : '' }}>
    {{ $eval->titre }}
</option>




                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="formateur_id" class="block text-sm font-medium text-gray-700 mb-1">Formateur</label>
                    <select name="formateur_id" id="formateur_id" required class="input">
                        @foreach ($formateurs as $f)
                            <option value="{{ $f->id }}" {{ $f->id == $module->formateur_id ? 'selected' : '' }}>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="certificat" class="block text-sm font-medium text-gray-700 mb-1">Certificat</label>
                    <select name="certificat" id="certificat" required class="input">
                        <option value="1" {{ $module->certificat == 1 ? 'selected' : '' }}>Oui</option>
                        <option value="0" {{ $module->certificat == 0 ? 'selected' : '' }}>Non</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 3. Ressources & images --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">3. Ressources & images</h3>
            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                <x-oneduc.input label="Ressources (URL ou chemin)" name="resources" :value="old('resources', $module->resources)" class="w-full" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Image d’en-tête</label>
                    <input type="file" name="header_image" id="header_image" class="file" accept="image/*">
                    <img id="showHeaderImage" src="{{ $module->header_image ? asset('storage/' . $module->header_image) : url('upload/category_images/NoImage.png') }}" class="mt-3 w-48 h-48 object-cover rounded shadow" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Image principale</label>
                    <input type="file" name="module_image" id="module_image" class="file" accept="image/*">
                    <img id="showImage" src="{{ $module->module_image ? asset('storage/' . $module->module_image) : url('upload/category_images/NoImage.png') }}" class="mt-3 w-48 h-48 object-cover rounded shadow" />
                </div>
            </div>
        </div>

        {{-- 4. Contenu pédagogique --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">4. Contenu pédagogique</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-oneduc.textarea label="Prérequis" name="prerequi" :value="old('prerequi', $module->prerequi)" />
                    <x-oneduc.textarea label="Description" name="description" :value="old('description', $module->description)" />

            </div>
        </div>

        {{-- 5. Options --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-700 mb-4">5. Options</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-oneduc.checkbox label="Bestseller" name="bestseller" :checked="$module->bestseller" />
                <x-oneduc.checkbox label="Vedette" name="vedette" :checked="$module->vedette" />
                <x-oneduc.checkbox label="Valeur ajoutée" name="surevalue" :checked="$module->surevalue" />
                <x-oneduc.checkbox label="Actif" name="status" :checked="$module->status" />
            </div>
        </div>

        {{-- Bouton --}}
        <div class="text-right pt-6">
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium px-6 py-2 rounded shadow">
                <i class="ti ti-edit mr-2"></i> Mettre à jour
            </button>
        </div>
    </form>
</div>

<!-- Scripts image preview -->
<script>
    document.getElementById('header_image').addEventListener('change', function (e) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('showHeaderImage').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });

    document.getElementById('module_image').addEventListener('change', function (e) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('showImage').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });
</script>

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
