@extends('formateur.dashboard')

@section('formateur')

@php
  $selectedModuleIds = $group->modules->pluck('id')->map(fn($id) => (int) $id)->values();
  $persistedModuleIdSet = $selectedModuleIds->flip();

  $sortedSelected = $group->modules
    ->sortBy(fn($m) => (int) ($m->pivot->position ?? 999999))
    ->values()
    ->map(fn($m, $i) => [
      'id' => (int) $m->id,
      'title' => (string) $m->module_title,
      'position' => (int) ($m->pivot->position ?? ($i + 1)),
      'persisted' => true,
      'manage_url' => route('formateur.groupes.modules.lecons.edit', [
        'group' => $group->id,
        'module' => $m->id,
      ]),
    ])
    ->values();

  $availableModules = collect($modules)
    ->filter(fn($module) => !empty($module->status) && (int) $module->status === 1)
    ->values()
    ->map(fn($module) => [
      'id' => (int) $module->id,
      'title' => (string) $module->module_title,
    ])
    ->values();

  $availableModulesById = $availableModules->keyBy('id');
  $oldPositions = old('module_positions', []);
  $oldModuleIds = collect(old('modules', []))
    ->map(fn($id) => (int) $id)
    ->filter(fn($id) => $id > 0)
    ->unique()
    ->values();

  $selectedModulesForFlow = $sortedSelected;

  if ($oldModuleIds->isNotEmpty()) {
    $selectedModulesForFlow = $oldModuleIds
      ->sortBy(fn($id) => (int) data_get($oldPositions, (string) $id, PHP_INT_MAX))
      ->values()
      ->map(function ($id, $index) use ($availableModulesById, $persistedModuleIdSet, $group) {
        $isPersisted = $persistedModuleIdSet->has($id);
        return [
          'id' => $id,
          'title' => (string) data_get($availableModulesById->get($id), 'title', "Module #{$id}"),
          'position' => $index + 1,
          'persisted' => $isPersisted,
          'manage_url' => $isPersisted
            ? route('formateur.groupes.modules.lecons.edit', ['group' => $group->id, 'module' => $id])
            : '',
        ];
      })
      ->values();
  }

  $moduleBadgeCount = $selectedModulesForFlow->count();

  $initialActiveTab = 'general';
  if ($errors->has('modules') || $errors->has('modules.*') || $errors->has('module_positions') || $errors->has('module_positions.*')) {
    $initialActiveTab = 'parcours';
  } elseif ($errors->has('stagiaires') || $errors->has('stagiaires.*') || $errors->has('remove_students') || $errors->has('remove_students.*') || $errors->has('password')) {
    $initialActiveTab = 'stagiaires';
  }
@endphp

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE (Style identique à la création) --}}
  <div class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <h1 class="font-raleway text-titre text-bleuone leading-tight mb-2">
            Modification du groupe : <br/><span class="text-orangeone">{{ $group->name }}</span>
        </h1>
        <p class="font-varela text-gray-600 mb-4">
          Gérez la configuration, la liste des apprenants et l'ordre pédagogique des modules.
        </p>

        {{-- Pastilles d'info --}}
        <div class="flex flex-wrap items-center gap-3 mb-4 font-varela text-sm">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-vertone/10 text-vertone border border-vertone/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <span class="font-bold">{{ $group->students->count() }}</span> Stagiaires
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orangeone/10 text-orangeone border border-orangeone/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                <span class="font-bold">{{ $moduleBadgeCount }}</span> Modules
            </div>
        </div>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-2">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.groupes.index') }}" class="hover:underline text-bleuone">Mes groupes</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Modifier {{ $group->name }}</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Groupes.svg') }}" alt="Illustration" class="max-w-[256px] h-auto opacity-80">
      </div>
    </div>
  </div>

  {{-- CONTENU PRINCIPAL --}}
  <main
    x-data="groupEdit()"
    class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full"
  >

    <form method="POST" action="{{ route('formateur.groupes.update', $group->id) }}" class="space-y-8">
      @csrf
      @method('PUT')

      {{-- 1. NAVIGATION UNIFORMISÉE (Style Wizard) --}}
      <nav aria-label="Sections du groupe">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            
            {{-- BOUTON 1 : INFOS --}}
            <button type="button"
                @click="activeTab = 'general'"
                :class="activeTab === 'general' 
                    ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' 
                    : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                class="w-full px-6 py-4 rounded-full transition font-varela text-lg font-bold focus:outline-none flex items-center justify-center gap-2">
                <span>1.</span>
                <span>Informations</span>
            </button>

            {{-- BOUTON 2 : STAGIAIRES --}}
            <button type="button"
                @click="activeTab = 'stagiaires'"
                :class="activeTab === 'stagiaires' 
                    ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' 
                    : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                class="w-full px-6 py-4 rounded-full transition font-varela text-lg font-bold focus:outline-none flex items-center justify-center gap-2">
                <span>2.</span>
                <span>Stagiaires</span>
            </button>

            {{-- BOUTON 3 : MODULES --}}
            <button type="button"
                @click="activeTab = 'parcours'; $nextTick(() => window.dispatchEvent(new CustomEvent('oneduc:group-flow-refresh')))"
                :class="activeTab === 'parcours' 
                    ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' 
                    : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                class="w-full px-6 py-4 rounded-full transition font-varela text-lg font-bold focus:outline-none flex items-center justify-center gap-2">
                <span>3.</span>
                <span>Modules</span>
            </button>
        </div>
        <div class="h-1 w-full bg-gray-100 rounded mt-6 mb-2"></div>
      </nav>

      {{-- 2. ZONES DE CONTENU --}}
      
      {{-- SECTION 1 : Configuration générale --}}
      <section x-show="activeTab === 'general'" x-cloak class="animate-fade-in-down">
        <p class="text-base text-gray-600 mb-6">Nommer le groupe et ajouter une description.</p>

        <div class="mb-6">
          <label for="nom" class="block mb-2 text-base font-medium text-gray-900">Nom du groupe *</label>
          <input id="nom" name="nom" type="text" required
                 value="{{ old('nom', $group->name) }}"
                 class="bg-gray-50 border {{ $errors->has('nom') ? 'border-red-400' : 'border-gray-300' }} text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
                 placeholder="Ex : Groupe Marketing 2025 - Niveau 1">
          @error('nom')
            <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
          @enderror
          <p class="text-xs text-gray-500 mt-1">Un nom clair facilite la recherche et le suivi.</p>
        </div>

        <div class="mb-6">
          <label for="description" class="block mb-2 text-base font-medium text-gray-900">Description</label>
          <textarea id="description" name="description" rows="3"
                    class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
                    placeholder="Objectifs, public, période…">{{ old('description', $group->description) }}</textarea>
        </div>
      </section>

      {{-- SECTION 2 : Stagiaires --}}
      <section x-show="activeTab === 'stagiaires'" x-cloak class="animate-fade-in-down">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-xl font-bold text-bleuone font-raleway mb-1">Gestion des stagiaires</h3>
                <p class="text-gray-600 font-lisible">Ajoutez ou retirez des apprenants.</p>
            </div>
            <div id="removed-recap" class="hidden text-sm bg-red-50 text-red-700 px-4 py-2 rounded-lg border border-red-100">
                <span class="font-bold">À retirer :</span>
                <ul id="removed-recap-list" class="inline-flex gap-2 list-none ml-2 italic"></ul>
            </div>
        </div>

        <div id="remove-students-hidden"></div>

        {{-- Tableau Stagiaires Existants --}}
        @if($group->students->count() > 0)
        <div class="border border-gray-200 rounded-[12px] overflow-hidden mb-8">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold">
                    <tr>
                        <th class="px-6 py-3">Nom complet</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($group->students as $stagiaire)
                    <tr class="hover:bg-gray-50 transition" data-student-row="{{ $stagiaire->id }}">
                        <td class="px-6 py-3 font-medium text-gray-900">
                            {{ $stagiaire->prenom }} <span class="uppercase">{{ $stagiaire->name }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $stagiaire->email }}</td>
                        <td class="px-6 py-3 text-right">
                            <button type="button"
                                @click="toggleRemove({{ $stagiaire->id }}, '{{ addslashes($stagiaire->prenom.' '.$stagiaire->name) }}')"
                                class="text-orangeone hover:text-red-600 font-bold text-xs uppercase tracking-wider transition">
                                Retirer
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Ajout Nouveaux Stagiaires (même structure que création) --}}
        <div class="mb-8">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
            <div class="space-y-3">
              <h3 class="text-xl font-bold text-bleuone font-raleway">Ajouter vos stagiaires</h3>
              <p class="text-sm text-gray-600 font-lisible mt-1">Renseignez les informations de vos apprenants.</p>
            </div>
            <div class="flex items-center gap-2 sm:pt-1">
              <button type="button"
                      class="w-10 h-10 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-bleuone hover:border-bleuone/30 transition"
                      onclick="openCsvModalEdit()"
                      aria-label="Importer des stagiaires par CSV"
                      title="Importer un lot CSV">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
              </button>
            </div>
          </div>

          <div id="stagiaires-container-edit" class="space-y-3">
            <div class="bg-white border border-gray-200 p-4 rounded-[12px] shadow-sm relative stagiaire-row-edit group hover:border-orangeone/50 transition">
              <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_auto] gap-4 items-start">
                <div>
                  <label for="stagiaires_edit_0_prenom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prénom</label>
                  <input id="stagiaires_edit_0_prenom" name="stagiaires[0][prenom]" type="text" placeholder="Ex: Thomas"
                         class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
                </div>

                <div>
                  <label for="stagiaires_edit_0_nom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nom</label>
                  <input id="stagiaires_edit_0_nom" name="stagiaires[0][nom]" type="text" placeholder="Ex: Dupont"
                         class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
                </div>

                <div>
                  <label for="stagiaires_edit_0_email" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email professionnel</label>
                  <input id="stagiaires_edit_0_email" name="stagiaires[0][email]" type="email" placeholder="thomas.dupont@entreprise.com"
                         class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
                </div>

                <div class="flex items-end h-full pb-[3px]">
                  <button type="button" class="text-gray-300 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50"
                          onclick="removeStagiaireEdit(this)" title="Supprimer la ligne">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <button type="button"
                    class="px-4 py-2 bg-bleuone/10 text-bleuone border border-bleuone/20 font-bold rounded-lg hover:bg-bleuone hover:text-white transition inline-flex items-center justify-center gap-2 text-sm"
                    onclick="addStagiaireEdit()">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
              Ajouter un stagiaire
            </button>
          </div>
        </div>

        <div class="mt-6 bg-orangeone/5 border border-orangeone/20 rounded-[12px] p-3">
          <div class="mb-2 flex items-center gap-2">
            <h4 class="text-sm font-bold text-gray-800 font-raleway">Code d'accès provisoire du groupe</h4>
            <div class="relative group">
              <button type="button" aria-label="Information" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                i
              </button>
              <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                Les stagiaires recevront un e-mail avec leur identifiant et un lien qu'ils pourront utiliser pour se connecter.
              </div>
            </div>
          </div>

          <div class="w-full max-w-sm">
            <label for="password" class="sr-only">Mot de passe commun</label>
            <input id="password" name="password" type="text" required minlength="8" autocomplete="off"
                   value="{{ old('password', $group->temporary_password) }}"
                   class="bg-white border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone block w-full px-3 py-2.5 font-mono tracking-wide"
                   placeholder="Ex: Formation2026!">
            @error('password')
              <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </section>

      {{-- Modale import CSV (discrète, sans sortir du wizard) --}}
      <div id="csv-modal-edit" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h4 class="font-bold text-bleuone">Import de stagiaires (CSV)</h4>
            <button type="button" class="text-gray-400 hover:text-gray-700" onclick="closeCsvModalEdit()" aria-label="Fermer">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="px-5 py-4 space-y-4">
            <p class="text-sm text-gray-600">
              Format attendu: colonnes <span class="font-bold">prenom</span>, <span class="font-bold">nom</span>, <span class="font-bold">email</span>.
            </p>
            <p class="text-xs text-gray-500 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2">
              Exemple d’en-tête: <code>prenom;nom;email</code> (ou séparateur virgule).
            </p>
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
              <p class="text-xs font-semibold text-gray-700 mb-1">Exemple CSV (2 stagiaires)</p>
              <pre class="text-xs text-gray-700 whitespace-pre-wrap">prenom;nom;email
Camille;Martin;camille.martin@entreprise.fr
Lucas;Bernard;lucas.bernard@entreprise.fr</pre>
            </div>

            <input id="csv-file-edit" type="file" accept=".csv,text/csv"
                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">

            <div id="csv-feedback-edit" class="hidden rounded-lg px-3 py-2 text-sm"></div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4">
            <button type="button"
                    class="px-3 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
                    onclick="closeCsvModalEdit()">
              Fermer
            </button>
            <button type="button"
                    id="csv-import-confirm-edit"
                    class="px-3 py-2 text-sm rounded-lg bg-bleuone text-white hover:opacity-90">
              Importer
            </button>
          </div>
        </div>
      </div>


      {{-- SECTION 3 : Parcours / Modules --}}
      <section x-show="activeTab === 'parcours'" x-cloak class="animate-fade-in-down">
        
        <div class="mb-6">
            <h3 class="text-xl font-bold text-bleuone font-raleway mb-1">Parcours pédagogique</h3>
            <p class="text-gray-600 font-lisible">Définissez l'ordre chronologique des modules pour vos stagiaires.</p>
        </div>

        <div
          data-group-module-flow
          data-mode="edit"
          data-available-modules='@json($availableModules)'
          data-selected-modules='@json($selectedModulesForFlow)'
          data-manage-lessons-label="Gérer les leçons"
          class="space-y-6"
        ></div>

        @if($errors->has('modules') || $errors->has('modules.*'))
          <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('modules') ?: $errors->first('modules.*') }}
          </div>
        @endif
        @if($errors->has('module_positions') || $errors->has('module_positions.*'))
          <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('module_positions') ?: $errors->first('module_positions.*') }}
          </div>
        @endif
        <p class="text-xs text-gray-500 mt-2 font-lisible bg-blue-50 p-2 rounded inline-block border border-blue-100">
           ℹ️ L'ordre défini ici sera celui présenté à l'apprenant dans son espace.
        </p>

      </section>

      <hr class="border-gray-100 my-8">
      
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('formateur.groupes.index') }}" class="text-gray-500 font-bold hover:text-bleuone transition">
          Annuler
        </a>
        
        <button type="submit" class="btn-oneduc w-full md:w-auto px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
            Enregistrer les modifications
        </button>
      </div>

    </form>
  </main>
</div>

{{-- STYLES & SCRIPTS (Inchangés mais essentiels) --}}
<style>
  [x-cloak] { display: none !important; }
  .animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }
  @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
  }
</style>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('groupEdit', () => ({
    activeTab: @json($initialActiveTab),
    removed: [],
    init() {},

    // --- STAGIAIRES ---
    toggleRemove(id, name) {
      const n = parseInt(id, 10);
      if (!n || this.removed.includes(n)) return;
      this.removed.push(n);
      const row = document.querySelector(`[data-student-row='${n}']`);
      if (row) row.classList.add('hidden');
      const recap = document.getElementById('removed-recap');
      if (recap) recap.classList.remove('hidden');
      const ul = document.getElementById('removed-recap-list');
      if (ul) {
        const li = document.createElement('li');
        li.textContent = name;
        ul.appendChild(li);
      }
      const hiddenWrap = document.getElementById('remove-students-hidden');
      if (hiddenWrap && !hiddenWrap.querySelector(`input[value='${n}']`)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_students[]';
        input.value = n;
        hiddenWrap.appendChild(input);
      }
    }
  }));
});
</script>

<script>
(function () {
  function fillStagiaireRowEdit(row, data) {
    const prenomInput = row.querySelector('input[name$="[prenom]"]');
    const nomInput = row.querySelector('input[name$="[nom]"]');
    const emailInput = row.querySelector('input[name$="[email]"]');

    if (prenomInput) prenomInput.value = data.prenom || '';
    if (nomInput) nomInput.value = data.nom || '';
    if (emailInput) emailInput.value = data.email || '';
  }

  function findEmptyStagiaireRowEdit() {
    const rows = Array.from(document.querySelectorAll('#stagiaires-container-edit .stagiaire-row-edit'));
    return rows.find((row) => {
      const values = Array.from(row.querySelectorAll('input[name$="[prenom]"], input[name$="[nom]"], input[name$="[email]"]'))
        .map((i) => (i.value || '').trim());
      return values.every(v => v === '');
    }) || null;
  }

  window.addStagiaireEdit = function (data = null) {
    const container = document.getElementById('stagiaires-container-edit');
    if (!container) return;

    const rows = container.querySelectorAll('.stagiaire-row-edit');
    const index = rows.length;

    const tpl = `
      <div class="bg-white border border-gray-200 p-4 rounded-[12px] shadow-sm relative stagiaire-row-edit group hover:border-orangeone/50 transition mt-3 animate-fade-in-down">
        <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_auto] gap-4 items-start">
          <div>
            <label for="stagiaires_edit_${index}_prenom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prénom</label>
            <input id="stagiaires_edit_${index}_prenom" name="stagiaires[${index}][prenom]" type="text" placeholder="Prénom"
                   class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div>
            <label for="stagiaires_edit_${index}_nom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nom</label>
            <input id="stagiaires_edit_${index}_nom" name="stagiaires[${index}][nom]" type="text" placeholder="Nom"
                   class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div>
            <label for="stagiaires_edit_${index}_email" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email professionnel</label>
            <input id="stagiaires_edit_${index}_email" name="stagiaires[${index}][email]" type="email" placeholder="Email"
                   class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div class="flex items-end h-full pb-[3px]">
            <button type="button" class="text-gray-300 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50"
                    onclick="removeStagiaireEdit(this)" title="Supprimer la ligne">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>`;

    container.insertAdjacentHTML('beforeend', tpl);

    if (data && (data.prenom || data.nom || data.email)) {
      const inserted = container.lastElementChild;
      if (inserted) fillStagiaireRowEdit(inserted, data);
    }
  };

  window.removeStagiaireEdit = function (btn) {
    const row = btn.closest('.stagiaire-row-edit');
    const container = document.getElementById('stagiaires-container-edit');
    if (!container || !row) return;

    if (container.querySelectorAll('.stagiaire-row-edit').length <= 1) {
      alert('Le groupe doit contenir au moins un stagiaire.');
      return;
    }
    row.remove();
  };

  function showCsvFeedbackEdit(message, type = 'info') {
    const box = document.getElementById('csv-feedback-edit');
    if (!box) return;
    box.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border', 'border-red-100', 'bg-green-50', 'text-green-700', 'border-green-100', 'bg-gray-50', 'text-gray-700', 'border-gray-100');
    if (type === 'error') {
      box.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-100');
    } else if (type === 'success') {
      box.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-100');
    } else {
      box.classList.add('bg-gray-50', 'text-gray-700', 'border', 'border-gray-100');
    }
    box.textContent = message;
  }

  window.openCsvModalEdit = function () {
    const modal = document.getElementById('csv-modal-edit');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  window.closeCsvModalEdit = function () {
    const modal = document.getElementById('csv-modal-edit');
    const input = document.getElementById('csv-file-edit');
    const box = document.getElementById('csv-feedback-edit');
    if (modal) {
      modal.classList.remove('flex');
      modal.classList.add('hidden');
    }
    if (input) input.value = '';
    if (box) {
      box.classList.add('hidden');
      box.textContent = '';
    }
  };

  function normalizeCsvHeaderEdit(header) {
    return (header || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\s+/g, '')
      .trim();
  }

  function detectDelimiterEdit(text) {
    const sample = (text || '').split(/\r?\n/).slice(0, 3).join('\n');
    const semicolonCount = (sample.match(/;/g) || []).length;
    const commaCount = (sample.match(/,/g) || []).length;
    return semicolonCount >= commaCount ? ';' : ',';
  }

  function parseCsvLineEdit(line, delimiter) {
    const out = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
      const ch = line[i];

      if (ch === '"') {
        if (inQuotes && line[i + 1] === '"') {
          current += '"';
          i++;
        } else {
          inQuotes = !inQuotes;
        }
        continue;
      }

      if (ch === delimiter && !inQuotes) {
        out.push(current.trim());
        current = '';
        continue;
      }

      current += ch;
    }

    out.push(current.trim());
    return out;
  }

  function extractStudentsFromCsvEdit(text) {
    const delimiter = detectDelimiterEdit(text);
    const lines = (text || '').split(/\r?\n/).filter(l => l.trim() !== '');
    if (lines.length === 0) {
      return { students: [], skipped: 0 };
    }

    const rows = lines.map(line => parseCsvLineEdit(line, delimiter));
    const first = rows[0].map(normalizeCsvHeaderEdit);

    const emailAliases = ['email', 'e-mail', 'mail', 'courriel'];
    const prenomAliases = ['prenom', 'firstname', 'first_name', 'givenname', 'given_name'];
    const nomAliases = ['nom', 'name', 'lastname', 'last_name', 'surname', 'familyname'];

    const emailIdx = first.findIndex(h => emailAliases.includes(h));
    const prenomIdx = first.findIndex(h => prenomAliases.includes(h));
    const nomIdx = first.findIndex(h => nomAliases.includes(h));
    const hasHeader = emailIdx !== -1 || prenomIdx !== -1 || nomIdx !== -1;

    const startAt = hasHeader ? 1 : 0;
    const mapIdx = {
      prenom: prenomIdx !== -1 ? prenomIdx : 0,
      nom: nomIdx !== -1 ? nomIdx : 1,
      email: emailIdx !== -1 ? emailIdx : 2,
    };

    const students = [];
    let skipped = 0;

    for (let i = startAt; i < rows.length; i++) {
      const cols = rows[i];
      const prenom = (cols[mapIdx.prenom] || '').trim();
      const nom = (cols[mapIdx.nom] || '').trim();
      const email = (cols[mapIdx.email] || '').trim().toLowerCase();

      if (!prenom && !nom && !email) continue;
      if (!prenom || !nom || !email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        skipped++;
        continue;
      }

      students.push({ prenom, nom, email });
    }

    return { students, skipped };
  }

  function collectExistingEmailsEdit() {
    const fromNewRows = Array.from(document.querySelectorAll('#stagiaires-container-edit input[name$="[email]"]'))
      .map((i) => (i.value || '').trim().toLowerCase())
      .filter(Boolean);

    const fromCurrentGroup = Array.from(document.querySelectorAll('tr[data-student-row] td:nth-child(2)'))
      .map((cell) => (cell.textContent || '').trim().toLowerCase())
      .filter(Boolean);

    return new Set([...fromNewRows, ...fromCurrentGroup]);
  }

  function addStudentsFromCsvEdit(students) {
    let added = 0;

    students.forEach((student) => {
      const emptyRow = findEmptyStagiaireRowEdit();
      if (emptyRow) {
        fillStagiaireRowEdit(emptyRow, student);
      } else {
        addStagiaireEdit(student);
      }
      added++;
    });

    return { added };
  }

  const importBtn = document.getElementById('csv-import-confirm-edit');
  if (importBtn) {
    importBtn.addEventListener('click', () => {
      const input = document.getElementById('csv-file-edit');
      const file = input?.files?.[0];

      if (!file) {
        showCsvFeedbackEdit('Sélectionnez un fichier CSV.', 'error');
        return;
      }

      const reader = new FileReader();
      reader.onload = () => {
        const text = String(reader.result || '');
        const { students, skipped } = extractStudentsFromCsvEdit(text);

        if (!students.length) {
          showCsvFeedbackEdit('Aucun stagiaire valide trouvé dans le fichier.', 'error');
          return;
        }

        const existing = collectExistingEmailsEdit();
        const uniqueStudents = [];
        let duplicates = 0;

        students.forEach((student) => {
          if (existing.has(student.email)) {
            duplicates++;
            return;
          }
          existing.add(student.email);
          uniqueStudents.push(student);
        });

        const { added } = addStudentsFromCsvEdit(uniqueStudents);
        showCsvFeedbackEdit(`Import terminé: ${added} ajouté(s), ${duplicates} doublon(s), ${skipped} ligne(s) ignorée(s).`, 'success');
      };
      reader.onerror = () => showCsvFeedbackEdit('Lecture du fichier impossible.', 'error');
      reader.readAsText(file);
    });
  }

  const modal = document.getElementById('csv-modal-edit');
  if (modal) {
    modal.addEventListener('click', (event) => {
      if (event.target === modal) {
        closeCsvModalEdit();
      }
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      const modalEl = document.getElementById('csv-modal-edit');
      if (modalEl && modalEl.classList.contains('flex')) {
        closeCsvModalEdit();
      }
    }
  });
})();
</script>

@endsection
