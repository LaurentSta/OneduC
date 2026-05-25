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
        <h2 class="text-lg font-semibold mb-2">Responsable de traitement</h2>
        <p>
          L’Association Onéduc (SIREN 904 800 661), dont le siège est situé au 78 rue Danton, boîte n°10, 93310 Le Pré-Saint-Gervais,
          est responsable du traitement des données personnelles collectées via la plateforme Oneduc.fr.<br>
          Contact : <a href="mailto:contact@oneduc.fr" class="underline text-blue-600">contact@oneduc.fr</a>
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Quelles données sont collectées ?</h2>
        <p>Nous collectons uniquement les données nécessaires au fonctionnement de la plateforme :</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
          <li><strong>Données de compte :</strong> nom, prénom, adresse e-mail, mot de passe (chiffré)</li>
          <li><strong>Données pédagogiques :</strong> scores aux modules SCORM, résultats de quiz, temps de connexion, progression dans les parcours</li>
          <li><strong>Données techniques :</strong> adresse IP (journaux serveur), cookies de session</li>
        </ul>

        <h2 class="text-lg font-semibold mt-6 mb-2">Bases légales des traitements</h2>
        <ul class="list-disc list-inside mt-2 space-y-1">
          <li><strong>Gestion du compte et accès à la plateforme :</strong> exécution du contrat (art. 6.1.b RGPD)</li>
          <li><strong>Suivi de la progression pédagogique :</strong> exécution du contrat (art. 6.1.b RGPD)</li>
          <li><strong>Sécurité et journaux techniques :</strong> intérêt légitime (art. 6.1.f RGPD)</li>
        </ul>

        <h2 class="text-lg font-semibold mt-6 mb-2">Durées de conservation</h2>
        <ul class="list-disc list-inside mt-2 space-y-1">
          <li><strong>Données de compte :</strong> durée de l’inscription, puis 3 ans après clôture du compte</li>
          <li><strong>Données pédagogiques :</strong> durée de la formation, puis 5 ans (justificatif de suivi)</li>
          <li><strong>Journaux techniques :</strong> 12 mois</li>
        </ul>

        <h2 class="text-lg font-semibold mt-6 mb-2">Destinataires des données</h2>
        <p>
          Vos données sont hébergées par <strong>IONOS SARL</strong> (sous-traitant), qui agit exclusivement selon nos instructions.
          Aucun transfert de données n’est effectué en dehors de l’Union européenne.
          Vos données ne sont jamais vendues ni cédées à des tiers à des fins commerciales.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Comment vos données sont-elles protégées ?</h2>
        <p>
          Nous mettons en œuvre des mesures techniques et organisationnelles adaptées : chiffrement des mots de passe,
          connexions sécurisées (HTTPS), accès restreint aux données selon les rôles, sauvegardes régulières.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Vos droits</h2>
        <p>Conformément au RGPD, vous disposez des droits suivants sur vos données personnelles :</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
          <li><strong>Droit d’accès :</strong> obtenir une copie de vos données</li>
          <li><strong>Droit de rectification :</strong> corriger des données inexactes</li>
          <li><strong>Droit à l’effacement :</strong> demander la suppression de vos données</li>
          <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré</li>
          <li><strong>Droit d’opposition et de limitation :</strong> limiter ou vous opposer à certains traitements</li>
        </ul>
        <p class="mt-2">
          Pour exercer ces droits, écrivez à <a href="mailto:contact@oneduc.fr" class="underline text-blue-600">contact@oneduc.fr</a>.
          Nous nous engageons à répondre dans un délai d’un mois.
        </p>
        <p class="mt-2">
          Si vous estimez que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la
          <strong>CNIL</strong> : <a href="https://www.cnil.fr" class="underline text-blue-600" target="_blank">www.cnil.fr</a>.
        </p>

        <p class="mt-6 text-sm text-gray-500">Dernière mise à jour : 16 mai 2026</p>
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
