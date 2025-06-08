@extends('formateur.dashboard')
@section('title', 'Sécurité - Oneduc.fr')

@section('formateur')
<div class="container mx-auto px-4 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Sécurité</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Modifier votre mot de passe
        </x-typography>
      </div>

    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <!-- Formulaire sécurité -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <form method="POST" action="{{ route('formateur.securite') }}">
          @csrf

          <div class="grid gap-y-4 text-sm">
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
    </div>

    <!-- Sidebar Navigation -->
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
</div>
@endsection
