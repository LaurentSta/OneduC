@extends('formateur.dashboard')
@section('title', 'Profil Formateur - Oneduc.fr')

@section('formateur')

{{-- 🧩 En-tête harmonisée --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Profil Formateur</h1>
    <p class="text-sous-titre font-varela text-orangeone">Vos informations professionnelles</p>
    <div class="text-sm text-gray-600 font-lisible mt-2">Gérez et vérifiez vos données personnelles liées à votre compte Oneduc.</div>
  </div>
</div>

{{-- 🧩 Contenu principal : deux colonnes --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- Bloc gauche : infos formateur --}}
  <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">
    <div class="flex justify-center mb-6">
      <img src="{{ (!empty($profileData->photo)) ? url('upload/formateur_images/' . $profileData->photo) : url('upload/no_image.jpg') }}"
           class="w-32 h-32 rounded-full shadow-lg object-cover" alt="Photo de profil">
    </div>

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

  {{-- Bloc droit : sidebar navigation --}}
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

@endsection
