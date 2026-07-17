{{--
  z-[60]/z-[70] : strictement au-dessus du bouton d'aide flottant (z-50,
  resources/views/frontend/master.blade.php) qui, à z-index égal,
  interceptait les clics sur les boutons en bas d'écran.
--}}
<div
    class="js-cookie-consent fixed bottom-0 left-0 z-[60] w-full bg-[#004461] text-white shadow-lg px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-4"
    role="alert"
    aria-live="polite"
    @if($alreadyConsentedWithCookies) style="display:none" @endif
>
    <span class="text-sm md:text-base">
        Ce site utilise des cookies essentiels à son fonctionnement et, avec votre accord, des cookies facultatifs (lecture de vidéos).
        <a href="{{ url('/cookies') }}" class="underline hover:text-orange-400 ml-1">En savoir plus</a>.
    </span>
    <div class="flex flex-wrap justify-center gap-2 mt-2 md:mt-0">
        <button type="button" class="js-cookie-consent-manage bg-transparent text-white font-semibold px-4 py-2 rounded border border-white/40 hover:bg-white/10 transition">
            Personnaliser
        </button>
        <button type="button" class="js-cookie-consent-decline bg-white text-[#004461] font-semibold px-4 py-2 rounded border border-orange-400 hover:bg-orange-100 transition">
            Tout refuser
        </button>
        <button type="button" class="js-cookie-consent-agree bg-orange-400 text-[#004461] font-semibold px-4 py-2 rounded hover:bg-orange-500 transition">
            Tout accepter
        </button>
    </div>
</div>

<div class="js-cookie-consent-modal-backdrop fixed inset-0 z-[70] hidden items-center justify-center bg-black/50 px-4">
    <div
        class="js-cookie-consent-modal w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl bg-white text-[#004461] shadow-xl p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-consent-modal-title"
    >
        <h2 id="cookie-consent-modal-title" class="text-lg font-semibold mb-1">Gérer mes préférences de cookies</h2>
        <p class="text-sm text-gray-600 mb-4">
            Les cookies essentiels ne peuvent pas être désactivés car ils sont indispensables au fonctionnement du site.
            Vous pouvez modifier votre choix à tout moment via le lien « Gérer mes cookies » en bas de page.
        </p>

        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
                <div>
                    <p class="font-semibold">Cookies essentiels</p>
                    <p class="text-sm text-gray-600">Session de connexion, sécurité des formulaires (CSRF) et mémorisation de vos choix de cookies.</p>
                </div>
                <span class="mt-1 inline-flex shrink-0 items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500">
                    Toujours actif
                </span>
            </div>

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-semibold">Cookies vidéos</p>
                    <p class="text-sm text-gray-600">Permettent la lecture des vidéos YouTube intégrées sur le site (mode confidentialité renforcée youtube-nocookie.com).</p>
                </div>
                <label class="mt-1 inline-flex shrink-0 cursor-pointer items-center">
                    <input type="checkbox" class="js-cookie-consent-category peer sr-only" data-category="video">
                    <span class="relative h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-orange-400 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="js-cookie-consent-modal-close px-4 py-2 rounded font-semibold text-[#004461] border border-gray-200 hover:bg-gray-50 transition">
                Annuler
            </button>
            <button type="button" class="js-cookie-consent-save bg-orange-400 text-[#004461] font-semibold px-4 py-2 rounded hover:bg-orange-500 transition">
                Enregistrer mes choix
            </button>
        </div>
    </div>
</div>
