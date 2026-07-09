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

<div class="rounded-[20px] border border-gray-100 bg-white shadow-md px-6 py-6 md:px-8 md:py-7 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
    <div class="lg:col-span-8">
      <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Sécurité']]" />

      <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
        Sécurité
      </h1>
      <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
        Mot de passe et suppression du compte
      </p>
      <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
        Gérez les actions sensibles liées à votre compte stagiaire.
      </p>

      {{-- Mention RGPD (courte) --}}
      <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600">
        Cette action sécurise votre compte. Consultez la
        <a href="{{ $privacyUrl }}" class="text-orangeone hover:underline">politique de confidentialité</a>.
        Pour exercer vos droits :
        <a href="{{ $contactUrl }}" class="text-orangeone hover:underline">contact</a>.
      </p>
    </div>
  </div>
</div>

<div class="w-full max-w-[1285px] mx-auto px-0">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

    <div class="lg:col-span-2 space-y-8">
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

        @if (session('message') || session('status'))
          <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-semibold text-green-700">{{ session('message') ?? session('status') }}</p>
          </div>
        @endif

        <form method="POST" action="{{ route('stagiaire.securite') }}" novalidate>
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
                    class="btn-oneduc !px-6 !py-2.5 !text-sm">
              Modifier le mot de passe
            </button>

            <a href="{{ route('stagiaire.profile') }}"
               class="btn-oneduc-outline !px-6 !py-2.5 !text-sm">
              Annuler
            </a>
          </div>

        </form>
      </div>

      <x-account-deletion-zone
        :form-action="route('stagiaire.account.destroy')"
        title="Supprimer mon compte stagiaire"
        description="La suppression du compte stagiaire est définitive. Vous perdrez l’accès à votre espace et toutes les données pédagogiques liées à votre parcours."
        modal-title="Supprimer définitivement le compte stagiaire"
        modal-description="Après confirmation, votre progression et toutes les données liées à vos activités de formation ne pourront plus être récupérées."
        :consequences="[
          'Votre accès à la plateforme sera fermé définitivement.',
          'Vos progressions, résultats, traces SCORM, tentatives de quiz, retours pédagogiques et activités liées à votre compte seront supprimés.',
          'Vous ne pourrez plus récupérer vos données ni vous reconnecter après validation.',
        ]"
        submit-label="Supprimer définitivement le compte stagiaire"
        password-label="Mot de passe actuel du stagiaire"
        password-placeholder="Confirmez avec votre mot de passe actuel"
      />
    </div>

    {{-- SIDEBAR --}}
    @include('stagiaire.partials.profile_menu')

  </div>
</div>

@endsection
