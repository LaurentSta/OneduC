@extends('admin.admin_dashboard')

@section('title', 'Modèles de parcours')

@section('admin')
    <div class="mx-auto w-full max-w-[1600px] space-y-5">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Catalogue Oneduc</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-950">Modèles de parcours</h1>
                <p class="mt-1 text-sm text-slate-600">Composez des parcours officiels que les formateurs pourront copier sans données de session.</p>
            </div>
            <a href="{{ route('admin.modeles-parcours.create') }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-orangeone px-4 text-sm font-semibold text-white hover:bg-orangeone-hover">
                <i class="ti ti-plus" aria-hidden="true"></i>
                Nouveau modèle
            </a>
        </header>

        @foreach (['success' => 'emerald', 'error' => 'red'] as $cle => $couleur)
            @if (session($cle))
                <div class="rounded-lg border border-{{ $couleur }}-200 bg-{{ $couleur }}-50 px-4 py-3 text-sm text-{{ $couleur }}-800">{{ session($cle) }}</div>
            @endif
        @endforeach

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">L’action demandée n’a pas pu être effectuée :</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-labelledby="liste-modeles">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 id="liste-modeles" class="text-base font-semibold text-slate-950">{{ $modeles->total() }} modèle{{ $modeles->total() > 1 ? 's' : '' }}</h2>
            </div>

            @if ($modeles->isEmpty())
                <div class="px-6 py-14 text-center">
                    <i class="ti ti-route-off text-4xl text-slate-300" aria-hidden="true"></i>
                    <p class="mt-3 text-sm text-slate-500">Aucun modèle global n’a encore été créé.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table-dense min-w-[980px] w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th scope="col">Modèle</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Étapes</th>
                                <th scope="col">Copies formateur</th>
                                <th scope="col">Auteur</th>
                                <th scope="col" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modeles as $modele)
                                @php
                                    $badge = match ($modele->statut) {
                                        'publie' => 'admin-badge admin-badge--success',
                                        'archive' => 'admin-badge admin-badge--neutral',
                                        default => 'inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700',
                                    };
                                    $libelleStatut = match ($modele->statut) {
                                        'publie' => 'Publié',
                                        'archive' => 'Archivé',
                                        default => 'Brouillon',
                                    };
                                    $nomAuteur = trim(($modele->auteur?->prenom ?? '').' '.($modele->auteur?->name ?? ''));
                                @endphp
                                <tr>
                                    <td>
                                        <p class="font-semibold text-slate-900">{{ $modele->titre }}</p>
                                        @if ($modele->description)
                                            <p class="mt-0.5 max-w-[360px] truncate text-xs text-slate-500">{{ $modele->description }}</p>
                                        @endif
                                    </td>
                                    <td><span class="{{ $badge }}">{{ $libelleStatut }}</span></td>
                                    <td class="tabular-nums text-slate-600">{{ $modele->items_count }}</td>
                                    <td class="tabular-nums text-slate-600">{{ $modele->copies_formateurs_count }}</td>
                                    <td class="text-slate-600">{{ $nomAuteur ?: ($modele->auteur?->username ?? 'Administrateur supprimé') }}</td>
                                    <td>
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @if ($modele->estBrouillon())
                                                <a href="{{ route('admin.modeles-parcours.edit', $modele) }}" class="text-sm font-semibold text-bleuone hover:text-orangeone">Modifier</a>
                                                <form method="POST" action="{{ route('admin.modeles-parcours.publier', $modele) }}">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Publier</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.modeles-parcours.destroy', $modele) }}" onsubmit="return confirm('Supprimer définitivement ce brouillon ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Supprimer</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.modeles-parcours.dupliquer', $modele) }}">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-semibold text-bleuone hover:text-orangeone">Nouvelle version</button>
                                                </form>
                                                @if ($modele->estPublie())
                                                    <form method="POST" action="{{ route('admin.modeles-parcours.archiver', $modele) }}">
                                                        @csrf
                                                        <button type="submit" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Archiver</button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4">{{ $modeles->links() }}</div>
            @endif
        </section>
    </div>
@endsection
