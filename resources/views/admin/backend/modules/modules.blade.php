{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/modules.blade.php --}}
@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
  <div class="bg-white rounded-lg shadow-sm p-5 my-6 w-full border border-gray-300">

    {{-- En-tête Administratif --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between border-b-2 border-bleuone pb-3 mb-4">
      <div>
        <h1 class="text-lg font-bold text-bleuone uppercase tracking-tight">Catalogue des Modules</h1>
        <p class="text-gray-500 text-[11px] italic">Gestion administrative de l'offre de formation</p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.modules.add') }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 bg-orangeone text-white text-[11px] font-bold uppercase rounded hover:bg-orangeone-hover transition shadow-sm">
          <i class="ti ti-plus"></i>
          Nouveau module
        </a>
      </div>
    </div>

    {{-- Tableau de Gestion --}}
    <div class="overflow-x-auto border border-gray-300 rounded-sm">
      <table id="tableModules" class="w-full text-left border-collapse">
        {{-- En-tête --}}
        <thead class="bg-gray-200 text-[10px] uppercase font-bold text-gray-700 tracking-wide">
          <tr>
            <th class="px-2 py-2 border-r border-gray-300 w-8 text-center">#</th>
            <th class="px-3 py-2 border-r border-gray-300">Désignation</th>
            <th class="px-3 py-2 border-r border-gray-300 w-32">Catégorie</th>
            <th class="px-3 py-2 border-r border-gray-300 w-32">Formateur</th>
            <th class="px-2 py-2 border-r border-gray-300 text-center w-16">Sect.</th>
            <th class="px-2 py-2 border-r border-gray-300 text-center w-16">Leç.</th>
            <th class="px-2 py-2 border-r border-gray-300 text-center w-16">Quiz</th>
            <th class="px-2 py-2 border-r border-gray-300 text-center w-16">Img</th>
            <th class="px-2 py-2 border-r border-gray-300 text-center w-16">État</th>
            <th class="px-3 py-2 text-right w-48">Actions</th>
          </tr>
        </thead>

        <tbody class="text-[11px] text-gray-700">
          @forelse ($modules as $key => $module)
            <tr class="border-b border-gray-200 transition-colors">

              {{-- Index --}}
              <td class="px-2 py-1.5 border-r border-gray-200 text-center font-mono text-gray-400">
                {{ $key + 1 }}
              </td>

              {{-- Nom --}}
              <td class="px-3 py-1.5 border-r border-gray-200">
                <div class="font-bold text-bleuone truncate max-w-[250px]" title="{{ $module->module_name }}">
                    {{ $module->module_name }}
                </div>
                <div class="text-[9px] text-gray-400 italic">ID: {{ $module->id }}</div>
              </td>

              {{-- Catégorie --}}
              <td class="px-3 py-1.5 border-r border-gray-200">
                <span class="px-1.5 py-0.5 bg-white border border-gray-300 rounded text-[9px] font-bold uppercase text-gray-600 shadow-sm">
                    {{ optional($module->category)->category_name ?? '—' }}
                </span>
              </td>

              {{-- Formateur --}}
              <td class="px-3 py-1.5 border-r border-gray-200 font-medium text-gray-600 truncate">
                {{ optional($module->formateur)->name ?? '—' }}
              </td>

              {{-- Stats --}}
              <td class="px-2 py-1.5 border-r border-gray-200 text-center font-bold text-bleuone">
                {{ $module->sections_count ?? 0 }}
              </td>

              <td class="px-2 py-1.5 border-r border-gray-200 text-center font-bold text-orangeone">
                {{ $module->lectures_count ?? 0 }}
              </td>

              <td class="px-2 py-1.5 border-r border-gray-200 text-center font-bold text-vertone">
                {{ (int)($module->quiz_questions_planned ?? 0) }}
              </td>

              {{-- Image --}}
              <td class="px-2 py-1.5 border-r border-gray-200 text-center">
                <div class="flex justify-center">
                    <img src="{{ $module->module_image ? asset('storage/'.$module->module_image) : asset('upload/module_images/NoImage.png') }}"
                         class="h-6 w-6 rounded-sm border border-gray-300 object-cover bg-white"
                         loading="lazy">
                </div>
              </td>

              {{-- Toggle Actif --}}
              <td class="px-2 py-1.5 border-r border-gray-200 text-center">
                <form action="{{ route('admin.modules.toggle-status', $module->id) }}" method="POST" class="flex justify-center">
                  @csrf
                  @method('PATCH')
                  <button type="submit"
                          class="relative inline-flex items-center h-4 w-8 rounded-full transition-colors focus:outline-none
                                 {{ $module->status ? 'bg-vertone' : 'bg-gray-300' }}">
                    <span class="inline-block h-3 w-3 transform bg-white rounded-full transition-transform shadow-sm
                                 {{ $module->status ? 'translate-x-4' : 'translate-x-1' }}"></span>
                  </button>
                </form>
              </td>

              {{-- Actions --}}
              <td class="px-3 py-1.5 text-right">
                <div class="flex items-center justify-end gap-1">
                  
                  {{-- Config --}}
                  <a href="{{ route('admin.modules.edit', $module->id) }}"
                     class="group flex items-center gap-1 px-2 py-1 bg-white border border-gray-300 text-gray-600 rounded-sm hover:border-bleuone hover:text-bleuone transition text-[9px] font-bold uppercase shadow-sm"
                     title="Configuration">
                    <i class="ti ti-settings text-xs"></i>
                    <span class="hidden xl:inline">Config</span>
                  </a>

                  {{-- Contenu --}}
                  <a href="{{ route('admin.modules.lecture.add', $module->id) }}"
                     class="group flex items-center gap-1 px-2 py-1 bg-white border border-gray-300 text-gray-600 rounded-sm hover:bg-orangeone hover:border-orangeone hover:text-white transition text-[9px] font-bold uppercase shadow-sm"
                     title="Contenu Pédagogique">
                    <i class="ti ti-stack-2 text-xs"></i>
                    <span class="hidden xl:inline">Contenu</span>
                  </a>

                  {{-- Supprimer --}}
                  <form action="{{ route('admin.modules.delete', ['id' => $module->id]) }}" method="GET" class="delete-module-form">
                    <button type="submit" 
                            class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-sm transition"
                            title="Supprimer">
                      <i class="ti ti-trash text-xs"></i>
                    </button>
                  </form>

                </div>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-4 py-8 text-center text-gray-400 italic bg-gray-50 text-xs">
                  Aucun module trouvé dans le catalogue.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

{{-- Styles CSS pour forcer l'alternance --}}
<style>
    /* Force l'alternance des lignes (Gris 100) même avec DataTables */
    #tableModules tbody tr:nth-child(even) {
        background-color: #f3f4f6 !important; /* Tailwind gray-100 */
    }
    
    /* Force le blanc sur les lignes impaires */
    #tableModules tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
    }

    /* Effet de survol orangé léger */
    #tableModules tbody tr:hover {
        background-color: rgba(233, 77, 42, 0.08) !important; /* Orangeone tint */
    }

    /* Ajustements DataTables pour éviter les doubles bordures */
    table.dataTable { border-collapse: collapse !important; }
    table.dataTable thead th, table.dataTable thead td { border-bottom: 1px solid #d1d5db !important; }
    table.dataTable.no-footer { border-bottom: 1px solid #d1d5db !important; }
</style>

{{-- Scripts --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    $('#tableModules').DataTable({
      language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
      order: [[1,'asc']], 
      columnDefs: [
        { targets: [0,7,8,9], orderable: false }, 
        { targets: [4,5,6,7,8], className: 'dt-center' },
        { targets: [9], className: 'dt-right' }
      ],
      pageLength: 25,
      autoWidth: false,
      dom: '<"flex justify-between items-center mb-2 text-xs"f>rt<"flex justify-between items-center mt-2 text-xs"ip>'
    });

    @if(session('success')) toastr.success(@json(session('success'))); @endif
    @if(session('error')) toastr.error(@json(session('error'))); @endif

    document.querySelectorAll('.delete-module-form').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm("Action irréversible : Voulez-vous supprimer ce module ?")) {
          this.submit();
        }
      });
    });
  });
</script>

@endsection