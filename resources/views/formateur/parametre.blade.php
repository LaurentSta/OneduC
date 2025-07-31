@extends('formateur.dashboard')
@section('title', 'Préférences - Oneduc.fr')

@section('formateur')

{{-- 🧩 En-tête harmonisée --}}
<div class="max-w-[1285px] mx-auto px-8 pt-4">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-4 mb-6">
    <h1 class="text-titre font-raleway text-bleuone mb-2">Préférences</h1>
    <p class="text-sous-titre font-varela text-orangeone">Modifier vos informations professionnelles</p>
    <div class="text-sm text-gray-600 font-lisible mt-2">Vous pouvez ici mettre à jour vos coordonnées et votre photo de profil.</div>
  </div>
</div>

{{-- 🧩 Contenu principal : deux colonnes --}}
<div class="max-w-[1285px] mx-auto px-8 pb-12 grid grid-cols-1 lg:grid-cols-3 gap-8">

  {{-- Formulaire de modification --}}
  <div class="lg:col-span-2 bg-white rounded-[20px] shadow-md p-8">
    <form method="POST" action="{{ route('formateur.profil.store') }}" enctype="multipart/form-data">
      @csrf

      {{-- Avatar --}}
      <div class="flex justify-center mb-6">
        <div class="relative group w-28 h-28">
          <img id="avatar"
               src="{{ !empty($profileData->photo) ? asset('upload/formateur_images/'.$profileData->photo) : asset('upload/NoPhoto.png') }}"
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

      {{-- Champs du formulaire --}}
      <div class="grid gap-y-4 text-sm w-full max-w-md mx-auto">
        @foreach ([
          'prenom' => 'Prénom',
          'name' => 'Nom',
          'email' => 'Email',
          'phoneNumber' => 'Téléphone',
          'address' => 'Adresse'
        ] as $field => $label)
          <div class="grid grid-cols-[140px_auto] gap-x-4 items-center">
            <label class="text-right text-gray-700 font-medium">{{ $label }}</label>
            <input type="{{ $field === 'email' ? 'email' : 'text' }}"
                   name="{{ $field }}"
                   class="w-full rounded-lg border-gray-300"
                   value="{{ old($field, $field === 'phoneNumber' ? $profileData->phone : $profileData->$field) }}">
          </div>
        @endforeach
      </div>

      {{-- Bouton --}}
      <div class="mt-6 text-center">
        <button type="submit" class="btn-oneduc">
          Enregistrer les modifications
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
        <a href="{{ route('formateur.parametre') }}" class="text-[#E94D2A] font-semibold">Préférences</a>
      </li>
      <li class="flex items-center space-x-2">
        <span class="w-2.5 h-2.5 bg-orangeone rounded-full"></span>
        <a href="{{ route('formateur.securite') }}" class="text-gray-700 hover:text-[#004461] font-medium">Sécurité</a>
      </li>
    </ul>
  </aside>
</div>

{{-- Rafraîchissement de l’avatar après soumission --}}
@if (session('message'))
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const img = document.querySelector('img#avatar');
    if (img) {
      const currentSrc = img.src.split('?')[0];
      img.src = `${currentSrc}?v=${Date.now()}`;
    }
  });
</script>
@endif

@endsection
