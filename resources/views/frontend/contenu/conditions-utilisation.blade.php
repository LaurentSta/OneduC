@extends('frontend.master')
@section('title', "Conditions d’utilisation - Oneduc.fr")
@section('description', "Conditions générales d’utilisation de la plateforme Oneduc.fr.")
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

  @php $breadcrumbCGU = [['label' => 'Accueil', 'url' => route('index')], ['label' => "Conditions d’utilisation"]]; @endphp
  <x-oneduc.breadcrumb :items="$breadcrumbCGU" />

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
    <div class="lg:col-span-2">
      <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
        <h2 class="text-lg font-semibold mb-2">Objet des conditions</h2>
        <p>Les présentes conditions générales d’utilisation (CGU) encadrent juridiquement l’accès et l’utilisation de la plateforme Oneduc.fr, éditée par l’Association Onéduc (SIREN 904 800 661). En accédant au site, vous acceptez sans réserve les présentes conditions.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Accès aux services</h2>
        <p>L’accès à la plateforme est réservé aux utilisateurs titulaires d’un compte personnel, créé à leur initiative ou à l’invitation d’un formateur ou d’un administrateur. L’utilisateur est responsable de la confidentialité de ses identifiants de connexion et s’engage à signaler toute utilisation non autorisée à <a href="mailto:contact@oneduc.fr" class="underline text-blue-600">contact@oneduc.fr</a>.</p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Comportement des utilisateurs</h2>
        <p>Les utilisateurs s’engagent à :</p>
        <ul class="list-disc list-inside mt-2 space-y-1">
          <li>Utiliser la plateforme de manière respectueuse et conforme à sa finalité pédagogique</li>
          <li>Ne pas tenter de perturber, pirater ou altérer le fonctionnement du site</li>
          <li>Ne pas diffuser de contenus illicites, offensants ou portant atteinte aux droits d’autrui</li>
          <li>Ne pas partager leurs identifiants avec des tiers</li>
        </ul>

        <h2 class="text-lg font-semibold mt-6 mb-2">Propriété intellectuelle</h2>
        <p>
          L’ensemble des contenus disponibles sur Oneduc.fr — modules SCORM, activités pédagogiques, ressources, textes, visuels, code source — sont la propriété de l’Association Onéduc ou de leurs auteurs respectifs, et sont protégés par le droit de la propriété intellectuelle.
          Toute reproduction, représentation, modification ou exploitation non autorisée est interdite.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Suspension et résiliation</h2>
        <p>
          L’Association Onéduc se réserve le droit de suspendre ou supprimer un compte en cas de non-respect des présentes conditions, sans préavis ni indemnité.
          L’utilisateur peut demander la suppression de son compte à tout moment en écrivant à <a href="mailto:contact@oneduc.fr" class="underline text-blue-600">contact@oneduc.fr</a>.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Responsabilité</h2>
        <p>
          L’Association Onéduc s’efforce d’assurer la disponibilité et l’exactitude des informations proposées, sans pouvoir garantir l’absence d’interruption technique ou d’erreur involontaire.
          Sa responsabilité ne saurait être engagée en cas d’utilisation non conforme de la plateforme par un utilisateur.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Données personnelles</h2>
        <p>
          Les données personnelles sont traitées selon les conditions précisées dans la
          <a href="{{ route('confidentialite') }}" class="underline text-blue-600">politique de confidentialité</a>,
          conformément au RGPD.
        </p>

        <h2 class="text-lg font-semibold mt-6 mb-2">Loi applicable et juridiction</h2>
        <p>
          Les présentes CGU sont soumises au droit français. En cas de litige, et à défaut de résolution amiable,
          les tribunaux compétents du ressort du siège de l’Association Onéduc seront saisis.
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
