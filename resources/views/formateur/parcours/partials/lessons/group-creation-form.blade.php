@if (($activeLessonPart ?? null) === 'informations')
@php
    $trainingGroupActivityKey = $currentLesson['completion_activity_key'] ?? null;
    $trainingGroupActivityStatusKey = $trainingGroupActivityKey
        ? implode('.', [$activeChapterKey, $activeLessonKey, $trainingGroupActivityKey])
        : null;
    $trainingGroupActivityCompleted = $trainingGroupActivityStatusKey
        ? (($activityStatusMap[$trainingGroupActivityStatusKey] ?? false) === true)
        : false;
@endphp
<article class="mx-auto w-full max-w-[1285px] rounded-[24px] border border-gray-100 bg-white p-5 shadow-sm md:p-6">
    <form id="training-group-information-form" class="space-y-7" novalidate>
        <div id="training-group-information-errors" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold">Certaines informations doivent être corrigées avant de continuer.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm" data-role="messages"></ul>
                </div>
            </div>
        </div>

        <div>
            <div class="mb-2 flex items-center gap-2">
                <label for="training-group-name" class="block text-base font-medium text-gray-900">Nom du groupe</label>
                <div class="relative group">
                    <button type="button" aria-label="Information sur le nom du groupe" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                        ?
                    </button>
                    <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                        Ce champ est obligatoire. Choisissez un nom simple et explicite pour retrouver facilement le groupe par la suite.
                    </div>
                </div>
            </div>
            <input
                id="training-group-name"
                type="text"
                required
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone"
                placeholder="Ex : Groupe Marketing 2025 - Niveau 1"
            >
        </div>

        <div>
            <div class="mb-2 flex items-center gap-2">
                <label for="training-group-description" class="block text-base font-medium text-gray-900">Description</label>
                <div class="relative group">
                    <button type="button" aria-label="Information sur la description du groupe" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                        ?
                    </button>
                    <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                        Ce champ est facultatif. Vous pouvez ajouter quelques mots pour préciser l’objectif, le public ou le contexte du groupe.
                    </div>
                </div>
            </div>
            <textarea
                id="training-group-description"
                rows="3"
                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone"
                placeholder="Objectifs, public, période..."
            ></textarea>
        </div>

        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <details class="min-w-0 flex-1">
                <summary class="inline-flex cursor-pointer list-none items-center gap-3 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm marker:hidden">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4" />
                        </svg>
                    </span>
                    <span>Options</span>
                </summary>

            <div class="mt-3 rounded-[18px] border border-gray-200 bg-white px-4 py-4">
                <div class="grid gap-4 lg:grid-cols-2 lg:items-stretch">
                    <div class="space-y-4 rounded-[18px] border border-sky-200 bg-sky-50/80 px-4 py-4">
                        <div class="rounded-[18px] border border-white/70 bg-white/80 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <label for="training-group-active" class="flex items-center gap-3 text-base font-medium text-gray-900">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9m6.364-5.364a9 9 0 11-12.728 0" />
                                        </svg>
                                    </span>
                                    <span>Activer le groupe</span>
                                </label>

                                <input id="training-group-active" type="checkbox" checked class="peer sr-only">
                                <label
                                    for="training-group-active"
                                    aria-label="Activer ou désactiver le groupe"
                                    class="relative inline-flex h-7 w-12 cursor-pointer rounded-full bg-gray-300 transition-colors duration-200 after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform after:duration-200 after:content-[''] peer-checked:bg-vertone peer-checked:after:translate-x-5"
                                ></label>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-[18px] border border-white/70 bg-white/80 px-4 py-4">
                                <label for="training-group-start-date" class="mb-2 block text-base font-medium text-gray-900">Date de démarrage</label>
                                <input
                                    id="training-group-start-date"
                                    type="date"
                                    class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone"
                                >
                            </div>

                            <div class="rounded-[18px] border border-white/70 bg-white/80 px-4 py-4">
                                <label for="training-group-end-date" class="mb-2 block text-base font-medium text-gray-900">Date de fin</label>
                                <input
                                    id="training-group-end-date"
                                    type="date"
                                    class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 rounded-[18px] border border-orange-200 bg-orange-50/80 px-4 py-4">
                        <h3 class="text-base font-medium text-gray-900">Co-formateurs</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            Ajoutez un ou plusieurs formateurs actifs deja inscrits pour coanimer ce groupe.
                        </p>

                        <label for="training-co-trainer-search" class="mt-5 block text-sm font-semibold text-gray-900">Rechercher par nom ou email</label>
                        <input
                            id="training-co-trainer-search"
                            type="search"
                            autocomplete="off"
                            class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-orangeone focus:ring-orangeone"
                            placeholder="Saisissez Karim ou son email"
                        >
                        <div id="training-co-trainer-suggestions" class="mt-2 hidden overflow-hidden rounded-xl border border-orange-200 bg-white shadow-sm">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-4 px-4 py-3 text-left transition hover:bg-orange-50"
                                data-co-trainer-name="Karim Benali"
                                data-co-trainer-email="karim.benali@oneduc-demo.fr"
                            >
                                <span>
                                    <span class="block text-sm font-bold text-gray-900">Karim Benali</span>
                                    <span class="block text-xs font-semibold text-gray-500">karim.benali@oneduc-demo.fr</span>
                                </span>
                                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orangeone">Ajouter</span>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Seuls les formateurs actifs deja inscrits sont proposes.</p>

                        <p class="mt-6 text-xs font-bold uppercase tracking-[0.24em] text-gray-500">Formateurs associes</p>
                        <div id="training-co-trainers-empty" class="mt-3 inline-flex rounded-lg border border-dashed border-gray-300 bg-white/70 px-4 py-3 text-sm text-gray-500">
                            Aucun co-formateur ajoute pour le moment.
                        </div>
                        <div id="training-co-trainers-selected" class="mt-3 hidden rounded-lg border border-orange-200 bg-white px-4 py-3">
                            <span class="block text-sm font-bold text-gray-900">Karim Benali</span>
                            <span class="block text-xs font-semibold text-gray-500">karim.benali@oneduc-demo.fr</span>
                        </div>
                    </div>
                </div>
            </div>
            </details>

            @if (!empty($mixedPartUrls['stagiaires'] ?? null))
                <a
                    href="{{ $mixedPartUrls['stagiaires'] }}"
                    id="training-group-information-next"
                    class="inline-flex items-center justify-center rounded-full bg-orangeone px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600"
                >
                    Suivant
                </a>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const form = document.getElementById('training-group-information-form');
            const nextButton = document.getElementById('training-group-information-next');
            const errorBox = document.getElementById('training-group-information-errors');
            const errorList = errorBox?.querySelector('[data-role="messages"]');

            if (!form || !nextButton || !errorBox || !errorList) {
                return;
            }

            const nameInput = document.getElementById('training-group-name');
            const descriptionInput = document.getElementById('training-group-description');
            const coTrainerSearch = document.getElementById('training-co-trainer-search');
            const coTrainerSuggestions = document.getElementById('training-co-trainer-suggestions');
            const coTrainerSuggestionButton = coTrainerSuggestions?.querySelector('[data-co-trainer-email]');
            const coTrainerEmpty = document.getElementById('training-co-trainers-empty');
            const coTrainerSelected = document.getElementById('training-co-trainers-selected');
            const invalidClasses = ['border-red-500', 'bg-red-50'];
            const storageKey = 'oneduc_training_group_creation';
            const activityCompleted = @json($trainingGroupActivityCompleted);
            const karimCoTrainer = {
                name: 'Karim Benali',
                email: 'karim.benali@oneduc-demo.fr',
            };
            const readSavedData = () => {
                const savedData = JSON.parse(window.localStorage.getItem(storageKey) || '{}');

                if (!activityCompleted && savedData.__activityCompleted !== false) {
                    window.localStorage.removeItem(storageKey);
                    return { __activityCompleted: false };
                }

                return savedData;
            };
            const writeSavedData = (data) => {
                const savedData = readSavedData();
                window.localStorage.setItem(storageKey, JSON.stringify({
                    ...savedData,
                    ...data,
                    __activityCompleted: false,
                }));
            };

            const clearFieldState = (field) => {
                field.classList.remove(...invalidClasses);
            };

            [nameInput, descriptionInput].forEach((field) => {
                field?.addEventListener('input', () => {
                    clearFieldState(field);
                    writeSavedData({
                        name: nameInput.value.trim(),
                        description: descriptionInput.value.trim(),
                        coTrainer: coTrainerSelected?.dataset.email || '',
                    });
                });
            });

            const updateCoTrainerSuggestion = () => {
                if (!coTrainerSearch || !coTrainerSuggestions) {
                    return;
                }

                const query = coTrainerSearch.value.trim().toLowerCase();
                const searchableText = `${karimCoTrainer.name} ${karimCoTrainer.email}`.toLowerCase();
                coTrainerSuggestions.classList.toggle('hidden', query.length === 0 || !searchableText.includes(query));
            };

            coTrainerSearch?.addEventListener('input', updateCoTrainerSuggestion);

            coTrainerSuggestionButton?.addEventListener('click', () => {
                coTrainerSearch.value = karimCoTrainer.email;
                coTrainerSuggestions.classList.add('hidden');
                coTrainerEmpty?.classList.add('hidden');
                coTrainerSelected?.classList.remove('hidden');
                coTrainerSelected?.setAttribute('data-email', karimCoTrainer.email);
                writeSavedData({ coTrainer: karimCoTrainer.email });
            });

            const savedData = readSavedData();
            if (savedData.name) {
                nameInput.value = savedData.name;
            }
            if (savedData.description) {
                descriptionInput.value = savedData.description;
            }
            if (savedData.coTrainer === karimCoTrainer.email) {
                coTrainerSearch.value = karimCoTrainer.email;
                coTrainerEmpty?.classList.add('hidden');
                coTrainerSelected?.classList.remove('hidden');
                coTrainerSelected?.setAttribute('data-email', karimCoTrainer.email);
            }

            nextButton.addEventListener('click', (event) => {
                const missingFields = [];

                if (!nameInput.value.trim()) {
                    missingFields.push('le nom du groupe');
                    nameInput.classList.add(...invalidClasses);
                }

                if (missingFields.length === 0) {
                    writeSavedData({
                        name: nameInput.value.trim(),
                        description: descriptionInput.value.trim(),
                        coTrainer: coTrainerSelected?.dataset.email || '',
                    });
                    errorBox.classList.add('hidden');
                    errorList.innerHTML = '';
                    return;
                }

                event.preventDefault();
                errorList.innerHTML = '';
                missingFields.forEach((field) => {
                    const item = document.createElement('li');
                    item.textContent = `Veuillez renseigner ${field}.`;
                    errorList.appendChild(item);
                });
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        })();
    </script>
</article>
@elseif (($activeLessonPart ?? null) === 'stagiaires')
<article class="mx-auto w-full max-w-[1285px] rounded-[24px] border border-gray-100 bg-white p-5 shadow-sm md:p-6">
    <form
        id="training-group-students-form"
        action="{{ $mixedPartUrls['modules'] ?? '#' }}"
        method="GET"
        class="space-y-7"
        onsubmit="return window.validateTrainingGroupStudents ? window.validateTrainingGroupStudents(event, this) : this.reportValidity();"
    >
        <div id="training-group-students-errors" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold">Certaines informations doivent être corrigées avant de continuer.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm" data-role="messages"></ul>
                </div>
            </div>
        </div>

        <section>
            <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <h2 class="font-raleway text-xl font-bold text-bleuone">Ajouter vos stagiaires</h2>
                        <div class="relative group">
                            <button type="button" aria-label="Information sur l'ajout de stagiaires" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                                ?
                            </button>
                            <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-80 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                                Ajoutez vos apprenants ligne par ligne ou via import CSV. Le code d’accès provisoire défini plus bas sera réutilisé pour leurs comptes.
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">
                        Ajoutez les apprenants ligne par ligne. Le code d'accès provisoire sera reutilise pour leurs comptes.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-bleuone/30 hover:text-bleuone"
                    aria-label="Importer des stagiaires par CSV"
                    title="Importer un lot CSV"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                </button>
            </div>

            <div id="training-students-container" class="space-y-3">
                <div class="training-student-row rounded-[12px] border border-gray-200 bg-white p-4 shadow-sm transition hover:border-orangeone/50">
                    <div class="grid grid-cols-1 items-start gap-4 md:grid-cols-[1fr_1fr_2fr_auto]">
                        <div>
                            <label for="training-student-0-firstname" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Prénom</label>
                            <input id="training-student-0-firstname" type="text" maxlength="255" placeholder="Ex: Thomas" required class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                        </div>

                        <div>
                            <label for="training-student-0-lastname" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Nom</label>
                            <input id="training-student-0-lastname" type="text" maxlength="255" placeholder="Ex: Dupont" required class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                        </div>

                        <div>
                            <label for="training-student-0-email" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Email professionnel</label>
                            <input id="training-student-0-email" type="email" maxlength="255" placeholder="thomas.dupont@entreprise.com" required class="w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                        </div>

                        <div class="flex h-full items-end pb-[3px]">
                            <button type="button" class="training-student-remove rounded-full p-2 text-gray-300 transition hover:bg-red-50 hover:text-red-600" title="Supprimer la ligne">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button
                    type="button"
                    id="training-add-student"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-bleuone bg-bleuone px-4 py-2 text-sm font-bold text-white transition hover:opacity-90"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un stagiaire
                </button>
            </div>
        </section>

        <section class="rounded-[12px] border border-orangeone/20 bg-orangeone/5 p-3">
            <div class="mb-2 flex items-center gap-2">
                <h3 class="font-raleway text-sm font-bold text-gray-800">Mot de passe provisoire du groupe</h3>
                <div class="relative group">
                    <button type="button" aria-label="Information sur le mot de passe provisoire" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                        ?
                    </button>
                    <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                        Les stagiaires recevront un e-mail avec leur identifiant et un lien qu'ils pourront utiliser pour se connecter.
                    </div>
                </div>
            </div>

            <div class="w-full max-w-sm">
                <label for="training-students-password" class="sr-only">Mot de passe provisoire</label>
                <input
                    id="training-students-password"
                    type="text"
                    required
                    autocomplete="off"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 font-mono text-sm tracking-wide text-gray-900 focus:border-orangeone focus:ring-orangeone"
                    placeholder="Ex: Formation2026!"
                >
            </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if (!empty($mixedPartUrls['informations'] ?? null))
                <a
                    href="{{ $mixedPartUrls['informations'] }}"
                    class="inline-flex items-center justify-center rounded-full border border-gray-300 px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                >
                    Précédent
                </a>
            @endif

            @if (!empty($mixedPartUrls['modules'] ?? null))
                <button
                    type="submit"
                    id="training-group-students-next"
                    class="inline-flex items-center justify-center rounded-full bg-orangeone px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600"
                >
                    Suivant
                </button>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const container = document.getElementById('training-students-container');
            const addButton = document.getElementById('training-add-student');
            const nextButton = document.getElementById('training-group-students-next');
            const passwordInput = document.getElementById('training-students-password');
            const errorBox = document.getElementById('training-group-students-errors');
            const errorList = errorBox?.querySelector('[data-role="messages"]');
            const invalidClasses = ['border-red-500', 'bg-red-50'];
            const storageKey = 'oneduc_training_group_creation';
            const isValidEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

            if (!container || !addButton || !nextButton || !passwordInput || !errorBox || !errorList) {
                return;
            }

            const readSavedData = () => JSON.parse(window.localStorage.getItem(storageKey) || '{}');
            const collectStudents = () => Array.from(container.querySelectorAll('.training-student-row')).map((row) => ({
                firstname: row.querySelector('[data-field="firstname"], input[id*="firstname"]')?.value.trim() || '',
                lastname: row.querySelector('[data-field="lastname"], input[id*="lastname"]')?.value.trim() || '',
                email: row.querySelector('[data-field="email"], input[type="email"], input[id*="email"]')?.value.trim() || '',
            })).filter((student) => student.firstname || student.lastname || student.email);
            const writeSavedData = () => {
                const savedData = readSavedData();
                window.localStorage.setItem(storageKey, JSON.stringify({
                    ...savedData,
                    students: collectStudents(),
                    temporaryPassword: passwordInput.value.trim(),
                }));
            };

            const renumberRows = () => {
                container.querySelectorAll('.training-student-row').forEach((row, index) => {
                    row.querySelectorAll('input').forEach((input) => {
                        const field = input.dataset.field;
                        if (!field) return;

                        input.id = `training-student-${index}-${field}`;
                        const label = row.querySelector(`label[data-field="${field}"]`);
                        if (label) label.setAttribute('for', input.id);
                    });
                });
            };

            container.querySelectorAll('.training-student-row input').forEach((input) => {
                if (input.id.includes('firstname')) input.dataset.field = 'firstname';
                if (input.id.includes('lastname')) input.dataset.field = 'lastname';
                if (input.id.includes('email')) input.dataset.field = 'email';
            });
            container.querySelectorAll('.training-student-row label').forEach((label) => {
                if (label.getAttribute('for')?.includes('firstname')) label.dataset.field = 'firstname';
                if (label.getAttribute('for')?.includes('lastname')) label.dataset.field = 'lastname';
                if (label.getAttribute('for')?.includes('email')) label.dataset.field = 'email';
            });

            const bindRow = (row) => {
                row.querySelector('.training-student-remove')?.addEventListener('click', () => {
                    if (container.querySelectorAll('.training-student-row').length > 1) {
                        row.remove();
                        renumberRows();
                        writeSavedData();
                    }
                });

                row.querySelectorAll('input').forEach((input) => {
                    input.addEventListener('input', () => {
                        input.classList.remove(...invalidClasses);
                        writeSavedData();
                    });
                });
            };

            container.querySelectorAll('.training-student-row').forEach(bindRow);
            passwordInput.addEventListener('input', () => {
                passwordInput.classList.remove(...invalidClasses);
                writeSavedData();
            });

            const hydrateSavedStudents = () => {
                const savedData = readSavedData();
                const savedStudents = Array.isArray(savedData.students) ? savedData.students : [];
                const template = container.querySelector('.training-student-row');

                if (savedStudents.length > 0 && template) {
                    container.innerHTML = '';
                    savedStudents.forEach((student) => {
                        const row = template.cloneNode(true);
                        row.querySelector('[data-field="firstname"], input[id*="firstname"]').value = student.firstname || '';
                        row.querySelector('[data-field="lastname"], input[id*="lastname"]').value = student.lastname || '';
                        row.querySelector('[data-field="email"], input[type="email"], input[id*="email"]').value = student.email || '';
                        row.querySelectorAll('input').forEach((input) => input.classList.remove(...invalidClasses));
                        container.appendChild(row);
                        bindRow(row);
                    });
                    renumberRows();
                }

                if (savedData.temporaryPassword) {
                    passwordInput.value = savedData.temporaryPassword;
                }
            };

            hydrateSavedStudents();

            addButton.addEventListener('click', () => {
                const template = container.querySelector('.training-student-row');
                if (!template) return;

                const clone = template.cloneNode(true);
                clone.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                    input.classList.remove(...invalidClasses);
                });
                container.appendChild(clone);
                bindRow(clone);
                renumberRows();
                writeSavedData();
            });

            window.validateTrainingGroupStudents = (event, form) => {
                const messages = [];
                const usedEmails = new Map();
                errorList.innerHTML = '';
                container.querySelectorAll('input').forEach((input) => input.classList.remove(...invalidClasses));
                passwordInput.classList.remove(...invalidClasses);

                container.querySelectorAll('.training-student-row').forEach((row) => {
                    const firstname = row.querySelector('[data-field="firstname"], input[id*="firstname"]');
                    const lastname = row.querySelector('[data-field="lastname"], input[id*="lastname"]');
                    const email = row.querySelector('[data-field="email"], input[type="email"], input[id*="email"]');
                    const requiredFields = [firstname, lastname, email].filter(Boolean);
                    const emailValue = email?.value.trim().toLowerCase() || '';

                    requiredFields.forEach((field) => {
                        if (!field.value.trim()) {
                            field.classList.add(...invalidClasses);
                        }
                    });

                    if (requiredFields.length < 3 || requiredFields.some((field) => !field.value.trim())) {
                        messages.push('Veuillez compléter prénom, nom et e-mail pour chaque stagiaire ajouté.');
                    }

                    if ([firstname, lastname].some((field) => (field?.value.trim().length || 0) > 255)) {
                        firstname?.classList.add(...invalidClasses);
                        lastname?.classList.add(...invalidClasses);
                        messages.push('Le prénom et le nom ne doivent pas dépasser 255 caractères.');
                    }

                    if (email?.value.trim().length > 255) {
                        email.classList.add(...invalidClasses);
                        messages.push('L’adresse e-mail ne doit pas dépasser 255 caractères.');
                    }

                    if (emailValue && !isValidEmail(emailValue)) {
                        email.classList.add(...invalidClasses);
                        messages.push('Veuillez renseigner une adresse e-mail valide pour chaque stagiaire.');
                    }

                    if (emailValue && usedEmails.has(emailValue)) {
                        email?.classList.add(...invalidClasses);
                        usedEmails.get(emailValue)?.classList.add(...invalidClasses);
                        messages.push('Chaque stagiaire doit avoir une adresse e-mail différente.');
                    } else if (emailValue && isValidEmail(emailValue)) {
                        usedEmails.set(emailValue, email);
                    }
                });

                if (!passwordInput.value.trim()) {
                    passwordInput.classList.add(...invalidClasses);
                    messages.push('Veuillez renseigner le code d’accès provisoire.');
                }

                const uniqueMessages = [...new Set(messages)];
                if (uniqueMessages.length === 0) {
                    writeSavedData();
                    errorBox.classList.add('hidden');
                    if (event?.type === 'click') {
                        window.location.assign(form?.getAttribute('action') || nextButton.form?.getAttribute('action') || nextButton.dataset.nextUrl);
                        return false;
                    }
                    return true;
                }

                event.preventDefault();
                uniqueMessages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    errorList.appendChild(item);
                });
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            };
        })();
    </script>
</article>
@elseif (($activeLessonPart ?? null) === 'modules')
@php
    $trainingAvailableModules = [
        [
            'id' => 101,
            'title' => 'Securite alimentaire 2026',
            'lesson_count' => 5,
            'question_count' => 8,
            'duration_label' => '45 min',
        ],
        [
            'id' => 102,
            'title' => 'Hygiene en cuisine professionnelle',
            'lesson_count' => 4,
            'question_count' => 6,
            'duration_label' => '40 min',
        ],
        [
            'id' => 103,
            'title' => 'Nettoyage et desinfection des espaces',
            'lesson_count' => 4,
            'question_count' => 7,
            'duration_label' => '50 min',
        ],
        [
            'id' => 104,
            'title' => 'Conservation et chaine du froid',
            'lesson_count' => 4,
            'question_count' => 6,
            'duration_label' => '40 min',
        ],
        [
            'id' => 105,
            'title' => 'Allergenes et information client',
            'lesson_count' => 3,
            'question_count' => 5,
            'duration_label' => '35 min',
        ],
        [
            'id' => 106,
            'title' => 'Reception et stockage des denrees',
            'lesson_count' => 4,
            'question_count' => 6,
            'duration_label' => '45 min',
        ],
        [
            'id' => 107,
            'title' => 'Prevention des contaminations croisees',
            'lesson_count' => 5,
            'question_count' => 8,
            'duration_label' => '55 min',
        ],
        [
            'id' => 108,
            'title' => 'Equilibre alimentaire et menus',
            'lesson_count' => 3,
            'question_count' => 5,
            'duration_label' => '30 min',
        ],
        [
            'id' => 109,
            'title' => 'Gestion des dechets alimentaires',
            'lesson_count' => 3,
            'question_count' => 4,
            'duration_label' => '30 min',
        ],
    ];
    $trainingGroupCompletionUrl = route('formateur.parcours.lessons.part.complete', [
        'module' => $activeModuleKey,
        'chapter' => $activeChapterKey,
        'lesson' => $activeLessonKey,
        'part' => 'finalisation',
    ]);
@endphp
<article class="mx-auto w-full max-w-[1285px] rounded-[24px] border border-gray-100 bg-white p-5 shadow-sm md:p-6">
    <form
        id="training-group-modules-form"
        action="{{ $mixedPartUrls['finalisation'] ?? '#' }}"
        data-completion-url="{{ $trainingGroupCompletionUrl }}"
        data-csrf-token="{{ csrf_token() }}"
        class="space-y-6"
        onsubmit="window.validateTrainingGroupModules ? window.validateTrainingGroupModules(event, this) : event.preventDefault(); return false;"
    >
        <div id="training-group-modules-errors" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold">Certaines informations doivent être corrigées avant de continuer.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm" data-role="messages"></ul>
                </div>
            </div>
        </div>

        <div id="training-group-modules-success" class="hidden rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
            Le groupe est prêt : les modules sélectionnés sont organisés.
        </div>

        <section>
            <div class="mb-6">
                <div class="flex items-center gap-2">
                    <h2 class="font-raleway text-xl font-bold text-bleuone">Organisation des modules</h2>
                    <div class="relative group">
                        <button type="button" aria-label="Information sur le parcours pédagogique" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                            ?
                        </button>
                        <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-80 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                            Ajoutez les modules utiles pour ce groupe, organisez-les dans l’ordre souhaité, puis validez la création. Vous retrouverez ensuite la même interface dans l’édition pour ajuster le parcours.
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    Le rendu reprend déjà la même logique d’organisation que sur la fiche d’édition.
                </p>
                <p class="mt-2 text-sm font-semibold text-orangeone">
                    Pour valider l’activité, le groupe doit contenir les modules attendus, dans la bonne quantité et dans le bon ordre.
                </p>
            </div>

            <div
                data-group-module-flow
                data-mode="create"
                data-available-modules='@json($trainingAvailableModules)'
                data-available-parcours='[]'
                data-selected-modules='[]'
                data-storage-key="oneduc_training_group_creation"
                class="space-y-6"
            ></div>

            <p class="mt-3 inline-block rounded border border-blue-100 bg-blue-50 p-2 text-xs text-gray-500">
                L’ordre défini ici sera celui présenté à l’apprenant dans son espace.
            </p>
        </section>

        <div class="flex flex-col gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            @if (!empty($mixedPartUrls['stagiaires'] ?? null))
                <a
                    href="{{ $mixedPartUrls['stagiaires'] }}"
                    class="inline-flex items-center justify-center rounded-full border border-gray-300 px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-50"
                >
                    Précédent
                </a>
            @endif

            <button
                type="submit"
                id="training-group-modules-submit"
                class="inline-flex items-center justify-center rounded-full bg-orangeone px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600"
            >
                Créer le groupe
            </button>
        </div>
    </form>

    <script>
        (() => {
            const form = document.getElementById('training-group-modules-form');
            const errorBox = document.getElementById('training-group-modules-errors');
            const errorList = errorBox?.querySelector('[data-role="messages"]');
            const successBox = document.getElementById('training-group-modules-success');
            const submitButton = document.getElementById('training-group-modules-submit');

            if (!form || !errorBox || !errorList || !successBox || !submitButton) {
                return;
            }

            window.requestAnimationFrame(() => {
                window.dispatchEvent(new CustomEvent('oneduc:group-flow-refresh'));
            });

            const showMessages = (messages) => {
                errorList.innerHTML = '';
                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = message;
                    errorList.appendChild(item);
                });
                errorBox.classList.remove('hidden');
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            };

            window.validateTrainingGroupModules = async (event) => {
                event.preventDefault();

                const selectedModules = form.querySelectorAll('input[name="modules[]"]');
                errorList.innerHTML = '';
                successBox.classList.add('hidden');

                if (selectedModules.length === 0) {
                    showMessages(['Ajoutez au moins un module au parcours.']);
                    return false;
                }

                const savedData = JSON.parse(window.localStorage.getItem('oneduc_training_group_creation') || '{}');
                const selectedModuleData = Array.from(selectedModules).map((input) => {
                    const row = input.closest('tr');
                    return {
                        id: input.value,
                        title: row?.querySelector('td:nth-child(2)')?.textContent.trim() || `Module #${input.value}`,
                    };
                });
                const completionPayload = {
                    ...savedData,
                    modules: selectedModuleData,
                };

                submitButton.disabled = true;
                submitButton.classList.add('cursor-wait', 'opacity-70');

                try {
                    const response = await fetch(form.dataset.completionUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': form.dataset.csrfToken,
                        },
                        body: JSON.stringify(completionPayload),
                    });
                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        showMessages(Array.isArray(data.messages) && data.messages.length > 0
                            ? data.messages
                            : ['La création du groupe n’est pas encore complète.']);
                        return false;
                    }

                    window.localStorage.setItem('oneduc_training_group_creation', JSON.stringify(completionPayload));
                    errorBox.classList.add('hidden');
                    successBox.classList.remove('hidden');
                    window.location.assign(data.redirect_url || form.getAttribute('action') || '#');
                    return false;
                } catch (error) {
                    showMessages(['Impossible de valider l’activité pour le moment. Réessayez dans quelques instants.']);
                } finally {
                    submitButton.disabled = false;
                    submitButton.classList.remove('cursor-wait', 'opacity-70');
                }

                return false;
            };
        })();
    </script>
</article>
@elseif (($activeLessonPart ?? null) === 'finalisation')
@php
    $trainingGroupCompletionUrl = route('formateur.parcours.lessons.part.complete', [
        'module' => $activeModuleKey,
        'chapter' => $activeChapterKey,
        'lesson' => $activeLessonKey,
        'part' => 'finalisation',
    ]);
    $trainingGroupActivityKey = $currentLesson['completion_activity_key'] ?? null;
    $trainingGroupActivityStatusKey = $trainingGroupActivityKey
        ? implode('.', [$activeChapterKey, $activeLessonKey, $trainingGroupActivityKey])
        : null;
    $trainingGroupActivityCompleted = $trainingGroupActivityStatusKey
        ? (($activityStatusMap[$trainingGroupActivityStatusKey] ?? false) === true)
        : false;
    $trainingGroupReviewUrl = $currentLesson['url'] ?? ($mixedPartUrls['introduction'] ?? '#');
    $trainingGroupRestartUrl = $mixedPartUrls['informations'] ?? ($currentLesson['url'] ?? '#');
@endphp
<article
    class="mx-auto w-full max-w-[1285px] rounded-[24px] border border-gray-100 bg-white p-5 shadow-sm md:p-6"
    data-completion-url="{{ $trainingGroupCompletionUrl }}"
    data-csrf-token="{{ csrf_token() }}"
    data-activity-completed="{{ $trainingGroupActivityCompleted ? 'true' : 'false' }}"
>
    <main class="space-y-8">
        <section aria-labelledby="training-created-groups-title">
            <h2 id="training-created-groups-title" class="sr-only">Liste des groupes</h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div aria-disabled="true" class="flex min-h-[180px] cursor-not-allowed flex-col items-center justify-center rounded-[20px] border-4 border-dashed border-orangeone/40 p-10 text-lg font-semibold text-orangeone/50">
                    Ajouter un groupe
                </div>

                <article class="training-final-highlight-card flex flex-col rounded-[20px] border-2 border-orangeone bg-white p-6 shadow">
                    <div class="flex-1 space-y-5">
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <h3 id="training-final-group-name" class="truncate font-raleway text-xl font-bold text-bleuone">
                                        Groupe Hygiene alimentaire 2026
                                    </h3>
                                    <p class="mt-2 text-xs italic text-gray-400">
                                        Créé le {{ now()->format('d/m/Y') }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-vertone/20 bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">
                                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-vertone"></span>
                                        Actif
                                    </span>

                                    <div class="relative inline-flex group">
                                        <span class="inline-flex cursor-default items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                            1 co-formateur
                                        </span>
                                        <div class="pointer-events-none absolute right-0 top-full z-20 mt-2 hidden w-max max-w-xs rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 shadow-lg group-hover:block group-focus-within:block">
                                            <div>Karim Ben Ali</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p id="training-final-group-description" class="line-clamp-3 text-sm leading-7 text-gray-700">
                            Groupe de formation dédié aux bases de l'hygiène alimentaire.
                        </p>

                        <div class="space-y-4 rounded-2xl bg-gray-50/80 p-4">
                            <div>
                                <h4 class="mb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500">Modules associés</h4>
                                <div id="training-final-group-modules" class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center rounded-full bg-vertone/10 px-3 py-1 text-xs text-vertone">
                                        Securite alimentaire 2026
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-4">
                                <div>
                                    <h4 class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500">Stagiaires</h4>
                                </div>
                                <span id="training-final-group-students-count" class="shrink-0 cursor-default text-sm font-semibold text-orangeone">
                                    1 stagiaire
                                </span>
                            </div>

                            <div
                                id="training-final-group-students-list"
                                class="space-y-2"
                                aria-label="Stagiaires ajoutés au groupe"
                            >
                                <div class="rounded-xl border border-white bg-white px-3 py-2 text-sm text-slate-600">
                                    <span class="font-semibold text-bleuone">Marie Dupont</span>
                                    <span class="mt-0.5 block text-xs font-medium text-slate-400">marie.dupont@email.fr</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <button type="button" disabled class="btn-oneduc w-1/2 cursor-not-allowed text-center opacity-50">
                            Modifier
                        </button>
                        <button type="button" disabled class="btn-oneduc-blue w-1/2 cursor-not-allowed opacity-50">
                            Supprimer
                        </button>
                    </div>
                </article>
            </div>
        </section>

        <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-end">
            <a
                href="{{ $trainingGroupReviewUrl }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-orangeone hover:text-orangeone"
            >
                Revoir la leçon
            </a>

            <button
                type="button"
                id="training-group-restart-activity"
                data-restart-url="{{ $trainingGroupRestartUrl }}"
                class="inline-flex items-center justify-center rounded-full border border-orangeone bg-white px-5 py-3 text-sm font-bold text-orangeone transition hover:bg-orange-50"
            >
                Recommencer l’activité
            </button>

            @if (!empty($nextLesson['url'] ?? null))
                <a href="{{ $nextLesson['url'] }}" class="btn-oneduc !rounded-full !px-7 !py-3">
                    Leçon suivante
                </a>
            @endif
        </div>
    </main>

    <script>
        (() => {
            const container = document.currentScript.closest('article');
            const savedData = JSON.parse(window.localStorage.getItem('oneduc_training_group_creation') || '{}');
            const groupName = document.getElementById('training-final-group-name');
            const description = document.getElementById('training-final-group-description');
            const modulesList = document.getElementById('training-final-group-modules');
            const studentsCount = document.getElementById('training-final-group-students-count');
            const studentsList = document.getElementById('training-final-group-students-list');
            const restartButton = document.getElementById('training-group-restart-activity');
            const completionReloadKey = 'oneduc_training_group_finalisation_reloaded';

            if (groupName && savedData.name) {
                groupName.textContent = savedData.name;
            }

            if (description) {
                description.textContent = savedData.description || 'Groupe de formation dédié aux bases de l hygiene alimentaire.';
            }

            if (modulesList) {
                const modules = Array.isArray(savedData.modules) ? savedData.modules : [];
                modulesList.innerHTML = '';

                if (modules.length === 0) {
                    const item = document.createElement('span');
                    item.className = 'inline-flex items-center rounded-full bg-vertone/10 px-3 py-1 text-xs text-vertone';
                    item.textContent = 'Securite alimentaire 2026';
                    modulesList.appendChild(item);
                } else {
                    modules.forEach((module) => {
                        const item = document.createElement('span');
                        item.className = 'inline-flex items-center rounded-full bg-vertone/10 px-3 py-1 text-xs text-vertone';
                        item.textContent = module.title || `Module #${module.id}`;
                        modulesList.appendChild(item);
                    });
                }
            }

            if (studentsCount) {
                const count = Array.isArray(savedData.students) ? savedData.students.length : 0;
                studentsCount.textContent = `${Math.max(1, count)} stagiaire${Math.max(1, count) > 1 ? 's' : ''}`;
            }

            if (studentsList) {
                const students = Array.isArray(savedData.students)
                    ? savedData.students.filter((student) => student.firstname || student.lastname || student.email)
                    : [];

                studentsList.innerHTML = '';

                if (students.length === 0) {
                    const item = document.createElement('div');
                    item.className = 'rounded-xl border border-white bg-white px-3 py-2 text-sm text-slate-600';
                    item.innerHTML = '<span class="font-semibold text-bleuone">Marie Dupont</span><span class="mt-0.5 block text-xs font-medium text-slate-400">marie.dupont@email.fr</span>';
                    studentsList.appendChild(item);
                } else {
                    students.forEach((student) => {
                        const item = document.createElement('div');
                        const name = `${student.firstname || ''} ${student.lastname || ''}`.trim() || 'Stagiaire';
                        const email = student.email || 'Adresse e-mail non renseignée';

                        item.className = 'rounded-xl border border-white bg-white px-3 py-2 text-sm text-slate-600';

                        const nameNode = document.createElement('span');
                        nameNode.className = 'font-semibold text-bleuone';
                        nameNode.textContent = name;

                        const emailNode = document.createElement('span');
                        emailNode.className = 'mt-0.5 block text-xs font-medium text-slate-400';
                        emailNode.textContent = email;

                        item.appendChild(nameNode);
                        item.appendChild(emailNode);
                        studentsList.appendChild(item);
                    });
                }
            }

            const markFinalisationCompleted = async () => {
                if (!container || container.dataset.activityCompleted === 'true') {
                    window.sessionStorage.removeItem(completionReloadKey);
                    return;
                }

                if (!container.dataset.completionUrl || !Array.isArray(savedData.modules) || savedData.modules.length === 0) {
                    return;
                }

                try {
                    const response = await fetch(container.dataset.completionUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': container.dataset.csrfToken,
                        },
                        body: JSON.stringify(savedData),
                    });
                    const data = await response.json();

                    if (response.ok && data.success && window.sessionStorage.getItem(completionReloadKey) !== 'done') {
                        window.localStorage.setItem('oneduc_training_group_creation', JSON.stringify({
                            ...savedData,
                            __activityCompleted: true,
                        }));
                        window.sessionStorage.setItem(completionReloadKey, 'done');
                        window.location.reload();
                    }
                } catch (error) {
                    // Le bouton suivant reste disponible : la validation principale se fait deja a la creation.
                }
            };

            restartButton?.addEventListener('click', () => {
                window.localStorage.removeItem('oneduc_training_group_creation');
                window.sessionStorage.removeItem(completionReloadKey);
                window.location.assign(restartButton.dataset.restartUrl || window.location.href);
            });

            markFinalisationCompleted();
        })();
    </script>

    <style>
        @keyframes training-final-highlight-pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 18px 40px rgba(233, 77, 42, 0.12);
            }

            50% {
                transform: scale(1.025);
                box-shadow: 0 22px 48px rgba(233, 77, 42, 0.22);
            }
        }

        .training-final-highlight-card {
            transform-origin: center;
            animation: training-final-highlight-pulse 2.4s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .training-final-highlight-card {
                animation: none;
            }
        }
    </style>
</article>
@else
<article class="mx-auto w-full max-w-[1285px] rounded-[24px] border border-gray-100 bg-white p-5 shadow-sm md:p-6">
    <div class="flex flex-col gap-2 border-b border-gray-100 pb-5 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-orangeone">Formulaire</p>
            <h2 class="mt-1 font-raleway text-2xl font-medium text-bleuone">Creation du groupe</h2>
        </div>
        <p class="max-w-xl text-sm leading-6 text-slate-500">
            Zone d entrainement pour renseigner les informations principales avant la creation reelle du groupe.
        </p>
    </div>

    <form class="mt-6 space-y-6">
        <section class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Nom du groupe</span>
                <input type="text" value="Hygiene alimentaire 2026" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone/20">
            </label>

            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Date de debut</span>
                <input type="date" value="2026-04-08" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone/20">
            </label>
        </section>

        <label class="block">
            <span class="text-sm font-semibold text-slate-700">Description courte</span>
            <textarea rows="3" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-700 shadow-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone/20">Groupe de formation dedie aux bases de l hygiene alimentaire.</textarea>
        </label>

        <section class="grid gap-4 md:grid-cols-3">
            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4">
                <input type="checkbox" checked class="mt-1 rounded border-slate-300 text-orangeone focus:ring-orangeone">
                <span>
                    <span class="block text-sm font-semibold text-slate-700">Visible</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Le groupe apparait dans l espace formateur.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4">
                <input type="checkbox" checked class="mt-1 rounded border-slate-300 text-orangeone focus:ring-orangeone">
                <span>
                    <span class="block text-sm font-semibold text-slate-700">Ouvert</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Les stagiaires pourront acceder au groupe.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4">
                <input type="checkbox" class="mt-1 rounded border-slate-300 text-orangeone focus:ring-orangeone">
                <span>
                    <span class="block text-sm font-semibold text-slate-700">Co-formateur</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Un autre formateur pourra accompagner le groupe.</span>
                </span>
            </label>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Parcours associe</span>
                <select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone/20">
                    <option>Parcours Hygiene alimentaire</option>
                    <option>Parcours Certification TOSA</option>
                    <option>Parcours Internet</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-semibold text-slate-700">Module principal</span>
                <select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orangeone/20">
                    <option>Module Hygiene alimentaire</option>
                    <option>Module Excel avance</option>
                    <option>Module Internet</option>
                </select>
            </label>
        </section>

        <div class="flex flex-col gap-3 border-t border-gray-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs leading-5 text-slate-500">
                Ce formulaire est integre au parcours pour l entrainement. La creation definitive se fera ensuite dans l espace groupes.
            </p>
            <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full bg-slate-200 px-5 py-3 text-sm font-bold text-slate-500" aria-disabled="true">
                Simulation uniquement
            </span>
        </div>
    </form>
</article>
@endif
