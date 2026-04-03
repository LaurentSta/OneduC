
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
        <a href="{{ url('/') }}" class="inline-flex items-center">
          <img src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}" alt="Logo Oneduc" class="h-10 w-auto shrink-0 origin-left scale-110 transform">
        </a>
      </div>

      <div class="flex items-center gap-3">

        <form action="{{ route('formateur.objectifs.index') }}"
              method="GET"
              class="hidden md:flex items-center">
          <label for="formateur-objectives-search" class="sr-only">Rechercher un objectif</label>
          <div class="flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-2 shadow-sm transition focus-within:border-orangeone focus-within:bg-white">
            <input id="formateur-objectives-search"
                   name="search"
                   type="search"
                   value="{{ request()->routeIs('formateur.objectifs.index') ? request('search') : '' }}"
                   placeholder="Mots-cles objectif"
                   class="w-44 lg:w-56 border-0 bg-transparent p-0 text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-0">
          </div>
        </form>

        @include('components.user-notification-bell')

        <!-- Fullscreen button (non-fonctionnel pour l'instant) -->
        <button class="text-gray-600 hover:text-orangeone text-2xl hidden lg:inline-block">
          <i class="la la-expand-arrows-alt"></i>
        </button>

        @php
          $headerProfileMenuItems = [
            [
              'label' => 'Mon profil',
              'href' => route('formateur.profile'),
              'active' => request()->routeIs('formateur.profile'),
              'icon' => 'profile',
            ],
            [
              'label' => 'Paramètres',
              'href' => route('formateur.parametre'),
              'active' => request()->routeIs('formateur.parametre'),
              'icon' => 'settings',
            ],
            [
              'label' => 'Sécurité',
              'href' => route('formateur.securite.show'),
              'active' => request()->routeIs('formateur.securite') || request()->routeIs('formateur.securite.show'),
              'icon' => 'security',
            ],
          ];
        @endphp

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
               class="absolute right-0 mt-2 w-64 rounded-[20px] border border-gray-200 bg-white shadow-md z-20"
               style="display: none;">
            <div class="border-b p-4">
              <p class="text-sm font-semibold">{{ Auth::user()->username }}</p>
              <p class="text-xs text-gray-500">{{ Auth::user()->name }}</p>
            </div>
            <div class="space-y-2 p-3">
              @foreach ($headerProfileMenuItems as $item)
                <a
                  href="{{ $item['href'] }}"
                  class="group flex items-center gap-3 rounded-[16px] border px-3 py-3 text-sm transition {{ $item['active'] ? 'border-orangeone/20 bg-orangeone/10 text-orangeone' : 'border-slate-200 bg-white text-slate-600 hover:border-bleuone/20 hover:bg-bleuone/5 hover:text-bleuone' }}"
                  @if($item['active']) aria-current="page" @endif
                >
                  <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full transition {{ $item['active'] ? 'bg-orangeone text-white' : 'bg-bleuone/10 text-bleuone group-hover:bg-bleuone group-hover:text-white' }}">
                    @if($item['icon'] === 'profile')
                      <x-icons.profile-iconify class="h-4 w-4" />
                    @elseif($item['icon'] === 'settings')
                      <x-icons.settings-iconify class="h-4 w-4" />
                    @else
                      <x-icons.security-iconify class="h-4 w-4" />
                    @endif
                  </span>

                  <span class="font-varela {{ $item['active'] ? 'font-semibold' : 'font-medium' }}">
                    {{ $item['label'] }}
                  </span>
                </a>
              @endforeach
            </div>

            <div class="border-t px-3 py-3">
              <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf
                <button type="submit" class="block w-full rounded-[14px] px-4 py-2 text-left text-red-600 transition hover:bg-red-50">
                  Déconnexion
                </button>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
  </header>
