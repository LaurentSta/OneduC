<!-- Footer: Start -->
<footer class="landing-footer bg-body footer-text">
    <div class="footer-top position-relative overflow-hidden z-1">
        <img
          src="{{ asset('frontend/assets/img/front-pages/backgrounds/footer-bg-light.png') }}"
          alt="fond de pied de page"
          class="footer-bg banner-bg-img z-n1"
           />
        <div class="container">
          <div class="row gx-0 gy-6 g-lg-10">
            <div class="col-lg-5">
              <a href="landing-page.html" class="app-brand-link mb-6">
                <span class="app-brand-logo demo">
                  <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                      fill="#7367F0" />
                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                      fill="#7367F0" />
                  </svg>
                </span>
                <span class="app-brand-text demo footer-link fw-bold ms-2 ps-1">Vuexy</span>
              </a>
              <p class="footer-text footer-logo-description mb-6">
                Le tableau de bord administrateur le plus convivial et hautement personnalisable pour les développeurs.
              </p>
              <form class="footer-form">
                <label for="footer-email" class="small">Abonnez-vous à la newsletter</label>
                <div class="d-flex mt-1">
                  <input
                    type="email"
                    class="form-control rounded-0 rounded-start-bottom rounded-start-top"
                    id="footer-email"
                    placeholder="Votre email" />
                  <button type="submit" class="btn btn-primary shadow-none rounded-0 rounded-end-bottom rounded-end-top">
                    S'abonner
                  </button>
                </div>
              </form>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <h6 class="footer-title mb-6">Démos</h6>
              <ul class="list-unstyled">
                <li class="mb-4">
                  <a href="{{route('become.instructor')}}" target="_blank" class="footer-link">Inscription formateur</a>
                </li>
                <li class="mb-4">
                  <a href="../horizontal-menu-template/" target="_blank" class="footer-link">Disposition horizontale</a>
                </li>
                <li class="mb-4">
                  <a href="../vertical-menu-template-bordered/" target="_blank" class="footer-link">Disposition bordée</a>
                </li>
                <li class="mb-4">
                  <a href="../vertical-menu-template-semi-dark/" target="_blank" class="footer-link">Disposition semi-sombre</a>
                </li>
                <li class="mb-4">
                  <a href="../vertical-menu-template-dark/" target="_blank" class="footer-link">Disposition sombre</a>
                </li>
              </ul>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <h6 class="footer-title mb-6">Pages</h6>
              <ul class="list-unstyled">
                <li class="mb-4">
                  <a href="pricing-page.html" class="footer-link">Tarifs</a>
                </li>
                <li class="mb-4">
                  <a href="payment-page.html" class="footer-link">Paiement<span class="badge bg-primary ms-2">Nouveau</span></a>
                </li>
                <li class="mb-4">
                  <a href="checkout-page.html" class="footer-link">Validation de commande</a>
                </li>
                <li class="mb-4">
                  <a href="help-center-landing.html" class="footer-link">Centre d'aide</a>
                </li>
                <li class="mb-4">
                  <a href="../vertical-menu-template/auth-login-cover.html" target="_blank" class="footer-link">Connexion/Inscription</a>
                </li>
              </ul>
            </div>
            <div class="col-lg-3 col-md-4">
              <h6 class="footer-title mb-6">Téléchargez notre application</h6>
              <a href="javascript:void(0);" class="d-block mb-4">
                <img src="{{ asset('frontend/assets/img/front-pages/landing-page/apple-icon.png') }}" alt="icône Apple" />
              </a>
              <a href="javascript:void(0);" class="d-block">
                <img src="{{ asset('frontend/assets/img/front-pages/landing-page/google-play-icon.png') }}" alt="icône Google Play" />
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom py-3 py-md-5">
        <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
          <div class="mb-2 mb-md-0">
            <span class="footer-bottom-text">©
              <script>
                document.write(new Date().getFullYear());
              </script>
            </span>
            <a href="https://pixinvent.com" target="_blank" class="fw-medium text-white">Pixinvent,</a>
            <span class="footer-bottom-text"> créé avec ❤️ pour un web meilleur.</span>
          </div>
          <div>
            <a href="https://github.com/pixinvent" class="me-3" target="_blank">
              <img src="{{ asset('frontend/assets/img/front-pages/icons/github.svg') }}" alt="icône GitHub" />
            </a>
            <a href="https://www.facebook.com/pixinvents/" class="me-3" target="_blank">
              <img src="{{ asset('frontend/assets/img/front-pages/icons/facebook.svg') }}" alt="icône Facebook" />
            </a>
            <a href="https://twitter.com/pixinvents" class="me-3" target="_blank">
              <img src="{{ asset('frontend/assets/img/front-pages/icons/twitter.svg') }}" alt="icône Twitter" />
            </a>
            <a href="https://www.instagram.com/pixinvents/" target="_blank">
              <img src="{{ asset('frontend/assets/img/front-pages/icons/instagram.svg') }}" alt="icône Instagram" />
            </a>
          </div>
        </div>
      </div>

  </footer>
  <!-- Footer: End -->
