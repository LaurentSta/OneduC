@php
    $groupEditSimulationUrl = $mixedPartUrls['modifier-contenu-groupe'] ?? '#';
    $groups = [
        [
            'name' => 'Hygiene alimentaire 2026',
            'created_at' => '14/01/2026',
            'active' => true,
            'description' => 'Groupe utilise pour organiser les ressources, les exercices et le suivi des stagiaires du parcours hygiene.',
            'start' => '02/02/2026',
            'end' => '27/03/2026',
            'modules' => ['Hygiene alimentaire', 'Bonnes pratiques', 'Evaluation finale'],
            'students' => 18,
            'target' => true,
        ],
        [
            'name' => 'Accueil securite',
            'created_at' => '08/01/2026',
            'active' => true,
            'description' => 'Parcours court pour les nouveaux arrivants avec les consignes essentielles de securite.',
            'start' => '19/01/2026',
            'end' => '06/02/2026',
            'modules' => ['Consignes securite', 'Risques courants'],
            'students' => 12,
            'target' => false,
        ],
        [
            'name' => 'Excel avance',
            'created_at' => '22/12/2025',
            'active' => true,
            'description' => 'Groupe de perfectionnement dedie aux tableaux croises, formules avancees et automatisations simples.',
            'start' => '05/01/2026',
            'end' => '20/02/2026',
            'modules' => ['Tableaux croises', 'Formules avancees'],
            'students' => 9,
            'target' => false,
        ],
        [
            'name' => 'Management de proximite',
            'created_at' => '16/12/2025',
            'active' => true,
            'description' => 'Sequence pour accompagner les responsables dans les entretiens, les retours et le suivi terrain.',
            'start' => '12/01/2026',
            'end' => '13/03/2026',
            'modules' => ['Posture manageriale', 'Feedback'],
            'students' => 15,
            'target' => false,
        ],
        [
            'name' => 'Parcours integration',
            'created_at' => '02/12/2025',
            'active' => true,
            'description' => 'Socle commun pour accueillir les nouveaux collaborateurs et leur donner les premiers reperes.',
            'start' => '06/01/2026',
            'end' => '31/01/2026',
            'modules' => ['Bienvenue', 'Outils internes', 'Premiers pas'],
            'students' => 24,
            'target' => false,
        ],
        [
            'name' => 'Bureautique niveau 1',
            'created_at' => '18/11/2025',
            'active' => false,
            'description' => 'Ancien groupe conserve pour consultation et reprise de contenus si besoin.',
            'start' => '25/11/2025',
            'end' => '20/12/2025',
            'modules' => ['Traitement de texte', 'Messagerie'],
            'students' => 7,
            'target' => false,
        ],
    ];
@endphp

<div class="mx-auto w-full max-w-[1285px]" x-data="{ selectedGroup: false }">
    <section class="rounded-[20px] bg-white px-6 py-6 shadow-md sm:px-8" aria-labelledby="simulation-groups-title">
        <div class="mb-6">
            <div>
                <h2 id="simulation-groups-title" class="font-raleway text-2xl font-semibold text-bleuone">
                    Mes groupes de formation
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Simulation de la page formateur/groupes avec six groupes deja existants.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article class="flex min-h-[395px] cursor-not-allowed flex-col items-center justify-center rounded-[20px] border-4 border-dashed border-orangeone bg-slate-50/40 p-10 text-orangeone opacity-85">
                <span class="font-varela text-lg font-bold">
                    Ajouter un groupe
                </span>
            </article>

            @foreach ($groups as $group)
                <article @class([
                    'flex min-h-[395px] flex-col rounded-[20px] border bg-white p-6 shadow',
                    'border-orangeone/45 ring-2 ring-orangeone/10' => $group['target'],
                    'border-gray-200' => ! $group['target'],
                ])>
                    <div class="flex-1 space-y-5">
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="truncate font-raleway text-xl font-bold text-bleuone">
                                        {{ $group['name'] }}
                                    </h3>
                                    <p class="mt-2 text-xs italic text-gray-400 font-lisible">
                                        Cree le {{ $group['created_at'] }}
                                    </p>
                                </div>

                                <span @class([
                                    'inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold',
                                    'border-vertone/20 bg-vertone/10 text-vertone' => $group['active'],
                                    'border-gray-200 bg-gray-100 text-gray-600' => ! $group['active'],
                                ])>
                                    <span @class([
                                        'inline-flex h-2.5 w-2.5 rounded-full',
                                        'bg-vertone' => $group['active'],
                                        'bg-gray-400' => ! $group['active'],
                                    ])></span>
                                    {{ $group['active'] ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>

                        <p class="text-sm leading-7 text-gray-700 font-lisible">
                            {{ $group['description'] }}
                        </p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-3 rounded-2xl border border-bleuone/15 bg-slate-50 px-4 py-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-bleuone/10 text-bleuone">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Debut</p>
                                    <p class="text-sm font-bold text-bleuone">{{ $group['start'] }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 rounded-2xl border border-orangeone/15 bg-orange-50/60 px-4 py-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orangeone/10 text-orangeone">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Fin</p>
                                    <p class="text-sm font-bold text-orangeone">{{ $group['end'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-gray-50/80 p-4 space-y-4">
                            <div>
                                <h4 class="mb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500 font-varela">
                                    Modules associes
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($group['modules'] as $module)
                                        <span class="inline-flex items-center rounded-full bg-vertone/10 px-3 py-1 text-xs font-varela text-vertone">
                                            {{ $module }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-4">
                                <h4 class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500 font-varela">
                                    Stagiaires
                                </h4>
                                <span class="shrink-0 text-sm font-semibold text-orangeone font-lisible">
                                    {{ $group['students'] }} stagiaires
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        @if ($group['target'])
                            <a href="{{ $groupEditSimulationUrl }}"
                               class="btn-oneduc w-1/2 text-center">
                                Modifier
                            </a>
                        @else
                            <button type="button"
                                    class="btn-oneduc w-1/2 cursor-not-allowed opacity-45"
                                    disabled>
                                Modifier
                            </button>
                        @endif

                        <button type="button"
                                class="btn-oneduc-blue w-1/2 cursor-not-allowed opacity-45"
                                disabled>
                            Supprimer
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <div x-show="selectedGroup"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mt-6 rounded-[18px] border border-vertone/20 bg-vertone/10 px-5 py-4 text-sm font-semibold text-vertone">
            Groupe selectionne : Hygiene alimentaire 2026. La prochaine etape pourra afficher la fiche du groupe et ses contenus.
        </div>
    </section>
</div>
