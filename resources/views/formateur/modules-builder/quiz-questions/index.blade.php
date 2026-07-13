{{-- resources/views/formateur/modules-builder/quiz-questions/index.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8 font-sans">

    {{-- Fil d'Ariane --}}
    <nav class="flex mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('formateur.modules.builder.index') }}" class="hover:text-orangeone flex items-center">
                    <i class="ti ti-folders mr-1 text-sm"></i> Mes modules
                </a>
            </li>
            <li>
                <a href="{{ route('formateur.modules.builder.edit', ['module' => $lecture->module_id]) }}" class="hover:text-orangeone flex items-center">
                    <i class="ti ti-chevron-right mx-1"></i> Structure
                </a>
            </li>
            <li>
                <a href="{{ route('formateur.modules.builder.lectures.edit', ['lecture' => $lecture->id]) }}" class="hover:text-orangeone flex items-center">
                    <i class="ti ti-chevron-right mx-1"></i> Édition Leçon
                </a>
            </li>
            <li class="flex items-center">
                <i class="ti ti-chevron-right mx-1"></i>
                <span class="text-bleuone">Banque de Questions</span>
            </li>
        </ol>
    </nav>

    {{-- En-tête Administratif --}}
    <div class="flex flex-col md:flex-row md:items-end md:justify-between border-b-2 border-bleuone pb-3 mb-6 gap-4">
        <div>
            <h1 class="text-xl font-bold text-bleuone uppercase tracking-tight">Gestion des Quiz</h1>
            <p class="text-gray-500 text-[10px] italic">Banque de questions pour : <span class="font-bold text-gray-700">{{ $lecture->lecture_title }}</span></p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('formateur.modules.builder.lectures.edit', ['lecture' => $lecture->id]) }}"
               class="px-3 py-1.5 bg-gray-100 text-gray-600 text-[11px] font-bold uppercase rounded border border-gray-300 hover:bg-gray-200 transition flex items-center gap-1">
                <i class="ti ti-arrow-back-up"></i> Retour Leçon
            </a>
            <a href="{{ route('formateur.modules.builder.lectures.quiz.questions.import.template', ['lecture' => $lecture->id]) }}"
               class="px-3 py-1.5 bg-white text-bleuone text-[11px] font-bold uppercase rounded border border-bleuone hover:bg-bleuone hover:text-white transition flex items-center gap-1">
                <i class="ti ti-file-download"></i> Modèle CSV
            </a>
            <a href="{{ route('formateur.modules.builder.lectures.quiz.questions.create', ['lecture' => $lecture->id]) }}"
               class="px-4 py-1.5 bg-orangeone text-white text-[11px] font-bold uppercase rounded shadow-sm hover:bg-orangeone-hover transition flex items-center gap-1">
                <i class="ti ti-plus"></i> Ajouter une question
            </a>
        </div>
    </div>

    {{-- Alertes --}}
    @if(session('success'))
        <div class="mb-4 rounded-sm border-l-4 border-green-500 bg-green-50 p-3 text-xs text-green-800 font-medium shadow-sm">
            <i class="ti ti-check mr-1"></i> {{ session('success') }}
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

    <div class="mb-4 rounded border border-gray-300 bg-white p-3 shadow-sm">
        <form action="{{ route('formateur.modules.builder.lectures.quiz.questions.import', ['lecture' => $lecture->id]) }}"
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
            <div>
                <button type="submit"
                        class="px-4 py-2 bg-bleuone text-white text-[11px] font-bold uppercase rounded hover:bg-bleuone/90 transition">
                    Importer CSV
                </button>
            </div>
        </form>

    </div>

    {{-- Tableau de Gestion (Style Data Grid) --}}
    <div class="bg-white border border-gray-300 rounded shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-200 text-[10px] uppercase font-bold text-gray-700 tracking-wide">
                    <tr>
                        <th class="px-3 py-2 border-r border-gray-300 w-12 text-center">ID</th>
                        <th class="px-3 py-2 border-r border-gray-300">Énoncé de la question</th>
                        <th class="px-3 py-2 border-r border-gray-300 w-32 text-center">Type</th>
                        <th class="px-3 py-2 border-r border-gray-300 w-20 text-center">Options</th>
                        <th class="px-3 py-2 border-r border-gray-300 w-20 text-center">Image</th>
                        <th class="px-3 py-2 border-r border-gray-300 w-20 text-center">Son</th>
                        <th class="px-3 py-2 border-r border-gray-300 w-20 text-center">État</th>
                        <th class="px-3 py-2 text-right w-40">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-[11px] text-gray-700">
                    @forelse($questions as $q)
                        <tr class="border-b border-gray-200 even:bg-gray-50 hover:bg-orangeone/5 transition-colors">
                            {{-- ID --}}
                            <td class="px-3 py-1.5 border-r border-gray-200 text-center font-mono text-gray-500">
                                {{ $q->id }}
                            </td>

                            {{-- Question --}}
                            <td class="px-3 py-1.5 border-r border-gray-200 font-medium text-bleuone">
                                {{ \Illuminate\Support\Str::limit($q->question_text, 100) }}
                            </td>

                            {{-- Type --}}
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

                            {{-- Nb Options --}}
                            <td class="px-3 py-1.5 border-r border-gray-200 text-center font-mono">
                                {{ $q->type === 'cloze' ? '—' : (int) ($q->options_count ?? 0) }}
                            </td>

                            {{-- Image --}}
                            <td class="px-3 py-1.5 border-r border-gray-200 text-center">
                                @if(!empty($q->image_path))
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded border bg-green-100 text-green-700 border-green-300 text-[9px] font-bold uppercase">
                                        Oui
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded border bg-red-100 text-red-700 border-red-300 text-[9px] font-bold uppercase">
                                        Non
                                    </span>
                                @endif
                            </td>

                            {{-- Son --}}
                            <td class="px-3 py-1.5 border-r border-gray-200 text-center">
                                @if(!empty($q->audio_path))
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded border bg-green-100 text-green-700 border-green-300 text-[9px] font-bold uppercase">
                                        Oui
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded border bg-red-100 text-red-700 border-red-300 text-[9px] font-bold uppercase">
                                        Non
                                    </span>
                                @endif
                            </td>

                            {{-- État --}}
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

                            {{-- Actions --}}
                            <td class="px-3 py-1.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('formateur.modules.builder.lectures.quiz.questions.edit', ['lecture' => $lecture->id, 'question' => $q->id]) }}"
                                       class="group flex items-center gap-1 px-2 py-1 bg-white border border-gray-300 text-bleuone rounded-sm hover:border-bleuone hover:bg-bleuone hover:text-white transition text-[9px] font-bold uppercase shadow-sm"
                                       title="Modifier">
                                        <i class="ti ti-pencil"></i> <span class="hidden md:inline">Éditer</span>
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
                                        :action="route('formateur.modules.builder.lectures.quiz.questions.destroy', ['lecture' => $lecture->id, 'question' => $q->id])"
                                        method="DELETE"
                                        confirm-label="Supprimer"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic bg-gray-50 text-xs border-b border-gray-200">
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
</div>
@endsection
