@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Référentiels de compétences</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour structurer l’évaluation des acquis
                </x-typography>
                <div class="prose-oneduc">
                    Un <strong>référentiel</strong> organise les compétences et leurs indicateurs d’acquisition.
                    Il sera sélectionné lors de la création d’un groupe afin de suivre les stagiaires dans un cadre cohérent.
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

<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">

        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Tous les référentiels</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.referentiels.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                    <i class="ti ti-plus mr-1"></i> Ajouter un référentiel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="referentialTable" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
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
                    <tr class="border-b hover:bg-gray-50 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-4 py-3">{{ $key + 1 }}</td>
                        <td class="px-4 py-3">{{ $ref->name }}</td>
                        <td class="px-4 py-3">{{ $ref->code }}</td>
                        <td class="px-4 py-3 max-w-xs truncate" title="{{ $ref->description }}">
                            {{ $ref->description ?? '—' }}
                        </td>

                        {{-- Compteurs --}}
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">
                            {{ $ref->domains_count ?? 0 }}
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">
                            {{ $ref->skills_count ?? 0 }}
                        </td>

                        <td class="px-4 py-3">
                            @if($ref->status)
                                <span class="text-green-700 font-semibold">Actif</span>
                            @else
                                <span class="text-gray-700 font-semibold">Inactif</span>
                            @endif
                        </td>

                        {{-- Menu burger --}}
                        <td class="px-4 py-3 text-center w-32">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button type="button"
                                        @click="open = !open"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-300 hover:bg-gray-50"
                                        aria-haspopup="true"
                                        :aria-expanded="open.toString()"
                                        aria-label="Ouvrir le menu du référentiel">
                                    {{-- Burger sans icônes externes --}}
                                    <span class="block w-5">
                                        <span class="block h-0.5 bg-gray-700 mb-1"></span>
                                        <span class="block h-0.5 bg-gray-700 mb-1"></span>
                                        <span class="block h-0.5 bg-gray-700"></span>
                                    </span>
                                </button>

                                <div x-show="open"
                                    @click.outside="open = false"
                                    x-transition
                                    class="absolute right-0 mt-2 w-56 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 z-50"
                                    role="menu">
                                    <div class="py-2 text-sm text-gray-700">

                                        <a href="{{ route('admin.referentiels.edit', $ref->id) }}"
                                        class="block px-4 py-2 hover:bg-gray-50"
                                        role="menuitem">
                                            Modifier le référentiel
                                        </a>

                                        <a href="{{ route('admin.referentiels.domains.index', $ref) }}"
                                        class="block px-4 py-2 hover:bg-gray-50"
                                        role="menuitem">
                                            Gérer les domaines
                                        </a>

                                        <a href="{{ route('admin.referentiels.skills.index', $ref) }}"
                                        class="block px-4 py-2 hover:bg-gray-50"
                                        role="menuitem">
                                            Gérer les compétences
                                        </a>

                                        <div class="my-2 border-t"></div>

                                        <form id="delete-form-{{ $ref->id }}"
                                            action="{{ route('admin.referentiels.destroy', $ref->id) }}"
                                            method="POST" class="px-4">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="confirmDelete({{ $ref->id }})"
                                                    class="w-full text-left px-0 py-2 text-red-700 hover:text-red-800"
                                                    role="menuitem">
                                                Supprimer
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr class="border-b">
                        <td class="px-4 py-3 text-center text-gray-500">—</td>
                        <td class="px-4 py-3 text-center text-gray-500">Aucun référentiel trouvé.</td>
                        <td class="px-4 py-3 text-center text-gray-500">—</td>
                        <td class="px-4 py-3 text-center text-gray-500">—</td>
                        <td class="px-4 py-3 text-center text-gray-500">—</td>
                        <td class="px-4 py-3 text-center text-gray-500">—</td>
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
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: 5, orderable: false }
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
