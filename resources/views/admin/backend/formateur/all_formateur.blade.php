@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Formateurs</h1>
                <p class="text-sm text-gray-600">Gestion des comptes formateurs, activation et suivi des stagiaires.</p>
            </div>
            <a href="{{ route('formateur.inscription.form') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                <i class="ti ti-plus"></i>
                Ajouter un formateur
            </a>
        </div>

        <div class="overflow-x-auto">
            <table id="tableFormateurs" class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Téléphone</th>
                        <th class="px-4 py-3">Stagiaires</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-center">Activer</th>
                        <th class="px-4 py-3">Adhésion</th>
                        <th class="px-4 py-3 text-center">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allFormateur as $key => $item)
                        @php
                            $adhesionIsValid = $item->hasValidAssociationMembership();
                            $adhesionHasExpired = $item->adhesion_status === 'active'
                                && $item->adhesion_valid_until
                                && $item->adhesion_valid_until->lt(today());
                            $adhesionGraceEndsAt = $item->associationGraceEndsAt();
                            $adhesionGraceIsActive = $item->hasActiveAssociationGracePeriod();
                            $adhesionDate = $item->adhesion_valid_until?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d');
                        @endphp
                        <tr class="border-b border-gray-100 transition">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item->name }}</td>
                            <td class="px-4 py-3">{{ $item->username }}</td>
                            <td class="px-4 py-3">{{ $item->email }}</td>
                            <td class="px-4 py-3">{{ $item->phone }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                    {{ $item->stagiaires_count }} stagiaires
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($item->status == 1)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">Actif</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="relative inline-block">
                                    <input type="checkbox" class="peer sr-only opacity-0 status-toggle" id="toggle-{{ $item->id }}" data-user-id="{{ $item->id }}" {{ $item->status ? 'checked' : '' }} />
                                    <label for="toggle-{{ $item->id }}" class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-400 px-0.5 transition-colors before:h-5 before:w-5 before:rounded-full before:bg-white before:shadow before:transition-transform before:duration-300 peer-checked:bg-green-500 peer-checked:before:translate-x-full">
                                        <span class="sr-only">Activer ou désactiver</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-4 py-3 min-w-[280px]">
                                <div class="space-y-3">
                                    @if ($adhesionIsValid)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-700 text-xs font-bold">
                                            Adhésion validée
                                            @if ($item->adhesion_valid_until)
                                                jusqu'au {{ $item->adhesion_valid_until->format('d/m/Y') }}
                                            @endif
                                        </span>
                                    @elseif ($item->adhesion_status === 'expired' || $adhesionHasExpired)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold">Adhésion expirée</span>
                                    @elseif ($adhesionGraceIsActive)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                            Accès découverte jusqu'au {{ $adhesionGraceEndsAt->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">
                                            Adhésion à relancer
                                        </span>
                                        <p class="mt-2 text-xs leading-relaxed text-slate-500">
                                            Vous pouvez couper l'accès avec le bouton d'activation.
                                        </p>
                                    @endif

                                    <form method="POST" action="{{ route('admin.formateurs.adhesion.update', $item) }}" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="adhesion_status" value="active">
                                        <input type="date"
                                               name="adhesion_valid_until"
                                               value="{{ $adhesionDate }}"
                                               class="h-9 rounded-lg border border-slate-300 px-2 text-xs text-slate-700 focus:border-orangeone focus:ring-orangeone/20">
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-orangeone px-3 py-2 text-xs font-varela font-semibold text-white transition hover:bg-orangeone-hover">
                                            <i class="ti ti-check"></i>
                                            Valider
                                        </button>
                                    </form>

                                    @if ($item->adhesion_status !== 'pending')
                                        <form method="POST" action="{{ route('admin.formateurs.adhesion.update', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="adhesion_status" value="pending">
                                            <button type="submit" class="text-xs font-varela font-semibold text-slate-500 underline-offset-4 hover:text-red-700 hover:underline">
                                                Remettre en attente
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer btn-delete" data-user-id="{{ $item->id }}" data-user-name="{{ $item->name }}" aria-haspopup="dialog">
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

<form id="deleteForm" method="POST" class="hidden">
  @csrf
  @method('DELETE')
</form>

<script>
    $(document).ready(function () {
        $('#tableFormateurs').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false },
                { targets: -2, orderable: false },
                { targets: -3, orderable: false }
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
                success: function(response){ toastr.success(response.message); },
                error: function(){
                    toastr.error("Une erreur est survenue lors de la mise à jour du statut.");
                    toggle.prop('checked', !isChecked);
                }
            });
        });
    });
</script>

<script>
  (function(){
    const modal = document.getElementById('confirmModal');
    const nameSpan = document.getElementById('modalUserName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const form = document.getElementById('deleteForm');

    const routeTemplate = "{{ route('admin.formateurs.destroy', ['user' => '__ID__']) }}";

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', () => {
        const pendingId = btn.dataset.userId;
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.success('{{ session('success') }}', 'Succès', { closeButton: true, progressBar: true, timeOut: 5000 });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        toastr.error('{{ session('error') }}', 'Erreur', { closeButton: true, progressBar: true, timeOut: 5000 });
    });
</script>
@endif

@endsection
