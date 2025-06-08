@extends('admin.admin_dashboard')
@section('admin')


<!-- En-tête -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Gestion des stagiaires</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour suivre les apprenants et leurs parcours de formation
                </x-typography>
                <div class="prose-oneduc">
                    Vous pouvez ici consulter l’ensemble des <strong>stagiaires</strong>, visualiser leur formateur référent, et accéder à leurs groupes ou résultats.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <div class="flex items-center justify-between px-2 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Tous les stagiaires</h2>
        </div>

        <div class="overflow-x-auto mt-4">
            <table id="tableStagiaires" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Téléphone</th>
                        <th class="px-4 py-3">Formateur référent</th>
                        <th class="px-4 py-3 text-center">Activer</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($allStagiaires as $key => $stagiaire)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">{{ $stagiaire->name }}</td>
                            <td class="px-4 py-3">{{ $stagiaire->username }}</td>
                            <td class="px-4 py-3">{{ $stagiaire->email }}</td>
                            <td class="px-4 py-3">{{ $stagiaire->phone }}</td>
                            <td class="px-4 py-3">
                                {{ $stagiaire->formateur?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="relative inline-block">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only opacity-0 status-toggle"
                                        id="toggle-{{ $stagiaire->id }}"
                                        data-user-id="{{ $stagiaire->id }}"
                                        {{ $stagiaire->status ? 'checked' : '' }}
                                    />
                                    <label
                                        for="toggle-{{ $stagiaire->id }}"
                                        class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-400 px-0.5 transition-colors
                                               before:h-5 before:w-5 before:rounded-full before:bg-white before:shadow before:transition-transform
                                               before:duration-300 peer-checked:bg-green-500
                                               peer-checked:before:translate-x-full
                                               peer-focus-visible:outline
                                               peer-focus-visible:outline-offset-2
                                               peer-focus-visible:outline-gray-400
                                               peer-checked:peer-focus-visible:outline-green-500">
                                        <span class="sr-only" aria-label="Activer ou désactiver le stagiaire">Toggle stagiaire</span>
                                    </label>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
<script>
    $(document).ready(function () {
        $('#tableStagiaires').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false }
            ]
        });
    });
</script>
<script>
    $(document).ready(function(){
        $('.status-toggle').on('change', function(){
            const toggle = $(this);
            const userId = toggle.data('user-id');
            const isChecked = toggle.is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ route('admin.update.user.status') }}",
                method: "POST",
                data: {
                    user_id: userId,
                    is_checked: isChecked,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response){
                    toastr.success(response.message);
                },
                error: function(){
                    toastr.error("Erreur lors de la mise à jour du statut.");
                    toggle.prop('checked', !isChecked);
                }
            });
        });
    });
</script>

@endsection
