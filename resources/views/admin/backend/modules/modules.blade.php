@extends('admin.admin_dashboard')
@section('admin')

{{-- Carte en-tête --}}
<div class="max-w-[1248px] mx-auto px-4">
  <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <x-typography variant="titre">Gestion des modules</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          …pour structurer les parcours de formation
        </x-typography>
        <div class="prose-oneduc">
          Les <strong>modules</strong> contiennent des sections et des leçons. Cette page permet de créer, éditer, organiser et supprimer.
        </div>
      </div>
      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/PointDInterrogation.svg') }}"
             alt="Aide gestion des modules"
             class="w-full max-w-xs" loading="lazy">
      </div>
    </div>
  </div>
</div>

{{-- Carte tableau --}}
<div class="max-w-[1248px] mx-auto px-4">
  <div class="bg-white rounded-[20px] shadow-md w-full">
    <div class="flex items-center justify-between px-8 py-5 border-b">
      <h2 class="text-xl font-semibold text-gray-800">Tous les modules</h2>
      <a href="{{ route('admin.modules.add') }}"
         class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600">
        <i class="ti ti-plus mr-1"></i> Ajouter un module
      </a>
    </div>

    <div class="overflow-x-auto p-6">
      <table id="tableModules" class="w-full text-sm text-left text-gray-700">
        <thead class="text-xs text-gray-600 uppercase bg-gray-100">
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
          <th class="px-2 py-3 text-center w-16">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($modules as $key => $module)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3">{{ $key + 1 }}</td>

            <td class="px-4 py-3">
              <div class="font-medium text-gray-900">{{ $module->module_name }}</div>
              <div class="text-gray-500 text-xs line-clamp-2"
                   title="{{ $module->description }}">
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
              {{ $module->sections_count ?? $module->sections->count() }}
            </td>

            <td class="px-4 py-3 text-center">
              {{ $module->lectures_count ?? (method_exists($module, 'lectures') ? $module->lectures->count() : 0) }}
            </td>

            <td class="px-4 py-3 text-center" title="Somme des question_count des leçons">
              {{ (int)($module->questions_count ?? 0) }}
            </td>

            <td class="px-4 py-3">
              <img src="{{ $module->module_image ? asset('storage/'.$module->module_image)
                                                  : asset('upload/module_images/NoImage.png') }}"
                   alt="Image du module {{ $module->module_name }}"
                   class="h-10 w-10 rounded-full object-cover"
                   loading="lazy">
            </td>

            {{-- Colonne Actif avec toggle --}}
            <td class="px-4 py-3 text-center">
              <form action="{{ route('admin.modules.toggle-status', $module->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center justify-start w-12 h-6 rounded-full transition
                               {{ $module->status ? 'bg-vertone' : 'bg-gray-300' }}"
                        aria-label="{{ $module->status ? 'Désactiver' : 'Activer' }} le module">
                  <span class="sr-only">Basculer statut</span>
                  <span class="h-5 w-5 bg-white rounded-full shadow transform transition
                               {{ $module->status ? 'translate-x-6' : 'translate-x-0' }}"></span>
                </button>
              </form>
            </td>

            {{-- Colonne Actions avec menu hamburger --}}
            <td class="px-2 py-3 text-center relative">
              <div class="inline-block text-left">
                <button type="button"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 hover:bg-gray-50"
                        aria-haspopup="true" aria-expanded="false" data-menu-trigger="menu-{{ $module->id }}">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                  </svg>
                  <span class="sr-only">Ouvrir le menu</span>
                </button>

                <div id="menu-{{ $module->id }}"
                     class="hidden absolute right-0 z-20 mt-2 w-44 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5">
                  <div class="py-1">
                    <a href="{{ route('admin.modules.edit', $module->id) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Éditer</a>

                    <a href="{{ route('admin.modules.lecture.add', $module->id) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Contenu</a>

                    <form action="{{ route('admin.modules.delete', ['id' => $module->id]) }}" method="GET"
      class="delete-module-form">
                    <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-orange-700 hover:bg-orange-50">
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
    toastr.success(@json(session('success')), 'Succès', {
      closeButton: true, progressBar: true, timeOut: 5000
    });
  });
</script>
@endif
@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    toastr.error(@json(session('error')), 'Erreur', {
      closeButton: true, progressBar: true, timeOut: 5000
    });
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
        { targets: [4,5,6,7,8], className: 'dt-center' }
      ],
      pageLength: 25
    });
  });
</script>

{{-- Script menu hamburger --}}
<script>
  document.addEventListener('click', function(e){
    const openMenus = document.querySelectorAll('[id^="menu-"]:not(.hidden)');
    const trigger = e.target.closest('[data-menu-trigger]');
    const menu = e.target.closest('[id^="menu-"]');
    if (!trigger && !menu) { openMenus.forEach(m=>m.classList.add('hidden')); return; }
    if (trigger) {
      const id = trigger.getAttribute('data-menu-trigger');
      const el = document.getElementById(id);
      openMenus.forEach(m=>{ if(m!==el) m.classList.add('hidden'); });
      el.classList.toggle('hidden');
      trigger.setAttribute('aria-expanded', el.classList.contains('hidden') ? 'false' : 'true');
    }
  });
</script>
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
