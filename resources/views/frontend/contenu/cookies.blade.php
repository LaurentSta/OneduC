@extends('frontend.master')
@section('title', 'Cookies - Oneduc.fr')
@section('description', 'Politique d’utilisation des cookies sur Oneduc.fr.')
@section('home')
<div class="container mx-auto px-4 pt-8 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Cookies</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Comprendre l'utilisation des cookies
        </x-typography>
        <div class="prose-oneduc font-lisible">
          <p>Cette page détaille les types de cookies utilisés sur Oneduc.fr et leur finalité.</p>
          <p>Vous y trouverez également les moyens de les refuser, les désactiver ou les configurer.</p>
        </div>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          <img src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg') }}" alt="Cookies" class="w-full h-auto">
        </div>
      </div>
    </div>
  </div>

  <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Cookies']]" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <h2 class="text-lg font-semibold mb-2">Qu'est-ce qu'un cookie ?</h2>
        <p>Un cookie est un petit fichier texte déposé sur votre appareil lorsque vous visitez un site web.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Pourquoi utilisons-nous des cookies ?</h2>
        <p>Les cookies nous permettent d’améliorer votre expérience utilisateur, de mesurer l’audience du site et de sécuriser votre navigation.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Comment gérer vos cookies ?</h2>
        <p>Vous pouvez paramétrer votre navigateur pour accepter ou refuser tout ou partie des cookies.</p>

        <p class="mt-6 text-sm text-gray-500">Dernière mise à jour : 28 mai 2025</p>
      </div>
    </div>

    <!-- Menu latéral -->
    <aside class="bg-white rounded-[20px] shadow-md p-6 h-fit">
      <h3 class="text-lg font-semibold text-[#004461] mb-4">Informations légales</h3>
      <ul class="space-y-3 text-sm">
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
          <a href="{{ route('mentions-legales') }}" class="text-gray-700 hover:text-[#004461] font-medium">Mentions légales</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
          <a href="{{ route('cookies') }}" class="text-[#E94D2A] font-semibold">Cookies</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
          <a href="{{ route('confidentialite') }}" class="text-gray-700 hover:text-[#004461] font-medium">Confidentialité</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
          <a href="{{ route('conditions-utilisation') }}" class="text-gray-700 hover:text-[#004461] font-medium">Conditions d’utilisation</a>
        </li>
      </ul>
    </aside>
  </div>
</div>
@endsection
