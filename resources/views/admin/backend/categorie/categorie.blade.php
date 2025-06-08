@extends('admin.admin_dashboard')
@section('admin')



<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            {{-- Colonne texte --}}
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Catégories et sous-catégories</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour structurer les contenus de formation
                </x-typography>
                <div class="prose-oneduc">
                    Les <strong>catégories</strong> regroupent les grandes thématiques (ex. : bureautique, citoyenneté, informatique).
                    Chaque catégorie peut contenir plusieurs <strong>sous-catégories</strong> plus précises, pour organiser les modules de façon claire et logique.
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
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <!-- ✅ Bouton Ajouter -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Toutes les catégories</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.subcategories.all') }}"
                   class="inline-flex items-center px-4 py-2 border border-orangeone text-orangeone text-sm font-medium rounded hover:bg-orange-50">
                    <i class="ti ti-list-details mr-1"></i> Voir les sous-catégories
                </a>
                <a href="{{ route('admin.categories.ajout') }}"
                   class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                    <i class="ti ti-plus mr-1"></i> Ajouter une catégorie
                </a>
            </div>
        </div>

        <!-- ⬇️ Partie tableau -->
        <div class="overflow-x-auto">
            <table id="categoryTable" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom de la catégorie</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">Sous-catégories</th>

                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tfoot class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom de la catégorie</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">Sous-catégories</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                    @forelse ($categories as $key => $category)
                        <tr class="border-b hover:bg-gray-50 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">{{ $category->category_name }}</td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $category->category_description }}">
                                {{ $category->category_description }}
                            </td>

                            <td class="px-4 py-3">
                                <img src="{{ $category->category_image ? asset('storage/' . $category->category_image) : asset('upload/category_images/NoImage.png') }}"
                                     alt="Image catégorie"
                                     class="h-10 w-10 rounded-full object-cover">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.subcategories.all', ['category_id' => $category->id]) }}"
                                   class="text-orangeone font-semibold hover:underline">
                                    {{ $category->subcategories_count }}
                                </a>
                            </td>



                            <td class="px-4 py-3 text-center w-48">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition">
                                        <i class="ti ti-pencil mr-1"></i> Éditer
                                    </a>
                                    <button onclick="confirmDelete({{ $category->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition">
                                        <i class="ti ti-trash mr-1"></i> Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-center text-gray-500">Aucune catégorie trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>


<!-- JS unique -->
<script>
    function confirmDelete(id) {
        if (confirm("⚠️ Êtes-vous sûr de vouloir supprimer cette catégorie ?")) {
            window.location.href = "{{ route('admin.categories.delete', ':id') }}".replace(':id', id);
        }
    }

    $(document).ready(function () {
        $('#categoryTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: 3, orderable: false },
                { targets: 4, orderable: false }
            ]
        });

        @if(session('success'))
        Toastify({
            text: "{{ session('success') }}",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745",
            close: true,
            style: {
                fontSize: "16px",
                borderRadius: "8px",
            }
        }).showToast();
        @endif
    });
</script>

@endsection
