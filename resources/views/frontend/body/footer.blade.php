<footer class="bg-orangeone text-white pt-12 pb-8 font-lisible">
    <div class="max-w-[1248px] mx-auto px-4">

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">

        <!-- Bloc 1 : Présentation -->
        <!-- Bloc 1 : Présentation -->
<div class="space-y-4 flex flex-col items-center">
    <!-- Logo -->
    <div>
        <img src="{{ asset('frontend/assets/img/front-pages/landing-page/LogoBlanc.svg') }}"
             alt="Logo Oneduc" class="h-32 w-auto">
    </div>

    <!-- Réseaux sociaux -->
    <div class="flex space-x-4">
        <!-- Email -->
        <a href="mailto:contact@oneduc.fr" class="group" aria-label="Email">
            <div class="w-10 h-10 rounded-full border border-white flex items-center justify-center transition hover:bg-white">
                <svg class="h-5 w-5 text-white group-hover:text-[#004461]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path d="M3 5h18a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2z"/>
      <path d="M3 7l9 6l9-6"/>
    </svg>
            </div>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/profile.php?id=100080367780798" target="_blank" class="group" aria-label="Facebook">
            <div class="w-10 h-10 rounded-full border border-white flex items-center justify-center transition hover:bg-white">
                <svg class="h-5 w-5 text-white group-hover:text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                </svg>
            </div>
        </a>

        <!-- YouTube -->
        <a href="https://www.youtube.com/@Oneduc_fr/" target="_blank" class="group" aria-label="YouTube">
            <div class="w-10 h-10 rounded-full border border-white flex items-center justify-center transition hover:bg-white">
                <svg class="h-5 w-5 text-white group-hover:text-[#FF0000]" fill="currentColor" viewBox="0 0 24 24">
      <path fill-rule="evenodd" clip-rule="evenodd"
            d="M21.8 8s-.2-1.4-.8-2c-.6-.6-1.3-.8-2.7-.9C16 5 12 5 12 5s-4 0-6.3.1c-1.4.1-2.1.3-2.7.9-.6.6-.8 2-.8 2S2 9.6 2 11.2v1.6C2 14.4 2.2 16 2.2 16s.2 1.4.8 2c.6.6 1.3.8 2.7.9 2.3.2 6.3.2 6.3.2s4 0 6.3-.2c1.4-.1 2.1-.3 2.7-.9.6-.6.8-2 .8-2s.2-1.6.2-3.2V11.2c0-1.6-.2-3.2-.2-3.2zM10 15V9l5 3-5 3z"
            fill="currentColor" />
    </svg>
            </div>
        </a>

        <!-- LinkedIn -->
        <a href="https://www.linkedin.com" target="_blank" class="group" aria-label="LinkedIn">
            <div class="w-10 h-10 rounded-full border border-white flex items-center justify-center transition hover:bg-white">
                <svg class="h-5 w-5 text-white group-hover:text-[#0077B5]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>
                </svg>
            </div>
        </a>
    </div>
</div>


        <!-- Bloc 2 : Liens rapides -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold font-varela">Liens utiles</h3>
          <ul class="space-y-2">

            <li><a href="{{ route('formateur.inscription.form') }}" class="text-white hover:text-white/80 transition underline-offset-2 hover:underline">Devenir formateur</a>
            </li>
            <li><a href="{{ route('association') }}" class="text-white hover:text-white/80 transition underline-offset-2 hover:underline">À propos</a></li>
            <li><a href="{{ route('charte-graphique') }}" class="text-white hover:text-white/80 transition underline-offset-2 hover:underline">Charte graphique</a></li>
            <li><a href="{{ route('categories.all') }}" class="text-white hover:text-white/80 transition underline-offset-2 hover:underline">Formations</a></li>
            <li><a href="{{ route('contact') }}" class="text-white hover:text-white/80 transition underline-offset-2 hover:underline">Contact</a></li>
          </ul>
        </div>

        <!-- Bloc 3 : Services -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold font-varela">Fonctionnalités</h3>
          <ul class="space-y-2">
            {{-- Tooltip explicatif : le code d'accès est peu connu des nouveaux visiteurs --}}
            <li class="relative" x-data="{ tip: false }">
              <a href="{{ route('stagiaire.code.form') }}" class="text-white hover:text-white/80 transition underline-offset-2 hover:underline inline-flex items-center gap-1.5">
                Code d'accès stagiaire
              </a>
              <button type="button"
                @mouseenter="tip=true" @mouseleave="tip=false"
                @focus="tip=true"      @blur="tip=false"
                class="ml-1 w-4 h-4 rounded-full border border-white/60 text-white/80 text-xs leading-none inline-flex items-center justify-center hover:border-white hover:text-white transition"
                aria-describedby="tooltip-code-acces"
                aria-label="En savoir plus sur le code d'accès stagiaire">?</button>
              <span id="tooltip-code-acces" role="tooltip" x-show="tip" x-transition
                class="absolute bottom-full left-0 mb-2 w-64 bg-bleuone text-white text-sm rounded-lg p-3 shadow-lg z-10 font-lisible leading-relaxed pointer-events-none">
                Le code d'accès est donné par votre formateur. Il vous permet de rejoindre une formation sans créer de compte.
              </span>
            </li>



          </ul>
        </div>


        <!-- Bloc 4 : Contact -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold font-varela">Nous contacter</h3>
          <address class="not-italic text-white space-y-2">
            <p>Association Oneduc</p>
            {{-- Numéro de téléphone supprimé (était fictif : 01 23 45 67 89) --}}
            <p>
              <a href="mailto:contact@oneduc.fr" class="hover:underline underline-offset-2">
                contact@oneduc.fr
              </a>
            </p>
          </address>
        </div>

      </div>

      {{-- WCAG AA : text-white/70 → text-white/90 pour améliorer le contraste sur fond orange --}}
      <div class="border-t border-white/30 pt-6 flex flex-col md:flex-row justify-between items-center text-sm text-white/90">
        <p class="mb-4 md:mb-0">© 2025 Oneduc. Tous droits réservés.</p>
        <div class="flex flex-wrap gap-x-6 gap-y-2">
          <a href="{{ route('mentions-legales') }}" class="hover:text-white underline underline-offset-2 transition">Mentions légales</a>
          <a href="{{ route('confidentialite') }}" class="hover:text-white underline underline-offset-2 transition">Politique de confidentialité</a>
          <a href="{{ route('conditions-utilisation') }}" class="hover:text-white underline underline-offset-2 transition">Conditions d'utilisation</a>
          <a href="{{ route('cookies') }}" class="hover:text-white underline underline-offset-2 transition">Cookies</a>
        </div>
      </div>
    </div>
  </footer>
