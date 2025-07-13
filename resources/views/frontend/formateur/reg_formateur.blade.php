@extends('frontend.master')

@section('home')

{{-- BLOC INTRO — Inscription Formateur --}}
<div class="container mx-auto px-4 pt-8 pb-2">
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-4 w-full max-w-[1285px] mx-auto">

        <div class="grid grid-cols-12 gap-6 items-center">
            {{-- Texte --}}
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Inscription Formateur</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Rejoignez la communauté Onéduc.
                </x-typography>
                <x-typography>
                    Vous êtes formateur et souhaitez partager vos connaissances à travers des parcours interactifs et accessibles ?
                    Complétez ce formulaire pour créer votre compte. Vous pourrez ensuite gérer vos groupes, suivre les progrès de vos stagiaires et enrichir leurs apprentissages.
                </x-typography>
            </div>

            {{-- Image --}}
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('images/svg/Enseignant.svg')) !!}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="content-wrapper bg-white py-10">
    <div class="mx-auto px-4 max-w-[1285px]">
        <div class="card shadow-sm p-6">

            <p class="text-sm text-gray-600 mb-6">* Champs obligatoires</p>

            <!-- Messages de session -->
            @if(session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="list-disc pl-4 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('formateur.inscription') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Prénom -->
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:border-orangeone focus:ring focus:ring-orangeone/30">
                    </div>
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:border-orangeone focus:ring focus:ring-orangeone/30">
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone (optionnel)</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Société -->
                    <div>
                        <label for="societe" class="block text-sm font-medium text-gray-700">Société ou Asso (optionnel)</label>
                        <input type="text" id="societe" name="societe" value="{{ old('societe') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <!-- Mot de passe -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe *</label>
                        <input type="password" id="password" name="password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmation *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                </div>
                <!-- Adresse -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Adresse (optionnel)</label>
                    <input type="text" id="address" name="address" value="{{ old('address') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>

                <!-- Boutons -->
                <div class="pt-4 flex space-x-4">
                    <button type="submit" class="btn-oneduc">S'inscrire</button>
                    <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
