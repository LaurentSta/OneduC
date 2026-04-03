@extends('stagiaire.master')
@section('title', 'Mes préférences - Oneduc.fr')

@section('content')

{{-- /resources/views/stagiaire/stagiaire_parametre.blade.php --}}

@php
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');

  $phoneValue = old('phoneNumber', $profileData->phoneNumber ?? ($profileData->phone ?? ''));
@endphp

<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Préférences</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Modifier mes informations
      </x-typography>
      <x-typography>Modifiez votre photo et vos coordonnées.</x-typography>

      {{-- Mention RGPD (courte) --}}
      <p class="mt-2 text-sm text-gray-600">
        Seules les données nécessaires à la gestion du compte sont demandées.
        <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
        Pour exercer vos droits :
        <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
      </p>

      <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
        <ol class="list-none p-0 inline-flex items-center space-x-1">
          <li class="flex items-center">
            <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center" aria-label="Retour au tableau de bord">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
              </svg>
            </a>
            <span class="mx-2 text-gray-400">/</span>
          </li>
          <li class="text-gray-400">Préférences</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="w-full max-w-[1285px] mx-auto px-0">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <form method="POST" action="{{ route('stagiaire.profil.store') }}" enctype="multipart/form-data" novalidate>
          @csrf

          @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
              <p class="text-sm font-semibold text-red-700">Le formulaire contient des erreurs.</p>
              <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Photo --}}
          <div class="flex flex-col items-center text-center mb-8">
            <div class="relative w-28 h-28">
              <img
                src="{{ !empty($profileData->photo) ? asset('upload/user_images/'.$profileData->photo) : asset('upload/admin_images/NoPhoto.png') }}"
                alt="Photo de profil"
                class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md"
              >
            </div>

            <div class="mt-4 w-full max-w-sm">
              <label for="photo" class="block text-sm font-semibold text-[#004461]">Changer la photo</label>
              <p class="text-xs text-gray-500 mt-1">Utilisée uniquement pour l’affichage du profil.</p>
              <input
                id="photo"
                name="photo"
                type="file"
                accept="image/*"
                class="mt-3 block w-full text-sm text-gray-700
                       file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                       file:text-sm file:font-semibold file:bg-gray-100 file:text-[#004461]
                       hover:file:bg-gray-200"
              >
            </div>
          </div>

          {{-- Champs (minimisés) --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
              <input id="prenom" type="text" name="prenom"
                     value="{{ old('prenom', $profileData->prenom ?? '') }}"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="given-name">
            </div>

            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
              <input id="name" type="text" name="name"
                     value="{{ old('name', $profileData->name ?? '') }}"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="family-name">
            </div>

            <div class="sm:col-span-2">
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input id="email" type="email" name="email"
                     value="{{ old('email', $profileData->email ?? '') }}"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="email">
            </div>

            <div>
              <label for="phoneNumber" class="block text-sm font-medium text-gray-700">Téléphone</label>
              <input id="phoneNumber" type="text" name="phoneNumber"
                     value="{{ $phoneValue }}"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="tel">
            </div>

            <div>
              <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
              <input id="address" type="text" name="address"
                     value="{{ old('address', $profileData->address ?? '') }}"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="street-address">
            </div>
          </div>

          <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <button type="submit"
                    class="btn-oneduc !px-6 !py-2.5 !text-sm">
              Enregistrer
            </button>

            <a href="{{ route('stagiaire.profile') }}"
               class="btn-oneduc-outline !px-6 !py-2.5 !text-sm">
              Annuler
            </a>
          </div>

        </form>
      </div>
    </div>

    {{-- SIDEBAR --}}
    @include('stagiaire.partials.profile_menu')

  </div>
</div>

@endsection
