@extends('frontend.master')
@section('title', 'Conditions d’utilisation - Oneduc.fr')
@section('description', 'Conditions générales d’utilisation de la plateforme Oneduc.fr.')
@section('home')
<div class="container mx-auto px-4 pt-8 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Conditions d’utilisation</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Utiliser la plateforme en toute confiance
        </x-typography>
        <div class="prose-oneduc font-lisible">
          <p>Ce document définit les règles et les modalités d’accès, d’inscription et d’utilisation des services proposés par Oneduc.fr.</p>
          <p>En accédant au site, vous acceptez sans réserve les présentes conditions générales.</p>
        </div>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          <img src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg') }}" alt="Conditions d’utilisation" class="w-full h-auto">
        </div>
      </div>
    </div>
  </div>

  <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Conditions d’utilisation']]" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <h2 class="text-lg font-semibold mb-2">Objet des conditions</h2>
        <p>Les présentes conditions encadrent juridiquement l’utilisation de la plateforme Oneduc.fr.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Accès aux services</h2>
        <p>L’accès à la plateforme est réservé aux utilisateurs disposant d’un compte personnel ou invité par un formateur/administrateur.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Comportement des utilisateurs</h2>
        <p>Les utilisateurs s’engagent à utiliser la plateforme de manière respectueuse, sans nuire à son bon fonctionnement ou à autrui.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Responsabilité</h2>
        <p>Oneduc s’efforce d’assurer l’exactitude des informations, sans pouvoir être tenu responsable des erreurs involontaires ou interruptions techniques.</p>

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
          <a href="{{ route('cookies') }}" class="text-gray-700 hover:text-[#004461] font-medium">Cookies</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
          <a href="{{ route('confidentialite') }}" class="text-gray-700 hover:text-[#004461] font-medium">Confidentialité</a>
        </li>
        <li class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
          <a href="{{ route('conditions-utilisation') }}" class="text-[#E94D2A] font-semibold">Conditions d’utilisation</a>
        </li>
      </ul>
    </aside>
  </div>
</div>
@endsection
