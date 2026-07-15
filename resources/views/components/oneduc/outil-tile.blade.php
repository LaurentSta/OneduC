@props([
    'title',
    'iconBg',
    'badgeCount' => null,
    'ctaRoute' => null,
    'ctaLabel' => null,
    'ctaBg' => null,
    'toolId',
    'categories' => [],
    'modalites' => [],
    'temporalite' => [],
    'contexte' => [],
])

@php
    $filtresOutil = [
        'categories' => $categories,
        'modalites' => $modalites,
        'temporalite' => $temporalite,
        'contexte' => $contexte,
    ];
@endphp

<div {{ $attributes }}
     data-outil-filtres="{{ json_encode($filtresOutil) }}"
     x-show="outilVisible(@js($filtresOutil))">
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

    {{-- Détail de l'outil : fenêtre modale centrée, affichée uniquement quand sélectionné --}}
    <template x-teleport="body">
        <div x-show="selectedTool === '{{ $toolId }}'"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="outil-modal-titre-{{ $toolId }}">

            <div class="absolute inset-0 bg-gray-900/60"
                 x-show="selectedTool === '{{ $toolId }}'"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="selectedTool = null"></div>

            <div class="relative w-full max-w-md rounded-2xl border border-gray-100 bg-white p-5 text-left shadow-md"
                 x-show="selectedTool === '{{ $toolId }}'"
                 @click.outside="selectedTool = null"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-90">

                <button type="button"
                        @click="selectedTool = null"
                        aria-label="Fermer"
                        class="absolute right-4 top-4 rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="mb-3 flex items-center gap-2.5 pr-6">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $iconBg }} text-white">
                        {{ $icon }}
                    </span>
                    <h3 id="outil-modal-titre-{{ $toolId }}" class="font-bold text-gray-900">{{ $title }}</h3>
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
    </template>
</div>
