@extends('formateur.parcours.layout')

@section('parcours_content')
<div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pb-16">

    <header class="mb-12 flex flex-col items-center text-center">
        <img
            src="{{ asset('frontend/assets/img/front-pages/branding/LogoOneducPositionG.svg') }}"
            alt="Logo Oneduc"
            class="mb-6 h-10 w-auto opacity-80"
        >
        <span class="inline-flex items-center rounded-full border border-orangeone/20 bg-orangeone/10 px-4 py-1.5 text-sm font-varela text-orangeone">
            Parcours formateur
        </span>
        <h1 class="mt-5 font-raleway text-3xl font-medium leading-tight text-bleuone md:text-4xl">
            Formation à l'utilisation d'Oneduc
        </h1>
        <p class="mt-4 max-w-xl font-lisible text-lg leading-relaxed text-slate-600">
            Prenez vos repères, organisez vos groupes et accompagnez vos apprenants grâce à ce parcours conçu pour les formateurs.
        </p>
    </header>

    <section class="space-y-4" aria-label="Modules du parcours">
        @foreach ($parcoursModules as $moduleKey => $module)
            @php
                $isUnderConstruction = !empty($module['is_under_construction']);
                $isCompleted = ($moduleCompletionMap[$moduleKey] ?? false) === true;
                $moduleUrl = $module['url'] ?? '#';
            @endphp

            <article class="flex items-center gap-5 rounded-[28px] border bg-white p-6 transition-all duration-200
                @if ($isCompleted)
                    border-vertone/40 shadow-md
                @elseif (!$isUnderConstruction)
                    border-orangeone/30 shadow-md hover:-translate-y-0.5 hover:shadow-lg
                @else
                    border-slate-200 shadow-sm
                @endif
            ">
                {{-- Numéro --}}
                <div class="flex size-12 shrink-0 items-center justify-center rounded-[18px] text-base font-varela font-bold
                    @if ($isCompleted) bg-teal-50 text-vertone
                    @elseif (!$isUnderConstruction) bg-orangeone/10 text-orangeone
                    @else bg-slate-100 text-slate-300
                    @endif
                ">
                    {{ $loop->iteration }}
                </div>

                {{-- Contenu --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-varela uppercase tracking-[0.24em]
                            {{ $isUnderConstruction ? 'text-slate-400' : 'text-orangeone' }}
                        ">
                            {{ $module['label'] }}
                        </span>
                        @if ($isCompleted)
                            <span class="rounded-full border border-vertone/30 bg-teal-50 px-3 py-0.5 text-xs font-varela text-vertone">
                                Validé
                            </span>
                        @elseif (!$isUnderConstruction)
                            <span class="rounded-full border border-orangeone/20 bg-orangeone/10 px-3 py-0.5 text-xs font-varela text-orangeone">
                                Disponible
                            </span>
                        @else
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-0.5 text-xs font-varela text-slate-400">
                                À venir
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-1 font-raleway text-lg font-semibold leading-tight
                        {{ $isUnderConstruction ? 'text-slate-400' : 'text-bleuone' }}
                    ">
                        {{ $module['title'] }}
                    </h2>

                    <p class="mt-1 font-lisible text-sm leading-6
                        {{ $isUnderConstruction ? 'text-slate-400' : 'text-slate-600' }}
                    ">
                        {{ $module['description'] }}
                    </p>

                    <p class="mt-2 font-varela text-xs
                        {{ $isUnderConstruction ? 'text-slate-300' : 'text-slate-400' }}
                    ">
                        {{ $module['duration_label'] }}
                    </p>
                </div>

                {{-- CTA --}}
                <div class="shrink-0">
                    @if ($isCompleted)
                        <a href="{{ $moduleUrl }}" class="btn-oneduc-blue !rounded-full !px-5 !py-2.5 !text-sm whitespace-nowrap">
                            Reprendre
                        </a>
                    @elseif (!$isUnderConstruction)
                        <a href="{{ $moduleUrl }}" class="btn-oneduc !rounded-full !px-5 !py-2.5 !text-sm whitespace-nowrap">
                            {{ $module['cta_label'] ?? 'Accéder' }}
                        </a>
                    @else
                        <a
                            href="{{ $moduleUrl }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 font-varela text-xs text-slate-400 transition hover:border-slate-300"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Bientôt
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <div class="mt-10 flex justify-center">
        <a href="{{ route('formateur.dashboard') }}" class="btn-oneduc-outline !px-7 !py-3">
            Retour au tableau de bord
        </a>
    </div>

</div>
@endsection
