<!-- NAVBAR AVEC SOUS-MENU CLIQUABLE -->
 <!--/var/www/Oneduc_Prod/resources/views/frontend/body/header.blade.php-->
<nav class="bg-white border-b shadow-sm" style="border-color: #e7eef3;">
    <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center">

      <!-- Logo -->
      <div class="flex items-center mb-4 md:mb-0">
        <a href="{{ route('index') }}">
          <img
              src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}"
              alt="Logo Onéduc"
              class="h-[72px] md:h-[84px] w-auto"
            />
        </a>
      </div>

      <!-- Menu -->
      <div class="flex flex-wrap justify-center md:justify-end items-center gap-10 text-base font-varela text-gray-700">

        <a href="{{ route('index') }}" class="px-2 hover:text-orangeone transition">Accueil</a>
        <a href="{{ route('categories.all') }}" class="px-2 hover:text-orangeone transition">Formations</a>

        <!-- Association avec sous-menu -->
        <div
          x-data="{
            open: false,
            toggle() {
              this.open = !this.open;
            },
            close() {
              this.open = false;
            }
          }"
          x-ref="associationMenu"
          class="relative"
          @keydown.escape.window="close()"
          @click.window="if (open && !$refs.associationMenu.contains($event.target)) close()"
          @focusin.window="if (open && !$refs.associationMenu.contains($event.target)) close()"
        >
          <button
            type="button"
            x-ref="associationTrigger"
            @click="toggle()"
            :aria-expanded="open.toString()"
            aria-haspopup="true"
            class="relative z-50 px-2 flex items-center gap-1 hover:text-orangeone transition focus:outline-none"
          >
            Association
            <!-- Icône flèche -->
            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Sous-menu -->
          <ul
            x-cloak
            x-show="open"
            @click.outside="close()"
            x-transition:enter="transition ease-out duration-180"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
            class="absolute left-0 mt-2 bg-white text-gray-900 shadow-md rounded-md py-2 w-48 z-50"
          >
            <li>
              <a href="{{ route('association') }}" @click="close()" class="block px-4 py-2 hover:bg-orangeone hover:text-white transition">Associations</a>
            </li>
            <li>
              <a href="{{ route('adhesion') }}" @click="close()" class="block px-4 py-2 hover:bg-orangeone hover:text-white transition">Adhésions</a>
            </li>
            <li>
                <a href="{{ route('contact') }}" @click="close()" class="block px-4 py-2 hover:bg-orangeone hover:text-white transition">Contactez-nous</a>


            </li>
          </ul>
        </div>

        {{-- Accessibilité : FALC et réglage de la taille du texte --}}
        <div
          x-data="{
            open: false,
            toggle() {
              this.open = !this.open;
            },
            close() {
              this.open = false;
            }
          }"
          x-ref="accessibilityMenu"
          class="relative"
          @keydown.escape.window="close()"
          @click.window="if (open && !$refs.accessibilityMenu.contains($event.target)) close()"
          @focusin.window="if (open && !$refs.accessibilityMenu.contains($event.target)) close()"
        >
          <button
            type="button"
            @click="toggle()"
            :aria-expanded="open.toString()"
            aria-haspopup="true"
            class="inline-flex items-center justify-center rounded-full border border-bleuone/20 bg-white w-10 h-10 text-bleuone transition hover:border-orangeone hover:text-orangeone focus:outline-none focus:ring-2 focus:ring-bleuone focus:ring-offset-2"
            aria-label="Ouvrir les options d'accessibilité"
          >
            <span class="text-lg font-bold leading-none select-none" style="font-family: 'OpenDyslexic', serif;">Aa</span>
          </button>

          <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-180"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
            class="absolute right-0 z-50 mt-3 w-72 rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-xl shadow-bleuone/10 max-h-[85vh] overflow-y-auto"
          >
            <p class="text-sm font-semibold text-bleuone">Options d'accessibilité</p>
            <button
              type="button"
              @click="close(); document.dispatchEvent(new CustomEvent('open-falc'))"
              class="mt-3 flex w-full items-center justify-between rounded-xl border border-orangeone/20 bg-orangeone/10 px-4 py-3 text-left font-varela text-sm font-semibold text-orangeone transition hover:border-orangeone hover:bg-orangeone hover:text-white focus:outline-none focus:ring-2 focus:ring-orangeone"
            >
              <span>Lire en FALC</span>
              <span class="text-xs font-bold uppercase">FALC</span>
            </button>

            <div class="mt-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Taille du texte</p>
              <div class="grid grid-cols-3 gap-2" role="group" aria-label="Taille du texte">
                <button type="button" data-text-size="normal" onclick="setTextSize('normal')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Taille de texte normale">A</button>
                <button type="button" data-text-size="large" onclick="setTextSize('large')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Texte agrandi">A+</button>
                <button type="button" data-text-size="xlarge" onclick="setTextSize('xlarge')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-base font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Texte très agrandi">A++</button>
              </div>
            </div>

            <div class="mt-4 border-t border-slate-100 pt-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Police de lecture</p>
              <button
                type="button"
                data-dyslexic-toggle
                onclick="toggleDyslexicFont()"
                class="flex w-full items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-left font-varela text-sm font-semibold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                aria-pressed="false"
              >
                <span>OpenDyslexic</span>
                <span data-dyslexic-status class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">Off</span>
              </button>
            </div>

            {{-- Interlignage --}}
            <div class="mt-4 border-t border-slate-100 pt-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Interlignage</p>
              <div class="grid grid-cols-3 gap-2" role="group" aria-label="Interlignage">
                <button type="button" data-line-height="normal" onclick="setLineHeight('normal')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Interlignage normal">Normal</button>
                <button type="button" data-line-height="large" onclick="setLineHeight('large')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Interlignage large">Large</button>
                <button type="button" data-line-height="xlarge" onclick="setLineHeight('xlarge')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Interlignage très large">Max</button>
              </div>
            </div>

            {{-- Espacement des lettres --}}
            <div class="mt-4 border-t border-slate-100 pt-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Espacement</p>
              <div class="grid grid-cols-3 gap-2" role="group" aria-label="Espacement des lettres">
                <button type="button" data-letter-spacing="normal" onclick="setLetterSpacing('normal')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Espacement normal">Normal</button>
                <button type="button" data-letter-spacing="large" onclick="setLetterSpacing('large')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Espacement large">Large</button>
                <button type="button" data-letter-spacing="xlarge" onclick="setLetterSpacing('xlarge')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone"
                  aria-label="Espacement très large">Max</button>
              </div>
            </div>

            {{-- Contraste --}}
            <div class="mt-4 border-t border-slate-100 pt-4">
              <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Contraste</p>
              <div class="grid grid-cols-2 gap-2" role="group" aria-label="Mode de contraste">
                <button type="button" data-contrast="normal" onclick="setContrast('normal')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone">
                  Normal
                </button>
                <button type="button" data-contrast="dark" onclick="setContrast('dark')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone">
                  Sombre
                </button>
                <button type="button" data-contrast="sepia" onclick="setContrast('sepia')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone">
                  Sépia
                </button>
                <button type="button" data-contrast="high" onclick="setContrast('high')"
                  class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-bleuone transition hover:border-bleuone hover:bg-bleuone/5 focus:outline-none focus:ring-2 focus:ring-bleuone">
                  Contraste ++
                </button>
              </div>
            </div>

            {{-- Réinitialiser --}}
            <div class="mt-4 border-t border-slate-100 pt-4">
              <button type="button" onclick="resetA11y()"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-500 transition hover:border-orangeone hover:text-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4.5 9A8.5 8.5 0 0119.5 15M19.5 15A8.5 8.5 0 014.5 9" />
                </svg>
                Réinitialiser
              </button>
            </div>
          </div>
        </div>

        @auth
          @php
              $role = Auth::user()->role;
              $dashboardRoute = match ($role) {
                  'admin' => route('admin.dashboard'),
                  'formateur' => route('formateur.dashboard'),
                  'observateur' => route('observateur.dashboard'),
                  'stagiaire' => route('stagiaire.dashboard'),
                  default => '#',
              };
          @endphp
          <a href="{{ $dashboardRoute }}" class="btn-oneduc flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Tableau de bord
          </a>
        @else
            {{-- On pointe vers la nouvelle page de choix --}}
            <a href="{{ route('login.selection') }}" class="btn-oneduc flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Connexion
            </a>
          @endauth

      </div>
    </div>
  </nav>
