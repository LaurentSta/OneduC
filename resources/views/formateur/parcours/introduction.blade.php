@extends('formateur.parcours.layout')

@section('hide_parcours_brand_header', 'true')

@php
    $firstChapterUrl = $currentModule['first_chapter_url'] ?? $currentModule['url'];
    $introItems = $currentModule['intro_items'] ?? [
        [
            'title' => 'Dérouler un chapitre',
            'body' => 'Utilisez la flèche orange pour afficher les leçons.',
            'icon' => 'chevron',
        ],
        [
            'title' => 'Ouvrir une leçon',
            'body' => 'Cliquez sur une leçon pour accéder à son contenu.',
            'icon' => 'play',
        ],
        [
            'title' => 'Suivre le contenu',
            'body' => 'Le contenu principal de la leçon apparaîtra dans l’espace prévu.',
            'icon' => 'screen',
        ],
        [
            'title' => 'Réaliser une activité',
            'body' => 'Quand une activité est disponible, un bouton vous permet de la lancer.',
            'icon' => 'check',
        ],
        [
            'title' => 'Revenir au plan',
            'body' => 'Le panneau latéral permet de retrouver les chapitres, leçons et bilans.',
            'icon' => 'list',
        ],
        [
            'title' => 'Avancer à son rythme',
            'body' => 'Les boutons précédent et suivant facilitent la navigation.',
            'icon' => 'arrow',
        ],
    ];
@endphp

@section('parcours_content')
    @if (!empty($introductionScormUrl))
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script src="{{ asset('scorm_core/js/API.js') }}"></script>
        <script>
            window.SCORM_CONTEXT = {
                lecture_id: 0,
                module_id: @json($activeModuleKey),
                section_id: 'introduction',
                next_url: @json($firstChapterUrl),
                final_url: @json($firstChapterUrl),
                is_already_done: false,
                anonymous: false,
                read_only: false,
                force_next_lesson: true,
                quiz_start_url: null,
                quiz_button_label: null,
                next_button_label: 'Commencer le module',
                quiz_tester_url: null,

                goToNextLesson: function () {
                    window.location.href = this.next_url;
                },

                goToQuiz: function () {
                    this.goToNextLesson();
                }
            };

            window.goToNextLesson = function () {
                window.SCORM_CONTEXT.goToNextLesson();
            };

            window.goToQuiz = function () {
                window.SCORM_CONTEXT.goToQuiz();
            };
        </script>

        <div
            x-data="{
                fullscreenSupported: false,
                fullscreenActive: false,
                async toggleFullscreen() {
                    const target = this.$refs.lessonViewport;

                    if (!target || !this.fullscreenSupported) {
                        return;
                    }

                    try {
                        if (document.fullscreenElement) {
                            await document.exitFullscreen();
                            return;
                        }

                        if (typeof target.requestFullscreen === 'function') {
                            await target.requestFullscreen();
                        }
                    } catch (error) {
                        console.error('Impossible de basculer en plein écran.', error);
                    }
                },
                syncFullscreenState() {
                    this.fullscreenActive = !!document.fullscreenElement;
                },
                init() {
                    this.fullscreenSupported = !!document.fullscreenEnabled;
                    this.syncFullscreenState();

                    document.addEventListener('fullscreenchange', () => this.syncFullscreenState());
                }
            }"
            class="px-2 pt-12 sm:px-3 lg:px-4"
        >
            <section
                x-ref="lessonViewport"
                class="relative h-[calc(100vh-4rem)] min-h-[520px] overflow-hidden rounded-[24px] border border-gray-100 bg-white shadow-sm"
            >
                <div class="pointer-events-none absolute left-4 top-4 z-20 flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        x-show="fullscreenSupported"
                        x-cloak
                        @click="toggleFullscreen()"
                        class="pointer-events-auto inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/95 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-700 shadow-sm backdrop-blur transition hover:bg-white"
                        :aria-pressed="fullscreenActive.toString()"
                    >
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path x-show="!fullscreenActive" x-cloak d="M8 4H4v4" style="display: none;" />
                            <path x-show="!fullscreenActive" x-cloak d="M16 4h4v4" style="display: none;" />
                            <path x-show="!fullscreenActive" x-cloak d="M8 20H4v-4" style="display: none;" />
                            <path x-show="!fullscreenActive" x-cloak d="M16 20h4v-4" style="display: none;" />
                            <path x-show="fullscreenActive" x-cloak d="M9 4H4v5" style="display: none;" />
                            <path x-show="fullscreenActive" x-cloak d="M15 4h5v5" style="display: none;" />
                            <path x-show="fullscreenActive" x-cloak d="M9 20H4v-5" style="display: none;" />
                            <path x-show="fullscreenActive" x-cloak d="M15 20h5v-5" style="display: none;" />
                        </svg>
                        <span x-text="fullscreenActive ? 'Quitter mode plein écran' : 'Mode plein écran'"></span>
                    </button>
                </div>

                <iframe
                    id="scorm-iframe"
                    title="Introduction - {{ $currentModule['title'] }}"
                    src="{{ $introductionScormUrl }}"
                    frameborder="0"
                    allowfullscreen
                    scrolling="no"
                    class="block h-full w-full bg-white"
                ></iframe>
            </section>
        </div>

    @else
    <div class="mx-auto max-w-6xl px-2 pt-12 sm:px-3 lg:px-4">
        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-8 px-6 py-8 md:px-8 lg:grid-cols-[minmax(0,1fr)_320px] lg:px-10 lg:py-10">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-orangeone">{{ $currentModule['label'] }}</p>
                    <h1 class="mt-3 font-raleway text-3xl font-medium leading-tight text-bleuone md:text-4xl">
                        Avant de commencer
                    </h1>
                    <p class="mt-4 max-w-3xl text-base leading-8 text-slate-600">
                        Cette page présente les principaux boutons et repères que vous allez utiliser dans le module.
                    </p>
                </div>

                <aside class="rounded-[24px] border border-orange-100 bg-orange-50/60 p-6">
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Module</p>
                    <p class="mt-3 font-raleway text-2xl font-medium leading-tight text-bleuone">{{ $currentModule['title'] }}</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $currentModule['duration_label'] }}</p>
                </aside>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-8 md:px-8 lg:px-10">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($introItems as $item)
                        <article class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-orange-50 text-orangeone">
                                    @switch($item['icon'] ?? 'check')
                                        @case('chevron')
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                            @break
                                        @case('play')
                                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                            @break
                                        @case('screen')
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v10H4z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20h6" />
                                            </svg>
                                            @break
                                        @case('list')
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                                            </svg>
                                            @break
                                        @case('arrow')
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                                            </svg>
                                            @break
                                        @default
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                    @endswitch
                                </span>

                                <div>
                                    <h2 class="text-lg font-bold leading-tight text-bleuone">{{ $item['title'] }}</h2>
                                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ $item['body'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between md:px-8 lg:px-10">
                <a href="{{ $currentModule['url'] }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone">
                    Revenir a la presentation
                </a>

                <a href="{{ $firstChapterUrl }}" class="btn-oneduc !rounded-full !px-7 !py-3">
                    Commencer le module
                </a>
            </div>
        </section>
    </div>
    @endif
@endsection
