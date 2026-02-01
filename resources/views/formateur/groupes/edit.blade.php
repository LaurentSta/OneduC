{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/groupes/edit.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')

@php
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

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-2">
          Mes groupes de formation
        </p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Modifier un groupe sans changer vos repères.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Vous pouvez ajuster la configuration, le parcours (modules) et la liste des stagiaires.
        </p>

        {{-- Pastilles --}}
        <div class="flex flex-wrap items-center gap-2 mb-3">
          <span class="px-3 py-1 bg-bleuone text-white text-xs font-bold rounded-full">
            {{ $group->name }}
          </span>
          <span class="px-3 py-1 bg-vertone/10 text-vertone text-xs font-bold rounded-full border border-vertone/20">
            {{ $group->students->count() }} stagiaire{{ $group->students->count() > 1 ? 's' : '' }}
          </span>
          <span class="px-3 py-1 bg-orangeone/10 text-orangeone text-xs font-bold rounded-full border border-orangeone/20">
            {{ count($selectedModuleIds) }} module{{ count($selectedModuleIds) > 1 ? 's' : '' }} actif{{ count($selectedModuleIds) > 1 ? 's' : '' }}
          </span>
        </div>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.groupes.index') }}" class="hover:underline text-bleuone">Mes groupes</a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Modifier</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Groupes.svg') }}"
             alt="Illustration des groupes de formation"
             class="max-w-[256px] h-auto">
      </div>

    </div>
  </header>

  {{-- CONTENU --}}
  <main
  x-data="groupEdit()"
  data-next-index="{{ max(0, (int) $group->students->count()) }}"
  data-selected-modules='@json($sortedSelected)'
  class="space-y-6"
>

    <form method="POST" action="{{ route('formateur.groupes.update', $group->id) }}" class="space-y-6">
      @csrf
      @method('PUT')
      {{-- Onglets --}}
      <div class="bg-white rounded-[20px] shadow-md p-6">
        <div class="border-b border-gray-100">
          <nav class="-mb-px flex gap-6" aria-label="Onglets">
            <button type="button"
                    @click="activeTab='general'"
                    :class="activeTab==='general' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                    class="whitespace-nowrap border-b-2 px-1 pb-3 font-varela font-semibold">
              Configuration générale
            </button>

            <button type="button"
                    @click="activeTab='parcours'"
                    :class="activeTab==='parcours' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                    class="whitespace-nowrap border-b-2 px-1 pb-3 font-varela font-semibold">
              Parcours de formation
            </button>

            <button type="button"
                    @click="activeTab='stagiaires'"
                    :class="activeTab==='stagiaires' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                    class="whitespace-nowrap border-b-2 px-1 pb-3 font-varela font-semibold">
              Liste des stagiaires
            </button>
          </nav>
        </div>

        {{-- ONGLET 1 : Configuration générale --}}
        <section x-show="activeTab==='general'" x-cloak class="pt-6">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <tbody class="divide-y divide-gray-100">
                <tr>
                  <th class="py-4 pr-4 text-left align-top w-[260px] text-bleuone font-bold">
                    Nom du groupe <span class="text-orangeone">*</span>
                  </th>
                  <td class="py-4">
                    <input id="nom" type="text" name="nom" value="{{ old('nom', $group->name) }}" required
                      class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-orangeone focus:border-orangeone">
                    <p class="text-xs text-gray-500 mt-1">Nom visible par vous et vos stagiaires.</p>
                  </td>
                </tr>

                <tr>
                  <th class="py-4 pr-4 text-left align-top text-bleuone font-bold">
                    Description
                  </th>
                  <td class="py-4">
                    <textarea id="description" name="description" rows="4"
                      class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-orangeone focus:border-orangeone"
                      placeholder="Objectifs du groupe, public, période…">{{ old('description', $group->description) }}</textarea>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        {{-- ONGLET 2 : Parcours de formation (ajout + ordre global) --}}
        <section x-show="activeTab==='parcours'" x-cloak class="pt-6">

         

          {{-- Ajouter un module --}}
          <div class="bg-gray-50 border border-gray-100 rounded-[16px] p-4 mb-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
              <div class="min-w-0">
                <p class="font-varela font-semibold text-bleuone">Ajouter un module au groupe</p>
                <p class="text-xs text-gray-500 font-lisible">
                  Sélectionnez un module dans la liste, puis cliquez sur “Ajouter”.
                </p>
              </div>

              <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <select x-model="newModuleId"
                        class="w-full sm:w-[380px] bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:ring-orangeone focus:border-orangeone">
                  <option value="">— Sélectionner un module —</option>
                  @foreach($modules as $m)
                    <option value="{{ $m->id }}">{{ $m->module_title }}</option>
                  @endforeach
                </select>

                <button type="button"
                        class="btn-oneduc"
                        :disabled="!newModuleId"
                        @click="addModuleFromSelect()">
                  Ajouter
                </button>
              </div>
            </div>

            <div x-show="addError" x-cloak class="mt-3 text-xs text-orangeone font-bold">
              <span x-text="addError"></span>
            </div>
          </div>

          {{-- Tableau des modules sélectionnés (ordre global) --}}
          <div class="flex items-center justify-between gap-3 mb-3">
            <p class="font-lisible text-gray-700">Ordre pédagogique du parcours (global au groupe).</p>
            <div class="text-sm font-bold text-bleuone">
              Modules actifs : <span class="text-orangeone" x-text="modulesSelected.length"></span>
            </div>
          </div>

          <div class="overflow-x-auto border border-gray-100 rounded-[16px]">
            <table class="min-w-[900px] w-full text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left text-xs uppercase tracking-widest text-gray-500">
                  <th class="py-3 px-4 w-[90px]">Ordre</th>
                  <th class="py-3 px-4">Module</th>
                  <th class="py-3 px-4 text-right w-[420px]">Actions</th>
                </tr>
              </thead>

              <tbody class="bg-white">
                <template x-for="(m, idx) in modulesSelected" :key="m.id">
                  <tr class="border-t border-gray-100">
                    <td class="py-3 px-4">
                      <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-bleuone/10 text-bleuone font-bold">
                        <span x-text="m.position"></span>
                      </span>

                      {{-- Champs envoyés au contrôleur --}}
                      <input type="hidden" name="modules[]" :value="m.id">
                      <input type="hidden" :name="'module_positions['+m.id+']'" :value="m.position">
                    </td>

                    <td class="py-3 px-4">
                      <div class="font-bold text-bleuone" x-text="m.title"></div>
                      <p class="text-xs text-gray-500" x-show="!m.persisted" x-cloak>
                        Ajouté — enregistrez pour gérer les leçons
                      </p>
                    </td>

                    <td class="py-3 px-4 text-right">
                      <div class="inline-flex items-center gap-2 flex-wrap justify-end">

                        <!-- Gérer les leçons -->
                        <template x-if="m.persisted">
                          <a
                            :href="m.manage_url"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                                  border-2 border-bleuone text-bleuone font-bold text-xs
                                  hover:bg-bleuone hover:text-white transition"
                          >
                            Gérer les leçons
                          </a>
                        </template>


                        <!-- Monter -->
                        <button type="button"
                          class="px-3 py-2 rounded-lg border-2 border-bleuone text-bleuone font-bold text-xs
                                hover:bg-bleuone hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed"
                          :disabled="idx === 0"
                          @click="moveModule(m.id, -1)">
                          Monter
                        </button>

                        <!-- Descendre -->
                        <button type="button"
                          class="px-3 py-2 rounded-lg border-2 border-bleuone text-bleuone font-bold text-xs
                                hover:bg-bleuone hover:text-white transition disabled:opacity-40 disabled:cursor-not-allowed"
                          :disabled="idx === modulesSelected.length - 1"
                          @click="moveModule(m.id, +1)">
                          Descendre
                        </button>

                        <!-- Retirer -->
                        <button type="button"
                          class="px-3 py-2 rounded-lg border-2 border-orangeone text-orangeone font-bold text-xs
                                hover:bg-orangeone hover:text-white transition"
                          @click="removeModule(m.id)">
                          Retirer
                        </button>

                      </div>
                    </td>

                  </tr>
                </template>

                <tr x-show="modulesSelected.length === 0">
                  <td colspan="3" class="py-8 px-4 text-center text-gray-400 italic">
                    Aucun module n’est associé au groupe.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <p class="text-xs text-gray-500 mt-3 font-lisible">
            L’ordre est enregistré au clic sur “Mettre à jour le groupe”.
          </p>
        </section>


        {{-- ONGLET 3 : Stagiaires --}}
        <section x-show="activeTab==='stagiaires'" x-cloak class="pt-6">
          <div id="remove-students-hidden"></div>

          <div class="flex items-start justify-between gap-4 mb-4">
            <p class="font-lisible text-gray-700">
              Retirez un stagiaire du groupe ou ajoutez-en de nouveaux.
            </p>

            <div id="removed-recap" class="hidden text-xs text-orangeone font-bold">
              Stagiaires à retirer à l’enregistrement :
              <ul id="removed-recap-list" class="mt-2 list-disc list-inside font-lisible text-orangeone/80"></ul>
            </div>
          </div>

          {{-- Table stagiaires existants --}}
          <div class="overflow-x-auto border border-gray-100 rounded-[16px] mb-6">
            <table class="min-w-[820px] w-full text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left text-xs uppercase tracking-widest text-gray-500">
                  <th class="py-3 px-4">Nom</th>
                  <th class="py-3 px-4">Prénom</th>
                  <th class="py-3 px-4">Email</th>
                  <th class="py-3 px-4 text-right w-[140px]">Action</th>
                </tr>
              </thead>

              <tbody class="bg-white">
                @forelse($group->students as $stagiaire)
                  <tr class="border-t border-gray-100" data-student-row="{{ $stagiaire->id }}">
                    <td class="py-3 px-4 font-bold text-bleuone">{{ $stagiaire->name }}</td>
                    <td class="py-3 px-4 text-gray-700">{{ $stagiaire->prenom }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ $stagiaire->email }}</td>
                    <td class="py-3 px-4 text-right">
                      <button type="button"
                              @click="toggleRemove({{ $stagiaire->id }}, '{{ addslashes($stagiaire->prenom.' '.$stagiaire->name) }}')"
                              class="text-orangeone font-bold text-xs hover:underline">
                        Retirer
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="py-6 px-4 text-center text-gray-400 italic">
                      Aucun stagiaire enregistré.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Table nouveaux stagiaires --}}
          <div class="bg-gray-50 border border-dashed border-gray-200 rounded-[20px] p-6">
            <div class="flex items-center justify-between gap-4 mb-3">
              <div>
                <p class="font-bold text-bleuone">Ajouter des stagiaires</p>
                <p class="text-xs text-gray-500 font-lisible">Ils seront créés à l’enregistrement.</p>
              </div>
              <button type="button"
                      @click="addStudentRow()"
                      class="btn-oneduc">
                + Ajouter une ligne
              </button>
            </div>

            <div class="overflow-x-auto bg-white rounded-[16px] border border-gray-100">
              <table class="min-w-[820px] w-full text-sm">
                <thead class="bg-gray-50">
                  <tr class="text-left text-xs uppercase tracking-widest text-gray-500">
                    <th class="py-3 px-4">Prénom</th>
                    <th class="py-3 px-4">Nom</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4 text-right w-[140px]">Action</th>
                  </tr>
                </thead>
                <tbody id="new-students-tbody" class="bg-white">
                  {{-- lignes ajoutées en JS --}}
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>

      {{-- Barre d’actions --}}
      <div class="bg-white rounded-[20px] shadow-md p-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('formateur.groupes.index') }}"
           class="text-bleuone font-bold hover:underline">
          Retour à mes groupes
        </a>

        <div class="flex items-center gap-3">
          <a href="{{ route('formateur.groupes.index') }}"
             class="btn-oneduc bg-white border-bleuone text-bleuone hover:bg-bleuone hover:text-white">
            Annuler
          </a>

          <button type="submit" class="btn-oneduc">
            Mettre à jour le groupe
          </button>
        </div>
      </div>

    </form>

  </main>
</div>

<style>
  [x-cloak] { display: none !important; }
</style>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('groupEdit', () => ({
    activeTab: 'general',

    // Stagiaires
    removed: [],
    nextNewStudentIndex: 0,

    // Modules (parcours)
    newModuleId: '',
    addError: '',
    modulesSelected: [],

    init() {
      this.nextNewStudentIndex = parseInt(this.$el.dataset.nextIndex || '0', 10);

      let parsed = [];
      try {
        parsed = JSON.parse(this.$el.dataset.selectedModules || '[]');
        if (!Array.isArray(parsed)) parsed = [];
      } catch (e) {
        parsed = [];
      }

      this.modulesSelected = parsed.map((m) => ({
        id: parseInt(m.id, 10),
        title: String(m.title ?? '').trim() || `Module #${m.id}`,
        position: parseInt(m.position ?? 0, 10) || 0,
        persisted: m.persisted === false ? false : true,
        manage_url: String(m.manage_url ?? '')
      }));

      this.normalizePositions();
    },

    // ----- Modules : ajout / retrait / ordre -----
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
        id,
        title,
        position: this.modulesSelected.length + 1,
        persisted: false,
        manage_url: '' // normal : pas encore en base
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
      this.modulesSelected = this.modulesSelected.map((m, i) => ({
        ...m,
        position: i + 1
      }));
    },

    // ----- Stagiaires : retirer / ajouter -----
    toggleRemove(id, name) {
      const n = parseInt(id, 10);
      if (!n) return;
      if (this.removed.includes(n)) return;

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
      tr.className = 'border-t border-gray-100';

      tr.innerHTML = `
        <td class='py-3 pr-3'>
          <input required name='stagiaires[${i}][prenom]' type='text' placeholder='Prénom'
            class='w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-3 pr-3'>
          <input required name='stagiaires[${i}][nom]' type='text' placeholder='Nom'
            class='w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-3 pr-3'>
          <input required name='stagiaires[${i}][email]' type='email' placeholder='Email'
            class='w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orangeone focus:border-orangeone'>
        </td>
        <td class='py-3 text-right'>
          <button type='button'
            class='text-orangeone font-bold text-sm hover:underline'
            aria-label='Retirer la ligne'
            onclick="this.closest('tr').remove()">
            Retirer
          </button>
        </td>
      `;

      container.appendChild(tr);
    }
  }));
});
</script>

@endsection
