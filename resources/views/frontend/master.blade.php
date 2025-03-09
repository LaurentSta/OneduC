<!doctype html>

<html
  lang="en"
  class="light-style layout-navbar-fixed layout-wide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('frontend/assets/') }}"
  data-template="front-pages-no-customizer"
  data-style="light">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Landing Page - Front Pages | Vuexy - Bootstrap Admin Template</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
      <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Public+Sans&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway&amp;display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&amp;display=swap">


    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/tabler-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/front-page.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/nouislider/nouislider.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/swiper/swiper.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/front-page-landing.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('frontend/assets/vendor/js/helpers.js') }}"></script>

    <!-- Config -->
    <script src="{{ asset('frontend/assets/js/front-config.js') }}"></script>
  </head>

  <body>
    <script src="{{ asset('frontend/assets/vendor/js/dropdown-hover.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/js/mega-dropdown.js') }}"></script>

    <!-- Navbar: Start -->
    @include("frontend.body.header")
    <!-- Navbar: End -->

    <!-- Sections: Start -->
    @yield('home')
    <!-- Sections: End -->

    <!-- Footer: Start -->
    @include("frontend.body.footer")
    <!-- Footer: End -->

    <!-- Core JS -->
    <script src="{{ asset('frontend/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('frontend/assets/vendor/libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/swiper/swiper.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('frontend/assets/js/front-main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('frontend/assets/js/front-page-landing.js') }}"></script>


    <!-- Dropdown -->
    <script>
        document.addEventListener("DOMContentLoaded", function(){
          document.querySelectorAll(".nav-item.dropdown").forEach(function(item) {
            item.addEventListener("mouseenter", function(){
              item.classList.add("show");
              item.querySelector(".dropdown-menu").classList.add("show");
            });
            item.addEventListener("mouseleave", function(){
              item.classList.remove("show");
              item.querySelector(".dropdown-menu").classList.remove("show");
            });
          });
        });
        </script>

  </body>
</html>
