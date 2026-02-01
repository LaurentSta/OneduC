{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/formations/index.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')

{{-- Wrapper principal --}}
<div class="w-full px-6 lg:px-8">
  <div class="max-w-[1285px] mx-auto my-6">

    {{-- En-tête de page (reprend ta structure) --}}
    <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6 border border-gray-100">
      <div class="grid grid-cols-12 gap-6 items-center">

        {{-- Texte --}}
        <div class="col-span-12 md:col-span-9">
          <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
            Mes modules de formation
          </p>
          <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
            Suivez les modules que vous utilisez dans vos groupes.
          </p>
          <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
            Retrouvez ici tous les modules de formation utilisés, leur statut, les groupes concernés et les stagiaires associés.
          </p>

          {{-- Fil d’Ariane --}}
          <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
            <ol class="inline-flex items-center space-x-1">
              <li class="flex items-center">
                <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                  <span class="sr-only">Accueil</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                  </svg>
                </a>
                <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
              </li>
              <li class="text-gray-400">Mes modules</li>
            </ol>
          </nav>
        </div>

        {{-- Illustration --}}
        <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
          <img
            src="{{ asset('images/svg/Modules.svg') }}"
            alt="Illustration des modules de formation"
            class="max-w-[256px] h-auto"
            loading="lazy"
          >
        </div>

      </div>
    </header>

    {{-- Contenu --}}
    <main class="space-y-8">

      @if($modules->isEmpty())
        <div class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">
          <p class="text-gray-500 font-lisible">Aucun module de formation trouvé.</p>
        </div>
      @else

        {{-- Carte tableau --}}
        <div class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">
          <div class="flex items-start justify-between gap-3 mb-4">
            <div>
              <h2 class="text-base font-semibold text-[#004461]">Liste des modules</h2>
              <p class="text-sm text-gray-600">Tri et recherche.</p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table id="modulesTable" class="table-oneduc w-full text-sm">
              <caption class="sr-only">Tableau des modules de formation utilisés par le formateur</caption>

              <thead>
                <tr>
                  <th scope="col" class="px-3 py-3 text-left">Titre</th>
                  <th scope="col" class="px-3 py-3 text-left">Date de création</th>
                  <th scope="col" class="px-3 py-3 text-left">Statut</th>
                  <th scope="col" class="px-3 py-3 text-center">Groupes associés</th>
                  <th scope="col" class="px-3 py-3 text-center">Stagiaires total</th>
                  <th scope="col" class="px-3 py-3 text-right">Actions</th>
                </tr>
              </thead>

              <tbody>
                @foreach ($modules as $module)
                  @php
                    /**
                     * Logique d'origine (conservée) :
                     * - groupes via $module->groups
                     * - stagiaires total = union des users(role=stagiaire) de tous les groupes
                     * - statut :
                     *    - si groupes > 0 => "utilise"
                     *    - sinon si module->status == 1 => "non_utilise"
                     *    - sinon => "indisponible"
                     *
                     * Remarque perf :
                     * pour éviter les requêtes en boucle, charger dans le contrôleur :
                     * ->with(['groups.users' => fn($q)=>$q->where('role','stagiaire')])
                     */
                    $groupes = $module->groups ?? collect();
                    $stagiaires = $groupes
                      ->flatMap(fn($g) => ($g->users ?? collect())->where('role', 'stagiaire'))
                      ->unique('id');

                    if ($groupes->count() > 0) {
                      $statut = 'utilise';
                    } elseif ((int)($module->status ?? 0) === 1) {
                      $statut = 'non_utilise';
                    } else {
                      $statut = 'indisponible';
                    }

                    $titre = $module->module_title ?? $module->module_name ?? $module->name ?? 'Sans titre';
                  @endphp

                  <tr class="border-b border-gray-100">

                    {{-- Titre --}}
                    <td class="px-3 py-3 font-semibold text-gray-900">
                      {{ $titre }}
                    </td>

                    {{-- Date de création --}}
                    <td class="px-3 py-3 text-gray-700">
                      {{ optional($module->created_at)->format('d/m/Y') ?? '—' }}
                    </td>

                    {{-- Statut --}}
                    <td class="px-3 py-3">
                      @if($statut === 'utilise')
                        <span class="inline-flex items-center px-2.5 py-1 text-green-800 bg-green-100 rounded-full text-xs font-semibold border border-green-200">
                          <span class="h-1.5 w-1.5 rounded-full bg-green-600 mr-2" aria-hidden="true"></span>
                          Utilisé
                        </span>
                      @elseif($statut === 'non_utilise')
                        <span class="inline-flex items-center px-2.5 py-1 text-blue-800 bg-blue-100 rounded-full text-xs font-semibold border border-blue-200">
                          <span class="h-1.5 w-1.5 rounded-full bg-blue-600 mr-2" aria-hidden="true"></span>
                          Non utilisé
                        </span>
                      @else
                        <span class="inline-flex items-center px-2.5 py-1 text-red-800 bg-red-100 rounded-full text-xs font-semibold border border-red-200">
                          <span class="h-1.5 w-1.5 rounded-full bg-red-600 mr-2" aria-hidden="true"></span>
                          Indisponible
                        </span>
                      @endif
                    </td>

                    {{-- Groupes associés --}}
                    <td class="px-3 py-3 text-center font-semibold text-gray-900">
                      {{ $groupes->count() }}
                    </td>

                    {{-- Stagiaires total --}}
                    <td class="px-3 py-3 text-center font-semibold text-gray-900">
                      {{ $stagiaires->count() }}
                    </td>

                    {{-- Actions --}}
                    <td class="px-3 py-3 text-right">
                      @if($statut === 'indisponible')
                        <span class="text-gray-400 line-through text-sm cursor-not-allowed select-none">Voir</span>
                      @else
                        <div class="inline-flex items-center justify-end gap-3">

                          {{-- Voir --}}
                          <a
                            href="{{ route('formateur.formations.detail', $module->id) }}"
                            class="btn-oneduc-sm btn-oneduc-sm--outline"
                          >
                            Voir
                          </a>

                          {{-- Cheminement pédagogique : par groupe --}}
                          @if($groupes->count() === 1)
                            @php $g = $groupes->first(); @endphp
                            <a
                              href="{{ route('formateur.groupes.modules.lecons.edit', ['group' => $g->id, 'module' => $module->id]) }}"
                              class="btn-oneduc-sm btn-oneduc-sm--outline"
                            >
                              Cheminement
                            </a>

                          @elseif($groupes->count() > 1)
                            <div class="relative group">
                              <button type="button" class="btn-oneduc-sm btn-oneduc-sm--outline">
                                Cheminement
                              </button>

                              <div class="hidden group-hover:block absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-[14px] shadow-lg overflow-hidden z-10">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 bg-gray-50">
                                  Choisir un groupe
                                </div>
                                <div class="max-h-64 overflow-auto">
                                  @foreach($groupes as $g)
                                    <a
                                      href="{{ route('formateur.groupes.modules.lecons.edit', ['group' => $g->id, 'module' => $module->id]) }}"
                                      class="block px-4 py-2 text-sm text-gray-800 hover:bg-gray-50"
                                    >
                                      {{ $g->name ?? ('Groupe #'.$g->id) }}
                                    </a>
                                  @endforeach
                                </div>
                              </div>
                            </div>
                          @endif

                        </div>
                      @endif
                    </td>

                  </tr>
                @endforeach
              </tbody>

            </table>
          </div>
        </div>
      @endif

    </main>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

  jQuery('#modulesTable').DataTable({
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[0, 'asc']],
    autoWidth: false,
    responsive: false,
    language: {
      search: "Rechercher :",
      lengthMenu: "Afficher _MENU_ lignes",
      info: "Affichage _START_ à _END_ sur _TOTAL_",
      infoEmpty: "Aucune donnée",
      infoFiltered: "(filtré sur _MAX_)",
      zeroRecords: "Aucun résultat",
      paginate: {
        first: "Début",
        last: "Fin",
        next: "Suivant",
        previous: "Précédent"
      }
    },
    columnDefs: [
      { targets: [5], orderable: false }
    ]
  });
});
</script>
@endpush
