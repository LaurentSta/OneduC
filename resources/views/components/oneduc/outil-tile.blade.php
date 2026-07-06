@props([
    'title',
    'iconBg',
    'badgeCount' => null,
    'ctaRoute' => null,
    'ctaLabel' => null,
    'ctaBg' => null,
    'toolId',
])

<div {{ $attributes }}>
    <button type="button"
            @click="selectedTool = '{{ $toolId }}'"
            :aria-pressed="(selectedTool === '{{ $toolId }}').toString()"
            :class="selectedTool === '{{ $toolId }}' ? 'ring-2 ring-bleuone bg-bleuone/5' : ''"
            class="flex w-full flex-col items-center gap-2 rounded-2xl bg-white p-4 text-center shadow-md transition hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-bleuone/40">

        <span class="relative flex h-14 w-14 items-center justify-center rounded-2xl {{ $iconBg }} text-white">
            {{ $icon }}
            @if($badgeCount)
                <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-gray-700 ring-2 ring-gray-100">
                    {{ $badgeCount }}
                </span>
            @endif
        </span>
        <span class="text-xs font-bold leading-tight text-gray-800 md:text-sm">{{ $title }}</span>
    </button>

    {{-- Détail de l'outil : téléporté dans le panneau de droite, affiché uniquement quand sélectionné --}}
    <template x-teleport="#outil-detail-panel">
        <div x-show="selectedTool === '{{ $toolId }}'" x-cloak class="text-left">
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
    </template>
</div>
