<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Lecture SCORM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>


</head>
<body class="bg-white text-gray-900">



@if ($evaluation->scorm_path)
    <script>
        window.SCORM_CONTEXT = {
            evaluation_id: {{ $evaluation->id }},
            isFinalEvaluation: true
        };
    </script>
@endif
<main class="bg-white w-full h-screen overflow-hidden">
    @if ($evaluation->scorm_path)
        {{-- ✅ IFRAME ÉVALUATION EN PLEIN ÉCRAN --}}
        <iframe
            title="Évaluation finale"
            src="{{ asset('modules/scorm/01_evaluations/' . $evaluation->scorm_path . '/res/index.html') }}"
            frameborder="0"
            allowfullscreen
            class="w-full h-full">
        </iframe>
    @else
        <div class="text-center py-20 text-gray-500 italic">
            ⚠️ L’évaluation demandée est introuvable ou mal configurée.
        </div>
    @endif
</main>

</body>
</html>

