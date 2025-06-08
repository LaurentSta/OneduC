@extends('admin.admin_dashboard')
@section('admin')

<!-- En-tête explicative -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Gestion des groupes</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour organiser les stagiaires par niveau, lieu ou parcours
                </x-typography>
                <div class="prose-oneduc">
                    Les <strong>groupes</strong> permettent de répartir les stagiaires en fonction de leur formateur, de leur niveau ou des objectifs du parcours. Depuis ce tableau, vous pouvez consulter les groupes existants, voir les participants et effectuer des modifications.
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

<!-- Tableau des groupes -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <div class="flex items-center justify-between px-2 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Tous les groupes</h2>
            <a href="{{ route('admin.groupes.add') }}" class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                <i class="ti ti-plus mr-1"></i> Ajouter un groupe
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table id="tableGroupes" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Formateur</th>
                        <th class="px-4 py-3">Nb. stagiaires</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupes as $index => $groupe)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">{{ $groupe->name }}</td>
                            <td class="px-4 py-3">{{ Str::limit($groupe->description, 50) }}</td>
                            <td class="px-4 py-3">{{ $groupe->instructor->name ?? 'Non assigné' }}</td>
                            <td class="px-4 py-3">{{ $groupe->students->count() }}</td>
                            <td class="px-4 py-3">
                                <img src="{{ $groupe->groupe_image ? asset('storage/' . $groupe->groupe_image) : asset('upload/groupe_images/NoImage.png') }}"
                                     alt="Image groupe"
                                     class="h-10 w-10 rounded-full object-cover">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('admin.groupes.edit', $groupe->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                                        <i class="ti ti-pencil mr-1"></i> Éditer
                                    </a>
                                    <form action="{{ route('admin.groupes.delete', $groupe->id) }}" method="POST" onsubmit="return confirm('Supprimer ce groupe ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                                            <i class="ti ti-trash mr-1"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-center text-gray-500">Aucun groupe trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script DataTables -->
<script>
    $(document).ready(function () {
        $('#tableGroupes').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false },
                { targets: 5, orderable: false }
            ]
        });
    });
</script>

@endsection
