@if (in_array(($activeLessonPart ?? null), ['ajustement-groupe-suite', 'ajustement-groupe-finalisation'], true))
@php
    $isFinalisation = ($activeLessonPart ?? null) === 'ajustement-groupe-finalisation';
    $isSuiteSimulation = ($activeLessonPart ?? null) === 'ajustement-groupe-suite';
    $formateurId = (int) auth()->id();
    $allowedPerPage = [10, 25, 50, 100];
    $perPage = (int) request('per_page', 10);

    if (! in_array($perPage, $allowedPerPage, true)) {
        $perPage = 10;
    }

    if ($isSuiteSimulation) {
        $groupes = collect([
            (object) ['id' => 1, 'name' => 'Hygiene alimentaire 2026'],
        ]);

        $simulatedStudents = collect([
            (object) [
                'prenom' => 'Marie',
                'name' => 'Dupont',
                'email' => 'marie.dupont@email.fr',
                'code_acces' => 'MARIE1',
                'groupesStagiaire' => collect([(object) ['name' => 'Hygiene alimentaire 2026']]),
            ],
            (object) [
                'prenom' => 'Jean',
                'name' => 'Martin',
                'email' => 'jean.martin@email.fr',
                'code_acces' => 'JEANM1',
                'groupesStagiaire' => collect([(object) ['name' => 'Hygiene alimentaire 2026']]),
            ],
        ]);

        if ($search = request('search')) {
            $normalizedSearch = mb_strtolower($search);
            $simulatedStudents = $simulatedStudents->filter(function ($student) use ($normalizedSearch) {
                return str_contains(mb_strtolower($student->prenom), $normalizedSearch)
                    || str_contains(mb_strtolower($student->name), $normalizedSearch)
                    || str_contains(mb_strtolower($student->email), $normalizedSearch);
            })->values();
        }

        if (request('group_id') && (string) request('group_id') !== '1') {
            $simulatedStudents = collect();
        }

        $currentPage = (int) request('stagiaires_page', 1);
        $stagiaires = new \Illuminate\Pagination\LengthAwarePaginator(
            $simulatedStudents->forPage($currentPage, $perPage)->values(),
            $simulatedStudents->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'pageName' => 'stagiaires_page']
        );
        $stagiaires->appends(request()->query());
    } else {
        $accessibleGroupIds = \App\Models\Group::query()
            ->accessibleByTrainer($formateurId)
            ->pluck('groups.id')
            ->map(fn ($groupId) => (int) $groupId)
            ->values();

        $groupes = \App\Models\Group::query()
            ->whereIn('id', $accessibleGroupIds->all())
            ->orderBy('name')
            ->get(['id', 'name']);

        $stagiairesQuery = \App\Models\User::query()
            ->where('role', 'stagiaire')
            ->where(function ($query) use ($accessibleGroupIds, $formateurId) {
                $query->where('formateur_id', $formateurId)
                    ->orWhereHas('groupesStagiaire', function ($groupQuery) use ($accessibleGroupIds) {
                        $groupQuery->whereIn('groups.id', $accessibleGroupIds->all());
                    });
            });

        if ($groupId = request('group_id')) {
            $stagiairesQuery->whereHas('groupesStagiaire', function ($groupQuery) use ($groupId, $accessibleGroupIds) {
                $groupQuery->where('groups.id', $groupId)
                    ->whereIn('groups.id', $accessibleGroupIds->all());
            });
        }

        if ($search = request('search')) {
            $stagiairesQuery->where(function ($query) use ($search) {
                $query->where('prenom', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $stagiaires = $stagiairesQuery
            ->with(['groupesStagiaire' => function ($query) use ($accessibleGroupIds) {
                $query->whereIn('groups.id', $accessibleGroupIds->all())->orderBy('name');
            }])
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'stagiaires_page')
            ->withQueryString();
    }
@endphp

<div class="mx-auto w-full max-w-[1285px] space-y-6">
    <header class="rounded-[20px] bg-white px-8 py-6 shadow-md">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-orangeone">Ecran inferieur du simulateur</p>
                <h2 class="mt-2 font-raleway text-2xl font-semibold text-bleuone">Mes stagiaires</h2>
                <p class="mt-1 text-sm leading-6 text-gray-500">
                    Retrouvez la liste des stagiaires et les actions principales comme sur la page formateur.
                </p>
            </div>
            @if($isFinalisation)
                @if (!empty($nextLesson['url'] ?? null))
                    <a href="{{ $nextLesson['url'] }}"
                       class="btn-oneduc h-10 w-full sm:w-auto !px-5 !text-sm">
                        Leçon suivante
                    </a>
                @endif
            @else
                <a href="{{ $mixedPartUrls['ajouter-stagiaire'] ?? '#' }}"
                   class="btn-oneduc h-10 w-full sm:w-auto !px-5 !text-sm">
                    <x-icons.add-stagiaire-button-iconify class="h-4 w-4 shrink-0" />
                    Ajouter un stagiaire
                </a>
            @endif
        </div>
    </header>

    <main class="space-y-6 rounded-[20px] bg-white px-6 py-6 shadow-md">
        @if (session('success'))
            <div class="rounded-[16px] border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ url()->current() }}" class="space-y-3" onsubmit="return false;">
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-full md:w-[180px]">
                    <label for="lesson_stagiaires_per_page" class="sr-only">Nombre de stagiaires a afficher</label>
                    <select id="lesson_stagiaires_per_page"
                            name="per_page"
                            class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
                        @foreach ($allowedPerPage as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>
                                {{ $option }} par page
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:flex-1 md:min-w-[260px]">
                    <label for="lesson_stagiaires_search" class="sr-only">Recherche prenom</label>
                    <input type="text"
                           id="lesson_stagiaires_search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Recherche prenom"
                           class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
                </div>

                <div class="w-full md:w-[280px]">
                    <label for="lesson_stagiaires_group_id" class="sr-only">Recherche de groupe</label>
                    <select id="lesson_stagiaires_group_id"
                            name="group_id"
                            class="h-10 w-full rounded-md border border-gray-300 px-4 text-sm font-lisible shadow-sm focus:border-orangeone focus:ring-orangeone">
                        <option value="">Recherche de groupe</option>
                        @foreach ($groupes as $groupe)
                            <option value="{{ $groupe->id }}" @selected((string) request('group_id') === (string) $groupe->id)>
                                {{ $groupe->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="button" disabled class="btn-oneduc inline-flex h-10 w-full cursor-not-allowed items-center justify-center gap-2 opacity-45 sm:w-[160px] !text-sm">
                    <x-icons.filter-iconify class="h-4 w-4 shrink-0" />
                    <span>Filtrer</span>
                </button>

                @if(request()->filled('search') || request()->filled('group_id') || request('per_page', 10) != 10)
                    <span aria-disabled="true" class="btn-oneduc-outline h-10 cursor-not-allowed opacity-45 !text-sm">
                        Reinitialiser
                    </span>
                @endif
            </div>

            @if(request('group_id'))
                <p class="pt-1 text-sm text-gray-600 font-varela">
                    Groupe selectionne :
                    <span class="font-semibold text-orangeone">
                        {{ optional($groupes->firstWhere('id', (int) request('group_id')))->name }}
                    </span>
                </p>
            @endif
        </form>

        <div class="overflow-x-auto rounded-[20px] border-2 border-bleuone/20 bg-white shadow-md">
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
                    @forelse ($stagiaires as $index => $stagiaire)
                        <tr class="border-t {{ $index % 2 === 0 ? 'bg-white' : 'bg-orangeone/8' }} hover:bg-orangeone/15 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ $stagiaires->firstItem() + $index }}</td>
                            <td class="px-6 py-4">{{ $stagiaire->prenom }}</td>
                            <td class="px-6 py-4">{{ $stagiaire->name }}</td>
                            <td class="px-6 py-4">{{ $stagiaire->email }}</td>
                            <td class="px-6 py-4 font-mono text-sm text-orangeone">{{ $stagiaire->code_acces ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @forelse ($stagiaire->groupesStagiaire as $groupe)
                                    <span class="mb-1 mr-1 inline-block rounded-full bg-vertone/10 px-2 py-1 text-xs font-varela text-vertone">
                                        {{ $groupe->name }}
                                    </span>
                                @empty
                                    <span class="text-xs italic text-gray-400">Aucun</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4">
                                <span aria-disabled="true"
                                   class="btn-oneduc cursor-not-allowed opacity-45 !px-3 !py-1 !text-sm">
                                    <x-icons.edit-iconify class="h-4 w-4" />
                                    Modifier
                                </span>
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

        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex items-center gap-3 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                    <x-icons.stagiaire-iconify class="h-4 w-4" />
                </span>
                <span>Nombre total de stagiaires :</span>
                <span class="font-bold text-bleuone">{{ $stagiaires->total() }}</span>
            </div>
            <div class="inline-flex items-center gap-3 rounded-full border border-orangeone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orangeone/10 text-orangeone">
                    <x-icons.add-stagiaire-iconify class="h-4 w-4" />
                </span>
                <span>Nombre total de groupes :</span>
                <span class="font-bold text-orangeone">{{ $groupes->count() }}</span>
            </div>
        </div>

        <div>
            <div class="pointer-events-none opacity-45">
                {{ $stagiaires->links('pagination::tailwind') }}
            </div>
        </div>
    </main>
</div>
@endif
