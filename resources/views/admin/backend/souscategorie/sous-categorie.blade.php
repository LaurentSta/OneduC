@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        {{-- En-tête minimal (sans banderole) --}}
        <div class="flex flex-col gap-2 border-b border-gray-100 pb-4 mb-4">
            <h1 class="text-[20px] font-varela text-bleuone">Sous-catégories</h1>
            <p class="text-sm text-gray-600">
                Détaillez chaque thématique en sous-catégories plus précises.
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="text-sm text-gray-600">
                Total : <span class="font-semibold text-gray-900">{{ $subcategories->count() }}</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.categories.all') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-bleuone/20 text-bleuone text-sm font-varela rounded-lg
                          hover:bg-bleuone hover:text-white transition cursor-pointer">
                    <i class="ti ti-arrow-left"></i>
                    Retour aux catégories
                </a>

                <a href="{{ route('admin.subcategories.add') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg
                          hover:bg-orangeone-hover transition cursor-pointer">
                    <i class="ti ti-plus"></i>
                    Ajouter une sous-catégorie
                </a>
            </div>
        </div>

        {{-- Tableau (utilise le style global table-oneduc + DataTables) --}}
        <div class="overflow-x-auto">
            <table id="subcategoryTable" class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        {{-- Tu avais dit : nom avant catégorie parente --}}
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Catégorie parente</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tfoot class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Catégorie parente</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </tfoot>

                <tbody>
                    @forelse ($subcategories as $key => $subcategory)
                        <tr class="border-b border-gray-100 transition">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $key + 1 }}</td>

                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $subcategory->subcategory_name }}
                            </td>

                            <td class="px-4 py-3 text-gray-700">
                                {{ $subcategory->category->category_name ?? 'Catégorie supprimée' }}
                            </td>

                            <td class="px-4 py-3 max-w-[520px] truncate text-gray-600"
                                title="{{ $subcategory->subcategory_description }}">
                                {{ $subcategory->subcategory_description
                                    ? \Illuminate\Support\Str::limit($subcategory->subcategory_description, 80)
                                    : '-' }}
                            </td>

                            <td class="px-4 py-3">
                                <img src="{{ $subcategory->subcategory_image
                                    ? asset('storage/' . $subcategory->subcategory_image)
                                    : asset('upload/subcategory_images/NoImage.png') }}"
                                     alt="Image sous-catégorie {{ $subcategory->subcategory_name }}"
                                     class="h-10 w-10 rounded-full object-cover border border-gray-200">
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.subcategories.edit', $subcategory->id) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone
                                              hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                        <i class="ti ti-pencil"></i>
                                        Éditer
                                    </a>

                                    <button type="button"
                                            onclick="confirmDelete({{ $subcategory->id }})"
                                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700
                                                   hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer">
                                        <i class="ti ti-trash"></i>
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                Aucune sous-catégorie trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Suppression --}}
<script>
    function confirmDelete(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette sous-catégorie ? Cette action est irréversible.')) {
            window.location.href = "{{ url('/admin/sous-categories/delete/') }}/" + id;
        }
    }

    $(document).ready(function () {
        $('#subcategoryTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: 4, orderable: false }, // image
                { targets: 5, orderable: false }  // actions
            ]
        });
    });
</script>

{{-- Toastify --}}
@if(session('success'))
<script>
    window.addEventListener('DOMContentLoaded', () => {
        Toastify({
            text: @json(session('success')),
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#01c69c",
            close: true
        }).showToast();
    });
</script>
@endif

@endsection
