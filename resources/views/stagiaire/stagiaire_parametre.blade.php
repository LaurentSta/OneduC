@extends('stagiaire.master')
@section('title', 'Mes préférences - Oneduc.fr')

@section('content')
<div class="container mx-auto px-4 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Préférences</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Modifier vos informations personnelles
        </x-typography>
      </div>
    </div>
  </div>

   <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <!-- Formulaire préférences -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <form method="POST" action="{{ route('stagiaire.profil.store') }}" enctype="multipart/form-data">
          @csrf

          <!-- Avatar -->
          <div class="flex justify-center mb-6">
            <div class="relative group w-28 h-28">
              <img src="{{ !empty($profileData->photo) ? asset('upload/user_images/'.$profileData->photo) : asset('upload/NoPhoto.png') }}"
                   alt="Avatar"
                   class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md">
              <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                <label for="avatar-upload" class="cursor-pointer text-white text-xl">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7h2l.4-1.2A2 2 0 017.2 4h9.6a2 2 0 011.8 1.8L19 7h2a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                    <circle cx="12" cy="13" r="3"/>
                  </svg>
                  <input id="avatar-upload" type="file" name="photo" class="hidden" accept="image/*">
                </label>
              </div>
            </div>
          </div>

          <!-- Champs du formulaire -->
          <div class="flex justify-center">
            <div class="grid gap-y-4 text-sm w-full max-w-md">
              <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
                <label class="text-right text-gray-700 font-medium">Prénom</label>
                <input type="text" name="prenom" class="w-full rounded-lg border-gray-300" value="{{ old('prenom', $profileData->prenom) }}">
              </div>
              <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
                <label class="text-right text-gray-700 font-medium">Nom</label>
                <input type="text" name="name" class="w-full rounded-lg border-gray-300" value="{{ old('name', $profileData->name) }}">
              </div>
              <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
                <label class="text-right text-gray-700 font-medium">Email</label>
                <input type="email" name="email" class="w-full rounded-lg border-gray-300" value="{{ old('email', $profileData->email) }}">
              </div>
              <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
                <label class="text-right text-gray-700 font-medium">Téléphone</label>
                <input type="text" name="phoneNumber" class="w-full rounded-lg border-gray-300" value="{{ old('phoneNumber', $profileData->phone) }}">
              </div>
              <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
                <label class="text-right text-gray-700 font-medium">Adresse</label>
                <input type="text" name="address" class="w-full rounded-lg border-gray-300" value="{{ old('address', $profileData->address) }}">
              </div>
            </div>
          </div>

          <div class="mt-6 text-center">
            <button type="submit" class="btn-oneduc">
            Enregistrer les modifications
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
          <a href="{{ route('stagiaire.parametre') }}" class="text-[#E94D2A] font-semibold">Préférences</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('stagiaire.securite.show') }}" class="text-gray-700 hover:text-[#004461] font-medium">Sécurité</a>
        </li>
      </ul>
    </aside>
  </div>
</div>
@endsection
