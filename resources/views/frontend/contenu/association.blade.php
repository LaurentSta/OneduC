@extends('frontend.master')
@section('title', "L'association Onéduc")
@section('description', "Onéduc est portée par une association loi 1901 dédiée à l'inclusion numérique et à la formation accompagnée.")
@section('home')
<div class="max-w-[1248px] mx-auto px-4">
  <div class="bg-white rounded-[24px] shadow-md p-8 my-10 w-full">
    <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] items-center">
      <div>
        <x-typography variant="titre">L'association Oneduc</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Une association loi 1901 pour porter le projet dans la duree.
        </x-typography>
        <div class="prose-oneduc font-lisible">
          <p>Oneduc est une association loi 1901 fondee pour donner un cadre collectif, durable et lisible au developpement de la plateforme.</p>
          <p>Ce choix associatif permet de soutenir un outil utile aux formateurs, d'organiser sa gouvernance et de faire vivre une vision pedagogique plus inclusive que purement marchande.</p>
          <p>Laurent Staelens, createur de la plateforme, en confie l'usage, l'adaptation, le developpement et l'exploitation a l'association afin que le projet puisse grandir dans un cadre partage.</p>
        </div>
      </div>

      <div class="grid gap-4">
        <div class="overflow-hidden rounded-[28px] border border-slate-200 shadow-lg">
          <img
            src="{{ asset('upload/formateur_images/202505311622_laurent_staelens.jpg') }}"
            alt="Portrait de Laurent Staelens"
            class="h-full w-full object-cover">
        </div>
        <div class="rounded-[28px] bg-slate-50 p-5 border border-slate-200">
          <p class="text-xs font-varela uppercase tracking-[0.2em] text-orangeone">Pourquoi ce modele ?</p>
          <p class="mt-2 text-sm leading-relaxed text-slate-600 font-lisible">
            Pour garder la priorite sur l'utilite sociale, l'accompagnement des usages et la qualite pedagogique.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="bg-[#f8f7fa] py-16">
  <div class="max-w-[1248px] mx-auto px-4">
    <div class="grid gap-5 md:grid-cols-3">
      <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm">
        <h3 class="text-xl font-semibold text-bleuone">Pourquoi une association ?</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Le cadre loi 1901 donne une legitimite claire au projet, structure ses decisions et facilite les partenariats avec les acteurs de la formation.
        </p>
      </div>
      <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm">
        <h3 class="text-xl font-semibold text-bleuone">A quoi sert l'adhesion ?</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          L'adhesion soutient l'hebergement, les evolutions fonctionnelles, l'animation du projet et la vie associative autour d'Oneduc.
        </p>
      </div>
      <div class="rounded-3xl bg-white p-6 border border-slate-200 shadow-sm">
        <h3 class="text-xl font-semibold text-bleuone">Ce que vous rejoignez</h3>
        <p class="mt-3 text-slate-600 leading-relaxed font-lisible">
          Une dynamique collective qui rassemble des personnes de terrain autour d'un outil pedagogique plus lisible, plus accessible et plus humain.
        </p>
      </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <div class="rounded-[28px] bg-bleuone p-8 text-white">
        <p class="text-sm font-varela uppercase tracking-[0.25em] text-orange-200">Association loi 1901</p>
        <h2 class="mt-4 text-3xl font-raleway font-bold">
          Un cadre ethique pour faire grandir la plateforme.
        </h2>
        <p class="mt-4 text-white/85 leading-relaxed font-lisible">
          L'association n'est pas un simple habillage juridique. Elle permet d'expliquer pourquoi Oneduc existe, ce qu'il cherche a rendre possible et comment ses ressources sont reinvesties dans le projet.
        </p>
      </div>

      <div class="rounded-[28px] border border-slate-200 bg-white p-8">
        <h2 class="text-2xl font-raleway font-bold text-bleuone">Ce que permet l'adhesion</h2>
        <div class="mt-5 space-y-4 text-slate-600 leading-relaxed font-lisible">
          <p>Elle marque un soutien concret au projet associatif et a sa continuite technique.</p>
          <p>Elle permet de participer a la vie de l'association, notamment aux assemblees generales avec droit de vote pour les membres actifs.</p>
          <p>Elle aide a faire vivre un outil pense pour le terrain plutot qu'un simple produit logiciel.</p>
        </div>

        <div class="mt-6 flex flex-wrap gap-4">
          <a href="{{ route('adhesion') }}" class="btn-oneduc">Adherer a l'association</a>
          <a href="{{ asset('docs/statutsONEDUCsignes.pdf') }}" target="_blank" class="btn-oneduc-outline">Telecharger les statuts</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white">
  <div class="max-w-[1248px] mx-auto px-4 space-y-16 py-16 font-lisible">
    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Etoile">
        Article 1 - Nom
      </h3>
      <p class="text-lg mt-2">Il est fonde entre les adherents aux presents statuts une association regie par la loi du 1er juillet 1901 et le decret du 16 aout 1901, ayant pour titre : ONEDUC.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Etoile">
        Article 2 - But - Objet
      </h3>
      <p class="text-lg mt-2">De creer des outils numeriques et educatifs, et promouvoir leurs usages dans le monde de la formation professionnelle, de l'education et de l'enseignement, y compris pour le secteur adapte.</p>
      <p class="text-lg mt-4">D'accompagner les formateurs, enseignants et etablissements dans la digitalisation des formations et des parcours, a l'aide notamment de la plateforme Oneduc.fr.</p>
      <p class="text-lg mt-4">De valider les prestataires autorises a fournir un accompagnement a la prise en main de la plateforme Oneduc.fr aupres des utilisateurs et de determiner les conditions de cette autorisation.</p>
      <p class="text-lg mt-4">De developper toute activite en rapport avec ces objets.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Etoile">
        Article 3 - Siege social - Duree
      </h3>
      <p class="text-lg mt-2">Le siege social est fixe au 78 rue Danton, boite n°10, 93310 Le Pre Saint Gervais.</p>
      <p class="text-lg mt-4">Il pourra etre transfere par simple decision du conseil d'administration.</p>
      <p class="text-lg mt-4">La duree de l'association est illimitee.</p>
      <p class="text-lg mt-4">L'exercice comptable court du 01 janvier au 31 decembre de chaque annee.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Etoile">
        Article 4 - Composition
      </h3>
      <p class="text-lg mt-2">L'association se compose de :</p>
      <ul class="list-disc list-inside text-lg mt-2 space-y-1">
        <li>Membres fondateurs</li>
        <li>Membres d'honneur</li>
        <li>Membres actifs ou adherents</li>
      </ul>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Etoile">
        Article 5 - Admission
      </h3>
      <p class="text-lg mt-2">L'association est ouverte aux personnes physiques et personnes morales.</p>
    </div>

    <div>
      <h3 class="text-2xl font-semibold flex items-center gap-3 font-raleway text-bleuone">
        <img src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif') }}" width="40" height="40" alt="Etoile">
        Article 6 - Membres - Cotisations
      </h3>
      <div class="space-y-4 text-lg">
        <p>Sont membres fondateurs ceux qui sont a l'origine de la creation du projet de l'association, qui ont apporte a l'association un element indispensable a son fonctionnement.</p>
        <p>Les membres fondateurs sont membres de droit de l'association. Ce titre confere aux personnes qui l'ont obtenu le droit de participer a l'Assemblee Generale avec droit de vote sans etre tenues de payer une cotisation annuelle.</p>
        <p>Sont membres d'honneur ceux qui ont rendu des services signales a l'association.</p>
        <p>Les membres d'honneur sont designes par le Conseil d'administration. Ce titre leur confere le droit de participer a l'Assemblee Generale avec droit de vote sans etre tenues de payer une cotisation annuelle.</p>
        <p>Sont membres actifs toutes les personnes, physiques ou morales, qui adherent aux presents statuts et sont agreees par le bureau, apres en avoir informe et debattu avec le conseil d'administration.</p>
        <p>Celui-ci peut refuser des adhesions et n'est pas tenu dans ce cas de justifier sa decision.</p>
        <p>Les membres actifs s'engagent a verser une cotisation annuelle. Ils ont le pouvoir de vote lors des assemblees generales.</p>
        <p>Les personnes morales sont representees par leur representant legal en exercice ou par toute autre personne dont l'habilitation a cet effet aura ete notifiee a l'association.</p>
        <p>Le montant des cotisations est fixe chaque annee par le bureau.</p>
      </div>
    </div>
  </div>
</section>
@endsection
