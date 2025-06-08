@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-5xl mx-auto p-6 bg-white shadow rounded-lg">

    <h2 class="text-2xl font-bold mb-6 text-[#004461]">Ajouter une nouvelle catégorie</h2>

    <form method="POST" action="{{ route('store.category') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Nom de la catégorie -->
        <div>
            <label for="category_name" class="block text-sm font-medium text-gray-700">
                Nom de la catégorie <span class="text-red-600">*</span>
            </label>
            <input type="text"
                   id="category_name"
                   name="category_name"
                   required
                   placeholder="Ex : Technologie"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm">
            @error('category_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="category_description" class="block text-sm font-medium text-gray-700">
                Description de la catégorie
            </label>
            <textarea id="category_description"
                      name="category_description"
                      rows="4"
                      placeholder="Décris ta catégorie ici..."
                      class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"></textarea>
            @error('category_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Image Preview + Upload -->
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <!-- Aperçu -->
            <div class="flex-shrink-0">
                <img id="preview-image"
                     src="{{ asset('upload/category_images/NoImage.png') }}"
                     alt="Aperçu de l'image"
                     class="rounded border border-gray-300 object-cover w-40 h-40">
            </div>

            <!-- Upload -->
            <div class="w-full">
                <label for="category-image" class="block text-sm font-medium text-gray-700">
                    Image <span class="text-red-600">*</span>
                </label>
                <input type="file"
                       id="category-image"
                       name="category_image"
                       accept="image/*,.svg,.svg+xml"
                       required
                       class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-orangeone file:text-white hover:file:bg-orange-600 cursor-pointer" />
                @error('category_image')
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
            <button type="reset"
                    class="inline-flex items-center px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-100 transition">
                <i class="ti ti-refresh mr-2"></i> Annuler
            </button>
        </div>
    </form>

</div>

<!-- Script d’aperçu de l’image -->
<script>
    document.getElementById('category-image').addEventListener('change', function(event) {
        if (event.target.files.length > 0) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview-image').src = reader.result;
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
    document.addEventListener('DOMContentLoaded', function () {
        toastr.success('{{ session('success') }}', 'Succès', {
            closeButton: true,
            progressBar: true,
            timeOut: 4000
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        toastr.error('{{ session('error') }}', 'Erreur', {
            closeButton: true,
            progressBar: true,
            timeOut: 4000
        });
    });
</script>
@endif

@endsection
