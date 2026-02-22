@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
      <div>
        <h1 class="text-[20px] font-varela text-bleuone">Évaluations finales</h1>
        <p class="text-sm text-gray-600">Gestion des évaluations SCORM de fin de module.</p>
      </div>
      <a href="{{ route('admin.evaluations.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
        <i class="ti ti-plus"></i>
        Ajouter
      </a>
    </div>

    @if($evaluations->count())
      <div class="overflow-x-auto">
        <table id="evaluationTable" class="table-oneduc w-full text-sm text-left text-gray-700">
          <thead class="text-xs uppercase">
            <tr>
              <th class="px-4 py-3">#</th>
              <th class="px-4 py-3">Titre</th>
              <th class="px-4 py-3">Fichier SCORM</th>
              <th class="px-4 py-3">Modules liés</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($evaluations as $key => $evaluation)
              @php
                $rel = \App\Support\LearningAssetPath::resolveEvaluationIndexRelativePath($evaluation->scorm_path);
                $scormUrl = $rel ? url($rel) : null;
              @endphp
              <tr class="border-b border-gray-100 transition">
                <td class="px-4 py-3">{{ $key + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $evaluation->titre }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2 flex-wrap">
                    @if($scormUrl)
                      <a href="{{ $scormUrl }}" target="_blank" rel="noopener" class="underline break-all text-bleuone">
                        /{{ $rel }}
                      </a>
                      <button type="button" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded border border-gray-300 hover:bg-gray-50" onclick="copyToClipboard('{{ $scormUrl }}')">
                        <i class="ti ti-copy"></i>
                        Copier
                      </button>
                    @else
                      <span class="text-gray-500">Aucun chemin configuré</span>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-200">
                    {{ $evaluation->modules_count ?? 0 }} module(s)
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="inline-flex items-center gap-2">
                    <a href="{{ route('admin.evaluations.edit', $evaluation->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                      <i class="ti ti-pencil"></i>
                      Éditer
                    </a>
                    <form action="{{ route('admin.evaluations.destroy', $evaluation) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer">
                        <i class="ti ti-trash"></i>
                        Supprimer
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="px-4 py-6 text-gray-600">Aucune évaluation enregistrée.</div>
    @endif
  </div>
</div>

<script>
  function copyToClipboard(text) { navigator.clipboard?.writeText(text); }

  $(function () {
    const table = document.getElementById('evaluationTable');
    if (table) {
      $('#evaluationTable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
        order: [[1, 'asc']],
        columnDefs: [
          { targets: 0, orderable: false, searchable: false },
          { targets: 2, orderable: false },
          { targets: 3, orderable: false, searchable: false },
          { targets: 4, orderable: false, searchable: false }
        ]
      });
    }
  });
</script>

@endsection
