@extends('stagiaire.master')
@section('title', 'Mon Profil - Oneduc.fr')

@section('content')

{{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Profil --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">
        <div class="col-span-12">
            <x-typography variant="titre">Profil stagiaire</x-typography>
            <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                Vos informations personnelles
            </x-typography>
            <x-typography>
                Consultez et modifiez vos informations de contact, votre mot de passe et vos préférences.
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
                    <li class="text-gray-400">Mon profil</li>
                </ol>
            </nav>
        </div>
    </div>
</div>


{{-- 📄 CONTENU PRINCIPAL – Aligné comme l’en-tête --}}


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
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
                        @php $infos = [
                            'Email' => $profileData->email,
                            'Adresse' => $profileData->address ?? 'Non renseignée',
                            'Date de début' => 'À renseigner',
                            'Temps sur le site' => gmdate('H\h i\m s\s', $totalSiteTime ?? 0),
                            'Code d’accès' => 'À renseigner'
                        ]; @endphp

                        @foreach ($infos as $label => $value)
                            <div class="grid grid-cols-[140px_auto] gap-x-4">
                                <div class="text-right text-gray-700 font-medium">{{ $label }}</div>
                                <div class="text-left text-gray-500">{{ $value }}</div>
                            </div>
                        @endforeach
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


@endsection
