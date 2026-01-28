@extends('stagiaire.master')
@section('title', 'Sécurité - Oneduc.fr')

@section('content')

{{-- /resources/views/stagiaire/stagiaire_securite.blade.php --}}

@php
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');
@endphp

<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Sécurité</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Modifier mon mot de passe
      </x-typography>
      <x-typography>
        Utilisez un mot de passe long et unique.
      </x-typography>

      {{-- Mention RGPD (courte) --}}
      <p class="mt-2 text-sm text-gray-600">
        Cette action sécurise votre compte. Consultez la
        <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">politique de confidentialité</a>.
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
          <li class="text-gray-400">Sécurité</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="w-full max-w-[1285px] mx-auto px-0">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">

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

        @if (session('status'))
          <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-semibold text-green-700">{{ session('status') }}</p>
          </div>
        @endif

        {{-- Conserve ta route existante pour ne pas casser.
             Idéalement : une route update dédiée (POST/PATCH). --}}
        <form method="POST" action="{{ route('stagiaire.securite.show') }}" novalidate>
          @csrf

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
              <label for="currentPassword" class="block text-sm font-medium text-gray-700">
                Mot de passe actuel
              </label>
              <input id="currentPassword" type="password" name="currentPassword"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="current-password" required>
            </div>

            <div>
              <label for="newPassword" class="block text-sm font-medium text-gray-700">
                Nouveau mot de passe
              </label>
              <input id="newPassword" type="password" name="newPassword"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="new-password" required minlength="12">
              <p class="mt-2 text-xs text-gray-500">Conseil : 12 caractères ou plus.</p>
            </div>

            <div>
              <label for="newPassword_confirmation" class="block text-sm font-medium text-gray-700">
                Confirmer le nouveau
              </label>
              <input id="newPassword_confirmation" type="password" name="newPassword_confirmation"
                     class="mt-2 w-full rounded-xl border-gray-300 focus:border-orangeone focus:ring-orangeone"
                     autocomplete="new-password" required minlength="12">
            </div>
          </div>

          <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <button type="submit"
                    class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-semibold bg-orangeone text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone">
              Modifier le mot de passe
            </button>

            <a href="{{ route('stagiaire.profile') }}"
               class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-[#004461] hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone">
              Annuler
            </a>
          </div>

        </form>
      </div>
    </div>

    {{-- SIDEBAR --}}
    <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit" aria-label="Navigation Mon Espace">
      <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
      <ul class="space-y-3 text-sm">
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
          <a href="{{ route('stagiaire.profile') }}" class="text-gray-700 hover:text-[#004461] font-medium">Profil</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
          <a href="{{ route('stagiaire.parametre') }}" class="text-gray-700 hover:text-[#004461] font-medium">Préférences</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full" aria-hidden="true"></span>
          <a href="{{ route('stagiaire.securite.show') }}" class="text-[#E94D2A] font-semibold" aria-current="page">Sécurité</a>
        </li>
      </ul>
    </aside>

  </div>
</div>

@endsection
