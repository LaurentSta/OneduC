<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onéduc - Observateur</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" type="image/x-icon">
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>
<body
  x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
  x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 ? true : false })"
  class="bg-gray-100 text-gray-900 font-sans">

  @include('observateur.body_dashboard.header')
  @include('observateur.body_dashboard.sidebar')

  <main class="flex-1 p-6 lg:ml-[12.5rem]" style="padding-top: calc(var(--app-header-h, 86px) + 12px);">
    @yield('observateur')
  </main>

  <script>
    function syncAppHeaderOffset() {
      const header = document.getElementById('app-header');
      const height = header ? header.offsetHeight : 86;
      document.documentElement.style.setProperty('--app-header-h', `${height}px`);
    }

    document.addEventListener('DOMContentLoaded', syncAppHeaderOffset);
    window.addEventListener('resize', syncAppHeaderOffset);
  </script>
@include('partials.a11y-scripts')
</body>
</html>
