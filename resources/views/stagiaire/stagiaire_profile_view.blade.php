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
<div class="rounded-[20px] border border-gray-100 bg-white shadow-md px-6 py-6 md:px-8 md:py-7 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
    <div class="lg:col-span-8">
      <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Mon profil']]" />

      <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
        Mon profil
      </h1>
      <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
        Informations essentielles
      </p>
      <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
        Consultez vos informations et accédez aux réglages (préférences et sécurité).
      </p>

      {{-- Mention RGPD (courte) --}}
      <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600">
        Vos données sont utilisées pour la gestion de votre compte et le suivi pédagogique.
        <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
        Pour exercer vos droits, contactez-nous via
        <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">le formulaire de contact</a>.
      </p>
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
