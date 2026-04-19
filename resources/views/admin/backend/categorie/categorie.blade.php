@extends('admin.admin_dashboard')
@section('admin')




<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        {{-- En-tête minimal (sans banderole) --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Catégories</h1>
                <p class="text-sm text-gray-600">
                    Organiser les contenus de formation par thématiques et accéder aux sous-catégories.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.subcategories.all') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-orangeone text-orangeone text-sm font-varela rounded-lg hover:bg-orangeone hover:text-white transition cursor-pointer">
                    <i class="ti ti-list-details"></i>
                    Voir les sous-catégories
                </a>

                <a href="{{ route('admin.categories.ajout') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                    <i class="ti ti-plus"></i>
                    Ajouter une catégorie
                </a>
            </div>
        </div>

        {{-- Tableau --}}
        <div class="overflow-x-auto">
            <table id="categoryTable"
       class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-center">Sous-catégories</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tfoot class="text-xs uppercase">

                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-center">Sous-catégories</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </tfoot>

                <tbody>
                    @forelse ($categories as $key => $category)
                        {{-- Remplace uniquement le <tr> du @forelse par ce bloc (fond pastel différent par ligne) --}}



<tr class="border-b border-gray-100 transition">
    <td class="px-4 py-3 whitespace-nowrap">{{ $key + 1 }}</td>
    <td class="px-4 py-3 font-medium text-gray-900">
        {{ $category->category_name }}
    </td>
    <td class="px-4 py-3 max-w-[520px] truncate text-gray-600"
        title="{{ $category->category_description }}">
        {{ $category->category_description }}
    </td>
    <td class="px-4 py-3">
        <img src="{{ $category->category_image_url }}"
             alt="Image catégorie {{ $category->category_name }}"
             class="h-10 w-10 rounded-full object-cover border border-gray-200">
    </td>
    <td class="px-4 py-3 text-center">
        <a href="{{ route('admin.subcategories.all', ['category_id' => $category->id]) }}"
           class="inline-flex items-center justify-center min-w-[44px] px-3 py-1 rounded-full bg-orangeone/10 text-orangeone font-semibold hover:bg-orangeone hover:text-white transition cursor-pointer">
            {{ $category->subcategories_count }}
        </a>
    </td>
    <td class="px-4 py-3 text-right">
        <div class="inline-flex items-center gap-2">
            <a href="{{ route('admin.categories.edit', $category->id) }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                <i class="ti ti-pencil"></i>
                Éditer
            </a>

            <button type="button"
                    onclick="confirmDelete({{ $category->id }})"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer">
                <i class="ti ti-trash"></i>
                Supprimer
            </button>
        </div>
    </td>
</tr>

                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                Aucune catégorie trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette catégorie ?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ url('/admin/categories') }}/" + id;
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
                           + '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    $(document).ready(function () {
        $('#categoryTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: 3, orderable: false },
                { targets: 4, orderable: false },
                { targets: 5, orderable: false }
            ]
        });

        @if(session('success'))
        Toastify({
            text: "{{ session('success') }}",
            duration: 4000,
            gravity: "top",
            position: "right",
            backgroundColor: "#01c69c",
            close: true,
            style: { fontSize: "16px", borderRadius: "10px" }
        }).showToast();
        @endif
    });
</script>

@endsection
