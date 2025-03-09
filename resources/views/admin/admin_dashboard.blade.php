
<!doctype html>

<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../assets/"
  data-template="vertical-menu-template-no-customizer"
  data-style="light">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Tableau de bord administrateur</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset ('backend/assets/img/favicon/favicon.ico')}}" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/fonts/fontawesome.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/fonts/tabler-icons.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/fonts/flag-icons.css')}}" />

    <!-- Core CSS -->

    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/css/rtl/core.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/css/rtl/theme-default.css')}}" />

    <link rel="stylesheet" href="{{asset ('backend/assets/css/demo.css')}}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/node-waves/node-waves.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/typeahead-js/typeahead.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
    <link rel="stylesheet" href="{{asset ('backend/assets/vendor/libs/apex-charts/apex-charts.css')}}" />


    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{asset ('backend/assets/vendor/js/helpers.js')}}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{asset ('backend/assets/js/config.js')}}"></script>
  </head>

  <body>


    <!-- Content -->

    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!--Démarrage sidebar-->
                @include('admin.body.sidebar')
            <!--Fin sidebar-->

                <!-- Layout container -->
          <div class="layout-page">
            <!-- Navbar -->
            @include('admin.body.header')
            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">

            <!-- Content -->
            @yield('admin')
            <!-- / Content -->

              <!-- Footer -->
              @include('admin.body.footer')
              <!-- / Footer -->

              <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
          </div>
          <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
      </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- bui/assets/vendor/js/core.js -->

    <script src="{{ asset('backend/assets/vendor/libs/jquery/jquery.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/popper/popper.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/js/bootstrap.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/node-waves/node-waves.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/hammer/hammer.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/libs/typeahead-js/typeahead.js')}}"></script>
    <script src="{{ asset('backend/assets/vendor/js/menu.js')}}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="{{ asset('backend/assets/js/main.js')}}"></script>

    <!-- Page JS -->
  </body>
</html>
