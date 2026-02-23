{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/section/edit_module_section.blade.php --}}

@extends('admin.admin_dashboard')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <style>
        .quill-box {
            background: #fff;
        }
        .quill-box .ql-editor {
            min-height: 140px;
        }
        #editor-section_html .ql-editor { min-height: 220px; }
    </style>
@endpush


@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        <nav class="flex mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                <li>
                    <a href="{{ route('admin.modules') }}" class="hover:text-orangeone flex items-center">
                        <i class="ti ti-folders mr-1 text-sm"></i> Modules
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.modules.lecture.add', ['id' => $section->module_id]) }}" class="hover:text-orangeone flex items-center">
                        <i class="ti ti-chevron-right mx-1"></i> Structure
                    </a>
                </li>
                <li class="flex items-center">
                    <i class="ti ti-chevron-right mx-1"></i>
                    <span class="text-bleuone">Section #{{ $section->id }}</span>
                </li>
            </ol>
        </nav>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-5">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Modifier la section</h1>
                <p class="text-sm text-gray-600">
                    Mets a jour le titre, les questions d'introduction et la video associee.
                </p>
            </div>

            <a href="{{ route('admin.modules.lecture.add', ['id' => $section->module_id]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 transition text-xs font-varela cursor-pointer">
                <i class="ti ti-arrow-back-up"></i>
                Retour structure
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @php
            $currentRawVideo = old('video_url', $section->video_url);
            $currentVideoSrc = \App\Support\LearningAssetPath::resolveSectionVideoUrl($currentRawVideo);
        @endphp

        <form method="POST" action="{{ route('admin.sections.update', $section->id) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <section class="rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Informations generales</h2>
                </div>
                <div class="p-4 space-y-2">
                    <label for="section_title" class="block text-xs font-semibold uppercase tracking-wide text-gray-600">
                        Titre de la section
                    </label>
                    <input
                        type="text"
                        name="section_title"
                        id="section_title"
                        class="block w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none"
                        value="{{ old('section_title', $section->section_title) }}"
                        required
                    >
                    @error('section_title')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Questions pour commencer</h2>
                </div>
                <div class="p-4 space-y-2">
                    <input type="hidden" name="section_html" id="section_html" value="{{ old('section_html', $section->section_html) }}">
                    <div
                        id="editor-section_html"
                        class="quill-box w-full border border-gray-300 rounded-lg"
                        aria-describedby="section_html_help"
                    ></div>
                    <p id="section_html_help" class="text-xs text-gray-500">
                        Conseil : utilise une liste a puces (1 question = 1 ligne).
                    </p>
                    @error('section_html')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-bleuone">Video pedagogique</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label for="video_file" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">
                            Importer un fichier video
                        </label>
                        <input
                            type="file"
                            name="video_file"
                            id="video_file"
                            accept=".mp4,.m4v,.mov,.avi,.webm,video/mp4,video/webm,video/quicktime,video/x-msvideo"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none bg-white"
                        >
                        <p class="text-xs text-gray-500 mt-2">
                            Formats supportes : mp4, m4v, mov, avi, webm.
                        </p>
                        @error('video_file')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($currentVideoSrc)
                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Video actuellement associee</p>
                            <video class="w-full max-h-72 rounded border border-gray-200" controls preload="metadata">
                                <source src="{{ $currentVideoSrc }}" type="video/mp4">
                                Votre navigateur ne supporte pas la lecture de videos.
                            </video>
                            <p class="text-xs text-gray-500 mt-2 break-all">
                                Source : {{ $currentRawVideo }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <label for="video_url" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">
                            Chemin ou URL video (optionnel)
                        </label>
                        <input
                            type="text"
                            name="video_url"
                            id="video_url"
                            value="{{ old('video_url', $section->video_url) }}"
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none bg-white"
                            placeholder="Exemple : /storage/modules/videos/sections/section_{{ $section->id }}/ma-video.mp4"
                            autocomplete="off"
                        >
                        <p class="text-xs text-gray-500 mt-2">
                            Si un fichier est importe ci-dessus, ce champ est remplace automatiquement.
                        </p>
                        @error('video_url')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="pt-4 border-t border-gray-200 flex flex-wrap items-center justify-end gap-3">
                <a href="{{ route('admin.modules.lecture.add', ['id' => $section->module_id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-xs font-varela cursor-pointer">
                    <i class="ti ti-x"></i>
                    Annuler
                </a>

                <button
                    type="submit"
                    name="stay"
                    value="1"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-orange-200 text-orange-700 hover:bg-orangeone hover:text-white hover:border-orangeone transition text-xs font-varela cursor-pointer"
                >
                    <i class="ti ti-device-floppy"></i>
                    Enregistrer et continuer
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-bleuone text-white text-xs font-varela rounded-lg hover:bg-bleuone/90 transition cursor-pointer"
                >
                    <i class="ti ti-check"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fields = ['section_html'];

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
