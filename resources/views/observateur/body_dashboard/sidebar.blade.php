<aside
  id="observateur-sidebar"
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  class="fixed left-0 z-40 w-[12.5rem] lg:translate-x-0 transition-transform duration-300 flex flex-col bg-[#004461] text-white shadow-lg"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  aria-label="Navigation observateur">

  <div class="flex-1 overflow-y-auto">
    <a href="{{ route('observateur.dashboard') }}" class="flex items-center justify-center h-16 bg-[#00374F] text-xl font-bold tracking-wide uppercase hover:bg-orangeone transition">
      Observateur
    </a>

    <nav class="px-2 py-4 text-xs space-y-4 text-center">
      <a href="{{ route('observateur.dashboard') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded {{ request()->routeIs('observateur.dashboard') ? 'bg-orangeone' : '' }}">
        <span class="text-base font-medium">Tableau de bord</span>
      </a>

      <hr class="border-white/20 border-t border w-3/4 mx-auto">

      <a href="{{ route('observateur.groupes.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded {{ request()->routeIs('observateur.groupes.*') ? 'bg-orangeone' : '' }}">
        <span class="text-base font-medium">Groupes</span>
      </a>

      <a href="{{ route('observateur.progressions.groupes') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded {{ request()->routeIs('observateur.progressions.*') ? 'bg-orangeone' : '' }}">
        <span class="text-base font-medium">Progression</span>
      </a>
    </nav>
  </div>

  <div class="border-t border-white/20 text-center text-xs py-3">
    <span class="block px-4 py-1 text-white/80">Lecture seule</span>
  </div>
</aside>
