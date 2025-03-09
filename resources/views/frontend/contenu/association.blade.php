
@extends('frontend.master')
@section('home')

<div class="container">
    <div class="row entete mt-7">
        <div class="col-md-6 col-xxl-9">
            <h1>L'association</h1>
            <h2>…en faveur de l’inclusion numérique.</h2>
            <p class="fs-5">Onéduc est une association loi 1901, fondée par Laurent Staelens, propriétaire et créateur de la plateforme onéduc.fr. Laurent Staelens confère à l’association Onéduc les droits d’utiliser, d’adapter, de développer, d’exploiter la plateforme, y compris à titre commercial.</p>
        </div>
        <div class="col align-self-center" style="text-align: center;"><a class="btn btn-primary btn-lg rounded-pill me-2 mb-0" role="button" style="margin-right: 20px;" href="#">Contactez l'association</a></div>
    </div>
</div>
<div data-bs-spy="scroll" class="scrollspy-example">
    <section class="bg-body section-py landing-reviews pb-0 mt-0 pt-7" id="landingReviews">
        <div class="container">
            <h3><img class="pe-0 me-4" width="40" height="40" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Article 1- NOM</h3>
            <p class="fs-5">Il est fondé entre les adhérents aux présents statuts une association régie par la loi du 1er juillet 1901 et le décret du 16 août 1901, ayant pour titre : ONEDUC.</p>
        </div>
    </section>
    <section class="bg-body section-py landing-reviews pb-0 pt-7" id="landingReviews-1">
        <div class="container">
            <h3><img class="pe-0 me-4" width="40" height="40" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Article 2 – BUT – OBJET</h3>
            <p class="fs-5">De créer des outils numériques et éducatifs, et promouvoir leurs usages dans le monde de la formation professionnelle, de l’éducation et de l’enseignement, y compris pour le secteur adapté.</p>
            <p class="fs-5">D’accompagner les formateurs, enseignants et établissements dans la digitalisation des formations et des parcours, à l’aide notamment de la plateforme Onéduc.fr;<br><br>De valider les prestataires autorisés à fournir un accompagnement à la prise en main de la plateforme Onéduc.fr auprès des utilisateurs (formateurs, enseignants, établissements…) et de déterminer les conditions de cette autorisation ;<br><br>De développer toute activité en rapport avec ces objets,</p>
        </div>
    </section>
    <section class="bg-body section-py landing-reviews pb-0 pt-7" id="landingReviews-2">
        <div class="container">
            <h3><img class="pe-0 me-4" width="40" height="40" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Article 3 – SIÈGE SOCIAL – DURÉE</h3>
            <p class="fs-5">Le siège social est fixé au 78 rue danton, boîte n° 10, 93310 Le Pré Saint Gervais.<br><br>Il pourra être transféré par simple décision du conseil d’administration ;<br><br>La durée de l’association est illimitée.<br><br>L’exercice comptable court du 01 janvier au 31 décembre de chaque année.</p>
        </div>
    </section>
    <section class="bg-body section-py landing-reviews pb-0 pt-7" id="landingReviews-3">
        <div class="container">
            <h3><img class="pe-0 me-4" width="40" height="40" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Article 4 – COMPOSITION</h3>
            <p class="fs-5">L’association se compose de : </p>
            <ul>
                <li>Membres fondateurs</li>
                <li>Membres d’honneur</li>
                <li>Membres actifs ou adhérents</li>
            </ul>
        </div>
    </section>
    <section class="bg-body section-py landing-reviews pb-0 pt-7" id="landingReviews-4">
        <div class="container">
            <h3><img class="pe-0 me-4" width="40" height="40" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Article 5 – ADMISSION</h3>
            <p class="fs-5">L’association est ouverte aux personnes physiques et personnes morales. </p>
            <ul></ul>
        </div>
    </section>
    <section class="bg-body section-py landing-reviews pb-0 pt-7" id="landingReviews-5">
        <div class="container">
            <h3><img class="pe-0 me-4" width="40" height="40" src="{{ asset('frontend/assets/img/front-pages/icons/etoile8.gif')}}">Article 6 – MEMBRES – COTISATIONS</h3>
            <p class="fs-5">Sont membres fondateurs ceux qui sont à l’origine de la création du projet de l’association, qui ont apporté à l’association un élément indispensable à son fonctionnement.<br><br>Les membres fondateurs sont membres de droit de l’association. Ce titre confère aux personnes qui l’ont obtenu le droit de participer à l’Assemblée Générale avec droit de vote sans être tenues de payer une cotisation annuelle.</p>
            <p class="fs-5">Sont membres d’honneur ceux qui ont rendu des services signalés à l’association.<br><br>Les membres d’honneur sont désignés par le Conseil d’administration. Ce titre leur confère le droit de participer à l’Assemblée Générale avec droit de vote sans être tenues de payer une cotisation annuelle.<br><br>Sont membres actifs toutes les personnes, physiques ou morales, qui adhèrent aux présents statuts et sont agréées par le bureau, après en avoir informé et débattu avec le conseil d’administration. Celui-ci peut refuser des adhésions et n’est pas tenu dans ce cas de justifier sa décision. Les membres actifs s’engagent à verser une cotisation annuelle. Ils ont le pouvoir de vote lors des assemblées générales.<br><br>Les personnes morales sont représentées par leur représentant légal en exercice ou par toute autre personne dont l’habilitation à cet effet aura été notifiée à l’association.<br><br>Le montant des cotisations est fixé chaque année par le bureau.</p>
            <ul></ul>
        </div>
    </section>
    <section class="bg-body section-py landing-reviews pb-0 pt-7" id="landingReviews-6" style="text-align: center;"><a class="btn btn-primary btn-lg rounded-pill me-2 mb-0" role="button" style="margin-right: 20px;" href="#">Télécharger les status</a></section>
</div>
<p> </p>

@endsection
