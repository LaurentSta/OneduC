@extends('formateur.dashboard')

@section('formateur')

{{-- 🧩 EN-TÊTE DE PAGE FORMATEUR – Modifier un stagiaire --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">
        <div class="col-span-12">
            <x-typography variant="titre">Modifier le stagiaire</x-typography>
            <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                Mettez à jour les informations du stagiaire.
            </x-typography>
            <x-typography>
                Vous pouvez corriger ses informations ou réinitialiser son mot de passe si nécessaire.
            </x-typography>

            {{-- 📍 Fil d’Ariane --}}
            <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
                <ol class="list-none p-0 inline-flex items-center space-x-1">
                    <li class="flex items-center">
                        <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                            </svg>
                        </a>
                        <span class="mx-2 text-gray-400">/</span>
                    </li>
                    <li class="flex items-center">
                        <a href="{{ route('formateur.stagiaires.index') }}" class="hover:underline text-bleuone">Mes stagiaires</a>
                        <span class="mx-2 text-gray-400">/</span>
                    </li>
                    <li class="text-gray-400">Modifier un stagiaire</li>
                </ol>
            </nav>
        </div>
    </div>
</div>




    <div class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full max-w-[1285px] mx-auto">

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded font-lisible text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('formateur.stagiaires.update', $stagiaire->id) }}">
            @csrf
            @method('PUT')

         
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 font-varela">Prénom</label>
        <input type="text" name="prenom" value="{{ old('prenom', $stagiaire->prenom) }}" class="input w-full" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 font-varela">Nom</label>
        <input type="text" name="name" value="{{ old('name', $stagiaire->name) }}" class="input w-full" required>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 font-varela">Adresse email</label>
        <input type="email" name="email" value="{{ old('email', $stagiaire->email) }}" class="input w-full" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 font-varela">Nouveau mot de passe</label>
        <input type="password" name="password" class="input w-full" placeholder="Laisser vide si inchangé">
    </div>
</div>


            {{-- Groupes associés --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 font-varela">Groupes associés</label>
                <div class="mt-2 space-x-2">
                    @forelse($stagiaire->groupesStagiaire as $groupe)
                        <span class="inline-block bg-vertone/10 text-vertone text-xs font-varela px-3 py-1 rounded-full">
                            {{ $groupe->name }}
                        </span>
                    @empty
                        <span class="text-gray-500 text-sm font-lisible">Aucun groupe associé</span>
                    @endforelse
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex justify-between items-center mt-8">
                <a href="{{ route('formateur.stagiaires.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 underline font-lisible">
                    ← Retour à la liste des stagiaires
                </a>

                <button type="submit" class="btn-oneduc">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>



<style>
    .input {
        @apply mt-1 px-4 py-2 w-full border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible;
    }
</style>

@endsection
