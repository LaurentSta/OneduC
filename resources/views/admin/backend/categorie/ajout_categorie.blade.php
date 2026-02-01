@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        {{-- En-tête minimal, cohérent avec le tableau --}}
        <div class="flex flex-col gap-2 border-b border-gray-100 pb-4 mb-6">
            <h1 class="text-[20px] font-varela text-bleuone">Ajouter une catégorie</h1>
            <p class="text-sm text-gray-600">
                Créer une nouvelle catégorie pour organiser les contenus de formation.
            </p>
        </div>

        <form method="POST"
              action="{{ route('admin.categories.store') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            {{-- Nom --}}
            <div>
                <label for="category_name" class="block text-sm font-medium text-gray-700">
                    Nom de la catégorie <span class="text-red-600">*</span>
                </label>
                <input type="text"
                       id="category_name"
                       name="category_name"
                       required
                       placeholder="Ex : Bureautique"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                              focus:ring-orangeone focus:border-orangeone text-sm">
                @error('category_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="category_description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea id="category_description"
                          name="category_description"
                          rows="4"
                          placeholder="Décris la catégorie en quelques lignes…"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm
                                 focus:ring-orangeone focus:border-orangeone text-sm"></textarea>
                @error('category_description')
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
                    <label for="category-image" class="block text-sm font-medium text-gray-700">
                        Image <span class="text-red-600">*</span>
                    </label>
                    <input type="file"
                           id="category-image"
                           name="category_image"
                           accept="image/*,.svg,.svg+xml"
                           required
                           class="mt-1 block w-full text-sm text-gray-700 cursor-pointer
                                  file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                  file:text-sm file:font-varela file:bg-orangeone file:text-white
                                  hover:file:bg-orangeone-hover" />
                    <p class="mt-2 text-xs text-gray-500">
                        Formats recommandés : PNG, JPG, SVG. Privilégier une image carrée.
                    </p>
                    @error('category_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100 mt-6 pt-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-orangeone text-white text-sm font-varela rounded-lg
                               hover:bg-orangeone-hover transition cursor-pointer">
                    <i class="ti ti-check"></i>
                    Sauvegarder
                </button>

                <a href="{{ route('admin.categories.all') }}"
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
    document.getElementById('category-image')?.addEventListener('change', function(event) {
        if (event.target.files && event.target.files.length > 0) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('preview-image').src = reader.result;
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
    document.addEventListener('DOMContentLoaded', function () {
        toastr.success(@json(session('success')), 'Succès', {
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
        toastr.error(@json(session('error')), 'Erreur', {
            closeButton: true,
            progressBar: true,
            timeOut: 4000
        });
    });
</script>
@endif

@endsection
