@extends('stagiaire.master')
@section('title', 'Mon Profil - Oneduc.fr')

@section('content')

{{-- /resources/views/stagiaire/stagiaire_profile_view.blade.php --}}

@php
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');

  $email = $profileData->email;
  $adresse = $profileData->address ?: null;
  $telephone = $profileData->phone ?: null;
  $photoVersion = $profileData->updated_at?->timestamp ?? $profileData->created_at?->timestamp ?? time();
  $photoUrl = !empty($profileData->photo)
    ? asset('upload/user_images/'.$profileData->photo).'?v='.$photoVersion
    : asset('upload/NoPhoto.png');

  $siteTimeSeconds = (int) ($totalSiteTime ?? 0);
  $tempsSite = gmdate('H\h i\m s\s', max(0, $siteTimeSeconds));
@endphp

{{-- EN-TÊTE DE PAGE --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Mon profil</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Informations essentielles
      </x-typography>
      <x-typography>
        Consultez vos informations et accédez aux réglages (préférences et sécurité).
      </x-typography>

      {{-- Mention RGPD (courte) --}}
      <p class="mt-2 text-sm text-gray-600">
        Vos données sont utilisées pour la gestion de votre compte et le suivi pédagogique.
        <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
        Pour exercer vos droits, contactez-nous via
        <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">le formulaire de contact</a>.
      </p>

      {{-- Fil d’Ariane --}}
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
          <li class="text-gray-400">Mon profil</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

{{-- CONTENU PRINCIPAL --}}
<div class="w-full max-w-[1285px] mx-auto px-0">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    {{-- COLONNE PRINCIPALE --}}
    <div class="lg:col-span-2">
      <section class="bg-white rounded-[20px] shadow-md p-8 w-full" aria-labelledby="profil-identite">

        <div class="flex flex-col items-center text-center mb-8">
          <div class="relative group w-28 h-28">
            <img
              src="{{ $photoUrl }}"
              alt="Photo de profil"
              class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md"
            >
          </div>

          <h2 id="profil-identite" class="text-xl font-semibold mt-4">
            {{ $profileData->prenom }} {{ $profileData->name }}
          </h2>
          <p class="text-gray-500 text-sm">Stagiaire</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500">Email</p>
            <p class="mt-1 text-sm text-gray-700 break-words">{{ $email }}</p>
          </div>

          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500">Temps sur le site</p>
            <p class="mt-1 text-sm text-gray-700">{{ $tempsSite }}</p>
          </div>

          {{-- Adresse (affichée seulement si renseignée) --}}
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500">Adresse</p>
            <p class="mt-1 text-sm text-gray-700">{{ $adresse ?: 'Non renseignée' }}</p>
          </div>

          {{-- Téléphone --}}
          <div class="rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500">Téléphone</p>
            <p class="mt-1 text-sm text-gray-700">{{ $telephone ?: 'Non renseigné' }}</p>
          </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
          <a href="{{ route('stagiaire.parametre') }}"
             class="btn-oneduc !px-5 !py-2.5 !text-sm">
            Modifier mes informations
          </a>
          <a href="{{ route('stagiaire.securite.show') }}"
             class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
            Modifier mon mot de passe
          </a>
        </div>

      </section>
    </div>

    {{-- SIDEBAR --}}
    @include('stagiaire.partials.profile_menu')

  </div>
</div>

@endsection
