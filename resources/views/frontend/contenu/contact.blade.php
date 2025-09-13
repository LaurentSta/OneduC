@extends('frontend.master')
@section('title', 'Contactez-nous')
@section('home')

<div class="container mx-auto px-4 pt-8 pb-2">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Formulaire de contact</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Une question, une remarque ? Écrivez-nous.
        </x-typography>
        <x-typography>
          Remplissez ce formulaire, notre équipe vous répondra dans les plus brefs délais.
          Merci d’indiquer votre profil pour adapter au mieux notre réponse.
        </x-typography>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          {!! file_get_contents(public_path('frontend/assets/img/illustrations/AssociationOneduc.svg')) !!}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="content-wrapper bg-white py-10">
  <div class="mx-auto px-4 max-w-[1285px]">
    <div class="card shadow-sm p-6">

      {{-- Alertes --}}
      @if(session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4 text-green-800" role="alert">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4 text-red-800" role="alert" aria-live="assertive">
          <p class="font-medium">Le formulaire comporte des erreurs.</p>
          <ul class="mt-2 list-disc list-inside text-sm">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
          </ul>
        </div>
      @endif

      <p class="text-sm text-gray-600 mb-6">* Champs obligatoires</p>

      <form action="{{ route('contact.send') }}" method="POST" class="space-y-6" novalidate>
        @csrf

        {{-- Honeypot anti-bot --}}
        <div class="sr-only">
          <label for="website">Ne pas remplir</label>
          <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Prénom --}}
          <div>
            <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
            <input id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" autocomplete="given-name"
              class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('prenom') ? 'border-red-500' : 'border-gray-300' }}">
            @error('prenom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Nom --}}
          <div>
            <label for="nom" class="block text-sm font-medium text-gray-700">Nom *</label>
            <input id="nom" name="nom" type="text" required value="{{ old('nom') }}" autocomplete="family-name"
              class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('nom') ? 'border-red-500' : 'border-gray-300' }}">
            @error('nom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Type d’utilisateur --}}
          <div>
            <span class="block text-sm font-medium text-gray-700 mb-2">Je suis *</span>
            <div class="flex items-center gap-6">
              <label class="flex items-center">
                <input type="radio" name="type_utilisateur" value="formateur"
                  {{ old('type_utilisateur','formateur')==='formateur'?'checked':'' }}
                  class="form-radio text-orangeone focus:ring-orangeone" onchange="toggleObjetOptions(this.value)">
                <span class="ml-2 text-sm text-gray-700">Formateur</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="type_utilisateur" value="stagiaire"
                  {{ old('type_utilisateur')==='stagiaire'?'checked':'' }}
                  class="form-radio text-orangeone focus:ring-orangeone" onchange="toggleObjetOptions(this.value)">
                <span class="ml-2 text-sm text-gray-700">Stagiaire</span>
              </label>
            </div>
            @error('type_utilisateur') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Objet conditionnel --}}
          <div>
            <label class="block text-sm font-medium text-gray-700">Objet *</label>

            <div id="objet-formateur" class="mt-2 hidden">
              <label for="objet_formateur" class="block text-sm text-gray-500">Formateur</label>
              <select id="objet_formateur" name="objet_formateur"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Sélectionnez un objet</option>
                <option value="demande_info" @selected(old('objet_formateur')==='demande_info')>Demande d'information</option>
                <option value="support" @selected(old('objet_formateur')==='support')>Support technique</option>
                <option value="creation_module" @selected(old('objet_formateur')==='creation_module')>Demande de création de leçon/module</option>
                <option value="autre" @selected(old('objet_formateur')==='autre')>Autre</option>
              </select>
              @error('objet_formateur') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div id="objet-stagiaire" class="mt-2 hidden">
              <label for="objet_stagiaire" class="block text-sm text-gray-500">Stagiaire</label>
              <select id="objet_stagiaire" name="objet_stagiaire"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                <option value="">Sélectionnez un objet</option>
                <option value="bug" @selected(old('objet_stagiaire')==='bug')>Signaler un bug</option>
                <option value="incomprehension" @selected(old('objet_stagiaire')==='incomprehension')>Incompréhension sur une leçon</option>
                <option value="probleme_connexion" @selected(old('objet_stagiaire')==='probleme_connexion')>Problème de connexion</option>
              </select>
              @error('objet_stagiaire') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="email"
              class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Téléphone --}}
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" autocomplete="tel"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Créneau d’appel --}}
          <div>
            <label for="heure_appel" class="block text-sm font-medium text-gray-700">Meilleur moment pour être appelé</label>
            <select id="heure_appel" name="heure_appel"
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
              <option value="">Sélectionner une heure</option>
              @for ($h = 8; $h <= 18; $h++)
                <option value="{{ $h }}h"   @selected(old('heure_appel') === $h.'h')>{{ $h }}h</option>
                <option value="{{ $h }}h30" @selected(old('heure_appel') === $h.'h30')>{{ $h }}h30</option>
              @endfor
            </select>
            @error('heure_appel') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Message --}}
        <div>
          <label for="message" class="block text-sm font-medium text-gray-700">Votre message / question *</label>
          <textarea id="message" name="message" rows="4" required
            class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('message') ? 'border-red-500' : 'border-gray-300' }}">{{ old('message') }}</textarea>
          @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- reCAPTCHA toujours affiché --}}
        @error('g-recaptcha-response')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <div class="mt-4">
          {!! NoCaptcha::display(['data-theme' => 'light', 'data-size' => 'normal']) !!}
        </div>

        {{-- Boutons --}}
        <div class="pt-4 flex space-x-4">
          <button type="submit" class="btn-oneduc">Envoyer</button>
          <button type="reset" class="btn btn-outline-secondary">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
  {!! NoCaptcha::renderJs('fr') !!}
  <script>
    function toggleObjetOptions(type) {
      const f = document.getElementById('objet-formateur');
      const s = document.getElementById('objet-stagiaire');
      f.classList.add('hidden'); s.classList.add('hidden');
      if (type === 'formateur') f.classList.remove('hidden'); else if (type === 'stagiaire') s.classList.remove('hidden');
    }
    document.addEventListener("DOMContentLoaded", function () {
      toggleObjetOptions(@json(old('type_utilisateur','formateur')));
    });
  </script>
@endpush

@endsection
