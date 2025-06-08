@extends('frontend.master')
@section('home')
<!-- EN-TÊTE DE LA PAGE ASSOCIATION -->
<div class="max-w-[1248px] mx-auto px-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 my-10 w-full">
      <div class="grid grid-cols-12 gap-6 items-center">

        {{-- Colonne texte : 8 colonnes sur 12 --}}
        <div class="col-span-12 md:col-span-8">
          <x-typography variant="titre">L'association Onéduc</x-typography>
          <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
            …en faveur de l’inclusion numérique
          </x-typography>
          <div class="prose-oneduc font-lisible">
            <p>Onéduc est une association loi 1901, fondée par Laurent Staelens, propriétaire et créateur de la plateforme onéduc.fr.</p>
            <p>Laurent Staelens confère à l’association Onéduc les droits d’utiliser, d’adapter, de développer, d’exploiter la plateforme, y compris à titre commercial.</p>
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
  </div>


<!-- ARTICLES DE L'ASSOCIATION -->
<section class="bg-white">
  <div class="max-w-[1248px] mx-auto px-4 space-y-16 py-16 font-lisible">

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Étoile">
        Article 1 - NOM
      </h3>
      <p class="text-lg mt-2">Il est fondé entre les adhérents aux présents statuts une association régie par la loi du 1er juillet 1901 et le décret du 16 août 1901, ayant pour titre : ONEDUC.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Étoile">
        Article 2 – BUT – OBJET
      </h3>
      <p class="text-lg mt-2">De créer des outils numériques et éducatifs, et promouvoir leurs usages dans le monde de la formation professionnelle, de l’éducation et de l’enseignement, y compris pour le secteur adapté.</p>
      <p class="text-lg mt-4">D’accompagner les formateurs, enseignants et établissements dans la digitalisation des formations et des parcours, à l’aide notamment de la plateforme Onéduc.fr ;</p>
      <p class="text-lg mt-4">De valider les prestataires autorisés à fournir un accompagnement à la prise en main de la plateforme Onéduc.fr auprès des utilisateurs (formateurs, enseignants, établissements…) et de déterminer les conditions de cette autorisation ;</p>
      <p class="text-lg mt-4">De développer toute activité en rapport avec ces objets.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Étoile">
        Article 3 – SIÈGE SOCIAL – DURÉE
      </h3>
      <p class="text-lg mt-2">Le siège social est fixé au 78 rue Danton, boîte n°10, 93310 Le Pré Saint Gervais.</p>
      <p class="text-lg mt-4">Il pourra être transféré par simple décision du conseil d’administration.</p>
      <p class="text-lg mt-4">La durée de l’association est illimitée.</p>
      <p class="text-lg mt-4">L’exercice comptable court du 01 janvier au 31 décembre de chaque année.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Étoile">
        Article 4 – COMPOSITION
      </h3>
      <p class="text-lg mt-2">L’association se compose de :</p>
      <ul class="list-disc list-inside text-lg mt-2 space-y-1">
        <li>Membres fondateurs</li>
        <li>Membres d’honneur</li>
        <li>Membres actifs ou adhérents</li>
      </ul>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Étoile">
        Article 5 – ADMISSION
      </h3>
      <p class="text-lg mt-2">L’association est ouverte aux personnes physiques et personnes morales.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Étoile">
        Article 6 – MEMBRES – COTISATIONS
      </h3>
      <div class="space-y-4 text-lg">
        <p>Sont membres fondateurs ceux qui sont à l’origine de la création du projet de l’association, qui ont apporté à l’association un élément indispensable à son fonctionnement.</p>
        <p>Les membres fondateurs sont membres de droit de l’association. Ce titre confère aux personnes qui l’ont obtenu le droit de participer à l’Assemblée Générale avec droit de vote sans être tenues de payer une cotisation annuelle.</p>
        <p>Sont membres d’honneur ceux qui ont rendu des services signalés à l’association.</p>
        <p>Les membres d’honneur sont désignés par le Conseil d’administration. Ce titre leur confère le droit de participer à l’Assemblée Générale avec droit de vote sans être tenues de payer une cotisation annuelle.</p>
        <p>Sont membres actifs toutes les personnes, physiques ou morales, qui adhèrent aux présents statuts et sont agréées par le bureau, après en avoir informé et débattu avec le conseil d’administration.</p>
        <p>Celui-ci peut refuser des adhésions et n’est pas tenu dans ce cas de justifier sa décision.</p>
        <p>Les membres actifs s’engagent à verser une cotisation annuelle. Ils ont le pouvoir de vote lors des assemblées générales.</p>
        <p>Les personnes morales sont représentées par leur représentant légal en exercice ou par toute autre personne dont l’habilitation à cet effet aura été notifiée à l’association.</p>
        <p>Le montant des cotisations est fixé chaque année par le bureau.</p>
      </div>
    </div>

    <div class="text-center pt-10">
      <a href="{{ asset('docs/statutsONEDUCsignes.pdf') }}" target="_blank" class="btn-oneduc">Télécharger les statuts</a>
    </div>
  </div>
</section>

@endsection
