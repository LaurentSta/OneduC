@extends('admin.admin_dashboard')

@section('title', 'Utilisateurs')

@section('admin')
    <div class="mx-auto w-full max-w-[1600px] space-y-5">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <span>Administration</span>
                    <i class="ti ti-chevron-right text-sm" aria-hidden="true"></i>
                    <span class="text-bleuone">Utilisateurs</span>
                </div>
                <h1 class="!mb-1 !text-2xl !font-semibold text-slate-950">Gestion des utilisateurs</h1>
                <p class="text-sm text-slate-600">Pilotez les comptes formateurs et stagiaires depuis une vue unique.</p>
            </div>

            <a href="{{ route('admin.utilisateurs.create', ['role' => $filtres['role'] ?: 'formateur']) }}"
               class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-orangeone px-4 py-2 text-sm font-semibold text-white transition hover:bg-orangeone-hover focus:outline-none focus:ring-2 focus:ring-orangeone focus:ring-offset-2">
                <i class="ti ti-user-plus text-lg" aria-hidden="true"></i>
                Créer un compte
            </a>
        </header>

        <section aria-label="Indicateurs utilisateurs" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @php
                $indicateurs = [
                    ['label' => 'Comptes', 'value' => $statistiques['total'], 'icon' => 'ti-users', 'tone' => 'text-bleuone bg-bleuone/10'],
                    ['label' => 'Actifs', 'value' => $statistiques['actifs'], 'icon' => 'ti-circle-check', 'tone' => 'text-emerald-700 bg-emerald-50'],
                    ['label' => 'Inactifs', 'value' => $statistiques['inactifs'], 'icon' => 'ti-circle-pause', 'tone' => 'text-slate-600 bg-slate-100'],
                    ['label' => 'Formateurs', 'value' => $statistiques['formateurs'], 'icon' => 'ti-chalkboard', 'tone' => 'text-blue-700 bg-blue-50'],
                    ['label' => 'Stagiaires', 'value' => $statistiques['stagiaires'], 'icon' => 'ti-school', 'tone' => 'text-violet-700 bg-violet-50'],
                    ['label' => 'Sans groupe', 'value' => $statistiques['sans_groupe'], 'icon' => 'ti-user-question', 'tone' => 'text-amber-700 bg-amber-50'],
                ];
            @endphp

            @foreach ($indicateurs as $indicateur)
                <article class="rounded-xl border border-slate-200 bg-white p-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $indicateur['label'] }}</p>
                            <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-950">{{ number_format($indicateur['value'], 0, ',', ' ') }}</p>
                        </div>
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $indicateur['tone'] }}">
                            <i class="ti {{ $indicateur['icon'] }} text-lg" aria-hidden="true"></i>
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-labelledby="liste-utilisateurs-titre">
            <div class="border-b border-slate-200 px-4 pt-4 sm:px-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h2 id="liste-utilisateurs-titre" class="text-base font-semibold text-slate-950">Répertoire des comptes</h2>
                        <p class="mt-0.5 text-sm text-slate-500">{{ number_format($utilisateurs->total(), 0, ',', ' ') }} résultat{{ $utilisateurs->total() > 1 ? 's' : '' }}</p>
                    </div>

                    <nav class="flex w-full overflow-x-auto xl:w-auto" aria-label="Filtrer par rôle">
                        @php
                            $baseFiltresRole = request()->except(['page', 'role']);
                            $ongletsRole = [
                                null => ['label' => 'Tous', 'count' => $statistiques['total']],
                                'formateur' => ['label' => 'Formateurs', 'count' => $statistiques['formateurs']],
                                'stagiaire' => ['label' => 'Stagiaires', 'count' => $statistiques['stagiaires']],
                            ];
                        @endphp
                        @foreach ($ongletsRole as $valeurRole => $onglet)
                            @php
                                $parametresRole = $valeurRole
                                    ? array_merge($baseFiltresRole, ['role' => $valeurRole])
                                    : $baseFiltresRole;
                                $estActif = $filtres['role'] === $valeurRole;
                            @endphp
                            <a href="{{ route('admin.utilisateurs.index', $parametresRole) }}"
                               class="inline-flex min-h-10 shrink-0 items-center gap-2 border-b-2 px-3 text-sm font-semibold transition {{ $estActif ? 'border-orangeone text-bleuone' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-900' }}"
                               @if ($estActif) aria-current="page" @endif>
                                {{ $onglet['label'] }}
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs tabular-nums text-slate-600">{{ $onglet['count'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <form method="GET" action="{{ route('admin.utilisateurs.index') }}" class="mt-4 grid gap-3 border-t border-slate-100 py-4 md:grid-cols-2 xl:grid-cols-[minmax(240px,1fr)_160px_180px_160px_110px_auto]">
                    @if ($filtres['role'])
                        <input type="hidden" name="role" value="{{ $filtres['role'] }}">
                    @endif

                    <div>
                        <label for="recherche" class="sr-only">Rechercher un utilisateur</label>
                        <div class="relative">
                            <i class="ti ti-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true"></i>
                            <input id="recherche" name="recherche" type="search" value="{{ $filtres['recherche'] }}"
                                   placeholder="Nom, email, structure…"
                                   class="h-10 w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-bleuone focus:ring-bleuone">
                        </div>
                    </div>

                    <div>
                        <label for="statut" class="sr-only">Statut du compte</label>
                        <select id="statut" name="statut" class="h-10 w-full rounded-lg border-slate-300 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
                            <option value="">Tous les statuts</option>
                            <option value="actif" @selected($filtres['statut'] === 'actif')>Actifs</option>
                            <option value="inactif" @selected($filtres['statut'] === 'inactif')>Inactifs</option>
                        </select>
                    </div>

                    <div>
                        <label for="rattachement" class="sr-only">Rattachement à un groupe</label>
                        <select id="rattachement" name="rattachement" class="h-10 w-full rounded-lg border-slate-300 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
                            <option value="">Tous les rattachements</option>
                            <option value="avec_groupe" @selected($filtres['rattachement'] === 'avec_groupe')>Avec groupe</option>
                            <option value="sans_groupe" @selected($filtres['rattachement'] === 'sans_groupe')>Sans groupe</option>
                        </select>
                    </div>

                    <div>
                        <label for="tri" class="sr-only">Trier les comptes</label>
                        <select id="tri" name="tri" class="h-10 w-full rounded-lg border-slate-300 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
                            <option value="recent" @selected($filtres['tri'] === 'recent')>Plus récents</option>
                            <option value="nom" @selected($filtres['tri'] === 'nom')>Nom A–Z</option>
                            <option value="ancien" @selected($filtres['tri'] === 'ancien')>Plus anciens</option>
                        </select>
                    </div>

                    <div>
                        <label for="par_page" class="sr-only">Résultats par page</label>
                        <select id="par_page" name="par_page" class="h-10 w-full rounded-lg border-slate-300 py-2 text-sm focus:border-bleuone focus:ring-bleuone">
                            @foreach ([20, 50, 100] as $taille)
                                <option value="{{ $taille }}" @selected($filtres['parPage'] === $taille)>{{ $taille }} / page</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg bg-bleuone px-4 text-sm font-semibold text-white hover:bg-bleuone-light focus:outline-none focus:ring-2 focus:ring-bleuone focus:ring-offset-2">
                            Filtrer
                        </button>
                        @if (collect($filtres)->except(['tri', 'parPage'])->filter()->isNotEmpty())
                            <a href="{{ route('admin.utilisateurs.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50" title="Effacer les filtres">
                                <i class="ti ti-filter-off text-lg" aria-hidden="true"></i>
                                <span class="sr-only">Effacer les filtres</span>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if ($utilisateurs->isEmpty())
                <div class="px-6 py-16 text-center">
                    <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                        <i class="ti ti-users-minus text-2xl" aria-hidden="true"></i>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">Aucun compte trouvé</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">Modifiez les filtres ou créez un nouveau compte utilisateur.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table-dense min-w-[1120px] w-full text-left text-sm">
                        <caption class="sr-only">Liste des comptes formateurs et stagiaires</caption>
                        <thead>
                            <tr>
                                <th scope="col">Identité</th>
                                <th scope="col">Rôle</th>
                                <th scope="col">Rattachement</th>
                                <th scope="col">Accès</th>
                                <th scope="col">Dernière mise à jour</th>
                                <th scope="col" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($utilisateurs as $utilisateur)
                                @php
                                    $nomComplet = trim($utilisateur->prenom.' '.$utilisateur->name);
                                    $initiales = mb_strtoupper(mb_substr((string) $utilisateur->prenom, 0, 1).mb_substr((string) $utilisateur->name, 0, 1));
                                    $estFormateur = $utilisateur->role === 'formateur';
                                    $routeSuppression = $estFormateur
                                        ? route('admin.formateurs.destroy', $utilisateur)
                                        : route('admin.stagiaires.destroy', $utilisateur);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="flex min-w-[250px] items-center gap-3">
                                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-bleuone/10 text-xs font-bold text-bleuone" aria-hidden="true">{{ $initiales ?: '—' }}</span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-950">{{ $nomComplet ?: $utilisateur->username ?: 'Sans nom' }}</p>
                                                <p class="truncate text-xs text-slate-500">{{ $utilisateur->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-badge {{ $estFormateur ? 'admin-badge--blue' : 'admin-badge--violet' }}">
                                            <i class="ti {{ $estFormateur ? 'ti-chalkboard' : 'ti-school' }}" aria-hidden="true"></i>
                                            {{ $estFormateur ? 'Formateur' : 'Stagiaire' }}
                                        </span>
                                        @if ($estFormateur && $utilisateur->societe)
                                            <p class="mt-1.5 max-w-[180px] truncate text-xs text-slate-500">{{ $utilisateur->societe }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($estFormateur)
                                            <p class="text-sm font-medium text-slate-800">{{ $utilisateur->groupes_encadres_count }} groupe{{ $utilisateur->groupes_encadres_count > 1 ? 's' : '' }} piloté{{ $utilisateur->groupes_encadres_count > 1 ? 's' : '' }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ $utilisateur->groupes_formateur_count }} groupe{{ $utilisateur->groupes_formateur_count > 1 ? 's' : '' }} co-animé{{ $utilisateur->groupes_formateur_count > 1 ? 's' : '' }}
                                                · {{ $utilisateur->stagiaires_count }} stagiaire{{ $utilisateur->stagiaires_count > 1 ? 's' : '' }} direct{{ $utilisateur->stagiaires_count > 1 ? 's' : '' }}
                                            </p>
                                        @else
                                            <p class="text-sm font-medium text-slate-800">
                                                {{ $utilisateur->formateur ? trim($utilisateur->formateur->prenom.' '.$utilisateur->formateur->name) : 'Aucun formateur principal' }}
                                            </p>
                                            <div class="mt-1 flex max-w-[300px] flex-wrap gap-1">
                                                @forelse ($utilisateur->groupesStagiaire->take(2) as $groupe)
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600">{{ $groupe->name }}</span>
                                                @empty
                                                    <span class="text-xs font-medium text-amber-700">Aucun groupe</span>
                                                @endforelse
                                                @if ($utilisateur->groupesStagiaire->count() > 2)
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600">+{{ $utilisateur->groupesStagiaire->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="admin-badge {{ $utilisateur->status ? 'admin-badge--success' : 'admin-badge--neutral' }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                            {{ $utilisateur->status ? 'Actif' : 'Inactif' }}
                                        </span>
                                        @if ($estFormateur)
                                            @php
                                                $adhesionValide = $utilisateur->hasValidAssociationMembership();
                                                $adhesionLibelle = $adhesionValide
                                                    ? 'Adhésion valide'
                                                    : ($utilisateur->adhesion_status === 'pending' ? 'Adhésion en attente' : 'Adhésion expirée');
                                            @endphp
                                            <p class="mt-1.5 text-xs {{ $adhesionValide ? 'text-emerald-700' : 'text-amber-700' }}">{{ $adhesionLibelle }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="text-sm text-slate-700">{{ $utilisateur->updated_at?->format('d/m/Y') ?? '—' }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">Créé le {{ $utilisateur->created_at?->format('d/m/Y') ?? '—' }}</p>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.utilisateurs.edit', $utilisateur) }}" class="admin-icon-button" title="Modifier {{ $nomComplet }}">
                                                <i class="ti ti-pencil" aria-hidden="true"></i>
                                                <span class="sr-only">Modifier {{ $nomComplet }}</span>
                                            </a>

                                            <form method="POST" action="{{ route('admin.utilisateurs.statut.update', $utilisateur) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $utilisateur->status ? 0 : 1 }}">
                                                <button type="submit" class="admin-icon-button" title="{{ $utilisateur->status ? 'Désactiver' : 'Activer' }} {{ $nomComplet }}">
                                                    <i class="ti {{ $utilisateur->status ? 'ti-user-pause' : 'ti-user-check' }}" aria-hidden="true"></i>
                                                    <span class="sr-only">{{ $utilisateur->status ? 'Désactiver' : 'Activer' }} {{ $nomComplet }}</span>
                                                </button>
                                            </form>

                                            @if (! $estFormateur)
                                                <button type="button" class="admin-icon-button admin-icon-button--warning"
                                                        x-on:click="$dispatch('open-modal', 'reset-stagiaire-{{ $utilisateur->id }}')"
                                                        title="Réinitialiser la progression de {{ $nomComplet }}">
                                                    <i class="ti ti-refresh" aria-hidden="true"></i>
                                                    <span class="sr-only">Réinitialiser la progression de {{ $nomComplet }}</span>
                                                </button>
                                            @endif

                                            <button type="button" class="admin-icon-button admin-icon-button--danger"
                                                    x-on:click="$dispatch('open-modal', 'supprimer-utilisateur-{{ $utilisateur->id }}')"
                                                    title="Supprimer {{ $nomComplet }}">
                                                <i class="ti ti-trash" aria-hidden="true"></i>
                                                <span class="sr-only">Supprimer {{ $nomComplet }}</span>
                                            </button>
                                        </div>

                                        @if (! $estFormateur)
                                            <x-confirm-modal
                                                :name="'reset-stagiaire-'.$utilisateur->id"
                                                title="Réinitialiser la progression"
                                                :message="'Toutes les progressions Quiz, SCORM et vidéo de '.$nomComplet.' seront remises à zéro. Cette action est irréversible.'"
                                                :action="route('admin.stagiaires.reset', $utilisateur)"
                                                method="POST"
                                                confirm-label="Réinitialiser"
                                            />
                                        @endif

                                        <x-confirm-modal
                                            :name="'supprimer-utilisateur-'.$utilisateur->id"
                                            :title="$estFormateur ? 'Supprimer le formateur' : 'Supprimer le stagiaire'"
                                            :message="$estFormateur
                                                ? 'Cette suppression archive le formateur, supprime ses groupes et peut aussi supprimer les stagiaires sans autre rattachement ainsi que leurs données pédagogiques.'
                                                : 'Cette suppression archive le compte et efface les progressions, résultats et données pédagogiques liées au stagiaire.'"
                                            :action="$routeSuppression"
                                            method="DELETE"
                                            confirm-label="Supprimer"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($utilisateurs->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3 sm:px-5">
                        {{ $utilisateurs->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
