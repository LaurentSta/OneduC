@extends('admin.admin_dashboard')
@section('admin')

<!-- En-tête explicative -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-9">
                <x-typography variant="titre">Gestion des formateurs</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …pour piloter l’encadrement pédagogique
                </x-typography>
                <div class="prose-oneduc">
                    Les <strong>formateurs</strong> accompagnent les stagiaires dans leurs parcours de formation.
                    Depuis ce tableau, vous pouvez activer/désactiver un compte, accéder aux stagiaires et ajouter de nouveaux profils.
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
            <h2 class="text-xl font-semibold text-gray-800">Tous les formateurs</h2>
            <a href="{{ route('formateur.inscription.form') }}" class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
                <i class="ti ti-plus mr-1"></i> Ajouter un formateur
            </a>
        </div>

        <div class="overflow-x-auto mt-4">
            <table id="tableFormateurs" class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-600 uppercase bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Téléphone</th>
                        <th class="px-4 py-3">Stagiaires</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-center">Activer</th>
                        <th class="px-4 py-3 text-center">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allFormateur as $key => $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">{{ $item->name }}</td>
                            <td class="px-4 py-3">{{ $item->username }}</td>
                            <td class="px-4 py-3">{{ $item->email }}</td>
                            <td class="px-4 py-3">{{ $item->phone }}</td>
                            <td class="px-4 py-3">
                                <span class="text-blue-600 font-semibold">
                                    {{ $item->stagiaires_count }} stagiaires
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->status == 1)
                                    <span class="inline-block px-3 py-1 text-sm text-white bg-green-600 rounded">Actif</span>
                                @else
                                    <span class="inline-block px-3 py-1 text-sm text-white bg-red-600 rounded">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="relative inline-block">
                                    <input
                                        type="checkbox"
                                        class="peer sr-only opacity-0 status-toggle"
                                        id="toggle-{{ $item->id }}"
                                        data-user-id="{{ $item->id }}"
                                        {{ $item->status ? 'checked' : '' }}
                                    />
                                    <label
                                        for="toggle-{{ $item->id }}"
                                        class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-400 px-0.5 transition-colors
                                               before:h-5 before:w-5 before:rounded-full before:bg-white before:shadow before:transition-transform
                                               before:duration-300 peer-checked:bg-green-500
                                               peer-checked:before:translate-x-full
                                               peer-focus-visible:outline
                                               peer-focus-visible:outline-offset-2
                                               peer-focus-visible:outline-gray-400
                                               peer-checked:peer-focus-visible:outline-green-500">
                                        <span class="sr-only">Activer ou désactiver</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded bg-red-600 text-white hover:bg-red-700 btn-delete"
                                    data-user-id="{{ $item->id }}"
                                    data-user-name="{{ $item->name }}"
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
        Supprimer le formateur <span id="modalUserName" class="font-semibold"></span> ?
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
        $('#tableFormateurs').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
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
                    toastr.error("Une erreur est survenue lors de la mise à jour du statut.");
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

    const routeTemplate = "{{ route('admin.formateurs.destroy', ['user' => '__ID__']) }}";
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

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') modal.classList.add('hidden');
    });
  })();
</script>

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

@endsection
