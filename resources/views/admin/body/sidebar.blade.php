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
        {{-- En-tête Admin --}}
        <a
            href="{{ route('admin.dashboard') }}"
            class="flex items-center justify-center h-16 bg-[#00374F] text-xl font-bold tracking-wide uppercase hover:bg-orangeone transition"
        >
            Admin
        </a>

        {{-- Navigation --}}
        <nav class="px-2 py-4 text-xs space-y-4 text-center">
            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 4l9 5.75M4.5 10.5v7.5A1.5 1.5 0 006 19.5h3.75v-6h4.5v6H18a1.5 1.5 0 001.5-1.5v-7.5" />
                </svg>
                <span class="text-base font-medium">Dashboard</span>
            </a>

            <hr class="border-white/20 border-t w-3/4 mx-auto">

            {{-- Catégories --}}
            <a href="{{ route('admin.categories.all') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="6" x2="20" y2="6" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="18" x2="20" y2="18" />
                </svg>
                <span class="text-base font-medium">Catégories</span>
            </a>

            {{-- Modules --}}
            <a href="{{ route('admin.modules') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-copy-icon lucide-book-copy">
                    <path d="M2 16V4a2 2 0 0 1 2-2h11"/>
                    <path d="M22 18H11a2 2 0 1 0 0 4h10.5a.5.5 0 0 0 .5-.5v-15a.5.5 0 0 0-.5-.5H11a2 2 0 0 0-2 2v12"/>
                    <path d="M5 14H4a2 2 0 1 0 0 4h1"/>
                </svg>
                <span class="text-base font-medium">Modules</span>
            </a>

            {{-- Formateurs --}}
            <a href="{{ route('admin.formateurs') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-lock-icon lucide-user-lock">
                    <circle cx="10" cy="7" r="4"/>
                    <path d="M10.3 15H7a4 4 0 0 0-4 4v2"/>
                    <path d="M15 15.5V14a2 2 0 0 1 4 0v1.5"/>
                    <rect width="8" height="5" x="13" y="16" rx=".899"/>
                </svg>
                <span class="text-base font-medium">Formateurs</span>
            </a>

            {{-- Groupes --}}
            <a href="{{ route('admin.groupes') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-icon lucide-users">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <path d="M16 3.128a4 4 0 0 1 0 7.744"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <circle cx="9" cy="7" r="4"/>
                </svg>
                <span class="text-base font-medium">Groupes</span>
            </a>

            {{-- Stagiaires --}}
            <a href="{{ route('admin.stagiaires.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-check-icon lucide-user-check">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <polyline points="16 11 18 13 22 9"/>
                </svg>
                <span class="text-base font-medium">Stagiaires</span>
            </a>

            {{-- Évaluations --}}
            <a href="{{ route('admin.evaluations.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-todo-icon lucide-list-todo">
                    <rect x="3" y="5" width="6" height="6" rx="1"/>
                    <path d="m3 17 2 2 4-4"/>
                    <path d="M13 6h8"/>
                    <path d="M13 12h8"/>
                    <path d="M13 18h8"/>
                </svg>
                <span class="text-base font-medium">Évaluation</span>
            </a>

            {{-- Feedback --}}
            <a href="{{ route('admin.retours.index') }}" class="flex flex-col items-center px-4 py-2 hover:bg-orangeone rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                    <line x1="8" y1="9" x2="16" y2="9" />
                    <line x1="8" y1="13" x2="14" y2="13" />
                </svg>
                <span class="text-base font-medium">Feedback</span>
            </a>
        </nav>
    </div>

    {{-- Bas de sidebar --}}
    <div class="border-t border-white/20 text-center text-xs py-3">
        <a href="#" class="block px-4 py-1 hover:text-orangeone">Support</a>
        <a href="#" class="block px-4 py-1 hover:text-orangeone">Documentation</a>
    </div>
</aside>
