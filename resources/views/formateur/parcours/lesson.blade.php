@extends('formateur.parcours.layout')

@section('parcours_content')
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm md:p-10">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-orangeone">
                    {{ $currentLesson['code'] }}
                </span>
                <span class="inline-flex rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                    {{ $currentLesson['duration_label'] }}
                </span>
            </div>

            <h1 class="mt-5 font-raleway text-4xl font-semibold leading-tight text-bleuone">{{ $currentLesson['title'] }}</h1>

            <div class="mt-8 grid gap-5 md:grid-cols-2">
                <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Objectif</p>
                    <p class="mt-3 text-base leading-8 text-bleuone">{{ $currentLesson['objective'] }}</p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Intention pedagogique</p>
                    <p class="mt-3 text-base leading-8 text-bleuone">{{ $currentLesson['pedagogical_intention'] }}</p>
                </article>
            </div>

            <div class="mt-8 space-y-5">
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Methode pedagogique</p>
                    <p class="mt-3 text-base leading-8 text-slate-600">{{ $currentLesson['method'] }}</p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Processus d apprentissage</p>
                    <p class="mt-3 text-base leading-8 text-slate-600">{{ $currentLesson['learning_process'] }}</p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Sujet a aborder</p>
                    <p class="mt-3 text-base leading-8 text-slate-600">{{ $currentLesson['subject'] }}</p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Activite et medias necessaires</p>
                    <p class="mt-3 text-base leading-8 text-slate-600">{{ $currentLesson['activity'] }}</p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Ressources existantes ou a creer</p>
                    <p class="mt-3 text-base leading-8 text-slate-600">{{ $currentLesson['resources'] }}</p>
                </article>
            </div>
        </section>

        <aside class="space-y-6">
            <article class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">Navigation</p>
                <h2 class="mt-4 font-raleway text-3xl font-semibold text-bleuone">{{ $currentChapter['title'] }}</h2>
                <div class="mt-6 space-y-3">
                    @if ($previousLesson)
                        <a href="{{ $previousLesson['url'] }}" class="flex items-center justify-between rounded-[22px] border border-slate-200 px-5 py-4 transition hover:border-orangeone/30 hover:bg-orange-50/30">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Lecon precedente</p>
                                <p class="mt-2 text-lg font-semibold text-bleuone">{{ $previousLesson['title'] }}</p>
                            </div>
                            <span class="text-orangeone text-2xl leading-none">←</span>
                        </a>
                    @endif

                    @if ($nextLesson)
                        <a href="{{ $nextLesson['url'] }}" class="flex items-center justify-between rounded-[22px] border border-orange-200 bg-orange-50/60 px-5 py-4 transition hover:border-orangeone/50 hover:bg-orange-50">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-orangeone">Lecon suivante</p>
                                <p class="mt-2 text-lg font-semibold text-bleuone">{{ $nextLesson['title'] }}</p>
                            </div>
                            <span class="text-orangeone text-2xl leading-none">→</span>
                        </a>
                    @endif
                </div>
            </article>

            <article class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Reperes</p>
                <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                    <p><span class="font-semibold text-bleuone">Module :</span> {{ $currentModule['title'] }}</p>
                    <p><span class="font-semibold text-bleuone">Chapitre :</span> {{ $currentChapter['title'] }}</p>
                    <p><span class="font-semibold text-bleuone">Duree :</span> {{ $currentLesson['duration_label'] }}</p>
                </div>
            </article>
        </aside>
    </div>
@endsection
