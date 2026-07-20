@extends('frontend.master')

@section('title', 'Déclaration d’accessibilité - Oneduc')
@section('description', 'État d’accessibilité du site Oneduc, non-conformités connues, contact et voies de recours.')

@section('home')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
  <header class="rounded-[20px] bg-white p-6 sm:p-8">
    <p class="mb-3 font-varela text-sm font-bold uppercase tracking-wider text-bleuone">Accessibilité numérique</p>
    <h1 class="mb-4">Déclaration d’accessibilité</h1>
    <p class="max-w-3xl text-lg leading-relaxed text-gray-700">
      L’Association Oneduc s’engage à rendre son site internet accessible conformément à l’article 47 de la loi n° 2005-102 du 11 février 2005.
    </p>
    <p class="mt-4 max-w-3xl leading-relaxed text-gray-700">
      À cette fin, elle met en œuvre la stratégie et les actions présentées dans le
      <a href="{{ route('accessibilite.schema') }}" class="font-semibold text-bleuone underline">schéma pluriannuel 2026-2028</a>
      et le <a href="{{ route('accessibilite.plan-2026') }}" class="font-semibold text-bleuone underline">plan d’action 2026</a>.
    </p>
    <p class="mt-4 text-sm text-gray-600">Déclaration établie et mise à jour le 20 juillet 2026.</p>
  </header>

  <div class="mt-6">
    @include('frontend.contenu.partials.navigation-accessibilite', ['actif' => 'declaration'])
  </div>

  <section aria-labelledby="etat-conformite" class="mt-6 rounded-[20px] border-2 border-red-300 bg-red-50 p-6 sm:p-8">
    <h2 id="etat-conformite" class="text-2xl font-bold text-red-900">État de conformité</h2>
    <p class="mt-4 text-lg font-bold text-red-900">Accessibilité : non conforme</p>
    <p class="mt-3 leading-relaxed text-red-950">
      Le site Oneduc est non conforme avec le référentiel général d’amélioration de l’accessibilité (RGAA), version 4.1.2. Aucun audit de conformité RGAA complet et en cours de validité ne permet actuellement de mesurer le respect des critères. En conséquence, aucun pourcentage de conformité n’est publié.
    </p>
  </section>

  <div class="mt-6 space-y-6">
    <section aria-labelledby="perimetre" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="perimetre" class="text-2xl font-bold text-bleuone">Périmètre de la déclaration</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Cette déclaration s’applique au site Oneduc disponible à l’adresse
        <a href="{{ url('/') }}" class="font-semibold text-bleuone underline">{{ url('/') }}</a>, y compris ses pages publiques et ses espaces authentifiés.
      </p>
      <p class="mt-3 leading-relaxed text-gray-800">
        La démarche couvre progressivement les espaces administrateur, formateur, stagiaire et observateur, les outils collaboratifs, les contenus pédagogiques, les contenus SCORM et les documents proposés au téléchargement.
      </p>
    </section>

    <section aria-labelledby="resultats-tests" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="resultats-tests" class="text-2xl font-bold text-bleuone">Résultats des tests</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Un examen préliminaire du code a permis d’identifier des améliorations, mais il ne constitue pas un audit de conformité. Un audit RGAA 4.1.2 portant sur un échantillon représentatif est inscrit au plan d’action 2026. La présente déclaration sera mise à jour après cet audit.
      </p>
    </section>

    <section aria-labelledby="contenus-inaccessibles" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="contenus-inaccessibles" class="text-2xl font-bold text-bleuone">Contenus non accessibles</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        En l’absence d’audit complet, la liste exhaustive des non-conformités n’est pas encore établie. Les difficultés déjà repérées concernent notamment :
      </p>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li>certains contrastes de textes et de composants ;</li>
        <li>les alternatives de certains graphiques, zones dessinées sur canvas et médias ;</li>
        <li>la navigation au clavier et la restitution du focus de certains composants dynamiques ;</li>
        <li>l’accessibilité variable des contenus SCORM et des documents importés ;</li>
        <li>l’association complète des erreurs et aides aux champs de formulaire.</li>
      </ul>

      <h3 class="mt-7 text-xl font-bold text-bleuone">Dérogations pour charge disproportionnée</h3>
      <p class="mt-3 text-gray-800">Aucune dérogation pour charge disproportionnée n’a été établie à ce jour.</p>

      <h3 class="mt-7 text-xl font-bold text-bleuone">Contenus non soumis à l’obligation d’accessibilité</h3>
      <p class="mt-3 text-gray-800">Aucun contenu n’a été formellement identifié comme exempté à ce jour.</p>
    </section>

    <section aria-labelledby="etablissement" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="etablissement" class="text-2xl font-bold text-bleuone">Établissement de cette déclaration</h2>
      <p class="mt-4 text-gray-800">Cette déclaration a été établie et mise à jour le 20 juillet 2026.</p>

      <h3 class="mt-6 text-xl font-bold text-bleuone">Technologies utilisées</h3>
      <ul class="mt-3 list-disc space-y-2 pl-6 text-gray-800">
        <li>HTML5 ;</li>
        <li>CSS et Tailwind CSS ;</li>
        <li>JavaScript, Alpine.js et React ;</li>
        <li>Laravel pour la génération des pages.</li>
      </ul>

      <h3 class="mt-6 text-xl font-bold text-bleuone">Environnement et échantillon de test</h3>
      <p class="mt-3 leading-relaxed text-gray-800">
        Aucun environnement de test, outil d’audit ou échantillon de pages n’est déclaré à ce stade au titre d’un audit RGAA. Ces informations seront publiées après l’évaluation prévue en 2026.
      </p>
    </section>

    <section aria-labelledby="contact-accessibilite" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="contact-accessibilite" class="text-2xl font-bold text-bleuone">Retour d’information et contact</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Si vous n’arrivez pas à accéder à un contenu ou à un service, contactez l’Association Oneduc afin d’être orienté vers une solution accessible ou d’obtenir le contenu sous une autre forme.
      </p>
      <ul class="mt-4 list-disc space-y-2 pl-6 text-gray-800">
        <li><a href="{{ route('contact') }}" class="font-semibold text-bleuone underline">Utiliser le formulaire de contact</a> ;</li>
        <li><a href="mailto:contact@oneduc.fr" class="font-semibold text-bleuone underline">écrire à contact@oneduc.fr</a>.</li>
      </ul>
    </section>

    <section aria-labelledby="recours" class="rounded-[20px] bg-white p-6 sm:p-8">
      <h2 id="recours" class="text-2xl font-bold text-bleuone">Voies de recours</h2>
      <p class="mt-4 leading-relaxed text-gray-800">
        Cette procédure peut être utilisée après avoir signalé un défaut d’accessibilité qui empêche d’accéder à un contenu ou à un service, sans avoir obtenu de réponse satisfaisante.
      </p>
      <ul class="mt-4 list-disc space-y-3 pl-6 text-gray-800">
        <li><a href="https://www.defenseurdesdroits.fr/nous-contacter-355" class="font-semibold text-bleuone underline">écrire au Défenseur des droits</a> ;</li>
        <li><a href="https://www.defenseurdesdroits.fr/carte-des-delegues" class="font-semibold text-bleuone underline">contacter un délégué du Défenseur des droits</a> ;</li>
        <li>envoyer gratuitement un courrier, sans timbre, à : Défenseur des droits, Libre réponse 71120, 75342 Paris CEDEX 07.</li>
      </ul>
    </section>

    <p>
      <a href="https://accessibilite.numerique.gouv.fr/" class="font-semibold text-bleuone underline">Consulter le référentiel RGAA officiel</a>.
    </p>
  </div>
</div>
@endsection
