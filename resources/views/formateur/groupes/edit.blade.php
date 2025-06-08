@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-8 mt-8">

    <h2 class="text-2xl font-bold text-[#004461] mb-6">✏️ Modifier le groupe : {{ $group->name }}</h2>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>🔴 {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('formateur.groupes.update', $group->id) }}">
        @csrf
        @method('PUT')

        <!-- Nom -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Nom du groupe</label>
            <input type="text" name="nom" value="{{ old('nom', $group->name) }}" class="input" required>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3" class="input">{{ old('description', $group->description) }}</textarea>
        </div>

        <!-- Modules -->
        <div class="mb-6">
            <p class="font-medium text-gray-700 mb-2">Modules associés</p>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($modules as $module)
                    <label class="flex items-center space-x-2 bg-gray-50 border rounded px-4 py-2">
                        <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                               {{ in_array($module->id, $group->modules->pluck('id')->toArray()) ? 'checked' : '' }}>
                        <span>{{ $module->module_title }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Stagiaires (affichage en lecture seule) -->
        <div class="mb-6">
            <p class="font-medium text-gray-700 mb-2">Stagiaires dans ce groupe</p>
            @if ($group->students->count())
                <ul class="list-disc list-inside text-sm text-gray-600">
                    @foreach ($group->students as $stagiaire)
                        <li>{{ $stagiaire->prenom }} {{ $stagiaire->name }} ({{ $stagiaire->email }})</li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">Aucun stagiaire dans ce groupe.</p>
            @endif
        </div>
        <!-- Ajout de nouveaux stagiaires -->
        <div class="mb-6">
            <p class="font-medium text-gray-700 mb-2">Ajouter de nouveaux stagiaires</p>
            <div id="nouveaux-stagiaires-container">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-2">
                    <input type="text" name="stagiaires[0][prenom]" placeholder="Prénom" class="input">
                    <input type="text" name="stagiaires[0][nom]" placeholder="Nom" class="input">
                    <input type="email" name="stagiaires[0][email]" placeholder="Email" class="input">
                </div>
            </div>
            <button type="button" onclick="ajouterStagiaire()" class="btn-secondary">+ Ajouter un stagiaire</button>
        </div>

        <!-- Boutons -->
        <div class="flex justify-between">
            <a href="{{ route('formateur.groupes.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800 underline">⬅️ Retour à la liste</a>

            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded text-sm font-semibold transition">
                💾 Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<style>
    .input {
        @apply w-full mt-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm;
    }
</style>
<script>
    function ajouterStagiaire() {
        const container = document.getElementById('nouveaux-stagiaires-container');
        const index = container.children.length;
        const div = document.createElement('div');
        div.classList.add('grid', 'grid-cols-1', 'md:grid-cols-3', 'gap-3', 'mb-2');
        div.innerHTML = `
            <input type="text" name="stagiaires[${index}][prenom]" placeholder="Prénom" class="input">
            <input type="text" name="stagiaires[${index}][nom]" placeholder="Nom" class="input">
            <input type="email" name="stagiaires[${index}][email]" placeholder="Email" class="input">
        `;
        container.appendChild(div);
    }
</script>

@endsection
