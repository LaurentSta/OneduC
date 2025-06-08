@extends('admin.admin_dashboard')
@section('admin')

<!-- En-tête explicative -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Gestion des modules</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour structurer les parcours de formation
                </x-typography>
                <div class="prose-oneduc">
                    Les <strong>modules</strong> sont les briques de base de chaque formation. Ils peuvent contenir plusieurs sections et leçons. Ce tableau vous permet de les gérer efficacement.
                </div>
            </div>
            <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Carte tableau -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <div class="flex items-center justify-between px-2 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Tous les modules</h2>
            <a href="{{ route('admin.modules.add') }}" class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                <i class="ti ti-plus mr-1"></i> Ajouter un module
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table id="tableModules" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Sections</th>
                        <th class="px-4 py-3">Lectures</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($modules as $key => $module)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">{{ $module->module_name }}</td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $module->description }}">{{ $module->description }}</td>
                            <td class="px-4 py-3">{{ $module->sections->count() }}</td>
                            <td class="px-4 py-3">{{ $module->lectures->count() }}</td>
                            <td class="px-4 py-3">
                                <img src="{{ $module->module_image ? asset('storage/' . $module->module_image) : asset('upload/module_images/NoImage.png') }}" alt="Image module" class="h-10 w-10 rounded-full object-cover">
                            </td>
                            <!-- Actions -->
<td class="px-4 py-3 text-center w-56">
    <div class="flex justify-center gap-3 flex-wrap text-white text-sm">

        <!-- Éditer -->
        <a href="{{ route('admin.modules.edit', $module->id) }}"
           class="bg-bleuone p-2 rounded-full hover:bg-bleuone/90 transition"
           title="Éditer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15.232 5.232l3.536 3.536M9 11l3 3L20.485 5.515a2.121 2.121 0 00-3-3L9 11zM5 19h14" />
            </svg>
        </a>

        <!-- Contenu -->
        <a href="{{ route('admin.modules.lecture.add', $module->id) }}"
           class="bg-vertone p-2 rounded-full hover:bg-vertone/90 transition"
           title="Contenu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
        </a>

        <!-- Supprimer -->
        <a href="{{ route('admin.modules.delete', ['id' => $module->id]) }}"
           onclick="return confirm('Supprimer ce module ?')"
           class="bg-orangeone p-2 rounded-full hover:bg-orangeone/90 transition"
           title="Supprimer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>

    </div>
</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">Aucun module trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toastr -->
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

<!-- DataTables -->
<script>
    $(document).ready(function () {
        $('#tableModules').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false }
            ]
        });
    });
</script>

@endsection
