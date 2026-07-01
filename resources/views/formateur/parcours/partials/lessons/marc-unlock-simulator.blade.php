<div
    x-data="{
        step: 1,
        email: 'marc.lefebvre@',
        profileEmail: 'marc.lefebvre@',
        messageBody: 'Bonjour Marc,\n\nVoici votre code d acces pour rejoindre la formation Hygiene alimentaire 2026.\n\nA bientot.',
        codeCopied: false,
        codeSent: false,
        profileSaved: false,
        profileDeleted: false,
        profileRecreated: false,
        feedback: {
            type: 'info',
            title: 'Mission',
            body: 'Commencez par retrouver le code d acces de Marc dans le tableau des stagiaires.'
        },
        setStep(value) {
            this.step = value;
        },
        copyCode() {
            this.codeCopied = true;
            this.feedback = {
                type: 'success',
                title: 'Code repere',
                body: 'Le code MARC01 est le code d acces a transmettre a Marc.'
            };
        },
        sendCode() {
            if (!this.codeCopied) {
                this.feedback = {
                    type: 'warning',
                    title: 'Code manquant',
                    body: 'Reperez d abord le code d acces de Marc dans le tableau des stagiaires.'
                };
                return;
            }

            this.codeSent = true;
            this.step = 2;
            this.feedback = {
                type: 'success',
                title: 'Message envoye',
                body: 'Marc a recu le lien de connexion et son code d acces. Si le blocage persiste, passez a la correction du profil.'
            };
        },
        saveProfile() {
            const isValid = this.profileEmail.includes('@') && this.profileEmail.includes('.') && !this.profileEmail.endsWith('@');

            if (!isValid) {
                this.feedback = {
                    type: 'warning',
                    title: 'Adresse incomplete',
                    body: 'Corrigez l adresse e-mail avant d enregistrer la fiche profil.'
                };
                return;
            }

            this.email = this.profileEmail;
            this.profileSaved = true;
            this.step = 3;
            this.feedback = {
                type: 'success',
                title: 'Profil corrige',
                body: 'La fiche de Marc est maintenant coherente. La suppression / recreation reste la solution de dernier recours.'
            };
        },
        deleteProfile() {
            this.profileDeleted = true;
            this.profileRecreated = false;
            this.feedback = {
                type: 'warning',
                title: 'Profil supprime',
                body: 'Le profil est retire du tableau. Il faut maintenant le recreer avec les bonnes informations.'
            };
        },
        recreateProfile() {
            if (!this.profileDeleted) {
                this.feedback = {
                    type: 'warning',
                    title: 'Ordre des actions',
                    body: 'Cette solution de dernier recours commence par la suppression du profil incorrect.'
                };
                return;
            }

            this.profileRecreated = true;
            this.feedback = {
                type: 'success',
                title: 'Marc est debloque',
                body: 'Le profil a ete recree proprement. Marc dispose maintenant du bon e-mail, du bon groupe et du code MARC01.'
            };
        }
    }"
    class="mx-auto w-full max-w-[1285px] space-y-4"
>
    <header class="sticky top-0 z-20 rounded-[20px] border border-bleuone/10 bg-white/95 px-5 py-4 shadow-sm backdrop-blur">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Exercice</p>
                <h1 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Debloquez Marc</h1>
                <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                    1. Donnez-lui son code d'acces. 2. Si besoin, modifiez son profil. 3. En dernier recours, supprimez puis recréez le profil.
                </p>
            </div>

            <div class="grid gap-2 sm:grid-cols-3">
                <button
                    type="button"
                    @click="setStep(1)"
                    class="rounded-full px-4 py-2 text-xs font-black uppercase tracking-wide transition"
                    :class="step === 1 ? 'bg-orangeone text-white' : (codeSent ? 'bg-vertone/10 text-vertone' : 'bg-slate-100 text-slate-500')"
                >
                    1 Code
                </button>
                <button
                    type="button"
                    @click="setStep(2)"
                    class="rounded-full px-4 py-2 text-xs font-black uppercase tracking-wide transition"
                    :class="step === 2 ? 'bg-orangeone text-white' : (profileSaved ? 'bg-vertone/10 text-vertone' : 'bg-slate-100 text-slate-500')"
                >
                    2 Profil
                </button>
                <button
                    type="button"
                    @click="setStep(3)"
                    class="rounded-full px-4 py-2 text-xs font-black uppercase tracking-wide transition"
                    :class="step === 3 ? 'bg-orangeone text-white' : (profileRecreated ? 'bg-vertone/10 text-vertone' : 'bg-slate-100 text-slate-500')"
                >
                    3 Recréer
                </button>
            </div>
        </div>
    </header>

    <div
        class="rounded-[18px] border px-4 py-3 shadow-sm"
        :class="{
            'border-bleuone/20 bg-bleuone/5 text-bleuone': feedback.type === 'info',
            'border-orangeone/25 bg-orangeone/10 text-orangeone': feedback.type === 'warning',
            'border-vertone/25 bg-vertone/10 text-vertone': feedback.type === 'success'
        }"
    >
        <p class="text-sm font-bold" x-text="feedback.title"></p>
        <p class="mt-1 text-sm leading-6" x-text="feedback.body"></p>
    </div>

    <main class="grid gap-4 xl:grid-cols-[minmax(0,1.05fr)_minmax(420px,0.95fr)]">
        <section class="space-y-4">
            <article class="rounded-[20px] bg-white p-4 shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Tableau 1</p>
                        <h2 class="mt-1 font-raleway text-xl font-semibold text-bleuone">Mes stagiaires</h2>
                        <p class="mt-1 text-sm text-slate-500">Comme sur la page formateur/stagiaires, reperez Marc et son code.</p>
                    </div>
                    <button type="button" disabled class="btn-oneduc h-10 cursor-not-allowed opacity-45 !px-4 !text-sm">
                        Ajouter un stagiaire
                    </button>
                </div>

                <div class="mt-4 overflow-x-auto rounded-[18px] border border-bleuone/15">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-bleuone text-xs uppercase text-white">
                            <tr>
                                <th class="px-4 py-3">Prenom</th>
                                <th class="px-4 py-3">Nom</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Code d'acces</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t bg-orangeone/8">
                                <td class="px-4 py-4 font-semibold">Marc</td>
                                <td class="px-4 py-4">Lefebvre</td>
                                <td class="px-4 py-4 font-mono text-xs" x-text="email"></td>
                                <td class="px-4 py-4">
                                    <span class="rounded-lg bg-orangeone/10 px-2 py-1 font-mono text-sm font-bold text-orangeone">MARC01</span>
                                </td>
                                <td class="px-4 py-4">
                                    <button type="button" @click="copyCode()" class="btn-oneduc !px-3 !py-1.5 !text-xs">
                                        Utiliser ce code
                                    </button>
                                </td>
                            </tr>
                            <tr class="border-t bg-white">
                                <td class="px-4 py-4 font-semibold">Sofia</td>
                                <td class="px-4 py-4">Martin</td>
                                <td class="px-4 py-4 font-mono text-xs">sofia.martin@example.fr</td>
                                <td class="px-4 py-4 font-mono text-sm text-slate-400">SOFIA1</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">Non concerne</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-[20px] bg-white p-4 shadow-md">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Tableau 2</p>
                <h2 class="mt-1 font-raleway text-xl font-semibold text-bleuone">Suivi des solutions</h2>

                <div class="mt-4 overflow-x-auto rounded-[18px] border border-slate-200">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Niveau</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t">
                                <td class="px-4 py-3 font-bold text-bleuone">1</td>
                                <td class="px-4 py-3">Envoyer le lien et le code d'acces</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold" :class="codeSent ? 'bg-vertone/10 text-vertone' : 'bg-slate-100 text-slate-500'" x-text="codeSent ? 'Fait' : 'A faire'"></span>
                                </td>
                            </tr>
                            <tr class="border-t">
                                <td class="px-4 py-3 font-bold text-bleuone">2</td>
                                <td class="px-4 py-3">Modifier la fiche profil si l'e-mail bloque</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold" :class="profileSaved ? 'bg-vertone/10 text-vertone' : 'bg-slate-100 text-slate-500'" x-text="profileSaved ? 'Fait' : 'Si besoin'"></span>
                                </td>
                            </tr>
                            <tr class="border-t">
                                <td class="px-4 py-3 font-bold text-bleuone">3</td>
                                <td class="px-4 py-3">Supprimer et recréer le profil en dernier recours</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold" :class="profileRecreated ? 'bg-vertone/10 text-vertone' : 'bg-slate-100 text-slate-500'" x-text="profileRecreated ? 'Fait' : 'Dernier recours'"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="rounded-[20px] bg-white p-5 shadow-md">
            <template x-if="step === 1">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Niveau 1</p>
                    <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Envoyer le code d'acces</h2>
                    <p class="mt-1 text-sm text-slate-500">Le premier reflexe consiste a transmettre le bon code au stagiaire.</p>

                    <div class="mt-5 rounded-[18px] border border-slate-200 bg-slate-50 p-4">
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Destinataire</span>
                            <input type="text" value="Marc Lefebvre" disabled class="mt-1 h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-500">
                        </label>

                        <label class="mt-4 block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Message</span>
                            <textarea x-model="messageBody" rows="6" class="mt-1 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm leading-6 shadow-sm focus:border-orangeone focus:ring-orangeone"></textarea>
                        </label>

                        <div class="mt-4 rounded-[16px] border border-orangeone/20 bg-white px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Ajout automatique</p>
                            <p class="mt-1 text-sm text-slate-600">Lien : {{ route('stagiaire.code.form.legacy') }}</p>
                            <p class="mt-1 font-mono text-lg font-bold text-orangeone">Code : MARC01</p>
                        </div>
                    </div>

                    <button type="button" @click="sendCode()" class="btn-oneduc mt-5 h-11 w-full justify-center !rounded-full !px-5 !text-sm">
                        Envoyer le mail avec le code
                    </button>
                </div>
            </template>

            <template x-if="step === 2">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Niveau 2</p>
                    <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Modifier le profil</h2>
                    <p class="mt-1 text-sm text-slate-500">Si Marc ne recoit rien, controlez l'adresse e-mail et corrigez la fiche.</p>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Prenom</span>
                            <input type="text" value="Marc" disabled class="mt-1 h-10 w-full rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-500">
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Nom</span>
                            <input type="text" value="Lefebvre" disabled class="mt-1 h-10 w-full rounded-md border border-slate-200 bg-slate-50 px-3 text-sm text-slate-500">
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">E-mail</span>
                            <input
                                type="email"
                                x-model="profileEmail"
                                class="mt-1 h-10 w-full rounded-md border px-3 text-sm shadow-sm focus:border-orangeone focus:ring-orangeone"
                                :class="profileSaved ? 'border-vertone' : 'border-orangeone'"
                            >
                        </label>

                        <label class="block md:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Code d'acces</span>
                            <input type="text" value="MARC01" disabled class="mt-1 h-10 w-full rounded-md border border-slate-200 bg-slate-50 px-3 font-mono text-sm font-bold tracking-widest text-orangeone">
                        </label>
                    </div>

                    <button type="button" @click="saveProfile()" class="btn-oneduc mt-5 h-11 w-full justify-center !rounded-full !px-5 !text-sm">
                        Enregistrer la fiche corrigee
                    </button>
                </div>
            </template>

            <template x-if="step === 3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Niveau 3</p>
                    <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Supprimer puis recréer</h2>
                    <p class="mt-1 text-sm text-slate-500">Cette solution est a utiliser uniquement si le profil reste inutilisable.</p>

                    <div class="mt-5 overflow-hidden rounded-[18px] border border-slate-200">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Profil</th>
                                    <th class="px-4 py-3">Etat</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t" x-show="!profileDeleted"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95">
                                    <td class="px-4 py-4 font-semibold">Marc Lefebvre</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-orangeone/10 px-3 py-1 text-xs font-bold text-orangeone">Profil incorrect</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button type="button" @click="deleteProfile()" class="rounded-full bg-red-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                                <tr class="border-t" x-show="profileDeleted && !profileRecreated" x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95">
                                    <td class="px-4 py-4 text-slate-400">Marc Lefebvre</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Supprime</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button type="button" @click="recreateProfile()" class="btn-oneduc !px-3 !py-1.5 !text-xs">
                                            Recréer
                                        </button>
                                    </td>
                                </tr>
                                <tr class="border-t" x-show="profileRecreated" x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95">
                                    <td class="px-4 py-4 font-semibold">Marc Lefebvre</td>
                                    <td class="px-4 py-4">
                                        <span class="rounded-full bg-vertone/10 px-3 py-1 text-xs font-bold text-vertone">Profil propre</span>
                                    </td>
                                    <td class="px-4 py-4 font-mono text-sm font-bold text-orangeone">MARC01</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="profileRecreated" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="mt-5 flex justify-end">
                        <a href="{{ $mixedNextUrl }}" class="btn-oneduc !rounded-full !px-6 !py-3">
                            Continuer
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </template>
        </section>
    </main>
</div>
