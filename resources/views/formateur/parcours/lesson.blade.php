@extends('formateur.parcours.layout')

@section('hide_parcours_header', 'true')

@php
    $customPresentation = $currentLesson['custom_presentation'] ?? null;
    $lessonLayout = $currentLesson['layout'] ?? 'default';
    $isMixedScormForm = $lessonLayout === 'scorm_form';
    $activeLessonPart = $activeLessonPart ?? null;
    $mixedParts = $currentLesson['scorm_parts'] ?? [];
    $mixedPartKeys = array_keys($mixedParts);
    $mixedPartUrls = $currentLesson['part_urls'] ?? [];
    $mixedCurrentIndex = array_search($activeLessonPart, $mixedPartKeys, true);
    $mixedNextPart = $mixedCurrentIndex !== false ? ($mixedPartKeys[$mixedCurrentIndex + 1] ?? null) : null;
    $mixedActivePartConfig = $activeLessonPart ? ($mixedParts[$activeLessonPart] ?? []) : [];
    $mixedActiveScormUrl = $activeLessonPart ? ($currentLesson['scorm_part_urls'][$activeLessonPart] ?? null) : null;
    $mixedNextUrl = $mixedNextPart
        ? ($mixedPartUrls[$mixedNextPart] ?? '#')
        : ($nextLesson['url'] ?? '#');
    $mixedIsFullPart = ($mixedActivePartConfig['height'] ?? null) === 'full';
    $mixedIsFormOnlyPart = ($mixedActivePartConfig['height'] ?? null) === 'form_only';
    $mixedHasForm = !empty($mixedActivePartConfig['form']);
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
                'title' => 'Activité prévue',
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
        'placeholder_note' => 'Cette leçon est actuellement proposée sous une forme éditoriale provisoire.',
    ];
@endphp

@section('parcours_content')
    <div
        x-data="{
            sidebarOpen: window.innerWidth >= 1024,
            sidebarClosing: false,
            fullscreenSupported: false,
            fullscreenActive: false,
            toggleSidebar() {
                if (this.sidebarOpen) {
                    this.sidebarClosing = true;
                    this.sidebarOpen = false;
                    window.setTimeout(() => {
                        this.sidebarClosing = false;
                    }, 260);
                    return;
                }

                this.sidebarOpen = true;
            },
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
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        this.sidebarOpen = true;
                    }
                });
            }
        }"
        class="space-y-2 lg:space-y-3"
    >
        <div class="flex items-start gap-2 px-2 pt-1 sm:px-3 lg:px-4">
            <button
                type="button"
                @click="toggleSidebar()"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:border-orangeone hover:text-orangeone lg:h-9 lg:w-9"
                :aria-pressed="sidebarOpen.toString()"
                aria-label="Afficher ou masquer le plan"
                title="Afficher ou masquer le plan"
            >
                <svg class="h-3.5 w-3.5 lg:h-4 lg:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 6h16" />
                    <path d="M4 12h16" />
                    <path d="M4 18h16" />
                </svg>
            </button>

            <div class="min-w-0 flex-1">
                <x-formateur.hierarchy-breadcrumb
                    :module="['label' => 'Module', 'title' => $currentModule['title'], 'url' => $currentModule['url']]"
                    :chapter="['label' => $currentChapter['label'] ?? 'Chapitre', 'title' => $currentChapter['title'], 'url' => $currentChapter['url']]"
                    :lesson="['label' => 'Leçon', 'title' => $currentLesson['title'], 'url' => null]"
                />
            </div>
        </div>
	    @if (!empty($currentLesson['scorm_url']) || !empty($currentLesson['intro_scorm_url']) || !empty($mixedActiveScormUrl))
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script src="{{ asset('scorm_core/js/API.js') }}"></script>
        <script>
            window.SCORM_CONTEXT = {
                lecture_id: 0,
                module_id: @json($activeModuleKey),
                section_id: @json($activeChapterKey),
	                next_url: @json($mixedNextUrl),
                final_url: @json($currentChapter['url']),
                is_already_done: false,
                anonymous: false,
                read_only: false,
                force_next_lesson: @json(empty($nextActivity)),
                quiz_start_url: @json($nextActivity['url'] ?? null),
                quiz_button_label: @json($nextActivity['button_label'] ?? null),
                hide_next_button: @json((bool) ($currentLesson['hide_scorm_next_button'] ?? false)),
		                next_button_label: @json($mixedNextPart ? 'Continuer' : ($nextLesson ? 'Leçon suivante' : 'Retour au chapitre')),
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
        class="grid items-start gap-6"
        :class="(sidebarOpen || sidebarClosing) ? 'lg:grid-cols-[19rem_minmax(0,1fr)]' : 'lg:grid-cols-[minmax(0,1fr)]'"
    >
        @include('formateur.parcours.partials.sidebar')

        <section
            x-ref="lessonViewport"
            class="relative h-[calc(100vh-4rem)] min-h-[calc(100vh-4rem)] rounded-[28px] border border-gray-100 bg-gray-100 shadow-sm lg:h-[calc(100vh-4.5rem)] lg:min-h-[calc(100vh-4.5rem)] {{ $isMixedScormForm ? 'overflow-y-auto' : 'overflow-hidden' }}"
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

            @if ($isMixedScormForm)
                <div class="{{ $mixedIsFullPart ? 'h-full' : 'space-y-4 p-4 md:p-5' }}">
                    @if ($mixedIsFullPart)
                        <div class="h-full overflow-hidden bg-white">
                            <div class="h-full min-h-[calc(100vh-4rem)] bg-slate-100 lg:min-h-[calc(100vh-4.5rem)]">
                                @if (!empty($mixedActiveScormUrl))
                                    <iframe
                                        id="scorm-iframe-{{ $activeLessonPart }}"
                                        title="{{ $currentLesson['title'] }}"
                                        src="{{ $mixedActiveScormUrl }}"
                                        frameborder="0"
                                        allowfullscreen
                                        class="block h-full w-full bg-white"
                                    ></iframe>
                                @else
                                    <div class="flex h-full items-center justify-center p-6 text-center">
                                        <div class="max-w-xl rounded-[24px] border border-orangeone/20 bg-white px-8 py-7 shadow-sm">
                                            <p class="text-xs font-black uppercase tracking-[0.24em] text-orangeone">SCORM a integrer</p>
                                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                                Déposez le paquet Storyline dans le dossier prévu pour afficher le contenu ici.
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif ($mixedIsFormOnlyPart)
                        {{-- Partie simulateur pur : le SCORM precedent porte les consignes audio. --}}
                    @else
                    <div class="overflow-hidden rounded-[24px] border border-gray-100 bg-white shadow-sm">
                        <div class="h-[38vh] min-h-[300px] bg-slate-100">
                            @if (!empty($mixedActiveScormUrl))
                                <iframe
                                    id="scorm-iframe-{{ $activeLessonPart }}"
                                    title="{{ $currentLesson['title'] }}"
                                    src="{{ $mixedActiveScormUrl }}"
                                    frameborder="0"
                                    allowfullscreen
                                    class="block h-full w-full bg-white"
                                ></iframe>
                            @else
                                <div class="flex h-full items-center justify-center p-6 text-center">
                                    <div class="max-w-xl">
                                        <p class="text-xs font-black uppercase tracking-[0.24em] text-orangeone">SCORM a integrer</p>
                                        <p class="mt-3 text-sm leading-7 text-slate-600">
                                            Déposez le paquet Storyline dans le dossier prévu pour afficher le contenu ici.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @endif

                    @if ($mixedHasForm)
                        @if (($mixedActivePartConfig['form'] ?? null) === 'group_creation')
                            @include('formateur.parcours.partials.lessons.group-creation-form')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'path_creation')
                            @include('formateur.parcours.partials.lessons.path-creation-form')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'path_creation_finalisation')
                            @include('formateur.parcours.partials.lessons.path-creation-finalisation')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'stagiaires_index')
                            @include('formateur.parcours.partials.lessons.stagiaires-index')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'stagiaire_create')
                            @include('formateur.parcours.partials.lessons.stagiaire-create')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'marc_unlock_simulator')
                            @include('formateur.parcours.partials.lessons.marc-unlock-simulator')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'marc_students_table')
                            @include('formateur.parcours.partials.lessons.marc-students-table')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'marc_profile_message')
                            @include('formateur.parcours.partials.lessons.marc-profile-message')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'marc_unlock_results')
                            @include('formateur.parcours.partials.lessons.marc-unlock-results')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'content_modification_intro')
                            @include('formateur.parcours.partials.lessons.content-modification-intro')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'content_modification_group_edit')
                            @include('formateur.parcours.partials.lessons.content-modification-group-edit')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'content_modification_results')
                            @include('formateur.parcours.partials.lessons.content-modification-results')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'module_2_final_results')
                            @include('formateur.parcours.partials.lessons.module-2-final-results')
                        @elseif (($mixedActivePartConfig['form'] ?? null) === 'module_2_usability_questionnaire')
                            @include('formateur.parcours.partials.lessons.module-2-usability-questionnaire')
                        @endif
                    @endif
                </div>
            @elseif (!empty($currentLesson['scorm_url']))
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
                                <span class="inline-flex rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    {{ $currentLesson['duration_label'] }}
                                </span>
                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    {{ $currentChapter['title'] }}
                                </span>
                                <span
                                    class="inline-flex rounded-full border border-bleuone/10 bg-bleuone/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-bleuone"
                                    data-parcours-tooltip="{{ ($currentLesson['type'] ?? 'objectif') === 'bilan' ? 'Bilan' : 'Objectif opérationnel' }}"
                                >
	                                    {{ ($currentLesson['type'] ?? 'objectif') === 'bilan' ? 'Bilan' : 'Leçon' }}
                                </span>
                            </div>

                            <h1 class="mt-5 font-raleway text-3xl font-medium leading-tight text-bleuone md:text-4xl">
                                {{ $currentLesson['title'] }}
                            </h1>

                            <p class="mt-5 max-w-4xl text-base leading-8 text-slate-600 md:text-lg">
                                {{ $currentLesson['objective'] }}
                            </p>
                        </div>

                        <div class="space-y-6 px-6 py-6 md:px-8 md:py-8">
                            <section class="rounded-[28px] border-2 border-dashed border-orange-200 bg-orange-50/40 p-8 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-orangeone shadow-sm">
                                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h6M9 13h4" />
                                    </svg>
                                </div>
                                <p class="mt-5 text-xs font-black uppercase tracking-[0.24em] text-orangeone">
                                    {{ $currentLesson['scorm_slot_label'] ?? 'Contenu de leçon' }}
                                </p>
                                <h2 class="mt-3 font-raleway text-2xl font-medium leading-tight text-bleuone">
                                    Contenu de leçon à intégrer
                                </h2>
                                <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600 md:text-base">
	                                    Cet emplacement reste volontairement vide pour accueillir le contenu de cette leçon.
                                </p>
                            </section>

                            <section class="grid gap-4 md:grid-cols-2">
                                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Activité prévue</p>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $currentLesson['activity'] ?: 'Activité à créer ultérieurement.' }}</p>

                                    @if (!empty($nextActivity))
                                        <a href="{{ $nextActivity['url'] }}" class="mt-5 inline-flex items-center justify-center rounded-full bg-orangeone px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">
                                            {{ $nextActivity['button_label'] ?? 'Réaliser l’activité' }}
                                        </a>
                                    @endif
                                </article>

                                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Evaluation / validation</p>
                                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $currentLesson['evaluation'] ?: 'Critere de validation a preciser.' }}</p>
                                </article>
                            </section>

                            <section class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-6">
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Repere pedagogique</p>
                                <div class="mt-4 grid gap-4 text-sm leading-7 text-slate-600 md:grid-cols-2">
                                    <p><span class="font-semibold text-bleuone">Intention :</span> {{ $currentLesson['pedagogical_intention'] }}</p>
                                    <p><span class="font-semibold text-bleuone">Methode :</span> {{ $currentLesson['method'] }}</p>
                                    <p><span class="font-semibold text-bleuone">Processus :</span> {{ $currentLesson['learning_process'] }}</p>
                                    <p><span class="font-semibold text-bleuone">Sujet :</span> {{ $currentLesson['subject'] }}</p>
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
                                            Leçon précédente
                                        </a>
                                    @endif
                                </div>

                                @if ($nextActivity)
                                    <a href="{{ $nextActivity['url'] }}" class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm">
                                        {{ $nextActivity['button_label'] ?? 'Réaliser l’activité' }}
                                    </a>
                                @elseif ($nextLesson)
                                    <a href="{{ $nextLesson['url'] }}" class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm">
                                        Leçon suivante
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @endif
        </section>
    </div>
    </div>

    @if (!empty($currentLesson['scorm_url']) && ! $isMixedScormForm)
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
