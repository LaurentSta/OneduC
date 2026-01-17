@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-3xl mx-auto mt-10">
    <div class="bg-white p-6 rounded-2xl shadow">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-bleuone">Test import SCORM (ZIP)</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-orangeone hover:underline">
                ← Retour tableau de bord
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('imported_version'))
            @php($v = session('imported_version'))
            <div class="mb-6 p-4 rounded-xl bg-gray-50 border border-gray-200 text-sm text-gray-800">
                <div class="font-semibold text-bleuone mb-2">Détails de l’import</div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <div class="text-xs text-gray-500">SCORM</div>
                        <div class="font-medium">{{ $v['slug'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Version</div>
                        <div class="font-medium">{{ $v['version'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Injection API.js</div>
                        <div class="font-medium">{{ $v['api_injected'] ? 'Oui' : 'Non / Déjà présent' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Date import</div>
                        <div class="font-medium">{{ $v['imported_at'] ?? '—' }}</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="text-xs text-gray-500 mb-1">Index</div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <code class="break-all text-xs bg-white border border-gray-200 rounded-lg p-2">{{ $v['index_path'] }}</code>

                        <a href="{{ $v['url'] }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center px-4 py-2 bg-bleuone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                            Ouvrir le SCORM
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.scorm.library.test.import') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Fichier SCORM (.zip) – 200 Mo max
                </label>
                <input type="file" name="zip" accept=".zip"
                       class="block w-full text-sm border border-gray-300 rounded-lg p-2 bg-white" required>
                <p class="text-xs text-gray-500 mt-1">
                    Structure attendue : res/index.html (iSpring).
                </p>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="inject_api" value="0">
                <input id="inject_api" type="checkbox" name="inject_api" value="1" checked
                       class="h-5 w-5 rounded border-gray-300 text-orangeone focus:ring-orangeone">
                <label for="inject_api" class="text-sm font-medium text-gray-800">
                    Injecter <code class="px-1 py-0.5 bg-gray-100 rounded">&lt;script src="/scorm_core/js/API.js"&gt;</code> dans <code>res/index.html</code>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-orangeone text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                    Importer et décompresser
                </button>
            </div>
        </form>

        <div class="mt-6 text-xs text-gray-500">
            Le ZIP est supprimé après extraction. La dernière version importée devient automatiquement active.
        </div>
    </div>
</div>
@endsection
