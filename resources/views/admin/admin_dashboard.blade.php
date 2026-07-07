{{-- /home/laurents/Oneduc_Dev/resources/views/admin/admin_dashboard.blade.php --}}

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onéduc - Administrateur</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/fonts/tabler-icons.css') }}">
    <link rel="icon" href="{{ asset('backend/assets/img/favicon/favicon.ico') }}" type="image/x-icon">

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- jQuery + DataTables --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <style>
        :root {
            --admin-header-height: 4rem; /* 64px */
            --admin-sidebar-width: 10rem; /* 160px */
        }

        .admin-shell {
            padding-top: var(--admin-header-height);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 1024px) {
            .admin-shell {
                margin-left: var(--admin-sidebar-width);
            }

            .admin-shell.sidebar-closed {
                margin-left: 0;
            }
        }

        @media (max-width: 1023.98px) {
            .admin-shell {
                margin-left: 0 !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body x-data="{ sidebarOpen: true }" class="bg-gray-100 text-gray-900 font-sans min-h-screen">

    {{-- HEADER ADMIN --}}
    @include('admin.body.header')

    {{-- SIDEBAR (fixed) --}}
    @include('admin.body.sidebar')

    {{-- ZONE CONTENU + FOOTER (prend tout l’espace restant) --}}
    <div
        class="admin-shell"
        :class="{ 'sidebar-closed': !sidebarOpen }"
    >
        {{-- CONTENU --}}
        <main id="page-transition" class="flex-1 p-6">
            @yield('admin')
        </main>

        {{-- FOOTER (en bas, centré) --}}
        <footer class="border-t border-gray-200 bg-white">
            <div class="px-6 py-4 text-sm text-gray-600 text-center">
                © <script>document.write(new Date().getFullYear());</script>,
                made with ❤️ by <a href="" target="_blank" class="underline hover:text-gray-900">LaurentS</a>
            </div>
        </footer>
    </div>

    @stack('scripts')
@include('partials.a11y-scripts')
</body>

</html>
