@extends('frontend.master')
@section('title', 'Confidentialité - Oneduc.fr')
@section('description', 'Politique de confidentialité de la plateforme Oneduc.fr en conformité avec le RGPD.')
@section('home')
<div class="container mx-auto px-4 pt-8 pb-8">
  <div class="bg-white rounded-[20px] shadow-md px-8 py-0 my-10 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Politique de confidentialité</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Vos données, notre responsabilité
        </x-typography>
        <div class="prose-oneduc font-lisible">
          <p>Cette page décrit les engagements d’Oneduc en matière de collecte, de traitement et de protection des données personnelles conformément au RGPD.</p>
          <p>Vous y trouverez les droits dont vous disposez, ainsi que les moyens pour les exercer.</p>
        </div>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <div class="w-full max-w-xs">
          <img src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg') }}" alt="Confidentialité" class="w-full h-auto">
        </div>
      </div>
    </div>
  </div>

  <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Confidentialité']]" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <h2 class="text-lg font-semibold mb-2">Quelles données sont collectées ?</h2>
        <p>Nous collectons uniquement les données nécessaires à l’accès et à l’amélioration de nos services de formation.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">À quoi servent vos données ?</h2>
        <p>Les données servent à gérer votre accès à la plateforme, personnaliser les contenus et suivre votre progression pédagogique.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Comment vos données sont-elles protégées ?</h2>
        <p>Nous mettons en œuvre des mesures techniques et organisationnelles pour assurer la sécurité de vos données personnelles.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Quels sont vos droits ?</h2>
        <p>Vous pouvez accéder, rectifier ou supprimer vos données. Pour cela, contactez-nous à l’adresse <a href="mailto:contact@oneduc.fr" class="underline text-blue-600">contact@oneduc.fr</a>.</p>

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
          <a href="{{ route('confidentialite') }}" class="text-[#E94D2A] font-semibold">Confidentialité</a>
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
