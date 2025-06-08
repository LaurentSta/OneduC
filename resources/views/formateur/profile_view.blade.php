@extends('formateur.dashboard')
@section('title', 'Profil Formateur - Oneduc.fr')

@section('formateur')
<div class="container mx-auto px-4 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Profil Formateur</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Vos informations professionnelles
        </x-typography>
      </div>

    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <!-- Bloc Infos Profil -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <div class="flex justify-center mb-6">
        <img src="{{ (!empty($profileData->photo))
            ? url('upload/formateur_images/' . $profileData->photo)
            : url('upload/no_image.jpg') }}"
            class="w-32 h-32 rounded-full shadow-lg object-cover"
            alt="Photo de profil">
    </div>


        <div class="flex justify-center">
          <div class="grid gap-y-2 text-sm">
            <div class="grid grid-cols-[140px_auto] gap-x-4">
              <div class="text-right text-gray-700 font-medium">Email</div>
              <div class="text-left text-gray-500">{{ $profileData->email }}</div>
            </div>
            <div class="grid grid-cols-[140px_auto] gap-x-4">
              <div class="text-right text-gray-700 font-medium">Téléphone</div>
              <div class="text-left text-gray-500">{{ $profileData->phone ?? 'Non renseigné' }}</div>
            </div>
            <div class="grid grid-cols-[140px_auto] gap-x-4">
              <div class="text-right text-gray-700 font-medium">Adresse</div>
              <div class="text-left text-gray-500">{{ $profileData->address ?? 'Non renseignée' }}</div>
            </div>
            <div class="grid grid-cols-[140px_auto] gap-x-4">
              <div class="text-right text-gray-700 font-medium">Date d’ajout</div>
              <div class="text-left text-gray-500">{{ $profileData->created_at?->format('d/m/Y') }}</div>
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
          <a href="{{ route('formateur.profile') }}" class="text-[#E94D2A] font-semibold">Profil</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('formateur.parametre') }}" class="text-gray-700 hover:text-[#004461] font-medium">Préférences</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
          <a href="{{ route('formateur.securite') }}" class="text-gray-700 hover:text-[#004461] font-medium">Sécurité</a>
        </li>
      </ul>
    </aside>
  </div>
</div>
@endsection
