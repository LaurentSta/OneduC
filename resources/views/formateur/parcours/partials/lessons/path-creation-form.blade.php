@if (($activeLessonPart ?? null) === 'remplir-formulaire')
<article class="mx-auto w-full max-w-[1285px] rounded-[24px] border border-gray-100 bg-white p-5 shadow-sm md:p-6">
    <form id="training-path-creation-form" class="space-y-7" novalidate>
        <div id="training-path-creation-errors" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-bold">Certaines informations doivent etre completees avant de continuer.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm" data-role="messages"></ul>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="space-y-5 rounded-[22px] border border-slate-200 bg-slate-50/70 p-5">
                <div>
                    <label for="training-path-name" class="mb-2 block text-base font-medium text-gray-900">Nom du parcours</label>
                    <input
                        id="training-path-name"
                        type="text"
                        required
                        class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone"
                        placeholder="Ex : Parcours hygiene alimentaire 2026"
                    >
                </div>

                <div>
                    <label for="training-path-description" class="mb-2 block text-base font-medium text-gray-900">Description</label>
                    <textarea
                        id="training-path-description"
                        rows="3"
                        class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone"
                        placeholder="Objectif, public, contexte..."
                    ></textarea>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="training-path-status" class="mb-2 block text-base font-medium text-gray-900">Visibilite</label>
                        <select id="training-path-status" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone">
                            <option value="draft">Brouillon</option>
                            <option value="visible" selected>Visible</option>
                        </select>
                    </div>

                    <div>
                        <label for="training-path-access" class="mb-2 block text-base font-medium text-gray-900">Acces</label>
                        <select id="training-path-access" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base text-gray-900 focus:border-orangeone focus:ring-orangeone">
                            <option value="progressive" selected>Progressif</option>
                            <option value="free">Libre</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="rounded-[22px] border border-orange-200 bg-orange-50/80 p-5">
                <h3 class="text-base font-medium text-gray-900">Modules disponibles</h3>
                <p class="mt-2 text-sm leading-6 text-gray-600">Selectionnez les modules a placer dans le parcours.</p>

                <div class="mt-5 space-y-3" data-role="module-list">
                    @foreach ([
                        'Introduction hygiene alimentaire',
                        'Bonnes pratiques en cuisine',
                        'Controle et tracabilite',
                    ] as $index => $moduleLabel)
                        <label class="flex cursor-pointer items-start gap-3 rounded-[16px] border border-white/70 bg-white px-4 py-3 text-sm font-semibold text-gray-800 shadow-sm">
                            <input type="checkbox" class="mt-1 rounded border-gray-300 text-orangeone focus:ring-orangeone" value="{{ $moduleLabel }}" @checked($index < 2)>
                            <span>{{ $moduleLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="flex flex-col gap-4 rounded-[22px] border border-sky-200 bg-sky-50/80 p-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-base font-medium text-gray-900">Progression construite</h3>
                <p class="mt-1 text-sm leading-6 text-gray-600">Le parcours doit contenir au moins deux modules pour valider la simulation.</p>
            </div>

            @if (!empty($mixedPartUrls['felicitations'] ?? null))
                <a
                    href="{{ $mixedPartUrls['felicitations'] }}"
                    id="training-path-creation-next"
                    class="inline-flex items-center justify-center rounded-full bg-orangeone px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600"
                >
                    Creer le parcours
                </a>
            @endif
        </div>
    </form>

    <script>
        (() => {
            const form = document.getElementById('training-path-creation-form');
            const nextButton = document.getElementById('training-path-creation-next');
            const errorBox = document.getElementById('training-path-creation-errors');
            const errorList = errorBox?.querySelector('[data-role="messages"]');

            if (!form || !nextButton || !errorBox || !errorList) {
                return;
            }

            const nameInput = document.getElementById('training-path-name');
            const descriptionInput = document.getElementById('training-path-description');
            const statusInput = document.getElementById('training-path-status');
            const accessInput = document.getElementById('training-path-access');
            const moduleInputs = Array.from(form.querySelectorAll('[data-role="module-list"] input[type="checkbox"]'));
            const invalidClasses = ['border-red-500', 'bg-red-50'];
            const storageKey = 'oneduc_training_path_creation';

            nameInput?.addEventListener('input', () => nameInput.classList.remove(...invalidClasses));

            nextButton.addEventListener('click', (event) => {
                const missingFields = [];
                const selectedModules = moduleInputs.filter((input) => input.checked).map((input) => input.value);

                if (!nameInput.value.trim()) {
                    missingFields.push('le nom du parcours');
                    nameInput.classList.add(...invalidClasses);
                }

                if (selectedModules.length < 2) {
                    missingFields.push('au moins deux modules');
                }

                if (missingFields.length === 0) {
                    window.localStorage.setItem(storageKey, JSON.stringify({
                        name: nameInput.value.trim(),
                        description: descriptionInput.value.trim(),
                        status: statusInput.value,
                        access: accessInput.value,
                        modules: selectedModules,
                    }));
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
@endif
