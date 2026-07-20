

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
</head>
<body
  x-data="{
    sidebarOpen: window.innerWidth >= 1024,
    sidebarCollapsed: localStorage.getItem('oneduc-stagiaire-sidebar-collapsed') === '1',
    sidebarTransitionsReady: false,
  }"
  x-init="
    window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 ? true : false });
    $watch('sidebarCollapsed', value => localStorage.setItem('oneduc-stagiaire-sidebar-collapsed', value ? '1' : '0'));
    $nextTick(() => sidebarTransitionsReady = true);
  "
  class="bg-gray-100 text-gray-900 font-sans">

    <a
      href="#page-transition"
      class="fixed left-3 top-3 z-[70] -translate-y-20 rounded-md bg-white px-4 py-2 text-sm font-semibold text-bleuone transition focus:translate-y-0 focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-orangeone"
    >
      Aller au contenu principal
    </a>

    {{-- HEADER --}}
    @include('stagiaire.body_dashboard.header')

        {{-- SIDEBAR --}}
        @include('stagiaire.body_dashboard.sidebar')

        {{-- CONTENU PRINCIPAL --}}
        <main
            id="page-transition"
            tabindex="-1"
            class="flex-1 p-6"
            :class="{ 'transition-[margin-left] duration-300': sidebarTransitionsReady, 'lg:ml-48': !sidebarCollapsed }">
            @yield('content')
        </main>

    {{-- FOOTER --}}
    @include('stagiaire.body_dashboard.footer')
    <!-- Video.js JS -->
<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
@include('partials.a11y-scripts')
</body>
</html>

 
