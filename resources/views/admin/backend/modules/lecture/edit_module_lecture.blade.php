{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/lecture/edit_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')

@push('styles')
    <style>
        /* Cartes de formulaire (fond charte en transparence) */
        .form-card {
            background: rgba(0, 68, 97, 0.06); /* bleuone #004461 avec transparence */
            border: 1px solid rgba(0, 68, 97, 0.14);
            border-radius: 16px;
            padding: 16px;
        }
        .form-card-title {
            font-size: 1.05rem; /* ~text-lg */
            font-weight: 700;
            color: #004461; /* bleuone */
            margin-bottom: 8px;
        }
        .form-card-subtitle {
            font-size: 0.875rem;
            color: rgba(0,0,0,0.55);
            margin-top: 6px;
            line-height: 1.35;
        }
        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #004461;
        }
    </style>
@endpush

@section('admin')

<div class="max-w-4xl mx-auto mt-10">

    <div class="bg-white p-6 rounded-2xl shadow">

        {{-- Navigation --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <nav class="text-sm text-gray-600">
                <ol class="list-reset flex flex-wrap">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="text-orangeone hover:underline">
                            Accueil
                        </a>
                    </li>
                    <li><span class="mx-2">/</span></li>
                    <li>
                        <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}" class="text-orangeone hover:underline">
                            Module
                        </a>
                    </li>
                    <li><span class="mx-2">/</span></li>
                    <li class="text-gray-800 font-semibold">Éditer la lecture</li>
                </ol>
            </nav>

            <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}"
               class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded-lg hover:opacity-90 transition">
                Retour au module
            </a>
        </div>

        {{-- Titre page --}}
        <div class="mb-6">
            <h2 class="page-title">Modifier une leçon</h2>
        </div>

        <form method="POST" action="{{ route('admin.lectures.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="id" value="{{ $mlecture->id }}">

            {{-- Titre de la lecture --}}
            <div class="form-card">
                <label for="lecture_title" class="form-card-title">
                    Titre de la leçon
                </label>

                <input
                    type="text"
                    name="lecture_title"
                    id="lecture_title"
                    value="{{ old('lecture_title', $mlecture->lecture_title) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white"
                    placeholder="Titre de la leçon"
                    required
                >
            </div>

            {{-- Lien SCORM --}}
            <div class="form-card">
                <label for="scorm_path" class="form-card-title">
                    Lien SCORM (nom du dossier)
                </label>

                <input
                    type="text"
                    name="scorm_path"
                    id="scorm_path"
                    value="{{ old('scorm_path', $mlecture->scorm_path) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white"
                    placeholder="Ex : TestLeconScorm"
                >

                <p class="form-card-subtitle">
                    Ce champ permet de spécifier le dossier contenant la leçon SCORM.
                    Exemple : <code>TestLeconScorm</code> affichera
                    <code>/modules/scorm/TestLeconScorm/res/index.html</code>.
                </p>
            </div>

            {{-- Slides + Questions --}}
            <div class="form-card">
                <div class="form-card-title">Indicateurs de contenu</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="slide_count" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de diapositives
                        </label>
                        <input
                            type="number"
                            name="slide_count"
                            id="slide_count"
                            value="{{ old('slide_count', $mlecture->slide_count) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white"
                            placeholder="Ex : 12"
                        >
                    </div>

                    <div>
                        <label for="question_count" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de questions
                        </label>
                        <input
                            type="number"
                            name="question_count"
                            id="question_count"
                            value="{{ old('question_count', $mlecture->question_count) }}"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white"
                            placeholder="Ex : 5"
                        >
                    </div>
                </div>

                <p class="form-card-subtitle">
                    Ces valeurs servent à l’analyse de progression et au tableau de bord.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center px-6 py-2 bg-orangeone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition"
                >
                    Sauvegarder les modifications
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
