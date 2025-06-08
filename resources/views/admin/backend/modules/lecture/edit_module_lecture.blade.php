@extends('admin.admin_dashboard')

@section('admin')

{{-- TinyMCE --}}
<script src="https://cdn.tiny.cloud/1/p8paiytitentec3x3f27lr3rpr0nkucd2vq47g7ltstut5fg/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#editor',
    plugins: 'image media link table lists code fullscreen preview charmap anchor hr',
    toolbar: 'undo redo | styles | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image media link | fullscreen preview | code',
    height: 500,
    content_css: false,
    content_style: "body { font-family: 'OpenDyslexic', 'Arial', sans-serif; font-size: 16px; line-height: 1.6; }"
  });
</script>

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

        {{-- Lien média (vidéo ou SCORM) --}}
        <div>
            <label for="video" class="block text-sm font-medium text-gray-700 mb-1">Lien média (vidéo ou SCORM)</label>
            <input type="url" name="video" id="video"
                value="{{ old('video', $mlecture->video) }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"
                placeholder="Ex : https://oneduc.fr/videos/moncours.mp4 ou /modules/scorm/monmodule/res/index.html">
            <p class="text-xs text-gray-500 mt-1 leading-snug">
                Ce champ peut contenir un lien vers une <strong>vidéo</strong> (.mp4) ou un <strong>module SCORM</strong> (.html).<br>
                Pour un SCORM, entre un lien local comme : <code>/modules/scorm/NomDuDossier/res/index.html</code>.
            </p>
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

        {{-- Contenu complémentaire --}}
        <div>
            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu de la lecture</label>
            <textarea id="editor" name="content" rows="12"
                      class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm">{{ old('content', $mlecture->content) }}</textarea>
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
