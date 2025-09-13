@extends('frontend.master')

@section('home')

{{-- BLOC INTRO — Inscription Formateur --}}
<div class="container mx-auto px-4 pt-8 pb-2">
  <div class="bg-white rounded-[20px] shadow-md p-8 mb-4 w-full max-w-[1285px] mx-auto">
    <div class="grid grid-cols-12 gap-6 items-center">
      {{-- Texte --}}
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Inscription Formateur</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Rejoignez la communauté Onéduc.
        </x-typography>
        <x-typography>
          Vous êtes formateur et souhaitez partager vos connaissances à travers des parcours interactifs et accessibles ?
          Complétez ce formulaire pour créer votre compte. Vous pourrez ensuite gérer vos groupes, suivre les progrès de vos stagiaires et enrichir leurs apprentissages.
        </x-typography>
      </div>
      {{-- Image --}}
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          {!! file_get_contents(public_path('images/svg/Enseignant.svg')) !!}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="content-wrapper bg-white py-10">
  <div class="mx-auto px-4 max-w-[1285px]">
    <div class="card shadow-sm p-6">

      <p class="text-sm text-gray-600 mb-6">* Champs obligatoires</p>

      {{-- Messages de session --}}
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

      <form action="{{ route('formateur.inscription') }}" method="POST" class="space-y-6" novalidate>
        @csrf

        {{-- Honeypot anti-bot --}}
        <div class="sr-only">
          <label for="website">Ne pas remplir</label>
          <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Prénom --}}
          <div>
            <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom *</label>
            <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required
                   autocomplete="given-name"
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('prenom') ? 'border-red-500' : 'border-gray-300' }}">
            @error('prenom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Nom --}}
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nom *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   autocomplete="family-name"
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Téléphone --}}
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone (optionnel)</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                   autocomplete="tel"
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 border-gray-300">
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Société --}}
          <div>
            <label for="societe" class="block text-sm font-medium text-gray-700">Société ou Asso (optionnel)</label>
            <input type="text" id="societe" name="societe" value="{{ old('societe') }}"
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 border-gray-300">
            @error('societe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Mot de passe --}}
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe *</label>
            <input type="password" id="password" name="password" required
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }}">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Confirmation mot de passe --}}
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmation *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   class="mt-1 block w-full border rounded-md shadow-sm p-2 border-gray-300">
          </div>
        </div>

        {{-- Adresse --}}
        <div>
          <label for="address" class="block text-sm font-medium text-gray-700">Adresse (optionnel)</label>
          <input type="text" id="address" name="address" value="{{ old('address') }}"
                 class="mt-1 block w-full border rounded-md shadow-sm p-2 border-gray-300">
          @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- reCAPTCHA --}}
        @error('g-recaptcha-response')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <div class="mt-2">
          {!! NoCaptcha::display(['data-theme' => 'light']) !!}
        </div>

        {{-- Boutons --}}
        <div class="pt-4 flex space-x-4">
          <button type="submit" class="btn-oneduc">S'inscrire</button>
          <button type="reset" class="btn btn-outline-secondary">Annuler</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
  {!! NoCaptcha::renderJs('fr') !!}
@endpush

@endsection
