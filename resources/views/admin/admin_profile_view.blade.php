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
         class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-semibold bg-orangeone text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone">
        Modifier mes informations
      </a>

      <a href="{{ route('admin.securite') }}"
         class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-[#004461] hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone">
        Modifier mon mot de passe
      </a>
    </div>

  </div>

  {{-- SIDEBAR --}}
  <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit" aria-label="Navigation Mon Espace">
    <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
    <ul class="space-y-3 text-sm">
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
        <a href="{{ route('admin.profile') }}" class="text-[#E94D2A] font-semibold" aria-current="page">
          Profil
        </a>
      </li>

      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
        <a href="{{ route('admin.parametre') }}"
           class="text-gray-700 hover:text-[#004461] font-medium">
          Préférences
        </a>
      </li>

      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
        <a href="{{ route('admin.securite') }}"
           class="text-gray-700 hover:text-[#004461] font-medium">
          Sécurité
        </a>
      </li>
    </ul>
  </aside>

</div>

@endsection
