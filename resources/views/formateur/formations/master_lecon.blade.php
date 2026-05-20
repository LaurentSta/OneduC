{{-- resources/views/formateur/formations/master_lecon.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Formateur — Chapitres & Leçons')</title>

  @vite(['resources/css/app.css','resources/js/app.js'])

  {{-- Video.js (utilisé dans chapitre.blade.php) --}}
  <link href="https://vjs.zencdn.net/8.9.0/video-js.css" rel="stylesheet" />
  <script src="https://vjs.zencdn.net/8.9.0/video.min.js"></script>
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>
<body class="bg-white text-gray-900">
  <div
    x-data="{ sidebarOpen: true }"
    class="bg-gray-50 min-h-screen"
    @keydown.escape.window="sidebarOpen = false"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
  >
    {{-- HEADER formations formateur --}}
    @include('formateur.formations.body_formations.header')

    <div class="mx-auto px-4 md:px-6 py-0">
      <div
        class="grid gap-6"
        :class="sidebarOpen
                  ? 'md:grid-cols-[18rem_1fr] xl:grid-cols-[20rem_1fr]'
                  : 'grid-cols-1'">
        {{-- SIDEBAR si $module défini --}}
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
            @include('formateur.formations.body_formations.sidebar', [
              'module' => $module,
              'lectureStats' => $lectureStats ?? [],
              'sectionStatuses' => $sectionStatuses ?? [],
              'selectedLecture' => $selectedLecture ?? null,
            ])
          </aside>
        @endif

        {{-- CONTENU --}}
        <main class="min-w-0">
          <div class="px-0 lg:px-0 py-0">
            @yield('content')
          </div>
        </main>
      </div>
    </div>

    {{-- Bouton global "leçon suivante" (facultatif, utilisé par API.js) --}}
    <div id="next-lesson-wrapper"
         class="hidden fixed bottom-6 right-6 z-50"
         aria-live="polite">
      <button id="next-lesson-button"
              type="button"
              class="opacity-0 pointer-events-none px-4 py-2 rounded-[10px] shadow-md bg-[#E94D2A] text-white hover:bg-[#cf4121] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#E94D2A] transition"
              aria-label="Aller à la leçon suivante">
        Leçon suivante
      </button>
    </div>
  </div>
  <script>
    function syncAppHeaderOffset() {
      const header = document.getElementById('app-header') || document.querySelector('header');
      const height = header ? header.offsetHeight : 86;
      document.documentElement.style.setProperty('--app-header-h', `${height}px`);
    }

    document.addEventListener('DOMContentLoaded', syncAppHeaderOffset);
    window.addEventListener('load', syncAppHeaderOffset);
    window.addEventListener('resize', syncAppHeaderOffset);
  </script>
@include('partials.a11y-scripts')
</body>
</html>
