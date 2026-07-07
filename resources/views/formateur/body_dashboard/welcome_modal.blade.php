@php
    $parcoursUrl = route('formateur.parcours.index');
@endphp

<div
    x-data="{
        storageKey: 'oneduc-formateur-welcome-dismissed-{{ auth()->id() }}',
        open: false,
        disableFuture: false,
        init() {
            this.open = window.localStorage.getItem(this.storageKey) !== '1';
        },
        persistPreference() {
            if (this.disableFuture) {
                window.localStorage.setItem(this.storageKey, '1');
            }
        },
        dismiss() {
            this.persistPreference();
            this.open = false;
        },
        openGuide() {
            this.persistPreference();
            window.location.assign('{{ $parcoursUrl }}');
        }
    }"
    x-init="init()"
    x-show="open"
    class="fixed inset-0 z-[90] flex items-center justify-center p-4"
    @keydown.escape.window="dismiss()"
    style="display: none;"
>
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-[2px]" @click="dismiss()"></div>

    <section class="relative w-full max-w-xl rounded-[28px] border border-bleuone/10 bg-white p-8 shadow-2xl md:p-10">
        <h2 class="font-raleway text-3xl font-semibold leading-tight text-bleuone">
            Bienvenue dans votre espace formateur
        </h2>

        <p class="mt-4 font-varela text-base leading-7 text-gray-700">
            Prenez vos reperes pour decouvrir les fonctionnalites essentielles de la plateforme.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <button
                type="button"
                @click="openGuide()"
                class="inline-flex items-center justify-center rounded-xl bg-orangeone px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-600"
            >
                Decouvrir le parcours
            </button>

            <button
                type="button"
                @click="dismiss()"
                class="inline-flex items-center justify-center rounded-xl border border-bleuone px-6 py-3 text-sm font-semibold text-bleuone transition hover:bg-slate-50"
            >
                Plus tard
            </button>
        </div>

        <label class="mt-7 flex items-center gap-3 font-varela text-sm text-gray-700">
            <input type="checkbox" x-model="disableFuture" class="h-4 w-4 rounded border-gray-300 text-orangeone focus:ring-orange-200">
            <span>Ne plus afficher cette fenêtre</span>
        </label>
    </section>
</div>
