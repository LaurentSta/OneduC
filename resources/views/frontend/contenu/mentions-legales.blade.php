@extends('frontend.master')
@section('title', 'Mentions légales - Oneduc.fr')
@section('description', 'Informations légales sur l’éditeur du site Oneduc.fr, hébergement, publication et contact.')
@section('home')
<div class="container mx-auto px-4 pt-8 pb-8">
<div class="bg-white rounded-[20px] shadow-md px-8 py-0 my-10 w-full">
      <div class="grid grid-cols-12 gap-6 items-center">

        {{-- Colonne texte : 8 colonnes sur 12 --}}
        <div class="col-span-12 md:col-span-8">
          <x-typography variant="titre">Mentions légales</x-typography>
          <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
    Transparence et obligations légales
</x-typography>
<div class="prose-oneduc font-lisible">
    <p>Cette page vous informe des obligations légales liées à l’édition et à la gestion du site oneduc.fr.</p>
    <p>Elle précise l’identité de l’éditeur, les conditions d’hébergement, ainsi que les moyens de contact disponibles pour toute question ou réclamation.</p>
</div>

        </div>

        {{-- Colonne image : 4 colonnes sur 12 --}}
        <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
          <div class="w-full max-w-xs">
            <img src="{{ asset('frontend/assets/img/illustrations/AssociationOneduc.svg') }}" alt="Association Oneduc" class="w-full h-auto">
          </div>
        </div>

      </div>
    </div>
    <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('index')], ['label' => 'Mentions légales']]" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
        <!-- Contenu principal -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[20px] shadow-md p-8 w-full">
                <h2 class="text-lg font-semibold mb-2">Éditeur du site</h2>
                <p>
                    <strong>Oneduc.fr</strong> est édité par l'Association Onéduc, déclarée en préfecture conformément à la loi du 1<sup>er</sup> juillet 1901.<br>
                    <strong>SIREN :</strong> 904 800 661<br>
                    <strong>Adresse :</strong> 78 rue Danton, boîte n°10, 93310 Le Pré-Saint-Gervais, France<br>
                    <strong>Email :</strong> contact@oneduc.fr<br>
                    <strong>Présidente :</strong> Anne-Sophie FRANVILLE<br>
                    <strong>Directeur de la publication :</strong> Laurent Staelens
                </p>

                <h2 class="text-lg font-semibold mt-6 mb-2">Hébergement</h2>
                <p>
                    Le site est hébergé par :<br>
                    <strong>IONOS SARL</strong><br>
                    7 place de la Gare, 57200 Sarreguemines<br>
                    Site : <a href="https://www.ionos.fr" class="underline text-blue-600" target="_blank">www.ionos.fr</a>
                </p>

                <h2 class="text-lg font-semibold mt-6 mb-2">Contact</h2>
                <p>Pour toute question ou réclamation, veuillez écrire à <a href="mailto:contact@oneduc.fr" class="underline text-blue-600">contact@oneduc.fr</a>.</p>

                <p class="mt-6 text-sm text-gray-500">Dernière mise à jour : 28 mai 2025</p>
            </div>
        </div>

        <!-- Menu latéral -->
<aside class="bg-white rounded-[20px] shadow-md p-6 h-fit">
    <h3 class="text-lg font-semibold text-[#004461] mb-4">Informations légales</h3>
    <ul class="space-y-3 text-sm">
        <li class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 bg-[#E94D2A] rounded-full"></span>
            <a href="{{ route('mentions-legales') }}" class="text-[#E94D2A] font-semibold">Mentions légales</a>
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
            <a href="{{ route('conditions-utilisation') }}" class="text-gray-700 hover:text-[#004461] font-medium">Conditions d’utilisation</a>
        </li>
    </ul>
</aside>


    </div>
</div>
@endsection
