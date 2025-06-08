<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Onéduc - Administrateur</title>
  @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Ton Tailwind + JS --}}
  <link rel="icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" type="image/x-icon">
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>
<body x-data="{ sidebarOpen: true }" class="bg-gray-100 text-gray-900 font-sans">

{{-- HEADER ADMIN --}}
@include('admin.body.header')

{{-- SIDEBAR --}}
@include('admin.body.sidebar')



    {{-- CONTENU PRINCIPAL --}}
    <main class="flex-1 p-6">
      @yield('admin')
    </main>
  </div>

  {{-- FOOTER --}}
  <footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
      <div
        class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
        <div class="text-body">
          ©
          <script>
            document.write(new Date().getFullYear());
          </script>
          , made with ❤️ by <a href="" target="_blank" class="footer-link">LaurentS</a>
        </div>

      </div>
    </div>
  </footer>




</body>
</html>
