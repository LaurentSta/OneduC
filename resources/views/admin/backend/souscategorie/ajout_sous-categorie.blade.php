{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/souscategorie/ajout_sous-categorie.blade.php --}}

@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        {{-- En-tête minimal --}}
        <div class="flex flex-col gap-2 border-b border-gray-100 pb-4 mb-6">
            <h1 class="text-[20px] font-varela text-bleuone">Ajouter une sous-catégorie</h1>
            <p class="text-sm text-gray-600">
                Associer une sous-catégorie à une catégorie parente et définir son image.
            </p>
        </div>

        <form method="POST"
              action="{{ route('admin.subcategories.store') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            {{-- Catégorie parente --}}
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">
                    Catégorie parente <span class="text-red-600">*</span>
                </label>

                <select name="category_id"
                        id="category_id"
                        required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                               focus:ring-orangeone focus:border-orangeone text-sm">
                    <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>
                        Choisissez une catégorie
                    </option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ (string)old('category_id') === (string)$category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>

                @error('category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nom --}}
            <div>
                <label for="subcategory_name" class="block text-sm font-medium text-gray-700">
                    Nom de la sous-catégorie <span class="text-red-600">*</span>
                </label>
                <input type="text"
                       id="subcategory_name"
                       name="subcategory_name"
                       value="{{ old('subcategory_name') }}"
                       placeholder="Ex : Excel"
                       required
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                              focus:ring-orangeone focus:border-orangeone text-sm">

                @error('subcategory_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="subcategory_description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea id="subcategory_description"
                          name="subcategory_description"
                          rows="4"
                          placeholder="Ajoutez une description…"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                                 focus:ring-orangeone focus:border-orangeone text-sm">{{ old('subcategory_description') }}</textarea>

                @error('subcategory_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Image (aperçu + upload) --}}
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-start">
                <div class="sm:col-span-3">
                    <p class="text-sm font-medium text-gray-700 mb-2">Aperçu</p>
                    <img id="preview-image"
     src="{{ asset('upload/category_images/NoImage.png') }}"
     alt="Aperçu de l'image"
     class="w-36 h-36 rounded-[16px] border border-gray-200 object-cover bg-gray-50">

                </div>

                <div class="sm:col-span-9">
                    <label for="subcategory-image" class="block text-sm font-medium text-gray-700">
                        Image
                    </label>
                    <input type="file"
                           id="subcategory-image"
                           name="subcategory_image"
                           accept="image/*,.svg,.svg+xml"
                           class="mt-1 block w-full text-sm text-gray-700 cursor-pointer
                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                  file:text-sm file:font-varela file:bg-orangeone file:text-white
                                  hover:file:bg-orangeone-hover" />

                    <p class="mt-2 text-xs text-gray-500">
                        Formats recommandés : PNG, JPG, SVG. Privilégier une image carrée.
                    </p>

                    @error('subcategory_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 border-t border-gray-100 mt-6 pt-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-orangeone text-white text-sm font-varela rounded-lg
                               hover:bg-orangeone-hover transition cursor-pointer">
                    <i class="ti ti-check"></i>
                    Sauvegarder
                </button>

                <a href="{{ route('admin.subcategories.all') }}"
                   class="inline-flex items-center gap-2 px-5 py-2 border border-bleuone/20 text-bleuone text-sm font-varela rounded-lg
                          hover:bg-bleuone hover:text-white transition cursor-pointer">
                    <i class="ti ti-arrow-left"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Aperçu image --}}
<script>
    document.getElementById('subcategory-image')?.addEventListener('change', function(event) {
        if (event.target.files && event.target.files.length > 0) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    });
</script>

{{-- Notifications (Toastr) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.success(@json(session('success')), 'Succès', {
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
        toastr.error(@json(session('error')), 'Erreur', {
            closeButton: true,
            progressBar: true,
            timeOut: 5000
        });
    });
</script>
@endif

@endsection
