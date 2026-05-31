@if (($activeLessonPart ?? null) === 'felicitations')
@php
    $availableModules = \App\Data\ParcoursFormateur::pathCreationSimulationModules();
    $pathCreationReviewUrl = $currentLesson['url'] ?? ($mixedPartUrls['ouvrir-formulaire'] ?? '#');
    $pathCreationRestartUrl = $mixedPartUrls['remplir-formulaire'] ?? ($currentLesson['url'] ?? '#');
@endphp

<div class="mx-auto w-full max-w-[1285px] space-y-6">
    <section
        class="rounded-[20px] bg-white px-8 py-6 shadow-md"
        data-path-finalisation-summary
        data-available-modules='@json($availableModules)'
    >
        <div class="grid grid-cols-12 gap-6 items-start">
            <div class="col-span-12 lg:col-span-8">
                <p class="text-xs font-bold uppercase tracking-widest text-orangeone" data-path-summary-kicker>Parcours etabli dans le simulateur</p>
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

    <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
        <a
            href="{{ $pathCreationReviewUrl }}"
            class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
        >
            Revoir la leçon
        </a>

        <button
            type="button"
            id="path-creation-restart-activity"
            data-restart-url="{{ $pathCreationRestartUrl }}"
            class="inline-flex items-center justify-center rounded-full border border-orangeone bg-white px-5 py-3 text-sm font-bold text-orangeone transition hover:bg-orange-50"
        >
            Refaire l’activité
        </button>

        @if (!empty($nextLesson['url'] ?? null))
            <a href="{{ $nextLesson['url'] }}" class="btn-oneduc !rounded-full !px-7 !py-3">
                Leçon suivante
            </a>
        @endif
    </div>
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
        const kicker = summary.querySelector('[data-path-summary-kicker]');
        if (payload.stopped) {
            if (kicker) kicker.textContent = 'Activite stoppee apres 3 essais';
            if (title) title.textContent = payload.title || 'Parcours non finalise';
            if (description) description.textContent = 'Vous avez utilise les trois essais disponibles. Vous pouvez refaire l activite pour reconstruire le parcours attendu.';
        } else {
            if (title && payload.title) title.textContent = payload.title;
            if (description && payload.description) description.textContent = payload.description;
        }

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

        const restartButton = document.getElementById('path-creation-restart-activity');
        restartButton?.addEventListener('click', function () {
            window.localStorage.removeItem('oneduc_training_path_creation');
            window.location.assign(restartButton.dataset.restartUrl || window.location.href);
        });
    });
</script>
@endif
