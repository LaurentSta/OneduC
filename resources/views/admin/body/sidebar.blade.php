{{-- /home/laurents/Oneduc_Dev/resources/views/admin/body/sidebar.blade.php --}}

@php
    // Hauteur du header (dans ton layout actuel : py-3 + logo 40px -> 64px est un bon repère)
    $headerHeight = '4rem'; // 64px
@endphp

<aside
    x-show="sidebarOpen"
    x-transition:enter="transition transform duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition transform duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed left-0 z-40 hidden lg:flex flex-col w-40 bg-[#004461] text-white shadow-lg"
    style="top: {{ $headerHeight }}; height: calc(100vh - {{ $headerHeight }});"
    aria-label="Navigation administrateur"
>
    {{-- Zone haute (menu) --}}
    <div class="flex-1 overflow-y-auto">
        {{-- En-tête Admin (inchangé) --}}
        <a
            href="{{ route('admin.dashboard') }}"
            class="flex items-center justify-center h-16 bg-[#00374F] text-xl font-bold tracking-wide uppercase hover:bg-orangeone transition"
        >
            Admin
        </a>

        {{-- Navigation (icônes supprimées, structure conservée) --}}
        <nav class="px-2 py-4 text-xs space-y-3 text-center" aria-label="Menu principal">

            @php
                $itemBase = 'block px-4 py-2 rounded transition';
                $itemHover = 'hover:bg-orangeone';
                $itemActive = 'bg-orangeone';
            @endphp

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.dashboard') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Dashboard</span>
            </a>

            <hr class="border-white/20 border-t w-3/4 mx-auto">

            {{-- Catégories --}}
            <a href="{{ route('admin.categories.all') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.subcategories.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Catégories</span>
            </a>

            {{-- Modules --}}
            <a href="{{ route('admin.modules') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.modules*') || request()->routeIs('admin.lectures.*') || request()->routeIs('admin.sections.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Modules</span>
            </a>
            {{-- SCORM --}}
            @php
                // Lien robuste : si tu as la page index, on l’utilise, sinon on retombe sur la page test import
                $scormMenuUrl =
                    \Illuminate\Support\Facades\Route::has('admin.scorm.library.index') ? route('admin.scorm.library.index') :
                    (\Illuminate\Support\Facades\Route::has('admin.scorm.library.test') ? route('admin.scorm.library.test') : url('/admin/scorm-library/test'));
            @endphp

            <a href="{{ $scormMenuUrl }}"
            class="{{ $itemBase }} {{ request()->routeIs('admin.scorm.*') || request()->is('admin/scorm-library*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">SCORM</span>
            </a>

            {{-- Formateurs --}}
            <a href="{{ route('admin.formateurs') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.formateurs*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Formateurs</span>
            </a>

            {{-- Groupes --}}
            <a href="{{ route('admin.groupes') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.groupes*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Groupes</span>
            </a>

            {{-- Stagiaires --}}
            <a href="{{ route('admin.stagiaires.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.stagiaires.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Stagiaires</span>
            </a>

            {{-- Évaluations --}}
            <a href="{{ route('admin.evaluations.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.evaluations.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Évaluation</span>
            </a>

            {{-- Référentiels --}}
            <a href="{{ route('admin.referentiels.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.referentiels.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Référentiels</span>
            </a>

            {{-- Feedback --}}
            <a href="{{ route('admin.retours.index') }}"
               class="{{ $itemBase }} {{ request()->routeIs('admin.retours.*') ? $itemActive : $itemHover }}">
                <span class="text-base font-medium">Feedback</span>
            </a>

        </nav>
    </div>

    {{-- Bas de sidebar (inchangé) --}}
    <div class="border-t border-white/20 text-center text-xs py-3">
        <a href="#" class="block px-4 py-1 hover:text-orangeone">Support</a>
        <a href="#" class="block px-4 py-1 hover:text-orangeone">Documentation</a>
    </div>
</aside>
