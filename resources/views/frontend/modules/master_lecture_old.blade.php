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
            @include('frontend.modules.body.header')
        @endif

        <div class="mx-auto px-4 md:px-6 py-4">
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
                        @include('frontend.modules.body.sidebar', [
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
</body>
</html>
