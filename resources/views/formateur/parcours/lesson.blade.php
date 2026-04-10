@extends('formateur.parcours.layout')

@php
    $customPresentation = $currentLesson['custom_presentation'] ?? null;
    $editorial = $currentLesson['editorial'] ?? [
        'intro' => [
            $currentLesson['objective'],
            $currentLesson['pedagogical_intention'],
        ],
        'focus_cards' => [
            [
                'title' => 'Sujet aborde',
                'body' => $currentLesson['subject'],
            ],
            [
                'title' => 'Activite prevue',
                'body' => $currentLesson['activity'],
            ],
        ],
        'steps' => [
            $currentLesson['method'],
            $currentLesson['learning_process'],
        ],
        'checklist' => [
            $currentLesson['resources'],
        ],
        'placeholder_note' => 'Cette lecon est actuellement proposee sous une forme editoriale provisoire.',
    ];
@endphp

@section('parcours_content')
    @if (!empty($currentLesson['scorm_url']))
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script src="{{ asset('scorm_core/js/API.js') }}"></script>
        <script>
            window.SCORM_CONTEXT = {
                lecture_id: 0,
                module_id: @json($activeModuleKey),
                section_id: @json($activeChapterKey),
                next_url: @json($nextLesson['url'] ?? '#'),
                final_url: @json($currentChapter['url']),
                is_already_done: false,
                anonymous: false,
                read_only: false,
                force_next_lesson: @json(empty($nextActivity)),
                quiz_start_url: @json($nextActivity['url'] ?? null),
                quiz_button_label: @json($nextActivity['button_label'] ?? null),
                next_button_label: @json($nextLesson ? 'Leçon suivante' : 'Retour au chapitre'),
                quiz_tester_url: null,

                goToNextLesson: function () {
                    if (this.next_url && this.next_url !== '#') {
                        window.location.href = this.next_url;
                        return;
                    }

                    if (this.final_url) {
                        window.location.href = this.final_url;
                    }
                },

                goToQuiz: function () {
                    if (this.quiz_start_url) {
                        window.location.href = this.quiz_start_url;
                        return;
                    }

                    if (this.next_url && this.next_url !== '#') {
                        window.location.href = this.next_url;
                    }
                }
            };

            window.goToQuiz = function () {
                if (window.SCORM_CONTEXT && typeof window.SCORM_CONTEXT.goToQuiz === 'function') {
                    window.SCORM_CONTEXT.goToQuiz();
                }
            };

            window.goToNextLesson = function () {
                if (window.SCORM_CONTEXT && typeof window.SCORM_CONTEXT.goToNextLesson === 'function') {
                    window.SCORM_CONTEXT.goToNextLesson();
                }
            };
        </script>
    @endif

    <div
        x-data="{
            sidebarOpen: window.innerWidth >= 1024,
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
                    console.error('Impossible de basculer en plein ecran.', error);
                }
            },
            syncFullscreenState() {
                this.fullscreenActive = !!document.fullscreenElement;
            },
            init() {
                this.fullscreenSupported = !!document.fullscreenEnabled;
                this.syncFullscreenState();

                document.addEventListener('fullscreenchange', () => this.syncFullscreenState());
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        this.sidebarOpen = true;
                    }
                });
            }
        }"
        class="grid items-start gap-6 lg:grid-cols-[19rem_minmax(0,1fr)]"
    >
        @include('formateur.parcours.partials.sidebar')

        <section
            x-ref="lessonViewport"
            class="relative h-[calc(100vh-13rem)] min-h-[calc(100vh-13rem)] overflow-hidden rounded-[28px] border border-gray-100 bg-gray-100 shadow-sm"
        >
            <div class="pointer-events-none absolute left-4 top-4 z-20 flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    @click="sidebarOpen = !sidebarOpen"
                    class="pointer-events-auto inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/95 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-700 shadow-sm backdrop-blur transition hover:bg-white lg:hidden"
                >
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 7h16" />
                        <path d="M4 12h16" />
                        <path d="M4 17h16" />
                    </svg>
                    <span>Plan</span>
                </button>

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
                    <span x-text="fullscreenActive ? 'Quitter mode plein ecran' : 'Mode plein ecran'"></span>
                </button>
            </div>

            @if (!empty($currentLesson['scorm_url']))
                <iframe
                    id="scorm-iframe"
                    title="{{ $currentLesson['title'] }}"
                    src="{{ $currentLesson['scorm_url'] }}"
                    frameborder="0"
                    allowfullscreen
                    class="block h-full w-full"
                ></iframe>
            @elseif (!empty($customPresentation['pages']))
                <div class="h-full p-4 md:p-6">
                    @include('formateur.parcours.partials.lessons.group-wizard-presentation')
                </div>
            @else
                <div class="h-full overflow-y-auto p-4 md:p-6">
                    <article class="mx-auto max-w-5xl overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-6 py-6 md:px-8 md:py-8">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-orangeone">
                                    {{ $currentLesson['code'] }}
                                </span>
                                <span class="inline-flex rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    {{ $currentLesson['duration_label'] }}
                                </span>
                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    {{ $currentChapter['title'] }}
                                </span>
                            </div>

                            <h1 class="mt-5 font-raleway text-3xl font-medium leading-tight text-bleuone md:text-4xl">
                                {{ $currentLesson['title'] }}
                            </h1>

                            <p class="mt-5 max-w-4xl text-base leading-8 text-slate-600 md:text-lg">
                                {{ $currentLesson['objective'] }}
                            </p>
                        </div>

                        <div class="space-y-10 px-6 py-6 md:px-8 md:py-8">
                            <section class="space-y-4 text-sm leading-8 text-slate-700 md:text-base">
                                @foreach ($editorial['intro'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </section>

                            <section class="grid gap-4 md:grid-cols-2">
                                @foreach ($editorial['focus_cards'] as $card)
                                    <article class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-orangeone">{{ $loop->iteration < 10 ? '0' . $loop->iteration : $loop->iteration }}</p>
                                        <h2 class="mt-3 text-xl font-bold leading-tight text-bleuone">{{ $card['title'] }}</h2>
                                        <p class="mt-3 text-sm leading-7 text-slate-600 md:text-base">{{ $card['body'] }}</p>
                                    </article>
                                @endforeach
                            </section>

                            <section class="grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
                                <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-6">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orangeone text-white shadow-md">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                        <h2 class="text-xl font-black text-bleuone">Le deroule conseille</h2>
                                    </div>

                                    <ol class="mt-6 space-y-4">
                                        @foreach ($editorial['steps'] as $step)
                                            <li class="flex items-start gap-4">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-sm font-black text-orangeone shadow-sm ring-1 ring-orange-100">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <p class="pt-0.5 text-sm leading-7 text-slate-600 md:text-base">{{ $step }}</p>
                                            </li>
                                        @endforeach
                                    </ol>
                                </article>

                                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Repere pedagogique</p>
                                    <div class="mt-4 space-y-4 text-sm leading-7 text-slate-600">
                                        <p><span class="font-semibold text-bleuone">Intention :</span> {{ $currentLesson['pedagogical_intention'] }}</p>
                                        <p><span class="font-semibold text-bleuone">Methode :</span> {{ $currentLesson['method'] }}</p>
                                        <p><span class="font-semibold text-bleuone">Processus :</span> {{ $currentLesson['learning_process'] }}</p>
                                        <p><span class="font-semibold text-bleuone">Sujet :</span> {{ $currentLesson['subject'] }}</p>
                                    </div>
                                </article>
                            </section>

                            <section class="rounded-[24px] border border-orange-200 bg-orange-50/60 p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-orangeone">A verifier a la fin de la lecon</p>
                                <div class="mt-5 grid gap-3 md:grid-cols-2">
                                    @foreach ($editorial['checklist'] as $item)
                                        <div class="flex items-start gap-3 rounded-[18px] bg-white/80 px-4 py-4">
                                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <p class="text-sm leading-7 text-slate-700">{{ $item }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <section class="rounded-[24px] border border-slate-200 bg-white p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">En attendant le SCORM</p>
                                <p class="mt-3 text-sm leading-7 text-slate-600 md:text-base">{{ $editorial['placeholder_note'] }}</p>
                                <div class="mt-5 grid gap-4 md:grid-cols-2">
                                    <article class="rounded-[20px] border border-slate-200 bg-slate-50/70 p-5">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Activite prevue</p>
                                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $currentLesson['activity'] }}</p>
                                    </article>
                                    <article class="rounded-[20px] border border-slate-200 bg-slate-50/70 p-5">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ressources a preparer</p>
                                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $currentLesson['resources'] }}</p>
                                    </article>
                                </div>
                            </section>
                        </div>

                        <div class="border-t border-gray-100 bg-white px-6 py-5 md:px-8">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-3">
                                    <a
                                        href="{{ $currentChapter['url'] }}"
                                        class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
                                    >
                                        Revenir au chapitre
                                    </a>

                                    @if ($previousLesson)
                                        <a
                                            href="{{ $previousLesson['url'] }}"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
                                        >
                                            Lecon precedente
                                        </a>
                                    @endif
                                </div>

                                @if ($nextActivity)
                                    <a href="{{ $nextActivity['url'] }}" class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm">
                                        {{ $nextActivity['button_label'] ?? 'Realiser l activite' }}
                                    </a>
                                @elseif ($nextLesson)
                                    <a href="{{ $nextLesson['url'] }}" class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm">
                                        Lecon suivante
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @endif
        </section>
    </div>

    @if (!empty($currentLesson['scorm_url']))
        <div
            id="next-lesson-wrapper"
            class="hidden pointer-events-none fixed bottom-10 z-50 transition-all duration-300"
            style="left: 50%; transform: translateX(-50%);"
        >
            <button
                id="next-lesson-button"
                type="button"
                class="pointer-events-auto cursor-pointer rounded-full bg-[#E94D2A] px-6 py-2.5 text-white opacity-0 transition-all duration-300 hover:opacity-90"
            >
                <span id="next-button-text">Leçon suivante</span>
            </button>
        </div>
    @endif
@endsection
