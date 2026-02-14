{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/lecture/edit_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')

@section('admin')
@php
    /**
     * LOGIQUE DE PERSISTANCE DU CHEMIN SCORM
     * 1. Priorité au chemin venant d'être flashé en session après un import réussi
     * 2. Sinon, lecture du chemin stocké en base de données
     */
    $displayScormPath = session('new_scorm_path') ?? $mlecture->scorm_path;
@endphp

<div class="w-full px-6 lg:px-8">
  <div class="max-w-[1100px] mx-auto my-6">
    <div class="bg-white rounded-[20px] shadow-soft p-6 border border-gray-100">

      {{-- Top Bar --}}
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between border-b border-gray-100 pb-4 mb-6">
        <div class="min-w-0">
          <h2 class="admin-page-title">Édition de la leçon</h2>
          <p class="text-sm text-gray-600">Configuration des contenus, objectifs et validations.</p>
        </div>

        <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}"
           class="btn-admin px-4 py-2 bg-white border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white">
          <i class="ti ti-arrow-left"></i>
          Retour
        </a>
      </div>

      {{-- Alertes de session --}}
      @if(session('success') || session('success_scorm_v2'))
        <div class="mb-6 rounded-xl border border-green-100 bg-green-50 p-4 text-sm text-green-800">
          {{ session('success') ?? session('success_scorm_v2') }}
        </div>
      @endif

      @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-800">
          {{ session('error') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-100 bg-red-50 p-4 text-sm text-red-800">
          <div class="font-semibold mb-2">Certaines informations sont incorrectes :</div>
          <ul class="list-disc ml-5 space-y-1">
            @foreach ($errors->all() as $message)
              <li>{{ $message }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Tabs Navigation --}}
      <div class="mb-10">
          <div class="inline-flex p-1.5 bg-gray-100 rounded-[22px] w-full sm:w-auto shadow-inner border border-gray-200/50">
              <button type="button"
                      class="tab-btn flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold rounded-[18px] transition-all duration-300 ease-out cursor-pointer"
                      data-tab="tab-contenu"
                      aria-controls="tab-contenu"
                      aria-selected="true">
                  <i class="ti ti-layout-columns text-lg"></i>
                  <span>Contenu</span>
              </button>

              <button type="button"
                      class="tab-btn flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold rounded-[18px] transition-all duration-300 ease-out cursor-pointer"
                      data-tab="tab-objectifs"
                      aria-controls="tab-objectifs"
                      aria-selected="false">
                  <i class="ti ti-target text-lg"></i>
                  <span>Objectifs</span>
              </button>

              <button type="button"
                      class="tab-btn flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold rounded-[18px] transition-all duration-300 ease-out cursor-pointer"
                      data-tab="tab-quiz"
                      aria-controls="tab-quiz"
                      aria-selected="false">
                  <i class="ti ti-checklist text-lg"></i>
                  <span>Quiz</span>
              </button>
          </div>
      </div>

      {{-- resources/views/admin/backend/modules/lecture/edit_module_lecture.blade.php --}}
<form method="POST" action="{{ route('admin.lectures.update') }}" id="main-lecture-form">
    @csrf
    <input type="hidden" name="id" value="{{ $mlecture->id }}">
    
    {{-- AJOUTEZ CETTE LIGNE : Elle préserve le chemin lors de l'enregistrement global --}}
    <input type="hidden" name="scorm_path" value="{{ $mlecture->scorm_path }}">

        {{-- Panel 1: Contenu (titre + SCORM) --}}
        <section id="tab-contenu" class="tab-panel" role="tabpanel" aria-labelledby="tab-contenu">
          <div class="space-y-6">

        
            {{-- Titre et Durée --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                {{-- Colonne 1 : Titre (prend 75% de la largeur) --}}
                <div class="md:col-span-3">
                    <label for="lecture_title" class="block text-sm font-extrabold text-bleuone uppercase mb-2">
                        Titre de la leçon
                    </label>
                    <input
                        type="text"
                        name="lecture_title"
                        id="lecture_title"
                        value="{{ old('lecture_title', $mlecture->lecture_title) }}"
                        class="w-full px-5 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-gray-900 font-semibold
                              focus:outline-none focus:ring-2 focus:ring-orangeone/40 focus:border-orangeone transition"
                        required
                    >
                </div>

                {{-- Colonne 2 : Durée (prend 25% de la largeur) --}}
                <div class="md:col-span-1">
                    <label for="duration" class="block text-sm font-extrabold text-bleuone uppercase mb-2">
                        <i class="ti ti-clock"></i> Durée (min)
                    </label>
                    <input
                        type="number"
                        name="duration"
                        id="duration"
                        value="{{ old('duration', $mlecture->duration) }}"
                        min="0"
                        placeholder="Ex: 10"
                        class="w-full px-5 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-gray-900 font-semibold
                              focus:outline-none focus:ring-2 focus:ring-orangeone/40 focus:border-orangeone transition text-center"
                    >
                </div>

            </div>

            {{-- BLOC SCORM --}}
            <div class="admin-card p-6">
              <div class="admin-card-title mb-4">
                <i class="ti ti-world"></i>
                Module interactif (SCORM)
              </div>

              <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 mb-6">
                <label class="block text-sm font-bold text-bleuone mb-2 uppercase tracking-wide">État du contenu</label>
                
                @if($displayScormPath)
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="ti ti-check mr-1"></i> Contenu prêt
                            </span>
                            <p class="text-[11px] text-gray-500 font-mono mt-1 break-all">
                                public/{{ $displayScormPath }}
                            </p>
                        </div>
                        
                        {{-- Prévisualisation Admin via LectureController@showScorm --}}
                        <a href="{{ route('lecture.scorm', ['id' => $mlecture->id]) }}" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-bleuone text-white rounded-lg hover:bg-opacity-90 transition shadow-sm text-sm">
                            <i class="ti ti-eye"></i>
                            Visualiser
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-cloud-upload text-3xl text-gray-300"></i>
                        <p class="text-sm text-gray-500 mt-1">Aucun fichier SCORM détecté.</p>
                    </div>
                @endif
              </div>

              {{-- Import ZIP --}}
              <div class="rounded-2xl border border-dashed border-gray-200 bg-white/40 p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center">
                  <div>
                    <input type="file" name="zip" accept=".zip" form="form-import-scorm"
                        class="block w-full text-sm text-gray-600
                                file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                file:bg-orangeone file:text-white hover:file:bg-orangeone-hover transition"
                        required>
                  </div>
                  <div class="flex md:justify-end">
                    <button type="submit" form="form-import-scorm"
                            class="btn-admin px-6 py-2 bg-white border border-orangeone/30 text-orangeone hover:bg-orangeone hover:text-white text-sm">
                      Mettre à jour le ZIP
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- Panel 2: Objectifs --}}
        <section id="tab-objectifs" class="tab-panel hidden" role="tabpanel" aria-labelledby="tab-objectifs">
          <div class="bg-white rounded-[20px] shadow-soft p-6 border border-gray-100">
            <div class="flex items-start justify-between gap-3 mb-4">
              <div>
                <h3 class="text-lg font-semibold text-bleuone">Objectifs de la leçon</h3>
                <p class="text-sm text-gray-600 mt-1">Ajoute un ou plusieurs objectifs liés à cette leçon.</p>
              </div>

              <button type="button" id="add-objective"
                      class="btn-admin px-4 py-2 bg-white border border-orangeone/30 text-orangeone hover:bg-orangeone hover:text-white text-sm">
                <i class="ti ti-plus"></i> Ajouter
              </button>
            </div>

            <div id="objectives-list" class="space-y-3">
              @php $objectives = $mlecture->objectives ?? collect(); @endphp
              @forelse($objectives as $i => $obj)
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4" data-row>
                  <input type="hidden" name="objectives[{{ $i }}][id]" value="{{ $obj->id }}">
                  <input type="hidden" name="objectives[{{ $i }}][_delete]" value="0">

                  <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 md:col-span-7">
                      <label class="block text-sm font-medium text-gray-700">Objectif</label>
                      <input type="text" name="objectives[{{ $i }}][title]" value="{{ old("objectives.$i.title", $obj->title) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orangeone/40 focus:border-orangeone transition">
                    </div>
                    <div class="col-span-6 md:col-span-3">
                      <label class="block text-sm font-medium text-gray-700">Position</label>
                      <input type="number" name="objectives[{{ $i }}][position]" value="{{ old("objectives.$i.position", $obj->position) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orangeone/40 focus:border-orangeone transition" min="1">
                    </div>
                    <div class="col-span-6 md:col-span-2 flex items-end">
                      <button type="button" class="btn-admin w-full px-3 py-2 bg-white border border-red-200 text-red-700 hover:bg-red-600 hover:text-white text-sm" data-remove>
                        <i class="ti ti-trash"></i> Supprimer
                      </button>
                    </div>
                    <div class="col-span-12">
                      <label class="block text-sm font-medium text-gray-700">Description (optionnel)</label>
                      <textarea name="objectives[{{ $i }}][description]" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orangeone/40 focus:border-orangeone transition" rows="2">{{ old("objectives.$i.description", $obj->description) }}</textarea>
                    </div>
                    <div class="col-span-12">
                      <label class="block text-sm font-medium text-gray-700">Compétences liées (optionnel)</label>
                      @php
                        $selectedCompetencyIds = old("objectives.$i.competency_ids", $obj->competencies?->pluck('id')->all() ?? []);
                      @endphp
                      <select name="objectives[{{ $i }}][competency_ids][]" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-orangeone/40 focus:border-orangeone transition" multiple size="6">
                        @foreach($competencies as $c)
                          <option value="{{ $c->id }}" {{ in_array($c->id, $selectedCompetencyIds, true) ? 'selected' : '' }}>
                            {{ $c->code ? $c->code.' — ' : '' }}{{ $c->label }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
              @empty
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">Aucun objectif pour le moment.</div>
              @endforelse
            </div>
          </div>
        </section>

        {{-- Panel 3: Quiz --}}
        <section id="tab-quiz" class="tab-panel hidden" role="tabpanel" aria-labelledby="tab-quiz">
          <div class="admin-card p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div class="admin-card-title">
                <i class="ti ti-checklist"></i> Validation quiz
              </div>
              <a href="{{ route('admin.quiz.questions.index', ['lecture' => $mlecture->id]) }}"
                 class="btn-admin px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm">
                Gérer les questions
              </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white/60 p-4 rounded-2xl border border-gray-100">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="quiz_enabled" value="0">
                <input type="checkbox" name="quiz_enabled" value="1"
                       {{ old('quiz_enabled', $mlecture->quiz_enabled) ? 'checked' : '' }}
                       class="h-6 w-6 rounded-lg text-orangeone border-gray-300 focus:ring-orangeone/40 transition">
                <div>
                  <div class="font-semibold text-gray-900">Activer le quiz</div>
                  <div class="text-xs text-gray-600">Obligatoire pour valider la leçon</div>
                </div>
              </label>

              <div>
                <label class="text-xs font-extrabold uppercase text-gray-500 block mb-1">Questions par tentative</label>
                <input type="number" name="quiz_questions_per_attempt" value="{{ old('quiz_questions_per_attempt', $mlecture->quiz_questions_per_attempt) }}" min="0"
                       class="w-full px-4 py-2 rounded-xl bg-white border border-gray-200 focus:ring-2 focus:ring-orangeone/40 focus:border-orangeone transition">
              </div>
            </div>
          </div>
        </section>

        {{-- Footer actions --}}
        <div class="flex flex-col md:flex-row md:justify-end gap-3 pt-4 border-t border-gray-100">
          <button type="submit" name="save_action" value="stay"
                  class="btn-admin w-full md:w-auto px-8 py-3 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
            Enregistrer
          </button>
          <button type="submit" name="save_action" value="back"
                  class="btn-admin w-full md:w-auto px-10 py-3 bg-orangeone text-white hover:bg-orangeone-hover">
            Enregistrer et quitter
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

{{-- FORM IMPORT SCORM (Indépendant) --}}
<form id="form-import-scorm"
      method="POST"
      action="{{ route('admin.scorm.import') }}"
      enctype="multipart/form-data"
      class="hidden">
  @csrf
  <input type="hidden" name="lecture_id" value="{{ $mlecture->id }}">
</form>

<script>
/**
 * GESTION DES ONGLETS
 */
(function () {
  const STORAGE_KEY = 'oneduc_admin_lecture_edit_tab';
  const tabButtons = Array.from(document.querySelectorAll('.tab-btn'));
  const panels = Array.from(document.querySelectorAll('.tab-panel'));

  function setActive(tabId) {
    tabButtons.forEach(btn => {
      const isActive = btn.dataset.tab === tabId;
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
      if (isActive) {
        btn.classList.add('bg-white', 'text-bleuone', 'shadow-sm', 'ring-1', 'ring-black/5');
        btn.classList.remove('text-gray-500');
      } else {
        btn.classList.remove('bg-white', 'text-bleuone', 'shadow-sm', 'ring-1', 'ring-black/5');
        btn.classList.add('text-gray-500');
      }
    });

    panels.forEach(p => {
      if (p.id === tabId) {
        p.classList.remove('hidden');
      } else {
        p.classList.add('hidden');
      }
    });
    localStorage.setItem(STORAGE_KEY, tabId);
  }

  tabButtons.forEach(btn => btn.addEventListener('click', () => setActive(btn.dataset.tab)));
  const saved = localStorage.getItem(STORAGE_KEY);
  setActive(saved && document.getElementById(saved) ? saved : 'tab-contenu');
})();

/**
 * GESTION DYNAMIQUE DES OBJECTIFS
 */
(function () {
    const list = document.getElementById('objectives-list');
    const btnAdd = document.getElementById('add-objective');
    if (!list || !btnAdd) return;

    const competencies = @json($competencies->map(fn($c) => ['id' => $c->id, 'label' => ($c->code ? ($c->code.' — ') : '').$c->label]));
    let index = list.querySelectorAll('[data-row]').length;

    function buildRow(i) {
        const wrapper = document.createElement('div');
        wrapper.className = 'rounded-xl border border-gray-200 bg-gray-50 p-4';
        wrapper.setAttribute('data-row', '1');
        const options = competencies.map(c => `<option value="${c.id}">${c.label}</option>`).join('');
        
        wrapper.innerHTML = `
            <input type="hidden" name="objectives[${i}][id]" value="">
            <input type="hidden" name="objectives[${i}][_delete]" value="0">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-12 md:col-span-7"><label class="block text-sm font-medium text-gray-700">Objectif</label><input type="text" name="objectives[${i}][title]" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div class="col-span-6 md:col-span-3"><label class="block text-sm font-medium text-gray-700">Position</label><input type="number" name="objectives[${i}][position]" value="${i+1}" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div class="col-span-6 md:col-span-2 flex items-end"><button type="button" class="btn-admin w-full px-3 py-2 bg-white text-red-700 border border-red-200" data-remove><i class="ti ti-trash"></i> Supprimer</button></div>
                <div class="col-span-12"><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="objectives[${i}][description]" class="mt-1 w-full rounded-lg border-gray-300" rows="2"></textarea></div>
                <div class="col-span-12"><label class="block text-sm font-medium text-gray-700">Compétences</label><select name="objectives[${i}][competency_ids][]" class="mt-1 w-full rounded-lg border-gray-300" multiple size="6">${options}</select></div>
            </div>`;
        return wrapper;
    }

    btnAdd.addEventListener('click', () => { list.appendChild(buildRow(index)); index++; });
    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove]');
        if (!btn) return;
        const row = btn.closest('[data-row]');
        const idInput = row.querySelector('input[name$="[id]"]');
        if (idInput && idInput.value) {
            row.querySelector('input[name$="[_delete]"]').value = "1";
            row.style.display = 'none';
        } else {
            row.remove();
        }
    });
})();
</script>
@endsection