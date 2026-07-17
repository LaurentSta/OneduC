@if (($activeLessonPart ?? null) === 'remplir-formulaire')
@php
    $availableModules = \App\Data\ParcoursFormateur::pathCreationSimulationModules();

    $selectedItems = [];
    $storeUrl = $mixedPartUrls['felicitations'] ?? '#';
    $method = 'GET';
    $builderMode = 'simulation';
@endphp

<div class="mx-auto w-full max-w-[1285px]">
    @include('formateur.mes-parcours._form')
</div>
@endif
