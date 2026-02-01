{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/modules.blade.php --}}

@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

    {{-- En-tête minimal (sans banderole) --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-6">
      <div>
        <h1 class="text-[20px] font-varela text-bleuone">Modules</h1>
        <p class="text-sm text-gray-600">
          Créer, organiser et gérer les modules (sections, leçons, quiz).
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.modules.add') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
          <i class="ti ti-plus"></i>
          Ajouter un module
        </a>
      </div>
    </div>

    {{-- Tableau --}}
    <div class="overflow-x-auto">
      <table id="tableModules" class="table-oneduc w-full text-sm text-left text-gray-700">
        <thead class="text-xs uppercase">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Nom</th>
            <th class="px-4 py-3">Catégorie</th>
            <th class="px-4 py-3">Formateur</th>
            <th class="px-4 py-3 text-center">Sections</th>
            <th class="px-4 py-3 text-center">Leçons</th>
            <th class="px-4 py-3 text-center">Questions</th>
            <th class="px-4 py-3">Image</th>
            <th class="px-4 py-3 text-center">Actif</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>

        <tfoot class="text-xs uppercase">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Nom</th>
            <th class="px-4 py-3">Catégorie</th>
            <th class="px-4 py-3">Formateur</th>
            <th class="px-4 py-3 text-center">Sections</th>
            <th class="px-4 py-3 text-center">Leçons</th>
            <th class="px-4 py-3 text-center">Questions</th>
            <th class="px-4 py-3">Image</th>
            <th class="px-4 py-3 text-center">Actif</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </tfoot>

        <tbody>
          @forelse ($modules as $key => $module)
            <tr class="border-b border-gray-100">

              <td class="px-4 py-3 whitespace-nowrap">{{ $key + 1 }}</td>

              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ $module->module_name }}</div>
                <div class="text-gray-600 text-xs line-clamp-2" title="{{ $module->description }}">
                  {{ \Illuminate\Support\Str::limit($module->description, 110) }}
                </div>
              </td>

              <td class="px-4 py-3">
                {{ optional($module->category)->category_name ?? '—' }}
              </td>

              <td class="px-4 py-3">
                {{ optional($module->formateur)->name ?? '—' }}
              </td>

              <td class="px-4 py-3 text-center">
                {{ $module->sections_count ?? ($module->relationLoaded('sections') ? $module->sections->count() : 0) }}
              </td>

              <td class="px-4 py-3 text-center">
                {{ $module->lectures_count ?? (method_exists($module, 'lectures') && $module->relationLoaded('lectures') ? $module->lectures->count() : ($module->lectures_count ?? 0)) }}
              </td>

              <td class="px-4 py-3 text-center" title="Somme des questions planifiées (quiz)">
                {{ (int)($module->quiz_questions_planned ?? 0) }}
              </td>

              <td class="px-4 py-3">
                <img src="{{ $module->module_image ? asset('storage/'.$module->module_image) : asset('upload/module_images/NoImage.png') }}"
                     alt="Image du module {{ $module->module_name }}"
                     class="h-10 w-10 rounded-full object-cover border border-gray-200"
                     loading="lazy">
              </td>

              {{-- Actif --}}
              <td class="px-4 py-3 text-center">
                <form action="{{ route('admin.modules.toggle-status', $module->id) }}" method="POST">
                  @csrf
                  @method('PATCH')

                  <button type="submit"
                          class="inline-flex items-center justify-start w-12 h-6 rounded-full transition cursor-pointer
                                 {{ $module->status ? 'bg-vertone' : 'bg-gray-300' }}"
                          aria-label="{{ $module->status ? 'Désactiver' : 'Activer' }} le module">
                    <span class="sr-only">Basculer statut</span>
                    <span class="h-5 w-5 bg-white rounded-full shadow transform transition
                                 {{ $module->status ? 'translate-x-6' : 'translate-x-0' }}"></span>
                  </button>
                </form>
              </td>

              {{-- Actions (menu) --}}
              <td class="px-4 py-3 text-right relative">
                <div class="inline-block text-left">

                  <button type="button"
                          class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-bleuone/20 text-bleuone
                                 hover:bg-bleuone hover:text-white transition cursor-pointer"
                          aria-haspopup="true"
                          aria-expanded="false"
                          data-menu-trigger="menu-{{ $module->id }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                    <span class="sr-only">Ouvrir le menu</span>
                  </button>

                  <div id="menu-{{ $module->id }}"
                       class="hidden absolute right-0 z-20 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 overflow-hidden">
                    <div class="py-1">

                      <a href="{{ route('admin.modules.edit', $module->id) }}"
                         class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        Éditer
                      </a>

                      <a href="{{ route('admin.modules.lecture.add', $module->id) }}"
                         class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        Contenu
                      </a>

                      <form action="{{ route('admin.modules.delete', ['id' => $module->id]) }}"
                            method="GET"
                            class="delete-module-form">
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-orangeone hover:bg-orangeone/10 transition cursor-pointer">
                          Supprimer
                        </button>
                      </form>

                    </div>
                  </div>

                </div>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-4 py-6 text-center text-gray-500">Aucun module trouvé.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</div>

{{-- Toastr --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    toastr.success(@json(session('success')), 'Succès', { closeButton:true, progressBar:true, timeOut:5000 });
  });
</script>
@endif
@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    toastr.error(@json(session('error')), 'Erreur', { closeButton:true, progressBar:true, timeOut:5000 });
  });
</script>
@endif

{{-- DataTables --}}
<script>
  document.addEventListener('DOMContentLoaded', () => {
    $('#tableModules').DataTable({
      language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
      order: [[1,'asc']],
      columnDefs: [
        { targets: [0,7,8,9], orderable: false },   // #, Image, Actif, Actions
        { targets: [4,5,6,8], className: 'dt-center' },
        { targets: [9], className: 'dt-right' }
      ],
      pageLength: 25
    });
  });
</script>

{{-- Menu Actions (avec fermeture douce via CSS global) --}}
<script>
  document.addEventListener('click', function(e) {
    const openMenus = document.querySelectorAll('[id^="menu-"]:not(.hidden)');
    const trigger = e.target.closest('[data-menu-trigger]');
    const menu = e.target.closest('[id^="menu-"]');

    if (!trigger && !menu) {
      openMenus.forEach(m => m.classList.add('hidden'));
      return;
    }

    if (trigger) {
      const id = trigger.getAttribute('data-menu-trigger');
      const el = document.getElementById(id);
      openMenus.forEach(m => { if (m !== el) m.classList.add('hidden'); });
      el.classList.toggle('hidden');
      trigger.setAttribute('aria-expanded', el.classList.contains('hidden') ? 'false' : 'true');
    }
  });
</script>

{{-- Confirmation suppression --}}
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-module-form').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm("Êtes-vous sûr de vouloir supprimer ce module ? Cette action est irréversible.")) {
          this.submit();
        }
      });
    });
  });
</script>

@endsection
