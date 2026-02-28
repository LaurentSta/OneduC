{{-- /home/laurents/Oneduc_Dev/resources/views/admin/body/sidebar.blade.php --}}

@php
    $headerHeight = '4rem'; // 64px (aligné sur le header fixed h-16)
    $sidebarWidth = '10rem'; // 160px
@endphp

<aside
    x-show="sidebarOpen"
    x-transition:enter="transition transform duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition transform duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed left-0 z-40 hidden lg:flex flex-col bg-[#004461] text-white shadow-lg"
    style="top: {{ $headerHeight }}; height: calc(100vh - {{ $headerHeight }}); width: {{ $sidebarWidth }};"
    aria-label="Navigation administrateur"
>
    <div class="flex-1 overflow-y-auto">
        <a
            href="{{ route('admin.dashboard') }}"
            class="flex items-center justify-center h-16 bg-[#00374F] text-xl font-bold tracking-wide uppercase hover:bg-orangeone transition"
        >
            Admin
        </a>

        <nav class="px-2 py-4 text-xs space-y-3 text-center" aria-label="Menu principal">
            @php
                $itemBase   = 'block px-4 py-2 rounded transition';
                $itemHover  = 'hover:bg-orangeone';
                $itemActive = 'bg-orangeone';
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.dashboard') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Tableau de bord</span>
            </a>

            <a href="{{ route('admin.pilotage.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.pilotage.index') || request()->routeIs('admin.pilotage.tasks.*') || request()->routeIs('admin.pilotage.projects.*') || request()->routeIs('admin.pilotage.notifications.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Pilotage</span>
            </a>

            <a href="{{ route('admin.pilotage.journal') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.pilotage.journal') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Journal</span>
            </a>

            <hr class="border-white/20 border-t w-3/4 mx-auto">

            <a href="{{ route('admin.categories.all') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.subcategories.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Catégories</span>
            </a>

            <a href="{{ route('admin.modules') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.modules*') || request()->routeIs('admin.lectures.*') || request()->routeIs('admin.sections.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Modules</span>
            </a>

            {{-- Compétences (AJOUT) --}}
            <a href="{{ route('admin.competencies.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.competencies.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Compétences</span>
            </a>

            {{-- Badges (AJOUT) --}}
            <a href="{{ route('admin.badges.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.badges.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Badges</span>
            </a>

            <hr class="border-white/20 border-t w-3/4 mx-auto">

            <a href="{{ route('admin.formateurs') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.formateurs*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Formateurs</span>
            </a>

            <a href="{{ route('admin.groupes') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.groupes*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Groupes</span>
            </a>

            <a href="{{ route('admin.stagiaires.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.stagiaires.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Stagiaires</span>
            </a>

            <a href="{{ route('admin.evaluations.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.evaluations.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Évaluation</span>
            </a>

            <a href="{{ route('admin.referentiels.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.referentiels.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Référentiels</span>
            </a>

            <a href="{{ route('admin.retours.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.retours.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Feedback</span>
            </a>

            <hr class="border-white/20 border-t w-3/4 mx-auto">
            <p class="px-4 py-1 uppercase tracking-wide text-[10px] text-white/70">Jeu</p>

            <a href="{{ route('admin.nuage.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.nuage.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Nuage de mot</span>
            </a>
        </nav>
    </div>

    <div class="border-t border-white/20 text-center text-xs py-3">
        <a href="#" class="block px-4 py-1 hover:text-orangeone">Support</a>
        <a href="#" class="block px-4 py-1 hover:text-orangeone">Documentation</a>
    </div>
</aside>
