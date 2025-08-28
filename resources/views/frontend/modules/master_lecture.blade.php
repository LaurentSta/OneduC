<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lecture SCORM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AlpineJS + collapse -->
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.5/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>

    <!-- Video.js (si besoin vidéo) -->
    <link href="https://vjs.zencdn.net/8.9.0/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/8.9.0/video.min.js"></script>
</head>
<body class="bg-white text-gray-900">

    <!-- Wrapper Alpine: gère l’état sidebar + mémorisation -->
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
            <!-- Grille: sidebar fixe à gauche + contenu -->
            <div
              class="grid gap-6"
              :class="sidebarOpen
                        ? 'md:grid-cols-[18rem_1fr] xl:grid-cols-[20rem_1fr]'
                        : 'grid-cols-1'"
            >
              @if(isset($module))
                <div id="module-sidebar-wrapper"
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
                </div>
              @endif

              <main class="min-w-0">
                <div class="px-0 lg:px-0 py-0">
                  @yield('content')
                </div>
              </main>
            </div>
        </div>
    </div>
</body>
</html>
