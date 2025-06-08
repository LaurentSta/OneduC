@extends('frontend.master')
@section('title', "Code d'accès stagiaire - Oneduc.fr")
@section('description', "Connexion sécurisée pour les stagiaires via un code fourni par le formateur.")
@section('home')
<div class="container mx-auto px-4 pt-8 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Code d’accès stagiaire</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Accéder à votre espace de formation
        </x-typography>
        <div class="prose-oneduc font-lisible">
          <p>Renseignez le code d’accès transmis par votre formateur pour rejoindre votre parcours de formation personnalisé sur Oneduc.</p>
          <p>Ce code est composé de 6 caractères alphanumériques uniques, à saisir ci-dessous.</p>
        </div>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          <img src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg') }}" alt="Code d'accès" class="w-full h-auto">
        </div>
      </div>
    </div>
  </div>

  <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Code d\'accès']]" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <h2 class="text-lg font-semibold mb-4">Connexion</h2>

        @if ($errors->any())
          <div class="mb-4 text-red-600 text-sm">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('stagiaire.code.login') }}">
          @csrf

          <label class="block mb-2 text-sm font-medium text-gray-700">Code d'accès</label>
          <input type="text" name="code_acces" maxlength="6" required autofocus
                class="input w-full uppercase text-center tracking-widest">

          <button type="submit"
                  class="mt-6 w-full bg-orangeone hover:bg-orange-600 text-white py-2 px-4 rounded transition">
            Se connecter
          </button>
        </form>

        <p class="mt-6 text-sm text-gray-500">Assurez-vous d’utiliser un code valide. Contactez votre formateur en cas de souci.</p>
      </div>
    </div>

    <!-- Menu latéral désactivé ici -->
  </div>
</div>
@endsection
