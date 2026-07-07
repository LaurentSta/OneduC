@php
    $validationUrl = $mixedPartUrls['validation'] ?? '#';
@endphp

<div
    x-data="{
        drawerOpen: false,
        showInstructions: false,
        codeIncluded: false,
        notification: false,
        email: false,
        sent: false,
        feedback: null,
        sendMessage() {
            if (!this.codeIncluded || !this.email) {
                this.feedback = {
                    type: 'warning',
                    title: 'Options obligatoires',
                    body: 'Cochez Inclure le lien et le code d acces, puis Email, avant d envoyer le message a Marc.'
                };
                return;
            }

            this.sent = true;
            this.feedback = {
                type: 'success',
                title: 'Message envoye',
                body: 'Marc a recu le lien de connexion et son code d acces. Vous pouvez maintenant enregistrer les modifications.'
            };
        },
        save() {
            if (!this.codeIncluded || !this.email) {
                this.feedback = {
                    type: 'warning',
                    title: 'Options obligatoires',
                    body: 'Cochez Inclure le lien et le code d acces, puis Email, avant d enregistrer les modifications.'
                };
                return;
            }

            if (!this.sent) {
                this.feedback = {
                    type: 'warning',
                    title: 'Message non envoye',
                    body: 'Ouvrez le volet message avec l enveloppe et envoyez le code a Marc avant de continuer.'
                };
                return;
            }

            window.location.href = @js($validationUrl);
        }
    }"
    class="mx-auto w-full max-w-[1285px]"
>
    <div>
        <main class="rounded-[20px] bg-white px-8 py-8 shadow-md">
            <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">Etape 2</p>
                    <h1 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Modifier le stagiaire</h1>
                </div>

                <button
                    type="button"
                    @click="showInstructions = true"
                    class="consigne-invite inline-flex h-12 items-center justify-center gap-3 rounded-full border-2 border-orangeone/30 bg-orangeone/10 px-6 text-base font-bold text-orangeone shadow-sm transition hover:border-orangeone hover:bg-orangeone hover:text-white"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    Consigne
                </button>
            </header>

            <section class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
                <div class="rounded-[16px] border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-bleuone font-raleway mb-1">Informations du stagiaire</h2>
                    <p class="text-sm text-gray-600 font-lisible mb-6">Simulation de la fiche Marc Lefebvre.</p>

                    <p class="mb-5 rounded-[16px] border border-bleuone/10 bg-bleuone/5 px-4 py-3 text-sm leading-6 text-slate-700">
                        Cette fiche permet de controler les informations utiles avant l'envoi : adresse e-mail, code d'acces et rattachement au bon groupe.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label>
                            <span class="block mb-2 text-sm font-medium text-gray-900">Prenom</span>
                            <input type="text" value="Marc" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm">
                        </label>

                        <label>
                            <span class="block mb-2 text-sm font-medium text-gray-900">Nom</span>
                            <input type="text" value="Lefebvre" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm">
                        </label>

                        <label>
                            <span class="block mb-2 text-sm font-medium text-gray-900">Adresse e-mail</span>
                            <input type="email" value="marc.lefebvre@example.fr" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm">
                        </label>

                        <label>
                            <span class="block mb-2 text-sm font-medium text-gray-900">Nouveau mot de passe</span>
                            <input type="password" disabled placeholder="Laisser vide si inchange" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm">
                        </label>

                        <label class="md:col-span-2">
                            <span class="block mb-2 text-sm font-medium text-gray-900">Code d'acces</span>
                            <input type="text" value="MARC01" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 font-mono text-sm uppercase tracking-widest text-orangeone">
                        </label>
                    </div>
                </div>

                <aside class="rounded-[16px] border border-bleuone/20 bg-bleuone/5 p-6">
                    <h2 class="text-lg font-bold text-bleuone font-raleway mb-1">Groupes associes</h2>
                    <p class="text-sm text-gray-600 font-lisible mb-4">Marc doit rester rattache au bon groupe.</p>
                    <p class="mb-4 rounded-[16px] border border-white bg-white px-4 py-3 text-sm leading-6 text-slate-700">
                        Le groupe confirme que Marc est bien inscrit dans le parcours attendu avant de lui renvoyer son acces.
                    </p>
                    <label class="flex items-start gap-3 rounded-xl border border-white bg-white px-4 py-3 shadow-sm">
                        <input type="checkbox" checked disabled class="mt-1 rounded border-gray-300 text-bleuone">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Hygiene alimentaire 2026</span>
                            <span class="block text-xs text-gray-500">Groupe principal de Marc.</span>
                        </span>
                    </label>
                </aside>
            </section>

            <div
                x-show="feedback"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="mt-6 rounded-[18px] border px-4 py-3"
                :class="feedback && feedback.type === 'success' ? 'border-vertone/25 bg-vertone/10 text-vertone' : 'border-orangeone/25 bg-orangeone/10 text-orangeone'"
            >
                <p class="text-sm font-bold" x-text="feedback ? feedback.title : ''"></p>
                <p class="mt-1 text-sm leading-6" x-text="feedback ? feedback.body : ''"></p>
            </div>

            <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <a href="{{ $mixedPartUrls['debloquer-marc'] ?? '#' }}" class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
                    Retour au tableau
                </a>

                <div class="flex w-full flex-col items-center gap-3 md:w-auto md:flex-row">
                    <button
                        type="button"
                        @click="drawerOpen = true"
                        title="Messages avec Marc Lefebvre"
                        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-bleuone shadow-sm transition-colors hover:border-bleuone hover:bg-bleuone hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M3 8L10.89 13.26C11.2204 13.4793 11.6056 13.5963 12 13.5963C12.3944 13.5963 12.7796 13.4793 13.11 13.26L21 8M5 19H19C19.5304 19 20.0391 18.7893 20.4142 18.4142C20.7893 18.0391 21 17.5304 21 17V7C21 6.46957 20.7893 5.96086 20.4142 5.58579C20.0391 5.21071 19.5304 5 19 5H5C4.46957 5 3.96086 5.21071 3.58579 5.58579C3.21071 5.96086 3 6.46957 3 7V17C3 17.5304 3.21071 18.0391 3.58579 18.4142C3.96086 18.7893 4.46957 19 5 19Z"/>
                        </svg>
                        <span x-show="sent" x-cloak
                              x-transition:enter="transition ease-out duration-200"
                              x-transition:enter-start="opacity-0 scale-95"
                              x-transition:enter-end="opacity-100 scale-100"
                              x-transition:leave="transition ease-in duration-150"
                              x-transition:leave-start="opacity-100 scale-100"
                              x-transition:leave-end="opacity-0 scale-95"
                              class="absolute -right-1 -top-1 h-3 w-3 rounded-full bg-vertone"></span>
                    </button>

                    <button type="button" @click="save()" class="btn-oneduc w-full px-8 py-3 text-lg md:w-auto">
                        Enregistrer les modifications
                    </button>
                </div>
            </div>
        </main>
    </div>

    <div x-show="showInstructions" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/45" @click="showInstructions = false"></div>
        <section
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative mx-auto mt-24 w-[calc(100%-2rem)] max-w-lg rounded-[20px] border border-orangeone/20 bg-white p-6 shadow-[0_28px_80px_-24px_rgba(0,68,97,0.55),0_18px_36px_-22px_rgba(239,75,43,0.55)]"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-orangeone">Consigne</p>
                    <h2 class="mt-1 font-raleway text-2xl font-semibold text-bleuone">Envoyer le code a Marc</h2>
                </div>
                <button type="button" @click="showInstructions = false" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            <ol class="mt-5 list-decimal space-y-3 pl-5 text-base leading-7 text-slate-700">
                <li>
                    Cliquez sur l'enveloppe
                    <span class="mx-1 inline-flex h-8 w-8 align-middle items-center justify-center rounded-full border border-orangeone/25 bg-orangeone/10 text-orangeone shadow-sm" aria-hidden="true">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16v12H4z" />
                            <path d="m4 7 8 6 8-6" />
                        </svg>
                    </span>
                    pour ouvrir le volet message.
                </li>
                <li>Cochez Inclure le lien et le code d'acces.</li>
                <li>Cochez Email pour envoyer le message par mail a Marc.</li>
                <li>Envoyez le message, puis enregistrez les modifications pour acceder a la validation.</li>
            </ol>
        </section>
    </div>

    <div x-show="drawerOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/30" @click="drawerOpen = false"></div>
        <section
            x-transition:enter="transition-transform duration-300 ease-out"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform duration-200 ease-in"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col bg-white shadow-xl"
        >
            <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <p class="font-raleway text-base font-bold text-bleuone">Messages</p>
                    <p class="mt-0.5 text-xs text-gray-500">Marc Lefebvre</p>
                </div>
                <button type="button" @click="drawerOpen = false" class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                    </svg>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div class="space-y-4">
                    <p class="rounded-[16px] border border-bleuone/10 bg-bleuone/5 px-4 py-3 text-sm leading-6 text-slate-700">
                        Le volet message sert a transmettre directement a Marc son lien de connexion et son code d'acces, par notification et/ou e-mail.
                    </p>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-700">Sujet</span>
                        <input type="text" value="Votre code d'acces Oneduc" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-orangeone focus:ring-orangeone">
                    </label>

                    <label class="block">
                        <span class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-700">Message</span>
                        <textarea rows="6" class="w-full resize-none rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm leading-6 focus:border-orangeone focus:ring-orangeone">Bonjour Marc,

Voici votre lien de connexion et votre code d'acces pour rejoindre la formation.

Lien : https://oneduc.fr//stagiaire/connexion-code
Code : MARC01</textarea>
                    </label>

                    <label class="flex cursor-pointer select-none items-start gap-3 rounded-xl border border-orangeone/20 bg-orangeone/5 px-4 py-3">
                        <input type="checkbox" x-model="codeIncluded" class="mt-1 rounded border-orangeone text-orangeone focus:ring-orangeone">
                        <span>
                            <span class="block text-sm font-semibold text-bleuone">Inclure le lien et le code d'acces</span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500">Code ajoute : <span class="font-mono font-semibold text-orangeone">MARC01</span></span>
                        </span>
                    </label>

                    <div>
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-gray-700">Envoyer via</p>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" x-model="notification" class="rounded border-gray-300 text-orangeone focus:ring-orangeone">
                                Notification
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" x-model="email" class="rounded border-gray-300 text-orangeone focus:ring-orangeone">
                                Email
                            </label>
                        </div>
                    </div>

                    <button type="button" @click="sendMessage(); drawerOpen = false" class="btn-oneduc w-full py-2.5 text-sm">
                        Envoyer
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>
