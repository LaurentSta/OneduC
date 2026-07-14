@extends('admin.admin_dashboard')

@section('title', 'Tableau de bord')

@section('admin')
    @php
        $tauxComptesActifs = $utilisateurCount > 0
            ? (int) round(($utilisateurActifCount / $utilisateurCount) * 100)
            : 0;
        $indicateursPrincipaux = [
            [
                'label' => 'Comptes gérés',
                'value' => $utilisateurCount,
                'detail' => $utilisateurActifCount.' actifs · '.$utilisateurInactifCount.' inactifs',
                'icon' => 'ti-users',
                'href' => route('admin.utilisateurs.index'),
            ],
            [
                'label' => 'Formateurs',
                'value' => $formateurCount,
                'detail' => $formateursEnAttenteCount.' compte'.($formateursEnAttenteCount > 1 ? 's' : '').' à activer',
                'icon' => 'ti-chalkboard',
                'href' => route('admin.utilisateurs.index', ['role' => 'formateur']),
            ],
            [
                'label' => 'Stagiaires',
                'value' => $stagiaireCount,
                'detail' => $stagiairesSansGroupeCount.' sans groupe',
                'icon' => 'ti-school',
                'href' => route('admin.utilisateurs.index', ['role' => 'stagiaire']),
            ],
            [
                'label' => 'Groupes',
                'value' => $groupCount,
                'detail' => $groupesActifsCount.' actifs',
                'icon' => 'ti-users-group',
                'href' => route('admin.groupes'),
            ],
        ];
        $pointsAttention = [
            [
                'label' => 'Comptes formateurs à activer',
                'value' => $formateursEnAttenteCount,
                'icon' => 'ti-user-pause',
                'tone' => $formateursEnAttenteCount > 0 ? 'text-amber-700 bg-amber-50' : 'text-emerald-700 bg-emerald-50',
                'href' => route('admin.utilisateurs.index', ['role' => 'formateur', 'statut' => 'inactif']),
            ],
            [
                'label' => 'Adhésions formateurs à suivre',
                'value' => $adhesionsARegulariserCount,
                'icon' => 'ti-id-badge-2',
                'tone' => $adhesionsARegulariserCount > 0 ? 'text-orange-700 bg-orange-50' : 'text-emerald-700 bg-emerald-50',
                'href' => route('admin.utilisateurs.index', ['role' => 'formateur']),
            ],
            [
                'label' => 'Stagiaires sans groupe',
                'value' => $stagiairesSansGroupeCount,
                'icon' => 'ti-user-question',
                'tone' => $stagiairesSansGroupeCount > 0 ? 'text-amber-700 bg-amber-50' : 'text-emerald-700 bg-emerald-50',
                'href' => route('admin.utilisateurs.index', ['role' => 'stagiaire', 'rattachement' => 'sans_groupe']),
            ],
            [
                'label' => 'Groupes sans stagiaire',
                'value' => $groupesSansStagiaireCount,
                'icon' => 'ti-users-minus',
                'tone' => $groupesSansStagiaireCount > 0 ? 'text-slate-700 bg-slate-100' : 'text-emerald-700 bg-emerald-50',
                'href' => route('admin.groupes'),
            ],
        ];
    @endphp

    <div class="mx-auto w-full max-w-[1600px] space-y-5">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Vue d’ensemble</p>
                <h1 class="!mb-1 !text-2xl !font-semibold text-slate-950">Bonjour {{ Auth::user()->username ?: Auth::user()->prenom }}</h1>
                <p class="text-sm text-slate-600">Voici les éléments qui demandent votre attention aujourd’hui.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.utilisateurs.create', ['role' => 'formateur']) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 text-sm font-semibold text-slate-700 hover:border-bleuone hover:text-bleuone">
                    <i class="ti ti-user-plus text-lg" aria-hidden="true"></i>
                    Nouveau formateur
                </a>
                <a href="{{ route('admin.utilisateurs.create', ['role' => 'stagiaire']) }}" class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-orangeone px-3.5 text-sm font-semibold text-white hover:bg-orangeone-hover">
                    <i class="ti ti-school text-lg" aria-hidden="true"></i>
                    Nouveau stagiaire
                </a>
            </div>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs principaux">
            @foreach ($indicateursPrincipaux as $indicateur)
                <a href="{{ $indicateur['href'] }}" class="group rounded-xl border border-slate-200 bg-white p-4 transition hover:border-bleuone/40 hover:bg-slate-50/60">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $indicateur['label'] }}</p>
                            <p class="mt-1 text-3xl font-semibold tabular-nums text-slate-950">{{ number_format($indicateur['value'], 0, ',', ' ') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $indicateur['detail'] }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-bleuone/10 text-bleuone transition group-hover:bg-bleuone group-hover:text-white">
                            <i class="ti {{ $indicateur['icon'] }} text-xl" aria-hidden="true"></i>
                        </span>
                    </div>
                </a>
            @endforeach
        </section>

        <div class="grid gap-5 2xl:grid-cols-[minmax(0,1.65fr)_minmax(340px,0.8fr)]">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-labelledby="activite-recente-titre">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="activite-recente-titre" class="text-base font-semibold text-slate-950">Comptes récemment créés</h2>
                        <p class="mt-0.5 text-sm text-slate-500">{{ $comptesCreesCeMoisCount }} création{{ $comptesCreesCeMoisCount > 1 ? 's' : '' }} depuis le début du mois.</p>
                    </div>
                    <a href="{{ route('admin.utilisateurs.index') }}" class="shrink-0 text-sm font-semibold text-bleuone hover:text-orangeone">Voir tous</a>
                </div>

                @if ($utilisateursRecents->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <i class="ti ti-users-minus text-3xl text-slate-300" aria-hidden="true"></i>
                        <p class="mt-2 text-sm text-slate-500">Aucun compte formateur ou stagiaire.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="admin-table-dense min-w-[720px] w-full text-left text-sm">
                            <caption class="sr-only">Derniers comptes formateurs et stagiaires créés</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Rôle</th>
                                    <th scope="col">Rattachement</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col" class="text-right">Création</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($utilisateursRecents as $utilisateur)
                                    @php
                                        $estFormateur = $utilisateur->role === 'formateur';
                                        $nomComplet = trim($utilisateur->prenom.' '.$utilisateur->name);
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.utilisateurs.edit', $utilisateur) }}" class="font-semibold text-slate-900 hover:text-bleuone">{{ $nomComplet ?: $utilisateur->username }}</a>
                                            <p class="mt-0.5 max-w-[230px] truncate text-xs text-slate-500">{{ $utilisateur->email }}</p>
                                        </td>
                                        <td>
                                            <span class="admin-badge {{ $estFormateur ? 'admin-badge--blue' : 'admin-badge--violet' }}">{{ $estFormateur ? 'Formateur' : 'Stagiaire' }}</span>
                                        </td>
                                        <td class="text-slate-600">
                                            @if ($estFormateur)
                                                {{ $utilisateur->groupes_encadres_count }} piloté{{ $utilisateur->groupes_encadres_count > 1 ? 's' : '' }}
                                                · {{ $utilisateur->groupes_formateur_count }} co-animé{{ $utilisateur->groupes_formateur_count > 1 ? 's' : '' }}
                                            @else
                                                {{ $utilisateur->groupes_stagiaire_count }} groupe{{ $utilisateur->groupes_stagiaire_count > 1 ? 's' : '' }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="admin-badge {{ $utilisateur->status ? 'admin-badge--success' : 'admin-badge--neutral' }}">{{ $utilisateur->status ? 'Actif' : 'Inactif' }}</span>
                                        </td>
                                        <td class="text-right text-slate-500">{{ $utilisateur->created_at?->format('d/m/Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="space-y-5">
                <section class="rounded-xl border border-slate-200 bg-white" aria-labelledby="attention-titre">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h2 id="attention-titre" class="text-base font-semibold text-slate-950">Points d’attention</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Accès directs aux situations à traiter.</p>
                    </div>
                    <div class="divide-y divide-slate-100 px-2">
                        @foreach ($pointsAttention as $point)
                            <a href="{{ $point['href'] }}" class="flex items-center gap-3 rounded-lg px-3 py-3 hover:bg-slate-50">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $point['tone'] }}">
                                    <i class="ti {{ $point['icon'] }} text-lg" aria-hidden="true"></i>
                                </span>
                                <span class="min-w-0 flex-1 text-sm font-medium text-slate-700">{{ $point['label'] }}</span>
                                <span class="text-lg font-semibold tabular-nums text-slate-950">{{ $point['value'] }}</span>
                                <i class="ti ti-chevron-right text-slate-400" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-bleuone p-5 text-white" aria-labelledby="sante-plateforme-titre">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/65">Accès plateforme</p>
                            <h2 id="sante-plateforme-titre" class="mt-1 text-lg font-semibold text-white">{{ $tauxComptesActifs }} % des comptes actifs</h2>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                            <i class="ti ti-shield-check text-xl" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/15" role="progressbar" aria-valuenow="{{ $tauxComptesActifs }}" aria-valuemin="0" aria-valuemax="100" aria-label="Part des comptes actifs">
                        <div class="h-full rounded-full bg-vertone" style="width: {{ $tauxComptesActifs }}%"></div>
                    </div>
                    <dl class="mt-4 grid grid-cols-3 gap-3 border-t border-white/15 pt-4 text-center">
                        <div>
                            <dt class="text-xs text-white/65">Modules</dt>
                            <dd class="mt-0.5 text-lg font-semibold">{{ $moduleCount }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-white/65">Sections</dt>
                            <dd class="mt-0.5 text-lg font-semibold">{{ $sectionCount }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-white/65">Leçons</dt>
                            <dd class="mt-0.5 text-lg font-semibold">{{ $lectureCount }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>

        <section class="rounded-xl border border-slate-200 bg-white px-5 py-4" aria-labelledby="catalogue-titre">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 id="catalogue-titre" class="text-base font-semibold text-slate-950">Catalogue pédagogique</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $categoryCount }} catégories · {{ $subCategoryCount }} sous-catégories · {{ $moduleCount }} modules.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.categories.all') }}" class="inline-flex min-h-9 items-center rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Catégories</a>
                    <a href="{{ route('admin.modules') }}" class="inline-flex min-h-9 items-center rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Modules</a>
                    <a href="{{ route('admin.groupes.add') }}" class="inline-flex min-h-9 items-center gap-1.5 rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        <i class="ti ti-plus" aria-hidden="true"></i>
                        Groupe
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
