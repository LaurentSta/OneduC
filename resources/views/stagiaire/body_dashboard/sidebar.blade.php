
{{-- SIDEBAR --}}
<div  class="flex min-h-screen">

    <div
  x-show="sidebarOpen"
  x-transition:enter="transition transform duration-300"
  x-transition:enter-start="-translate-x-full"
  x-transition:enter-end="translate-x-0"
  x-transition:leave="transition transform duration-300"
  x-transition:leave-start="translate-x-0"
  x-transition:leave-end="-translate-x-full"
  class="lg:flex flex-col w-40 bg-[#004461] text-white h-screen justify-between transform transition-transform duration-300"
>
        <!-- En-tête Admin -->
        <div>
            <a href="{{ route('stagiaire.dashboard') }}" class="flex items-center justify-center h-16 bg-[#00374F] text-xl font-bold tracking-wide uppercase hover:bg-orangeone transition">
                Stagiaire
            </a>

            <!-- Navigation -->
            <nav class="px-2 py-4 text-xs space-y-4 text-center">
                <!-- Dashboard -->
                <a href="{{ route('stagiaire.dashboard') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4l9 5.75M4.5 10.5v7.5A1.5 1.5 0 006 19.5h3.75v-6h4.5v6H18a1.5 1.5 0 001.5-1.5v-7.5" />
                    </svg>
                    <span class="text-base font-medium">Dashboard</span>
                </a>
                <hr class="border-white/20 border-t border w-3/4 mx-auto">
                <!-- Catégories -->
                <a href="{{ route('stagiaire.modules') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-library-big-icon lucide-library-big"><rect width="8" height="18" x="3" y="3" rx="1"/><path d="M7 3v18"/><path d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z"/></svg>
                    <span class="text-base font-medium">Formations</span>
                </a>

                <!-- Résultats -->
                <a href="{{ route('stagiaire.resultats') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-column-increasing-icon lucide-chart-column-increasing"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>
                    <span class="text-base font-medium">Progressions</span>
                </a>

                <!-- Compétence -->
                <a href="#" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-pie-icon lucide-chart-pie"><path d="M21 12c.552 0 1.005-.449.95-.998a10 10 0 0 0-8.953-8.951c-.55-.055-.998.398-.998.95v8a1 1 0 0 0 1 1z"/><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/></svg>
                    <span class="text-base font-medium">Compétences</span>
                </a>

            </nav>
        </div>

        <!-- Barre inférieure -->
        <div class="border-t border-white/20 text-center text-xs py-3">
            <a href="#" class="block px-4 py-1 hover:text-orangeone">Support</a>
            <a href="#" class="block px-4 py-1 hover:text-orangeone">Documentation</a>
        </div>
    </div>








