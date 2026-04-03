{{-- /home/laurents/Oneduc_Dev/resources/views/admin/admin_parametre.blade.php --}}

@extends('admin.admin_dashboard')
@section('admin')

@php
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');

  $photoUrl = !empty($profileData->photo)
    ? asset('upload/admin_images/'.$profileData->photo)
    : asset('upload/admin_images/NoPhoto.png');
@endphp

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    {{-- EN-TÊTE (toujours au-dessus car dans le flux Bootstrap) --}}
    <div class="max-w-[1285px] mx-auto px-0 px-md-3 pt-2">
      <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
        <h1 class="text-titre font-raleway text-bleuone mb-2">Préférences</h1>
        <p class="text-sous-titre font-varela text-orangeone">Mettre à jour mes informations</p>

        <div class="text-sm text-gray-600 font-lisible mt-2">
          Modifiez vos coordonnées et votre photo de profil.
        </div>

        <p class="mt-3 text-sm text-gray-600 font-lisible">
          Vos données sont utilisées pour la gestion de votre compte et l’administration de la plateforme.
          <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
          Pour exercer vos droits :
          <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
        </p>
      </div>
    </div>

    {{-- CONTENU PRINCIPAL --}}
    <div class="max-w-[1285px] mx-auto px-0 px-md-3 pb-4">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- FORMULAIRE --}}
        <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">

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

          @if (session('message'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
              <p class="text-sm font-semibold text-green-700">{{ session('message') }}</p>
            </div>
          @endif

          <form method="POST" action="{{ route('admin.profil.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            {{-- Avatar --}}
            <div class="flex flex-col items-center text-center mb-8">
              <div class="relative group w-28 h-28">
                <img id="avatar"
                     src="{{ $photoUrl }}"
                     alt="Photo de profil"
                     class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md">
                <div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                  <label for="avatar-upload" class="cursor-pointer text-white text-sm font-semibold">
                    Modifier
                    <input id="avatar-upload" type="file" name="photo" class="hidden" accept="image/png, image/jpeg">
                  </label>
                </div>
              </div>

              <p class="mt-3 text-xs text-gray-500">
                Photo utilisée uniquement pour l’affichage du profil. JPG ou PNG, taille recommandée 800 Ko.
              </p>
            </div>

            {{-- Champs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

              <div>
                <label class="block text-sm font-medium text-gray-700" for="firstName">Prénom</label>
                <input
                  id="firstName"
                  name="firstName"
                  type="text"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  value="{{ old('firstName', $profileData->prenom ?? $profileData->username ?? '') }}"
                  autocomplete="given-name"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700" for="lastName">Nom</label>
                <input
                  id="lastName"
                  name="lastName"
                  type="text"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  value="{{ old('lastName', $profileData->name ?? '') }}"
                  autocomplete="family-name"
                >
              </div>

              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  value="{{ old('email', $profileData->email ?? '') }}"
                  autocomplete="email"
                  required
                >
              </div>

              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="phoneNumber">Téléphone</label>
                <input
                  id="phoneNumber"
                  name="phoneNumber"
                  type="text"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  value="{{ old('phoneNumber', $profileData->phoneNumber ?? ($profileData->phone ?? '')) }}"
                  autocomplete="tel"
                  placeholder="XX XX XX XX XX"
                >
              </div>

            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
              <button type="submit"
                      class="btn-oneduc !px-6 !py-2.5 !text-sm">
                Enregistrer
              </button>

              <a href="{{ route('admin.profile') }}"
                 class="btn-oneduc-outline !px-6 !py-2.5 !text-sm">
                Annuler
              </a>
            </div>

          </form>
        </div>

        {{-- SIDEBAR --}}
        @include('admin.partials.profile_menu')

      </div>
    </div>

  </div>
  <div class="content-backdrop fade"></div>
</div>

@if (session('message'))
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const img = document.querySelector('img#avatar');
    if (img) {
      const currentSrc = img.src.split('?')[0];
      img.src = `${currentSrc}?v=${Date.now()}`;
    }
  });
</script>
@endif
@endsection
