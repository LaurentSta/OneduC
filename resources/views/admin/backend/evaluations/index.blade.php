@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-[1248px] mx-auto px-4">
  <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <x-typography variant="titre">Évaluations finales</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          …pour évaluer les compétences des stagiaires en fin de module
        </x-typography>
        <div class="prose-oneduc">
          Chaque <strong>évaluation</strong> est un paquet SCORM intégré à un module. Une seule évaluation par module.
          Fichiers dans <code>/public/modules/scorm/01_evaluations/</code>.
        </div>
      </div>
      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <div class="w-full max-w-xs" role="img" aria-label="Aide sur les évaluations">
          {!! file_get_contents(public_path('images/svg/PointDInterrogation.svg')) !!}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="max-w-[1248px] mx-auto px-4">
  <div class="bg-white rounded-[20px] shadow-md my-10 w-full overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h2 class="text-xl font-semibold text-gray-800">Liste des évaluations SCORM</h2>
      <a href="{{ route('admin.evaluations.create') }}"
         class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600"
         aria-label="Ajouter une évaluation SCORM">
        <i class="ti ti-plus mr-1"></i> Ajouter
      </a>
    </div>

    @if($evaluations->count())
      <div class="overflow-x-auto p-6">
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
          <tbody>
            @foreach ($evaluations as $key => $evaluation)
              @php
                $scormUrl = url('modules/scorm/01_evaluations/'.$evaluation->scorm_path.'/res/index.html');
              @endphp
              <tr class="border-b hover:bg-gray-50 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                <td class="px-4 py-3">{{ $key + 1 }}</td>
                <td class="px-4 py-3">{{ $evaluation->titre }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ $scormUrl }}" target="_blank" rel="noopener" class="underline break-all">
                      /modules/scorm/01_evaluations/{{ $evaluation->scorm_path }}/res/index.html
                    </a>
                    <button type="button"
                            class="px-2 py-1 text-xs bg-gray-200 rounded hover:bg-gray-300"
                            onclick="copyToClipboard('{{ $scormUrl }}')">
                      Copier
                    </button>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $evaluation->modules_count ?? 0 }} module(s)
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex justify-center gap-2">
                    <a href="{{ route('admin.evaluations.edit', $evaluation->id) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700">
                      <i class="ti ti-pencil mr-1"></i> Éditer
                    </a>
                    <form action="{{ route('admin.evaluations.destroy', $evaluation) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700">
                        <i class="ti ti-trash mr-1"></i> Supprimer
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
      <div class="p-8">
        <p class="text-gray-600">Aucune évaluation enregistrée.</p>
      </div>
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
