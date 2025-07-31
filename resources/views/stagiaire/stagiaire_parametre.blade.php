@extends('stagiaire.master')
@section('title', 'Mes préférences - Oneduc.fr')

@section('content')

{{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Préférences --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Préférences</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Modifier vos informations personnelles
      </x-typography>
      <x-typography>
        Vous pouvez ici changer vos coordonnées personnelles et votre photo de profil.
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
          <li class="text-gray-400">Mes préférences</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

{{-- 📄 CONTENU PRINCIPAL – aligné avec l’en-tête --}}


  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
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

          <!-- Champs -->
          <div class="flex justify-center">
            <div class="grid gap-y-4 text-sm w-full max-w-md">
              @php $fields = [
                'prenom' => ['Prénom', 'text'],
                'name' => ['Nom', 'text'],
                'email' => ['Email', 'email'],
                'phoneNumber' => ['Téléphone', 'text'],
                'address' => ['Adresse', 'text']
              ]; @endphp

              @foreach ($fields as $name => [$label, $type])
                <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
                  <label class="text-right text-gray-700 font-medium">{{ $label }}</label>
                  <input type="{{ $type }}" name="{{ $name }}" class="w-full rounded-lg border-gray-300"
                         value="{{ old($name, $profileData->{$name} ?? '') }}">
                </div>
              @endforeach
            </div>
          </div>

          <div class="mt-6 text-center">
            <button type="submit" class="btn-oneduc">Enregistrer les modifications</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Sidebar -->
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


@endsection
