@php
    $moduleUrl = $currentModule['url'] ?? route('formateur.parcours.index');
    $dashboardUrl = route('formateur.dashboard');
    $completedItems = [
        'Creer un groupe de formation',
        'Structurer une progression lisible',
        'Ajuster un groupe',
        'Traiter un blocage d acces',
        'Modifier un contenu pour un groupe avance',
    ];
@endphp

<div class="mx-auto flex min-h-full w-full max-w-[1285px] items-center px-4 py-8 sm:px-6 lg:px-8">
    <section class="w-full overflow-hidden rounded-[28px] border border-gray-100 bg-white shadow-md">
        <div class="grid items-center gap-8 px-6 py-8 md:grid-cols-[minmax(0,1fr)_360px] md:px-10 lg:px-12">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-vertone">
                    Module 2 termine
                </p>

                <h1 class="mt-3 font-raleway text-3xl font-semibold leading-tight text-bleuone md:text-4xl">
                    Bravo, vous avez termine le module Organiser ses parcours
                </h1>

                <p class="mt-4 max-w-3xl text-base leading-8 text-slate-600">
                    Vous avez parcouru toute la chaine utile au formateur : preparer le cadre, creer une progression,
                    ajuster un groupe et traiter les cas particuliers sans perdre la lisibilite du parcours.
                </p>

                <div class="mt-7 grid gap-4 sm:grid-cols-3">
                    <article class="rounded-[18px] border border-bleuone/10 bg-bleuone/5 px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-bleuone">Chapitres</p>
                        <p class="mt-2 text-3xl font-black text-bleuone">3</p>
                        <p class="mt-1 text-sm text-slate-600">objectifs consolides</p>
                    </article>

                    <article class="rounded-[18px] border border-orangeone/10 bg-orangeone/5 px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-orangeone">Lecons</p>
                        <p class="mt-2 text-3xl font-black text-orangeone">7</p>
                        <p class="mt-1 text-sm text-slate-600">etapes parcourues</p>
                    </article>

                    <article class="rounded-[18px] border border-vertone/10 bg-vertone/10 px-4 py-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-vertone">Statut</p>
                        <p class="mt-2 text-3xl font-black text-vertone">100%</p>
                        <p class="mt-1 text-sm text-slate-600">module finalise</p>
                    </article>
                </div>

                <div class="mt-7 rounded-[20px] border border-slate-200 bg-slate-50/70 p-5">
                    <h2 class="font-raleway text-xl font-bold text-bleuone">Ce que vous savez maintenant faire</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($completedItems as $item)
                            <div class="flex items-start gap-3 rounded-[14px] bg-white px-4 py-3 shadow-sm">
                                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-vertone text-white">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.27a1 1 0 0 1-1.42.003L3.29 9.13a1 1 0 1 1 1.42-1.41l4.09 4.126 6.49-6.55a1 1 0 0 1 1.414-.006Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <p class="text-sm font-semibold leading-6 text-slate-700">{{ $item }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $moduleUrl }}" class="btn-oneduc-outline justify-center !px-6 !py-3 !text-sm">
                        Revenir au module 2
                    </a>
                    <a href="{{ $dashboardUrl }}" class="btn-oneduc justify-center !px-6 !py-3 !text-sm">
                        Retour au tableau de bord
                    </a>
                </div>
            </div>

            <div class="flex justify-center md:justify-end">
                <div class="relative w-full max-w-[340px] rounded-[28px] border border-orangeone/10 bg-orangeone/5 p-8">
                    <img
                        src="{{ asset('images/svg/Finish.svg') }}"
                        alt="Module termine"
                        class="mx-auto h-auto w-full max-w-[280px]"
                    >
                </div>
            </div>
        </div>
    </section>
</div>
