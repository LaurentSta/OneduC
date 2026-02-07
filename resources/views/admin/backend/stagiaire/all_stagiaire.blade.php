@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Gestion des stagiaires</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour suivre les apprenants et leurs parcours de formation
                </x-typography>
                <div class="prose-oneduc">
                    Vous pouvez consulter les <strong>stagiaires</strong>, réinitialiser leur parcours en cas de problème technique, et gérer l’activation des comptes.
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Formateur</th>
                        {{-- AJOUT : Colonne Reset --}}
                        <th class="px-4 py-3 text-center">Progression</th> 
                        <th class="px-4 py-3 text-center">Activer</th>
                        <th class="px-4 py-3 text-center">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allStagiaires as $key => $stagiaire)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold">{{ $stagiaire->name }}</div>
                                <div class="text-xs text-gray-500">{{ $stagiaire->username }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $stagiaire->email }}</td>
                            <td class="px-4 py-3">
                                {{ $stagiaire->formateur?->name ?? '—' }}
                            </td>
                            
                            {{-- AJOUT : Bouton Reset --}}
                            <td class="px-4 py-3 text-center">
                                <button 
                                    type="button" 
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded bg-orange-100 text-orange-700 hover:bg-orange-200 border border-orange-200 transition btn-reset"
                                    data-user-id="{{ $stagiaire->id }}"
                                    data-user-name="{{ $stagiaire->name }}"
                                    title="Réinitialiser toute la progression (Quiz, SCORM, Vidéos)">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Reset
                                </button>
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
                                               peer-checked:before:translate-x-full">
                                        <span class="sr-only">Toggle stagiaire</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded bg-red-600 text-white hover:bg-red-700 btn-delete"
                                    data-user-id="{{ $stagiaire->id }}"
                                    data-user-name="{{ $stagiaire->name }}">
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

{{-- MODALE SUPPRESSION (Existante) --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
  <div class="absolute inset-0 bg-black/40" data-modal-dismiss></div>
  <div class="relative mx-auto mt-24 w-full max-w-md rounded-[16px] bg-white shadow-lg">
    <div class="p-6">
      <h3 class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
      <p class="mt-2 text-sm text-gray-700">Supprimer le stagiaire <span id="modalUserName" class="font-semibold"></span> ?</p>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50" data-modal-dismiss>Annuler</button>
        <button type="button" id="confirmDeleteBtn" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Supprimer</button>
      </div>
    </div>
  </div>
</div>

{{-- AJOUT : MODALE RESET PROGRESSION --}}
<div id="resetModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" data-reset-dismiss></div>
    <div class="relative mx-auto mt-24 w-full max-w-md rounded-[16px] bg-white shadow-lg border-t-4 border-orangeone">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-6 h-6 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Réinitialiser la progression ?
        </h3>
        <p class="mt-2 text-sm text-gray-700">
          Vous allez effacer toutes les données (Quiz, SCORM, Vidéos) de <span id="modalResetUserName" class="font-bold text-gray-900"></span>.
          <br><br>
          <span class="text-red-600 font-semibold">Cette action est irréversible.</span> Le stagiaire repartira de 0%.
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50" data-reset-dismiss>Annuler</button>
          {{-- Formulaire caché dans la modale --}}
          <form id="resetForm" method="POST" action="">
              @csrf
              <button type="submit" class="px-4 py-2 rounded bg-orangeone text-white hover:bg-orange-600 font-semibold shadow-sm">
                  Confirmer le Reset
              </button>
          </form>
        </div>
      </div>
    </div>
  </div>

<form id="deleteForm" method="POST" class="hidden">@csrf @method('DELETE')</form>

{{-- SCRIPTS --}}
<script>
    $(document).ready(function () {
        $('#tableStagiaires').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false },
                { targets: -2, orderable: false },
                { targets: -3, orderable: false } // Reset column
            ]
        });

        // --- GESTION SUPPRESSION ---
        const modal = document.getElementById('confirmModal');
        const nameSpan = document.getElementById('modalUserName');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const deleteForm = document.getElementById('deleteForm');
        const deleteRouteTemplate = "{{ route('admin.stagiaires.destroy', ['user' => '__ID__']) }}";

        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('user-id');
            let name = $(this).data('user-name');
            nameSpan.textContent = name;
            deleteForm.action = deleteRouteTemplate.replace('__ID__', id);
            modal.classList.remove('hidden');
        });

        $('[data-modal-dismiss]').on('click', () => modal.classList.add('hidden'));
        confirmBtn.addEventListener('click', () => deleteForm.submit());

        // --- AJOUT : GESTION RESET ---
        const resetModal = document.getElementById('resetModal');
        const resetNameSpan = document.getElementById('modalResetUserName');
        const resetForm = document.getElementById('resetForm');
        // Attention : créez cette route ensuite
        const resetRouteTemplate = "{{ url('admin/stagiaires') }}/" + "__ID__" + "/reset-progression";

        $(document).on('click', '.btn-reset', function() {
            let id = $(this).data('user-id');
            let name = $(this).data('user-name');
            resetNameSpan.textContent = name;
            // On construit l'URL : /admin/stagiaires/{id}/reset-progression
            resetForm.action = resetRouteTemplate.replace('__ID__', id);
            resetModal.classList.remove('hidden');
        });

        $('[data-reset-dismiss]').on('click', () => resetModal.classList.add('hidden'));

        // Switch Status (AJAX) - inchangé
        $('.status-toggle').on('change', function(){
            const toggle = $(this);
            $.ajax({
                url: "{{ route('admin.update.user.status') }}",
                method: "POST",
                data: {
                    user_id: toggle.data('user-id'),
                    is_checked: toggle.is(':checked') ? 1 : 0,
                    _token: "{{ csrf_token() }}"
                },
                success: (res) => toastr.success(res.message),
                error: () => {
                    toastr.error("Erreur");
                    toggle.prop('checked', !toggle.is(':checked'));
                }
            });
        });
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
<script>toastr.success('{{ session('success') }}');</script>
@endif
@endsection