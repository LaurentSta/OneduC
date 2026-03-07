{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/lecture/edit_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')

@section('admin')
@php
    $displayScormPath = session('new_scorm_path') ?? $mlecture->scorm_path;
    $contentType = old('content_type', $mlecture->content_type ?? 'scorm');
    $slidesStatus = $mlecture->slides_status ?? 'none';

    $slidesStatusStyles = [
        'none' => 'bg-gray-100 text-gray-600',
        'pending' => 'bg-amber-100 text-amber-800',
        'processing' => 'bg-blue-100 text-blue-800',
        'ready' => 'bg-green-100 text-green-800',
        'failed' => 'bg-red-100 text-red-800',
    ];

    $slidesStatusLabels = [
        'none' => 'Aucun',
        'pending' => 'En attente',
        'processing' => 'Conversion',
        'ready' => 'Pret',
        'failed' => 'Erreur',
    ];
@endphp

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        {{-- Fil d'Ariane --}}
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
                    <span class="text-bleuone truncate max-w-xs">Lecon #{{ $mlecture->id }}</span>
                </li>
            </ol>
        </nav>

        {{-- En-tête --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-5">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Parametrage de l'unite</h1>
                <p class="text-sm text-gray-600">
                    Regle le contenu pedagogique, les objectifs et la validation par quiz.
                </p>
            </div>
            <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 transition text-xs font-varela cursor-pointer">
                <i class="ti ti-arrow-back-up"></i>
                Retour structure
            </a>
        </div>

        {{-- Alertes --}}
        @if(session('success') || session('success_scorm_v2'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <i class="ti ti-check mr-1"></i> {{ session('success') ?? session('success_scorm_v2') }}
                @if(session('success_scorm_v2') && session('new_scorm_path'))
                    <div class="mt-1 font-mono text-xs text-green-700">
                        SCORM charge : public/{{ session('new_scorm_path') }}
                    </div>
                @endif
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <i class="ti ti-alert-circle mr-1"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->has('zip') || $errors->has('slides_file') || $errors->has('lecture_id'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @if($errors->has('zip'))
                    <div><i class="ti ti-alert-circle mr-1"></i> {{ $errors->first('zip') }}</div>
                @endif
                @if($errors->has('slides_file'))
                    <div><i class="ti ti-alert-circle mr-1"></i> {{ $errors->first('slides_file') }}</div>
                @endif
                @if($errors->has('lecture_id'))
                    <div><i class="ti ti-alert-circle mr-1"></i> {{ $errors->first('lecture_id') }}</div>
                @endif
            </div>
        @endif

        {{-- Navigation onglets --}}
        <div class="mb-5 border-b border-gray-200">
            <div class="flex flex-wrap gap-2 -mb-px">
                <button
                    type="button"
                    class="tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-varela rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-bleuone hover:bg-gray-50 transition"
                    data-tab="tab-contenu"
                >
                    <i class="ti ti-layout-columns"></i> Contenu et media
                </button>
                <button
                    type="button"
                    class="tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-varela rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-bleuone hover:bg-gray-50 transition"
                    data-tab="tab-objectifs"
                >
                    <i class="ti ti-target"></i> Objectifs
                </button>
                <button
                    type="button"
                    class="tab-btn inline-flex items-center gap-2 px-4 py-2 text-xs font-varela rounded-t-lg border-b-2 border-transparent text-gray-500 hover:text-bleuone hover:bg-gray-50 transition"
                    data-tab="tab-quiz"
                >
                    <i class="ti ti-checklist"></i> Validation quiz
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.lectures.update') }}" id="main-lecture-form" class="space-y-5">
            @csrf
            <input type="hidden" name="id" value="{{ $mlecture->id }}">
            <input type="hidden" name="scorm_path" value="{{ $mlecture->scorm_path }}">

            {{-- ONGLET 1 : CONTENU --}}
            <section id="tab-contenu" class="tab-panel space-y-4">
                <section class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Informations generales</h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Titre de la lecon</label>
                                <input
                                    type="text"
                                    name="lecture_title"
                                    value="{{ old('lecture_title', $mlecture->lecture_title) }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Duree (min)</label>
                                <input
                                    type="number"
                                    name="duration"
                                    value="{{ old('duration', $mlecture->duration) }}"
                                    min="0"
                                    class="w-full px-3 py-2 text-sm text-center border border-gray-300 rounded-lg focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none"
                                >
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Type de contenu diffuse</label>
                            <div class="flex flex-wrap items-center gap-3">
                                <input id="content-type-scorm" class="sr-only" type="radio" name="content_type" value="scorm" {{ $contentType === 'scorm' ? 'checked' : '' }}>
                                <input id="content-type-slides" class="sr-only" type="radio" name="content_type" value="slides" {{ $contentType === 'slides' ? 'checked' : '' }}>

                                <span id="content-type-label-scorm" class="inline-flex items-center gap-2 rounded-md px-2 py-1 text-xs font-semibold">
                                    <i class="ti ti-package text-sm"></i>
                                    SCORM
                                    <span id="content-type-state-scorm" class="rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wide">{{ $contentType === 'scorm' ? 'ACTIF' : 'INACTIF' }}</span>
                                </span>

                                <button
                                    type="button"
                                    id="content-type-switch"
                                    role="switch"
                                    aria-checked="{{ $contentType === 'slides' ? 'true' : 'false' }}"
                                    aria-label="Basculer le type de contenu"
                                    class="relative inline-flex h-7 w-14 items-center rounded-full border border-gray-300 bg-gray-200 transition"
                                >
                                    <span id="content-type-switch-thumb" class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"></span>
                                </button>

                                <span id="content-type-label-slides" class="inline-flex items-center gap-2 rounded-md px-2 py-1 text-xs font-semibold">
                                    <i class="ti ti-slideshow text-sm"></i>
                                    Slides
                                    <span id="content-type-state-slides" class="rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wide">{{ $contentType === 'slides' ? 'ACTIF' : 'INACTIF' }}</span>
                                </span>
                            </div>
                            <div class="mt-2 text-xs font-semibold text-gray-700" aria-live="polite">
                                Mode actif : <span id="content-type-active-label"></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Un seul mode actif a la fois pour eviter toute confusion.</p>
                        </div>
                    </div>
                </section>

                <section id="content-block-scorm" class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-2">
                        <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Ressource SCORM</h2>
                        @if($displayScormPath)
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                Actif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                                Aucun
                            </span>
                        @endif
                    </div>

                    <div class="p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            @if($displayScormPath)
                                <div class="font-mono text-xs bg-white px-3 py-2 border border-gray-200 rounded-lg truncate text-gray-600">
                                    Path: public/{{ $displayScormPath }}
                                </div>
                                <a href="{{ route('lecture.scorm', ['id' => $mlecture->id]) }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                    <i class="ti ti-eye"></i>
                                    Previsualiser le module
                                </a>
                            @else
                                <p class="text-sm text-gray-500 italic">Aucun paquet SCORM n'est associe a cette lecon.</p>
                            @endif
                        </div>

                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Mise a jour du fichier ZIP</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input
                                    type="file"
                                    name="zip"
                                    accept=".zip"
                                    form="form-import-scorm"
                                    class="block w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 cursor-pointer"
                                >
                                <button
                                    type="submit"
                                    form="form-import-scorm"
                                    id="btn-import-scorm"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-orangeone text-white text-xs font-varela rounded-lg hover:bg-orangeone-hover transition whitespace-nowrap"
                                >
                                    <i class="ti ti-upload"></i>
                                    Envoyer
                                </button>
                            </div>
                            @if(session('success_scorm_v2') && session('new_scorm_path'))
                                <p class="mt-2 text-xs text-green-700 font-semibold">Dernier import valide.</p>
                            @else
                                <p class="mt-2 text-xs text-gray-500">Un message de confirmation s'affichera apres chargement.</p>
                            @endif
                        </div>
                    </div>
                </section>

                <section id="content-block-slides" class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-2">
                        <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Support Slides (PPT/PDF)</h2>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $slidesStatusStyles[$slidesStatus] ?? $slidesStatusStyles['none'] }}">
                            {{ $slidesStatusLabels[$slidesStatus] ?? 'Inconnu' }}
                        </span>
                    </div>
                    <div class="p-4 space-y-3">
                        <p class="text-xs text-gray-600">
                            Formats acceptes: <strong>.ppt</strong>, <strong>.pptx</strong>, <strong>.pdf</strong>.
                            La conversion s'execute en arriere-plan.
                        </p>

                        @if(($mlecture->slides_status ?? null) === 'ready' && !empty($mlecture->slides_path))
                            <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-700">
                                Slides generees: <strong>{{ (int) ($mlecture->slide_count ?? 0) }}</strong>
                                @if($mlecture->slides_converted_at)
                                    · Derniere conversion: {{ $mlecture->slides_converted_at->format('d/m/Y H:i') }}
                                @endif
                            </div>
                            <a href="{{ route('lecture.slides', ['id' => $mlecture->id]) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                <i class="ti ti-eye"></i>
                                Previsualiser les slides
                            </a>
                        @endif

                        @if(($mlecture->slides_status ?? null) === 'failed' && !empty($mlecture->slides_error))
                            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                Erreur de conversion: {{ $mlecture->slides_error }}
                            </div>
                        @endif

                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-3">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Importer un support Slides</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input
                                    type="file"
                                    name="slides_file"
                                    accept=".ppt,.pptx,.pdf"
                                    form="form-import-slides"
                                    class="block w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 cursor-pointer"
                                >
                                <button
                                    type="submit"
                                    form="form-import-slides"
                                    id="btn-import-slides"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-orangeone text-white text-xs font-varela rounded-lg hover:bg-orangeone-hover transition whitespace-nowrap"
                                >
                                    <i class="ti ti-upload"></i>
                                    Convertir
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            {{-- ONGLET 2 : OBJECTIFS --}}
            <section id="tab-objectifs" class="tab-panel hidden space-y-4">
                <section class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Objectifs pedagogiques</h2>
                        <button
                            type="button"
                            id="add-objective"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-orange-200 text-orange-700 hover:bg-orangeone hover:text-white hover:border-orangeone transition text-xs font-varela cursor-pointer"
                        >
                            <i class="ti ti-plus"></i>
                            Ajouter une ligne
                        </button>
                    </div>

                    <div class="p-4 space-y-2" id="objectives-list">
                        @php $objectives = $mlecture->objectives ?? collect(); @endphp
                        @forelse($objectives as $i => $obj)
                            <div class="p-3 border border-gray-200 bg-gray-50 rounded-lg flex gap-3 items-start" data-row>
                                <input type="hidden" name="objectives[{{ $i }}][id]" value="{{ $obj->id }}">
                                <input type="hidden" name="objectives[{{ $i }}][_delete]" value="0">

                                <div class="w-16">
                                    <label class="block text-[10px] font-semibold uppercase text-gray-500 mb-1">Pos.</label>
                                    <input
                                        type="number"
                                        name="objectives[{{ $i }}][position]"
                                        value="{{ old("objectives.$i.position", $obj->position) }}"
                                        class="w-full text-center text-xs border border-gray-300 rounded-lg py-1"
                                    >
                                </div>

                                <div class="flex-1 grid grid-cols-1 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold uppercase text-gray-500 mb-1">Intitule</label>
                                        <input
                                            type="text"
                                            name="objectives[{{ $i }}][title]"
                                            value="{{ old("objectives.$i.title", $obj->title) }}"
                                            class="w-full text-xs border border-gray-300 rounded-lg py-2 px-2 font-medium"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold uppercase text-gray-500 mb-1">Competences</label>
                                        @php $selectedCompetencyIds = old("objectives.$i.competency_ids", $obj->competencies?->pluck('id')->all() ?? []); @endphp
                                        <select name="objectives[{{ $i }}][competency_ids][]" class="w-full text-xs border border-gray-300 rounded-lg py-1 h-20" multiple>
                                            @foreach($competencies as $c)
                                                <option value="{{ $c->id }}" {{ in_array($c->id, $selectedCompetencyIds) ? 'selected' : '' }}>
                                                    {{ $c->code }} - {{ $c->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-semibold uppercase text-transparent mb-1">.</label>
                                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition" data-remove title="Supprimer">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-sm text-gray-500 italic border border-dashed border-gray-300 rounded-lg bg-gray-50">
                                Aucun objectif defini.
                            </div>
                        @endforelse
                    </div>
                </section>
            </section>

            {{-- ONGLET 3 : QUIZ --}}
            <section id="tab-quiz" class="tab-panel hidden space-y-4">
                <section class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Parametres d'evaluation</h2>
                        <a href="{{ route('admin.quiz.questions.index', ['lecture' => $mlecture->id]) }}"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                            <i class="ti ti-external-link"></i>
                            Banque de questions
                        </a>
                    </div>

                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-center gap-4 bg-orange-50 border border-orange-100 rounded-lg p-4">
                            <div class="flex items-center h-5">
                                <input type="hidden" name="quiz_enabled" value="0">
                                <input
                                    type="checkbox"
                                    name="quiz_enabled"
                                    value="1"
                                    {{ old('quiz_enabled', $mlecture->quiz_enabled) ? 'checked' : '' }}
                                    class="h-4 w-4 text-orangeone border-gray-300 rounded focus:ring-orangeone"
                                >
                            </div>
                            <div class="flex-1">
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-700">Activer le quiz de validation</label>
                                <p class="text-xs text-gray-500 mt-1">
                                    Si coche, l'apprenant devra reussir le quiz pour valider cette lecon.
                                </p>
                            </div>
                            <div class="w-full md:w-44">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">
                                    Questions / tentative
                                </label>
                                <input
                                    type="number"
                                    name="quiz_questions_per_attempt"
                                    value="{{ old('quiz_questions_per_attempt', $mlecture->quiz_questions_per_attempt) }}"
                                    min="0"
                                    class="w-full text-center text-sm border border-gray-300 rounded-lg py-2 px-2 focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none"
                                >
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            {{-- Footer actions --}}
            <div class="pt-4 border-t border-gray-200 flex flex-wrap justify-end gap-3">
                <button
                    type="submit"
                    name="save_action"
                    value="stay"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-orange-200 text-orange-700 hover:bg-orangeone hover:text-white hover:border-orangeone transition text-xs font-varela cursor-pointer"
                >
                    <i class="ti ti-device-floppy"></i>
                    Enregistrer et rester
                </button>
                <button
                    type="submit"
                    name="save_action"
                    value="back"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-bleuone text-white text-xs font-varela rounded-lg hover:bg-bleuone/90 transition cursor-pointer"
                >
                    <i class="ti ti-check"></i>
                    Enregistrer et quitter
                </button>
            </div>
        </form>
    </div>
</div>

{{-- FORM IMPORT SCORM --}}
<form id="form-import-scorm" method="POST" action="{{ route('admin.scorm.import') }}" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="lecture_id" value="{{ $mlecture->id }}">
</form>

{{-- FORM IMPORT SLIDES --}}
<form id="form-import-slides" method="POST" action="{{ route('admin.slides.import') }}" enctype="multipart/form-data" class="hidden">
    @csrf
    <input type="hidden" name="lecture_id" value="{{ $mlecture->id }}">
</form>

<script>
    (function () {
        const radios = document.querySelectorAll('input[name="content_type"]');
        const scormBlock = document.getElementById('content-block-scorm');
        const slidesBlock = document.getElementById('content-block-slides');
        const switchButton = document.getElementById('content-type-switch');
        const switchThumb = document.getElementById('content-type-switch-thumb');
        const labelScorm = document.getElementById('content-type-label-scorm');
        const labelSlides = document.getElementById('content-type-label-slides');
        const stateScorm = document.getElementById('content-type-state-scorm');
        const stateSlides = document.getElementById('content-type-state-slides');
        const activeLabel = document.getElementById('content-type-active-label');
        const radioScorm = document.getElementById('content-type-scorm');
        const radioSlides = document.getElementById('content-type-slides');

        if (!radios.length || !scormBlock || !slidesBlock) return;

        const syncContentBlocks = () => {
            const checked = document.querySelector('input[name="content_type"]:checked');
            const value = checked ? checked.value : 'scorm';

            scormBlock.style.display = value === 'scorm' ? '' : 'none';
            slidesBlock.style.display = value === 'slides' ? '' : 'none';

            if (activeLabel) {
                activeLabel.textContent = value === 'scorm' ? 'SCORM' : 'Slides';
            }

            if (switchButton) {
                switchButton.setAttribute('aria-checked', value === 'slides' ? 'true' : 'false');
                switchButton.classList.toggle('bg-bleuone', value === 'scorm');
                switchButton.classList.toggle('bg-orangeone', value === 'slides');
                switchButton.classList.toggle('border-bleuone', value === 'scorm');
                switchButton.classList.toggle('border-orangeone', value === 'slides');
                switchButton.classList.remove('bg-gray-200');
            }

            if (switchThumb) {
                switchThumb.classList.toggle('translate-x-1', value === 'scorm');
                switchThumb.classList.toggle('translate-x-8', value === 'slides');
            }

            if (labelScorm) {
                labelScorm.classList.toggle('bg-bleuone/10', value === 'scorm');
                labelScorm.classList.toggle('text-bleuone', value === 'scorm');
                labelScorm.classList.toggle('text-gray-500', value !== 'scorm');
            }

            if (stateScorm) {
                stateScorm.textContent = value === 'scorm' ? 'ACTIF' : 'INACTIF';
                stateScorm.classList.toggle('bg-bleuone', value === 'scorm');
                stateScorm.classList.toggle('text-white', value === 'scorm');
                stateScorm.classList.toggle('bg-gray-200', value !== 'scorm');
                stateScorm.classList.toggle('text-gray-600', value !== 'scorm');
            }

            if (labelSlides) {
                labelSlides.classList.toggle('bg-orange-100', value === 'slides');
                labelSlides.classList.toggle('text-orange-700', value === 'slides');
                labelSlides.classList.toggle('text-gray-500', value !== 'slides');
            }

            if (stateSlides) {
                stateSlides.textContent = value === 'slides' ? 'ACTIF' : 'INACTIF';
                stateSlides.classList.toggle('bg-orangeone', value === 'slides');
                stateSlides.classList.toggle('text-white', value === 'slides');
                stateSlides.classList.toggle('bg-gray-200', value !== 'slides');
                stateSlides.classList.toggle('text-gray-600', value !== 'slides');
            }
        };

        radios.forEach((radio) => {
            radio.addEventListener('change', syncContentBlocks);
        });

        if (switchButton && radioScorm && radioSlides) {
            switchButton.addEventListener('click', () => {
                const checked = document.querySelector('input[name="content_type"]:checked');
                const value = checked ? checked.value : 'scorm';

                if (value === 'scorm') {
                    radioSlides.checked = true;
                } else {
                    radioScorm.checked = true;
                }

                syncContentBlocks();
            });
        }

        syncContentBlocks();
    })();

    (function () {
        const btns = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');
        const activeClass = ['text-bleuone', 'border-bleuone', 'bg-blue-50'];
        const inactiveClass = ['text-gray-500', 'border-transparent', 'bg-transparent', 'hover:text-bleuone', 'hover:bg-gray-50'];

        function setTab(id) {
            btns.forEach((b) => {
                if (b.dataset.tab === id) {
                    b.classList.add(...activeClass);
                    b.classList.remove(...inactiveClass);
                } else {
                    b.classList.remove(...activeClass);
                    b.classList.add(...inactiveClass);
                }
            });
            panels.forEach((p) => p.id === id ? p.classList.remove('hidden') : p.classList.add('hidden'));
            localStorage.setItem('oneduc_lecture_tab', id);
        }

        btns.forEach((b) => b.addEventListener('click', () => setTab(b.dataset.tab)));
        setTab(localStorage.getItem('oneduc_lecture_tab') || 'tab-contenu');
    })();

    (function () {
        const form = document.getElementById('form-import-scorm');
        const submitBtn = document.getElementById('btn-import-scorm');
        const zipInput = document.querySelector('input[name="zip"][form="form-import-scorm"]');

        if (!form || !submitBtn || !zipInput) return;

        form.addEventListener('submit', function () {
            if (!zipInput.files || zipInput.files.length === 0) return;

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.innerHTML = '<i class="ti ti-loader text-[11px] mr-1"></i> Import en cours...';
        });
    })();

    (function () {
        const form = document.getElementById('form-import-slides');
        const submitBtn = document.getElementById('btn-import-slides');
        const slidesInput = document.querySelector('input[name="slides_file"][form="form-import-slides"]');

        if (!form || !submitBtn || !slidesInput) return;

        form.addEventListener('submit', function () {
            if (!slidesInput.files || slidesInput.files.length === 0) return;

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            submitBtn.innerHTML = '<i class="ti ti-loader text-[11px] mr-1"></i> Conversion...';
        });
    })();
</script>

<script>
(function () {
    const list = document.getElementById('objectives-list');
    const btn = document.getElementById('add-objective');
    if(!list || !btn) return;

    const competencies = @json($competencies->map(fn($c) => ['id' => $c->id, 'code' => $c->code, 'label' => $c->label]));

    btn.addEventListener('click', () => {
        const i = list.querySelectorAll('[data-row]').length;
        const div = document.createElement('div');
        div.className = 'p-3 border border-gray-200 bg-gray-50 rounded-lg flex gap-3 items-start';
        div.setAttribute('data-row', '1');

        let opts = competencies.map(c => `<option value="${c.id}">${c.code} - ${c.label}</option>`).join('');

        div.innerHTML = `
            <input type="hidden" name="objectives[${i}][id]" value=""><input type="hidden" name="objectives[${i}][_delete]" value="0">
            <div class="w-16">
                <label class="block text-[10px] font-semibold uppercase text-gray-500 mb-1">Pos.</label>
                <input type="number" name="objectives[${i}][position]" value="${i+1}" class="w-full text-center text-xs border border-gray-300 rounded-lg py-1">
            </div>
            <div class="flex-1 grid grid-cols-1 gap-2">
                <div>
                    <label class="block text-[10px] font-semibold uppercase text-gray-500 mb-1">Intitule</label>
                    <input type="text" name="objectives[${i}][title]" class="w-full text-xs border border-gray-300 rounded-lg py-2 px-2 font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold uppercase text-gray-500 mb-1">Competences</label>
                    <select name="objectives[${i}][competency_ids][]" class="w-full text-xs border border-gray-300 rounded-lg py-1 h-20" multiple>${opts}</select>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-semibold uppercase text-transparent mb-1">.</label>
                <button type="button" class="inline-flex items-center justify-center p-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition" data-remove>
                    <i class="ti ti-trash"></i>
                </button>
            </div>
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
