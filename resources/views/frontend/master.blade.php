
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="initial-scale=1.0">
  <title>Onéduc - Accueil</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/alpinejs" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>



</head>
<body class="bg-white text-gray-900 font-sans " style="background-color: #f8f7fa;">
<!--======================================
        START HEADER AREA
    ======================================-->

    @include('frontend.body.header')

    <!--======================================
            END HEADER AREA
    ======================================-->

     @yield('home')

    <!-- ================================
             END FOOTER AREA
    ================================= -->

    @include('frontend.body.footer')


    <!-- ================================
              END FOOTER AREA
    ================================= -->
@include('cookie-consent::index')

</body>
</html>
