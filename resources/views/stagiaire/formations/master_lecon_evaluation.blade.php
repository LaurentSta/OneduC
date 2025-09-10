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

    {{-- Video.js (optionnel) --}}
    <link href="https://vjs.zencdn.net/8.9.0/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/8.9.0/video.min.js"></script>
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
<body class="bg-white text-gray-900">
    <div
        x-data="{ sidebarOpen: true }"
        x-init="
            sidebarOpen = JSON.parse(localStorage.getItem('sidebarOpen') ?? 'true');
            $watch('sidebarOpen', v => localStorage.setItem('sidebarOpen', JSON.stringify(v)));
        "
        class="bg-gray-50 min-h-screen"
        @keydown.escape.window="sidebarOpen = false"
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
    >
        @if (!isset($hideHeader))
            @include('stagiaire.formations.body_formations.header')
        @endif

        <div class="mx-auto px-4 md:px-6 py-4">
            <div
              class="grid gap-6"
              :class="sidebarOpen
                        ? 'md:grid-cols-[18rem_1fr] xl:grid-cols-[20rem_1fr]'
                        : 'grid-cols-1'"            >
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
                        ])
                    </aside>
                @endif
                <main class="min-w-0">
                    <div class="px-0 lg:px-0 py-0">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
<div id="access-toolbar-anchor" class="access-anchor" aria-label="Outils d’accessibilité"></div>

        {{-- Bouton global "leçon suivante" (affiché par API.js depuis l’iframe) --}}
        <div id="next-lesson-wrapper"
             class="hidden fixed bottom-6 right-6 z-50"
             aria-live="polite">
            <button id="next-lesson-button"
                    type="button"
                    class="opacity-0 pointer-events-none px-4 py-2 rounded-[10px] shadow-md bg-[#004461] text-white hover:bg-[#00364d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#E94D2A] transition"
                    aria-label="Aller à la leçon suivante">
                Leçon suivante
            </button>
        </div>
    </div>
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
