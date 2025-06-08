@extends('admin.admin_dashboard')
@section('admin')

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<div class="max-w-7xl mx-auto p-4">

    <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Toutes les catégories</h2>
            <a href="{{ route('admin.categories.add') }}" class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                <i class="ti ti-plus mr-1"></i> Ajouter une catégorie
            </a>
        </div>

        <!-- Tableau -->
        <div class="overflow-x-auto p-4">
            <table id="categoryTable" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom de la catégorie</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $key => $category)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">{{ $category->category_name }}</td>
                            <td class="px-4 py-3">{{ $category->category_description }}</td>
                            <td class="px-4 py-3">
                                <img src="{{ $category->category_image ? asset('storage/' . $category->category_image) : asset('upload/category_images/NoImage.png') }}"
                                     alt="Image catégorie"
                                     class="h-10 w-10 rounded-full object-cover">
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

<!-- Supprimer confirmation -->
<script>
    function confirmDelete(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Cette action est irréversible.')) {
            window.location.href = "{{ url('/delete/category/') }}/" + id;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        $('#categoryTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            }
        });
    });
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
            close: true,
            style: {
                fontSize: "16px",
                borderRadius: "8px",
            }
        }).showToast();
    });
</script>
@endif

@endsection
