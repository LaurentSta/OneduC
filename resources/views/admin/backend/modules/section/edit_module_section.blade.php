@extends('admin.admin_dashboard')

@section('admin')

<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-white p-6 rounded-2xl shadow">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">✏️ Modifier la section</h2>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.sections.update', $section->id) }}" class="space-y-6">
            @csrf

            <div>
                <label for="section_title" class="block text-sm font-medium text-gray-700">Titre de la section</label>
                <input type="text" name="section_title" id="section_title"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    value="{{ old('section_title', $section->section_title) }}" required>

                @error('section_title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mt-6">
                <label for="section_html" class="block text-sm font-medium text-gray-700 mb-1">Contenu HTML de la section</label>
                <textarea name="section_html" id="section_html" rows="10" class="w-full border rounded p-3">
                    {{ old('section_html', $section->section_html) }}
                </textarea>
            </div>

<div class="space-y-6 mt-6">

    {{-- Objectif pédagogique --}}
    <div>
        <label for="objectif" class="block text-sm font-medium text-gray-700 mb-1">🎯 Objectifs pédagogiques</label>
        <textarea name="objectif" id="objectif" rows="4" class="w-full border rounded p-3">{{ old('objectif', $section->objectif) }}</textarea>
    </div>

    {{-- Méthode pédagogique --}}
    <div>
        <label for="methode" class="block text-sm font-medium text-gray-700 mb-1">🧠 Méthode pédagogique</label>
        <textarea name="methode" id="methode" rows="4" class="w-full border rounded p-3">{{ old('methode', $section->methode) }}</textarea>
    </div>

    {{-- Contexte pédagogique --}}
    <div>
        <label for="contexte" class="block text-sm font-medium text-gray-700 mb-1">📌 Contexte pédagogique</label>
        <textarea name="contexte" id="contexte" rows="4" class="w-full border rounded p-3">{{ old('contexte', $section->contexte) }}</textarea>
    </div>

    {{-- Vidéo pédagogique --}}
<div>
    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1">🎥 Nom du fichier vidéo (MP4)</label>
    <input type="text" name="video_url" id="video_url"
           value="{{ old('video_url', $section->video_url) }}"
           class="w-full px-4 py-2 border rounded shadow-sm focus:ring-orangeone focus:border-orangeone text-sm"
           placeholder="ex : VideoTest.mp4">
    <p class="text-sm text-gray-500 mt-1">
        La vidéo sera automatiquement chargée depuis : <code>/modules/scorm/02_videos/[nom].mp4</code>
    </p>
</div>




    {{-- Boutons --}}
    <div class="flex items-center gap-4">
        <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
            💾 Enregistrer
        </button>
        <a href="{{ url()->previous() }}"
            class="text-gray-600 hover:text-indigo-600 transition underline">
            ⬅️ Annuler
        </a>
    </div>
</div>

        </form>
    </div>
</div>

@endsection
