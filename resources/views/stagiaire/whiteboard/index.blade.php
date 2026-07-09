@extends('stagiaire.master')

@section('content')
<div class="mx-auto max-w-[1285px] px-8 py-8 space-y-8">
    <header class="rounded-[20px] border border-gray-100 bg-white shadow-md">
        <div class="grid gap-6 px-6 py-6 md:px-8 md:py-7 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-8">
                <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Tableaux blancs']]" />

                <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
                    Tableaux blancs du groupe
                </h1>
                <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
                    Un espace collaboratif pour partager idees, consignes et croquis avec votre promotion.
                </p>
                <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
                    Chaque groupe dispose de son propre tableau blanc. Entrez dans celui de votre groupe pour contribuer en direct.
                </p>
            </div>
            <div class="lg:col-span-4 flex justify-center lg:justify-end">
                <img src="{{ asset('images/svg/TableauDeBordStagiaire.svg') }}"
                     alt="Illustration tableau blanc"
                     class="max-w-[220px] h-auto">
            </div>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($groups as $group)
            <article class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-bleuone">{{ $group->name }}</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ $group->description ?: 'Tableau partage du groupe pour annotations, notes et croquis.' }}
                        </p>
                    </div>
                    <span class="rounded-full bg-bleuone/10 px-3 py-1 text-xs font-semibold text-bleuone">
                        {{ $group->whiteboard ? 'Actif' : 'Pret' }}
                    </span>
                </div>

                <div class="mt-5 flex items-center gap-3 text-xs text-slate-500">
                    <span>Formateur : {{ $group->instructor?->name ?? 'Non renseigne' }}</span>
                    @if($group->whiteboard?->updated_at)
                        <span>Mis a jour {{ $group->whiteboard->updated_at->diffForHumans() }}</span>
                    @endif
                </div>

                <a href="{{ route('stagiaire.whiteboard.show', ['group' => $group->id]) }}"
                   class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-bleuone px-4 py-3 text-sm font-semibold text-white transition hover:bg-orangeone">
                    Ouvrir le tableau blanc
                </a>
            </article>
        @empty
            <div class="col-span-full rounded-[24px] border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-slate-500">
                Aucun groupe n'est disponible pour le moment.
            </div>
        @endforelse
    </section>
</div>
@endsection
