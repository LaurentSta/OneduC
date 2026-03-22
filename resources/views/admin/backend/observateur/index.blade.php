@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Observateurs</h1>
                <p class="text-sm text-gray-600">Gestion des comptes observateurs, activation et rattachement aux groupes observés.</p>
            </div>
            <a href="{{ route('admin.observateurs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                <i class="ti ti-plus"></i>
                Ajouter un observateur
            </a>
        </div>

        <div class="overflow-x-auto">
            <table id="tableObservateurs" class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Groupes observés</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-center">Activer</th>
                        <th class="px-4 py-3 text-center">Modifier</th>
                        <th class="px-4 py-3 text-center">Supprimer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($observateurs as $key => $observateur)
                        <tr class="border-b border-gray-100 transition">
                            <td class="px-4 py-3">{{ $key + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ trim(($observateur->prenom ?? '').' '.($observateur->name ?? '')) }}</div>
                                <div class="text-xs text-gray-500">{{ $observateur->username }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $observateur->email }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($observateur->groupesObserve as $group)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                            {{ $group->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Aucun groupe</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if ($observateur->status)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">Actif</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="relative inline-block">
                                    <input type="checkbox" class="peer sr-only opacity-0 status-toggle" id="toggle-observateur-{{ $observateur->id }}" data-user-id="{{ $observateur->id }}" {{ $observateur->status ? 'checked' : '' }} />
                                    <label for="toggle-observateur-{{ $observateur->id }}" class="relative flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-400 px-0.5 transition-colors before:h-5 before:w-5 before:rounded-full before:bg-white before:shadow before:transition-transform before:duration-300 peer-checked:bg-green-500 peer-checked:before:translate-x-full">
                                        <span class="sr-only">Activer ou désactiver</span>
                                    </label>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin.observateurs.edit', $observateur) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-600 hover:text-white transition text-xs font-varela">
                                    <i class="ti ti-edit"></i>
                                    Modifier
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer btn-delete" data-user-id="{{ $observateur->id }}" data-user-name="{{ trim(($observateur->prenom ?? '').' '.($observateur->name ?? '')) }}">
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
      <p class="mt-2 text-sm text-gray-700">Supprimer l’observateur <span id="modalUserName" class="font-semibold"></span> ?</p>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" class="px-4 py-2 rounded border hover:bg-gray-50" data-modal-dismiss>Annuler</button>
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
        $('#tableObservateurs').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: 0, orderable: false },
                { targets: -1, orderable: false },
                { targets: -2, orderable: false },
                { targets: -3, orderable: false }
            ]
        });

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

<script>
  (function(){
    const modal = document.getElementById('confirmModal');
    const nameSpan = document.getElementById('modalUserName');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const form = document.getElementById('deleteForm');
    const routeTemplate = "{{ route('admin.observateurs.destroy', ['user' => '__ID__']) }}";

    document.querySelectorAll('.btn-delete').forEach(btn => {
      btn.addEventListener('click', () => {
        nameSpan.textContent = btn.dataset.userName || '';
        form.setAttribute('action', routeTemplate.replace('__ID__', btn.dataset.userId));
        modal.classList.remove('hidden');
      });
    });

    modal.querySelectorAll('[data-modal-dismiss]').forEach(el => {
      el.addEventListener('click', () => modal.classList.add('hidden'));
    });

    confirmBtn.addEventListener('click', () => form.submit());
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
