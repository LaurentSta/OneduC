@extends('formateur.parcours.layout')

@section('parcours_content')
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm md:p-10">
            <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">{{ $currentChapter['label'] }}</p>
            <h1 class="mt-4 font-raleway text-4xl font-semibold leading-tight text-bleuone">{{ $currentChapter['title'] }}</h1>
            <p class="mt-5 text-lg leading-9 text-slate-600">{{ $currentChapter['description'] }}</p>

            <div class="mt-8 grid gap-5 md:grid-cols-2">
                <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Objectif</p>
                    <p class="mt-3 text-base leading-8 text-bleuone">{{ $currentChapter['objective'] }}</p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-slate-50/70 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Duree estimee</p>
                    <p class="mt-3 text-base leading-8 text-bleuone">{{ $currentChapter['duration_label'] }}</p>
                </article>
            </div>

            <section class="mt-10">
                <h2 class="text-2xl font-semibold text-bleuone">Lecons du chapitre</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($currentChapter['lessons'] as $lesson)
                        <a
                            href="{{ $lesson['url'] }}"
                            class="flex items-center justify-between rounded-[22px] border border-slate-200 px-6 py-5 transition hover:border-orangeone/40 hover:bg-orange-50/30"
                        >
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-orangeone">{{ $lesson['code'] }}</p>
                                <h3 class="mt-2 text-xl font-semibold text-bleuone">{{ $lesson['title'] }}</h3>
                                <p class="mt-2 text-sm text-slate-500">{{ $lesson['duration_label'] }}</p>
                            </div>
                            <span class="text-orangeone text-2xl leading-none">→</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </section>

        <aside class="space-y-6">
            <article class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">Dans ce module</p>
                <h2 class="mt-4 font-raleway text-3xl font-semibold text-bleuone">{{ $currentModule['title'] }}</h2>
                <p class="mt-4 text-lg leading-9 text-slate-600">
                    Ce chapitre fait partie du module et conserve la meme navigation que le reste du parcours formateur.
                </p>
                <a href="{{ $currentModule['url'] }}" class="mt-8 inline-flex items-center rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-orangeone hover:text-orangeone">
                    Revenir au module
                </a>
            </article>

            <article class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Acces rapide</p>
                <a href="{{ $currentChapter['first_lesson_url'] }}" class="mt-5 flex items-center justify-between rounded-[22px] border border-orange-200 bg-orange-50/60 px-6 py-5 transition hover:border-orangeone/50 hover:bg-orange-50">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">Premiere lecon</p>
                        <p class="mt-2 text-2xl font-semibold text-bleuone">{{ array_values($currentChapter['lessons'])[0]['title'] ?? 'Ouvrir' }}</p>
                    </div>
                    <span class="text-3xl leading-none text-orangeone">→</span>
                </a>
            </article>
        </aside>
    </div>
@endsection
