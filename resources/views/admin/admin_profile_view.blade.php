{{-- /home/laurents/Oneduc_Dev/resources/views/admin/admin_profile_view.blade.php --}}

@extends('admin.admin_dashboard')
@section('admin')

@php
  // Liens RGPD
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');

  $photoUrl = !empty($profileData->photo)
    ? asset('upload/admin_images/'.$profileData->photo)
    : asset('upload/admin_images/NoPhoto.png');

  $username  = $profileData->username ?? '';
  $prenom    = $profileData->prenom ?? '';
  $name      = $profileData->name ?? '';
  $email     = $profileData->email ?? '';
  $telephone = $profileData->phone ?: null;
  $adresse   = $profileData->address ?: null;
  $dateAjout = $profileData->created_at?->format('d/m/Y');

  $fullName = trim($prenom.' '.$name);
@endphp

{{-- EN-TÊTE (harmonisé + RGPD court) --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Mon profil</h1>
    <p class="text-sous-titre font-varela text-orangeone">Informations essentielles</p>

    <div class="text-sm text-gray-600 font-lisible mt-2">
      Consultez vos informations et gérez votre compte administrateur.
    </div>

    <p class="mt-3 text-sm text-gray-600 font-lisible">
      Vos données sont utilisées pour la gestion de votre compte et l’administration de la plateforme.
      <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
      Pour exercer vos droits :
      <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
    </p>
  </div>
</div>

{{-- CONTENU PRINCIPAL : deux colonnes --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- COLONNE GAUCHE --}}
  <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">

    <div class="flex flex-col items-center text-center mb-8">
      <div class="w-32 h-32">
        <img
          src="{{ $photoUrl }}"
          class="w-32 h-32 rounded-full shadow-lg object-cover border-4 border-orangeone"
          alt="Photo de profil"
        >
      </div>

      <h2 class="text-xl font-semibold mt-4">
        {{ $fullName ?: $username }}
      </h2>
      <p class="text-gray-500 text-sm">Administrateur</p>
    </div>

    {{-- Cartes --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

      <div class="rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500">Identifiant</p>
        <p class="mt-1 text-sm text-gray-700 break-words">{{ $username ?: 'Non renseigné' }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500">Email</p>
        <p class="mt-1 text-sm text-gray-700 break-words">{{ $email }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500">Téléphone</p>
        <p class="mt-1 text-sm text-gray-700">{{ $telephone ?: 'Non renseigné' }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-500">Date d’ajout</p>
        <p class="mt-1 text-sm text-gray-700">{{ $dateAjout ?: 'Non disponible' }}</p>
      </div>

      <div class="rounded-xl border border-gray-100 p-4 sm:col-span-2">
        <p class="text-xs font-semibold text-gray-500">Adresse</p>
        <p class="mt-1 text-sm text-gray-700">{{ $adresse ?: 'Non renseignée' }}</p>
      </div>

    </div>

    {{-- Actions rapides : correction des liens (FIN des /admin/profile/edit et /admin/security) --}}
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('admin.parametre') }}"
         class="btn-oneduc !px-5 !py-2.5 !text-sm">
        Modifier mes informations
      </a>

      <a href="{{ route('admin.securite') }}"
         class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
        Modifier mon mot de passe
      </a>
    </div>

  </div>

  {{-- SIDEBAR --}}
  @include('admin.partials.profile_menu')

</div>

@endsection
