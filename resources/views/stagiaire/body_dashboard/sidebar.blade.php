
@php
    $isFormationActive = request()->routeIs('stagiaire.modules')
        || request()->routeIs('stagiaire.module.*')
        || request()->routeIs('stagiaire.evaluations.*')
        || request()->routeIs('stagiaire.quiz.*')
        || request()->routeIs('stagiaire.lesson.quiz.*')
        || request()->routeIs('stagiaire.live-quiz.*');

    $isProgressionActive = request()->routeIs('stagiaire.resultats')
        || request()->routeIs('stagiaire.progression.*');

    $isOutilsActive = request()->routeIs('stagiaire.outils');

    $isMessagesActive = request()->routeIs('stagiaire.messages.*');

    $isDocumentationActive = request()->routeIs('stagiaire.documentation');

    $navBaseClasses = 'flex flex-col items-center rounded-xl px-4 py-3 transition-all duration-200';
    $navIdleClasses = 'text-white/90 hover:bg-[#0A5B80] hover:text-white';
    $navActiveClasses = 'bg-[#0A5B80] text-white shadow-lg shadow-black/10';
@endphp

<aside
  id="stagiaire-sidebar"
  :class="{
    'translate-x-0': sidebarOpen,
    '-translate-x-full': !sidebarOpen,
    'lg:translate-x-0': !sidebarCollapsed,
    'lg:-translate-x-full': sidebarCollapsed,
  }"
  class="fixed left-0 z-40 w-48 transition-transform duration-300 flex flex-col bg-[#004461] text-white shadow-lg"
  style="top: var(--app-header-h, 86px); height: calc(100vh - var(--app-header-h, 86px));"
  aria-label="Navigation stagiaire">
        <div class="flex-1 overflow-y-auto">
            <a
                href="{{ route('stagiaire.dashboard') }}"
                class="flex min-h-24 flex-col items-center justify-center gap-1 bg-[#00374F] px-4 text-center transition hover:bg-[#0a4f70]"
            >
                <span class="text-[11px] font-bold uppercase tracking-[0.28em] text-white/70">Stagiaire</span>
                <span class="text-lg font-bold text-white">Tableau de bord</span>
            </a>

            <nav class="px-3 py-4 text-center" aria-label="Espaces du stagiaire">
                <div class="space-y-3">
                    <a
                        href="{{ route('stagiaire.modules') }}"
                        class="{{ $navBaseClasses }} {{ $isFormationActive ? $navActiveClasses : $navIdleClasses }}"
                        aria-current="{{ $isFormationActive ? 'page' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 lucide lucide-library-big-icon lucide-library-big"><rect width="8" height="18" x="3" y="3" rx="1"/><path d="M7 3v18"/><path d="M20.4 18.9c.2.5-.1 1.1-.6 1.3l-1.9.7c-.5.2-1.1-.1-1.3-.6L11.1 5.1c-.2-.5.1-1.1.6-1.3l1.9-.7c.5-.2 1.1.1 1.3.6Z"/></svg>
                        <span class="text-[17px] font-medium">Formation</span>
                    </a>

                    <a
                        href="{{ route('stagiaire.resultats') }}"
                        class="{{ $navBaseClasses }} {{ $isProgressionActive ? $navActiveClasses : $navIdleClasses }}"
                        aria-current="{{ $isProgressionActive ? 'page' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2 lucide lucide-chart-column-increasing-icon lucide-chart-column-increasing"><path d="M13 17V9"/><path d="M18 17V5"/><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M8 17v-3"/></svg>
                        <span class="text-[17px] font-medium">Progression</span>
                    </a>

                    <a
                        href="{{ route('stagiaire.outils') }}"
                        class="{{ $navBaseClasses }} {{ $isOutilsActive ? $navActiveClasses : $navIdleClasses }}"
                        aria-current="{{ $isOutilsActive ? 'page' : 'false' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                        </svg>
                        <span class="text-[17px] font-medium">Outils</span>
                    </a>

                    @php
                        $unreadMessages = \App\Models\FormateurMessage::where('stagiaire_id', auth()->id())->count();
                    @endphp
                    <a
                        href="{{ route('stagiaire.messages.index') }}"
                        class="{{ $navBaseClasses }} {{ $isMessagesActive ? $navActiveClasses : $navIdleClasses }} relative"
                        aria-current="{{ $isMessagesActive ? 'page' : 'false' }}"
                    >
                        @if($unreadMessages > 0)
                          <span class="absolute top-2 right-3 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-orangeone text-white text-[10px] font-bold">
                            {{ $unreadMessages > 9 ? '9+' : $unreadMessages }}
                          </span>
                        @endif
                        <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                            <path d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                        <span class="text-[17px] font-medium">Messages</span>
                    </a>
                </div>
            </nav>
        </div>

        <div class="border-t border-white/20 px-3 py-3 text-center text-xs">
            <a href="{{ route('contact') }}" class="block rounded-lg px-4 py-2 transition hover:bg-white/10 hover:text-orange-100">Support</a>
            <a
                href="{{ route('stagiaire.documentation') }}"
                class="mt-1 block rounded-lg px-4 py-2 transition {{ $isDocumentationActive ? 'bg-white/15 text-white' : 'hover:bg-white/10 hover:text-orange-100' }}"
                aria-current="{{ $isDocumentationActive ? 'page' : 'false' }}"
            >
                Documentation
            </a>
        </div>
    </aside>
