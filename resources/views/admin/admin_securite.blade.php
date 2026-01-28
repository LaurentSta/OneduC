{{-- /home/laurents/Oneduc_Dev/resources/views/admin/admin_securite.blade.php --}}

@extends('admin.admin_dashboard')
@section('admin')

@php
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');
@endphp

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    {{-- EN-TÊTE (Bootstrap wrapper -> pas de header qui passe dessous) --}}
    <div class="max-w-[1285px] mx-auto px-0 px-md-3 pt-2">
      <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
        <h1 class="text-titre font-raleway text-bleuone mb-2">Sécurité</h1>
        <p class="text-sous-titre font-varela text-orangeone">Modifier mon mot de passe</p>

        <div class="text-sm text-gray-600 font-lisible mt-2">
          Changez votre mot de passe pour sécuriser l’accès à votre compte.
        </div>

        <p class="mt-3 text-sm text-gray-600 font-lisible">
          Cette action renforce la sécurité de votre compte.
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
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
              <p class="text-sm font-semibold text-red-700">Le formulaire contient des erreurs.</p>
              <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if (session('message'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4" role="status">
              <p class="text-sm font-semibold text-green-700">{{ session('message') }}</p>
            </div>
          @endif

          @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
              <p class="text-sm font-semibold text-red-700">{{ session('error') }}</p>
            </div>
          @endif

          <form method="POST" action="{{ route('admin.securite.update') }}" novalidate>
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

              <div class="sm:col-span-2">
                <label for="currentPassword" class="block text-sm font-medium text-gray-700">
                  Mot de passe actuel
                </label>
                <input
                  id="currentPassword"
                  type="password"
                  name="currentPassword"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  autocomplete="current-password"
                  required
                >
              </div>

              <div>
                <label for="newPassword" class="block text-sm font-medium text-gray-700">
                  Nouveau mot de passe
                </label>
                <input
                  id="newPassword"
                  type="password"
                  name="newPassword"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  autocomplete="new-password"
                  minlength="12"
                  required
                >
                <p class="mt-2 text-xs text-gray-500">Conseil : 12 caractères ou plus.</p>
              </div>

              <div>
                <label for="newPassword_confirmation" class="block text-sm font-medium text-gray-700">
                  Confirmer le nouveau mot de passe
                </label>
                <input
                  id="newPassword_confirmation"
                  type="password"
                  name="newPassword_confirmation"
                  class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                  autocomplete="new-password"
                  minlength="12"
                  required
                >
              </div>

            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
              <button type="submit"
                      class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-semibold bg-orangeone text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone">
                Modifier le mot de passe
              </button>

              <a href="{{ route('admin.profile') }}"
                 class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-[#004461] hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone">
                Annuler
              </a>
            </div>

          </form>

          {{-- RGPD : minimisation / recommandation --}}
          <p class="mt-6 text-xs text-gray-500">
            Pour votre sécurité, le mot de passe n’est jamais affiché ni stocké en clair.
          </p>

        </div>

        {{-- SIDEBAR --}}
        <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit" aria-label="Navigation Mon Espace">
          <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
          <ul class="space-y-3 text-sm">
            <li class="flex items-center space-x-2">
              <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
              <a href="{{ route('admin.profile') }}" class="text-gray-700 hover:text-[#004461] font-medium">Profil</a>
            </li>

            <li class="flex items-center space-x-2">
              <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
              <a href="{{ route('admin.parametre') }}" class="text-gray-700 hover:text-[#004461] font-medium">Préférences</a>
            </li>

            <li class="flex items-center space-x-2">
              <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
              <a href="{{ route('admin.securite') }}" class="text-[#E94D2A] font-semibold" aria-current="page">Sécurité</a>
            </li>
          </ul>
        </aside>

      </div>
    </div>

  </div>
  <div class="content-backdrop fade"></div>
</div>

@endsection
