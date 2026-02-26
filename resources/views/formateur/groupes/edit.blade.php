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
  } elseif ($errors->has('stagiaires') || $errors->has('stagiaires.*') || $errors->has('remove_students') || $errors->has('remove_students.*')) {
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
    data-next-index="{{ max(0, (int) $group->students->count()) }}"
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
                <span>1.</span> Informations
            </button>

            {{-- BOUTON 2 : STAGIAIRES --}}
            <button type="button"
                @click="activeTab = 'stagiaires'"
                :class="activeTab === 'stagiaires' 
                    ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' 
                    : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                class="w-full px-6 py-4 rounded-full transition font-varela text-lg font-bold focus:outline-none flex items-center justify-center gap-2">
                <span>2.</span> Stagiaires
            </button>

            {{-- BOUTON 3 : MODULES --}}
            <button type="button"
                @click="activeTab = 'parcours'; $nextTick(() => window.dispatchEvent(new CustomEvent('oneduc:group-flow-refresh')))"
                :class="activeTab === 'parcours' 
                    ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' 
                    : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                class="w-full px-6 py-4 rounded-full transition font-varela text-lg font-bold focus:outline-none flex items-center justify-center gap-2">
                <span>3.</span> Modules
            </button>
        </div>
        <div class="h-1 w-full bg-gray-100 rounded mt-6 mb-2"></div>
      </nav>

      {{-- 2. ZONES DE CONTENU --}}
      
      {{-- SECTION 1 : Configuration générale --}}
      <section x-show="activeTab === 'general'" x-cloak class="animate-fade-in-down">
        <div class="mb-6">
            <h3 class="text-xl font-bold text-bleuone font-raleway mb-1">Informations du groupe</h3>
            <p class="text-gray-600 font-lisible">Modifiez le nom pour faciliter l'identification.</p>
        </div>

        <div class="space-y-6 max-w-3xl">
            <div>
                <label for="nom" class="block mb-2 text-base font-medium text-gray-900">Nom du groupe <span class="text-orangeone">*</span></label>
                <input id="nom" name="nom" type="text" value="{{ old('nom', $group->name) }}" required
                    class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5">
            </div>

            <div>
                <label for="description" class="block mb-2 text-base font-medium text-gray-900">Description</label>
                <textarea id="description" name="description" rows="4"
                    class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5">{{ old('description', $group->description) }}</textarea>
            </div>
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

        {{-- Ajout Nouveaux Stagiaires --}}
        <div class="bg-gray-50 border border-gray-200 rounded-[16px] p-6">
             <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-bleuone">Ajouter de nouveaux stagiaires</h4>
                <div class="flex items-center gap-2">
                    <button type="button"
                            class="w-10 h-10 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-bleuone hover:border-bleuone/30 transition"
                            onclick="openCsvModalEdit()"
                            aria-label="Importer des stagiaires par CSV"
                            title="Importer un lot CSV">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </button>

                    <button type="button" @click="addStudentRow()" class="btn-oneduc text-sm py-2 px-4">
                        + Ajouter une ligne
                    </button>
                </div>
            </div>
            
            <table class="w-full" x-show="nextNewStudentIndex > 0">
                 <tbody id="new-students-tbody" class="space-y-2 block sm:table-row-group">
                    {{-- Les TR seront injectés ici via JS --}}
                 </tbody>
            </table>
            <p x-show="nextNewStudentIndex === 0" class="text-sm text-gray-500 italic text-center py-2">
                Aucun nouveau stagiaire ajouté pour le moment.
            </p>
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
    nextNewStudentIndex: 0,

    init() {
      this.nextNewStudentIndex = parseInt(this.$el.dataset.nextIndex || '0', 10);

      window.addEventListener('oneduc:csv-import-edit', (event) => {
        const rows = event?.detail?.rows || [];
        this.addStudentsFromCsv(rows);
      });
    },

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
    },

    addStudentsFromCsv(rows = []) {
      if (!Array.isArray(rows) || rows.length === 0) return;
      rows.forEach((row) => this.addStudentRow(row));
    },

    addStudentRow(data = {}) {
      const container = document.getElementById('new-students-tbody');
      if (!container) return;

      const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

      const prenom = escapeHtml(data.prenom || '');
      const nom = escapeHtml(data.nom || '');
      const email = escapeHtml(data.email || '');

      const i = this.nextNewStudentIndex++;
      const tr = document.createElement('tr');
      tr.className = 'border-t border-gray-100 flex flex-col sm:table-row bg-gray-50 sm:bg-transparent p-4 sm:p-0 rounded-lg mb-2 sm:mb-0';
      tr.innerHTML = `
        <td class='py-2 sm:py-3 pr-3 w-full sm:w-auto block sm:table-cell'>
          <input required name='stagiaires[${i}][prenom]' type='text' placeholder='Prénom' value='${prenom}'
            class='w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-2 sm:py-3 pr-3 w-full sm:w-auto block sm:table-cell'>
          <input required name='stagiaires[${i}][nom]' type='text' placeholder='Nom' value='${nom}'
            class='w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-2 sm:py-3 pr-3 w-full sm:w-auto block sm:table-cell'>
          <input required name='stagiaires[${i}][email]' type='email' placeholder='Email' value='${email}'
            class='w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-2 sm:py-3 text-right block sm:table-cell'>
          <button type='button' class='text-red-500 font-bold text-sm hover:underline' onclick="this.closest('tr').remove()">
            Supprimer
          </button>
        </td>
      `;
      container.appendChild(tr);
    }
  }));
});
</script>

<script>
(function () {
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
    const fromNewRows = Array.from(document.querySelectorAll('#new-students-tbody input[name$="[email]"]'))
      .map((i) => (i.value || '').trim().toLowerCase())
      .filter(Boolean);

    const fromCurrentGroup = Array.from(document.querySelectorAll('tr[data-student-row] td:nth-child(2)'))
      .map((cell) => (cell.textContent || '').trim().toLowerCase())
      .filter(Boolean);

    return new Set([...fromNewRows, ...fromCurrentGroup]);
  }

  function dispatchCsvStudentsToEdit(students) {
    window.dispatchEvent(new CustomEvent('oneduc:csv-import-edit', {
      detail: { rows: students },
    }));
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

        if (uniqueStudents.length > 0) {
          dispatchCsvStudentsToEdit(uniqueStudents);
        }

        showCsvFeedbackEdit(`Import terminé: ${uniqueStudents.length} ajouté(s), ${duplicates} doublon(s), ${skipped} ligne(s) ignorée(s).`, 'success');
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
