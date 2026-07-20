@extends('frontend.master')

@section('title', 'Plan d’action accessibilité 2026 - Oneduc')
@section('description', 'Actions prévues par Oneduc en 2026 pour améliorer l’accessibilité numérique et préparer un audit RGAA.')

@section('home')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
  <header class="rounded-[20px] bg-white p-6 sm:p-8">
    <p class="mb-3 font-varela text-sm font-bold uppercase tracking-wider text-bleuone">Accessibilité numérique</p>
    <h1 class="mb-4">Plan d’action 2026</h1>
    <p class="max-w-3xl text-lg leading-relaxed text-gray-700">
      Ce plan transforme l’engagement d’Oneduc en actions vérifiables. Les échéances prévisionnelles seront ajustées selon les ressources disponibles et les résultats de l’audit.
    </p>
    <p class="mt-4 text-sm text-gray-600">Publication initiale et dernière mise à jour : 20 juillet 2026.</p>
  </header>

  <div class="mt-6">
    @include('frontend.contenu.partials.navigation-accessibilite', ['actif' => 'plan'])
  </div>

  <section aria-labelledby="suivi-plan" class="mt-6 rounded-[20px] bg-white p-6 sm:p-8">
    <h2 id="suivi-plan" class="text-2xl font-bold text-bleuone">Suivi des actions</h2>
    <p class="mt-4 text-gray-800">
      Les statuts publiés sont : <strong>mis en œuvre</strong>, <strong>en cours</strong> et <strong>prévu</strong>. Une action n’est marquée comme terminée qu’après vérification de son résultat.
    </p>
  </section>

  <ol class="mt-6 space-y-5">
    <li class="rounded-[20px] border-l-4 border-green-700 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">1. Publier les documents d’accessibilité</h2>
        <span class="w-fit rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-900">Mis en œuvre</span>
      </div>
      <p class="mt-3 text-gray-800">Déclaration, schéma 2026-2028, plan 2026 et mention de statut accessibles en HTML.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Preuve attendue :</strong> trois pages publiques reliées depuis le site.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-blue-700 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">2. Améliorer le socle de navigation publique</h2>
        <span class="w-fit rounded-full bg-blue-100 px-3 py-1 text-sm font-bold text-blue-900">En cours</span>
      </div>
      <p class="mt-3 text-gray-800">Ajouter un accès rapide au contenu, renforcer la visibilité du focus et fiabiliser progressivement les composants dynamiques.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Vérification :</strong> recette clavier sur les pages publiques représentatives.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">3. Désigner le référent accessibilité</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — T3 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Définir son rôle, le circuit des signalements et la coordination avec les responsables fonctionnels et techniques.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> rôle et moyen de contact publiés.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">4. Inventorier le périmètre numérique</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — T3 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Recenser les pages, parcours par rôle, outils collaboratifs, documents, médias, contenus SCORM et composants partagés.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> inventaire validé et échantillon d’audit défini.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">5. Réaliser l’audit RGAA 4.1.2</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — T4 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Évaluer un échantillon représentatif avec vérifications manuelles, clavier, zoom et technologies d’assistance.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> rapport, taux et liste priorisée publiés après validation.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">6. Corriger les obstacles critiques</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — T4 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Prioriser les blocages liés au clavier, au focus, aux contrastes, aux formulaires, aux alternatives des médias et aux parcours d’apprentissage.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> corrections vérifiées sur l’échantillon concerné.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">7. Encadrer la création des contenus</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — T4 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Ajouter des règles pour les alternatives d’images, les transcriptions, les sous-titres, les titres, les liens et les contenus SCORM.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> checklist éditoriale et contrôles intégrés au constructeur de contenus.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">8. Installer une recette continue</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — T4 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Combiner checklist manuelle, tests clavier, lecteurs d’écran, contrôles automatisés et tests utilisateurs lorsque cela est possible.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> procédure documentée et contrôles exécutés sur les parcours critiques.</p>
    </li>

    <li class="rounded-[20px] border-l-4 border-slate-500 bg-white p-6 sm:p-8">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <h2 class="text-xl font-bold text-bleuone">9. Publier le bilan 2026</h2>
        <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-800">Prévu — fin 2026</span>
      </div>
      <p class="mt-3 text-gray-800">Mettre à jour la déclaration après audit et publier les actions réalisées, reportées et programmées pour 2027.</p>
      <p class="mt-2 text-sm text-gray-600"><strong>Indicateur :</strong> déclaration actualisée, bilan 2026 et plan 2027 en ligne.</p>
    </li>
  </ol>
</div>
@endsection
