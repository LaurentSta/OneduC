

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
<body x-data="{ sidebarOpen: true }" class="bg-gray-100 text-gray-900 font-sans">
    {{-- HEADER --}}
    @include('stagiaire.body.header')

        {{-- SIDEBAR --}}
        @include('stagiaire.body.sidebar')
        {{-- CONTENU PRINCIPAL --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    {{-- FOOTER --}}
    @include('stagiaire.body.footer')
    <!-- Video.js JS -->
<script src="https://vjs.zencdn.net/7.21.1/video.min.js"></script>
</body>
</html>

 