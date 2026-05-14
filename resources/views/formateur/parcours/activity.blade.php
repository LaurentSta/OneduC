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

    $zoneThemes = [
        'information' => [
            'header' => 'bg-bleuone',
            'border' => 'border-sky-200',
            'well'   => 'border-sky-200/80 bg-sky-50/60',
        ],
        'stagiaire' => [
            'header' => 'bg-vertone',
            'border' => 'border-teal-200',
            'well'   => 'border-teal-200/80 bg-teal-50/60',
        ],
        'module' => [
            'header' => 'bg-orangeone',
            'border' => 'border-orange-200',
            'well'   => 'border-orange-200/80 bg-orange-50/60',
        ],
    ];
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
        class="space-y-4"
    >
        {{-- ===== MODAL DE FIN D'ACTIVITÉ ===== --}}
        <div
            x-show="showCompletionModal"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,44,63,0.55);"
            @click.self="closeModal()"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-md overflow-hidden rounded-[28px] bg-white shadow-2xl"
                @click.stop
            >
                {{-- Bouton fermer --}}
                <button
                    type="button"
                    @click="closeModal()"
                    class="absolute right-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white transition hover:bg-white/35"
                    aria-label="Fermer"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- En-tête : couleur et contenu selon variante --}}
                <div
                    class="px-6 pt-6 pb-5 text-white"
                    :class="{
                        'bg-vertone':   completionVariant === 'A',
                        'bg-orangeone': completionVariant === 'B',
                        'bg-bleuone':   completionVariant === 'C',
                    }"
                >
                    <div class="flex items-start gap-4">
                        {{-- Icône personnage (remplacez par une illustration si disponible) --}}
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 shadow-inner">
                            {{-- Variante A : souriant --}}
                            <svg x-show="completionVariant === 'A'" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            {{-- Variante B : pouce levé --}}
                            <svg x-show="completionVariant === 'B'" x-cloak class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                            </svg>
                            {{-- Variante C : accompagnement --}}
                            <svg x-show="completionVariant === 'C'" x-cloak class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1 pr-8">
                            <p class="text-xs font-semibold uppercase tracking-widest text-white/65">Resultat</p>
                            <h2 class="font-raleway text-xl font-bold leading-tight">
                                <span x-show="completionVariant === 'A'">Bravo !</span>
                                <span x-show="completionVariant === 'B'" x-cloak>C'est noté !</span>
                                <span x-show="completionVariant === 'C'" x-cloak>Reprenons ensemble</span>
                            </h2>
                        </div>
                    </div>

                    {{-- Message voix-off --}}
                    <div class="mt-4 text-sm leading-7 text-white/90">
                        <p x-show="completionVariant === 'A'">
                            Bravo, vous avez identifié du premier coup les 3 grandes étapes pour démarrer dans Onéduc.
                            Les informations posent le cadre. Les stagiaires donnent vie au groupe. Les modules organisent le contenu.
                            Vous êtes prêt pour la suite.
                        </p>
                        <p x-show="completionVariant === 'B'" x-cloak>
                            C'est noté ! Vous avez identifié les 3 grandes étapes pour démarrer dans Onéduc.
                            Quelques cartes vous ont fait hésiter — c'est normal, elles seront détaillées dans la prochaine leçon.
                            Les informations posent le cadre. Les stagiaires donnent vie au groupe. Les modules organisent le contenu.
                        </p>
                        <p x-show="completionVariant === 'C'" x-cloak>
                            Pas de souci, ces 3 étapes ne sont pas toujours évidentes au premier coup.
                            Reprenons ensemble : les informations posent le cadre du groupe, les stagiaires sont les participants, les modules sont les contenus à proposer.
                            Vous pouvez refaire l'activité, ou continuer — la prochaine leçon va tout éclaircir.
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-3 px-6 pb-6 pt-5">
                    <a
                        :href="lessonUrl"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border-2 border-bleuone bg-white px-5 py-3 text-sm font-bold text-bleuone transition hover:bg-bleuone hover:text-white"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Revoir la lecon
                    </a>

                    <button
                        type="button"
                        @click="resetActivity()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-orangeone hover:text-orangeone"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recommencer l activite
                    </button>

                    <a
                        :href="nextUrl"
                        class="btn-oneduc !w-full !justify-center !rounded-full !py-3 !text-sm"
                    >
                        Leçon suivante
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        {{-- ===== FIN MODAL ===== --}}

        <div class="flex items-start gap-3 px-4 pt-4 sm:px-6 lg:px-8">
            <button
                type="button"
                @click="toggleSidebar()"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 shadow-sm transition hover:border-orangeone hover:text-orangeone"
                :aria-pressed="sidebarOpen.toString()"
                aria-label="Afficher ou masquer le plan"
                title="Afficher ou masquer le plan"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 6h16" />
                    <path d="M4 12h16" />
                    <path d="M4 18h16" />
                </svg>
            </button>

            <div class="min-w-0 flex-1">
                <x-formateur.hierarchy-breadcrumb
                    :module="['label' => 'Module', 'title' => $currentModule['title'], 'url' => $currentModule['url']]"
                    :chapter="['label' => $currentChapter['label'] ?? 'Chapitre', 'title' => $currentChapter['title'], 'url' => $currentChapter['url']]"
                    :lesson="['label' => 'Lecon', 'title' => $currentLesson['title'], 'url' => $currentLesson['url']]"
                    :activity="['label' => 'Activite', 'title' => $currentActivity['title'], 'url' => null]"
                />
            </div>
        </div>

        <div
            class="grid items-start gap-6"
            :class="(sidebarOpen || sidebarClosing) ? 'lg:grid-cols-[19rem_minmax(0,1fr)]' : 'lg:grid-cols-[minmax(0,1fr)]'"
        >
            @include('formateur.parcours.partials.sidebar')

            <section
                x-ref="lessonViewport"
                class="relative h-[calc(100vh-13rem)] min-h-[calc(100vh-13rem)] overflow-hidden rounded-[28px] border border-gray-100 bg-gray-100 shadow-sm"
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
                        <span x-text="fullscreenActive ? 'Quitter mode plein ecran' : 'Mode plein ecran'"></span>
                    </button>
                </div>

                <div class="h-full overflow-y-auto p-4 md:p-6">
                    <article class="w-full overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-sm">

                        {{-- En-tête --}}
                        <div class="border-b border-gray-100 px-6 pb-3 pt-6 md:px-8 md:pb-4 md:pt-8">
                            <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
                                {{ $currentActivity['title'] }}
                            </h1>
                        </div>

                        <div class="space-y-6 px-6 pb-6 pt-5 md:px-8 md:pb-8">

                            {{-- Scénario fil rouge (optionnel) --}}
                            @if (!empty($currentActivity['scenario']))
                                <section class="flex items-start gap-3 rounded-[20px] border border-orangeone/20 bg-orange-50/60 px-5 py-4">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-orangeone text-white shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold text-orangeone uppercase tracking-wide">Cas concret</p>
                                        <p class="mt-1 text-base leading-7 text-slate-700">{{ $currentActivity['scenario'] }}</p>
                                    </div>
                                </section>
                            @endif

                            {{-- Consigne --}}
                            <section class="flex items-start gap-3 rounded-[20px] border border-bleuone/15 bg-bleuone/[0.04] px-5 py-4">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-bleuone text-white shadow-sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h7" />
                                    </svg>
                                </span>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-base font-bold text-bleuone">Consigne</p>
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-semibold transition"
                                            :class="completed
                                                ? 'border-teal-200 bg-teal-50 text-teal-700'
                                                : (failedAttempts >= 2
                                                    ? 'border-orange-200 bg-orange-50 text-orangeone'
                                                    : 'border-bleuone/20 bg-white text-bleuone')"
                                        >
                                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span x-show="!completed" x-text="(3 - failedAttempts) > 0 ? (3 - failedAttempts) + ' essai(s) restant(s)' : 'Aucun essai restant'"></span>
                                            <span x-show="completed" x-cloak>Activité réussie</span>
                                        </span>
                                    </div>
                                    <p class="mt-1 text-base leading-7 text-slate-600">
                                        Glissez ou sélectionnez chaque élément, puis déposez-le dans la bonne catégorie.
                                        Rangez tous les éléments avant de valider.
                                    </p>
                                </div>
                            </section>

                            {{-- Zones de tri --}}
                            <section class="grid gap-4 xl:grid-cols-3">
                                @foreach ($activityDropzones as $dropzone)
                                    @php
                                        $theme = $zoneThemes[$dropzone['id']] ?? [
                                            'header' => 'bg-slate-600',
                                            'border' => 'border-slate-200',
                                            'well'   => 'border-slate-200 bg-slate-50/60',
                                        ];
                                    @endphp

                                    <article
                                        class="overflow-hidden rounded-[24px] border shadow-sm transition {{ $theme['border'] }}"
                                        :class="zoneClasses('{{ $dropzone['id'] }}')"
                                        @dragover.prevent="allowDrop($event)"
                                        @drop.prevent="handleZoneDrop($event, '{{ $dropzone['id'] }}')"
                                    >
                                        {{-- En-tête coloré --}}
                                        <div class="{{ $theme['header'] }} px-5 py-4">
                                            <h2 class="text-lg font-black text-white">{{ $dropzone['label'] }}</h2>
                                            <p class="mt-0.5 text-sm leading-5 text-white/75">{{ $dropzone['description'] }}</p>
                                        </div>

                                        {{-- Zone de dépôt --}}
                                        <div class="min-h-[180px] p-4 {{ $theme['well'] }} border-t">
                                            <div class="flex flex-wrap gap-2">
                                                <template x-if="itemsForZone('{{ $dropzone['id'] }}').length === 0">
                                                    <div class="rounded-[16px] border border-dashed border-slate-200 bg-white px-4 py-3 text-base text-slate-400">
                                                        Deposez les elements ici.
                                                    </div>
                                                </template>

                                                <template x-for="card in itemsForZone('{{ $dropzone['id'] }}')" :key="'{{ $dropzone['id'] }}-' + card.id">
                                                    <button
                                                        type="button"
                                                        draggable="true"
                                                        @dragstart="startDrag(card.id)"
                                                        @dragend="endDrag()"
                                                        @click="toggleSelection(card.id)"
                                                        class="inline-flex min-h-[44px] items-center gap-2 rounded-[16px] border px-4 py-2.5 text-left text-[15px] font-semibold shadow-sm transition"
                                                        :class="cardClasses(card.id, false)"
                                                    >
                                                        <span class="min-w-0 flex-1" x-text="card.label"></span>
                                                        <span
                                                            x-show="!completed"
                                                            x-cloak
                                                            @click.stop="moveCardToPool(card.id)"
                                                            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-sm font-bold text-slate-400 transition hover:border-orangeone hover:text-orangeone"
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

                            {{-- Pool d'éléments --}}
                            <section class="rounded-[24px] border border-slate-200 bg-white p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-orangeone text-white">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                            </svg>
                                        </span>
                                        <p class="text-base font-bold text-bleuone">Elements a classer</p>
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-sm font-semibold text-slate-500 ring-1 ring-slate-200">
                                            <span x-text="pool.length"></span>&nbsp;en attente
                                        </span>
                                    </div>

                                    <button
                                        type="button"
                                        @click="resetActivity()"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-orangeone hover:text-orangeone"
                                    >
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Reinitialiser
                                    </button>
                                </div>

                                <div
                                    class="mt-4 rounded-[20px] border border-dashed p-4 transition"
                                    :class="missingItemIds.length > 0 ? 'border-orangeone ring-2 ring-orange-100 bg-orange-50/40' : 'border-slate-200 bg-slate-50/50'"
                                    @dragover.prevent="allowDrop($event)"
                                    @drop.prevent="handlePoolDrop($event)"
                                >
                                    <div class="flex flex-wrap gap-2">
                                        <template x-if="pool.length === 0">
                                            <div class="rounded-[16px] border border-dashed border-slate-200 bg-white px-4 py-3 text-base text-slate-500">
                                                Tous les elements ont ete deplaces. Verifiez les trois zones puis validez.
                                            </div>
                                        </template>

                                        <template x-for="card in itemsForPool()" :key="'pool-' + card.id">
                                            <button
                                                type="button"
                                                draggable="true"
                                                @dragstart="startDrag(card.id)"
                                                @dragend="endDrag()"
                                                @click="toggleSelection(card.id)"
                                                class="inline-flex min-h-[44px] items-center rounded-[16px] border px-4 py-2.5 text-left text-[15px] font-semibold shadow-sm transition"
                                                :class="cardClasses(card.id, true)"
                                            >
                                                <span x-text="card.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </section>

                            {{-- Message de feedback --}}
                            <div
                                x-show="message"
                                x-cloak
                                class="rounded-[20px] border px-5 py-4"
                                :class="completed ? 'border-teal-200 bg-teal-50 text-teal-800' : 'border-orange-200 bg-orange-50 text-orange-900'"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-black"
                                        :class="completed ? 'bg-vertone text-white' : 'bg-orangeone text-white'">
                                        <span x-text="completed ? '✓' : '!'"></span>
                                    </span>
                                    <div class="min-w-0 w-full">
                                        <p class="text-sm font-bold" x-text="completed ? 'Activite validee' : 'Ajustement necessaire'"></p>
                                        <p class="mt-1 text-sm leading-6" x-text="message"></p>

                                        {{-- Détail des éléments mal classés --}}
                                        <template x-if="!completed && wrongItems.length > 0">
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <template x-for="item in wrongItems" :key="item.id">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">
                                                        <span x-text="item.label"></span>
                                                        <svg class="h-3 w-3 shrink-0 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                        </svg>
                                                        <span class="font-bold text-bleuone" x-text="zoneLabelById(item.expected)"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Pied de page / actions --}}
                        <div class="border-t border-gray-100 bg-white px-6 py-5 md:px-8">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                                {{-- Gauche --}}
                                <div class="flex flex-wrap items-center gap-3">
                                    <a
                                        href="{{ $currentLesson['url'] }}"
                                        class="inline-flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
                                    >
                                        Revenir a la lecon
                                    </a>
                                </div>

                                {{-- Droite : état normal --}}
                                <div x-show="!completed" class="flex flex-wrap items-center gap-3">
                                    <p class="text-sm text-slate-500" x-show="selectedCardId" x-cloak>
                                        Selectionne : <span class="font-semibold text-bleuone" x-text="selectedCardLabel()"></span>
                                    </p>
                                    <button
                                        type="button"
                                        @click="validate()"
                                        class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm disabled:!cursor-not-allowed disabled:!opacity-60"
                                        :disabled="submitting"
                                    >
                                        <span x-text="submitting ? 'Validation...' : 'Valider l activite'"></span>
                                    </button>
                                </div>

                                {{-- Droite : état complété --}}
                                <div x-show="completed" x-cloak class="flex flex-wrap items-center gap-3">
                                    <button
                                        type="button"
                                        @click="resetActivity()"
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-orangeone hover:text-orangeone"
                                    >
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Recommencer
                                    </button>
                                    <button
                                        type="button"
                                        @click="showCompletionModal = true"
                                        class="inline-flex items-center gap-2 rounded-full border-2 border-bleuone px-5 py-3 text-sm font-semibold text-bleuone transition hover:bg-bleuone hover:text-white"
                                    >
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        Conseils
                                    </button>
                                    <a
                                        :href="nextUrl"
                                        class="btn-oneduc !rounded-full !px-6 !py-3 !text-sm"
                                    >
                                        Leçon suivante
                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </div>

    <script>
        window.parcoursSortingActivity = function (config) {
            return {
                sidebarOpen: window.innerWidth >= 1024,
                sidebarClosing: false,
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
                showCompletionModal: false,
                failedAttempts: 0,
                completionVariant: 'A',
                wrongItems: [],

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
                    if (this.completed) {
                        this.completionVariant = 'A';
                    }
                },

                closeModal() {
                    this.showCompletionModal = false;
                },

                zoneLabelById(zoneId) {
                    const zone = this.dropzones.find((z) => z.id === zoneId);
                    return zone ? zone.label : zoneId;
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
                        return 'border-teal-200 bg-teal-50 text-bleuone';
                    }

                    if (this.wrongItemIds.includes(cardId)) {
                        return 'border-orangeone bg-orange-50 text-bleuone ring-2 ring-orange-100';
                    }

                    if (this.selectedCardId === cardId) {
                        return 'border-bleuone bg-blue-50 text-bleuone ring-2 ring-blue-100';
                    }

                    if (fromPool) {
                        return 'border-sky-300 bg-sky-50 text-bleuone hover:border-bleuone hover:bg-sky-100';
                    }

                    return 'border-slate-200 bg-white text-bleuone hover:border-bleuone hover:bg-blue-50/40';
                },

                zoneClasses(zoneId) {
                    if (this.completed) {
                        return 'ring-2 ring-teal-200';
                    }

                    return this.hasWrongItemsInZone(zoneId)
                        ? 'ring-2 ring-orangeone ring-offset-1'
                        : '';
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
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
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
                            this.wrongItems = [];
                            this.completionVariant = this.failedAttempts === 0 ? 'A' : (this.failedAttempts < 3 ? 'B' : 'C');
                            this.showCompletionModal = true;
                        } else {
                            this.wrongItems = Array.isArray(payload.wrong_items) ? payload.wrong_items : [];
                            this.failedAttempts++;
                            if (this.failedAttempts >= 3) {
                                this.completionVariant = 'C';
                                this.showCompletionModal = true;
                            }
                        }
                    } catch (error) {
                        console.error('Impossible de valider l activite.', error);
                        this.message = 'Une erreur est survenue pendant la validation. Reessayez dans un instant.';
                    } finally {
                        this.submitting = false;
                    }
                },

                resetActivity() {
                    this.showCompletionModal = false;
                    this.completed = false;
                    this.selectedCardId = null;
                    this.message = '';
                    this.wrongItemIds = [];
                    this.missingItemIds = [];
                    this.failedAttempts = 0;
                    this.completionVariant = 'A';
                    this.wrongItems = [];
                    this.placements = this.normalizePlacements({});
                    this.rebuildPool();
                },
            };
        };
    </script>
@endsection
