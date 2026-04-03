{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/securite.blade.php (ou équivalent) --}}

@extends('formateur.dashboard')
@section('title', 'Sécurité - Oneduc.fr')

@section('formateur')

@php
  // Liens RGPD (sans casser si route absente)
  $privacyUrl = \Illuminate\Support\Facades\Route::has('page.confidentialite')
    ? route('page.confidentialite')
    : url('/confidentialite');

  $contactUrl = \Illuminate\Support\Facades\Route::has('contact')
    ? route('contact')
    : url('/contact');
@endphp

{{-- EN-TÊTE (harmonisée + RGPD court) --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Sécurité</h1>
    <p class="text-sous-titre font-varela text-orangeone">Mot de passe et suppression du compte</p>

    <div class="text-sm text-gray-600 font-lisible mt-2">
      Gérez les actions sensibles liées à votre compte formateur.
    </div>

    {{-- Mention RGPD (courte, utile) --}}
    <p class="mt-3 text-sm text-gray-600 font-lisible">
      Cette action renforce la sécurité de votre compte.
      <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">Politique de confidentialité</a>.
      Pour exercer vos droits :
      <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
    </p>
  </div>
</div>

{{-- CONTENU PRINCIPAL --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- FORMULAIRE SÉCURITÉ --}}
  <div class="lg:col-span-2 space-y-8">
    <div class="bg-white rounded-[20px] shadow-md p-8 w-full">

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

      {{-- Succès (si flash) --}}
      @if (session('message') || session('status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
          <p class="text-sm font-semibold text-green-700">{{ session('message') ?? session('status') }}</p>
        </div>
      @endif

      <form method="POST" action="{{ route('formateur.securite') }}" novalidate>
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full">

          {{-- Mot de passe actuel --}}
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

          {{-- Nouveau mot de passe --}}
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
              required
              minlength="12"
            >
            <p class="mt-2 text-xs text-gray-500">Conseil : 12 caractères ou plus.</p>
          </div>

          {{-- Confirmation --}}
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
              required
              minlength="12"
            >
          </div>

        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
          <button
            type="submit"
            class="btn-oneduc !px-6 !py-2.5 !text-sm"
          >
            Modifier le mot de passe
          </button>

          <a
            href="{{ route('formateur.profile') }}"
            class="btn-oneduc-outline !px-6 !py-2.5 !text-sm"
          >
            Annuler
          </a>
        </div>
      </form>
    </div>

    <x-account-deletion-zone
      :form-action="route('formateur.account.destroy')"
      title="Supprimer mon compte formateur"
      description="La suppression du compte formateur ferme définitivement votre espace et efface les données qui en dépendent. Cette action ne pourra pas être annulée."
      modal-title="Supprimer définitivement le compte formateur"
      modal-description="Avant de confirmer, prenez le temps de relire les impacts ci-dessous. Une fois la suppression validée, vos données et celles rattachées uniquement à votre espace ne pourront plus être récupérées."
      :consequences="[
        'Vos groupes et leurs rattachements pédagogiques seront supprimés définitivement.',
        'Les stagiaires rattachés uniquement à votre espace ou à vos groupes pourront être supprimés avec leurs données associées.',
        'Les progressions, résultats, traces SCORM, tentatives de quiz, retours pédagogiques, tableaux blancs et notifications liés à ces comptes seront perdus.',
        'Les stagiaires encore liés à un autre formateur ne seront pas supprimés, mais ils ne dépendront plus de votre compte.',
      ]"
      submit-label="Supprimer définitivement le compte formateur"
      password-label="Mot de passe actuel du formateur"
      password-placeholder="Confirmez avec votre mot de passe actuel"
    />
  </div>

  {{-- SIDEBAR --}}
  @include('formateur.partials.profile_menu')

</div>

@endsection
