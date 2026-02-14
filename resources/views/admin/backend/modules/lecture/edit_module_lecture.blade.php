{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/lecture/edit_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')

@section('admin')
@php
    $displayScormPath = session('new_scorm_path') ?? $mlecture->scorm_path;
@endphp

<div class="w-full px-6 lg:px-8 font-sans">
    
    {{-- 1. Fil d'Ariane --}}
    <nav class="flex mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('admin.modules') }}" class="hover:text-orangeone flex items-center">
                    <i class="ti ti-folders mr-1 text-sm"></i> Modules
                </a>
            </li>
            <li>
                <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}" class="hover:text-orangeone flex items-center">
                    <i class="ti ti-chevron-right mx-1"></i> Structure
                </a>
            </li>
            <li class="flex items-center">
                <i class="ti ti-chevron-right mx-1"></i>
                <span class="text-bleuone truncate max-w-xs">Édition : {{ $mlecture->lecture_title }}</span>
            </li>
        </ol>
    </nav>

    {{-- En-tête Administratif --}}
    <div class="flex justify-between items-end mb-6 border-b-2 border-bleuone pb-2">
        <div>
            <h1 class="text-xl font-bold text-bleuone uppercase tracking-tight">Paramétrage de l'unité</h1>
            <p class="text-gray-500 text-[10px] italic">Configuration technique, pédagogique et évaluation</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}" 
               class="text-[10px] font-bold bg-gray-100 px-3 py-1 rounded border border-gray-300 hover:bg-gray-200 transition uppercase flex items-center gap-1">
                <i class="ti ti-arrow-back-up"></i> Retour structure
            </a>
        </div>
    </div>

    {{-- Alertes --}}
    @if(session('success') || session('success_scorm_v2'))
        <div class="mb-4 rounded-sm border-l-4 border-green-500 bg-green-50 p-3 text-xs text-green-800 font-medium">
            <i class="ti ti-check mr-1"></i> {{ session('success') ?? session('success_scorm_v2') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-sm border-l-4 border-red-500 bg-red-50 p-3 text-xs text-red-800 font-medium">
            <i class="ti ti-alert-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Navigation des Onglets (Style Menu Admin) --}}
    <div class="mb-6 border-b border-gray-300">
        <div class="flex space-x-1">
            <button type="button" class="tab-btn px-4 py-2 text-xs font-bold uppercase tracking-wide border-t border-l border-r border-gray-300 rounded-t bg-white text-bleuone relative top-[1px]" 
                    data-tab="tab-contenu">
                <i class="ti ti-layout-columns mr-1"></i> Contenu & Média
            </button>
            <button type="button" class="tab-btn px-4 py-2 text-xs font-bold uppercase tracking-wide border-b border-gray-300 text-gray-500 hover:text-bleuone hover:bg-gray-50" 
                    data-tab="tab-objectifs">
                <i class="ti ti-target mr-1"></i> Objectifs
            </button>
            <button type="button" class="tab-btn px-4 py-2 text-xs font-bold uppercase tracking-wide border-b border-gray-300 text-gray-500 hover:text-bleuone hover:bg-gray-50" 
                    data-tab="tab-quiz">
                <i class="ti ti-checklist mr-1"></i> Validation Quiz
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.lectures.update') }}" id="main-lecture-form" class="space-y-6">
        @csrf
        <input type="hidden" name="id" value="{{ $mlecture->id }}">
        <input type="hidden" name="scorm_path" value="{{ $mlecture->scorm_path }}">

        {{-- ONGLET 1 : CONTENU --}}
        <section id="tab-contenu" class="tab-panel space-y-4">
            
            {{-- Info Base --}}
            <div class="bg-white p-4 border border-gray-300 rounded shadow-sm">
                <h3 class="text-xs font-bold text-gray-500 uppercase border-b border-gray-100 pb-2 mb-3">Informations Générales</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-bleuone uppercase mb-1">Titre de la leçon</label>
                        <input type="text" name="lecture_title" value="{{ old('lecture_title', $mlecture->lecture_title) }}"
                               class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-bleuone outline-none shadow-sm font-semibold text-gray-700" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-bleuone uppercase mb-1">Durée (min)</label>
                        <input type="number" name="duration" value="{{ old('duration', $mlecture->duration) }}" min="0"
                               class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-bleuone outline-none shadow-sm text-center">
                    </div>
                </div>
            </div>

            {{-- Bloc SCORM --}}
            <div class="bg-gray-50 p-4 border border-gray-300 rounded shadow-sm">
                <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase"><i class="ti ti-package"></i> Ressource SCORM (Interactif)</h3>
                    @if($displayScormPath)
                        <span class="px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-bold uppercase rounded border border-green-200">Actif</span>
                    @else
                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-[10px] font-bold uppercase rounded border border-gray-300">Aucun</span>
                    @endif
                </div>

                <div class="flex flex-col md:flex-row gap-4 items-start">
                    <div class="flex-1 text-xs text-gray-600">
                        @if($displayScormPath)
                            <div class="mb-2 font-mono bg-white px-2 py-1 border border-gray-200 rounded truncate text-[10px] text-gray-500">
                                Path: public/{{ $displayScormPath }}
                            </div>
                            <a href="{{ route('lecture.scorm', ['id' => $mlecture->id]) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1 bg-bleuone text-white text-[10px] font-bold uppercase rounded hover:bg-opacity-90 transition">
                                <i class="ti ti-eye"></i> Prévisualiser le module
                            </a>
                        @else
                            <p class="italic text-gray-400">Aucun paquet SCORM n'est associé à cette leçon.</p>
                        @endif
                    </div>
                    
                    {{-- Zone Upload Compacte --}}
                    <div class="w-full md:w-1/2 bg-white p-3 border border-dashed border-gray-300 rounded">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Mise à jour du fichier (.zip)</label>
                        <div class="flex gap-2">
                            <input type="file" name="zip" accept=".zip" form="form-import-scorm"
                                   class="block w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-sm file:border-0 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 cursor-pointer">
                            <button type="submit" form="form-import-scorm"
                                    class="px-3 py-1 bg-orangeone text-white text-[10px] font-bold uppercase rounded hover:bg-orangeone-hover transition whitespace-nowrap">
                                <i class="ti ti-upload"></i> Envoyer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ONGLET 2 : OBJECTIFS --}}
        <section id="tab-objectifs" class="tab-panel hidden space-y-4">
            <div class="bg-white p-4 border border-gray-300 rounded shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-bold text-gray-500 uppercase">Liste des objectifs pédagogiques</h3>
                    <button type="button" id="add-objective" class="px-3 py-1 bg-gray-100 border border-gray-300 text-bleuone text-[10px] font-bold uppercase rounded hover:bg-white hover:border-bleuone transition">
                        <i class="ti ti-plus"></i> Ajouter une ligne
                    </button>
                </div>

                <div id="objectives-list" class="space-y-2">
                    @php $objectives = $mlecture->objectives ?? collect(); @endphp
                    @forelse($objectives as $i => $obj)
                        <div class="p-3 border border-gray-200 bg-gray-50 rounded flex gap-3 items-start" data-row>
                            <input type="hidden" name="objectives[{{ $i }}][id]" value="{{ $obj->id }}">
                            <input type="hidden" name="objectives[{{ $i }}][_delete]" value="0">
                            
                            {{-- Position --}}
                            <div class="w-16">
                                <label class="block text-[9px] font-bold uppercase text-gray-400">Pos.</label>
                                <input type="number" name="objectives[{{ $i }}][position]" value="{{ old("objectives.$i.position", $obj->position) }}" 
                                       class="w-full text-center text-xs border border-gray-300 rounded py-1">
                            </div>
                            
                            {{-- Contenu --}}
                            <div class="flex-1 grid grid-cols-1 gap-2">
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400">Intitulé</label>
                                    <input type="text" name="objectives[{{ $i }}][title]" value="{{ old("objectives.$i.title", $obj->title) }}" 
                                           class="w-full text-xs border border-gray-300 rounded py-1 px-2 font-medium">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-gray-400">Compétences</label>
                                    @php $selectedCompetencyIds = old("objectives.$i.competency_ids", $obj->competencies?->pluck('id')->all() ?? []); @endphp
                                    <select name="objectives[{{ $i }}][competency_ids][]" class="w-full text-[10px] border border-gray-300 rounded py-1 h-16" multiple>
                                        @foreach($competencies as $c)
                                            <option value="{{ $c->id }}" {{ in_array($c->id, $selectedCompetencyIds) ? 'selected' : '' }}>{{ $c->code }} - {{ $c->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-transparent">.</label>
                                <button type="button" class="text-red-500 hover:text-red-700 p-1" data-remove title="Supprimer">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-xs text-gray-400 italic border border-dashed border-gray-300 rounded">Aucun objectif défini.</div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- ONGLET 3 : QUIZ --}}
        <section id="tab-quiz" class="tab-panel hidden space-y-4">
            <div class="bg-white p-4 border border-gray-300 rounded shadow-sm flex flex-col gap-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                    <h3 class="text-xs font-bold text-gray-500 uppercase">Paramètres d'évaluation</h3>
                    <a href="{{ route('admin.quiz.questions.index', ['lecture' => $mlecture->id]) }}" class="text-[10px] font-bold text-bleuone uppercase hover:underline">
                        <i class="ti ti-external-link"></i> Accéder à la banque de questions
                    </a>
                </div>

                <div class="flex items-center gap-4 bg-orangeone/5 p-3 rounded border border-orangeone/10">
                    <div class="flex items-center h-5">
                        <input type="hidden" name="quiz_enabled" value="0">
                        <input type="checkbox" name="quiz_enabled" value="1" {{ old('quiz_enabled', $mlecture->quiz_enabled) ? 'checked' : '' }}
                               class="h-4 w-4 text-orangeone border-gray-300 rounded focus:ring-orangeone">
                    </div>
                    <div class="flex-1">
                        <label class="text-xs font-bold text-gray-700 uppercase">Activer le Quiz de validation</label>
                        <p class="text-[10px] text-gray-500">Si coché, l'apprenant devra réussir le quiz pour valider cette leçon.</p>
                    </div>
                    <div class="w-32">
                        <label class="block text-[9px] font-bold text-gray-400 uppercase mb-1">Nb. Questions / Tentative</label>
                        <input type="number" name="quiz_questions_per_attempt" value="{{ old('quiz_questions_per_attempt', $mlecture->quiz_questions_per_attempt) }}" min="0"
                               class="w-full text-center text-xs border border-gray-300 rounded py-1">
                    </div>
                </div>
            </div>
        </section>

        {{-- Footer Actions --}}
        <div class="pt-4 border-t border-gray-300 flex justify-end gap-3">
            <button type="submit" name="save_action" value="stay" 
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-600 text-xs font-bold uppercase rounded hover:bg-gray-100 transition">
                Enregistrer (Rester)
            </button>
            <button type="submit" name="save_action" value="back" 
                    class="px-6 py-2 bg-bleuone text-white text-xs font-bold uppercase rounded hover:bg-bleuone/90 transition shadow-sm">
                Enregistrer & Quitter
            </button>
        </div>
    </form>
</div>

{{-- FORM IMPORT SCORM --}}
<form id="form-import-scorm" method="POST" action="{{ route('admin.scorm.import') }}" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="lecture_id" value="{{ $mlecture->id }}">
</form>

<script>
    // Tab Switching Logic (Persistant)
    (function () {
        const btns = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');
        const activeClass = ['border-t', 'border-l', 'border-r', 'border-gray-300', 'rounded-t', 'bg-white', 'text-bleuone', 'relative', 'top-[1px]'];
        const inactiveClass = ['border-b', 'border-gray-300', 'text-gray-500', 'hover:text-bleuone', 'hover:bg-gray-50'];

        function setTab(id) {
            btns.forEach(b => {
                if(b.dataset.tab === id) {
                    b.classList.add(...activeClass);
                    b.classList.remove(...inactiveClass);
                } else {
                    b.classList.remove(...activeClass);
                    b.classList.add(...inactiveClass);
                }
            });
            panels.forEach(p => p.id === id ? p.classList.remove('hidden') : p.classList.add('hidden'));
            localStorage.setItem('oneduc_lecture_tab', id);
        }

        btns.forEach(b => b.addEventListener('click', () => setTab(b.dataset.tab)));
        setTab(localStorage.getItem('oneduc_lecture_tab') || 'tab-contenu');
    })();

    // JS Objectifs (Simplifié)
    document.getElementById('add-objective')?.addEventListener('click', () => {
        const list = document.getElementById('objectives-list');
        const i = list.querySelectorAll('[data-row]').length;
        // ... (Logique identique à avant mais avec le nouveau HTML compact) ...
        // Note: Pour garder le code court ici, je n'ai pas remis tout le JS de génération de ligne 
        // mais il faudrait adapter le template string au nouveau design compact ci-dessus.
        alert('Fonctionnalité JS à reconnecter avec le nouveau template HTML'); 
    });
</script>

{{-- Réintégration du JS Objectifs complet --}}
<script>
(function () {
    const list = document.getElementById('objectives-list');
    const btn = document.getElementById('add-objective');
    if(!list || !btn) return;

    const competencies = @json($competencies->map(fn($c) => ['id' => $c->id, 'code' => $c->code, 'label' => $c->label]));

    btn.addEventListener('click', () => {
        const i = list.querySelectorAll('[data-row]').length;
        const div = document.createElement('div');
        div.className = 'p-3 border border-gray-200 bg-gray-50 rounded flex gap-3 items-start';
        div.setAttribute('data-row', '1');
        
        let opts = competencies.map(c => `<option value="${c.id}">${c.code} - ${c.label}</option>`).join('');

        div.innerHTML = `
            <input type="hidden" name="objectives[${i}][id]" value=""><input type="hidden" name="objectives[${i}][_delete]" value="0">
            <div class="w-16"><label class="block text-[9px] font-bold uppercase text-gray-400">Pos.</label><input type="number" name="objectives[${i}][position]" value="${i+1}" class="w-full text-center text-xs border border-gray-300 rounded py-1"></div>
            <div class="flex-1 grid grid-cols-1 gap-2">
                <div><label class="block text-[9px] font-bold uppercase text-gray-400">Intitulé</label><input type="text" name="objectives[${i}][title]" class="w-full text-xs border border-gray-300 rounded py-1 px-2 font-medium"></div>
                <div><label class="block text-[9px] font-bold uppercase text-gray-400">Compétences</label><select name="objectives[${i}][competency_ids][]" class="w-full text-[10px] border border-gray-300 rounded py-1 h-16" multiple>${opts}</select></div>
            </div>
            <div><label class="block text-[9px] font-bold uppercase text-transparent">.</label><button type="button" class="text-red-500 hover:text-red-700 p-1" data-remove><i class="ti ti-trash"></i></button></div>
        `;
        list.appendChild(div);
    });

    list.addEventListener('click', e => {
        const rBtn = e.target.closest('[data-remove]');
        if(!rBtn) return;
        const row = rBtn.closest('[data-row]');
        const idIn = row.querySelector('input[name$="[id]"]');
        if(idIn && idIn.value) {
            row.querySelector('input[name$="[_delete]"]').value = "1";
            row.style.display = 'none';
        } else { row.remove(); }
    });
})();
</script>
@endsection