{{-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/lecon.blade.php --}}
@extends('stagiaire.formations.master_lecon_evaluation')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Facades\Storage;

    $lecture = $selectedLecture ?? null;
    $moduleId  = $module->id ?? null;
    $lectureId = $lecture?->id;
    $sectionId = $lecture?->section_id;
    $contentType = (string) ($lecture->content_type ?? 'scorm');
    $isSlidesSelected = $contentType === 'slides';
    $isBlocksSelected = $contentType === 'blocks';
    $isScormSelected = !$isSlidesSelected && !$isBlocksSelected;

    // 1. Récupération du statut réel pour savoir si on affiche le bouton au chargement
    $st = $lectureStats[$lectureId] ?? [];
    $currentStatus = strtolower((string)($st['status'] ?? 'not_started'));
    $isAlreadyDone = in_array($currentStatus, ['completed', 'passed']);

    // 2. Détermination de l'URL suivante (Leçon suivante, Section suivante ou Fin)
    $nextUrl = '#';
    
    // On utilise l'URL calculée par la nouvelle logique du contrôleur
    if (!empty($nextLecture['url'])) {
        $nextUrl = $nextLecture['url'];
    } elseif ($moduleId) {
        $nextUrl = route('stagiaire.module.detail', $moduleId);
    }

    // 3. Génération de l'URL du Quiz (Signée pour la sécurité)
    $quizStartUrl = null;
    if ($lecture && $lecture->quiz_enabled && $moduleId && $sectionId) {
        $quizStartUrl = URL::signedRoute('stagiaire.quiz.start', [
            'module'  => $moduleId,
            'section' => $sectionId,
            'lecture' => $lecture->id,
        ]);
    }

    // 4. Source du contenu SCORM
    $scormSrc = $lecture?->scorm_asset_url;

    // 5. Source du contenu Slides
    $isSlidesMode = $lecture
        && $isSlidesSelected
        && ($lecture->slides_status ?? null) === 'ready'
        && !empty($lecture->slides_path);

    $slideImages = [];
    if ($isSlidesMode) {
        $slideImages = collect(Storage::disk('public')->files($lecture->slides_path))
            ->filter(fn (string $file) => (bool) preg_match('/^slide[-_]\\d+\\.jpg$/i', basename($file)))
            ->sortBy(function (string $file): int {
                if (preg_match('/(\\d+)\\.jpg$/i', basename($file), $matches)) {
                    return (int) $matches[1];
                }
                return PHP_INT_MAX;
            })
            ->values()
            ->map(fn (string $file) => route('media.storage', ['path' => $file], false))
            ->all();
    }

    $slidesStatus = (string) ($lecture->slides_status ?? 'none');
@endphp

<script>
    window.SCORM_CONTEXT = {
        lecture_id: @json($lectureId),
        user_id: @json(auth()->id()),
        next_url: @json($nextUrl),
        quiz_start_url: @json($quizStartUrl),
        is_already_done: @json($isAlreadyDone),
        debug: true
    };

    // Fonctions de redirection appelées par l'API.js
    window.goToQuiz = function() {
        if (window.SCORM_CONTEXT.quiz_start_url) {
            window.location.href = window.SCORM_CONTEXT.quiz_start_url;
        }
    };

    window.goToNextLesson = function() {
        window.location.href = window.SCORM_CONTEXT.next_url;
    };

    console.log("Oneduc : Contexte stagiaire prêt", window.SCORM_CONTEXT);
</script>

<main
    x-data="{
        fullscreenSupported: false,
        fullscreenActive: false,
        async toggleFullscreen() {
            const target = this.$refs.contentViewport;

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
        }
    }"
    class="w-full h-full"
>
    <div x-ref="contentViewport" class="relative w-full bg-gray-100" style="height: calc(100vh - var(--app-header-h, 86px));">
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

        @if ($isSlidesMode && !empty($slideImages))
            <div
                x-data="{
                    current: 1,
                    total: {{ count($slideImages) }},
                    slides: @js($slideImages),
                    get currentSrc() { return this.slides[this.current - 1] ?? null; }
                }"
                class="h-full flex flex-col"
            >
                <div class="relative flex-1 p-4 md:p-6">
                    <div class="absolute top-6 right-6 z-10 px-3 py-1 rounded-full bg-black/70 text-white text-xs font-semibold">
                        Slide <span x-text="current"></span> / <span x-text="total"></span>
                    </div>

                    <div class="h-full w-full flex items-center justify-center rounded-xl border border-gray-200 bg-white overflow-hidden">
                        <img :src="currentSrc" alt="Slide de cours" class="max-h-full max-w-full object-contain">
                    </div>

                    <div class="absolute inset-y-0 left-2 flex items-center">
                        <button
                            type="button"
                            @click="if(current > 1) current--"
                            class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition"
                            aria-label="Slide precedente"
                        >
                            <i class="ti ti-chevron-left"></i>
                        </button>
                    </div>

                    <div class="absolute inset-y-0 right-2 flex items-center">
                        <button
                            type="button"
                            @click="if(current < total) current++"
                            class="h-10 w-10 rounded-full bg-black/60 text-white hover:bg-black/75 transition"
                            aria-label="Slide suivante"
                        >
                            <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="border-t border-gray-200 bg-white px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="current = Math.max(1, current - 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                            Precedent
                        </button>
                        <button type="button" @click="current = Math.min(total, current + 1)" class="px-3 py-2 text-xs font-bold uppercase border border-gray-300 rounded hover:bg-gray-50">
                            Suivant
                        </button>
                    </div>

                    @if ($lecture && $lecture->quiz_enabled && $quizStartUrl)
                        <a href="{{ $quizStartUrl }}" class="btn-oneduc !px-5 !py-2 !text-xs">
                            Passer au questionnaire
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    @elseif($lecture)
                        <form method="POST" action="{{ route('lecture.valider', ['id' => $lecture->id]) }}">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $nextUrl }}">
                            <button type="submit" class="btn-oneduc !px-5 !py-2 !text-xs">
                                Continuer
                                <i class="ti ti-arrow-right"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @elseif ($lecture && $isSlidesSelected && in_array($slidesStatus, ['pending', 'processing'], true))
            <div class="flex items-center justify-center h-full">
                <div class="max-w-md w-full bg-white rounded-[24px] shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ti ti-refresh text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-bleuone mb-2">Conversion des slides en cours</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Le support a bien ete envoye. Les slides seront disponibles dans quelques instants.
                    </p>
                </div>
            </div>
        @elseif ($lecture && $isSlidesSelected && $slidesStatus === 'failed')
            <div class="flex items-center justify-center h-full">
                <div class="max-w-md w-full bg-white rounded-[24px] shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ti ti-alert-triangle text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-bleuone mb-2">Conversion des slides echouee</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Le support n'a pas pu etre converti. Contactez votre formateur.
                    </p>
                </div>
            </div>
        @elseif ($lecture && $isSlidesSelected)
            <div class="flex items-center justify-center h-full">
                <div class="max-w-md w-full bg-white rounded-[24px] shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ti ti-file-alert text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-bleuone mb-2">Support slides manquant</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Cette lecon est en mode Slides, mais aucun support converti n'est disponible pour le moment.
                    </p>
                </div>
            </div>
        @elseif ($lecture && $isBlocksSelected)
            <div class="h-full flex flex-col bg-white">
                <div class="flex-1 overflow-y-auto">
                    <div class="max-w-3xl mx-auto px-6 py-10">
                        @include('shared.lecture_blocks', ['blocks' => $lecture->content_blocks ?? []])
                    </div>
                </div>
                <div class="border-t border-gray-200 bg-white px-4 py-3 flex items-center justify-end gap-3">
                    @if ($lecture->quiz_enabled && $quizStartUrl)
                        <a href="{{ $quizStartUrl }}" class="btn-oneduc !px-5 !py-2 !text-xs">
                            Passer au questionnaire
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    @else
                        <form method="POST" action="{{ route('lecture.valider', ['id' => $lecture->id]) }}">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $nextUrl }}">
                            <button type="submit" class="btn-oneduc !px-5 !py-2 !text-xs">
                                Continuer
                                <i class="ti ti-arrow-right"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @elseif ($isScormSelected && $scormSrc)
            {{-- L'iframe qui charge le module interactif --}}
            <iframe
                id="scorm-iframe"
                title="{{ $lecture->lecture_title }}"
                src="{{ $scormSrc }}"
                frameborder="0"
                allowfullscreen
                class="w-full h-full block">
            </iframe>
        @elseif ($lecture && $isScormSelected)
            <div class="flex items-center justify-center h-full">
                <div class="max-w-md w-full bg-white rounded-[24px] shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ti ti-alert-triangle text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-bleuone mb-2">Ressource SCORM manquante</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Cette lecon est en mode SCORM, mais aucune ressource SCORM n'est configuree.
                    </p>
                </div>
            </div>

        @else
            <div class="flex items-center justify-center h-full">
                <div class="max-w-md w-full bg-white rounded-[24px] shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ti ti-alert-triangle text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-bleuone mb-2">Contenu non disponible</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Aucun contenu pret (SCORM ou Slides) pour la lecon <strong>"{{ $lecture->lecture_title ?? 'Sans titre' }}"</strong>.
                    </p>
                    <a href="{{ route('stagiaire.dashboard') }}" class="mt-8 inline-flex text-orangeone font-bold hover:underline">
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        @endif
    </div>
</main>

@endsection
