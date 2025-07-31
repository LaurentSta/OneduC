@extends('stagiaire.master')
@section('title', 'Sécurité - Oneduc.fr')

@section('content')

{{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Sécurité --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Sécurité</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Modifier votre mot de passe
      </x-typography>
      <x-typography>
        Vous pouvez ici changer votre mot de passe pour sécuriser votre accès à la plateforme.
      </x-typography>

      {{-- 📍 Fil d’Ariane --}}
      <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
        <ol class="list-none p-0 inline-flex items-center space-x-1">
          <li class="flex items-center">
            <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

{{-- 🔐 FORMULAIRE SÉCURITÉ --}}

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Formulaire sécurité -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <form method="POST" action="{{ route('stagiaire.securite.show') }}">
          @csrf

          <div class="grid gap-y-4 text-sm">
            @php
              $fields = [
                'currentPassword' => 'Mot de passe actuel',
                'newPassword' => 'Nouveau mot de passe',
                'newPassword_confirmation' => 'Confirmer le nouveau'
              ];
            @endphp

            @foreach ($fields as $name => $label)
              <div class="grid grid-cols-[180px_auto] gap-x-4 items-center">
                <label class="text-right text-gray-700 font-medium">{{ $label }}</label>
                <input type="password" name="{{ $name }}" class="w-full rounded-lg border-gray-300">
              </div>
            @endforeach
          </div>

          <div class="mt-6 text-center">
            <button type="submit" class="btn-oneduc">
              Modifier le mot de passe
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Sidebar Navigation -->
    <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit">
      <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
      <ul class="space-y-3 text-sm">
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('stagiaire.profile') }}" class="text-gray-700 hover:text-[#004461] font-medium">Profil</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('stagiaire.parametre') }}" class="text-gray-700 hover:text-[#004461] font-medium">Préférences</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('stagiaire.securite.show') }}" class="text-[#E94D2A] font-semibold">Sécurité</a>
        </li>
      </ul>
    </aside>
  </div>

@endsection
