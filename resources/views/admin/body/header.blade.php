<!-- /home/laurents/Oneduc_Dev/resources/views/admin/body/header.blade.php -->
@php
  $adminUser = Auth::user();
  $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
  $unreadCount = $hasNotificationsTable ? ($adminUser?->unreadNotifications()->count() ?? 0) : 0;
  $latestNotifications = $hasNotificationsTable ? ($adminUser?->notifications()->latest()->limit(8)->get() ?? collect()) : collect();
@endphp
<header class="fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 shadow-sm z-50">
    <div class="flex h-16 items-center justify-between px-4 w-full">

      <!-- Burger menu + brand toggle -->
      <div class="flex items-center gap-4">
        <!-- Toggler Burger toujours visible avec icône Heroicons -->
        <!-- Burger menu -->
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-orangeone">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="h-6 w-px bg-gray-300" aria-hidden="true"></div>

        <!-- Logo juste à droite du burger -->
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
        <img src="{{ asset('backend/assets/img/logos/LOGOOneducSVG.svg') }}" alt="Logo Oneduc" class="h-10 shrink-0 origin-left scale-110 transform">
        <span class="font-bold text-lg text-gray-800">Oneduc.fr</span>
    </a>
      </div>

      <div class="flex items-center gap-4">

        <!-- Notifications -->
        <div x-data="{ openNotif: false }"
             class="relative"
             @click.outside="openNotif = false"
             @keydown.escape.window="openNotif = false">
          <button @click="openNotif = !openNotif"
                  class="text-gray-600 hover:text-orangeone relative"
                  :aria-expanded="openNotif.toString()"
                  aria-haspopup="menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-[34px] h-[34px]">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.099A3.001 3.001 0 0112 18a3.001 3.001 0 01-2.857-0.901M6 8c0-3.314 2.239-6 5-6s5 2.686 5 6c0 5.25 2 6 2 6H4s2-0.75 2-6z" />
            </svg>
            @if($unreadCount > 0)
              <span class="absolute top-[8px] right-0 translate-x-1/2 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
              </span>
            @endif
          </button>

          <div x-show="openNotif"
               x-transition
               class="absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-lg z-30"
               style="display: none;">
            <div class="flex items-center justify-between px-4 py-3 border-b">
              <p class="text-sm font-semibold text-gray-800">Notifications</p>
              <div class="flex items-center gap-2">
                <a href="{{ route('admin.pilotage.notifications.index') }}" class="text-xs text-blue-700 hover:underline">Centre</a>
                <form method="POST" action="{{ route('admin.pilotage.notifications.read-all') }}">
                  @csrf
                  <button type="submit" class="text-xs text-gray-600 hover:text-orangeone">Tout lire</button>
                </form>
              </div>
            </div>
            <div class="max-h-96 overflow-y-auto">
              @forelse($latestNotifications as $notification)
                @php
                  $notificationUrl = data_get($notification->data, 'url', route('admin.pilotage.notifications.index'));
                  $title = data_get($notification->data, 'title', 'Notification');
                  $message = data_get($notification->data, 'message', '');
                @endphp
                <div class="px-4 py-3 border-b border-gray-100 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/40' }}">
                  <a href="{{ $notificationUrl }}" class="block">
                    <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
                    <p class="mt-1 text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($message, 90) }}</p>
                    <p class="mt-1 text-[11px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
                  </a>
                  @if(is_null($notification->read_at))
                    <form method="POST" action="{{ route('admin.pilotage.notifications.read', $notification->id) }}" class="mt-2">
                      @csrf
                      <button type="submit" class="text-xs text-blue-700 hover:underline">Marquer comme lu</button>
                    </form>
                  @endif
                </div>
              @empty
                <p class="px-4 py-6 text-sm text-gray-500">Aucune notification.</p>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Fullscreen button (non-fonctionnel pour l'instant) -->
        <button class="text-gray-600 hover:text-orangeone text-2xl hidden lg:inline-block">
          <i class="la la-expand-arrows-alt"></i>
        </button>

        @php
          $headerProfileMenuItems = [
            [
              'label' => 'Mon profil',
              'href' => route('admin.profile'),
              'active' => request()->routeIs('admin.profile'),
              'icon' => 'profile',
            ],
            [
              'label' => 'Paramètres',
              'href' => route('admin.parametre'),
              'active' => request()->routeIs('admin.parametre'),
              'icon' => 'settings',
            ],
            [
              'label' => 'Sécurité',
              'href' => route('admin.securite'),
              'active' => request()->routeIs('admin.securite'),
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
            <img src="{{ !empty($profileData->photo) ? asset('upload/admin_images/'.$profileData->photo) : asset('upload/admin_images/NoPhoto.png') }}"
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
