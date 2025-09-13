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
                    Vous pouvez consulter les <strong>stagiaires</strong>, voir leur formateur référent et gérer l’activation des comptes.
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
                        <th class="px-4 py-3 text-center">Supprimer</th>
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
                            <td class="px-4 py-3 text-center">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded bg-red-600 text-white hover:bg-red-700 btn-delete"
                                    data-user-id="{{ $stagiaire->id }}"
                                    data-user-name="{{ $stagiaire->name }}"
                                    aria-haspopup="dialog"
                                >
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modale de confirmation -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="absolute inset-0 bg-black/40" data-modal-dismiss></div>
  <div class="relative mx-auto mt-24 w-full max-w-md rounded-[16px] bg-white shadow-lg">
    <div class="p-6">
      <h3 id="modal-title" class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
      <p class="mt-2 text-sm text-gray-700">
        Supprimer le stagiaire <span id="modalUserName" class="font-semibold"></span> ?
        L’accès sera désactivé et le compte masqué.
      </p>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50" data-modal-dismiss>Annuler</button>
        <button type="button" id="confirmDeleteBtn" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Supprimer</button>
      </div>
    </div>
  </div>
</div>

<!-- Formulaire de suppression -->
<form id="deleteForm" method="POST" class="hidden">
  @csrf
  @method('DELETE')
</form>

<!-- DataTables -->
<script>
    $(document).ready(function () {
        $('#tableStagiaires').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false }, // Supprimer
                { targets: -2, orderable: false }  // Activer
            ]
        });
    });
</script>

<!-- Switch AJAX -->
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

<!-- Suppression: ouverture modale + submit -->
<script>
  (function(){
    const modal = document.getElementById('confirmModal');
    const nameSpan = document.getElementById('modalUserName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const form = document.getElementById('deleteForm');

    const routeTemplate = "{{ route('admin.stagiaires.destroy', ['user' => '__ID__']) }}";
    let pendingId = null;

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', () => {
        pendingId = btn.dataset.userId;
        nameSpan.textContent = btn.dataset.userName || '';
        form.setAttribute('action', routeTemplate.replace('__ID__', pendingId));
        modal.classList.remove('hidden');
        setTimeout(() => confirmBtn.focus(), 50);
      });
    });

    modal.querySelectorAll('[data-modal-dismiss]').forEach(el => {
      el.addEventListener('click', () => modal.classList.add('hidden'));
    });

    confirmBtn.addEventListener('click', () => form.submit());
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') modal.classList.add('hidden'); });
  })();
</script>

<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.success('{{ session('success') }}', 'Succès', {
            closeButton: true, progressBar: true, timeOut: 5000
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.error('{{ session('error') }}', 'Erreur', {
            closeButton: true, progressBar: true, timeOut: 5000
        });
    });
</script>
@endif

@endsection
