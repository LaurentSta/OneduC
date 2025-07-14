@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-5xl mx-auto p-6 bg-white shadow rounded-lg">

    <h2 class="text-2xl font-bold mb-6 text-[#004461]">Ajouter une sous-catégorie</h2>

    <form method="POST" action="{{ route('admin.subcategories.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Catégorie parente -->
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">
                Catégorie parente <span class="text-red-600">*</span>
            </label>
            <select name="category_id"
                    id="category_id"
                    required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm">
                <option disabled selected>Choisissez une catégorie</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nom de la sous-catégorie -->
        <div>
            <label for="subcategory_name" class="block text-sm font-medium text-gray-700">
                Nom de la sous-catégorie <span class="text-red-600">*</span>
            </label>
            <input type="text"
                   id="subcategory_name"
                   name="subcategory_name"
                   value="{{ old('subcategory_name') }}"
                   placeholder="Ex : Smartphones"
                   required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm">
            @error('subcategory_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="subcategory_description" class="block text-sm font-medium text-gray-700">
                Description
            </label>
            <textarea id="subcategory_description"
                      name="subcategory_description"
                      rows="4"
                      placeholder="Ajoutez une description..."
                      class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm">{{ old('subcategory_description') }}</textarea>
            @error('subcategory_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Image + preview -->
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <!-- Aperçu -->
            <div class="flex-shrink-0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu de l'image :</label>
                <img id="preview-image"
                    src="{{ asset('upload/category_images/NoImage.png') }}"
                    alt="Aperçu de l'image"
                    class="rounded border border-gray-300 object-cover w-40 h-40">

            </div>

            <!-- Upload -->
            <div class="w-full">
                <label for="subcategory-image" class="block text-sm font-medium text-gray-700">
                    Image
                </label>
                <input type="file"
                       id="subcategory-image"
                       name="subcategory_image"
                       accept="image/*,.svg,.svg+xml"
                       class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-orangeone file:text-white hover:file:bg-orange-600 cursor-pointer" />
                @error('subcategory_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Boutons -->
        <div class="flex items-center gap-4 pt-4">
            <button type="submit"
                    class="inline-flex items-center px-5 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600 transition">
                <i class="ti ti-check mr-2"></i> Sauvegarder
            </button>
            <a href="{{ route('admin.subcategories.all') }}"
               class="inline-flex items-center px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-100 transition">
                <i class="ti ti-arrow-left mr-2"></i> Annuler
            </a>
        </div>
    </form>
</div>

<!-- Script d’aperçu de l’image -->
<script>
    document.getElementById('subcategory-image').addEventListener('change', function(event) {
        if (event.target.files && event.target.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    });
</script>

<!-- Toastr notifications -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.success('{{ session('success') }}', 'Succès', {
            closeButton: true,
            progressBar: true,
            timeOut: 5000
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.error('{{ session('error') }}', 'Erreur', {
            closeButton: true,
            progressBar: true,
            timeOut: 5000
        });
    });
</script>
@endif

@endsection
