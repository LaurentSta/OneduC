<header id="app-header" class="fixed top-0 left-0 right-0 py-3 bg-white border-b border-gray-200 shadow-sm w-full z-50">
  <div class="flex items-center justify-between px-4 w-full">
    <div class="flex items-center gap-4">
      <button
          aria-controls="observateur-sidebar"
          type="button"
          @click="window.innerWidth >= 1024 ? (sidebarCollapsed = !sidebarCollapsed) : (sidebarOpen = !sidebarOpen)"
          :aria-expanded="(window.innerWidth >= 1024 ? !sidebarCollapsed : sidebarOpen).toString()"
          class="text-gray-600 hover:text-orangeone inline-flex">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
      </button>

      <div class="h-6 w-px bg-gray-300" aria-hidden="true"></div>

      <a href="{{ url('/') }}" class="inline-flex items-center">
        <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}" alt="Logo Oneduc" class="h-10 w-auto">
      </a>
    </div>

    <div class="flex items-center gap-3">
      <div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape.window="open = false">
        <button type="button"
                @click="open = !open"
                class="flex items-center gap-2 focus:outline-none"
                :aria-expanded="open.toString()"
                aria-haspopup="menu">
          <div class="w-[50px] h-[50px] rounded-full border border-gray-300 bg-bleuone/10 text-bleuone flex items-center justify-center font-bold uppercase">
            {{ strtoupper(substr((string) (Auth::user()->prenom ?? 'O'), 0, 1)) }}{{ strtoupper(substr((string) (Auth::user()->name ?? 'B'), 0, 1)) }}
          </div>
        </button>

        <div x-show="open"
             x-transition
             class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded shadow-md z-20"
             style="display: none;">
          <div class="p-4 border-b">
            <p class="text-sm font-semibold">{{ trim((Auth::user()->prenom ?? '').' '.(Auth::user()->name ?? '')) }}</p>
            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
          </div>
          <div class="px-4 py-3 text-sm text-gray-600">
            Profil lecture seule
          </div>
          <div class="border-t px-4 py-2">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="block w-full px-4 py-2 text-red-600 hover:bg-red-100">
                Déconnexion
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
