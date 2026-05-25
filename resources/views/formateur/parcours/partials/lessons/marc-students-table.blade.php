@php
    $marcProfileUrl = $mixedPartUrls['modifier-profil-marc'] ?? '#';
    $firstNames = [
        'Amina', 'Lucas', 'Sofia', 'Youssef', 'Camille', 'Nora', 'Hugo', 'Ines', 'Thomas', 'Lea',
        'Mehdi', 'Chloe', 'Adam', 'Sarah', 'Nicolas', 'Eva', 'Karim', 'Julie', 'Marc', 'Lina',
        'Antoine', 'Manon', 'Ibrahim', 'Clara', 'Romain', 'Nadia', 'Theo', 'Fatou', 'Paul', 'Emma',
        'Samir', 'Alice', 'Bastien', 'Maya', 'Quentin', 'Elsa', 'Omar', 'Lucie', 'Maxime', 'Noemie',
    ];
    $lastNames = [
        'Diallo', 'Moreau', 'Martin', 'Benali', 'Petit', 'Roux', 'Bernard', 'Durand', 'Leroy', 'Garcia',
        'Khan', 'Robert', 'Nguyen', 'Fischer', 'Dubois', 'Lopez', 'Haddad', 'Mercier', 'Lefebvre', 'Simon',
        'Laurent', 'Girard', 'Mansouri', 'Andre', 'Lambert', 'Da Silva', 'Faure', 'Camara', 'Renaud', 'Fontaine',
        'Bensaid', 'Garnier', 'Chevalier', 'Perrin', 'Barbier', 'Marchand', 'Ait Ali', 'Colin', 'Gauthier', 'Aubert',
    ];
    $groups = ['Hygiene alimentaire 2026', 'Excel avance', 'Accueil securite', 'Parcours integration', 'Bureautique niveau 1'];
    $allowedPerPage = [10, 25, 50, 100];
    $perPage = (int) request('per_page', 10);

    if (! in_array($perPage, $allowedPerPage, true)) {
        $perPage = 10;
    }

    $students = [];

    foreach ($firstNames as $index => $firstName) {
        $lastName = $lastNames[$index];
        $isMarc = $firstName === 'Marc' && $lastName === 'Lefebvre';
        $students[] = [
            'prenom' => $firstName,
            'nom' => $lastName,
            'email' => strtolower($firstName . '.' . str_replace(' ', '', $lastName)) . '@example.fr',
            'code' => $isMarc ? 'MARC01' : strtoupper(substr($firstName, 0, 3) . substr($lastName, 0, 2) . (($index % 9) + 1)),
            'groupe' => $isMarc ? 'Hygiene alimentaire 2026' : $groups[$index % count($groups)],
            'active' => $isMarc,
        ];
    }

    $search = trim((string) request('search', ''));
    $selectedGroupIndex = request('group_id');
    $hasSearchedMarc = str_contains(mb_strtolower($search), 'marc');
    $selectedGroup = is_numeric($selectedGroupIndex)
        ? ($groups[((int) $selectedGroupIndex) - 1] ?? null)
        : null;
    $hasSelectedMarcGroup = $selectedGroup === 'Hygiene alimentaire 2026';

    $visibleStudents = collect($students)
        ->filter(function (array $student) use ($search, $selectedGroup) {
            $matchesSearch = $search === ''
                || str_contains(mb_strtolower($student['prenom']), mb_strtolower($search))
                || str_contains(mb_strtolower($student['nom']), mb_strtolower($search))
                || str_contains(mb_strtolower($student['email']), mb_strtolower($search));

            $matchesGroup = $selectedGroup === null || $student['groupe'] === $selectedGroup;

            return $matchesSearch && $matchesGroup;
        })
        ->values();
    $filteredStudentsCount = $visibleStudents->count();
    $visibleStudents = $visibleStudents->take($perPage)->values();
@endphp

<div
    x-data="{ showInstructions: false }"
    class="mx-auto w-full max-w-[1285px] space-y-5"
>
    <header class="rounded-[20px] border border-bleuone/10 bg-white px-6 py-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Etape 1</p>
                <h1 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Retrouvez Marc dans le tableau des stagiaires</h1>
            </div>

            <button
                type="button"
                @click="showInstructions = true"
                class="inline-flex h-12 items-center justify-center gap-3 rounded-full border-2 border-orangeone/30 bg-orangeone/10 px-6 text-base font-bold text-orangeone shadow-sm transition hover:border-orangeone hover:bg-orangeone hover:text-white"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Consigne
            </button>
        </div>
    </header>

    <div>
        <main class="rounded-[20px] bg-white p-5 shadow-md">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="font-raleway text-2xl font-semibold text-bleuone">Mes stagiaires</h2>
                </div>
            </div>

            <form method="GET" action="{{ url()->current() }}" class="mt-5 space-y-3">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="w-full md:w-[180px]">
                        <label for="sim_stagiaires_per_page" class="sr-only">Nombre de stagiaires a afficher</label>
                        <select
                            id="sim_stagiaires_per_page"
                            name="per_page"
                            class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone"
                        >
                            @foreach ($allowedPerPage as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>
                                    {{ $option }} par page
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full md:min-w-[260px] md:flex-1">
                        <label for="sim_stagiaires_search" class="sr-only">Recherche prenom</label>
                        <input
                            id="sim_stagiaires_search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Recherche prenom"
                            class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone"
                        >
                    </div>

                    <div class="w-full md:w-[280px]">
                        <label for="sim_stagiaires_group_id" class="sr-only">Recherche de groupe</label>
                        <select
                            id="sim_stagiaires_group_id"
                            name="group_id"
                            class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone"
                        >
                            <option value="">Recherche de groupe</option>
                            @foreach ($groups as $index => $group)
                                <option value="{{ $index + 1 }}" @selected((string) request('group_id') === (string) ($index + 1))>
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-oneduc inline-flex h-10 w-full items-center justify-center gap-2 sm:w-[200px] !text-sm">
                        <x-icons.filter-iconify class="h-4 w-4 shrink-0" />
                        <span>Filtrer</span>
                    </button>

                    <span class="btn-oneduc h-10 w-full cursor-not-allowed opacity-45 sm:w-[200px] !text-sm">
                        <x-icons.add-stagiaire-button-iconify class="h-4 w-4 shrink-0" />
                        Ajouter un stagiaire
                    </span>

                </div>

                @if(request('group_id'))
                    <p class="pt-1 text-sm text-gray-600 font-varela">
                        Groupe selectionne :
                        <span class="text-orangeone font-semibold">
                            {{ $groups[((int) request('group_id')) - 1] ?? 'Hygiene alimentaire 2026' }}
                        </span>
                    </p>
                @endif
            </form>

            <div class="mt-5 overflow-x-auto rounded-[20px] border-2 border-bleuone/20 bg-white shadow-md">
            <table class="min-w-full bg-white text-left text-sm text-gray-800 font-lisible">
                <thead class="sticky top-0 z-10 bg-bleuone text-xs uppercase text-white font-varela">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Prenom</th>
                        <th class="px-6 py-3">Nom</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Code d'acces</th>
                        <th class="px-6 py-3">Groupe(s)</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visibleStudents as $index => $student)
                        @php
                            $canOpenMarc = $student['active'] && ($hasSearchedMarc || $hasSelectedMarcGroup);
                        @endphp

                        <tr class="border-t {{ $canOpenMarc ? 'bg-orangeone/10' : ($index % 2 === 0 ? 'bg-white' : 'bg-orangeone/5') }}">
                            <td class="px-6 py-4 font-medium">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-semibold {{ $canOpenMarc ? 'text-orangeone' : '' }}">{{ $student['prenom'] }}</td>
                            <td class="px-6 py-4">{{ $student['nom'] }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $student['email'] }}</td>
                            <td class="px-6 py-4 font-mono text-sm font-bold {{ $canOpenMarc ? 'text-orangeone' : 'text-slate-500' }}">{{ $student['code'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block rounded-full bg-vertone/10 px-2 py-1 text-xs font-varela text-vertone">
                                    {{ $student['groupe'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    @if ($canOpenMarc)
                                        <a href="{{ $marcProfileUrl }}" class="btn-oneduc !px-3 !py-1.5 !text-sm">
                                            Modifier
                                        </a>
                                    @else
                                        <span class="btn-oneduc cursor-not-allowed opacity-35 !px-3 !py-1.5 !text-sm">
                                            Modifier
                                        </span>
                                    @endif

                                    <span
                                        aria-disabled="true"
                                        title="Supprimer ce stagiaire"
                                        class="inline-flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-full border border-bleuone/20 bg-bleuone/10 text-bleuone opacity-35"
                                    >
                                        <x-icons.trash-iconify class="h-5 w-5" />
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun stagiaire trouve.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <a href="{{ url()->current() }}" class="btn-oneduc-outline h-10 !text-sm">
                    Reinitialiser
                </a>

                <div class="inline-flex items-center gap-3 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                        <x-icons.stagiaire-iconify class="h-4 w-4" />
                    </span>
                    <span>Stagiaires affiches :</span>
                    <span class="font-bold text-bleuone">{{ $visibleStudents->count() }}</span>
                </div>

                <div class="inline-flex items-center gap-3 rounded-full border border-orangeone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
                    <span>Resultats filtres :</span>
                    <span class="font-bold text-orangeone">{{ $filteredStudentsCount }}</span>
                </div>
            </div>
        </main>
    </div>

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
                    <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Debloquer Marc</h2>
                </div>
                <button type="button" @click="showInstructions = false" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            <ol class="mt-5 list-decimal space-y-3 pl-5 text-base leading-7 text-slate-700">
                <li>Utilisez la recherche ou le filtre de groupe pour retrouver Marc.</li>
                <li>Cliquez sur Modifier dans la ligne de Marc.</li>
            </ol>

            <div class="mt-5 rounded-[16px] border border-orangeone/20 bg-orangeone/5 px-4 py-3 text-base leading-7 text-slate-700">
                <span class="font-bold text-orangeone">Objectif :</span>
                acceder a la fiche de Marc pour lui envoyer son lien et son code d'acces.
            </div>
        </section>
    </div>
</div>
