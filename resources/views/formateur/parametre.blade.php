{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/parametre.blade.php --}}

@extends('formateur.dashboard')
@section('title', 'Préférences - Oneduc.fr')

@section('formateur')

@php
  // Liens RGPD (sans casser si route absente)
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');

  // Pré-remplissage téléphone : ton modèle semble utiliser 'phone'
  $phoneValue = old('phoneNumber', $profileData->phoneNumber ?? ($profileData->phone ?? ''));

  // Photo
  $photoUrl = !empty($profileData->photo)
    ? asset('upload/formateur_images/'.$profileData->photo)
    : asset('upload/NoPhoto.png');
@endphp

{{-- EN-TÊTE (harmonisée + RGPD court) --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Préférences</h1>
    <p class="text-sous-titre font-varela text-orangeone">Modifier mes informations</p>
    <div class="text-sm text-gray-600 font-lisible mt-2">
      Modifiez votre photo et vos coordonnées.
    </div>

    {{-- Mention RGPD (courte, utile) --}}
    <p class="mt-3 text-sm text-gray-600 font-lisible">
      Seules les données nécessaires à la gestion du compte sont demandées.
      <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
      Pour exercer vos droits :
      <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
    </p>
  </div>
</div>

{{-- CONTENU PRINCIPAL : deux colonnes --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- FORMULAIRE --}}
  <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">
    <form method="POST" action="{{ route('formateur.profil.store') }}" enctype="multipart/form-data" novalidate>
      @csrf

      {{-- Erreurs (accessible) --}}
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

      {{-- Message succès --}}
      @if (session('message'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
          <p class="text-sm font-semibold text-green-700">{{ session('message') }}</p>
        </div>
      @endif

      {{-- PHOTO (minimisation + explication courte) --}}
      <div class="flex flex-col items-center text-center mb-8">
        <div class="relative w-28 h-28">
          <img
            id="avatar"
            src="{{ $photoUrl }}"
            alt="Photo de profil"
            class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md"
          >
        </div>

        <div class="mt-4 w-full max-w-sm">
          <label for="photo" class="block text-sm font-semibold text-[#004461]">Changer la photo</label>
          <p class="text-xs text-gray-500 mt-1">Utilisée uniquement pour l’affichage du profil.</p>

          <input
            id="photo"
            type="file"
            name="photo"
            accept="image/*"
            class="mt-3 block w-full text-sm text-gray-700
                   file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                   file:text-sm file:font-semibold file:bg-gray-100 file:text-[#004461]
                   hover:file:bg-gray-200"
          >
        </div>
      </div>

      {{-- CHAMPS (minimisés, labels accessibles) --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        <div>
          <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
          <input
            id="prenom"
            type="text"
            name="prenom"
            value="{{ old('prenom', $profileData->prenom ?? '') }}"
            class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
            autocomplete="given-name"
          >
        </div>

        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
          <input
            id="name"
            type="text"
            name="name"
            value="{{ old('name', $profileData->name ?? '') }}"
            class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
            autocomplete="family-name"
          >
        </div>

        <div class="sm:col-span-2">
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', $profileData->email ?? '') }}"
            class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
            autocomplete="email"
          >
        </div>

        <div>
          <label for="phoneNumber" class="block text-sm font-medium text-gray-700">Téléphone</label>
          <input
            id="phoneNumber"
            type="text"
            name="phoneNumber"
            value="{{ $phoneValue }}"
            class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
            autocomplete="tel"
          >
        </div>

        <div>
          <label for="address" class="block text-sm font-medium text-gray-700">Adresse</label>
          <input
            id="address"
            type="text"
            name="address"
            value="{{ old('address', $profileData->address ?? '') }}"
            class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
            autocomplete="street-address"
          >
        </div>

      </div>

      {{-- ACTIONS --}}
      <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
        <button
          type="submit"
          class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-semibold bg-orangeone text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone"
        >
          Enregistrer
        </button>

        <a
          href="{{ route('formateur.profile') }}"
          class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-[#004461] hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone"
        >
          Annuler
        </a>
      </div>

    </form>
  </div>

  {{-- SIDEBAR --}}
  <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit" aria-label="Navigation Mon Espace">
    <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
    <ul class="space-y-3 text-sm">
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
        <a href="{{ route('formateur.profile') }}" class="text-gray-700 hover:text-[#004461] font-medium">Profil</a>
      </li>
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
        <a href="{{ route('formateur.parametre') }}" class="text-[#E94D2A] font-semibold" aria-current="page">Préférences</a>
      </li>
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
        <a href="{{ route('formateur.securite') }}" class="text-gray-700 hover:text-[#004461] font-medium">Sécurité</a>
      </li>
    </ul>
  </aside>

</div>

{{-- Rafraîchissement avatar après succès (sans dépendre d’un paramètre externe) --}}
@if (session('message'))
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const img = document.querySelector('img#avatar');
    if (img) {
      const base = img.src.split('?')[0];
      img.src = base + '?v=' + Date.now();
    }
  });
</script>
@endif

@endsection
