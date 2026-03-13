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

    $formatBytes = static function (?int $bytes): string {
        $bytes = max(0, (int) $bytes);
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', ' ') . ' Mo';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
        }

        return $bytes . ' o';
    };
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
    <div class="relative w-full bg-gray-100" style="height: calc(100vh - var(--app-header-h, 86px));">
        @if(($lessonResources ?? collect())->isNotEmpty())
            <div class="absolute right-4 top-4 z-20 flex flex-col items-end gap-3">
                <div
                    x-show="resourcesPanelOpen"
                    x-transition.opacity
                    x-cloak
                    class="w-[320px] max-w-[calc(100vw-2rem)] rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-2xl backdrop-blur"
                >
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                        <h2 class="text-sm font-bold text-bleuone">Documents de la leçon</h2>
                        <p class="mt-1 text-xs text-gray-500">Ces ressources ont été partagées par votre formateur.</p>
                        </div>
                        <button
                            type="button"
                            @click="resourcesPanelOpen = false"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-bleuone"
                            aria-label="Fermer les ressources"
                        >
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <div class="max-h-[60vh] space-y-3 overflow-y-auto pr-1">
                        @foreach($lessonResources as $resource)
                            @php
                                $resourceUrl = $resource->public_url;
                                $resourceExt = strtoupper($resource->extension ?: 'FILE');
                            @endphp
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <div class="flex items-start gap-3">
                                    @if($resource->is_image)
                                        <a href="{{ $resourceUrl }}" target="_blank" class="shrink-0">
                                            <img src="{{ $resourceUrl }}" alt="{{ $resource->title }}" class="h-12 w-12 rounded-lg border border-gray-200 object-cover bg-white">
                                        </a>
                                    @else
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-[11px] font-black text-bleuone">
                                            {{ $resourceExt }}
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-gray-800">{{ $resource->title }}</p>
                                        <p class="mt-1 truncate text-xs text-gray-500">{{ $resource->original_name }}</p>
                                        <div class="mt-1 flex items-center gap-1 text-[11px] text-gray-400">
                                            <span class="shrink-0">{{ $formatBytes($resource->file_size) }}</span>
                                            @if($resource->mime_type)
                                                <span class="shrink-0">•</span>
                                                <span class="truncate max-w-[170px]" title="{{ $resource->mime_type }}">{{ $resource->mime_type }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <a href="{{ $resourceUrl }}" target="_blank" class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-bold text-blue-700 hover:bg-blue-100">
                                                Ouvrir
                                            </a>
                                            <a href="{{ $resourceUrl }}" download="{{ $resource->original_name }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-bold text-gray-700 hover:bg-gray-100">
                                                Télécharger
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

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
