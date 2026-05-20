@if (($activeLessonPart ?? null) === 'felicitations')
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
@endphp

<div class="mx-auto w-full max-w-[1285px] space-y-6">
    <section
        class="rounded-[20px] bg-white px-8 py-6 shadow-md"
        data-path-finalisation-summary
        data-available-modules='@json($availableModules)'
    >
        <div class="grid grid-cols-12 gap-6 items-start">
            <div class="col-span-12 lg:col-span-8">
                <p class="text-xs font-bold uppercase tracking-widest text-orangeone">Parcours etabli dans le simulateur</p>
                <h2 class="mt-2 font-raleway text-2xl font-medium leading-tight text-[#004461]" data-path-summary-title>
                    Votre parcours de formation
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500" data-path-summary-description>
                    Voici le parcours construit pendant l'exercice.
                </p>
            </div>

            <div class="col-span-12 lg:col-span-4">
                <div class="rounded-[14px] border border-[#004461]/15 bg-[#004461]/5 px-5 py-4 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-widest text-[#004461]/60">Resume du parcours</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-[10px] border border-gray-100 bg-white px-3 py-2.5 shadow-sm">
                            <div class="text-lg font-bold leading-none text-[#004461]" data-path-stat="steps">0</div>
                            <div class="mt-0.5 text-[11px] text-gray-500">Etapes</div>
                        </div>
                        <div class="rounded-[10px] border border-gray-100 bg-white px-3 py-2.5 shadow-sm">
                            <div class="text-lg font-bold leading-none text-orangeone" data-path-stat="modules">0</div>
                            <div class="mt-0.5 text-[11px] text-gray-500">Modules</div>
                        </div>
                        <div class="rounded-[10px] border border-gray-100 bg-white px-3 py-2.5 shadow-sm">
                            <div class="text-lg font-bold leading-none text-vertone" data-path-stat="lessons">0</div>
                            <div class="mt-0.5 text-[11px] text-gray-500">Lecons</div>
                        </div>
                        <div class="rounded-[10px] border border-gray-100 bg-white px-3 py-2.5 shadow-sm">
                            <div class="text-lg font-bold leading-none text-purple-600" data-path-stat="questions">0</div>
                            <div class="mt-0.5 text-[11px] text-gray-500">Questions</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-1">
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                            <span data-path-stat="wordclouds">0</span>&nbsp;nuage(s) de mots
                        </span>
                        <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-700">
                            <span data-path-stat="polls">0</span>&nbsp;sondage(s)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div
        data-parcours-builder
        data-available-modules='@json($availableModules)'
        data-selected-items='@json([])'
        data-local-storage-key="oneduc_training_path_creation"
        data-csrf-token="{{ csrf_token() }}"
        data-store-url=""
        data-method="GET"
        data-mode="preview"
        class="min-h-[520px]"
    ></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const summary = document.querySelector('[data-path-finalisation-summary]');
        if (!summary) return;

        let payload = {};
        try {
            payload = JSON.parse(window.localStorage.getItem('oneduc_training_path_creation') || '{}');
        } catch (error) {
            payload = {};
        }

        const items = Array.isArray(payload.items) ? payload.items : [];
        const modules = JSON.parse(summary.dataset.availableModules || '[]');
        const moduleMap = new Map(modules.map((module) => [Number(module.id), module]));

        const title = summary.querySelector('[data-path-summary-title]');
        const description = summary.querySelector('[data-path-summary-description]');
        if (title && payload.title) title.textContent = payload.title;
        if (description && payload.description) description.textContent = payload.description;

        const moduleItems = items.filter((item) => item.type === 'module');
        const statValues = {
            steps: items.length,
            modules: moduleItems.length,
            lessons: moduleItems.reduce((total, item) => total + Number(moduleMap.get(Number(item.module_id))?.lesson_count || 0), 0),
            questions: moduleItems.reduce((total, item) => total + Number(moduleMap.get(Number(item.module_id))?.question_count || 0), 0),
            wordclouds: items.filter((item) => item.type === 'wordcloud').length,
            polls: items.filter((item) => item.type === 'poll').length,
        };

        Object.entries(statValues).forEach(([key, value]) => {
            const target = summary.querySelector(`[data-path-stat="${key}"]`);
            if (target) target.textContent = value;
        });
    });
</script>
@endif
