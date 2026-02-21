@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Stagiaires</h1>
                <p class="text-sm text-gray-600">Suivi des apprenants, activation des comptes et réinitialisation des progressions.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="tableStagiaires" class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Formateur</th>
                        <th class="px-4 py-3 text-center">Progression</th>
                        <th class="px-4 py-3 text-center">Activer</th>
                        <th class="px-4 py-3 text-center">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allStagiaires as $key => $stagiaire)
                        <tr class="border-b border-gray-100 transition">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $stagiaire->name }}</div>
                                <div class="text-xs text-gray-500">{{ $stagiaire->username }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $stagiaire->email }}</td>
                            <td class="px-4 py-3">{{ $stagiaire->formateur?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-orange-200 text-orange-700 hover:bg-orangeone hover:text-white hover:border-orangeone transition text-xs font-varela cursor-pointer btn-reset" data-user-id="{{ $stagiaire->id }}" data-user-name="{{ $stagiaire->name }}" title="Réinitialiser toute la progression (Quiz, SCORM, Vidéos)">
                                    <i class="ti ti-refresh"></i>
                                    Reset
                                </button>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="relative inline-block">
                                    <input type="checkbox" class="peer sr-only opacity-0 status-toggle" id="toggle-{{ $stagiaire->id }}" data-user-id="{{ $stagiaire->id }}" {{ $stagiaire->status ? 'checked' : '' }} />
                                    <label for="toggle-{{ $stagiaire->id }}" class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-400 px-0.5 transition-colors before:h-5 before:w-5 before:rounded-full before:bg-white before:shadow before:transition-transform before:duration-300 peer-checked:bg-green-500 peer-checked:before:translate-x-full">
                                        <span class="sr-only">Toggle stagiaire</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer btn-delete" data-user-id="{{ $stagiaire->id }}" data-user-name="{{ $stagiaire->name }}">
                                    <i class="ti ti-trash"></i>
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

<div id="resetModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" data-reset-dismiss></div>
    <div class="relative mx-auto mt-24 w-full max-w-md rounded-[16px] bg-white shadow-lg border-t-4 border-orangeone">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <i class="ti ti-alert-triangle text-orangeone"></i>
            Réinitialiser la progression ?
        </h3>
        <p class="mt-2 text-sm text-gray-700">
          Vous allez effacer toutes les données (Quiz, SCORM, Vidéos) de <span id="modalResetUserName" class="font-bold text-gray-900"></span>.
          <br><br>
          <span class="text-red-600 font-semibold">Cette action est irréversible.</span> Le stagiaire repartira de 0%.
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50" data-reset-dismiss>Annuler</button>
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

<script>
    $(document).ready(function () {
        $('#tableStagiaires').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false },
                { targets: -2, orderable: false },
                { targets: -3, orderable: false }
            ]
        });

        const modal = document.getElementById('confirmModal');
        const nameSpan = document.getElementById('modalUserName');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const deleteForm = document.getElementById('deleteForm');
        const deleteRouteTemplate = "{{ route('admin.stagiaires.destroy', ['user' => '__ID__']) }}";

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('user-id');
            const name = $(this).data('user-name');
            nameSpan.textContent = name;
            deleteForm.action = deleteRouteTemplate.replace('__ID__', id);
            modal.classList.remove('hidden');
        });

        $('[data-modal-dismiss]').on('click', () => modal.classList.add('hidden'));
        confirmBtn.addEventListener('click', () => deleteForm.submit());

        const resetModal = document.getElementById('resetModal');
        const resetNameSpan = document.getElementById('modalResetUserName');
        const resetForm = document.getElementById('resetForm');
        const resetRouteTemplate = "{{ url('admin/stagiaires') }}/" + "__ID__" + "/reset-progression";

        $(document).on('click', '.btn-reset', function() {
            const id = $(this).data('user-id');
            const name = $(this).data('user-name');
            resetNameSpan.textContent = name;
            resetForm.action = resetRouteTemplate.replace('__ID__', id);
            resetModal.classList.remove('hidden');
        });

        $('[data-reset-dismiss]').on('click', () => resetModal.classList.add('hidden'));

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
