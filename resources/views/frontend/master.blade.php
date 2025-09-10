
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <title>Onéduc - Accueil</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
  /* Onglet fixé sous la barre de navigation, au bord droit */
  .access-anchor{
    position: absolute;
    z-index: 9999; /* au-dessus du header/menu profil */
    pointer-events: none; /* laisse passer les clics sauf sur le bouton injecté */
    right: 20px;    
  }
  /* Bouton-onglet généré par Confort+ (classe définie via config JS) */
  .btn-access{
    pointer-events: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    min-height: 4px;
    padding: 10px 8px;
    border-style: solid;
    border-color: #e5e7eb;
    border-width: 0px 1px 1px 1px;
    
    border-radius: 0 0 20px 20px;
    color: #fff!important;
    outline: none;
  }
  .btn-access a {
  }
  .btn-access:focus-visible{ outline: 3px solid #E94D2A; outline-offset: 2px; }
  /* Panneau Confort+ décalé pour ne pas couvrir le header */
  #accessibility-toolbar, .accessibility-toolbar{
    position: fixed !important;
    right: 60px !important;
    transform: none !important;
    max-height: calc(100vh - var(--access-top, 96px) - 16px);
    overflow: auto;
    z-index: 9998;
     box-shadow: 0 ;
  }
  @media (max-width: 768px){
    .access-anchor{ right: 16px; top: auto; bottom: 16px; }
    #accessibility-toolbar, .accessibility-toolbar{ right: 16px !important; top: auto !important; bottom: 80px !important; }
    .btn-access{ writing-mode: initial; transform: none; width: 56px; min-height: 56px; border-radius: 9999px; padding: 12px; }
  }
</style>

</head>
<body class="bg-white text-gray-900 font-sans " style="background-color: #f8f7fa;">
<!--======================================
        START HEADER AREA
    ======================================-->

    @include('frontend.body.header')
<!-- Lanceur Confort+ ancré sous le nav -->
<div id="access-toolbar-anchor" class="access-anchor" aria-label="Outils d’accessibilité"></div>

      </div>
    <!--======================================
            END HEADER AREA
    ======================================-->
     @yield('home')
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

</body>
</html>
