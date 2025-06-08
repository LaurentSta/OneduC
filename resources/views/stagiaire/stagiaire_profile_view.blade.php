@extends('stagiaire.master')
@section('title', 'Mon Profil - Oneduc.fr')

@section('content')
<div class="container mx-auto px-4 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Profil stagiaire</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Vos informations personnelles
        </x-typography>
      </div>

    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <!-- Bloc Infos Profil -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <div class="flex flex-col items-center text-center mb-6">
          <div class="relative group w-28 h-28">
            <img src="{{ !empty($profileData->photo) ? asset('upload/user_images/'.$profileData->photo) : asset('upload/admin_images/NoPhoto.png') }}"
                 alt="Avatar"
                 class="w-full h-full object-cover rounded-full border-4 border-orangeone shadow-md">
          </div>
          <h4 class="text-xl font-semibold mt-4">{{ $profileData->prenom }} {{ $profileData->name }}</h4>
          <p class="text-gray-500 text-sm">Stagiaire</p>
        </div>

        <div class="flex justify-center">
            <div class="grid gap-y-2 text-sm">
                <div class="grid grid-cols-[140px_auto] gap-x-4">
                <div class="text-right text-gray-700 font-medium">Email</div>
                <div class="text-left text-gray-500">{{ $profileData->email }}</div>
                </div>
                <div class="grid grid-cols-[140px_auto] gap-x-4">
                <div class="text-right text-gray-700 font-medium">Adresse</div>
                <div class="text-left text-gray-500">{{ $profileData->address ?? 'Non renseignée' }}</div>
                </div>
                <div class="grid grid-cols-[140px_auto] gap-x-4">
                <div class="text-right text-gray-700 font-medium">Date de début</div>
                <div class="text-left text-gray-500">À renseigner</div>
                </div>


                <div class="grid grid-cols-[140px_auto] gap-x-4">
                <div class="text-right text-gray-700 font-medium">Temps sur le site</div>
                <div class="text-left text-gray-500">{{ gmdate('H\h i\m s\s', $totalSiteTime ?? 0) }}</div>
                </div>
                <div class="grid grid-cols-[140px_auto] gap-x-4">
                <div class="text-right text-gray-700 font-medium">Code d’accès</div>
                <div class="text-left text-gray-500">À renseigner</div>
                </div>
            </div>
        </div>




      </div>
    </div>

    <!-- Sidebar Navigation -->
    <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit">
      <h3 class="text-lg font-semibold text-[#004461] mb-4">Mon Espace</h3>
      <ul class="space-y-3 text-sm">
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('stagiaire.profile') }}" class="text-[#E94D2A] font-semibold">Profil</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('stagiaire.parametre') }}" class="text-gray-700 hover:text-[#004461] font-medium">Préférences</a>
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
