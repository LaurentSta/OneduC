<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Observateur — Parcours en lecture seule')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.5/dist/cdn.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js" defer></script>
  <link href="https://vjs.zencdn.net/8.9.0/video-js.css" rel="stylesheet" />
  <script src="https://vjs.zencdn.net/8.9.0/video.min.js"></script>
  <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-white text-gray-900">
  <div
    x-data="{ sidebarOpen: true }"
    x-init="
      sidebarOpen = JSON.parse(localStorage.getItem('observateurSidebarOpen') ?? 'true');
      $watch('sidebarOpen', v => localStorage.setItem('observateurSidebarOpen', JSON.stringify(v)));
    "
    class="bg-gray-50 min-h-screen"
    @keydown.escape.window="sidebarOpen = false"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
  >
    @include('observateur.formations.body_formations.header')

    <div class="mx-auto px-4 md:px-6 py-0">
      <div class="grid gap-6" :class="sidebarOpen ? 'md:grid-cols-[18rem_1fr] xl:grid-cols-[20rem_1fr]' : 'grid-cols-1'">
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
            @include('observateur.formations.body_formations.sidebar', [
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
  </div>
</body>
</html>
