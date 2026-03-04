@extends('formateur.dashboard')
@section('title', 'Documentation formateur - Oneduc.fr')

@section('formateur')
<div class="max-w-[1285px] mx-auto px-8 py-8 space-y-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 py-6">
    <h1 class="text-titre font-raleway text-bleuone">Documentation formateur</h1>
    <p class="text-sous-titre font-varela text-orangeone mt-2">Guide d'utilisation de votre espace Oneduc</p>
    <p class="text-gray-700 mt-3">
      Cette page regroupe les actions essentielles pour gérer vos groupes, suivre la progression et animer vos formations.
    </p>
  </header>

  <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">1. Démarrage rapide</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Consultez votre tableau de bord pour un aperçu global de votre activité.</li>
        <li>Mettez à jour votre profil et vos préférences dans <strong>Paramètre</strong>.</li>
        <li>Vérifiez votre sécurité de compte (mot de passe, confidentialité).</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">2. Gestion des stagiaires et groupes</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Créez vos groupes puis ajoutez les stagiaires individuellement ou en lot (CSV selon vos écrans).</li>
        <li>Modifiez les informations stagiaires à tout moment depuis la liste.</li>
        <li>Retirez un stagiaire d'un groupe si nécessaire pour garder un suivi propre.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">3. Formations et parcours</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Accédez à <strong>Formations</strong> pour visualiser vos modules publiés.</li>
        <li>Ouvrez le détail d'un module pour prévisualiser le parcours comme un stagiaire.</li>
        <li>Personnalisez l'ordre/activation des leçons par groupe pour adapter la pédagogie.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">4. Quiz et évaluation</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Les quiz se lancent dans les leçons et suivent un cycle question/réponse/résultat.</li>
        <li>Le nombre de questions peut être ajusté selon la configuration du module.</li>
        <li>Utilisez les résultats pour repérer les points de blocage récurrents.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">5. Progression et pilotage</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Analyse par groupes, par stagiaires et par modules depuis l'espace <strong>Progression</strong>.</li>
        <li>Suivi d'achèvement des leçons et indicateurs de complétion.</li>
        <li>En cas d'anomalie, vérifiez l'état d'accès du stagiaire et l'affectation au groupe.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">6. Support et bonnes pratiques</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Support rapide: utilisez le bouton <strong>Support</strong> (Discord + formulaire).</li>
        <li>Préférez des intitulés de modules explicites et des leçons courtes.</li>
        <li>Planifiez une revue hebdomadaire des progressions pour relancer les apprenants.</li>
      </ul>
    </article>
  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6">
    <h2 class="text-xl font-semibold text-bleuone">Liens utiles</h2>
    <div class="mt-4 flex flex-wrap gap-3">
      <a href="{{ route('formateur.dashboard') }}" class="btn-oneduc">Tableau de bord</a>
      <a href="{{ route('formateur.groupes.index') }}" class="btn btn-outline-secondary">Groupes</a>
      <a href="{{ route('formateur.stagiaires.index') }}" class="btn btn-outline-secondary">Stagiaires</a>
      <a href="{{ route('formateur.formations.index') }}" class="btn btn-outline-secondary">Formations</a>
      <a href="{{ route('formateur.progressions.groupes') }}" class="btn btn-outline-secondary">Progression</a>
      <a href="{{ route('contact') }}" class="btn btn-outline-secondary">Support</a>
    </div>
  </section>
</div>
@endsection
