
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <title>Onéduc - Accueil</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</head>
<body class="bg-white text-gray-900 font-sans " style="background-color: #f8f7fa;">
<!--======================================
        START HEADER AREA
    ======================================-->

    @include('frontend.body.header')
<!-- Lanceur Confort+ ancré sous le nav -->
<div id="access-toolbar-anchor" class="access-anchor" aria-label="Outils d’accessibilité"></div>

      </div>

      <!-- Pop-up Beta -->
<div 
    x-data="{ open: localStorage.getItem('betaPopupSeen') !== '1' }" 
    x-show="open"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 min-h-screen p-4"
    x-cloak
>
  <div class="bg-white rounded-lg shadow-lg max-w-md mx-auto p-6 text-center">
    <h2 class="text-xl font-bold text-bleuone mb-4">Version Bêta</h2>
    <p class="text-gray-700 mb-6">
      Ce site est actuellement en <strong>version bêta</strong>.<br>
      Il est développé par des formateurs bénévoles qui contribuent à son contenu.<br>
      Des améliorations et corrections sont en cours.
    </p>
    <div class="text-center">
      <button 
        @click="open = false; localStorage.setItem('betaPopupSeen', '1')" 
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
        class="opacity-0 transition-opacity duration-500 ease-out"
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
    const page = document.getElementById('page-transition');
    if (page) {
        requestAnimationFrame(() => {
            page.classList.remove('opacity-0');
            page.classList.add('opacity-100');
        });
    }
});
</script>

@stack('scripts')
</body>
</html>
