
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <title>Onéduc - Accueil</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
    /* Surcouche Confort+ uniquement pour le front public */
    body.oneduc-public {
      --oneduc-blue: #004461;
      --oneduc-blue-light: #005d85;
      --oneduc-orange: #E94D2A;
      --oneduc-border: #d8e6ee;
    }

    body.oneduc-public .access-anchor {
      right: 22px;
    }

    body.oneduc-public .btn-access {
      min-width: 56px;
      width: 56px;
      height: 56px;
      border-radius: 999px;
      border: none !important;
      outline: none !important;
      background: #fff !important;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    body.oneduc-public .btn-access::after {
      color: var(--oneduc-orange);
      font-size: 27px;
      font-weight: 800;
    }

    body.oneduc-public .btn-access:hover,
    body.oneduc-public .btn-access:focus-visible {
      background: #fff !important;
      border: none !important;
      outline: none !important;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.24) !important;
    }

    body.oneduc-public #accessibility-toolbar,
    body.oneduc-public .accessibility-toolbar {
      top: var(--access-top, 96px) !important;
      right: 16px !important;
      left: auto !important;
      z-index: 9998;
    }

    body.oneduc-public #accessibilitytoolbarGraphic {
      border: 1px solid var(--oneduc-border);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 18px 46px rgba(0, 68, 97, 0.2) !important;
      font-family: "Varela Round", Arial, sans-serif;
    }

    body.oneduc-public #uci_toolbar-quick {
      border-bottom: 1px solid #e7eef3;
      background: #fff;
    }

    body.oneduc-public #uci_middle_toolbar,
    body.oneduc-public .uci_menu_bton,
    body.oneduc-public .uci_menu_close {
      border: 1px solid #e7eef3 !important;
      border-color: #e7eef3 !important;
    }

    body.oneduc-public #accessibilitytoolbarGraphic .ucibtn-secondary,
    body.oneduc-public #closeMaskDiv .ucibtn-secondary {
      border-color: var(--oneduc-blue);
      color: var(--oneduc-blue);
    }

    body.oneduc-public #accessibilitytoolbarGraphic .ucibtn-secondary:hover,
    body.oneduc-public #accessibilitytoolbarGraphic .ucibtn-secondary:focus,
    body.oneduc-public #closeMaskDiv .ucibtn-secondary:hover,
    body.oneduc-public #closeMaskDiv .ucibtn-secondary:focus {
      background-color: var(--oneduc-blue);
      border-color: var(--oneduc-blue);
      color: #fff;
    }

    body.oneduc-public #accessibilitytoolbarGraphic .ucibtn-primary {
      background-color: var(--oneduc-orange);
      border-color: var(--oneduc-orange);
      color: #fff;
    }

    body.oneduc-public #accessibilitytoolbarGraphic .ucibtn-primary:hover,
    body.oneduc-public #accessibilitytoolbarGraphic .ucibtn-primary:focus {
      background-color: #c43d1f;
      border-color: #c43d1f;
      color: #fff;
    }

    body.oneduc-public #accessibilitytoolbarGraphic input[type="checkbox"]:checked + label:before,
    body.oneduc-public #accessibilitytoolbarGraphic input[type="checkbox"]:checked + label:after {
      background-color: var(--oneduc-orange);
      border-color: var(--oneduc-orange);
    }

    body.oneduc-public #accessibilitytoolbarGraphic input[type="radio"]:checked + label:before {
      border-color: var(--oneduc-orange);
    }

    body.oneduc-public #accessibilitytoolbarGraphic .uci_submenu {
      border: 1px solid var(--oneduc-border) !important;
      border-color: var(--oneduc-border);
      border-radius: 12px;
      box-shadow: 0 14px 30px rgba(0, 68, 97, 0.16) !important;
    }

    @media (max-width: 768px) {
      body.oneduc-public .access-anchor {
        right: 14px;
      }

      body.oneduc-public .btn-access {
        width: 52px;
        min-width: 52px;
        height: 52px;
      }

      body.oneduc-public #accessibility-toolbar,
      body.oneduc-public .accessibility-toolbar {
        right: 10px !important;
      }
    }
  </style>

</head>
<body class="oneduc-public bg-white text-gray-900 font-sans " style="background-color: #f8f7fa;">
<!--======================================
        START HEADER AREA
    ======================================-->

    @include('frontend.body.header')
<!-- Lanceur Confort+ ancré sous le nav -->
<div id="access-toolbar-anchor" class="access-anchor" aria-label="Outils d’accessibilité"></div>

      </div>

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
        class="bg-orangeone text-white px-6 py-2 rounded-md hover:bg-orange-600"
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
  // Position dynamique sous le vrai header
  function setAccessTop(){
    var header = document.querySelector('header') || document.getElementById('site-header');
    var h = (header && header.offsetHeight) ? header.offsetHeight : 84;
    document.documentElement.style.setProperty('--access-top', (h + 12) + 'px');
  }
  document.addEventListener('DOMContentLoaded', setAccessTop);
  window.addEventListener('resize', setAccessTop);

  // Paramètres Confort+
  var hebergementDomaine = window.location.origin;
  var hebergementFullPath = hebergementDomaine + '/confortplus/';
  window.accessibilitytoolbar_custom = {
    idLinkModeContainer: 'access-toolbar-anchor',
    cssLinkModeClassName: 'btn-access'
  };
</script>
<script src="{{ asset('confortplus/js/toolbar.min.js') }}" defer></script>
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
