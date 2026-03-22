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
        <div x-data="{ open: false }" class="relative">
          <button
            @click="open = !open"
            class="px-2 flex items-center gap-1 hover:text-orangeone transition focus:outline-none"
          >
            Association
            <!-- Icône flèche -->
            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Sous-menu -->
          <ul
            x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-180"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
            class="absolute left-0 mt-2 bg-white text-gray-900 shadow-md rounded-md py-2 w-48 z-50"
          >
            <li>
              <a href="{{ route('association') }}" class="block px-4 py-2 hover:bg-orangeone hover:text-white transition">Associations</a>
            </li>
            <li>
              <a href="{{ route('adhesion') }}" class="block px-4 py-2 hover:bg-orangeone hover:text-white transition">Adhésions</a>
            </li>
            <li>
                <a href="{{ route('contact') }}" class="block px-4 py-2 hover:bg-orangeone hover:text-white transition">Contactez-nous</a>


            </li>
          </ul>
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
