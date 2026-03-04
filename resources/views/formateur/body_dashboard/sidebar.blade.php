
<aside
  id="formateur-sidebar"
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  class="fixed left-0 z-40 w-64 lg:translate-x-0 transition-transform duration-300 flex flex-col bg-[#004461] text-white shadow-lg"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  aria-label="Navigation formateur">

<!-- En-tête Admin -->
        <div class="flex-1 overflow-y-auto">
            <a href="{{ route('formateur.dashboard') }}" class="flex items-center justify-center h-16 bg-[#00374F] text-xl font-bold tracking-wide uppercase hover:bg-orangeone transition">
                Formateur
            </a>
            <!-- Navigation -->
            <nav class="px-2 py-4 text-xs space-y-4 text-center">
                <!-- Dashboard -->
                <a href="{{ route('formateur.dashboard') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4l9 5.75M4.5 10.5v7.5A1.5 1.5 0 006 19.5h3.75v-6h4.5v6H18a1.5 1.5 0 001.5-1.5v-7.5" />
                    </svg>
                    <span class="text-base font-medium">Tableau de bord</span>
                </a>
                <hr class="border-white/20 border-t border w-3/4 mx-auto">
                <!-- Groupes -->
                <a href="{{ route('formateur.groupes.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-group-icon lucide-group"><path d="M3 7V5c0-1.1.9-2 2-2h2"/><path d="M17 3h2c1.1 0 2 .9 2 2v2"/><path d="M21 17v2c0 1.1-.9 2-2 2h-2"/><path d="M7 21H5c-1.1 0-2-.9-2-2v-2"/><rect width="7" height="5" x="7" y="7" rx="1"/><rect width="7" height="5" x="10" y="12" rx="1"/></svg>
                    <span class="text-base font-medium">Groupes</span>
                </a>
                <!-- Stagiaires -->
                <a href="{{ route('formateur.stagiaires.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/></svg>
                    <span class="text-base font-medium">Stagiaires</span>
                </a>
<!-- Modules -->
                <a href="{{ route('formateur.formations.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-presentation-icon lucide-presentation"><path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/></svg>
                    <span class="text-base font-medium">Formations</span>
                </a>

                <!-- Progression -->
                <a href="{{ route('formateur.progressions.groupes') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-no-axes-combined-icon lucide-chart-no-axes-combined"><path d="M12 16v5"/><path d="M16 14v7"/><path d="M20 10v11"/><path d="m22 3-8.646 8.646a.5.5 0 0 1-.708 0L9.354 8.354a.5.5 0 0 0-.707 0L2 15"/><path d="M4 18v3"/><path d="M8 14v7"/></svg>
                    <span class="text-base font-medium">Progression</span>
                </a>
            </nav>
        </div>
        <!-- Barre inférieure -->
        <div class="border-t border-white/20 text-center text-xs py-3">
            <a href="{{ route('contact') }}" class="block px-4 py-1 hover:text-orangeone">Support</a>
            <a href="{{ route('formateur.documentation') }}" class="block px-4 py-1 hover:text-orangeone">Documentation</a>
        </div>
    </aside>

