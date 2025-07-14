@extends('formateur.dashboard')

@section('formateur')

{{-- 🧩 EN-TÊTE DE PAGE --}}
<div class="container mx-auto px-4 pt-8 pb-2">
    <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Modifier un groupe</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Mettez à jour les informations, les modules et les stagiaires.
                </x-typography>
                <x-typography>
                    Cette page vous permet de modifier un groupe existant. Vous pouvez aussi ajouter de nouveaux stagiaires si besoin.
                </x-typography>
            </div>
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('frontend/assets/img/illustrations/AssociationOneduc.svg')) !!}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container mx-auto px-4 pb-12">
    <div class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full max-w-[1285px] mx-auto">
        {{-- ✏️ FORMULAIRE DE MISE À JOUR --}}
        

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded font-lisible text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('formateur.groupes.update', $group->id) }}">
                @csrf
                @method('PUT')

                {{-- 🔤 Nom --}}
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 font-varela">Nom du groupe <span class="text-red-500">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom', $group->name) }}" class="input w-full" required>
                </div>

                {{-- 📝 Description --}}
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 font-varela">Description</label>
                    <textarea name="description" rows="3" class="input w-full">{{ old('description', $group->description) }}</textarea>
                </div>

                {{-- 📚 Modules associés --}}
                <div class="mb-8">
                    <p class="text-sm font-medium text-gray-600 mb-2 font-varela">Modules associés</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($modules as $module)
                            <label class="flex items-start space-x-2 bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 hover:bg-gray-100 transition">
                                <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                                class="accent-vertone"
                                {{ in_array($module->id, $group->modules->pluck('id')->toArray()) ? 'checked' : '' }}>

                                <span class="text-sm text-gray-800 font-lisible">{{ $module->module_title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- 👥 Stagiaires existants --}}
                <div class="mb-8">
                    <p class="text-sm font-medium text-gray-600 mb-2 font-varela">Stagiaires déjà dans ce groupe</p>
                    @if ($group->students->count())
                        <ul class="list-disc list-inside text-sm text-gray-700 font-lisible">
                            @foreach ($group->students as $stagiaire)
                                <li>{{ $stagiaire->prenom }} {{ $stagiaire->name }} ({{ $stagiaire->email }})</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 font-lisible">Aucun stagiaire dans ce groupe.</p>
                    @endif
                </div>

                {{-- ➕ Ajouter de nouveaux stagiaires --}}
                <div class="mb-10">
                    <p class="text-sm font-medium text-gray-600 mb-2 font-varela">Ajouter de nouveaux stagiaires</p>
                    <div id="nouveaux-stagiaires-container">
                        <div class="stagiaire-bloc grid grid-cols-1 md:grid-cols-3 gap-3 mb-2 relative">
                            <input type="text" name="stagiaires[0][prenom]" placeholder="Prénom" class="input">
                            <input type="text" name="stagiaires[0][nom]" placeholder="Nom" class="input">
                            <input type="email" name="stagiaires[0][email]" placeholder="Email" class="input">
                            

                        </div>
                    </div>
                    {{-- Bouton pour ajouter un stagiaire --}}

                    <button type="button" onclick="ajouterStagiaire()"
                            class="inline-block px-4 py-2 text-sm font-varela border-4 border-orangeone text-orangeone rounded-full transition duration-300 hover:bg-orangeone hover:text-white">
                        + Ajouter un stagiaire
                    </button>

                </div>

                {{-- ✅ Boutons --}}
                <div class="flex justify-between items-center mt-8">
                    <a href="{{ route('formateur.groupes.index') }}"
                    class="text-sm text-gray-500 hover:text-gray-700 underline font-lisible">
                        ← Retour à la liste
                    </a>

                    <button type="submit"
                            class="btn-oneduc bg-orangeone border-orangeone hover:bg-white hover:text-orangeone">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        
    </div>
</div>
{{-- STYLES & JS --}}
<style>
    .input {
        @apply mt-1 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible;
    }
    @keyframes wiggle {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-3deg); }
            75% { transform: rotate(3deg); }
        }
        .hover-wiggle:hover {
            animation: wiggle 0.3s ease-in-out infinite;
        }


</style>


<script>
    function ajouterStagiaire() {
        const container = document.getElementById('nouveaux-stagiaires-container');
        const index = container.querySelectorAll('.stagiaire-bloc').length;

        const div = document.createElement('div');
        div.classList.add('stagiaire-bloc', 'grid', 'grid-cols-1', 'md:grid-cols-3', 'gap-3', 'mb-2', 'relative');

        // contenu HTML du bloc stagiaire
        div.innerHTML = `
            <input type="text" name="stagiaires[${index}][prenom]" placeholder="Prénom" class="input">
            <input type="text" name="stagiaires[${index}][nom]" placeholder="Nom" class="input">
            <input type="email" name="stagiaires[${index}][email]" placeholder="Email" class="input">
            ${index > 0 ? `
                <button type="button"
                        class="absolute -top-2 -right-2 bg-orangeone rounded-full w-8 h-8 flex items-center justify-center hover-wiggle transition hover:scale-105"
                        onclick="this.closest('.stagiaire-bloc').remove()"
                        title="Supprimer ce stagiaire">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="white" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round"
                         class="lucide lucide-x">
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>
            ` : ''}
        `;

        container.appendChild(div);
    }
</script>


@endsection
