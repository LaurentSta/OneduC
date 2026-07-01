@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Groupes</h1>
                <p class="text-sm text-gray-600">Organisation des stagiaires par groupe, formateur et parcours.</p>
            </div>
            <a href="{{ route('admin.groupes.add') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                <i class="ti ti-plus"></i>
                Ajouter un groupe
            </a>
        </div>

        <div class="overflow-x-auto">
            <table id="tableGroupes" class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
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
                        <tr class="border-b border-gray-100 transition">
                            <td class="px-4 py-3">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $groupe->name }}</td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $groupe->description }}">{{ Str::limit($groupe->description, 70) }}</td>
                            <td class="px-4 py-3">{{ $groupe->instructor->name ?? 'Non assigné' }}</td>
                            <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">{{ $groupe->students->count() }}</span></td>
                            <td class="px-4 py-3">
                                <img src="{{ $groupe->groupe_image ? asset('storage/' . $groupe->groupe_image) : asset('upload/groupe_images/NoImage.png') }}" alt="Image groupe" class="h-10 w-10 rounded-full object-cover border border-gray-200">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.groupes.edit', $groupe->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                        <i class="ti ti-pencil"></i>
                                        Éditer
                                    </a>
                                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-groupe-{{ $groupe->id }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer">
                                        <i class="ti ti-trash"></i>
                                        Supprimer
                                    </button>
                                    <x-confirm-modal
                                        name="delete-groupe-{{ $groupe->id }}"
                                        title="Supprimer ce groupe ?"
                                        :action="route('admin.groupes.delete', $groupe->id)"
                                        method="DELETE"
                                        confirm-label="Supprimer"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Aucun groupe trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#tableGroupes').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
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
