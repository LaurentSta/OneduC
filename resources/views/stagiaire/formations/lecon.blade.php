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
    $isScormSelected = !$isSlidesSelected;

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
    $scormSrc = $lecture && $lecture->scorm_path ? asset($lecture->scorm_path) : null;

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

<main class="w-full h-full">
    <div class="relative w-full bg-gray-100" style="height: calc(100vh - 64px);">
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
                        <a href="{{ $quizStartUrl }}" class="oneduc-btn-alert inline-flex items-center gap-2 px-5 py-2 bg-orangeone text-white rounded-full text-xs font-bold uppercase">
                            Passer au questionnaire
                            <i class="ti ti-arrow-right"></i>
                        </a>
                    @elseif($lecture)
                        <form method="POST" action="{{ route('lecture.valider', ['id' => $lecture->id]) }}">
                            @csrf
                            <input type="hidden" name="redirect_to" value="{{ $nextUrl }}">
                            <button type="submit" class="oneduc-btn-alert inline-flex items-center gap-2 px-5 py-2 bg-orangeone text-white rounded-full text-xs font-bold uppercase">
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
