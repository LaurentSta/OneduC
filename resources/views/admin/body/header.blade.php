@php
    $adminUser = Auth::user();
    $notificationsDisponibles = \Illuminate\Support\Facades\Schema::hasTable('notifications');
    $nombreNotificationsNonLues = $notificationsDisponibles
        ? ($adminUser?->unreadNotifications()->count() ?? 0)
        : 0;
    $dernieresNotifications = $notificationsDisponibles
        ? ($adminUser?->notifications()->latest()->limit(8)->get() ?? collect())
        : collect();

    $nomAdministrateur = trim(($adminUser?->prenom ?? '').' '.($adminUser?->name ?? ''));
    $nomAdministrateur = $nomAdministrateur !== '' ? $nomAdministrateur : ($adminUser?->username ?? 'Administrateur');
    $photoAdministrateur = ! empty($adminUser?->photo)
        ? asset('upload/admin_images/'.$adminUser->photo)
        : asset('upload/admin_images/NoPhoto.png');

    $titreSection = match (true) {
        request()->routeIs('admin.utilisateurs.*'),
        request()->routeIs('admin.formateurs*'),
        request()->routeIs('admin.stagiaires.*'),
        request()->routeIs('admin.observateurs.*'),
        request()->routeIs('admin.groupes*') => 'Utilisateurs',
        request()->routeIs('admin.categories.*'),
        request()->routeIs('admin.subcategories.*'),
        request()->routeIs('admin.modules*'),
        request()->routeIs('admin.lectures.*'),
        request()->routeIs('admin.sections.*'),
        request()->routeIs('admin.quiz.*'),
        request()->routeIs('admin.evaluations.*'),
        request()->routeIs('admin.referentiels.*'),
        request()->routeIs('admin.competencies.*'),
        request()->routeIs('admin.badges.*') => 'Pédagogie',
        request()->routeIs('admin.pilotage.*'),
        request()->routeIs('admin.retours.*') => 'Pilotage',
        request()->routeIs('admin.nuage.*') => 'Outils',
        request()->routeIs('admin.profile'),
        request()->routeIs('admin.parametre'),
        request()->routeIs('admin.securite') => 'Mon compte',
        default => 'Vue d’ensemble',
    };

    $elementsMenuProfil = [
        [
            'label' => 'Mon profil',
            'href' => route('admin.profile'),
            'active' => request()->routeIs('admin.profile'),
            'icon' => 'ti-user-circle',
        ],
        [
            'label' => 'Préférences',
            'href' => route('admin.parametre'),
            'active' => request()->routeIs('admin.parametre'),
            'icon' => 'ti-settings',
        ],
        [
            'label' => 'Sécurité',
            'href' => route('admin.securite'),
            'active' => request()->routeIs('admin.securite'),
            'icon' => 'ti-shield-lock',
        ],
    ];
@endphp

<header class="admin-header fixed inset-x-0 top-0 z-50 h-14 border-b border-slate-200 bg-white">
    <div class="flex h-full items-center justify-between gap-3 px-3 sm:px-4">
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            <button
                type="button"
                x-on:click="basculerNavigation($event.currentTarget)"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-slate-600 transition hover:bg-slate-100 hover:text-bleuone focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                aria-controls="navigation-administrateur"
                :aria-expanded="(estMobile() ? navigationMobileOuverte : !navigationReduite).toString()"
                :aria-label="estMobile()
                    ? (navigationMobileOuverte ? 'Fermer la navigation' : 'Ouvrir la navigation')
                    : (navigationReduite ? 'Déplier la navigation' : 'Replier la navigation')"
            >
                <i class="ti ti-menu-2 text-xl" aria-hidden="true"></i>
            </button>

            <a
                href="{{ route('admin.dashboard') }}"
                class="flex shrink-0 items-center gap-2 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                aria-label="Administration Oneduc — Accueil"
            >
                <img
                    src="{{ asset('backend/assets/img/logos/LOGOOneducSVG.svg') }}"
                    alt=""
                    class="h-8 w-auto"
                >
                <span class="hidden text-sm font-bold tracking-tight text-slate-900 sm:inline">Oneduc</span>
            </a>

            <div class="hidden h-5 w-px bg-slate-200 sm:block" aria-hidden="true"></div>
            <p class="hidden truncate text-sm font-medium text-slate-500 sm:block">
                Administration
                <span class="mx-1 text-slate-300" aria-hidden="true">/</span>
                <span class="text-slate-700">{{ $titreSection }}</span>
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
            <a
                href="{{ route('admin.utilisateurs.create') }}"
                class="hidden h-9 items-center gap-2 rounded-md bg-orangeone px-3 text-sm font-semibold text-white transition hover:bg-orangeone-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone md:inline-flex"
            >
                <i class="ti ti-user-plus text-base" aria-hidden="true"></i>
                Ajouter un utilisateur
            </a>

            <div
                x-data="{ notificationsOuvertes: false }"
                class="relative"
                x-on:click.outside="notificationsOuvertes = false"
                x-on:keydown.escape.window="notificationsOuvertes = false"
            >
                <button
                    type="button"
                    x-on:click="notificationsOuvertes = !notificationsOuvertes"
                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-slate-600 transition hover:bg-slate-100 hover:text-bleuone focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                    :aria-expanded="notificationsOuvertes.toString()"
                    aria-controls="panneau-notifications-administrateur"
                    aria-label="Notifications{{ $nombreNotificationsNonLues > 0 ? ' — '.$nombreNotificationsNonLues.' non lue(s)' : '' }}"
                >
                    <i class="ti ti-bell text-xl" aria-hidden="true"></i>
                    @if ($nombreNotificationsNonLues > 0)
                        <span class="absolute right-0.5 top-0.5 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white">
                            {{ $nombreNotificationsNonLues > 99 ? '99+' : $nombreNotificationsNonLues }}
                        </span>
                    @endif
                </button>

                <section
                    id="panneau-notifications-administrateur"
                    x-cloak
                    x-show="notificationsOuvertes"
                    x-transition.origin.top.right.duration.150ms
                    class="admin-elevation absolute right-0 mt-2 w-[min(24rem,calc(100vw-1.5rem))] overflow-hidden rounded-lg border border-slate-200 bg-white"
                    aria-label="Notifications récentes"
                >
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-3 py-2.5">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Notifications</h2>
                            <p class="text-xs text-slate-500">{{ $nombreNotificationsNonLues }} non lue(s)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('admin.pilotage.notifications.index') }}"
                                class="rounded text-xs font-semibold text-bleuone hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                            >
                                Tout afficher
                            </a>
                            @if ($nombreNotificationsNonLues > 0)
                                <form method="POST" action="{{ route('admin.pilotage.notifications.read-all') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded text-xs font-semibold text-slate-600 hover:text-orangeone focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                                    >
                                        Tout lire
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="max-h-80 overflow-y-auto overscroll-contain">
                        @forelse ($dernieresNotifications as $notification)
                            @php
                                $urlNotification = data_get($notification->data, 'url', route('admin.pilotage.notifications.index'));
                                $titreNotification = data_get($notification->data, 'title', 'Notification');
                                $messageNotification = data_get($notification->data, 'message', '');
                            @endphp
                            <article class="border-b border-slate-100 px-3 py-2.5 last:border-b-0 {{ $notification->read_at ? 'bg-white' : 'bg-sky-50/70' }}">
                                <a
                                    href="{{ $urlNotification }}"
                                    class="block rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                                >
                                    <div class="flex items-start gap-2">
                                        @if (is_null($notification->read_at))
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-orangeone" aria-label="Non lue"></span>
                                        @else
                                            <span class="mt-1.5 h-2 w-2 shrink-0" aria-hidden="true"></span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-slate-900">{{ $titreNotification }}</span>
                                            @if ($messageNotification !== '')
                                                <span class="mt-0.5 block text-xs leading-5 text-slate-600">{{ str($messageNotification)->limit(110) }}</span>
                                            @endif
                                            <span class="mt-1 block text-[11px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                        </span>
                                    </div>
                                </a>
                                @if (is_null($notification->read_at))
                                    <form method="POST" action="{{ route('admin.pilotage.notifications.read', $notification->id) }}" class="mt-1 pl-4">
                                        @csrf
                                        <button type="submit" class="rounded text-xs font-medium text-bleuone hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone">
                                            Marquer comme lue
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="px-4 py-8 text-center">
                                <i class="ti ti-bell-off text-2xl text-slate-300" aria-hidden="true"></i>
                                <p class="mt-2 text-sm text-slate-500">Aucune notification.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div
                x-data="{ profilOuvert: false }"
                class="relative"
                x-on:click.outside="profilOuvert = false"
                x-on:keydown.escape.window="profilOuvert = false"
            >
                <button
                    type="button"
                    x-on:click="profilOuvert = !profilOuvert"
                    class="flex h-10 items-center gap-2 rounded-md px-1.5 text-left transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orangeone"
                    :aria-expanded="profilOuvert.toString()"
                    aria-controls="menu-profil-administrateur"
                    aria-label="Ouvrir le menu du compte"
                >
                    <img
                        src="{{ $photoAdministrateur }}"
                        alt=""
                        class="h-8 w-8 rounded-full border border-slate-200 object-cover"
                    >
                    <span class="hidden max-w-36 min-w-0 lg:block">
                        <span class="block truncate text-xs font-semibold text-slate-900">{{ $nomAdministrateur }}</span>
                        <span class="block text-[11px] text-slate-500">Administrateur</span>
                    </span>
                    <i class="ti ti-chevron-down hidden text-sm text-slate-400 lg:block" aria-hidden="true"></i>
                </button>

                <div
                    id="menu-profil-administrateur"
                    x-cloak
                    x-show="profilOuvert"
                    x-transition.origin.top.right.duration.150ms
                    class="admin-elevation absolute right-0 mt-2 w-64 overflow-hidden rounded-lg border border-slate-200 bg-white"
                >
                    <div class="border-b border-slate-200 px-3 py-3">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $nomAdministrateur }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $adminUser?->email }}</p>
                    </div>

                    <nav class="p-1.5" aria-label="Compte administrateur">
                        @foreach ($elementsMenuProfil as $element)
                            <a
                                href="{{ $element['href'] }}"
                                class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm transition {{ $element['active'] ? 'bg-bleuone/10 font-semibold text-bleuone' : 'text-slate-700 hover:bg-slate-100 hover:text-bleuone' }}"
                                @if ($element['active']) aria-current="page" @endif
                            >
                                <i class="ti {{ $element['icon'] }} text-lg" aria-hidden="true"></i>
                                {{ $element['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="border-t border-slate-200 p-1.5">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-left text-sm font-medium text-red-700 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                            >
                                <i class="ti ti-logout text-lg" aria-hidden="true"></i>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
