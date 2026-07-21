@extends($layoutView)

@section($layoutSection)
@php
    $whiteboardConfigJson = json_encode($whiteboardConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $studentCount = $group->students()->count();
@endphp

<div
    x-data="{ fullscreen: false }"
    x-init="$watch('fullscreen', value => document.body.classList.toggle('overflow-hidden', value))"
    @keydown.escape.window="fullscreen = false"
    :class="fullscreen
        ? 'fixed inset-0 z-[60] flex flex-col gap-3 bg-gray-100 px-4 py-4'
        : 'w-full px-6 lg:px-8'"
>
    {{-- En-tête --}}
    <header x-show="!fullscreen" class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <nav class="text-sm font-varela text-gray-500 mb-2">
                    <ol class="inline-flex items-center space-x-1">
                        <li>
                            <a href="{{ $secondaryUrl }}" class="text-orangeone hover:underline">{{ $secondaryLabel }}</a>
                        </li>
                        <li><span class="mx-2 text-gray-400">/</span></li>
                        <li class="text-gray-400">{{ $pageTitle }}</li>
                    </ol>
                </nav>
                <p class="font-raleway text-2xl text-bleuone">{{ $pageTitle }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $pageDescription }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-bleuone/10 px-3 py-1 text-bleuone">Groupe : {{ $group->name }}</span>
                    <span class="rounded-full bg-bleuone/10 px-3 py-1 text-bleuone">{{ $studentCount }} participant{{ $studentCount > 1 ? 's' : '' }}</span>
                    <span class="rounded-full bg-bleuone/10 px-3 py-1 text-bleuone">Synchronisé toutes les 2 secondes</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="fullscreen = true" class="btn-oneduc-outline !px-4 !py-2 !text-sm">
                    <i class="ti ti-arrows-maximize" aria-hidden="true"></i>
                    Plein page
                </button>
                <a href="{{ $backUrl }}" class="btn-oneduc-outline !px-4 !py-2 !text-sm">
                    {{ $backLabel }}
                </a>
            </div>
        </div>
    </header>

    {{-- Barre compacte affichée uniquement en plein page --}}
    <div x-show="fullscreen" x-cloak class="flex shrink-0 items-center justify-between gap-4">
        <p class="font-raleway text-lg text-bleuone">{{ $pageTitle }} · {{ $group->name }}</p>
        <button type="button" @click="fullscreen = false" class="btn-oneduc-outline !px-4 !py-2 !text-sm">
            <i class="ti ti-arrows-minimize" aria-hidden="true"></i>
            Quitter le plein page
        </button>
    </div>

    <section
        class="relative overflow-hidden rounded-[20px] border border-gray-200 bg-white shadow-md"
        :class="fullscreen ? 'flex-1 min-h-0' : ''"
        :style="!fullscreen ? 'height: 82vh' : ''"
    >
        <div data-whiteboard-app class="absolute inset-0"></div>
        <script type="application/json" data-whiteboard-config>{!! $whiteboardConfigJson !!}</script>
    </section>

    <p x-show="!fullscreen" class="text-xs text-gray-500 mt-3">
        Dessinez, annotez, insérez des formes et du texte. Tout est sauvegardé automatiquement et partagé avec le groupe en temps réel.
    </p>

    <noscript>
        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4 text-sm text-orange-900">
            Activez JavaScript pour utiliser le tableau blanc collaboratif.
        </div>
    </noscript>
</div>
@endsection
