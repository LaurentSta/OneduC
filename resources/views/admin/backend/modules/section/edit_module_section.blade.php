{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/section/edit_module_section.blade.php --}}

@extends('admin.admin_dashboard')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <style>
        .quill-box { background:#fff; }
        .quill-box .ql-editor { min-height: 140px; }
        #editor-section_html .ql-editor { min-height: 220px; }

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
        }
    </style>
@endpush


@section('admin')

<div class="max-w-4xl mx-auto mt-10">

    <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Modifier la section</h2>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.sections.update', $section->id) }}" class="space-y-6">
            @csrf

            {{-- Titre --}}
            <div class="form-card">
                <label for="section_title" class="form-card-title">
                    Titre de la section
                </label>

                <input
                    type="text"
                    name="section_title"
                    id="section_title"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-orangeone focus:border-orangeone bg-white"
                    value="{{ old('section_title', $section->section_title) }}"
                    required
                >

                @error('section_title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


            {{-- Questions pour commencer (Quill -> section_html) --}}
            <div class="form-card">
                <label for="editor-section_html" class="form-card-title">
                    Questions pour commencer
                </label>

                <input type="hidden" name="section_html" id="section_html"
                    value="{{ old('section_html', $section->section_html) }}">

                <div
                    id="editor-section_html"
                    class="quill-box w-full border border-gray-300 rounded bg-white"
                    aria-describedby="section_html_help"
                ></div>

                <p id="section_html_help" class="form-card-subtitle">
                    Conseil : utilise une liste à puces (1 question = 1 ligne).
                </p>

                @error('section_html')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


            {{-- Objectifs pédagogiques (Quill -> objectif) --}}
            <div class="form-card">
                <label for="editor-objectif" class="form-card-title">
                    Objectifs pédagogiques
                </label>

                <input type="hidden" name="objectif" id="objectif"
                    value="{{ old('objectif', $section->objectif) }}">

                <div id="editor-objectif" class="quill-box w-full border border-gray-300 rounded bg-white"></div>

                @error('objectif')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


            {{-- Méthode pédagogique (Quill -> methode) --}}
            <div class="form-card">
                <label for="editor-methode" class="form-card-title">
                    Méthode pédagogique
                </label>

                <input type="hidden" name="methode" id="methode"
                    value="{{ old('methode', $section->methode) }}">

                <div id="editor-methode" class="quill-box w-full border border-gray-300 rounded bg-white"></div>

                @error('methode')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


            {{-- Contexte pédagogique (Quill -> contexte) --}}
            <div class="form-card">
                <label for="editor-contexte" class="form-card-title">
                    Contexte pédagogique
                </label>

                <input type="hidden" name="contexte" id="contexte"
                    value="{{ old('contexte', $section->contexte) }}">

                <div id="editor-contexte" class="quill-box w-full border border-gray-300 rounded bg-white"></div>

                @error('contexte')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


           {{-- Vidéo pédagogique --}}
            <div class="form-card">
                <label for="video_url" class="form-card-title">
                    Nom du fichier vidéo (MP4)
                </label>

                <input
                    type="text"
                    name="video_url"
                    id="video_url"
                    value="{{ old('video_url', $section->video_url) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:ring-orangeone focus:border-orangeone text-sm bg-white"
                    placeholder="Exemple : VideoTest.mp4"
                    autocomplete="off"
                >

                <p class="form-card-subtitle">
                    La vidéo sera chargée depuis :
                    <code>/modules/videos/[nom].mp4</code>
                </p>

                @error('video_url')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>


            {{-- Actions --}}
                <div class="flex items-center gap-4">
                    {{-- Enregistrer et continuer (reste sur la page) --}}
                    <button
                        type="submit"
                        name="stay"
                        value="1"
                        class="bg-orangeone hover:opacity-90 text-white font-semibold px-4 py-2 rounded-lg shadow"
                    >
                        Enregistrer et continuer
                    </button>

                    {{-- Enregistrer (retourne à la page précédente) --}}
                    <button
                        type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow"
                    >
                        Enregistrer
                    </button>

                    <a
                        href="{{ url()->previous() }}"
                        class="text-gray-600 hover:text-indigo-600 transition underline"
                    >
                        Annuler
                    </a>
                </div>


        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fields = ['section_html','objectif','methode','contexte'];

            const toolbar = [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ];

            const quills = {};

            fields.forEach((field) => {
                const hidden = document.getElementById(field);
                const editorId = 'editor-' + field;
                const editorEl = document.getElementById(editorId);

                if (!hidden || !editorEl) return;

                const quill = new Quill('#' + editorId, {
                    theme: 'snow',
                    modules: { toolbar }
                });

                // Pré-remplissage
                const initial = (hidden.value || '').trim();
                if (initial !== '') {
                    quill.clipboard.dangerouslyPasteHTML(initial);
                }

                const sync = () => {
                    hidden.value = editorEl.querySelector('.ql-editor').innerHTML;
                };

                quill.on('text-change', sync);
                quills[field] = { quill, sync, editorEl, hidden };
            });

            // Sécurité : sync au submit
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', () => {
                    fields.forEach((field) => {
                        if (quills[field]) quills[field].sync();
                    });
                });
            }
        });
    </script>
@endpush
