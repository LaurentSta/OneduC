@extends('stagiaire.master')

@section('content')
<div class="mx-auto max-w-[1285px] px-8 py-8 space-y-8">
    <header class="rounded-[24px] bg-white px-8 py-6 shadow-md">
        <div class="grid grid-cols-12 items-center gap-6">
            <div class="col-span-12 md:col-span-8">
                <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Tableaux blancs du groupe</p>
                <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
                    Un espace collaboratif pour partager idees, consignes et croquis avec votre promotion.
                </p>
                <p class="font-lisible text-lg text-gray-800 leading-loose">
                    Chaque groupe dispose de son propre tableau blanc. Entrez dans celui de votre groupe pour contribuer en direct.
                </p>
            </div>
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <img src="{{ asset('images/svg/TableauDeBordStagiaire.svg') }}"
                     alt="Illustration tableau blanc"
                     class="max-w-[320px] h-auto">
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
