@props([
    'title',
    'iconBg',
    'badgeCount' => null,
    'ctaRoute' => null,
    'ctaLabel' => null,
    'ctaBg' => null,
])

<div {{ $attributes->merge(['class' => 'relative']) }}
     x-data="{ open: false, alignRight: false, checkAlign() { this.alignRight = ($el.getBoundingClientRect().left + 320) > (window.innerWidth - 16) } }"
     @mouseenter="open = true; checkAlign()"
     @mouseleave="open = false"
     @focusin="open = true; checkAlign()"
     @focusout="open = false"
     @keydown.escape="open = false"
     @click.outside="open = false">

    @php
        $triggerClasses = 'flex w-full flex-col items-center gap-2 rounded-2xl bg-white p-4 text-center shadow-md transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-bleuone/40';
    @endphp

    @if($ctaRoute)
        <a href="{{ $ctaRoute }}" :aria-expanded="open.toString()" aria-haspopup="true" class="{{ $triggerClasses }}">
    @else
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true" class="{{ $triggerClasses }}">
    @endif

        <span class="relative flex h-14 w-14 items-center justify-center rounded-2xl {{ $iconBg }} text-white">
            {{ $icon }}
            @if($badgeCount)
                <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-gray-700 ring-2 ring-gray-100">
                    {{ $badgeCount }}
                </span>
            @endif
        </span>
        <span class="text-xs font-bold leading-tight text-gray-800 md:text-sm">{{ $title }}</span>

    @if($ctaRoute)
        </a>
    @else
        </button>
    @endif

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         :class="alignRight ? 'right-0' : 'left-0'"
         class="absolute top-full z-30 mt-3 w-80 max-w-[85vw] text-left">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center gap-2.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $iconBg }} text-white">
                    {{ $icon }}
                </span>
                <h3 class="font-bold text-gray-900">{{ $title }}</h3>
            </div>

            <p class="mb-3 text-xs leading-relaxed text-gray-600">
                {{ $description }}
            </p>

            @isset($badges)
                <div class="mb-3 flex flex-wrap gap-1.5 text-[11px]">
                    {{ $badges }}
                </div>
            @endisset

            @isset($body)
                <div class="border-t border-gray-100 pt-3">
                    {{ $body }}
                </div>
            @endisset

            @if($ctaRoute && $ctaLabel)
                <a href="{{ $ctaRoute }}"
                   class="mt-4 flex w-full items-center justify-center gap-2 rounded-[10px] {{ $ctaBg }} px-4 py-2.5 text-sm font-bold text-white transition">
                    {{ $ctaLabel }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>
