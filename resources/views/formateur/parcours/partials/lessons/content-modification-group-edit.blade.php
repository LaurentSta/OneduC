@php
    $introUrl = $mixedPartUrls['modifier-contenu'] ?? '#';
    $finalisationUrl = $mixedPartUrls['modifier-contenu-finalisation'] ?? '#';
    $students = [
        ['prenom' => 'Amina', 'nom' => 'Diallo', 'email' => 'amina.diallo@example.fr', 'created_at' => '12/01/2026'],
        ['prenom' => 'Lucas', 'nom' => 'Moreau', 'email' => 'lucas.moreau@example.fr', 'created_at' => '13/01/2026'],
        ['prenom' => 'Marc', 'nom' => 'Lefebvre', 'email' => 'marc.lefebvre@example.fr', 'created_at' => '14/01/2026'],
        ['prenom' => 'Sofia', 'nom' => 'Martin', 'email' => 'sofia.martin@example.fr', 'created_at' => '15/01/2026'],
        ['prenom' => 'Youssef', 'nom' => 'Benali', 'email' => 'youssef.benali@example.fr', 'created_at' => '16/01/2026'],
    ];
    $targetModuleId = 104;
    $availableModulesForFlow = [
        [
            'id' => 101,
            'title' => 'Hygiene alimentaire',
            'lesson_count' => 4,
            'question_count' => 0,
            'duration_label' => '25 min',
        ],
        [
            'id' => 102,
            'title' => 'Bonnes pratiques',
            'lesson_count' => 3,
            'question_count' => 0,
            'duration_label' => '20 min',
        ],
        [
            'id' => 103,
            'title' => 'Evaluation finale',
            'lesson_count' => 1,
            'question_count' => 5,
            'duration_label' => '10 min',
        ],
        [
            'id' => $targetModuleId,
            'title' => 'Conservation des aliments et DLC',
            'lesson_count' => 2,
            'question_count' => 4,
            'duration_label' => '12 min',
        ],
    ];
    $selectedModulesForFlow = [
        [
            'id' => 101,
            'title' => 'Hygiene alimentaire',
            'position' => 1,
            'persisted' => true,
            'manage_url' => '',
            'lesson_count' => 4,
            'question_count' => 0,
            'duration_label' => '25 min',
        ],
        [
            'id' => 102,
            'title' => 'Bonnes pratiques',
            'position' => 2,
            'persisted' => true,
            'manage_url' => '',
            'lesson_count' => 3,
            'question_count' => 0,
            'duration_label' => '20 min',
        ],
        [
            'id' => 103,
            'title' => 'Evaluation finale',
            'position' => 3,
            'persisted' => true,
            'manage_url' => '',
            'lesson_count' => 1,
            'question_count' => 5,
            'duration_label' => '10 min',
        ],
    ];
@endphp

<div
    x-data="{
        activeTab: 'general',
        saved: false,
        feedback: null,
        showInstructions: false,
        save() {
            const selectedTargetModule = document.querySelector('input[name=&quot;modules[]&quot;][value=&quot;{{ $targetModuleId }}&quot;]');

            if (!selectedTargetModule) {
                this.feedback = {
                    title: 'Module non ajoute',
                    body: 'Dans l onglet Modules, selectionnez Conservation des aliments et DLC puis cliquez sur Ajouter un module avant d enregistrer.'
                };
                this.activeTab = 'parcours';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            window.location.href = @js($finalisationUrl);
        }
    }"
    class="mx-auto w-full max-w-[1285px] space-y-6"
>
    <header class="rounded-[20px] border border-bleuone/10 bg-white px-6 py-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Etape 2</p>
                <h1 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Reconstruire un groupe</h1>
                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Modifier un contenu pour pouvoir rajouter un module
                </p>
            </div>

            <button
                type="button"
                @click="showInstructions = true"
                class="consigne-invite inline-flex h-12 items-center justify-center gap-3 rounded-full border-2 border-orangeone/30 bg-orangeone/10 px-6 text-base font-bold text-orangeone shadow-sm transition hover:border-orangeone hover:bg-orangeone hover:text-white"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Consigne
            </button>
        </div>
    </header>

    <div x-show="showInstructions" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/45" @click="showInstructions = false"></div>
        <section
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative mx-auto mt-24 w-[calc(100%-2rem)] max-w-lg rounded-[20px] border border-orangeone/20 bg-white p-6 shadow-[0_28px_80px_-24px_rgba(0,68,97,0.55),0_18px_36px_-22px_rgba(239,75,43,0.55)]"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-orangeone">Consigne</p>
                    <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Ajouter un module</h2>
                </div>
                <button type="button" @click="showInstructions = false" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            <ol class="mt-5 list-decimal space-y-3 pl-5 text-base leading-7 text-slate-700">
                <li>Cliquez sur l'onglet Modules.</li>
                <li>Ajoutez le module <span class="font-bold text-orangeone">Conservation des aliments et DLC</span>.</li>
                <li>Vérifiez qu'il apparaît dans l’enchaînement du parcours.</li>
                <li>Enregistrez les modifications.</li>
            </ol>
        </section>
    </div>

    <main class="rounded-[20px] bg-white px-8 py-8 shadow-md">
        <div
            x-show="feedback"
            x-cloak
            class="mb-6 rounded-[18px] border border-orangeone/25 bg-orangeone/10 px-5 py-4 text-sm leading-6 text-orangeone"
        >
            <p class="font-bold" x-text="feedback ? feedback.title : ''"></p>
            <p class="mt-1" x-text="feedback ? feedback.body : ''"></p>
        </div>

        <nav aria-label="Sections du groupe">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <button type="button"
                    @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                    class="flex w-full items-center justify-center gap-2 rounded-full px-6 py-4 font-varela text-lg font-bold transition focus:outline-none">
                    <span>1.</span>
                    <span>Informations</span>
                </button>

                <button type="button"
                    @click="activeTab = 'stagiaires'"
                    :class="activeTab === 'stagiaires' ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                    class="flex w-full items-center justify-center gap-2 rounded-full px-6 py-4 font-varela text-lg font-bold transition focus:outline-none">
                    <span>2.</span>
                    <span>Stagiaires</span>
                </button>

                <button type="button"
                    @click="activeTab = 'parcours'"
                    :class="activeTab === 'parcours' ? 'bg-bleuone text-white shadow-md ring-2 ring-bleuone ring-offset-2' : 'bg-white text-bleuone border border-bleuone hover:bg-bleuone/5'"
                    class="flex w-full items-center justify-center gap-2 rounded-full px-6 py-4 font-varela text-lg font-bold transition focus:outline-none">
                    <span>3.</span>
                    <span>Modules</span>
                </button>
            </div>
            <div class="mb-2 mt-6 h-1 w-full rounded bg-gray-100"></div>
        </nav>

        <section x-show="activeTab === 'general'" x-cloak class="animate-fade-in-down pt-6">
            <div class="mb-6">
                <label for="sim_group_name" class="mb-2 block text-base font-medium text-gray-900">Nom du groupe</label>
                <input id="sim_group_name" type="text" value="Hygiene alimentaire 2026"
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-base focus:border-orangeone focus:ring-orangeone">
            </div>

            <div class="mb-6">
                <label for="sim_group_description" class="mb-2 block text-base font-medium text-gray-900">Description</label>
                <textarea id="sim_group_description" rows="3"
                          class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-base focus:border-orangeone focus:ring-orangeone">Groupe utilise pour organiser les ressources, les exercices et le suivi des stagiaires du parcours hygiene.</textarea>
            </div>

            <details class="mb-6">
                <summary class="inline-flex cursor-pointer items-center gap-3 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm marker:hidden">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4" />
                        </svg>
                    </span>
                    <span>Options</span>
                </summary>

                <div class="mt-3 grid gap-4 rounded-[18px] border border-gray-200 bg-white px-4 py-4 lg:grid-cols-2">
                    <div class="space-y-4 rounded-[18px] border border-sky-200 bg-sky-50/80 px-4 py-4">
                        <div class="rounded-[18px] border border-white/70 bg-white/80 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <label class="flex items-center gap-3 text-base font-medium text-gray-900">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9m6.364-5.364a9 9 0 11-12.728 0" />
                                        </svg>
                                    </span>
                                    <span>Activer le groupe</span>
                                </label>
                                <span class="relative inline-flex h-7 w-12 rounded-full bg-vertone after:absolute after:left-6 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:content-['']"></span>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="rounded-[18px] border border-white/70 bg-white/80 px-4 py-4">
                                <span class="mb-2 block text-base font-medium text-gray-900">Date de demarrage</span>
                                <input type="date" value="2026-02-02" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base focus:border-orangeone focus:ring-orangeone">
                            </label>
                            <label class="rounded-[18px] border border-white/70 bg-white/80 px-4 py-4">
                                <span class="mb-2 block text-base font-medium text-gray-900">Date de fin</span>
                                <input type="date" value="2026-03-27" class="block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-base focus:border-orangeone focus:ring-orangeone">
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[18px] border border-orange-200 bg-orange-50/80 px-4 py-4">
                        <h3 class="font-raleway text-lg font-bold text-bleuone">Co-formateurs</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Aucun co-formateur ajoute pour cette simulation.</p>
                    </div>
                </div>
            </details>

            <div class="flex justify-end">
                <button type="button" @click="save()" class="btn-oneduc px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
                    Enregistrer les modifications
                </button>
            </div>
        </section>

        <section x-show="activeTab === 'stagiaires'" x-cloak class="animate-fade-in-down pt-6">
            <div class="mb-4 flex items-center gap-2">
                <h3 class="font-raleway text-xl font-bold text-bleuone">Liste des stagiaires</h3>
            </div>

            <div class="mb-8 overflow-hidden rounded-[12px] border border-gray-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-bleuone text-xs font-bold uppercase text-white">
                        <tr>
                            <th class="px-6 py-3">Nom complet</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Date de creation</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($students as $student)
                            <tr class="{{ $loop->odd ? 'bg-white' : 'bg-slate-50' }} transition hover:bg-orangeone/5">
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    {{ $student['prenom'] }} <span class="uppercase">{{ $student['nom'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ $student['email'] }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $student['created_at'] }}</td>
                                <td class="px-6 py-3 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <button type="button" class="inline-flex cursor-not-allowed items-center justify-center rounded-full p-2 text-bleuone opacity-45" disabled title="Voir le profil">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.97 9.97 0 0112 15c2.58 0 4.933.975 6.879 2.58M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="inline-flex cursor-not-allowed items-center justify-center rounded-full p-2 text-orangeone opacity-45" disabled title="Retirer du groupe">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-[12px] border border-orangeone/20 bg-orangeone/5 p-4">
                <h4 class="font-raleway text-sm font-bold text-gray-800">Mot de passe provisoire du groupe</h4>
                <input type="text" value="Formation2026!" class="mt-3 block w-full max-w-sm rounded-lg border border-gray-300 bg-white px-3 py-2.5 font-mono text-sm tracking-wide focus:border-orangeone focus:ring-orangeone">
            </div>
        </section>

        <section x-show="activeTab === 'parcours'" x-cloak class="animate-fade-in-down pt-6">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 class="font-raleway text-xl font-bold text-bleuone">Organisation des modules</h3>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Le groupe a terminé plus vite que prévu. Ajoutez un module complémentaire utile, puis vérifiez l’enchaînement du parcours.
                    </p>
                </div>
            </div>

            <div
                data-group-module-flow
                data-mode="edit"
                data-available-modules='@json($availableModulesForFlow)'
                data-available-parcours='[]'
                data-selected-modules='@json($selectedModulesForFlow)'
                data-initial-parcours-id=""
                data-manage-lessons-label="Gérer les leçons"
                class="space-y-6"
            ></div>

            <div class="mt-4 rounded-[14px] border border-blue-100 bg-blue-50 px-4 py-3 text-sm leading-6 text-slate-600">
                Sélectionnez <span class="font-bold text-bleuone">Conservation des aliments et DLC</span> dans la liste des modules, ajoutez-le, puis contrôlez sa place dans l’enchaînement.
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="save()" class="btn-oneduc px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
                    Enregistrer les modifications
                </button>
            </div>
        </section>
    </main>
</div>
