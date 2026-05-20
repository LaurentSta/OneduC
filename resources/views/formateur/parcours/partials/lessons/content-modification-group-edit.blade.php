@php
    $introUrl = $mixedPartUrls['modifier-contenu'] ?? '#';
    $students = [
        ['prenom' => 'Amina', 'nom' => 'Diallo', 'email' => 'amina.diallo@example.fr', 'created_at' => '12/01/2026'],
        ['prenom' => 'Lucas', 'nom' => 'Moreau', 'email' => 'lucas.moreau@example.fr', 'created_at' => '13/01/2026'],
        ['prenom' => 'Marc', 'nom' => 'Lefebvre', 'email' => 'marc.lefebvre@example.fr', 'created_at' => '14/01/2026'],
        ['prenom' => 'Sofia', 'nom' => 'Martin', 'email' => 'sofia.martin@example.fr', 'created_at' => '15/01/2026'],
        ['prenom' => 'Youssef', 'nom' => 'Benali', 'email' => 'youssef.benali@example.fr', 'created_at' => '16/01/2026'],
    ];
@endphp

<div
    x-data="{
        activeTab: 'general',
        addedContent: false,
        saved: false,
        chooseContent() {
            this.addedContent = true;
            this.activeTab = 'parcours';
        },
        save() {
            this.saved = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }"
    class="mx-auto w-full max-w-[1285px] space-y-6"
>
    <header class="rounded-[20px] bg-white px-8 py-6 shadow-md">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-9">
                <h1 class="font-raleway text-3xl font-semibold leading-tight text-bleuone">
                    Modification du groupe : <br>
                    <span class="text-orangeone">Hygiene alimentaire 2026</span>
                </h1>
                <p class="mt-3 font-varela text-gray-600">
                    Gere la configuration, la liste des apprenants et l'ordre pedagogique des modules.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-3 font-varela text-sm">
                    <div class="inline-flex items-center gap-2 rounded-full border border-vertone/20 bg-vertone/10 px-3 py-1 text-vertone">
                        <span class="font-bold">18</span> Stagiaires
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-orangeone/20 bg-orangeone/10 px-3 py-1 text-orangeone">
                        <span class="font-bold" x-text="addedContent ? 4 : 3"></span> Modules
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-blue-700">
                        Groupe suivi
                    </div>
                </div>

                <nav class="mt-4 text-sm font-varela text-gray-600" aria-label="Fil d'Ariane">
                    <ol class="inline-flex items-center space-x-1">
                        <li class="flex items-center">
                            <a href="{{ $introUrl }}" class="text-bleuone hover:underline">Mes groupes</a>
                            <span class="mx-2 text-gray-400">/</span>
                        </li>
                        <li class="text-gray-400">Modifier Hygiene alimentaire 2026</li>
                    </ol>
                </nav>
            </div>

            <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
                <img src="{{ asset('images/svg/Groupes.svg') }}" alt="Illustration" class="h-auto max-w-[220px] opacity-80">
            </div>
        </div>
    </header>

    <main class="rounded-[20px] bg-white px-8 py-8 shadow-md">
        <div
            x-show="saved"
            x-cloak
            class="mb-6 rounded-[18px] border border-vertone/20 bg-vertone/10 px-5 py-4 text-sm leading-6 text-vertone"
        >
            <p class="font-bold">Modification enregistree dans le simulateur.</p>
            <p class="mt-1">Le contenu complementaire est maintenant associe au groupe Hygiene alimentaire 2026.</p>
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
                        Le groupe a termine plus vite que prevu. Ajoutez un contenu complementaire utile, puis enregistrez.
                    </p>
                </div>
                <button type="button" @click="chooseContent()" class="btn-oneduc px-6 py-3">
                    Ajouter le contenu choisi
                </button>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
                <div class="rounded-[18px] border border-gray-200 bg-white p-5">
                    <h4 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-gray-500">Parcours actuel</h4>

                    <div class="space-y-3">
                        <div class="flex items-center gap-4 rounded-[16px] border border-gray-200 bg-slate-50 px-4 py-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-bleuone text-sm font-bold text-white">1</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-bleuone">Hygiene alimentaire</p>
                                <p class="text-xs text-slate-500">4 lecons · 25 min</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-[16px] border border-gray-200 bg-slate-50 px-4 py-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-bleuone text-sm font-bold text-white">2</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-bleuone">Bonnes pratiques</p>
                                <p class="text-xs text-slate-500">3 lecons · 20 min</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 rounded-[16px] border border-gray-200 bg-slate-50 px-4 py-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-bleuone text-sm font-bold text-white">3</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-bleuone">Evaluation finale</p>
                                <p class="text-xs text-slate-500">1 quiz · 10 min</p>
                            </div>
                        </div>

                        <div
                            x-show="addedContent"
                            x-cloak
                            class="flex items-center gap-4 rounded-[16px] border border-vertone/30 bg-vertone/10 px-4 py-4"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-vertone text-sm font-bold text-white">4</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-bleuone">Conservation des aliments et DLC</p>
                                <p class="text-xs text-slate-500">Contenu complementaire · 12 min</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-vertone">Ajoute</span>
                        </div>
                    </div>
                </div>

                <aside class="rounded-[18px] border border-orangeone/20 bg-orangeone/5 p-5">
                    <h4 class="font-raleway text-lg font-bold text-bleuone">Contenu propose</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Le contenu le plus pertinent ici est une sequence courte sur la conservation des aliments et les dates limites.
                    </p>

                    <div class="mt-5 rounded-[16px] border border-white bg-white p-4 shadow-sm">
                        <p class="font-bold text-bleuone">Conservation des aliments et DLC</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Rappel utile pour approfondir la formation sans refaire le programme deja acquis.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">12 min</span>
                            <span class="rounded-full bg-bleuone/10 px-3 py-1 text-xs font-bold text-bleuone">Approfondissement</span>
                        </div>
                    </div>

                    <button type="button" @click="chooseContent()" class="btn-oneduc mt-5 w-full py-3">
                        Selectionner ce contenu
                    </button>
                </aside>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="save()" class="btn-oneduc px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
                    Enregistrer les modifications
                </button>
            </div>
        </section>
    </main>
</div>
