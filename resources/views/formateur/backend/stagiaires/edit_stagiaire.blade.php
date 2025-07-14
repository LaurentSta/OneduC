@extends('formateur.dashboard')

@section('formateur')

{{-- EN-TÊTE --}}
<div class="container mx-auto px-4 pt-8 pb-2">
    <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Modifier le stagiaire</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Mettez à jour les informations du stagiaire.
                </x-typography>
                <x-typography>
                    Vous pouvez corriger ses informations, ou réinitialiser son mot de passe si nécessaire.
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
</div>


<style>
    .input {
        @apply mt-1 px-4 py-2 w-full border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible;
    }
</style>

@endsection
