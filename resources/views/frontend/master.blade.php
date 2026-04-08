
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <title>@yield('title', 'Onéduc - Accueil')</title>
  <meta name="description" content="@yield('description', 'Onéduc est une plateforme de formation pensée pour l inclusion numerique, la lisibilite des parcours et l accompagnement pedagogique.')">
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
    document.body.classList.add('page-is-entering');

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.body.classList.remove('page-is-entering');
        });
    });
});
</script>
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

@stack('scripts')
</body>
</html>
