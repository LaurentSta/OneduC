@extends('admin.admin_dashboard')
@section('admin')
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            {{-- Colonne texte --}}
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Sous-catégories</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour détailler chaque grande thématique
                </x-typography>
                <div class="prose-oneduc">
                    Les <strong>sous-catégories</strong> permettent de découper chaque grande thématique en sujets plus spécifiques.
                    Par exemple, la catégorie <em>Bureautique</em> pourra être déclinée en sous-catégories comme <strong>Word</strong>, <strong>Excel</strong> ou <strong>PowerPoint</strong>.
                </div>
            </div>

            {{-- Colonne image --}}
            <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
                </div>
            </div>

        </div>
    </div>
</div>


<div class="max-w-7xl mx-auto p-4">

    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Toutes les sous-catégories</h2>
            <a href="{{ route('admin.subcategories.add') }}"
               class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                <i class="ti ti-plus mr-1"></i> Ajouter une sous-catégorie
            </a>
        </div>

        <!-- Tableau -->
        <div class="overflow-x-auto p-4">
            <table id="subcategoryTable" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Catégorie parente</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-center w-48">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subcategories as $key => $subcategory)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">{{ $subcategory->category->category_name ?? 'Catégorie supprimée' }}</td>
                            <td class="px-4 py-3">{{ $subcategory->subcategory_name }}</td>
                            <td class="px-4 py-3">
                                {{ $subcategory->subcategory_description
                                    ? \Illuminate\Support\Str::limit($subcategory->subcategory_description, 50)
                                    : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <img src="{{ $subcategory->subcategory_image
                                    ? asset('storage/' . $subcategory->subcategory_image)
                                    : asset('upload/subcategory_images/NoImage.png') }}"
                                     alt="Image sous-catégorie"
                                     class="h-10 w-10 rounded-full object-cover">
                            </td>
                            <td class="px-4 py-3 text-center w-48">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.subcategories.edit', $subcategory->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition">
                                        <i class="ti ti-pencil mr-1"></i> Éditer
                                    </a>
                                    <button onclick="confirmDelete({{ $subcategory->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition">
                                        <i class="ti ti-trash mr-1"></i> Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-center text-gray-500">Aucune sous-catégorie trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Suppression -->
<script>
    function confirmDelete(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette sous-catégorie ? Cette action est irréversible.')) {
            window.location.href = "{{ url('/admin/sous-categories/delete/') }}/" + id;
        }
    }
</script>

<!-- Toastify -->
@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Toastify({
            text: "{{ session('success') }}",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745",
            close: true
        }).showToast();
    });
</script>
@endif

@endsection
