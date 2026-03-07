@extends('stagiaire.master')
@section('title', 'Documentation stagiaire - Oneduc.fr')

@section('content')
<div class="max-w-[1285px] mx-auto px-8 py-8 space-y-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 py-6">
    <h1 class="text-titre font-raleway text-bleuone">Documentation stagiaire</h1>
    <p class="text-sous-titre font-varela text-orangeone mt-2">Guide pour suivre vos formations sur Oneduc</p>
    <p class="text-gray-700 mt-3">
      Retrouvez ici les étapes clés pour avancer dans vos modules, réussir vos quiz et contacter le support.
    </p>
  </header>

  <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">1. Première connexion</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Lors de la première connexion, un changement de mot de passe est demandé.</li>
        <li>Utilisez un mot de passe unique et robuste.</li>
        <li>Vérifiez vos informations personnelles dans <strong>Paramètre</strong>.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">2. Suivre vos modules</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Accédez à <strong>Formations</strong> pour voir les modules disponibles.</li>
        <li>Ouvrez un module, puis ses sections et leçons dans l'ordre recommandé.</li>
        <li>Votre progression se met à jour au fil de votre lecture.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">3. Quiz dans les leçons</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Les quiz se lancent depuis la leçon quand ils sont disponibles.</li>
        <li>Répondez question par question, puis consultez le résultat final.</li>
        <li>Selon les réglages, vous pouvez recommencer un quiz pour progresser.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">4. Résultats et progression</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>La page <strong>Progressions</strong> affiche votre avancement global.</li>
        <li>Consultez les indicateurs de temps passé et de réussite.</li>
        <li>Utilisez ces données pour organiser vos révisions.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">5. Bonnes pratiques</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Travaillez régulièrement, même sur de courtes sessions.</li>
        <li>Prenez des notes à chaque leçon et revenez sur les points complexes.</li>
        <li>En cas de blocage, contactez rapidement votre formateur.</li>
      </ul>
    </article>

    <article class="bg-white rounded-[20px] shadow-md p-6">
      <h2 class="text-xl font-semibold text-bleuone">6. Aide et support</h2>
      <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
        <li>Le support est accessible via Discord pour une réponse rapide.</li>
        <li>Si vous ne pouvez pas utiliser Discord, le formulaire d'assistance reste disponible.</li>
        <li>Décrivez précisément le problème (module, leçon, message d'erreur, navigateur).</li>
      </ul>
    </article>
  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6">
    <h2 class="text-xl font-semibold text-bleuone">Liens utiles</h2>
    <div class="mt-4 flex flex-wrap gap-3">
      <a href="{{ route('stagiaire.dashboard') }}" class="btn-oneduc">Tableau de bord</a>
      <a href="{{ route('stagiaire.modules') }}" class="btn btn-outline-secondary">Formations</a>
      <a href="{{ route('stagiaire.resultats') }}" class="btn btn-outline-secondary">Progressions</a>
      <a href="{{ route('stagiaire.parametre') }}" class="btn btn-outline-secondary">Paramètre</a>
      <a href="{{ route('contact') }}" class="btn btn-outline-secondary">Support</a>
    </div>
  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6">
    <h2 class="text-xl font-semibold text-bleuone">Centre de support Discord</h2>
    <p class="text-gray-700 mt-3">
      Le support Oneduc est prioritairement traité sur Discord pour un suivi plus rapide. Si vous ne pouvez pas utiliser Discord,
      utilisez le formulaire web.
    </p>

    <div class="mt-4 flex flex-wrap gap-3">
      <a
        href="{{ config('services.discord.support_invite_url') !== '' ? config('services.discord.support_invite_url') : '#' }}"
        target="_blank"
        rel="noopener noreferrer"
        class="btn-oneduc {{ config('services.discord.support_invite_url') === '' ? 'pointer-events-none opacity-50' : '' }}"
        aria-disabled="{{ config('services.discord.support_invite_url') === '' ? 'true' : 'false' }}"
      >
        Rejoindre Discord
      </a>
      <a href="{{ route('contact') }}" class="btn btn-outline-secondary">Utiliser le formulaire</a>
    </div>

    @if(config('services.discord.support_invite_url') === '')
      <p class="text-xs text-amber-700 mt-3">Lien Discord non configuré (`DISCORD_SUPPORT_INVITE_URL`).</p>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <article class="rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold text-bleuone">Avant de poster votre demande</h3>
        <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
          <li>Précisez le module, la section et la leçon concernés.</li>
          <li>Ajoutez le message d’erreur exact si vous en avez un.</li>
          <li>Indiquez votre navigateur et votre appareil (PC, mobile).</li>
          <li>Ajoutez une capture d’écran si possible.</li>
        </ul>
      </article>

      <article class="rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold text-bleuone">Bonnes pratiques sur Discord</h3>
        <ul class="mt-3 text-gray-700 space-y-2 list-disc list-inside">
          <li>Un sujet = une demande, pour faciliter le suivi.</li>
          <li>Évitez de partager des données sensibles (mot de passe, code personnel).</li>
          <li>Utilisez un titre clair: “Bug quiz leçon X” plutôt que “Aide”.</li>
          <li>Restez sur le même fil jusqu’à résolution du problème.</li>
        </ul>
      </article>
    </div>
  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6">
    <h2 class="text-xl font-semibold text-bleuone">FAQ Support Discord</h2>
    <div class="mt-4 space-y-3">
      <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer font-semibold text-bleuone">Je n’arrive pas à rejoindre le serveur Discord, que faire ?</summary>
        <p class="mt-2 text-gray-700">Vérifiez que le lien d’invitation est complet. Si le problème persiste, passez par le formulaire de support.</p>
      </details>

      <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer font-semibold text-bleuone">Quel délai de réponse puis-je attendre ?</summary>
        <p class="mt-2 text-gray-700">Le canal Discord est le plus rapide. Les délais varient selon l’affluence et la complexité du sujet.</p>
      </details>

      <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer font-semibold text-bleuone">Puis-je ouvrir un ticket sans compte Discord ?</summary>
        <p class="mt-2 text-gray-700">Oui. Utilisez le formulaire web de contact, votre demande sera traitée par l’équipe support.</p>
      </details>

      <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer font-semibold text-bleuone">Quelles informations accélèrent le traitement ?</summary>
        <p class="mt-2 text-gray-700">Le contexte exact, l’URL si disponible, la date/heure, votre rôle et une capture d’écran.</p>
      </details>

      <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer font-semibold text-bleuone">Mon quiz ne se lance pas, est-ce un bug ?</summary>
        <p class="mt-2 text-gray-700">Souvent, c’est lié à la session, au navigateur ou à un blocage pop-up. Signalez la leçon concernée au support.</p>
      </details>
    </div>
  </section>
</div>
@endsection
