@php
    $backUrl = $mixedPartUrls['modifier-contenu-groupe'] ?? '#';
    $bilanUrl = route('formateur.parcours.lessons.show', [
        'module' => 'organiser-ses-parcours',
        'chapter' => 'mettre-en-place-un-parcours-coherent',
        'lesson' => 'bilan-module-2',
    ]);
    $modules = [
        'Hygiene alimentaire',
        'Bonnes pratiques',
        'Evaluation finale',
        'Conservation des aliments et DLC',
    ];
@endphp

<div class="mx-auto w-full max-w-[1285px] space-y-6">
    <section class="rounded-[20px] bg-white px-6 py-6 shadow-md sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-vertone">Resultat de l'exercice</p>
                <h1 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">
                    Groupe Hygiene alimentaire 2026 mis a jour
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                    Le module complementaire est maintenant associe au groupe. Le recapitulatif reprend la carte visible dans la page Mes groupes.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ $backUrl }}" class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
                    Revoir le simulateur
                </a>
                <a href="{{ $bilanUrl }}" class="btn-oneduc !px-6 !py-2.5 !text-sm">
                    Suivant
                </a>
            </div>
        </div>
    </section>

    <section class="rounded-[20px] bg-white px-6 py-6 shadow-md sm:px-8">
        <div class="mx-auto max-w-[520px]">
            <article class="flex flex-col rounded-[20px] border border-gray-200 bg-white p-6 shadow">
                <div class="flex-1 space-y-5">
                    <div class="border-b border-gray-100 pb-4">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="truncate font-raleway text-xl font-bold text-bleuone">
                                    Hygiene alimentaire 2026
                                </h3>
                                <p class="mt-2 text-xs italic text-gray-400 font-lisible">
                                    Cree le 18/05/2026
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <span class="inline-flex items-center gap-2 rounded-full border border-vertone/20 bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-vertone"></span>
                                    Actif
                                </span>

                                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                    Groupe suivi
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm leading-7 text-gray-700 font-lisible">
                        Groupe utilise pour organiser les ressources, les exercices et le suivi des stagiaires du parcours hygiene.
                    </p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="flex items-center gap-3 rounded-2xl border border-bleuone/15 bg-slate-50 px-4 py-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-bleuone/10 text-bleuone">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Debut</p>
                                <p class="text-sm font-bold text-bleuone">02/02/2026</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-2xl border border-orangeone/15 bg-orange-50/60 px-4 py-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orangeone/10 text-orangeone">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Fin</p>
                                <p class="text-sm font-bold text-orangeone">27/03/2026</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 rounded-2xl bg-gray-50/80 p-4">
                        <div>
                            <h4 class="mb-2 text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500 font-varela">Modules associes</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($modules as $module)
                                    <span @class([
                                        'inline-flex items-center rounded-full px-3 py-1 text-xs font-varela',
                                        'bg-vertone/10 text-vertone' => $module !== 'Conservation des aliments et DLC',
                                        'bg-orangeone/10 text-orangeone font-bold' => $module === 'Conservation des aliments et DLC',
                                    ])>
                                        {{ $module }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-4">
                            <div>
                                <h4 class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500 font-varela">Stagiaires</h4>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-orangeone font-lisible">
                                18 stagiaires
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="button" class="btn-oneduc w-1/2 cursor-not-allowed opacity-70" disabled>
                        Modifier
                    </button>
                    <button type="button" class="btn-oneduc-blue w-1/2 cursor-not-allowed opacity-70" disabled>
                        Supprimer
                    </button>
                </div>
            </article>

            <div class="mt-6 flex justify-end">
                <a href="{{ $bilanUrl }}" class="btn-oneduc px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
                    Suivant
                </a>
            </div>
        </div>
    </section>
</div>
