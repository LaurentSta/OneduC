{{--
  z-[60] : strictement au-dessus du bouton d'aide flottant (z-50,
  resources/views/frontend/master.blade.php) qui, à z-index égal,
  interceptait les clics sur "Accepter"/"Refuser" en bas d'écran.
--}}
<div
    class="js-cookie-consent fixed bottom-0 left-0 z-[60] w-full bg-[#004461] text-white shadow-lg px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-4"
    role="alert"
    aria-live="polite"
>
    <span class="text-sm md:text-base">
        Ce site utilise des cookies essentiels pour garantir son bon fonctionnement et améliorer votre expérience.
        <a href="{{ url('/confidentialite') }}" class="underline hover:text-orange-400 ml-2">En savoir plus</a>.
    </span>
    <div class="flex gap-2 mt-2 md:mt-0">
        <button
    class="js-cookie-consent-agree bg-orange-400 text-[#004461] font-semibold px-4 py-2 rounded hover:bg-orange-500 transition"
>
    Accepter
</button>
<button
    class="js-cookie-consent-decline bg-white text-[#004461] font-semibold px-4 py-2 rounded border border-orange-400 hover:bg-orange-100 transition"
>
    Refuser
</button>

        {{-- Pour ajouter un bouton de gestion plus tard : --}}
        {{-- <a href="{{ url('/parametres-confidentialite') }}" class="ml-2 underline">Gérer mes préférences</a> --}}
    </div>
</div>
