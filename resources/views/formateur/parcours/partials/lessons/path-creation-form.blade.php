@if (($activeLessonPart ?? null) === 'remplir-formulaire')
@php
    $availableModules = \App\Models\Module::active()
        ->with([
            'sections.lectures:id,module_id,section_id,duration,question_count,quiz_enabled,quiz_questions_per_attempt',
        ])
        ->orderBy('module_title')
        ->get()
        ->map(function (\App\Models\Module $module) {
            $lessonCount = (int) collect($module->sections ?? [])
                ->flatMap->lectures
                ->count();

            $questionCount = (int) collect($module->sections ?? [])
                ->flatMap->lectures
                ->sum(function ($lecture) {
                    $scorm = (int) ($lecture->question_count ?? 0);
                    $quiz = (bool) ($lecture->quiz_enabled ?? false)
                        ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
                        : 0;

                    return max($scorm, $quiz);
                });

            return [
                'id' => (int) $module->id,
                'title' => (string) $module->module_title,
                'lesson_count' => $lessonCount,
                'question_count' => $questionCount,
                'duration_label' => (string) ($module->formatted_duration ?? 'Rythme libre'),
            ];
        })
        ->values()
        ->all();

    $selectedItems = [];
    $storeUrl = $mixedPartUrls['felicitations'] ?? '#';
    $method = 'GET';
    $builderMode = 'simulation';
@endphp

<div class="mx-auto w-full max-w-[1285px]">
    @include('formateur.mes-formations._form')
</div>
@endif
