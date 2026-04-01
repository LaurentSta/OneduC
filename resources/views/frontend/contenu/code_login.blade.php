@extends('frontend.master')
@section('title', "Accès Stagiaire - Oneduc.fr")
@section('home')

{{-- RECOUPÉRATION DE TA BANDEROLE --}}
<div class="container mx-auto px-4 pt-8 pb-2">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8 py-8">
        <x-typography variant="titre">Espace Stagiaire</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Accéder à votre formation
        </x-typography>
        <x-typography>
          Choisissez la méthode de connexion qui vous convient le mieux pour retrouver vos cours.
        </x-typography>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          <img src="{{ asset('frontend/assets/img/illustrations/Stagiaires.svg') }}" alt="Illustration Stagiaire" class="w-full h-auto">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mx-auto px-4 pb-12">
  <div class="max-w-[1285px] mx-auto">
    <div class="flex flex-col md:flex-row items-stretch gap-8 relative mt-6">
      
      {{-- OPTION 1 : LE CODE (Icône Clé) --}}
      <div class="flex-1 bg-white rounded-3xl shadow-sm border-b-8 border-orangeone p-8 text-center flex flex-col items-center">
        <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-orangeone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-bleuone mb-2">J'ai un code d'accès</h2>
        <p class="text-sm text-gray-400 mb-8">Saisissez les 6 caractères fournis par votre formateur.</p>

        <form method="POST" action="{{ route('stagiaire.code.login') }}" class="w-full mt-auto">
          @csrf
          <input type="text" name="code_acces" maxlength="6" required placeholder="CODE12"
                 class="w-full bg-gray-50 border-gray-200 border rounded-xl py-4 text-center text-2xl font-bold tracking-widest text-orangeone focus:ring-2 focus:ring-orangeone mb-4 uppercase">
          <button type="submit" class="btn-oneduc w-full !py-4">
            Valider mon code
          </button>
        </form>
      </div>

      {{-- Séparateur visuel OU --}}
      <div class="flex items-center justify-center">
        <span class="bg-gray-200 text-gray-500 text-xs font-bold px-3 py-1 rounded-full">OU</span>
      </div>

      {{-- OPTION 2 : IDENTIFIANTS (Icône Profil) --}}
      <div class="flex-1 bg-white rounded-3xl shadow-sm border-b-8 border-bleuone p-8 text-center flex flex-col items-center">
        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-bleuone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-bleuone mb-2">J'ai un compte</h2>
        <p class="text-sm text-gray-400 mb-8">Connectez-vous avec votre email et votre mot de passe.</p>

        <form method="POST" action="{{ route('login.process') }}" class="w-full mt-auto space-y-3">
          @csrf
          <input type="email" name="email" required placeholder="Votre adresse email"
                 class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-bleuone">
          <input type="password" name="password" required placeholder="Votre mot de passe"
                 class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-bleuone">
          <button type="submit" class="btn-oneduc-blue w-full !py-4">
            Se connecter
          </button>
        </form>
      </div>

    </div>
  </div>
</div>
@endsection
