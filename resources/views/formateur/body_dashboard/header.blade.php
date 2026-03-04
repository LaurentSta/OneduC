
<header id="app-header" class="fixed top-0 left-0 right-0 py-3 bg-white border-b border-gray-200 shadow-sm w-full z-50">
    <div class="flex items-center justify-between px-4 w-full">

      <!-- Burger menu + brand toggle -->
      <div class="flex items-center gap-4">
        <!-- Toggler Burger toujours visible avec icône Heroicons -->
        <!-- Burger menu -->
        <button
            data-drawer-target="formateur-sidebar"
            data-drawer-toggle="formateur-sidebar"
            aria-controls="formateur-sidebar"
            type="button"
            @click="sidebarOpen = !sidebarOpen"
            class="text-gray-600 hover:text-orangeone inline-flex lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Logo juste à droite du burger -->
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
        <img src="{{ asset('backend/assets/img/logos/LOGOOneducSVG.svg') }}" alt="Logo Oneduc" class="h-10">
        <span class="font-bold text-lg text-gray-800">Oneduc.fr</span>
    </a>
      </div>

      <div class="flex items-center gap-4">

        @include('components.user-notification-bell')

        <!-- Fullscreen button (non-fonctionnel pour l'instant) -->
        <button class="text-gray-600 hover:text-orangeone text-2xl hidden lg:inline-block">
          <i class="la la-expand-arrows-alt"></i>
        </button>

        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape.window="open = false">
          <button type="button"
                  @click="open = !open"
                  class="flex items-center gap-2 focus:outline-none"
                  :aria-expanded="open.toString()"
                  aria-haspopup="menu">
            <img src="{{ !empty($profileData->photo) ? asset('upload/formateur_images/' . $profileData->photo) . '?v=' . time() : asset('upload/NoPhoto.png') }}"
     alt="avatar"
     class="w-[50px] h-[50px] rounded-full border border-gray-300 cursor-pointer" />

          </button>

          <div x-show="open"
               x-transition
               class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded shadow-md z-20"
               style="display: none;">
            <div class="p-4 border-b">
              <p class="text-sm font-semibold">{{ Auth::user()->username }}</p>
              <p class="text-xs text-gray-500">{{ Auth::user()->name }}</p>
            </div>
            <a href="{{ route('formateur.profile') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Mon profil</a>
            <a href="{{ route('formateur.parametre') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Paramètres</a>
            <a href="{{ route('formateur.securite.show') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Sécurité</a>

            <div class="border-t px-4 py-2">
              <form method="POST" action="{{ route('logout') }}" x-data>
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
