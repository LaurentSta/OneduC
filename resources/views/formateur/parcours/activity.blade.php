@extends('formateur.parcours.layout')

@php
    $activityDropzones = $currentActivity['dropzones'] ?? [];
    $activityCards = $currentActivity['items'] ?? [];
    $nextNavigationUrl = $nextLesson['url'] ?? $currentChapter['url'];
    $activitySubmitUrl = route('formateur.parcours.activities.submit', [
        'module' => $activeModuleKey,
        'chapter' => $activeChapterKey,
        'lesson' => $activeLessonKey,
        'activity' => $activeActivityKey,
    ]);
@endphp

@section('parcours_content')
    <div
        x-data="parcoursSortingActivity({
            cards: @js($activityCards),
            dropzones: @js($activityDropzones),
            submitUrl: @js($activitySubmitUrl),
            nextUrl: @js($nextNavigationUrl),
            lessonUrl: @js($currentLesson['url']),
            initialPlacements: @js($initialPlacements ?? []),
            completed: @js($activityCompleted ?? false),
            successMessage: @js($currentActivity['success_message'] ?? 'Bravo, l activite est validee.'),
        })"
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

            <div class="h-full overflow-y-auto p-4 md:p-6">
                <article class="mx-auto max-w-6xl overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-6 pb-3 pt-6 md:px-8 md:pb-4 md:pt-8">
                        <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
                            {{ $currentActivity['title'] }}
                        </h1>
                    </div>

                    <div class="space-y-8 px-6 pb-6 pt-4 md:px-8 md:pb-8 md:pt-4">
                        <section>
                            <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orangeone text-white shadow-md">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h7" />
                                        </svg>
                                    </span>
                                    <h2 class="text-lg font-black text-bleuone">Consigne</h2>
                                </div>

                                <ol class="mt-5 space-y-3">
                                    <li class="flex items-start gap-4">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-sm font-black text-orangeone shadow-sm ring-1 ring-orange-100">1</span>
                                        <p class="pt-0.5 text-sm leading-6 text-slate-600">Glissez chaque bloc dans la bonne categorie.</p>
                                    </li>
                                    <li class="flex items-start gap-4">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-sm font-black text-orangeone shadow-sm ring-1 ring-orange-100">2</span>
                                        <p class="pt-0.5 text-sm leading-6 text-slate-600">Rangez tous les elements, puis validez l activite.</p>
                                    </li>
                                </ol>
                            </article>
                        </section>

                        <section class="grid gap-4 xl:grid-cols-3">
                            @foreach ($activityDropzones as $dropzone)
                                @php
                                    $dropzoneTheme = match ($dropzone['id']) {
                                        'information' => [
                                            'surface' => 'border-sky-100 bg-sky-50/60',
                                            'well' => 'border-sky-200/80 bg-sky-100/35',
                                            'eyebrow' => 'text-sky-700',
                                        ],
                                        'stagiaire' => [
                                            'surface' => 'border-emerald-100 bg-emerald-50/60',
                                            'well' => 'border-emerald-200/80 bg-emerald-100/35',
                                            'eyebrow' => 'text-emerald-700',
                                        ],
                                        'module' => [
                                            'surface' => 'border-amber-100 bg-amber-50/60',
                                            'well' => 'border-amber-200/80 bg-amber-100/35',
                                            'eyebrow' => 'text-amber-700',
                                        ],
                                        default => [
                                            'surface' => 'border-slate-200 bg-white',
                                            'well' => 'border-slate-200 bg-slate-50/60',
                                            'eyebrow' => 'text-slate-500',
                                        ],
                                    };
                                @endphp
                                <article
                                    class="rounded-[24px] border p-5 shadow-sm transition {{ $dropzoneTheme['surface'] }}"
                                    :class="zoneClasses('{{ $dropzone['id'] }}')"
                                    @dragover.prevent="allowDrop($event)"
                                    @drop.prevent="handleZoneDrop($event, '{{ $dropzone['id'] }}')"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] {{ $dropzoneTheme['eyebrow'] }}">{{ $dropzone['label'] }}</p>
                                            <h2 class="mt-2 text-xl font-black text-bleuone">{{ $dropzone['label'] }}</h2>
                                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $dropzone['description'] }}</p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="assignSelected('{{ $dropzone['id'] }}')"
                                            class="inline-flex items-center justify-center rounded-full border border-slate-300 px-3 py-2 text-xs font-bold uppercase tracking-wide text-slate-600 transition hover:border-orangeone hover:text-orangeone disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="!selectedCardId || completed"
                                        >
                                            Placer ici
                                        </button>
                                    </div>

                                    <div class="mt-5 min-h-[220px] rounded-[20px] border border-dashed p-4 {{ $dropzoneTheme['well'] }}">
                                        <div class="flex flex-wrap gap-3">
                                            <template x-if="itemsForZone('{{ $dropzone['id'] }}').length === 0">
                                                <div class="rounded-[18px] border border-dashed border-slate-200 bg-white px-4 py-3 text-sm text-slate-400">
                                                    Deposez ici les elements de cette categorie.
                                                </div>
                                            </template>

                                            <template x-for="card in itemsForZone('{{ $dropzone['id'] }}')" :key="'{{ $dropzone['id'] }}-' + card.id">
                                                <button
                                                    type="button"
                                                    draggable="true"
                                                    @dragstart="startDrag(card.id)"
                                                    @dragend="endDrag()"
                                                    @click="toggleSelection(card.id)"
                                                    class="inline-flex min-h-[52px] items-center gap-2 rounded-[18px] border px-4 py-3 text-left text-sm font-semibold shadow-sm transition"
                                                    :class="cardClasses(card.id, false)"
                                                >
                                                    <span class="min-w-0 flex-1" x-text="card.label"></span>
                                                    <span
                                                        x-show="!completed"
                                                        x-cloak
                                                        @click.stop="moveCardToPool(card.id)"
                                                        class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-base font-bold text-slate-400 transition hover:border-orangeone hover:text-orangeone"
                                                    >
                                                        ×
                                                    </span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </section>

                        <section class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-5">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Elements a classer</p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                                    <span class="inline-flex rounded-full bg-white px-3 py-1.5 text-slate-500 ring-1 ring-slate-200">
                                        <span x-text="pool.length"></span>&nbsp;element(s) en attente
                                    </span>
                                    <button
                                        type="button"
                                        @click="resetActivity()"
                                        class="inline-flex items-center justify-center rounded-full border border-slate-300 px-4 py-2 text-slate-600 transition hover:border-orangeone hover:text-orangeone"
                                    >
                                        Reinitialiser
                                    </button>
                                </div>
                            </div>

                            <div
                                class="mt-5 rounded-[22px] border border-dashed bg-white p-4 transition"
                                :class="missingItemIds.length > 0 ? 'border-orangeone ring-2 ring-orange-100' : 'border-slate-200'"
                                @dragover.prevent="allowDrop($event)"
                                @drop.prevent="handlePoolDrop($event)"
                            >
                                <div class="flex flex-wrap gap-3">
                                    <template x-if="pool.length === 0">
                                        <div class="rounded-[18px] border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                            Tous les elements ont ete deplaces. Verifiez les trois blocs puis validez.
                                        </div>
                                    </template>

                                    <template x-for="card in itemsForPool()" :key="'pool-' + card.id">
                                        <button
                                            type="button"
                                            draggable="true"
                                            @dragstart="startDrag(card.id)"
                                            @dragend="endDrag()"
                                            @click="toggleSelection(card.id)"
                                            class="inline-flex min-h-[52px] items-center rounded-[18px] border px-4 py-3 text-left text-sm font-semibold shadow-sm transition"
                                            :class="cardClasses(card.id, true)"
                                        >
                                            <span x-text="card.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </section>

                        <div
                            x-show="message"
                            x-cloak
                            class="rounded-[24px] border px-5 py-4"
                            :class="completed ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-orange-200 bg-orange-50 text-orange-900'"
                        >
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-full text-sm font-black"
                                    :class="completed ? 'bg-emerald-600 text-white' : 'bg-orangeone text-white'">
                                    <span x-text="completed ? '✓' : '!'"></span>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold" x-text="completed ? 'Activite validee' : 'Ajustement necessaire'"></p>
                                    <p class="mt-1 text-sm leading-6" x-text="message"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 bg-white px-6 py-5 md:px-8">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-wrap items-center gap-3">
                                <a
                                    href="{{ $currentLesson['url'] }}"
                                    class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
                                >
                                    Revenir a la lecon
                                </a>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-sm text-slate-500" x-show="selectedCardId && !completed" x-cloak>
                                    Bloc selectionne : <span class="font-semibold text-bleuone" x-text="selectedCardLabel()"></span>
                                </p>

                                <button
                                    type="button"
                                    @click="validate()"
                                    class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm disabled:!cursor-not-allowed disabled:!opacity-60"
                                    :disabled="submitting || completed"
                                >
                                    <span x-text="submitting ? 'Validation...' : 'Valider l activite'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
            <div
                id="next-lesson-wrapper"
                x-cloak
                x-show="completed"
                class="fixed bottom-10 z-50 transition-all duration-300"
                style="left: 50%; transform: translateX(-50%);"
            >
                <a
                    :href="nextUrl"
                    id="next-lesson-button"
                    class="oneduc-btn-alert inline-flex items-center gap-2 rounded-full bg-[#E94D2A] px-6 py-2.5 text-white transition-all duration-300 hover:opacity-90"
                >
                    <span id="next-button-text">Leçon suivante</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </section>
    </div>

    <script>
        window.parcoursSortingActivity = function (config) {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                fullscreenSupported: false,
                fullscreenActive: false,
                cards: Array.isArray(config.cards) ? config.cards : [],
                dropzones: Array.isArray(config.dropzones) ? config.dropzones : [],
                submitUrl: config.submitUrl || '',
                nextUrl: config.nextUrl || '#',
                lessonUrl: config.lessonUrl || '#',
                successMessage: config.successMessage || 'Bravo, l activite est validee.',
                placements: {},
                pool: [],
                selectedCardId: null,
                draggedCardId: null,
                wrongItemIds: [],
                missingItemIds: [],
                message: '',
                completed: Boolean(config.completed),
                submitting: false,

                init() {
                    this.fullscreenSupported = !!document.fullscreenEnabled;
                    this.syncFullscreenState();
                    document.addEventListener('fullscreenchange', () => this.syncFullscreenState());
                    window.addEventListener('resize', () => {
                        if (window.innerWidth >= 1024) {
                            this.sidebarOpen = true;
                        }
                    });

                    this.placements = this.normalizePlacements(config.initialPlacements || {});
                    this.rebuildPool();
                    this.message = this.completed ? this.successMessage : '';
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
                        console.error('Impossible de basculer en plein ecran.', error);
                    }
                },

                syncFullscreenState() {
                    this.fullscreenActive = !!document.fullscreenElement;
                },

                normalizePlacements(rawPlacements) {
                    const normalized = {};
                    const validIds = new Set(this.cards.map((card) => card.id));

                    this.dropzones.forEach((zone) => {
                        const rawZoneItems = Array.isArray(rawPlacements?.[zone.id]) ? rawPlacements[zone.id] : [];
                        normalized[zone.id] = [...new Set(rawZoneItems.filter((itemId) => validIds.has(itemId)))];
                    });

                    return normalized;
                },

                rebuildPool() {
                    const placedIds = new Set(Object.values(this.placements).flat());
                    this.pool = this.cards
                        .map((card) => card.id)
                        .filter((cardId) => !placedIds.has(cardId));
                },

                selectedCardLabel() {
                    const card = this.cardById(this.selectedCardId);
                    return card ? card.label : '';
                },

                cardById(cardId) {
                    return this.cards.find((card) => card.id === cardId) || null;
                },

                itemsForPool() {
                    return this.pool
                        .map((cardId) => this.cardById(cardId))
                        .filter(Boolean);
                },

                itemsForZone(zoneId) {
                    return (this.placements[zoneId] || [])
                        .map((cardId) => this.cardById(cardId))
                        .filter(Boolean);
                },

                cardClasses(cardId, fromPool) {
                    if (this.completed) {
                        return 'border-emerald-200 bg-emerald-50 text-bleuone';
                    }

                    if (this.wrongItemIds.includes(cardId)) {
                        return 'border-orangeone bg-orange-50 text-bleuone ring-2 ring-orange-100';
                    }

                    if (this.selectedCardId === cardId) {
                        return 'border-bleuone bg-blue-50 text-bleuone ring-2 ring-blue-100';
                    }

                    return fromPool
                        ? 'border-slate-200 bg-white text-bleuone hover:border-bleuone hover:bg-blue-50/40'
                        : 'border-slate-200 bg-white text-bleuone hover:border-bleuone hover:bg-blue-50/40';
                },

                zoneClasses(zoneId) {
                    if (this.completed) {
                        return 'ring-1 ring-emerald-100';
                    }

                    return this.hasWrongItemsInZone(zoneId)
                        ? 'border-orangeone ring-2 ring-orange-100'
                        : 'hover:border-slate-300';
                },

                hasWrongItemsInZone(zoneId) {
                    return (this.placements[zoneId] || []).some((cardId) => this.wrongItemIds.includes(cardId));
                },

                toggleSelection(cardId) {
                    if (this.completed) {
                        return;
                    }

                    this.selectedCardId = this.selectedCardId === cardId ? null : cardId;
                },

                startDrag(cardId) {
                    if (this.completed) {
                        return;
                    }

                    this.draggedCardId = cardId;
                    this.selectedCardId = cardId;
                },

                endDrag() {
                    this.draggedCardId = null;
                },

                allowDrop(event) {
                    event.preventDefault();
                },

                clearFeedback() {
                    this.wrongItemIds = [];
                    this.missingItemIds = [];

                    if (!this.completed) {
                        this.message = '';
                    }
                },

                removeFromCurrentLocation(cardId) {
                    this.pool = this.pool.filter((id) => id !== cardId);

                    this.dropzones.forEach((zone) => {
                        this.placements[zone.id] = (this.placements[zone.id] || []).filter((id) => id !== cardId);
                    });
                },

                moveCardToZone(cardId, zoneId) {
                    if (this.completed || !zoneId) {
                        return;
                    }

                    this.removeFromCurrentLocation(cardId);
                    this.placements[zoneId] = [...(this.placements[zoneId] || []), cardId];
                    this.selectedCardId = null;
                    this.clearFeedback();
                },

                moveCardToPool(cardId) {
                    if (this.completed) {
                        return;
                    }

                    this.removeFromCurrentLocation(cardId);
                    this.pool = [...this.pool, cardId];
                    this.selectedCardId = null;
                    this.clearFeedback();
                },

                handlePoolDrop(event) {
                    event.preventDefault();

                    if (this.draggedCardId) {
                        this.moveCardToPool(this.draggedCardId);
                    }

                    this.endDrag();
                },

                handleZoneDrop(event, zoneId) {
                    event.preventDefault();

                    if (this.draggedCardId) {
                        this.moveCardToZone(this.draggedCardId, zoneId);
                    }

                    this.endDrag();
                },

                assignSelected(zoneId) {
                    if (!this.selectedCardId || this.completed) {
                        return;
                    }

                    this.moveCardToZone(this.selectedCardId, zoneId);
                },

                serializePlacements() {
                    const payload = {};

                    this.dropzones.forEach((zone) => {
                        payload[zone.id] = [...(this.placements[zone.id] || [])];
                    });

                    return payload;
                },

                async validate() {
                    if (this.submitting || this.completed) {
                        return;
                    }

                    this.submitting = true;

                    try {
                        const response = await fetch(this.submitUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.content || '',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                placements: this.serializePlacements(),
                            }),
                        });

                        const payload = await response.json();

                        this.wrongItemIds = Array.isArray(payload.wrong_item_ids) ? payload.wrong_item_ids : [];
                        this.missingItemIds = Array.isArray(payload.missing_item_ids) ? payload.missing_item_ids : [];
                        this.message = payload.message || '';

                        if (payload.success) {
                            this.completed = true;
                            this.selectedCardId = null;
                            this.wrongItemIds = [];
                            this.missingItemIds = [];
                        }
                    } catch (error) {
                        console.error('Impossible de valider l activite.', error);
                        this.message = 'Une erreur est survenue pendant la validation. Reessayez dans un instant.';
                    } finally {
                        this.submitting = false;
                    }
                },

                resetActivity() {
                    this.completed = false;
                    this.selectedCardId = null;
                    this.message = '';
                    this.wrongItemIds = [];
                    this.missingItemIds = [];
                    this.placements = this.normalizePlacements({});
                    this.rebuildPool();
                },
            };
        };
    </script>
@endsection
