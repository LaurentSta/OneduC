<header id="app-header" class="w-full bg-white shadow px-6 py-4">
  <div class="flex items-center gap-4 justify-between">
    <!-- Gauche : bouton + titre -->
    <div class="flex items-center gap-3">
      <button type="button"
              @click="$dispatch('toggle-sidebar')"
              aria-controls="module-sidebar-wrapper"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-gray-700
                     hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orangeone">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/>
        </svg>
        <span class="font-varela text-sm">Menu</span>
      </button>
      <!-- Logo -->
      <div class="flex items-center mb-4 md:mb-0">
        <a href="{{ route('index') }}">
          <img src="/frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg"
               alt="Logo Onéduc"
               class="h-[60px] w-auto">
        </a>
      </div>
    </div>

    <div class="flex items-center gap-3">
      @include('components.user-notification-bell')
      <a href="{{ route('formateur.dashboard') }}" class="text-sm text-orangeone hover:underline">
        Retour au tableau de bord
      </a>
    </div>
  </div>
</header>
