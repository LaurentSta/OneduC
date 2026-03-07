{{-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/lecon.blade.php --}}
@extends('stagiaire.formations.master_lecon_evaluation')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    use Illuminate\Support\Facades\URL;

    $lecture = $selectedLecture ?? null;
    $moduleId  = $module->id ?? null;
    $lectureId = $lecture?->id;
    $sectionId = $lecture?->section_id;

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
        @if ($scormSrc)
            {{-- L'iframe qui charge le module interactif --}}
            <iframe
                id="scorm-iframe"
                title="{{ $lecture->lecture_title }}"
                src="{{ $scormSrc }}"
                frameborder="0"
                allowfullscreen
                class="w-full h-full block">
            </iframe>

        @else
            <div class="flex items-center justify-center h-full">
                <div class="max-w-md w-full bg-white rounded-[24px] shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-16 h-16 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ti ti-alert-triangle text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-bleuone mb-2">Contenu non disponible</h1>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Le module interactif (SCORM) pour la leçon <strong>"{{ $lecture->lecture_title ?? 'Sans titre' }}"</strong> n'a pas encore été configuré.
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
