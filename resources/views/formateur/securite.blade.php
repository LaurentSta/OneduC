@extends('formateur.dashboard')
@section('title', 'Sécurité - Oneduc.fr')

@section('formateur')

{{-- 🧩 En-tête harmonisée --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Sécurité</h1>
    <p class="text-sous-titre font-varela text-orangeone">Modifier votre mot de passe</p>
    <div class="text-sm text-gray-600 font-lisible mt-2">Vous pouvez ici changer votre mot de passe pour sécuriser votre compte.</div>
  </div>
</div>

{{-- 🧩 Contenu principal --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- Formulaire sécurité --}}
  <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8 w-full">
    <form method="POST" action="{{ route('formateur.securite') }}">
      @csrf

      <div class="grid gap-y-4 text-sm w-full max-w-md mx-auto">
        <div class="grid grid-cols-[180px_auto] gap-x-4 items-center">
          <label class="text-right text-gray-700 font-medium">Mot de passe actuel</label>
          <input type="password" name="currentPassword" class="w-full rounded-lg border-gray-300">
        </div>
        <div class="grid grid-cols-[180px_auto] gap-x-4 items-center">
          <label class="text-right text-gray-700 font-medium">Nouveau mot de passe</label>
          <input type="password" name="newPassword" class="w-full rounded-lg border-gray-300">
        </div>
        <div class="grid grid-cols-[180px_auto] gap-x-4 items-center">
          <label class="text-right text-gray-700 font-medium">Confirmer le nouveau</label>
          <input type="password" name="newPassword_confirmation" class="w-full rounded-lg border-gray-300">
        </div>
      </div>

      <div class="mt-6 text-center">
        <button type="submit" class="btn-oneduc">
          Modifier le mot de passe
        </button>
      </div>
    </form>
  </div>

  {{-- Sidebar navigation --}}
  <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit">
    <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
    <ul class="space-y-3 text-sm">
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
        <a href="{{ route('formateur.profile') }}" class="text-gray-700 hover:text-[#004461] font-medium">Profil</a>
      </li>
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
        <a href="{{ route('formateur.parametre') }}" class="text-gray-700 hover:text-[#004461] font-medium">Préférences</a>
      </li>
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
        <a href="{{ route('formateur.securite') }}" class="text-[#E94D2A] font-semibold">Sécurité</a>
      </li>
    </ul>
  </aside>
</div>

@endsection
