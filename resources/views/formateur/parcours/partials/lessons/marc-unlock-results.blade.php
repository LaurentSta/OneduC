<div class="mx-auto w-full max-w-[1285px] space-y-5">
    <section class="rounded-[20px] bg-white px-6 py-6 shadow-md">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Resultats de l'exercice</p>
                <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Marc a ete debloque</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Le formateur a retrouve Marc, identifie son code d'acces, ouvert sa fiche, puis transmis les informations de connexion depuis le volet message.
                </p>
            </div>

            <span class="inline-flex items-center rounded-full bg-vertone/10 px-4 py-2 text-sm font-bold text-vertone">
                Exercice termine
            </span>
        </div>

        <div class="mt-6 overflow-hidden rounded-[20px] border border-bleuone/15">
            <table class="min-w-full bg-white text-left text-sm text-slate-700">
                <thead class="bg-bleuone text-xs uppercase text-white">
                    <tr>
                        <th class="px-5 py-3">Etape</th>
                        <th class="px-5 py-3">Action realisee</th>
                        <th class="px-5 py-3">Resultat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t bg-white">
                        <td class="px-5 py-4 font-bold text-bleuone">1</td>
                        <td class="px-5 py-4">Recherche de Marc dans le tableau des stagiaires</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">Marc retrouve</span>
                        </td>
                    </tr>
                    <tr class="border-t bg-orangeone/5">
                        <td class="px-5 py-4 font-bold text-bleuone">2</td>
                        <td class="px-5 py-4">Verification du code d'acces</td>
                        <td class="px-5 py-4 font-mono text-sm font-bold text-orangeone">MARC01</td>
                    </tr>
                    <tr class="border-t bg-white">
                        <td class="px-5 py-4 font-bold text-bleuone">3</td>
                        <td class="px-5 py-4">Ouverture de la fiche stagiaire</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">Profil consulte</span>
                        </td>
                    </tr>
                    <tr class="border-t bg-orangeone/5">
                        <td class="px-5 py-4 font-bold text-bleuone">4</td>
                        <td class="px-5 py-4">Envoi du lien et du code depuis le volet message</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">Message envoye</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <article class="rounded-[18px] border border-bleuone/10 bg-bleuone/5 px-4 py-4">
                <p class="text-xs font-black uppercase tracking-wide text-bleuone">Stagiaire</p>
                <p class="mt-2 text-lg font-bold text-bleuone">Marc Lefebvre</p>
            </article>
            <article class="rounded-[18px] border border-orangeone/20 bg-orangeone/5 px-4 py-4">
                <p class="text-xs font-black uppercase tracking-wide text-orangeone">Code transmis</p>
                <p class="mt-2 font-mono text-lg font-bold text-orangeone">MARC01</p>
            </article>
            <article class="rounded-[18px] border border-vertone/20 bg-vertone/5 px-4 py-4">
                <p class="text-xs font-black uppercase tracking-wide text-vertone">Statut</p>
                <p class="mt-2 text-lg font-bold text-vertone">Acces relance</p>
            </article>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ $mixedPartUrls['modifier-contenu'] ?? '#' }}" class="btn-oneduc !rounded-full !px-7 !py-3">
                Suivant
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</div>
