{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/profile_view.blade.php --}}

@extends('formateur.dashboard')
@section('title', 'Profil Formateur - Oneduc.fr')

@section('formateur')

@php
  // Liens RGPD (sans casser si route absente)
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');

  // Minimisation + valeurs sécurisées
  $email     = $profileData->email;
  $telephone = $profileData->phone ?: null;
  $adresse   = $profileData->address ?: null;
  $dateAjout = $profileData->created_at?->format('d/m/Y');
@endphp

{{-- EN-TÊTE (harmonisée + RGPD court) --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Mon profil</h1>
    <p class="text-sous-titre font-varela text-orangeone">Informations essentielles</p>

    <div class="text-sm text-gray-600 font-lisible mt-2">
      Consultez vos informations et accédez aux réglages (préférences et sécurité).
    </div>

    {{-- Mention RGPD (courte, utile) --}}
    <p class="mt-3 text-sm text-gray-600 font-lisible">
      Vos données sont utilisées pour la gestion de votre compte et l’organisation pédagogique.
      <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
      Pour exercer vos droits :
      <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
    </p>
  </div>
</div>

{{-- CONTENU PRINCIPAL : deux colonnes --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- COLONNE GAUCHE : identité + infos essentielles --}}
  <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">

    <div class="flex flex-col items-center text-center mb-8">
      <div class="w-32 h-32">
        <img
          src="{{ !empty($profileData->photo) ? url('upload/formateur_images/' . $profileData->photo) : url('upload/no_image.jpg') }}"
          class="w-32 h-32 rounded-full shadow-lg object-cover border-4 border-orangeone"
          alt="Photo de profil"
        >
      </div>

      <h2 class="text-xl font-semibold mt-4">
        {{ trim(($profileData->prenom ?? '') . ' ' . ($profileData->name ?? '')) }}
      </h2>
      <p class="text-gray-500 text-sm">Formateur</p>
    </div>

    {{-- Minimisation : on affiche seulement l’essentiel --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

      <div class="rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500">Email</p>
        <p class="mt-1 text-sm text-gray-700 break-words">{{ $email }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500">Téléphone</p>
        <p class="mt-1 text-sm text-gray-700">{{ $telephone ?: 'Non renseigné' }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4 sm:col-span-2">
        <p class="text-xs font-semibold text-gray-500">Adresse</p>
        <p class="mt-1 text-sm text-gray-700">{{ $adresse ?: 'Non renseignée' }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4 sm:col-span-2">
        <p class="text-xs font-semibold text-gray-500">Date d’ajout</p>
        <p class="mt-1 text-sm text-gray-700">{{ $dateAjout ?: 'Non disponible' }}</p>
      </div>

    </div>

    {{-- Actions rapides (cohérence UX) --}}
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('formateur.parametre') }}"
         class="btn-oneduc !px-5 !py-2.5 !text-sm">
        Modifier mes informations
      </a>

      <a href="{{ route('formateur.securite') }}"
         class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
        Modifier mon mot de passe
      </a>
    </div>

  </div>

  {{-- COLONNE DROITE : sidebar navigation --}}
  @include('formateur.partials.profile_menu')

</div>

@endsection
