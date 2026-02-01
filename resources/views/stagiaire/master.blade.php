

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onéduc - Administrateur</title>
  @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Ton Tailwind + JS --}}
  <link rel="icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" type="image/x-icon">
    <style>[x-cloak]{ display:none !important; }</style>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Video.js CSS -->
<link href="https://vjs.zencdn.net/7.21.1/video-js.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      /* Onglet fixé sous la barre de navigation, au bord droit */
      .access-anchor{
        position: absolute;
        z-index: 10; /* au-dessus du header/menu profil */
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
        color: #004461!important;
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
<body x-data="{ sidebarOpen: true }" class="bg-gray-100 text-gray-900 font-sans">

    {{-- HEADER --}}
    @include('stagiaire.body_dashboard.header')
      <!-- Conteneur link-mode Confort+ (ne mets PAS de <button> ici) -->
<div id="access-toolbar-anchor" class="access-anchor" aria-label="Outils d’accessibilité"></div>

        {{-- SIDEBAR --}}
        @include('stagiaire.body_dashboard.sidebar')

        <!-- Conteneur link-mode Confort+ (ne mets PAS de <button> ici) -->
<div id="access-toolbar-anchor" class="fixed top-4 right-4 z-50"></div>
        {{-- CONTENU PRINCIPAL --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    {{-- FOOTER --}}
    @include('stagiaire.body_dashboard.footer')
    <!-- Video.js JS -->
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


<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
</body>
</html>

 