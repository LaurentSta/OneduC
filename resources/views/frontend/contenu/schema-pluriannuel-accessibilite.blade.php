@extends('frontend.master')

@section('title', 'Schéma pluriannuel d’accessibilité 2026-2028 - Oneduc')
@section('description', 'Stratégie d’amélioration de l’accessibilité numérique d’Oneduc pour la période 2026-2028.')

@section('home')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
  <header class="rounded-[20px] bg-white p-6 sm:p-8">
    <p class="mb-3 font-varela text-sm font-bold uppercase tracking-wider text-bleuone">Accessibilité numérique</p>
    <h1 class="mb-4">Schéma pluriannuel 2026-2028</h1>
    <p class="max-w-3xl text-lg leading-relaxed text-gray-700">
      Ce schéma organise la démarche d’amélioration continue de l’accessibilité des services numériques proposés par Oneduc.
    </p>
    <p class="mt-4 text-sm text-gray-600">Publication initiale et dernière mise à jour : 20 juillet 2026.</p>
  </header>

  <div class="mt-6">
    @include('frontend.contenu.partials.navigation-accessibilite', ['actif' => 'schema'])
  </div>

  <div class="mt-6 space-y-6">
    <section aria-labelledby="strategie" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="strategie" class="text-2xl font-bold text-bleuone">Stratégie</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Oneduc intègre progressivement l’accessibilité dans la conception, le développement, la production des contenus pédagogiques, la recette et la maintenance. Les obstacles qui bloquent l’accès à une fonctionnalité essentielle seront traités en priorité.
      </p>
      <p class="mt-3 leading-relaxed text-gray-800">
        La démarche tient compte des moyens d’une association portée en partie par des bénévoles. Les échéances annuelles seront confirmées et ajustées dans chaque plan d’action, sans masquer les difficultés ni revendiquer une conformité non vérifiée.
      </p>
    </section>

    <section aria-labelledby="perimetre-schema" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="perimetre-schema" class="text-2xl font-bold text-bleuone">Périmètre</h2>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li>site public et parcours de contact ou d’inscription ;</li>
        <li>espaces administrateur, formateur, stagiaire et observateur ;</li>
        <li>outils collaboratifs et activités en direct ;</li>
        <li>constructeurs de modules, quiz et parcours ;</li>
        <li>lecteurs de contenus, vidéos, documents et contenus SCORM ;</li>
        <li>courriels, exports et documents téléchargeables produits par la plateforme.</li>
      </ul>
    </section>

    <section aria-labelledby="gouvernance" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="gouvernance" class="text-2xl font-bold text-bleuone">Gouvernance et suivi</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Le référent accessibilité reste à désigner. Dans l’intervalle, le suivi est assuré au niveau de l’association et les signalements sont reçus via le formulaire de contact. Le futur référent sera rattaché à la gouvernance de l’association. Il aura pour missions de coordonner l’inventaire et les audits, de suivre les corrections, d’accompagner les contributeurs et de tenir à jour les publications.
      </p>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li>désigner un référent accessibilité et publier son rôle ;</li>
        <li>centraliser les signalements via le formulaire de contact et assurer leur suivi ;</li>
        <li>tenir un inventaire des services, contenus, documents et composants à auditer ;</li>
        <li>documenter les décisions, dérogations éventuelles et alternatives proposées ;</li>
        <li>publier chaque année le bilan des actions et le plan de l’année suivante.</li>
      </ul>
    </section>

    <section aria-labelledby="moyens" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="moyens" class="text-2xl font-bold text-bleuone">Moyens mobilisés</h2>
      <div class="mt-4 space-y-5 text-gray-800">
        <div>
          <h3 class="text-xl font-bold text-bleuone">Ressources humaines et financières</h3>
          <p class="mt-2 leading-relaxed">
            La démarche s’appuie actuellement sur l’équipe associative et les contributeurs du projet. Le temps humain dédié et le budget spécifique restent à chiffrer puis à faire valider par l’association. Chaque plan annuel précisera les ressources effectivement affectées et les arbitrages nécessaires.
          </p>
        </div>
        <div>
          <h3 class="text-xl font-bold text-bleuone">Compétences et sensibilisation</h3>
          <p class="mt-2 leading-relaxed">
            Des règles de production accessible et une checklist de recette seront intégrées à la documentation. Les compétences en accessibilité seront prises en compte dans les futurs appels à contribution, recrutements éventuels et missions confiées aux personnes qui conçoivent, développent ou publient des contenus.
          </p>
        </div>
        <div>
          <h3 class="text-xl font-bold text-bleuone">Expertise, outils et contrats</h3>
          <p class="mt-2 leading-relaxed">
            Les tests automatisés existants seront complétés par une recette manuelle structurée. Le recours à un audit ou à un accompagnement externe sera étudié selon les ressources disponibles. Les futurs devis, contrats et conventions portant sur un service numérique devront préciser les exigences d’accessibilité, les livrables attendus et les modalités de recette.
          </p>
        </div>
      </div>
    </section>

    <section aria-labelledby="annee-2026" class="rounded-[20px] border-l-4 border-bleuone bg-white p-6 sm:p-8">
      <h2 id="annee-2026" class="text-2xl font-bold text-bleuone">2026 — Établir le socle</h2>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li>publier la déclaration, le présent schéma et le plan d’action annuel ;</li>
        <li>désigner un référent et inventorier le périmètre numérique ;</li>
        <li>réaliser un audit RGAA 4.1.2 représentatif ;</li>
        <li>corriger en priorité les obstacles liés au clavier, au focus, aux contrastes, aux formulaires et aux médias ;</li>
        <li>introduire une checklist de recette et des contrôles automatisés ;</li>
        <li>publier le bilan 2026 et préparer le plan 2027.</li>
      </ul>
    </section>

    <section aria-labelledby="inclusion" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="inclusion" class="text-2xl font-bold text-bleuone">Inclusion et mesures complémentaires</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Les personnes en situation de handicap seront associées, lorsque cela est possible, à l’évaluation des parcours prioritaires et au choix des corrections. Les signalements pourront conduire à proposer un contenu ou un accompagnement sous une autre forme.
      </p>
      <p class="mt-3 leading-relaxed text-gray-800">
        Au-delà des critères obligatoires, Oneduc maintiendra une présentation en langage simplifié de ses fonctions principales et étudiera, selon les usages et les moyens validés, les transcriptions, les sous-titres, la langue des signes française et les recommandations de niveau AAA utiles. Ces mesures complètent la conformité RGAA sans s’y substituer.
      </p>
    </section>

    <section aria-labelledby="annee-2027" class="rounded-[20px] border-l-4 border-bleuone bg-white p-6 sm:p-8">
      <h2 id="annee-2027" class="text-2xl font-bold text-bleuone">2027 — Étendre la mise en accessibilité</h2>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li>traiter les non-conformités restantes selon leur impact utilisateur ;</li>
        <li>évaluer les parcours propres aux quatre rôles et les outils collaboratifs ;</li>
        <li>renforcer le contrôle des contenus créés par les formateurs et importés en SCORM ;</li>
        <li>associer des personnes en situation de handicap aux tests de parcours clés ;</li>
        <li>mettre à jour la déclaration après chaque évaluation significative ;</li>
        <li>préparer l’adoption du RGAA 5 après sa publication effective.</li>
      </ul>
    </section>

    <section aria-labelledby="annee-2028" class="rounded-[20px] border-l-4 border-bleuone bg-white p-6 sm:p-8">
      <h2 id="annee-2028" class="text-2xl font-bold text-bleuone">2028 — Pérenniser</h2>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li>conduire une nouvelle évaluation représentative ;</li>
        <li>mesurer les délais de traitement des signalements et corrections ;</li>
        <li>consolider la formation, la recette continue et les exigences applicables aux prestataires ;</li>
        <li>publier le bilan du schéma 2026-2028 ;</li>
        <li>préparer le schéma pluriannuel suivant.</li>
      </ul>
    </section>

    <section aria-labelledby="formation-tests" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="formation-tests" class="text-2xl font-bold text-bleuone">Formation et méthodes de vérification</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Les développeurs, contributeurs et formateurs seront accompagnés avec des règles simples de production accessible. Les contrôles automatisés compléteront, sans les remplacer, les tests clavier, les vérifications manuelles RGAA, les essais avec des lecteurs d’écran et les tests utilisateurs.
      </p>
    </section>
  </div>
</div>
@endsection
