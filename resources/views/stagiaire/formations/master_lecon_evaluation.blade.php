<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lecture SCORM')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- AlpineJS + collapse --}}
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.5/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>
    <style>
    [x-cloak] {
      display: none !important;
    }
    /* ================================
    Bouton d’alerte pédagogique
    ================================ */

    /* Animation de pulsation douce */
    @keyframes oneduc-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(233, 77, 42, 0.55);
    }
    70% {
        box-shadow: 0 0 0 14px rgba(233, 77, 42, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(233, 77, 42, 0);
    }
    }

    /* Classe appliquée au bouton */
    .oneduc-btn-alert {
    animation: oneduc-pulse 1.8s infinite;
    }

    /* Respect des préférences d’accessibilité */
    @media (prefers-reduced-motion: reduce) {
    .oneduc-btn-alert {
        animation: none;
    }
    }
    </style>

</head>
<body class="bg-white text-gray-900">
 
    <div
        x-data="{ sidebarOpen: true, resourcesPanelOpen: false }"
        class="bg-gray-50 min-h-screen"
        @keydown.escape.window="sidebarOpen = false"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
    >
        @if (!isset($hideHeader))
            @include('stagiaire.formations.body_formations.header')
        @endif
        {{-- Bouton Confort+ sous le header --}}
<div id="access-toolbar-anchor" class="access-anchor" aria-label="Outils d’accessibilité"></div>
        <div class="mx-auto px-2 md:px-2 py-0">
            <div
              class="grid gap-6"
              :class="sidebarOpen
                        ? 'md:grid-cols-[18rem_1fr] xl:grid-cols-[20rem_1fr]'
                        : 'grid-cols-1'"
            >
                @if(isset($module))
                    <aside id="module-sidebar-wrapper"
                           x-show="sidebarOpen"
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="-translate-x-full opacity-0"
                           x-transition:enter-end="translate-x-0 opacity-100"
                           x-transition:leave="transition ease-in duration-150"
                           x-transition:leave-start="translate-x-0 opacity-100"
                           x-transition:leave-end="-translate-x-full opacity-0"
                           class="transform">
                        @include('stagiaire.formations.body_formations.sidebar', [
                            'module' => $module,
                            'lectureStats' => $lectureStats ?? [],
                            'sectionStatuses' => $sectionStatuses ?? [],
                            'selectedLecture' => $selectedLecture ?? null,
                            'moduleResources' => $moduleResources ?? ($lessonResources ?? collect()),
                        ])
                    </aside>
                @endif
                <main class="min-w-0">
                    <div class="px-0 lg:px-0 py-0">
                        @yield('content')
                    </div>
                    {{-- Dans master_lecon_evaluation.blade.php --}}
{{-- On retire 'inset-x-0' et on utilise 'left-1/2' avec un transform --}}
<div id="next-lesson-wrapper" 
     class="hidden fixed bottom-10 z-50 pointer-events-none transition-all duration-300"
     style="left: calc(50% + (var(--sidebar-offset, 0px) / 2)); transform: translateX(-50%);">
    <button id="next-lesson-button"
        type="button"
        class="opacity-0 pointer-events-auto cursor-pointer oneduc-btn-alert
               px-6 py-2.5 text-white bg-[#E94D2A]
               rounded-full
               transition-all duration-300 flex items-center gap-2
               hover:opacity-90 active:scale-95">

        <span id="next-button-text">Leçon suivante</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
    </button>
</div>
                </main>
            </div>
        </div>

        
    </div>
    <script>
  var hebergementDomaine = window.location.origin;
  var hebergementFullPath = hebergementDomaine + '/confortplus/';
  window.accessibilitytoolbar_custom = {
    idLinkModeContainer: 'access-toolbar-anchor',
    cssLinkModeClassName: 'btn-access'
  };
</script>
{{-- ... reste de votre code master ... --}}

    <script>
    function syncAppHeaderOffset() {
        const header = document.getElementById('app-header') || document.querySelector('header');
        const height = header ? header.offsetHeight : 86;
        document.documentElement.style.setProperty('--app-header-h', `${height}px`);
    }

    // Fonction pour calculer et appliquer le centrage sur la zone de contenu
    function ajusterPositionBouton() {
        const sidebar = document.getElementById('module-sidebar-wrapper');
        const wrapper = document.getElementById('next-lesson-wrapper');
        const main = document.querySelector('main');
        
        if (sidebar && wrapper && main) {
            // On récupère la largeur de la sidebar si elle est visible
            // Sinon on considère 0 si elle est masquée par AlpineJS
            const isSidebarOpen = sidebar && sidebar.offsetWidth > 0;
            const sidebarWidth = isSidebarOpen ? sidebar.offsetWidth : 0;
            
            // On définit une variable CSS pour décaler le bouton vers la droite
            // de la moitié de la largeur de la sidebar
            document.documentElement.style.setProperty('--sidebar-offset', sidebarWidth + 'px');
        }
    }

    // Écoute les événements AlpineJS de votre header/sidebar
    window.addEventListener('toggle-sidebar', () => {
        // Petit délai pour laisser le temps à l'animation de la sidebar de commencer
        setTimeout(ajusterPositionBouton, 50);
    });

    // Ajustement lors du redimensionnement de la fenêtre ou du chargement
    window.addEventListener('resize', syncAppHeaderOffset);
    window.addEventListener('resize', ajusterPositionBouton);
    window.addEventListener('load', syncAppHeaderOffset);
    window.addEventListener('load', ajusterPositionBouton);

    // Exécution immédiate au cas où
    document.addEventListener('DOMContentLoaded', syncAppHeaderOffset);
    document.addEventListener('DOMContentLoaded', ajusterPositionBouton);
    </script>
</body>
</html>
