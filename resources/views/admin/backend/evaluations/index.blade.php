@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">

            {{-- Colonne texte --}}
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Évaluations finales</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour évaluer les compétences des stagiaires en fin de module
                </x-typography>
                <div class="prose-oneduc">
                    Chaque <strong>évaluation</strong> est un fichier SCORM intégré au module de formation. Une seule évaluation par module est possible.
                    Ces évaluations peuvent être créées une fois puis réutilisées pour d’autres modules si besoin.
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
            <h2 class="text-xl font-semibold text-gray-800">Liste des évaluations SCORM</h2>
            <a href="{{ route('admin.evaluations.create') }}"
               class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                <i class="ti ti-plus mr-1"></i> Ajouter une évaluation
            </a>
        </div>

        <!-- ⬇️ Partie tableau -->
        <div class="overflow-x-auto">
            <table id="evaluationTable" class="w-full text-sm text-left text-gray-700">
    <thead class="text-xs text-gray-600 uppercase bg-gray-100">
        <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Titre</th>
            <th class="px-4 py-3">Fichier SCORM</th>
            <th class="px-4 py-3">Modules liés</th>
            <th class="px-4 py-3 text-center">Actions</th>
        </tr>
    </thead>

    <tfoot class="text-xs text-gray-600 uppercase bg-gray-100">
        <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Titre</th>
            <th class="px-4 py-3">Fichier SCORM</th>
            <th class="px-4 py-3">Modules liés</th>
            <th class="px-4 py-3 text-center">Actions</th>
        </tr>
    </tfoot>

    <tbody>
    @forelse ($evaluations as $key => $evaluation)
        <tr class="border-b hover:bg-gray-50 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
            <td class="px-4 py-3">{{ $key + 1 }}</td>
            <td class="px-4 py-3">{{ $evaluation->titre }}</td>
            <td class="px-4 py-3">{{ $evaluation->scorm_path }}</td>
            <td class="px-4 py-3">{{ $evaluation->modules_count ?? 0 }} module(s)</td>
            <td class="px-4 py-3 text-center w-48">
                <div class="flex justify-center gap-2">
                    <a href="{{ route('admin.evaluations.edit', $evaluation->id) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                        <i class="ti ti-pencil mr-1"></i> Éditer
                    </a>
                    <button onclick="confirmDelete({{ $evaluation->id }})"
                            class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                        <i class="ti ti-trash mr-1"></i> Supprimer
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-4 py-3 text-center text-gray-500">Aucune évaluation enregistrée.</td>
        </tr>
    @endforelse
</tbody>


</table>

        </div>

    </div>
</div>

<!-- JS -->
<script>
    function confirmDelete(id) {
        if (confirm("⚠️ Êtes-vous sûr de vouloir supprimer cette évaluation ?")) {
            window.location.href = "{{ route('admin.evaluations.delete', ':id') }}".replace(':id', id);
        }
    }

    $(function () {
    $('#evaluationTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        },
        order: [[1, 'asc']],
        columnDefs: [
            { targets: 0, orderable: false },
            { targets: 2, orderable: false },
            { targets: 3, orderable: false },
            { targets: 4, orderable: false } // ⬅️ ajoute ceci pour "Actions"
        ]
    });
});


</script>

@endsection
