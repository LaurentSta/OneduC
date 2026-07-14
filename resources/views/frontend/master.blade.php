
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <title>@yield('title', 'Onéduc - Accueil')</title>
  <meta name="description" content="@yield('description', 'Onéduc est une plateforme de formation pensée pour l inclusion numerique, la lisibilite des parcours et l accompagnement pedagogique.')">
  <link rel="canonical" href="@yield('canonical', url()->current())">

  {{-- Open Graph --}}
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Onéduc">
  <meta property="og:locale" content="fr_FR">
  <meta property="og:title" content="@yield('title', 'Onéduc - Accueil')">
  <meta property="og:description" content="@yield('description', 'Onéduc est une plateforme de formation pensée pour l inclusion numerique, la lisibilite des parcours et l accompagnement pedagogique.')">
  <meta property="og:url" content="@yield('canonical', url()->current())">
  <meta property="og:image" content="@yield('og_image', asset('frontend/assets/img/branding/logo.png'))">

  {{-- Twitter Card --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('title', 'Onéduc - Accueil')">
  <meta name="twitter:description" content="@yield('description', 'Onéduc est une plateforme de formation pensée pour l inclusion numerique, la lisibilite des parcours et l accompagnement pedagogique.')">
  <meta name="twitter:image" content="@yield('og_image', asset('frontend/assets/img/branding/logo.png'))">

  {{-- Données structurées : identité de l'association, sur toutes les pages publiques --}}
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "EducationalOrganization",
    "name": "Onéduc",
    "url": "{{ route('index') }}",
    "logo": "{{ asset('frontend/assets/img/branding/logo.png') }}",
    "description": "Association loi 1901 portant une plateforme de formation pensée pour l'inclusion numérique.",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "78 rue Danton, boîte n°10",
      "postalCode": "93310",
      "addressLocality": "Le Pré-Saint-Gervais",
      "addressCountry": "FR"
    }
  }
  </script>
  @stack('structured-data')

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;600;700;800&family=Varela+Round&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="oneduc-public bg-white text-gray-900 font-sans " style="background-color: #f8f7fa;">
<!--======================================
        START HEADER AREA
    ======================================-->

    @include('frontend.body.header')

      <!-- Pop-up Beta -->
<div
    id="beta-popup"
    class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50 min-h-screen p-4"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="beta-popup-title"
>
  <div class="bg-white rounded-lg shadow-lg max-w-md mx-auto p-6 text-center transition-transform duration-200 ease-out">
    <h2 id="beta-popup-title" class="text-xl font-bold text-bleuone mb-4">Version Bêta</h2>
    <p class="text-gray-700 mb-6">
      Ce site est actuellement en <strong>version bêta</strong>.<br>
      Il est développé par des formateurs bénévoles qui contribuent à son contenu.<br>
      Des améliorations et corrections sont en cours.
    </p>
    <div class="text-center">
      <button
        id="beta-popup-continue"
        type="button"
        class="btn-oneduc"
      >
        Continuer
      </button>
    </div>
  </div>
</div>


    <!--======================================
            END HEADER AREA
    ======================================-->
     <main
        id="page-transition"
        class="page-transition-root"
    >
        @yield('home')
    </main>

    <!-- ================================
             END FOOTER AREA
    ================================= -->
    @include('frontend.body.footer')
    <!-- ================================
              END FOOTER AREA
    ================================= -->
@include('cookie-consent::index')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const popup = document.getElementById('beta-popup');
  const continueBtn = document.getElementById('beta-popup-continue');

  if (!popup || !continueBtn) return;

  const storageKey = 'betaPopupSeen';
  let alreadySeen = false;

  try {
    alreadySeen = window.localStorage.getItem(storageKey) === '1';
  } catch (error) {
    alreadySeen = false;
  }

  if (!alreadySeen) {
    popup.classList.remove('hidden');
    popup.classList.add('flex');
    popup.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
  }

  const closePopup = () => {
    popup.classList.remove('flex');
    popup.classList.add('hidden');
    popup.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');

    try {
      window.localStorage.setItem(storageKey, '1');
    } catch (error) {
      // Ignore storage failures: the popup should not block access.
    }
  };

  continueBtn.addEventListener('click', closePopup);

  popup.addEventListener('click', event => {
    if (event.target === popup) {
      closePopup();
    }
  });
});
</script>

{{-- Bouton d'aide flottant : présent sur toute la page publique --}}
<a href="{{ route('contact') }}"
  class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-orangeone text-white shadow-xl flex items-center justify-center hover:scale-110 transition-transform focus:outline-none focus:ring-4 focus:ring-orangeone focus:ring-offset-2"
  aria-label="Besoin d'aide ? Contactez-nous">
  <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
  </svg>
</a>

{{-- Modal FALC (Facile à Lire et à Comprendre) --}}
<div id="falc-modal"
  class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-60 z-50 p-4"
  role="dialog" aria-modal="true" aria-labelledby="falc-title" aria-hidden="true">
  <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-auto p-8 space-y-6 overflow-y-auto max-h-[90vh]">
    <div class="flex justify-between items-start gap-4">
      <h2 id="falc-title" class="text-2xl font-raleway font-bold text-bleuone">Onéduc — Version facile à lire</h2>
      <button type="button" id="falc-close" class="flex-shrink-0 text-gray-400 hover:text-gray-700 transition focus:outline-none focus:ring-2 focus:ring-bleuone rounded" aria-label="Fermer">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="space-y-4 font-lisible text-xl leading-relaxed text-gray-800">
      <p><strong>Onéduc</strong> est un outil pour les <strong>formateurs</strong>.</p>
      <p>Les formateurs aident des personnes à apprendre.</p>
      <p>Avec Onéduc, un formateur peut :</p>
      <ul class="list-disc pl-6 space-y-2">
        <li>Créer des cours en ligne.</li>
        <li>Voir les progrès de ses apprenants.</li>
        <li>Utiliser des jeux et des activités interactives.</li>
      </ul>
      <p>C'est <strong>gratuit</strong> pendant les 30 premiers jours.</p>
      <p>Après, il faut <strong>adhérer à l'association Onéduc</strong> pour continuer.</p>
      <p>Il n'y a <strong>rien à installer</strong> sur l'ordinateur.</p>
      <p>Pour commencer, cliquez sur <strong class="text-orangeone">« Je suis formateur »</strong>.</p>
      <p>Si vous avez reçu un <strong>code d'accès</strong> de votre formateur,<br>cliquez sur <strong class="text-bleuone">« J'ai un code d'accès »</strong>.</p>
    </div>
    <div class="flex justify-center pt-2">
      <button type="button" id="falc-close-btn" class="btn-oneduc">Fermer</button>
    </div>
  </div>
</div>

@include('partials.a11y-scripts')

<script>
// Gestion du modal FALC
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var modal      = document.getElementById('falc-modal');
    var closeBtn   = document.getElementById('falc-close');
    var closeBtnOk = document.getElementById('falc-close-btn');
    if (!modal) return;

    function openFalc() {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('overflow-hidden');
      if (closeBtn) closeBtn.focus();
    }

    function closeFalc() {
      modal.classList.remove('flex');
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('open-falc', openFalc);
    if (closeBtn)   closeBtn.addEventListener('click',   closeFalc);
    if (closeBtnOk) closeBtnOk.addEventListener('click', closeFalc);
    modal.addEventListener('click', function (e) { if (e.target === modal) closeFalc(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeFalc(); });
  });
})();
</script>

@stack('scripts')
</body>
</html>
