@extends('admin.admin_dashboard')

@section('title', 'Groupes')

@section('admin')
    @php
        $nombreGroupesActifs = $groupes->where('is_active', true)->count();
        $nombreAffectations = $groupes->sum('students_count');
    @endphp

    <div class="space-y-4">
        <header class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Organisation pédagogique</p>
                <h1 id="titre-page-groupes" class="mt-1 text-2xl font-semibold tracking-tight text-bleuone">Groupes</h1>
                <p class="mt-1 max-w-3xl text-sm text-slate-600">
                    Pilotez les responsables de groupe et les affectations des stagiaires depuis une vue consolidée.
                </p>
            </div>

            <a
                href="{{ route('admin.groupes.add') }}"
                class="btn-oneduc inline-flex shrink-0 items-center justify-center gap-2 !px-4 !py-2.5 !text-sm"
            >
                <i class="ti ti-plus" aria-hidden="true"></i>
                Créer un groupe
            </a>
        </header>

        <section aria-labelledby="titre-page-groupes" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Groupes enregistrés</p>
                <p class="mt-1 text-2xl font-semibold text-slate-950">{{ $groupes->count() }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Groupes actifs</p>
                <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ $nombreGroupesActifs }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Affectations stagiaires</p>
                <p class="mt-1 text-2xl font-semibold text-bleuone">{{ $nombreAffectations }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Liste des groupes">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-900">Répertoire des groupes</h2>
                <p class="mt-0.5 text-xs text-slate-500">Les recherches et le tri s’appliquent à la liste ci-dessous.</p>
            </div>

            <div class="overflow-x-auto">
                <table id="tableGroupes" class="w-full min-w-[880px] text-left text-sm text-slate-700">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3">Groupe</th>
                            <th scope="col" class="px-4 py-3">État</th>
                            <th scope="col" class="px-4 py-3">Formateur responsable</th>
                            <th scope="col" class="px-4 py-3 text-center">Stagiaires</th>
                            <th scope="col" class="px-4 py-3">Dernière mise à jour</th>
                            <th scope="col" class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($groupes as $groupe)
                            @php
                                $nomFormateur = trim(($groupe->instructor->prenom ?? '').' '.($groupe->instructor->name ?? ''));
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3 align-top">
                                    <p class="font-semibold text-slate-950">{{ $groupe->name }}</p>
                                    <p class="mt-0.5 max-w-md text-xs leading-5 text-slate-500">
                                        {{ $groupe->description ? Str::limit($groupe->description, 105) : 'Aucune description renseignée.' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    @if ($groupe->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                            Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                            Inactif
                                        </span>
                                    @endif

                                    @if ($groupe->start_date || $groupe->end_date)
                                        <p class="mt-2 whitespace-nowrap text-xs text-slate-500">
                                            {{ $groupe->start_date?->format('d/m/Y') ?? '—' }} → {{ $groupe->end_date?->format('d/m/Y') ?? '—' }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    @if ($groupe->instructor)
                                        <p class="font-medium text-slate-900">{{ $nomFormateur !== '' ? $nomFormateur : $groupe->instructor->email }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $groupe->instructor->email }}</p>
                                        @unless ($groupe->instructor->status)
                                            <span class="mt-1 inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Compte inactif</span>
                                        @endunless
                                    @else
                                        <span class="text-sm font-medium text-red-700">Formateur indisponible</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center align-top">
                                    <span class="inline-flex min-w-9 items-center justify-center rounded-md bg-sky-50 px-2.5 py-1 text-sm font-semibold text-sky-800 ring-1 ring-inset ring-sky-200">
                                        {{ $groupe->students_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 align-top text-xs text-slate-500" data-order="{{ $groupe->updated_at?->timestamp ?? 0 }}">
                                    {{ $groupe->updated_at?->format('d/m/Y à H:i') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right align-top">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a
                                            href="{{ route('admin.groupes.edit', $groupe->id) }}"
                                            class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white p-2 text-slate-700 transition hover:border-bleuone hover:text-bleuone focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-bleuone"
                                            aria-label="Modifier le groupe {{ $groupe->name }}"
                                            title="Modifier"
                                        >
                                            <i class="ti ti-pencil text-base" aria-hidden="true"></i>
                                        </a>
                                        <button
                                            type="button"
                                            x-on:click="$dispatch('open-modal', 'supprimer-groupe-{{ $groupe->id }}')"
                                            class="inline-flex items-center justify-center rounded-md border border-red-200 bg-white p-2 text-red-700 transition hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                                            aria-label="Supprimer le groupe {{ $groupe->name }}"
                                            title="Supprimer"
                                        >
                                            <i class="ti ti-trash text-base" aria-hidden="true"></i>
                                        </button>
                                    </div>

                                    <x-confirm-modal
                                        name="supprimer-groupe-{{ $groupe->id }}"
                                        title="Supprimer ce groupe ?"
                                        :message="'Le groupe « '.$groupe->name.' » et ses rattachements seront supprimés. Cette action est irréversible.'"
                                        :action="route('admin.groupes.delete', $groupe->id)"
                                        method="DELETE"
                                        confirm-label="Supprimer le groupe"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <i class="ti ti-users-group text-3xl text-slate-300" aria-hidden="true"></i>
                                    <p class="mt-2 font-medium text-slate-700">Aucun groupe enregistré</p>
                                    <p class="mt-1 text-sm text-slate-500">Créez un premier groupe pour organiser les stagiaires.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.jQuery || !jQuery.fn.DataTable || !document.getElementById('tableGroupes')) {
                return;
            }

            jQuery('#tableGroupes').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
                pageLength: 25,
                order: [[0, 'asc']],
                columnDefs: [
                    { targets: [3, 5], orderable: false },
                ],
            });
        });
    </script>
@endpush
