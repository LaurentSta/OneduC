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
        <h2 class="text-lg font-semibold mb-2">Qu’est-ce qu’un cookie ?</h2>
        <p>Un cookie est un petit fichier texte déposé sur votre appareil lorsque vous visitez un site web. Il permet au site de mémoriser certaines informations entre vos visites.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Cookies utilisés sur Oneduc.fr</h2>
        <p>Oneduc.fr utilise uniquement des cookies strictement nécessaires au fonctionnement de la plateforme. Aucun cookie de suivi publicitaire ni d’outil d’analyse tiers n’est déposé.</p>

        <div class="mt-4 overflow-x-auto">
          <table class="w-full text-sm border-collapse">
            <thead>
              <tr class="bg-gray-50">
                <th class="text-left p-3 border border-gray-200 font-semibold">Nom</th>
                <th class="text-left p-3 border border-gray-200 font-semibold">Finalité</th>
                <th class="text-left p-3 border border-gray-200 font-semibold">Type</th>
                <th class="text-left p-3 border border-gray-200 font-semibold">Durée</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="p-3 border border-gray-200 font-mono text-xs">laravel_session</td>
                <td class="p-3 border border-gray-200">Maintien de votre session de connexion</td>
                <td class="p-3 border border-gray-200">Essentiel</td>
                <td class="p-3 border border-gray-200">Fin de session</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-3 border border-gray-200 font-mono text-xs">XSRF-TOKEN</td>
                <td class="p-3 border border-gray-200">Protection contre les attaques CSRF (sécurité des formulaires)</td>
                <td class="p-3 border border-gray-200">Essentiel</td>
                <td class="p-3 border border-gray-200">Fin de session</td>
              </tr>
              <tr>
                <td class="p-3 border border-gray-200 font-mono text-xs">laravel_cookie_consent</td>
                <td class="p-3 border border-gray-200">Mémorise votre accord sur l’utilisation des cookies</td>
                <td class="p-3 border border-gray-200">Fonctionnel</td>
                <td class="p-3 border border-gray-200">13 mois</td>
              </tr>
            </tbody>
          </table>
        </div>

        <h2 class="text-lg font-semibold mt-6 mb-2">Ces cookies nécessitent-ils mon consentement ?</h2>
        <p>
          Les cookies <strong>essentiels</strong> (session et sécurité) sont indispensables au fonctionnement du site et ne requièrent pas votre consentement préalable.
          Le cookie de mémorisation du consentement est déposé uniquement après votre accord.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Comment gérer ou supprimer vos cookies ?</h2>
        <p>Vous pouvez à tout moment configurer votre navigateur pour refuser ou supprimer les cookies :</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
          <li><strong>Chrome :</strong> Paramètres → Confidentialité et sécurité → Cookies</li>
          <li><strong>Firefox :</strong> Paramètres → Vie privée et sécurité → Cookies</li>
          <li><strong>Safari :</strong> Préférences → Confidentialité → Gérer les données du site</li>
          <li><strong>Edge :</strong> Paramètres → Cookies et autorisations de site</li>
        </ul>
        <p class="mt-2 text-sm text-gray-600">Attention : la suppression des cookies essentiels peut empêcher le bon fonctionnement de la plateforme (déconnexion automatique, etc.).</p>

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
