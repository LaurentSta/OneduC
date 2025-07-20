@extends('admin.admin_dashboard')

@section('admin')


<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded-lg">

    {{-- Navigation --}}
    <div class="flex justify-between items-center mb-6">
        <nav class="text-sm text-gray-600">
            <ol class="list-reset flex">
                <li><a href="{{ route('admin.dashboard') }}" class="text-orangeone hover:underline">Accueil</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}" class="text-orangeone hover:underline">Module</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-800 font-semibold">Éditer la lecture</li>
            </ol>
        </nav>

        <a href="{{ route('admin.modules.lecture.add', ['id' => $mlecture->module_id]) }}"
           class="inline-flex items-center px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600 transition">
            ← Retour au module
        </a>
    </div>
    {{-- Formulaire --}}
    <h2 class="text-xl font-bold mb-4 text-[#004461]">Modifier une lecture</h2>

    <form method="POST" action="{{ route('admin.lectures.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="id" value="{{ $mlecture->id }}">

        {{-- Titre --}}
        <div>
            <label for="lecture_title" class="block text-sm font-medium text-gray-700 mb-1">Titre de la lecture</label>
            <input type="text" name="lecture_title" id="lecture_title"
                   value="{{ old('lecture_title', $mlecture->lecture_title) }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"
                   placeholder="Titre de la lecture" required>
        </div>

        <!-- Lien SCORM (nom du dossier) -->
<div>
    <label for="scorm_path" class="block text-sm font-medium text-gray-700 mb-1">Lien SCORM (nom du dossier)</label>
    <input type="text" name="scorm_path" id="scorm_path"
           value="{{ old('scorm_path', $mlecture->scorm_path) }}"
           class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"
           placeholder="Ex: TestLeconScorm">
    <p class="text-xs text-gray-500 mt-1 leading-snug">
        Ce champ permet de spécifier le dossier contenant le fichier SCORM.<br>
        Exemple : <code>TestLeconScorm</code> → affichera <code>/modules/scorm/TestLeconScorm/res/index.html</code>.
    </p>
</div>

{{-- Slide & Question count sur une ligne --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Nombre de slides SCORM --}}
    <div>
        <label for="slide_count" class="block text-sm font-medium text-gray-700 mb-1">Nombre de slides SCORM</label>
        <input type="number" name="slide_count" id="slide_count"
               value="{{ old('slide_count', $mlecture->slide_count) }}"
               min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"
               placeholder="Ex : 12">
    </div>

    {{-- Nombre de questions SCORM --}}
    <div>
        <label for="question_count" class="block text-sm font-medium text-gray-700 mb-1">Nombre de questions SCORM</label>
        <input type="number" name="question_count" id="question_count"
               value="{{ old('question_count', $mlecture->question_count) }}"
               min="0"
               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"
               placeholder="Ex : 5">
    </div>
</div>



        {{-- Bouton d’enregistrement --}}
        <div class="text-right pt-4">
            <button type="submit"
                    class="inline-flex items-center px-6 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600 transition">
                💾 Sauvegarder les modifications
            </button>
        </div>
    </form>

</div>
@endsection
