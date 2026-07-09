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

<div class="rounded-[20px] border border-gray-100 bg-white shadow-md px-6 py-6 md:px-8 md:py-7 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
    <div class="lg:col-span-8">
      <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Préférences']]" />

      <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
        Préférences
      </h1>
      <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
        Modifier mes informations
      </p>
      <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
        Modifiez votre photo et vos coordonnées.
      </p>

      {{-- Mention RGPD (courte) --}}
      <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600">
        Seules les données nécessaires à la gestion du compte sont demandées.
        <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
        Pour exercer vos droits :
        <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
      </p>
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
