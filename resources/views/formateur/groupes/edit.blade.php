@extends('formateur.dashboard')

@section('formateur')

@php
  // Préparation des données pour AlpineJS
  // On récupère les modules déjà associés et on les trie par position
  $selectedModuleIds = $group->modules->pluck('id')->toArray();

  $sortedSelected = $group->modules
    ->sortBy(fn($m) => (int) ($m->pivot->position ?? 999999))
    ->values()
    ->map(fn($m, $i) => [
      'id'       => (int) $m->id,
      'title'    => $m->module_title,
      'position' => (int) ($m->pivot->position ?? ($i + 1)),
      'persisted'=> true,
      'manage_url' => route('formateur.groupes.modules.lecons.edit', [
        'group'  => $group->id,
        'module' => $m->id,
      ]),
    ]);
@endphp

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE (Style identique à la création) --}}
  <div class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <h1 class="font-raleway text-titre text-bleuone leading-tight mb-2">
            Modification du groupe : <span class="text-orangeone">{{ $group->name }}</span>
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
                <span class="font-bold">{{ count($selectedModuleIds) }}</span> Modules
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
        <img src="{{ asset('images/svg/Groupes.svg') }}" alt="Illustration" class="max-w-[180px] h-auto opacity-80">
      </div>
    </div>
  </div>

  {{-- CONTENU PRINCIPAL --}}
  <main
    x-data="groupEdit()"
    data-next-index="{{ max(0, (int) $group->students->count()) }}"
    data-selected-modules='@json($sortedSelected)'
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
                @click="activeTab = 'parcours'"
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
                <button type="button" @click="addStudentRow()" class="btn-oneduc text-sm py-2 px-4">
                    + Ajouter une ligne
                </button>
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


      {{-- SECTION 3 : Parcours / Modules (TABLEAU RESTAURÉ) --}}
      <section x-show="activeTab === 'parcours'" x-cloak class="animate-fade-in-down">
        
        <div class="mb-6">
            <h3 class="text-xl font-bold text-bleuone font-raleway mb-1">Parcours pédagogique</h3>
            <p class="text-gray-600 font-lisible">Définissez l'ordre chronologique des modules pour vos stagiaires.</p>
        </div>

        {{-- Selecteur d'ajout --}}
        <div class="bg-bleuone/5 border border-bleuone/10 rounded-[16px] p-6 mb-8 flex flex-col md:flex-row gap-4 items-end">
             <div class="flex-grow w-full">
                <label class="block text-sm font-bold text-bleuone mb-2">Ajouter un module au parcours</label>
                <select x-model="newModuleId" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:ring-orangeone focus:border-orangeone">
                    <option value="">— Sélectionner un module —</option>
                    @foreach($modules as $m)
                        <option value="{{ $m->id }}">{{ $m->module_title }}</option>
                    @endforeach
                </select>
                <p x-show="addError" x-text="addError" class="text-xs text-red-600 font-bold mt-2"></p>
             </div>
             <button type="button" class="btn-oneduc mb-[1px]" :disabled="!newModuleId" @click="addModuleFromSelect()">
                Ajouter
             </button>
        </div>

        {{-- TABLEAU DES MODULES (Pour l'ordre pédagogique) --}}
        <div class="border border-gray-200 rounded-[16px] overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-bold border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 w-[100px] text-center">Ordre</th>
                        <th class="px-6 py-4">Nom du module</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <template x-for="(m, idx) in modulesSelected" :key="m.id">
                        <tr class="hover:bg-gray-50 transition">
                            
                            {{-- Colonne Ordre --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-bleuone text-white font-bold shadow-sm" x-text="m.position"></span>
                                {{-- Hidden Inputs pour le POST --}}
                                <input type="hidden" name="modules[]" :value="m.id">
                                <input type="hidden" :name="'module_positions['+m.id+']'" :value="m.position">
                            </td>

                            {{-- Colonne Nom --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-bleuone text-base" x-text="m.title"></div>
                                <div x-show="!m.persisted" class="text-xs text-orangeone italic mt-1">Nouveau (en attente d'enregistrement)</div>
                            </td>

                            {{-- Colonne Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    
                                    {{-- Boutons Monter/Descendre Explicites --}}
                                    <div class="flex flex-col gap-1 mr-4 border-r border-gray-200 pr-4">
                                        <button type="button" @click="moveModule(m.id, -1)" :disabled="idx === 0" 
                                            class="text-xs font-bold text-gray-500 hover:text-bleuone disabled:opacity-20 flex items-center gap-1 uppercase">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                            Monter
                                        </button>
                                        <button type="button" @click="moveModule(m.id, +1)" :disabled="idx === modulesSelected.length - 1"
                                            class="text-xs font-bold text-gray-500 hover:text-bleuone disabled:opacity-20 flex items-center gap-1 uppercase">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            Descendre
                                        </button>
                                    </div>

                                    {{-- Lien Gestion Leçons --}}
                                    <template x-if="m.persisted">
                                        <a :href="m.manage_url" class="btn-oneduc text-xs !py-2 !px-3 bg-bleuone/10 text-bleuone border-bleuone/20 hover:bg-bleuone hover:text-white mr-2">
                                            Gérer les leçons
                                        </a>
                                    </template>

                                    {{-- Supprimer --}}
                                    <button type="button" @click="removeModule(m.id)" class="text-gray-400 hover:text-red-600 transition p-2" title="Retirer ce module">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="modulesSelected.length === 0">
                        <td colspan="3" class="py-8 text-center text-gray-400 italic">
                           Le parcours est vide. Ajoutez un module ci-dessus.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-xs text-gray-500 mt-2 font-lisible bg-blue-50 p-2 rounded inline-block border border-blue-100">
           ℹ️ L'ordre défini ci-dessus sera celui présenté à l'apprenant dans son espace.
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
    activeTab: 'general',
    removed: [],
    nextNewStudentIndex: 0,
    newModuleId: '',
    addError: '',
    modulesSelected: [],

    init() {
      this.nextNewStudentIndex = parseInt(this.$el.dataset.nextIndex || '0', 10);
      let parsed = [];
      try {
        parsed = JSON.parse(this.$el.dataset.selectedModules || '[]');
        if (!Array.isArray(parsed)) parsed = [];
      } catch (e) { parsed = []; }

      this.modulesSelected = parsed.map((m) => ({
        id: parseInt(m.id, 10),
        title: String(m.title ?? '').trim() || `Module #${m.id}`,
        position: parseInt(m.position ?? 0, 10) || 0,
        persisted: m.persisted !== false,
        manage_url: String(m.manage_url ?? '')
      }));

      this.normalizePositions();
    },

    // --- MODULES ---
    addModuleFromSelect() {
      this.addError = '';
      const id = parseInt(this.newModuleId || '0', 10);
      if (!id) return;
      if (this.modulesSelected.some(m => parseInt(m.id, 10) === id)) {
        this.addError = 'Ce module est déjà dans le parcours.';
        this.newModuleId = '';
        return;
      }
      const sel = this.$el.querySelector('select[x-model="newModuleId"]');
      const opt = sel ? sel.querySelector(`option[value="${id}"]`) : null;
      const title = opt ? opt.textContent.trim() : `Module #${id}`;

      this.modulesSelected.push({
        id, title,
        position: this.modulesSelected.length + 1,
        persisted: false,
        manage_url: ''
      });
      this.newModuleId = '';
      this.normalizePositions();
    },

    removeModule(id) {
      const n = parseInt(id, 10);
      this.modulesSelected = this.modulesSelected.filter(m => parseInt(m.id, 10) !== n);
      this.normalizePositions();
    },

    moveModule(id, delta) {
      const n = parseInt(id, 10);
      const idx = this.modulesSelected.findIndex(m => parseInt(m.id, 10) === n);
      if (idx < 0) return;
      const swap = idx + delta;
      if (swap < 0 || swap >= this.modulesSelected.length) return;
      [this.modulesSelected[idx], this.modulesSelected[swap]] = [this.modulesSelected[swap], this.modulesSelected[idx]];
      this.normalizePositions();
    },

    normalizePositions() {
      this.modulesSelected = this.modulesSelected.map((m, i) => ({ ...m, position: i + 1 }));
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

    addStudentRow() {
      const container = document.getElementById('new-students-tbody');
      if (!container) return;
      const i = this.nextNewStudentIndex++;
      const tr = document.createElement('tr');
      tr.className = 'border-t border-gray-100 flex flex-col sm:table-row bg-gray-50 sm:bg-transparent p-4 sm:p-0 rounded-lg mb-2 sm:mb-0';
      tr.innerHTML = `
        <td class='py-2 sm:py-3 pr-3 w-full sm:w-auto block sm:table-cell'>
          <input required name='stagiaires[${i}][prenom]' type='text' placeholder='Prénom'
            class='w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-2 sm:py-3 pr-3 w-full sm:w-auto block sm:table-cell'>
          <input required name='stagiaires[${i}][nom]' type='text' placeholder='Nom'
            class='w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-2 sm:py-3 pr-3 w-full sm:w-auto block sm:table-cell'>
          <input required name='stagiaires[${i}][email]' type='email' placeholder='Email'
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

@endsection