<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onéduc - Formateur</title>

  {{-- Tailwind CSS & JS via Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" type="image/x-icon">

  {{-- Alpine.js --}}
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  {{-- jQuery & DataTables --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  {{-- CSRF Token pour AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100 text-gray-900 font-sans">

    {{-- HEADER FIXE EN HAUT --}}
@include('formateur.body.header')
  {{-- SIDEBAR FIXE A GAUCHE --}}
  @include('formateur.body.sidebar')
  {{-- CONTENU PRINCIPAL --}}
  <main class="flex-1 p-6">
    @yield('formateur')
  </main>


</body>
</html>

