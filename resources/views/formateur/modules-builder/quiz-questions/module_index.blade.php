{{-- resources/views/formateur/modules-builder/quiz-questions/module_index.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8 font-sans">

    {{-- Fil d'Ariane --}}
    <nav class="flex mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('formateur.modules.builder.index') }}" class="hover:text-orangeone flex items-center">
                    <i class="ti ti-books mr-1 text-sm"></i> Mes créations
                </a>
            </li>
            <li class="flex items-center">
                <i class="ti ti-chevron-right mx-1"></i>
                <a href="{{ route('formateur.modules.builder.edit', $module) }}" class="hover:text-orangeone">
                    {{ $module->module_title ?: $module->module_name }}
                </a>
            </li>
            <li class="flex items-center">
                <i class="ti ti-chevron-right mx-1"></i>
                <span class="text-bleuone">Questions</span>
            </li>
        </ol>
    </nav>

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between border-b-2 border-bleuone pb-3 mb-6 gap-4">
        <div>
            <h1 class="text-xl font-bold text-bleuone uppercase tracking-tight">Questions de la formation</h1>
            <p class="text-gray-500 text-[10px] italic">Une même question peut servir au quiz de validation et au quiz en direct.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Arborescence ─────────────────────────────────────────── --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-300 rounded shadow-sm p-4 sticky top-6 max-h-[75vh] overflow-y-auto">
                @forelse($module->sections as $section)
                    <div class="mb-4 last:mb-0">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1.5 px-1">
                            {{ $section->section_title }}
                        </p>
                        <div class="space-y-1">
                            @forelse($section->lectures as $lec)
                                @php
                                    $isSelected = $selectedLecture && (int) $selectedLecture->id === (int) $lec->id;
                                    $count = (int) ($lec->quiz_questions_count ?? 0);
                                @endphp
                                <a href="{{ route('formateur.modules.builder.quiz-questions.index', ['module' => $module->id, 'lecture' => $lec->id]) }}"
                                   class="flex items-center justify-between gap-2 rounded px-3 py-2 text-xs transition
                                          {{ $isSelected ? 'bg-orangeone text-white font-bold' : 'text-gray-700 hover:bg-gray-100' }}">
                                    <span class="truncate">{{ $lec->lecture_title }}</span>
                                    <span class="shrink-0 inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[9px] font-bold
                                                 {{ $isSelected ? 'bg-white/20 text-white' : ($count > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400') }}">
                                        {{ $count }}
                                    </span>
                                </a>
                            @empty
                                <p class="px-3 py-1.5 text-[11px] text-gray-400 italic">Aucune leçon.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Cette formation n'a pas encore de chapitre.</p>
                @endforelse
            </div>
        </div>

        {{-- ── Panneau de la leçon sélectionnée ─────────────────────── --}}
        <div class="lg:col-span-2">
            @if (! $selectedLecture)
                <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-20 text-center">
                    <i class="ti ti-hand-click text-3xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-semibold text-gray-700">Choisissez une leçon</p>
                    <p class="text-xs text-gray-400 mt-1">Cliquez une leçon dans l'arborescence à gauche pour voir ou créer ses questions.</p>
                </div>
            @else
                {{-- Alertes --}}
                @if(session('success'))
                    <div class="mb-4 rounded-sm border-l-4 border-green-500 bg-green-50 p-3 text-xs text-green-800 font-medium shadow-sm">
                        <i class="ti ti-check mr-1"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-sm border-l-4 border-red-500 bg-red-50 p-3 text-xs text-red-800 font-medium shadow-sm">
                        <i class="ti ti-alert-triangle mr-1"></i> {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-sm border-l-4 border-red-500 bg-red-50 p-3 text-xs text-red-800 font-medium shadow-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('import_report'))
                    <div class="mb-4 rounded-sm border-l-4 border-blue-500 bg-blue-50 p-3 text-xs text-blue-900 font-medium shadow-sm">
                        <div class="font-bold mb-1">Rapport d'import CSV</div>
                        <div>{{ session('import_report.created', 0) }} question(s) créée(s).</div>
                        <div>{{ session('import_report.errors_count', 0) }} erreur(s).</div>
                    </div>
                @endif

                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Leçon sélectionnée</p>
                        <p class="text-base font-bold text-bleuone">{{ $selectedLecture->lecture_title }}</p>
                    </div>
                    <a href="{{ route('formateur.formations.lecture', ['module' => $module->id, 'section' => $selectedLecture->section_id, 'lecture' => $selectedLecture->id]) }}"
                       target="_blank" rel="noopener"
                       class="btn-oneduc-outline shrink-0 !px-4 !py-1.5 !text-xs inline-flex items-center gap-1.5">
                        <i class="ti ti-eye"></i> Aperçu de la leçon
                    </a>
                </div>

                <div x-data="{ activeTab: 'add' }" class="mb-6">
                    {{-- Onglets --}}
                    <div class="flex gap-1 border-b border-gray-300 mb-4">
                        <button type="button" @click="activeTab = 'add'"
                                :class="activeTab === 'add' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-xs font-bold uppercase border-b-2 transition">
                            Ajouter une question
                        </button>
                        <button type="button" @click="activeTab = 'ia'"
                                :class="activeTab === 'ia' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-xs font-bold uppercase border-b-2 transition">
                            Générer via IA
                        </button>
                        <button type="button" @click="activeTab = 'import'"
                                :class="activeTab === 'import' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="px-4 py-2 text-xs font-bold uppercase border-b-2 transition">
                            Importer
                        </button>
                    </div>

                    {{-- Onglet : Ajouter une question --}}
                    <div x-show="activeTab === 'add'" x-cloak class="bg-white border border-gray-300 rounded shadow-sm p-6">
                        @include('formateur.modules-builder.quiz-questions._form', ['lecture' => $selectedLecture])
                    </div>

                    {{-- Onglet : Générer via IA --}}
                    <div x-show="activeTab === 'ia'" x-cloak class="bg-white border border-gray-300 rounded shadow-sm p-6">
                        <form method="POST"
                              action="{{ route('formateur.modules.builder.lectures.quiz.questions.generate-ia', ['lecture' => $selectedLecture->id]) }}"
                              class="space-y-4 max-w-md">
                            @csrf
                            <div>
                                <label for="ia_count" class="block text-xs font-bold uppercase text-gray-600 mb-1">
                                    Nombre de questions à générer
                                </label>
                                <input type="number" id="ia_count" name="count" min="1" max="15" value="5" required
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <p class="text-[11px] text-gray-500">
                                L'IA lit le contenu déjà présent dans cette leçon et en tire des questions.
                                Les questions générées sont créées <strong>inactives</strong> : relisez-les et activez celles que vous retenez.
                            </p>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-bleuone text-white text-xs font-bold uppercase rounded hover:bg-bleuone/90 transition">
                                <i class="ti ti-sparkles"></i> Générer
                            </button>
                        </form>
                    </div>

                    {{-- Onglet : Importer --}}
                    <div x-show="activeTab === 'import'" x-cloak class="bg-white border border-gray-300 rounded shadow-sm p-6">
                        <form action="{{ route('formateur.modules.builder.lectures.quiz.questions.import', ['lecture' => $selectedLecture->id]) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="flex flex-col lg:flex-row lg:items-end gap-3">
                            @csrf
                            <div class="flex-1">
                                <label for="csv_file" class="block text-[11px] font-bold uppercase text-gray-600 mb-1">
                                    Importer des questions (CSV)
                                </label>
                                <input id="csv_file"
                                       name="csv_file"
                                       type="file"
                                       accept=".csv,text/csv,.txt"
                                       required
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-xs">
                                <p class="mt-1 text-[10px] text-gray-500">
                                    Colonnes obligatoires : <span class="font-mono">question_text</span>, <span class="font-mono">type</span>. Types : boolean, single, multiple, cloze.
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('formateur.modules.builder.lectures.quiz.questions.import.template', ['lecture' => $selectedLecture->id]) }}"
                                   class="px-3 py-2 bg-white text-bleuone text-[11px] font-bold uppercase rounded border border-bleuone hover:bg-bleuone hover:text-white transition flex items-center gap-1">
                                    <i class="ti ti-file-download"></i> Modèle
                                </a>
                                <button type="submit"
                                        class="px-4 py-2 bg-bleuone text-white text-[11px] font-bold uppercase rounded hover:bg-bleuone/90 transition">
                                    Importer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tableau des questions --}}
                <div class="bg-white border border-gray-300 rounded shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-200 text-[10px] uppercase font-bold text-gray-700 tracking-wide">
                                <tr>
                                    <th class="px-3 py-2 border-r border-gray-300">Énoncé de la question</th>
                                    <th class="px-3 py-2 border-r border-gray-300 w-32 text-center">Type</th>
                                    <th class="px-3 py-2 border-r border-gray-300 w-16 text-center">Points</th>
                                    <th class="px-3 py-2 border-r border-gray-300 w-20 text-center">État</th>
                                    <th class="px-3 py-2 border-r border-gray-300 w-56 text-center">Déplacer vers</th>
                                    <th class="px-3 py-2 text-right w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-[11px] text-gray-700">
                                @forelse($questions as $q)
                                    <tr class="border-b border-gray-200 even:bg-gray-50 hover:bg-orangeone/5 transition-colors">
                                        <td class="px-3 py-1.5 border-r border-gray-200 font-medium text-bleuone">
                                            {{ \Illuminate\Support\Str::limit($q->question_text, 90) }}
                                        </td>

                                        <td class="px-3 py-1.5 border-r border-gray-200 text-center">
                                            @php
                                                $badgeClass = match($q->type) {
                                                    'single' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'multiple' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                    'boolean' => 'bg-gray-100 text-gray-800 border-gray-200',
                                                    'cloze' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    default => 'bg-gray-100 text-gray-800 border-gray-200'
                                                };
                                                $label = match($q->type) {
                                                    'single' => 'Choix Unique',
                                                    'multiple' => 'Choix Multiple',
                                                    'boolean' => 'Vrai / Faux',
                                                    'cloze' => 'Texte à trous',
                                                    default => $q->type
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded border {{ $badgeClass }} text-[9px] font-bold uppercase">
                                                {{ $label }}
                                            </span>
                                        </td>

                                        <td class="px-3 py-1.5 border-r border-gray-200 text-center font-semibold text-gray-700">
                                            {{ $q->type === 'cloze' ? '—' : $q->points }}
                                        </td>

                                        <td class="px-3 py-1.5 border-r border-gray-200 text-center">
                                            @if($q->is_active)
                                                <span class="inline-flex items-center gap-1 text-green-700 font-bold text-[10px] uppercase">
                                                    <i class="ti ti-check"></i> Actif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-gray-400 font-bold text-[10px] uppercase">
                                                    <i class="ti ti-x"></i> Inactif
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-1.5 border-r border-gray-200">
                                            @if($otherLectures->isNotEmpty())
                                                <form method="POST"
                                                      action="{{ route('formateur.modules.builder.quiz-questions.move', ['question' => $q->id]) }}"
                                                      class="flex items-center gap-1.5">
                                                    @csrf
                                                    <select name="lecture_id" required
                                                            class="w-full rounded border border-gray-300 px-2 py-1 text-[10px] focus:border-bleuone focus:outline-none">
                                                        <option value="">Choisir une leçon…</option>
                                                        @foreach($otherLectures as $ol)
                                                            <option value="{{ $ol['id'] }}">{{ $ol['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit"
                                                            class="shrink-0 px-2 py-1 bg-gray-100 border border-gray-300 rounded text-[9px] font-bold uppercase hover:bg-bleuone hover:text-white hover:border-bleuone transition"
                                                            title="Déplacer cette question">
                                                        <i class="ti ti-arrow-right"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-[10px] text-gray-300 italic">—</span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-1.5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('formateur.modules.builder.lectures.quiz.questions.edit', ['lecture' => $selectedLecture->id, 'question' => $q->id]) }}"
                                                   class="group flex items-center gap-1 px-2 py-1 bg-white border border-gray-300 text-bleuone rounded-sm hover:border-bleuone hover:bg-bleuone hover:text-white transition text-[9px] font-bold uppercase shadow-sm"
                                                   title="Modifier">
                                                    <i class="ti ti-pencil"></i>
                                                </a>

                                                <button type="button"
                                                        x-data
                                                        x-on:click="$dispatch('open-modal', 'delete-question-{{ $q->id }}')"
                                                        class="group flex items-center gap-1 px-2 py-1 bg-white border border-gray-300 text-red-600 rounded-sm hover:border-red-600 hover:bg-red-600 hover:text-white transition text-[9px] font-bold uppercase shadow-sm"
                                                        title="Supprimer">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                                <x-confirm-modal
                                                    name="delete-question-{{ $q->id }}"
                                                    title="Attention : Cette action est irréversible."
                                                    message="Supprimer la question ?"
                                                    :action="route('formateur.modules.builder.lectures.quiz.questions.destroy', ['lecture' => $selectedLecture->id, 'question' => $q->id])"
                                                    method="DELETE"
                                                    confirm-label="Supprimer"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic bg-gray-50 text-xs border-b border-gray-200">
                                            <div class="flex flex-col items-center gap-2">
                                                <i class="ti ti-folder-off text-2xl text-gray-300"></i>
                                                <span>Aucune question enregistrée pour le moment.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
