@extends('formateur.parcours.layout')

@section('parcours_content')
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
            <div class="px-8 py-8 md:px-10 md:py-10">
                <p class="inline-flex rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">
                    Premiers pas
                </p>
                <h1 class="mt-5 font-raleway text-4xl font-semibold leading-tight text-bleuone">
                    Bienvenue dans votre parcours formateur
                </h1>
                <p class="mt-6 max-w-4xl text-lg leading-9 text-slate-600">
                    Cette page sert de point d entree. Vous gardez votre posture de formateur, avec un chemin simple pour avancer librement entre les modules.
                </p>
            </div>

            <div class="grid gap-4 border-t border-slate-100 bg-slate-50/70 px-8 py-8 md:grid-cols-3 md:px-10">
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <h2 class="text-2xl font-semibold leading-tight text-bleuone">Un parcours visible</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        Le chemin orange vous montre ou vous etes et ce qui vient ensuite.
                    </p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <h2 class="text-2xl font-semibold leading-tight text-bleuone">Une navigation libre</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        Chaque etape reste accessible sans imposer un ordre rigide.
                    </p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <h2 class="text-2xl font-semibold leading-tight text-bleuone">Une aide progressive</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        La vue stagiaire n apparait qu en apercu, quand elle est utile.
                    </p>
                </article>
            </div>

            <div class="grid gap-4 border-t border-slate-100 px-8 py-8 md:grid-cols-3 md:px-10">
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <h2 class="text-2xl font-semibold leading-tight text-bleuone">Ce que vous allez trouver ici</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        Des reperes clairs pour comprendre la plateforme, organiser vos groupes et suivre vos apprenants sans vous perdre dans le site.
                    </p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <h2 class="text-2xl font-semibold leading-tight text-bleuone">Comment utiliser ce parcours</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        Vous pouvez commencer par le module 1 puis revenir plus tard sur les autres parties, ou naviguer directement vers l etape qui vous aide le plus.
                    </p>
                </article>
                <article class="rounded-[24px] border border-slate-200 bg-white p-6">
                    <h2 class="text-2xl font-semibold leading-tight text-bleuone">Vue stagiaire</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        Elle sera montree sous forme d apercus cibles, sans vous basculer dans un vrai compte stagiaire.
                    </p>
                </article>
            </div>

            <div class="px-8 pb-8 md:px-10 md:pb-10">
                <article class="rounded-[28px] border border-orange-200 bg-orange-50/60 p-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">Focus</p>
                    <h2 class="mt-4 font-raleway text-3xl font-semibold text-[#d54b18]">Le compromis choisi</h2>
                    <p class="mt-4 text-lg leading-9 text-[#d54b18]">
                        Vous restez dans un espace formateur, mais vous pouvez voir la logique cote stagiaire au moment opportun. Cela evite une demonstration trop lourde des le depart.
                    </p>
                </article>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('formateur.parcours.modules.show', ['module' => 'prendre-ses-reperes']) }}" class="btn-oneduc !px-7 !py-4">
                        Commencer le module 1
                    </a>
                    <a href="{{ route('formateur.dashboard') }}" class="btn-oneduc-outline !px-7 !py-4">
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <article class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">Navigation libre</p>
                <h2 class="mt-4 font-raleway text-3xl font-semibold text-bleuone">Chemin du formateur</h2>
                <p class="mt-4 text-lg leading-9 text-slate-600">
                    Le parcours visuel ci-dessus vous laisse circuler librement entre les etapes. Vous n etes pas oblige de tout suivre dans l ordre.
                </p>

                <a
                    href="{{ route('formateur.parcours.modules.show', ['module' => 'prendre-ses-reperes']) }}"
                    class="mt-8 flex items-center justify-between rounded-[24px] border border-orange-200 bg-orange-50/60 px-6 py-5 transition hover:border-orangeone/50 hover:bg-orange-50"
                >
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.26em] text-orangeone">Etape suivante</p>
                        <p class="mt-2 text-2xl font-semibold text-bleuone">Module 1</p>
                    </div>
                    <span class="text-3xl leading-none text-orangeone">→</span>
                </a>
            </article>

            <article class="rounded-[28px] border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Acces rapides</p>
                <div class="mt-5 space-y-4">
                    <a href="{{ route('formateur.formations.index') }}" class="flex items-center justify-between rounded-[20px] border border-slate-200 px-5 py-4 transition hover:border-orangeone/30 hover:bg-orange-50/30">
                        <span class="text-lg font-medium text-bleuone">Mes formations</span>
                        <span class="text-sky-500">↗</span>
                    </a>
                    <a href="{{ route('formateur.groupes.index') }}" class="flex items-center justify-between rounded-[20px] border border-slate-200 px-5 py-4 transition hover:border-orangeone/30 hover:bg-orange-50/30">
                        <span class="text-lg font-medium text-bleuone">Mes groupes</span>
                        <span class="text-sky-500">↗</span>
                    </a>
                    <a href="{{ route('formateur.documentation') }}" class="flex items-center justify-between rounded-[20px] border border-slate-200 px-5 py-4 transition hover:border-orangeone/30 hover:bg-orange-50/30">
                        <span class="text-lg font-medium text-bleuone">Documentation</span>
                        <span class="text-sky-500">↗</span>
                    </a>
                </div>
            </article>
        </aside>
    </div>
@endsection
