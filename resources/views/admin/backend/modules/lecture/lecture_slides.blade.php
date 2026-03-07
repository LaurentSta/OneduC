@extends('admin.admin_dashboard')

@section('admin')
<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div class="min-w-0">
                <h1 class="text-[20px] font-varela text-bleuone truncate">
                    Prévisualisation slides : {{ $lecture->lecture_title }}
                </h1>
                <p class="text-xs text-gray-600 mt-1">
                    {{ count($slides) }} slide(s)
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition"
                   style="transition: all .35s cubic-bezier(.22,1,.36,1);">
                    <i class="ti ti-arrow-left"></i>
                    Retour
                </a>
            </div>
        </div>

        <div
            x-data="{
                current: 1,
                total: {{ count($slides) }},
                slides: @js($slides),
                get currentSrc() { return this.slides[this.current - 1] ?? null; }
            }"
            class="space-y-4"
        >
            <div class="relative rounded-[16px] overflow-hidden border border-gray-200 bg-gray-50" style="height: 80vh;">
                <img :src="currentSrc" alt="Slide de cours" class="w-full h-full object-contain">

                <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-black/70 text-white text-xs font-semibold">
                    Slide <span x-text="current"></span> / <span x-text="total"></span>
                </div>

                <div class="absolute inset-y-0 left-3 flex items-center">
                    <button type="button" @click="if(current > 1) current--"
                            class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition" aria-label="Slide précédente">
                        <i class="ti ti-chevron-left"></i>
                    </button>
                </div>
                <div class="absolute inset-y-0 right-3 flex items-center">
                    <button type="button" @click="if(current < total) current++"
                            class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition" aria-label="Slide suivante">
                        <i class="ti ti-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" @click="current = Math.max(1, current - 1)"
                            class="px-3 py-2 text-xs font-semibold uppercase border border-gray-300 rounded hover:bg-gray-50">
                        Précédent
                    </button>
                    <button type="button" @click="current = Math.min(total, current + 1)"
                            class="px-3 py-2 text-xs font-semibold uppercase border border-gray-300 rounded hover:bg-gray-50">
                        Suivant
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
