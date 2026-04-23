@php
    $isDashboardActive = request()->routeIs('formateur.dashboard');

    $isGroupesActive = request()->routeIs('formateur.groupes.*');

    $isStagiairesActive = request()->routeIs('formateur.stagiaires.*');

    $isFormationsActive = request()->routeIs('formateur.formations.*')
        || request()->routeIs('formateur.objectifs.*')
        || request()->routeIs('formateur.quiz.*')
        || request()->routeIs('formateur.lesson.quiz.*')
        || request()->routeIs('formateur.mes-formations.*');

    $isOutilsActive = request()->routeIs('formateur.outils.*')
        || request()->routeIs('formateur.nuages.*')
        || request()->routeIs('formateur.live-quiz.*')
        || request()->routeIs('formateur.groupes.whiteboard.*');

    $isProgressionActive = request()->routeIs('formateur.progressions.*')
        || request()->routeIs('formateur.progression.*');

    $isDocumentationActive = request()->routeIs('formateur.documentation');
    $hasParcoursRoute = \Illuminate\Support\Facades\Route::has('formateur.parcours.index');
    $isParcoursActive = $hasParcoursRoute && request()->routeIs('formateur.parcours.*');

    $navBaseClasses = 'flex flex-col items-center rounded-xl px-4 py-3 transition-all duration-200';
    $navIdleClasses = 'text-white/90 hover:bg-[#0A5B80] hover:text-white';
    $navActiveClasses = 'bg-[#0A5B80] text-white shadow-lg shadow-black/10';
@endphp

<aside
  id="formateur-sidebar"
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  class="fixed left-0 z-40 w-56 lg:translate-x-0 transition-transform duration-300 flex flex-col bg-[#004461] text-white shadow-lg"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  aria-label="Navigation formateur">
    <div class="flex-1 overflow-y-auto">
        <a
            href="{{ route('formateur.dashboard') }}"
            class="flex min-h-24 flex-col items-center justify-center gap-1 bg-[#00374F] px-4 text-center transition hover:bg-[#0a4f70]"
            aria-current="{{ $isDashboardActive ? 'page' : 'false' }}"
        >
            <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-white/70">Formateur</span>
            <span class="text-lg font-bold text-white">Tableau de bord</span>
        </a>

        <nav class="px-3 py-4 text-center" aria-label="Espaces du formateur">
            <div class="space-y-3">
                <a
                    href="{{ route('formateur.groupes.index') }}"
                    class="{{ $navBaseClasses }} {{ $isGroupesActive ? $navActiveClasses : $navIdleClasses }}"
                    aria-current="{{ $isGroupesActive ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 lucide lucide-group-icon lucide-group"><path d="M3 7V5c0-1.1.9-2 2-2h2"/><path d="M17 3h2c1.1 0 2 .9 2 2v2"/><path d="M21 17v2c0 1.1-.9 2-2 2h-2"/><path d="M7 21H5c-1.1 0-2-.9-2-2v-2"/><rect width="7" height="5" x="7" y="7" rx="1"/><rect width="7" height="5" x="10" y="12" rx="1"/></svg>
                    <span class="text-[17px] font-medium">Groupes</span>
                </a>

                <a
                    href="{{ route('formateur.stagiaires.index') }}"
                    class="{{ $navBaseClasses }} {{ $isStagiairesActive ? $navActiveClasses : $navIdleClasses }}"
                    aria-current="{{ $isStagiairesActive ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
                    <span class="text-[17px] font-medium">Stagiaires</span>
                </a>

                <a
                    href="{{ route('formateur.formations.index') }}"
                    class="{{ $navBaseClasses }} {{ $isFormationsActive ? $navActiveClasses : $navIdleClasses }}"
                    aria-current="{{ $isFormationsActive ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 lucide lucide-library-big-icon lucide-library-big"><rect width="8" height="18" x="3" y="3" rx="1"/><path d="M7 3v18"/><path d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z"/></svg>
                    <span class="text-[17px] font-medium">Formations</span>
                </a>

                <a
                    href="{{ route('formateur.outils.index') }}"
                    class="{{ $navBaseClasses }} {{ $isOutilsActive ? $navActiveClasses : $navIdleClasses }}"
                    aria-current="{{ $isOutilsActive ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    <span class="text-[17px] font-medium">Outils</span>
                </a>

                <a
                    href="{{ route('formateur.progressions.groupes') }}"
                    class="{{ $navBaseClasses }} {{ $isProgressionActive ? $navActiveClasses : $navIdleClasses }}"
                    aria-current="{{ $isProgressionActive ? 'page' : 'false' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 lucide lucide-chart-column-increasing-icon lucide-chart-column-increasing"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>
                    <span class="text-[17px] font-medium">Progression</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="border-t border-white/20 px-3 py-3 text-center text-xs">
        <a href="{{ route('contact') }}" class="block rounded-lg px-4 py-2 transition hover:bg-white/10 hover:text-orange-100">Support</a>
        @if ($hasParcoursRoute)
            <a
                href="{{ route('formateur.parcours.index') }}"
                class="mt-1 block rounded-lg px-4 py-2 transition {{ $isParcoursActive ? 'bg-white/15 text-white' : 'hover:bg-white/10 hover:text-orange-100' }}"
                aria-current="{{ $isParcoursActive ? 'page' : 'false' }}"
            >
                Formation formateur
            </a>
        @endif
        <a
            href="{{ route('formateur.documentation') }}"
            class="mt-1 block rounded-lg px-4 py-2 transition {{ $isDocumentationActive ? 'bg-white/15 text-white' : 'hover:bg-white/10 hover:text-orange-100' }}"
            aria-current="{{ $isDocumentationActive ? 'page' : 'false' }}"
        >
            Documentation
        </a>
    </div>
</aside>
