<!doctype html>

<html
  lang="fr"
  class="light-style layout-wide customizer-hide"
  dir="ltr"
  data-theme="theme-default"

  data-template="vertical-menu-template-no-customizer"
  data-style="light">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Couverture de connexion - Pages | Vuexy - Template Admin Bootstrap</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('frontend/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/rtl/theme-default.css') }}" />

    <link rel="stylesheet" href="{{ asset('frontend/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.css') }}" />

    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <!-- Vendor -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/libs/@form-validation/form-validation.css') }}" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('frontend/assets/vendor/css/pages/page-auth.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('frontend/assets/vendor/js/helpers.js') }}"></script>
    <!--! Les fichiers Template customizer & Theme config DOIVENT être inclus après les feuilles de style et helpers.js dans la section <head>. -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('frontend/assets/js/config.js') }}"></script>
  </head>

  <body>
    <!-- Content -->

    <div class="authentication-wrapper authentication-cover">
      <!-- Logo -->
      <a class="navbar-brand d-flex align-items-center" href="/"><img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg')}}" width="237" height="69"></a>
      <!-- /Logo -->
      <div class="authentication-inner row m-0">
        <!-- /Left Text -->
        <div class="d-none d-lg-flex col-lg-8 p-0">
          <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
            <img
              src="{{ asset('frontend/assets/img/illustrations/LogoOneduc.svg') }}"
              alt="couverture connexion"
              class="my-5 auth-illustration"
               />

            <img
              src="{{ asset('frontend/assets/img/illustrations/bg-shape-image-light.png') }}"
              alt="fond de couverture"
              class="platform-bg"
               />
          </div>
        </div>
        <!-- /Left Text -->

        <!-- Login -->
        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
          <div class="w-px-400 mx-auto mt-12 pt-5">
            <h4 class="mb-1">Bienvenue sur Oneduc.fr ! 👋</h4>
            <p class="mb-6">Veuillez vous connecter à votre compte et commencer l'aventure</p>

            <form id="formAuthentication" method="POST" action="{{ route('login') }}">
                @csrf
              <div class="mb-6">
                <label for="email" class="form-label">Email ou nom d'utilisateur</label>
                <input type="text" class="form-control" id="email"
                  name="email"
                  placeholder="Entrez votre email"
                  autofocus />
              </div>

              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="input-group input-group-merge">
                  <input
                    type="password"
                    id="password"
                    class="form-control"
                    name="password"
                    placeholder="••••••••••••"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>
              <div class="my-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me">Se souvenir de moi</label>
                  </div>
                  <a href="{{ route('password.request') }}">
                    <p class="mb-0">Mot de passe oublié ?</p>
                  </a>
                </div>
              </div>
              <button class="btn btn-primary d-grid w-100">Se connecter</button>
            </form>

            <p class="text-center">
              <span>Nouveau sur notre plateforme ?</span>
              <a href="{{route ('inscription')}}">
                <span>Créer un compte</span>
              </a>
            </p>

            <div class="divider my-6">
              <div class="divider-text">ou</div>
            </div>

            <div class="d-flex justify-content-center">
              <a href="javascript:;" class="btn btn-sm btn-icon rounded-pill btn-text-facebook me-1_5">
                <i class="tf-icons ti ti-brand-facebook-filled"></i>
              </a>

              <a href="javascript:;" class="btn btn-sm btn-icon rounded-pill btn-text-twitter me-1_5">
                <i class="tf-icons ti ti-brand-twitter-filled"></i>
              </a>

              <a href="javascript:;" class="btn btn-sm btn-icon rounded-pill btn-text-github me-1_5">
                <i class="tf-icons ti ti-brand-github-filled"></i>
              </a>

              <a href="javascript:;" class="btn btn-sm btn-icon rounded-pill btn-text-google-plus">
                <i class="tf-icons ti ti-brand-google-filled"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="{{ asset('frontend/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('frontend/assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('frontend/assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('frontend/assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('frontend/assets/js/pages-auth.js') }}"></script>
  </body>
</html>
