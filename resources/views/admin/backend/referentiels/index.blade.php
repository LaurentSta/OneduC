@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Référentiels de compétences</h1>
                <p class="text-sm text-gray-600">Structuration des domaines et compétences par référentiel.</p>
            </div>
            <a href="{{ route('admin.referentiels.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                <i class="ti ti-plus"></i>
                Ajouter un référentiel
            </a>
        </div>

        <div class="overflow-x-auto">
            <table id="referentialTable" class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-center">Domaines</th>
                    <th class="px-4 py-3 text-center">Compétences</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3 text-center">Menu</th>
                </tr>
                </thead>

                <tbody>
                @forelse ($referentiels as $key => $ref)
                    <tr class="border-b border-gray-100 transition">
                        <td class="px-4 py-3">{{ $key + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $ref->name }}</td>
                        <td class="px-4 py-3">{{ $ref->code }}</td>
                        <td class="px-4 py-3 max-w-xs truncate" title="{{ $ref->description }}">{{ $ref->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $ref->domains_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $ref->skills_count ?? 0 }}</td>
                        <td class="px-4 py-3">
                            @if($ref->status)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">Actif</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Inactif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center w-32">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button type="button" @click="open = !open" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="Ouvrir le menu du référentiel">
                                    <span class="block w-5">
                                        <span class="block h-0.5 bg-gray-700 mb-1"></span>
                                        <span class="block h-0.5 bg-gray-700 mb-1"></span>
                                        <span class="block h-0.5 bg-gray-700"></span>
                                    </span>
                                </button>

                                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 z-50" role="menu">
                                    <div class="py-2 text-sm text-gray-700">
                                        <a href="{{ route('admin.referentiels.edit', $ref->id) }}" class="block px-4 py-2 hover:bg-gray-50" role="menuitem">Modifier le référentiel</a>
                                        <a href="{{ route('admin.referentiels.domains.index', $ref) }}" class="block px-4 py-2 hover:bg-gray-50" role="menuitem">Gérer les domaines</a>
                                        <a href="{{ route('admin.referentiels.skills.index', $ref) }}" class="block px-4 py-2 hover:bg-gray-50" role="menuitem">Gérer les compétences</a>
                                        <div class="my-2 border-t"></div>
                                        <form id="delete-form-{{ $ref->id }}" action="{{ route('admin.referentiels.destroy', $ref->id) }}" method="POST" class="px-4">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete({{ $ref->id }})" class="w-full text-left px-0 py-2 text-red-700 hover:text-red-800" role="menuitem">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-gray-100">
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">Aucun référentiel trouvé.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce référentiel ?")) {
            document.getElementById('delete-form-' + id).submit();
        }
    }

    $(document).ready(function () {
        const hasRows = $('#referentialTable tbody tr').length > 0;
        if (!hasRows) return;

        $('#referentialTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: 5, orderable: false },
                { targets: 7, orderable: false }
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
            style: { fontSize: "16px", borderRadius: "8px" }
        }).showToast();
        @endif
    });
</script>

@endsection
